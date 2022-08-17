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
use webfiori\database\EntityMapper;
use webfiori\database\mssql\MSSQLTable;
use webfiori\database\mysql\MySQLTable;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\helpers\CreateClassHelper;
use webfiori\framework\cli\helpers\TableObjHelper;
use webfiori\framework\writers\DBClassWriter;
use webfiori\framework\writers\ServiceHolder;
use webfiori\framework\writers\TableClassWriter;
use webfiori\framework\writers\WebServiceWriter;
use webfiori\json\Json;
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
     * @var DBClassWriter
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
            $suffix .= strtolower($ch);
        }
        return $suffix;
    }

    private function writeServices() {
        $this->println("Writing web services...");
        $entityName = $this->getEntityName();
        $suffix = $this->getServiceSuffix($entityName);
        $uniqueParams = $this->getUniqueAPIParams();
        $t = $this->getTable();
        
        $servicesPrefix = [
            'Add'.$entityName => [
                'type' => 'Add',
                'name' => 'add-'.$suffix,
                'method' => 'post',
            ],
            'Update'.$entityName => [
                'type' => 'Update',
                'name' => 'update-'.$suffix,
                'method' => 'post'
            ],
            'Delete'.$entityName => [
                'type' => 'Delete',
                'name' => 'delete-'.$suffix,
                'method' => 'delete'
            ],
            'Get'.$entityName => [
                'type' => 'GetSingle',
                'name' => 'get-'.$suffix,
                'method' => 'get' 
            ],
            'GetAll'.$entityName.'s' => [
                'type' => 'GetAll',
                'name' => 'get-all-'.$suffix.'s',
                'method' => 'get' 
            ]
        ];
        $w = $this->dbObjWritter;
        
        if ($w->isColumnUpdateIncluded()) {
            $uniqueCols = $this->tableObjWriter->getTable()->getUniqueColsKeys();
            $colsKeys = $this->tableObjWriter->getTable()->getColsKeys();
            
            foreach ($colsKeys as $colKey) {
                if (!in_array($colKey, $uniqueCols)) {
                    $colObj = $t->getColByKey($colKey);
                    $paramProps = [
                        'name' => $colKey,
                        'type' => $this->getAPIParamType($colObj->getDatatype())
                    ];
                    $idxName = 'Update'.DBClassWriter::toMethodName($colKey, '').'Of'.$entityName;
                    $servicesPrefix[$idxName]= [ 
                        'name' => 'update-'.$colKey.'-of-'.$suffix,
                        'method' => 'post',
                        'type' => 'SingleUpdate',
                        'params' => array_merge([$paramProps], $uniqueParams)
                    ];
                }
            }
        }
        
        foreach ($servicesPrefix as $sName => $serviceProps) {
            $service = new ServiceHolder($serviceProps['name']);
            $service->addRequestMethod($serviceProps['method']);
            $t = $this->getTable();

            
            $writer = new WebServiceWriter($service);
            $writer->addUseStatement($this->dbObjWritter->getName(true));
            $writer->addUseStatement($t->getEntityMapper()->getEntityName(true));
            $writer->addUseStatement(Json::class);
            $writer->setNamespace($this->apisNs);
            $writer->setPath($this->apisNs);
            $writer->setClassName($sName);
            $apiType = $serviceProps['type'];
            if ($apiType == 'Add' || $apiType == 'Update') {
                $this->IncludeAPISetProps($writer, $apiType);
                
                foreach($t->getColsKeys() as $paramName) {
                    $colObj = $t->getColByKey($paramName);
                    $paramArr = [
                        'name' => $paramName,
                        'type' => $this->getAPIParamType($colObj->getDatatype()),
                    ];
                    if ($colObj->getDefault() !== null) {
                        $paramArr['default'] = $colObj->getDefault();
                    }
                    if ($colObj->isNull()) {
                        $paramArr['optional'] = true;
                    }
                    $service->addParameter($paramArr);
                }
            } else if ($apiType == 'SingleUpdate') {
                foreach ($serviceProps['params'] as $p) {
                    $service->addParameter($p);
                }
                $this->addSingleUpdateCode();
            } else if ($apiType == 'GetSingle' || $apiType == 'Delete') {
                
                if (count($uniqueParams) != 0) {
                    foreach ($uniqueParams as $p) {
                        $service->addParameter($p);
                    }
                    $this->addDeleteGetProcessCode($writer, $uniqueParams, $apiType);
                }
            } else if ($apiType == 'GetAll') {
                $service->addParameters([
                    'page' => [
                        'type' => 'int',
                        'default' => 1
                    ],
                    'size' => [
                        'type' => 'int',
                        'default' => 10
                    ]
                ]);
                $dbClassName = $this->dbObjWritter->getName();
                $entityName = $this->dbObjWritter->getEntityName();
                $writer->addProcessCode([
                    '$data = '.$dbClassName.'::get()->get'.$entityName.'s($this->getparamVal(\'page\'), $this->getParamVal(\'size\'));',
                    "\$this->send('application/json', new Json(["
                ]);
                $writer->addProcessCode([
                    "'data' => \$data"
                ], 3);
                $writer->addProcessCode([
                    ']));'
                ]);
            }
            $writer->writeClass();
        }
    }
    private function addSingleUpdateCode() {
        
    }
    private function getUniqueAPIParams() : array {
        $params = [];
        $t = $this->getTable();
        foreach($t->getColsKeys() as $paramName) {
            $colObj = $t->getColByKey($paramName);
            if ($colObj->isUnique()) {
                $paramArr = [
                    'name' => $paramName,
                    'type' => $this->getAPIParamType($colObj->getDatatype()),
                ];
                if ($colObj->getDefault() !== null) {
                    $paramArr['default'] = $colObj->getDefault();
                }
                if ($colObj->isNull()) {
                    $paramArr['optional'] = true;
                }
                $params[] = $paramArr;
            }
        }
        return $params;
    }
    private function addDeleteGetProcessCode(WebServiceWriter $w, $uniqueParamsArr, $type) {
        $dbClassName = $this->dbObjWritter->getName();
        $entityName = $this->dbObjWritter->getEntityName();
        $paramsStrArr = [];
        foreach ($uniqueParamsArr as $p) {
            $paramsStrArr[] = "\$this->getParamVal(".$p['name'].")";
        }
        $paramsStr = implode(", ", $paramsStrArr);
        if ($type == 'GetSingle') {
            $w->addProcessCode([
                '$entity = '.$dbClassName.'::get()->get'.$entityName.'('.$paramsStr.');',
                "\$this->send('application/json', new Json(["
            ]);
            $w->addProcessCode([
                "'data' => \$entity"
            ], 3);
            $w->addProcessCode([
                ']));'
            ]);
        } else if ($type == 'Delete') {
            $w->addProcessCode([
                '$entity = '.$dbClassName.'::get()->get'.$entityName.'('.$paramsStr.');',
                $dbClassName.'::get()->delete'.$entityName.'($entity);',
                "\$this->sendResponse('Record Removed.');"
            ]);
        }
    }
    private function IncludeAPISetProps(WebServiceWriter $w, $type) {
        $t = $this->getTable();
        $w->addProcessCode('$entity = new '.$t->getEntityMapper()->getEntityName().'();', 2);
        foreach($t->getColsKeys() as $paramName) {
            $w->addProcessCode('$entity->'.EntityMapper::mapToMethodName($paramName, 's').'($this->getParamVal(\''.$paramName.'\'));', 2);
        }
        $dbClassName = $this->dbObjWritter->getName();
        $entityName = $this->dbObjWritter->getEntityName();
        $w->addProcessCode("");
        if ($type == 'Add') {
            
            $w->addProcessCode("$dbClassName::get()->add$entityName(\$entity);");
            $w->addProcessCode("\$this->sendResponse('Record Created.');");
        } else if ($type == 'Update') {
            
            $w->addProcessCode("$dbClassName::get()->update$entityName(\$entity);");
            $w->addProcessCode("\$this->sendResponse('Record Updated.');");
        }
    }
    private function getAPIParamType($colDatatype) {
        if ($colDatatype == 'int') {
            return 'int';
        }
        if ($colDatatype == 'bool' || $colDatatype == 'boolean') {
            return 'boolean';
        }
        if ($colDatatype == 'decimal' || $colDatatype == 'money') {
            return 'double';
        }
        return 'string';
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
    /**
     * 
     * @return Table|null
     */
    public function getTable() {
        return $this->tableObjWriter->getTable();
    }
    private function createEntity() {
        $this->println("Creating entity class...");
        $this->tableObjWriter->getTable()->getEntityMapper()->create();
    }
    private function readTableInfo() {
        $this->println("Now, time to collect database table information.");
        $ns = ClassInfoReader::readNamespace($this->getCommand(), APP_DIR_NAME.'\\database', 'Provide us with a namespace for table class:');
        $this->tableObjWriter->setNamespace($ns);
        $this->tableObjWriter->setPath($ns);
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
