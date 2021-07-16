<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace webfiori\framework\cli;

use Error;
use ErrorException;
use Exception;
use webfiori\database\Column;
use webfiori\database\mysql\MySQLColumn;
use webfiori\database\Table;
use webfiori\framework\AutoLoader;
use webfiori\framework\DB;
use webfiori\framework\WebFioriApp;
/**
 * Description of UpdateTableCommand
 *
 * @author Eng.Ibrahim
 */
class UpdateTableCommand extends CLICommand {
    public function __construct() {
        parent::__construct('update-table', [
            '--table' => [
                'description' => 'The namespace of the table class (including namespace). '
                . 'Note that every \ in the namespace must be written as \\\\.',
                'optional' => true,
            ]
        ], 'Update a database table.');
    }
    /**
     * 
     * @param Table $tableObj
     */
    public function _addFks($tableObj) {
        $refTable = null;
        $refTabelsNs = [];

        do {
            $refTableName = $this->getInput('Enter the name of the referenced table class (with namespace):');
            try {
                $refTable = new $refTableName();
            } catch (Error $ex) {
                $this->error($ex->getMessage());
                continue;
            } catch (Exception $ex) {
                $this->error($ex->getMessage());
                continue;
            }

            if ($refTable instanceof Table) {
                $fkName = $this->getInput('Enter a name for the foreign key:', null, function ($val)
                {
                    $trimmed = trim($val);

                    if (strlen($trimmed) == 0) {
                        return false;
                    }

                    return true;
                });
                $fkCols = $this->_getFkCols($tableObj);
                $fkColsArr = [];

                foreach ($fkCols as $colKey) {
                    $fkColsArr[$colKey] = $this->select('Select the column that will be referenced by the column \''.$colKey.'\':', $refTable->getColsKeys());
                }
                $onUpdate = $this->select('Choose on update condition:', [
                    'cascade', 'restrict', 'set null', 'set default', 'no action'
                ], 1);
                $onDelete = $this->select('Choose on delete condition:', [
                    'cascade', 'restrict', 'set null', 'set default', 'no action'
                ], 1);

                try {
                    $tableObj->addReference($refTable, $fkColsArr, $fkName, $onUpdate, $onDelete);
                    $this->success('Foreign key added.');
                    $refTabelsNs[$fkName] = $refTableName;
                } catch (Exception $ex) {
                    $this->error($ex->getMessage());
                }
            } else {
                $this->error('The given class is not an instance of the class \'MySQLQuery\'.');
            }
        } while ($this->confirm('Would you like to add another foreign key?', false));

        $class = get_class($tableObj);
        $arr = $this->getClassNs($class);
        $arr['fk-info'] = $refTabelsNs;
        $writer = new QueryClassWriter($tableObj, $arr);
        $writer->writeClass();
        $db = $this->getDb('Would you like to run a query to '
                .'update the table in the database?');

        if ($db !== null) {
            $db->addTable($tableObj);
            $db->table($tableObj->getName())->addForeignKey($fkName);
            $this->runQuery($db);
        }

        $this->success('Table updated.');
    }
    public function exec() {
        $tableClassInput = $this->getArgValue('--table');
        
        while ($tableClassInput === null) {
            $tableClassInput = $this->getInput('Enter the name of table class (including namespace):');
            if (strlen($tableClassInput) == 0) {
                $tableClassInput = null;
                $this->error('Given class name is invalid!');
            }
        }
        $tableClass = trim($tableClassInput, '\\\\');
        
        try {
            $tableObj = new $tableClass();

            if (!($tableObj instanceof Table)) {
                $this->error('Given class is not an instance of "webfiori\database\Table".');

                return -1;
            }
        } catch (Error $ex) {
            $message = $ex->getMessage();

            if ($message == "Class '$tableClass' not found") {
                $this->error($ex->getMessage());

                return -1;
            }
            throw new ErrorException($ex->getMessage(), $ex->getCode(), E_ERROR, $ex->getFile(), $ex->getLine(), $ex->getPrevious());
        }

        $whatToDo = $this->select('What operation whould you like to do with the table?', [
            'Add new column.',
            'Add foreign key.',
            'Update existing column.',
            'Drop column.',
            'Drop foreign key.'
        ]);

        if ($whatToDo == 'Add new column.') {
            $col = $this->_addColumn($tableObj);

            if ($col instanceof Column) {
                $classInfo = $this->getClassNs($tableClass);

                $writer = new QueryClassWriter($tableObj, $classInfo);
                $writer->writeClass();
                $this->success('New column was added to the table.');
            } else {
                return -1;
            }
        } else if ($whatToDo == 'Drop column.') {
            $this->dropCol($tableObj);
        } else if ($whatToDo == 'Add foreign key.') {
            $this->_addFks($tableObj);
        } else if ($whatToDo == 'Update existing column.') {
            $this->updateCol($tableObj);
        } else if ($whatToDo == 'Drop foreign key.') {
            $this->_removeFk($tableObj);
        } else {
            $this->error('Option not implemented.');
        }
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _addColComment($colObj) {
        if ($this->confirm('Would you like to add your own comment about the column?', false)) {
            $comment = $this->getInput('Enter your comment:');

            if (strlen($comment) != 0) {
                $colObj->setComment($comment);
            }
        }
        $this->success('Column added.');
    }
    /**
     * 
     * @param Table $tableObj
     * @return Column Description
     */
    private function _addColumn($tableObj) {
        $colKey = $this->getInput('Enter a name for column key:');
        $col = new MySQLColumn();
        $colDatatype = $this->select('Select column data type:', $col->getSupportedTypes(), 0);
        $col->setDatatype($colDatatype);
        $isAdded = $tableObj->addColumn($colKey, $col);

        if (!$isAdded) {
            $this->warning('The column was not added. Mostly, key name is invalid.');
        } else {
            $colObj = $tableObj->getColByKey($colKey);
            $colObj->setName(str_replace('-', '_', $colKey));
            $this->_setSize($colObj);
            $this->_isPrimaryCheck($colObj);
            $this->_addColComment($colObj);
        }
        $db = $this->getDb('Would you like to run a query to '
                .'add the column in the database?');

        if ($db !== null) {
            $db->addTable($tableObj);
            $db->table($tableObj->getName())->addCol($colKey);
            $this->runQuery($db);
        }

        return $colObj;
    }
    /**
     * 
     * @param Table $tableObj
     * @return type
     */
    private function _getFkCols($tableObj) {
        $colNumber = 1;
        $keys = $tableObj->getColsKeys();
        $fkCols = [];

        do {
            $colKey = $this->select('Select column #'.$colNumber.':', $keys);

            if (!in_array($colKey, $fkCols)) {
                $fkCols[] = $colKey;
                $colNumber++;
            } else {
                $this->error('The column is already added.');
            }
        } while ($this->confirm('Would you like to add another column to the foreign key?', false));

        return $fkCols;
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _isPrimaryCheck($colObj) {
        $colObj->setIsPrimary($this->confirm('Is this column primary?', false));
        $type = $colObj->getDatatype();

        if (!$colObj->isPrimary()) {
            if (!($type == 'bool' || $type == 'boolean')) {
                $colObj->setIsUnique($this->confirm('Is this column unique?', false));
            }
            $this->_setDefaultValue($colObj);
            $colObj->setIsNull($this->confirm('Can this column have null values?', false));
        } else {
            if ($colObj->getDatatype() == 'int') {
                $colObj->setIsAutoInc($this->confirm('Is this column auto increment?', false));
            }
        }
    }
    /**
     * 
     * @param Table $tableObj
     */
    private function _removeFk($tableObj) {
        if ($tableObj->getForignKeysCount() == 0) {
            $this->info('Selected table has no foreign keys.');

            return;
        }
        $fks = $tableObj->getForignKeys();
        $optionsArr = [];

        foreach ($fks as $fkObj) {
            $optionsArr[] = $fkObj->getKeyName();
        }
        $toRemove = $this->select('Select the key that you would like to remove:', $optionsArr);
        $tableObj->removeReference($toRemove);

        $class = get_class($tableObj);
        $arr = $this->getClassNs($class);

        $writer = new QueryClassWriter($tableObj, $arr);
        $writer->writeClass();
        $db = $this->getDb('Would you like to run a query to '
                .'update the table in the database?');

        if ($db !== null) {
            $db->addTable($tableObj);
            $db->table($tableObj->getName())->dropForeignKey($toRemove);
            $this->runQuery($db);
        }

        $this->success('Table updated.');
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _setDefaultValue($colObj) {
        if ($colObj->getDatatype() == 'bool' || $colObj->getDatatype() == 'boolean') {
            $defaultVal = trim($this->getInput('Enter default value (true or false) (Hit "Enter" to skip):', ''));

            if ($defaultVal == 'true') {
                $colObj->setDefault(true);
            } else {
                if ($defaultVal == 'false') {
                    $colObj->setDefault(false);
                }
            }
        } else {
            $defaultVal = trim($this->getInput('Enter default value (Hit "Enter" to skip):', ''));

            if (strlen($defaultVal) != 0) {
                $colObj->setDefault($defaultVal);
            }
        }
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _setScale($colObj) {
        $colDataType = $colObj->getDatatype();

        if ($colDataType == 'decimal' || $colDataType == 'float' || $colDataType == 'double') {
            $validScale = false;

            do {
                $scale = $this->getInput('Enter the scale (number of numbers to the right of decimal point):');
                $validScale = $colObj->setScale($scale);

                if (!$validScale) {
                    $this->error('Invalid scale value.');
                }
            } while (!$validScale);
        }
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _setSize($colObj) {
        $type = $colObj->getDatatype();
        $supportSize = $type == 'int' 
                || $type == 'varchar'
                || $type == 'decimal' 
                || $type == 'float'
                || $type == 'double' 
                || $type == 'text';

        if ($supportSize) {
            $valid = false;

            do {
                $colDataType = $colObj->getDatatype();
                $dataSize = $this->getInput('Enter column size:');

                if ($colObj->getDatatype() == 'varchar' && $dataSize > 21845) {
                    $this->warning('The data type "varchar" has a maximum size of 21845. The '
                            .'data type of the column will be changed to "mediumtext" if you continue.');

                    if (!$this->confirm('Would you like to change data type?', false)) {
                        continue;
                    }
                }

                if ($colDataType == 'int' && $dataSize > 11) {
                    $this->warning('Size is set to 11 since this is the maximum size for "int" type.');
                }
                $valid = $colObj->setSize($dataSize);

                if (!$valid) {
                    $this->error('Invalid size is given.');
                } else {
                    $this->_setScale($colObj);
                }
            } while (!$valid);
        }
    }
    /**
     * 
     * @param Table $tableObj
     */
    private function dropCol($tableObj) {
        $colsKeys = $tableObj->getColsKeys();

        if (count($colsKeys) == 0) {
            $this->info('The table has no columns. Nothing to drop.');

            return;
        }
        $colToDrop = $this->select('Which column would you like to drop?', $colsKeys);
        $tableObj->removeColByKey($colToDrop);
        $class = get_class($tableObj);
        $writer = new QueryClassWriter($tableObj, $this->getClassNs($class));
        $writer->writeClass();
        $this->success('Column dropped.');
    }
    private function getClassNs($class) {
        $split = explode('\\', $class);
        $cName = $split[count($split) - 1];
        $ns = implode('\\', array_slice($split, 0, count($split) - 1));

        $path = AutoLoader::getClassPath($cName, $ns)[0];

        return [
            'name' => $cName,
            'namespace' => $ns,
            'path' => substr($path, 0, strlen($path) - strlen($cName.'.php'))
        ];
    }
    private function getDb($prompt) {
        $runQuery = $this->confirm($prompt, false);

        if ($runQuery) {
            $dbConnections = array_keys(WebFioriApp::getAppConfig()->getDBConnections());

            if (count($dbConnections) != 0) {
                $dbConn = $this->select('Select database connection:', $dbConnections, 0);


                return new DB($dbConn);
            } else {
                $this->error('No database connections available. Add connections inside the class \'AppConfig\' or use the command "add".');
            }
        }
    }
    /**
     * 
     * @param DB $db
     */
    private function runQuery($db) {
        $this->println('Exexuting the query "'.$db->getLastQuery().'"...');
        try {
            $db->execute();
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        }
    }
    /**
     * 
     * @param Table $tableObj
     * @return type
     */
    private function updateCol($tableObj) {
        $colsKeys = $tableObj->getColsKeys();

        if (count($colsKeys) == 0) {
            $this->info('The table has no columns. Nothing to update.');

            return;
        }
        $colToUpdate = $this->select('Which column would you like to update?', $colsKeys);
        $col = $tableObj->removeColByKey($colToUpdate);

        $colKey = $this->getInput('Enter a new name for column key:', $colToUpdate);
        $isAdded = $tableObj->addColumn($colKey, $col);

        if ($colKey != $colToUpdate) {
            $col->setName(str_replace('-', '_', $colKey));
        } else {
            $isAdded = true;
        }

        if (!$isAdded) {
            $this->warning('The column was not added. Mostly, key name is invalid.');
        } else {
            $colDatatype = $this->select('Select column data type:', $col->getSupportedTypes(), 0);
            $col->setDatatype($colDatatype);
            $this->_setSize($col);
            $this->_isPrimaryCheck($col);
            $this->_addColComment($col);
        }

        $class = get_class($tableObj);
        $writer = new QueryClassWriter($tableObj, $this->getClassNs($class));
        $writer->writeClass();
        $db = $this->getDb('Would you like to run a query to '
                .'update the column in the database?');

        if ($db !== null) {
            $db->addTable($tableObj);
            $db->table($tableObj->getName())->modifyCol($colKey);
            $this->runQuery($db);
        }

        $this->success('Column updated.');
    }
}
