<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace webfiori\framework\cli;
use webfiori\framework\cli\CLICommand;
use webfiori\database\Column;
use webfiori\database\Table;
use webfiori\database\mysql\MySQLTable;
use webfiori\database\mysql\MySQLColumn;
use webfiori\framework\AutoLoader;
use webfiori\framework\cli\QueryClassWriter;
use Error;
use ErrorException;
/**
 * Description of UpdateTableCommand
 *
 * @author Eng.Ibrahim
 */
class UpdateTableCommand extends CLICommand {
    public function __construct() {
        parent::__construct('update-table', [
            '--table' => [
                
            ]
        ], 'Update a database table.');
    }
    public function exec() {
        $tableClass = $this->getArgValue('--table');
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
            'Update existing column.',
            'Drop column.'
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
        } else if ($whatToDo == 'Update existing column.') {
            $this->updateCol($tableObj);
        } else {
            $this->error('Option not implemented.');
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
        $col = $tableObj->getColByKey($colToUpdate);
        
        $colKey = $this->getInput('Enter a new name for column key:', $colToUpdate);
        if ($colKey != $colToUpdate) {
            $isAdded = $tableObj->addColumn($colKey, $col);
        } else {
            $isAdded = true;
        }

        if (!$isAdded) {
            $this->warning('The column was not added. Mostly, key name is invalid.');
        } else {
            $colDatatype = $this->select('Select column data type:', $col->getSupportedTypes(), 0);
            $col->setDatatype($colDatatype);
            $colObj = $tableObj->getColByKey($colKey);
            $this->_setSize($colObj);
            $this->_isPrimaryCheck($colObj);
            $this->_addColComment($colObj);
        }
        
        $class = get_class($tableObj);
        $writer = new QueryClassWriter($tableObj, $this->getClassNs($class));
        $writer->writeClass();
        $this->success('Column dropped.');
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
    private function _setDefaultValue($colObj) {
        // TODO: Test default value using empty.
        if ($colObj->getDatatype() == 'bool' || $colObj->getDatatype() == 'boolean') {
            $defaultVal = trim($this->getInput('Enter default value (true or false) (Hit "Enter" to skip):', ''));

            if ($defaultVal == 'true') {
                $colObj->setDefault(true);
            } else if ($defaultVal == 'false') {
                $colObj->setDefault(false);
            }
        } else {
            $defaultVal = trim($this->getInput('Enter default value (Hit "Enter" to skip):', ''));

            if (strlen($defaultVal) != 0) {
                $colObj->setDefault($defaultVal);
            }
        }
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
    private function finalSteps() {
        if ($this->confirm('Would you like to create an entity class that maps to the database table?', false)) {
            $entityInfo = $this->getClassInfo();
            $entityInfo['implement-jsoni'] = $this->confirm('Would you like from your entity class to implement the interface JsonI?', true);
            $classInfo['entity-info'] = $entityInfo;
        }

        if (strlen($classInfo['namespace']) == 0) {
            $classInfo['namespace'] = 'app\database';
            $this->warning('The table class will be added to the namespace "'.$classInfo['namespace'].'" since no namespace was provided.');
        }

        if (isset($classInfo['entity-info']) && strlen($classInfo['entity-info']['namespace']) == 0) {
            $classInfo['entity-info']['namespace'] = 'app\database';
            $this->warning('The entity class will be added to the namespace "'.$classInfo['entity-info']['namespace'].'" since no namespace was provided.');
        }
        $writer = new QueryClassWriter($tempTable, $classInfo);
        $writer->writeClass();
        $this->success('New class created.');
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
            $this->_setSize($colObj);
            $this->_isPrimaryCheck($colObj);
            $this->_addColComment($colObj);
        }
        return $colObj;
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
}
