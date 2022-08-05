<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2019 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\cli\helpers;

use webfiori\database\ConnectionInfo;
use webfiori\database\mssql\MSSQLTable;
use webfiori\database\mysql\MySQLTable;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\helpers\CreateClassHelper;
use webfiori\framework\cli\helpers\TableObjHelper;
use webfiori\framework\writers\DBClassWriter;
use webfiori\framework\writers\ServiceHolder;
use webfiori\framework\writers\TableClassWriter;
use webfiori\framework\writers\WebServiceWriter;
/**
 * A helper class for creating database tables classes.
 *
 * @author Ibrahim
 */
class CreateFullRESTHelper extends CreateClassHelper {
    /**
     * 
     * @var TableClassWriter
     */
    private $tableObjWriter;
    /**
     * 
     * @var CreateDBAccessHelper
     */
    private $dbObjWritter;
    private $apisNs;
    /**
     * Creates new instance of the class.
     * 
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        parent::__construct($command);
        
        $dbType = $this->select('Database type:', ConnectionInfo::SUPPORTED_DATABASES);


        if ($dbType == 'mysql') {
            $tempTable = new MySQLTable();
        } else if ($dbType == 'mssql') {
            $tempTable = new MSSQLTable();
        }
        $this->tableObjWriter = new TableClassWriter($tempTable);
        $this->readEntityInfo();
        
        $entityName = $this->tableObjWriter->getEntityName();
        $this->tableObjWriter->setClassName($entityName.'Table');
        $this->readTableInfo();
        
        $t = $this->tableObjWriter->getTable();
        $t->getEntityMapper()->setEntityName($this->tableObjWriter->getEntityName());
        $t->getEntityMapper()->setNamespace($this->tableObjWriter->getEntityNamespace());
        $t->getEntityMapper()->setPath($this->tableObjWriter->getEntityPath());
        $this->dbObjWritter = new DBClassWriter($this->tableObjWriter->getEntityName().'DB', $this->tableObjWriter->getNamespace(), $t);
        if ($this->confirm('Would you like to have update methods for every single column?', false)) {
            $this->dbObjWritter->includeColumnsUpdate();
        }
        $this->readAPIInfo();
        $this->createEntity();
        $this->createTableClass();
        $this->createDbClass();
        $this->writeServices();
        $this->println("Done.");
    }
    public function getEntityName() : string {
        return $this->tableObjWriter->getEntityName();
    }
    private function getServiceSuffix($entityName) {
        $suffix = '';
        for ($x = 0 ; $x < strlen($entityName) ; $x++) {
            $ch = $entityName[$x];
            if ($x != 0 && $ch >= 'A' && $ch <= 'Z') {
                $suffix .= '-'.strtolower($ch);
                continue;
            } 
            $suffix .= $ch;
        }
        return $suffix;
    }

    private function writeServices() {
        $this->println("Writing web services...");
        $entityName = $this->getEntityName();
        $suffix = $this->getServiceSuffix($entityName);
        $servicesPrefix = [
            'Add'.$entityName => [
                'name' => 'add-'.$suffix,
                'method' => 'post'
            ],
            'Update'.$entityName => [
                'name' => 'update-'.$suffix,
                'method' => 'post'
            ],
            'Delete'.$entityName => [
                'name' => 'delete-'.$suffix,
                'method' => 'delete'
            ],
            'Get'.$entityName => [
                'name' => 'get-'.$suffix,
                'method' => 'get' 
            ]
        ];
        $w = $this->dbObjWritter;
        $w instanceof DBClassWriter;
        if ($w->isColumnUpdateIncluded()) {
            $uniqueCols = $this->tableObjWriter->getTable()->getUniqueColsKeys();
            $colsKeys = $this->tableObjWriter->getTable()->getColsKeys();
            
            foreach ($colsKeys as $colKey) {
                if (!in_array($colKey, $uniqueCols)) {
                    $servicesPrefix['Update'.DBClassWriter::toMethodName($colKey, '').'Of'.$entityName]
                             = [ 
                                 'name' => 'update-'.$colKey.'-of-'.$suffix,
                                 'method' => 'post'
                             ];
                }
            }
        }
        foreach ($servicesPrefix as $sName => $serviceProps) {
            $service = new ServiceHolder($serviceProps['name']);
            $service->addRequestMethod($serviceProps['method']);
            $writer = new WebServiceWriter($service);
            $writer->setNamespace($this->apisNs);
            $writer->setClassName($sName);
            $writer->writeClass();
        }
    }

    private function readAPIInfo() {
        $this->apisNs = ClassInfoReader::readNamespace($this->getCommand(), APP_DIR_NAME.'\\apis',"Last thing needed is to provide us with namespace for web services:");
    }
    private function createDbClass() {
        $this->println("Creating database access class...");
        $this->dbObjWritter->writeClass();
    }

    private function createTableClass() {
        $this->println("Creating database table class...");
        $this->tableObjWriter->writeClass();
    }

    private function createEntity() {
        $this->println("Creating entity class...");
        $this->tableObjWriter->getTable()->getEntityMapper()->create();
    }
    private function readTableInfo() {
        $this->println("Now, time to collect database table information.");
        $ns = ClassInfoReader::readNamespace($this->getCommand(), APP_DIR_NAME.'\\database', 'Provide us with a namespace for table class:');
        $this->tableObjWriter->setNamespace($ns);
        $tableHelper = new TableObjHelper(new CreateClassHelper($this->getCommand(), $this->tableObjWriter), $this->tableObjWriter->getTable());
        $tableHelper->setTableName();
        $tableHelper->setTableComment();
        $this->println('Now you have to add columns to the table.');
        $tableHelper->addColumns();
        
        if ($this->confirm('Would you like to add foreign keys to the table?', false)) {
            $tableHelper->addForeignKeys();
        }
    }
    private function readEntityInfo() {
        $this->println("First thing, we need entity class information.");
        $entityInfo = $this->getClassInfo(APP_DIR_NAME.'\\entity');
        $entityInfo['implement-jsoni'] = $this->confirm('Would you like from your entity class to implement the interface JsonI?', true);
        $this->tableObjWriter->setEntityInfo($entityInfo['name'], $entityInfo['namespace'], $entityInfo['path'], $entityInfo['implement-jsoni']);

        if ($this->confirm('Would you like to add extra attributes to the entity?', false)) {
            $addExtra = true;

            while ($addExtra) {

                if ($this->tableObjWriter->getTable()->getEntityMapper()->addAttribute($this->getInput('Enter attribute name:'))) {
                    
                } else {
                    $this->warning('Unable to add attribute.');
                }
                $addExtra = $this->confirm('Would you like to add another attribute?', false);
            }
        }
    }
}
