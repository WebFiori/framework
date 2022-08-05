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
use webfiori\framework\writers\TableClassWriter;
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
        $this->createEntity();
        $this->createTableClass();
        $this->println("Done.");
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
