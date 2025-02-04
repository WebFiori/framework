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
use webfiori\database\Table;
use webfiori\framework\cli\CLIUtils;
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
    private $apisNs;
    /**
     *
     * @var DBClassWriter
     */
    private $dbObjWriter;
    /**
     *
     * @var TableClassWriter
     */
    private $tableObjWriter;
    public function getEntityName() : string {
        return $this->tableObjWriter->getEntityName();
    }
    /**
     *
     * @return Table|null
     */
    public function getTable() {
        return $this->tableObjWriter->getTable();
    }
    public function readInfo() {
        $connection = CLIUtils::getConnectionName($this->getCommand());

        if ($connection === null) {
            $dbType = $this->select('Database type:', ConnectionInfo::SUPPORTED_DATABASES);
        } else {
            $dbType = $connection->getDatabaseType();
        }

        $tempTable = new MySQLTable();

        if ($dbType == 'mssql') {
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
        $this->dbObjWriter = new DBClassWriter($this->tableObjWriter->getEntityName().'DB', $this->tableObjWriter->getNamespace(), $t);

        if ($connection !== null) {
            $this->dbObjWriter->setConnection($connection->getName());
        }

        if ($this->confirm('Would you like to have update methods for every single column?', false)) {
            $this->dbObjWriter->includeColumnsUpdate();
        }
        $this->readAPIInfo();
        $this->createEntity();
        $this->createTableClass();
        $this->createDbClass();
        $this->writeServices();
        $this->println("Done.");
    }
    private function addDeleteGetProcessCode(WebServiceWriter $w, $uniqueParamsArr, $type) {
        $dbClassName = $this->dbObjWriter->getName();
        $entityName = $this->dbObjWriter->getEntityName();
        $paramsStrArr = [];

        foreach ($uniqueParamsArr as $p) {
            $paramsStrArr[] = "\$this->getParamVal('".$p['name']."')";
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
    private function addSingleUpdateCode() {
    }
    private function createDbClass() {
        $this->println("Creating database access class...");
        $this->dbObjWriter->writeClass();
    }
    private function createEntity() {
        $this->println("Creating entity class...");
        $this->tableObjWriter->getTable()->getEntityMapper()->create();
    }

    private function createTableClass() {
        $this->println("Creating database table class...");
        $this->tableObjWriter->writeClass();
    }
    private function getAPIParamType($colDatatype): string {
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
    private function getServiceSuffix($entityName): string {
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
    private function getUniqueAPIParams() : array {
        $params = [];
        $t = $this->getTable();

        foreach ($t->getColsKeys() as $paramName) {
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
    private function IncludeAPISetProps(WebServiceWriter $w, $type) {
        $t = $this->getTable();
        $w->addProcessCode('$entity = $this->getObject('.$t->getEntityMapper()->getEntityName().'::class);');


        $dbClassName = $this->dbObjWriter->getName();
        $entityName = $this->dbObjWriter->getEntityName();
        $w->addProcessCode("");

        if ($type == 'Add') {
            $w->addProcessCode("$dbClassName::get()->add$entityName(\$entity);");
            $w->addProcessCode("\$this->sendResponse('Record Created.');");
        } else if ($type == 'Update') {
            $w->addProcessCode("$dbClassName::get()->update$entityName(\$entity);");
            $w->addProcessCode("\$this->sendResponse('Record Updated.');");
        }
    }

    private function readAPIInfo() {
        $this->apisNs = CLIUtils::readNamespace($this->getCommand(), APP_DIR.'\\apis',"Last thing needed is to provide us with namespace for web services:");
    }
    private function readEntityInfo() {
        $this->println("First thing, we need entity class information.");
        $entityInfo = $this->getClassInfo(APP_DIR.'\\entity');
        $entityInfo['implement-jsoni'] = $this->confirm('Would you like from your entity class to implement the interface JsonI?', true);
        $this->tableObjWriter->setEntityInfo($entityInfo['name'], $entityInfo['namespace'], $entityInfo['path'], $entityInfo['implement-jsoni']);

        if ($this->confirm('Would you like to add extra attributes to the entity?', false)) {
            $addExtra = true;

            while ($addExtra) {
                if ($this->tableObjWriter->getTable()->getEntityMapper()->addAttribute($this->getInput('Enter attribute name:'))) {
                    $this->success('Attribute added.');
                } else {
                    $this->warning('Unable to add attribute.');
                }
                $addExtra = $this->confirm('Would you like to add another attribute?', false);
            }
        }
    }
    private function readTableInfo() {
        $this->println("Now, time to collect database table information.");
        $ns = CLIUtils::readNamespace($this->getCommand(), APP_DIR.'\\database', 'Provide us with a namespace for table class:');
        $this->tableObjWriter->setNamespace($ns);
        $this->tableObjWriter->setPath(ROOT_PATH.DS.$ns);

        $create = new CreateTableObj($this->getCommand());
        $create->getWriter()->setTable($this->tableObjWriter->getTable());
        $tableHelper = new TableObjHelper($create, $this->tableObjWriter->getTable());
        $tableHelper->setTableName();
        $tableHelper->setTableComment();
        $tableHelper->getCreateHelper()->setNamespace($ns);
        $tableHelper->getCreateHelper()->setPath(ROOT_PATH.DS.$ns);
        $tableHelper->getCreateHelper()->setClassName($this->tableObjWriter->getName());
        $this->println('Now you have to add columns to the table.');
        $tableHelper->addColumns();

        if ($this->confirm('Would you like to add foreign keys to the table?', false)) {
            $tableHelper->addForeignKeys();
        }
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
        $w = $this->dbObjWriter;

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
                    $servicesPrefix[$idxName] = [
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
            $writer->addUseStatement($this->dbObjWriter->getName(true));
            $writer->addUseStatement($t->getEntityMapper()->getEntityName(true));
            $writer->addUseStatement(Json::class);
            $writer->setNamespace($this->apisNs);
            $writer->setPath(ROOT_PATH.DS.$this->apisNs);
            $writer->setClassName($sName);
            $apiType = $serviceProps['type'];

            if ($apiType == 'Add' || $apiType == 'Update') {
                $this->IncludeAPISetProps($writer, $apiType);

                foreach ($t->getColsKeys() as $paramName) {
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
                $dbClassName = $this->dbObjWriter->getName();
                $entityName = $this->dbObjWriter->getEntityName();
                $writer->addProcessCode([
                    "\$pageNumber = \$this->getParamVal('page');",
                    "\$pageSize = \$this->getParamVal('size');",
                    '$recordsCount = '.$dbClassName.'::get()->get'.$entityName.'sCount();',
                    '$data = '.$dbClassName.'::get()->get'.$entityName.'s($pageNumber, $pageSize);',
                    "\$this->send('application/json', new Json(["
                ]);
                $writer->addProcessCode([
                    "'page' => new Json(["
                ], 3);

                $writer->addProcessCode([
                    "'pages-count' => ceil(\$recordsCount/\$pageSize),",
                    "'size' => \$pageSize,",
                    "'page-number' => \$pageNumber,",
                ], 4);

                $writer->addProcessCode([
                    "]),"
                ], 3);
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
}
