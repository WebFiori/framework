<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2020 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\cli\helpers;

use webfiori\framework\cli\helpers\CreateClassHelper;
use webfiori\database\Table;
use webfiori\database\mysql\MySQLColumn;
use webfiori\database\mssql\MSSQLColumn;
use webfiori\framework\AutoLoader;
use webfiori\database\Column;
use webfiori\framework\DB;
use webfiori\database\mysql\MySQLTable;
use webfiori\cli\InputValidator;

/**
 * A class which contains static methods which is used to create/modify tables.
 *
 * @author Ibrahim BinAlshikh
 */
class TableObjHelper {
    private $table;
    private $command;
    public function __construct(CreateClassHelper $c, Table $t) {
        $this->command = $c;
        $this->table = $t;
    }
    /**
     * 
     * @return Table
     */
    public function &getTable() {
        return $this->table;
    }
    /**
     * 
     * @return CreateClassHelper
     */
    public function getCreateHelper() : CreateClassHelper {
        return $this->command;
    }
    public function addColumn() {
        $helper = $this->getCreateHelper();
        $tempTable = $this->getTable();
        $colKey = $helper->getInput('Enter a name for column key:');

        if ($tempTable->hasColumnWithKey($colKey)) {
            $helper->warning("The table already has a key with name '$colKey'.");
            return;
        }
        $col = new MSSQLColumn();
        
        if ($tempTable instanceof MySQLTable) {
            $col = new MySQLColumn();
        }
        $col->setName(str_replace('-', '_', str_replace(' ', '_', $colKey)));
        $colDatatype = $helper->select('Column data type:', $col->getSupportedTypes(), 0);
        $col->setDatatype($colDatatype);
        $isAdded = $tempTable->addColumn($colKey, $col);

        if (!$isAdded) {
            $helper->warning('The column was not added. Mostly, key name is invalid. Try again.');
        } else {
            $colObj = $tempTable->getColByKey($colKey);
            $this->setSize($colObj);
            $this->isIdentityCheck($colObj);
            $this->isPrimaryCheck($colObj);
            $this->addColComment($colObj);
        }
        $this->getCreateHelper()->writeClass(false);
    }
    public function createEntity() {
        $helper = $this->getCreateHelper();
        $entityInfo = $helper->getClassInfo(APP_DIR_NAME.'\\entity');
        $entityInfo['implement-jsoni'] = $helper->confirm('Would you like from your entity class to implement the interface JsonI?', true);
        $helper->getWriter()->setEntityInfo($entityInfo['name'], $entityInfo['namespace'], $entityInfo['path'], $entityInfo['implement-jsoni']);

        if ($helper->confirm('Would you like to add extra attributes to the entity?', false)) {
            $addExtra = true;

            while ($addExtra) {

                if ($this->getTable()->getEntityMapper()->addAttribute($this->getInput('Enter attribute name:'))) {
                    $helper->success('Attribute added.');
                } else {
                    $helper->warning('Unable to add attribute.');
                }
                $addExtra = $helper->confirm('Would you like to add another attribute?', false);
            }
        }
    }

    public function setTableComment() {
        $helper = $this->getCreateHelper();
        $tableComment = $helper->getInput('Enter your optional comment about the table:');

        if (strlen($tableComment) != 0) {
            $this->getTable()->setComment($tableComment);
        }
    }
    public function setTableName() {
        $invalidTableName = true;
        $helper = $this->getCreateHelper();
        do {
            $tableName = $helper->getInput('Enter database table name:');
            $invalidTableName = !$this->getTable()->setName($tableName);

            if ($invalidTableName) {
                $helper->error('The given name is invalid.');
            }
        } while ($invalidTableName);
    }
    public function addColumns() {
        do {
            $this->addColumn();
        } while ($this->getCreateHelper()->confirm('Would you like to add another column?', false));
    }
    /**
     * 
     * @param MySQLColumn|MSSQLColumn $colObj
     */
    private function addColComment($colObj) {
        $comment = $this->getCreateHelper()->getInput('Enter your optional comment about the column:');

        if (strlen($comment) != 0) {
            $colObj->setComment($comment);
        }
        $this->getCreateHelper()->success('Column added.');
    }
    /**
     * 
     * @param MSSQLColumn $colObj
     */
    private function isIdentityCheck($colObj) {
        if ($colObj instanceof MSSQLColumn) {
            $dataType = $colObj->getDatatype();
            $t = $this->getTable();
            
            if (($dataType == 'int' || $dataType == 'bigint') && !$t->hasIdentity()) {
                $colObj->setIsIdentity($this->getCreateHelper()->confirm('Is this column an identity column?', false));
            }
        }
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function isPrimaryCheck($colObj) {
        $helper = $this->getCreateHelper();
        $colObj->setIsPrimary($helper->confirm('Is this column primary?', false));
        $type = $colObj->getDatatype();

        if (!$colObj->isPrimary()) {
            if (!($type == 'bool' || $type == 'boolean')) {
                $colObj->setIsUnique($helper->confirm('Is this column unique?', false));
            }
            $this->setDefaultValue($colObj);
            $colObj->setIsNull($helper->confirm('Can this column have null values?', false));
        } else if ($colObj->getDatatype() == 'int' && $colObj instanceof MySQLColumn) {
            $colObj->setIsAutoInc($helper->confirm('Is this column auto increment?', false));
        }
    }
    /**
     * 
     * @param Column $colObj
     */
    private function setDefaultValue($colObj) {
        $helper = $this->getCreateHelper();
        if (!($colObj->getDatatype() == 'bool' || $colObj->getDatatype() == 'boolean')) {
            $defaultVal = $helper->getInput('Enter default value (Hit "Enter" to skip):', '');

            if (strlen($defaultVal) != 0) {
                $colObj->setDefault($defaultVal);
            }
            return;
        }
        $defaultVal = $helper->getInput('Enter default value (true or false) (Hit "Enter" to skip):', '');

        if ($defaultVal == 'true') {
            $colObj->setDefault(true);
        } else if ($defaultVal == 'false') {
            $colObj->setDefault(false);
        }
    }
    /**
     * 
     * @param MySQLColumn|MSSQLColumn $colObj
     */
    private function setSize($colObj) {
        $type = $colObj->getDatatype();
        $helper = $this->getCreateHelper();
        $mySqlSupportSize = $type == 'int' 
                || $type == 'varchar'
                || $type == 'decimal' 
                || $type == 'float'
                || $type == 'double' 
                || $type == 'text';
        $mssqlSupportSize = $type == 'char'
                || $type == 'nchar'
                || $type == 'varchar'
                || $type == 'nvarchar'
                || $type == 'binary'
                || $type == 'varbinary'
                || $type == 'decimal'
                || $type == 'float';

        if (($colObj instanceof MySQLColumn && $mySqlSupportSize)
                || $colObj instanceof MSSQLColumn && $mssqlSupportSize) {
            $valid = false;

            do {
                $colDataType = $colObj->getDatatype();
                $dataSize = $helper->getCommand()->readInteger('Enter column size:');

                if ($colObj instanceof MySQLColumn && $colObj->getDatatype() == 'varchar' && $dataSize > 21845) {
                    $helper->warning('The data type "varchar" has a maximum size of 21845. The '
                            .'data type of the column will be changed to "mediumtext" if you continue.');

                    if (!$helper->confirm('Would you like to change data type?', false)) {
                        $valid = true;
                        continue;
                    }
                }

                if ($colDataType == 'int' && $dataSize > 11) {
                    $helper->warning('Size is set to 11 since this is the maximum size for "int" type.');
                }
                $valid = $colObj->setSize($dataSize);

                if ($valid) {
                    $this->setScale($colObj);
                    continue;
                }
                $helper->error('Invalid size is given.');
            } while (!$valid);
        }
    }
    /**
     * 
     * @param MySQLColumn|MSSQLColumn $colObj
     */
    private function setScale($colObj) {
        $colDataType = $colObj->getDatatype();

        if ($colDataType == 'decimal' || $colDataType == 'float' || $colDataType == 'double') {
            $validScale = false;

            do {
                $scale = $this->getCreateHelper()->getInput('Enter the scale (number of numbers to the right of decimal point):');
                $validScale = $colObj->setScale($scale);

                if (!$validScale) {
                    $this->getCreateHelper()->error('Invalid scale value.');
                }
            } while (!$validScale);
        }
    }
    public function addForeignKey() {
        $refTable = null;
        $helper = $this->getCreateHelper();
        
        $refTableName = $helper->getInput('Enter the name of the referenced table class (with namespace):');
        try {
            $refTable = new $refTableName();
        } catch (\Throwable $ex) {
            $helper->error($ex->getMessage());
            return;
        }

        if (!($refTable instanceof Table)) {
            $helper->error('The given class is not an instance of the class \''.Table::class.'\'.');
            return;
        }
        $fkName = $helper->getInput('Enter a name for the foreign key:', null, new InputValidator(function ($val)
        {
            $trimmed = trim($val);

            if (strlen($trimmed) == 0) {
                return false;
            }

            return true;
        }));
        $fkCols = $this->getFkCols();
        $fkColsArr = [];

        foreach ($fkCols as $colKey) {
            $fkColsArr[$colKey] = $helper->select('Select the column that will be referenced by the column \''.$colKey.'\':', $refTable->getColsKeys());
        }
        $onUpdate = $helper->select('Choose on update condition:', [
            'cascade', 'restrict', 'set null', 'set default', 'no action'
        ], 1);
        $onDelete = $helper->select('Choose on delete condition:', [
            'cascade', 'restrict', 'set null', 'set default', 'no action'
        ], 1);

        try {
            $this->getTable()->addReference($refTable, $fkColsArr, $fkName, $onUpdate, $onDelete);
            $helper->getWriter()->writeClass();
            $helper->success('Foreign key added.');
        } catch (\Throwable $ex) {
            $helper->error($ex->getMessage());
        }
    }
    public function addForeignKeys() {
        do {
            $this->addForeignKey();
        } while ($this->getCreateHelper()->confirm('Would you like to add another foreign key?', false));

    }
    /**
     * 
     * @return DB|null
     */
    public function confirmRunQuery() {
        $runQuery = $this->confirm('Would you like to update the database?', false);

        if ($runQuery) {
            $dbConnections = array_keys(WebFioriApp::getAppConfig()->getDBConnections());

            if (count($dbConnections) == 0) {
                $this->error('No database connections available. Add connections inside the class \'AppConfig\' or use the command "add".');
                return null;
            }
            $dbConn = $this->select('Select database connection:', $dbConnections, 0);

            return new DB($dbConn);
        }
    }
    public function removeForeignKey() {
        $tableObj = $this->getTable();
        $helper = $this->getCreateHelper();
        
        if ($tableObj->getForignKeysCount() == 0) {
            $helper->info('Selected table has no foreign keys.');

            return;
        }
        $fks = $tableObj->getForignKeys();
        $optionsArr = [];

        foreach ($fks as $fkObj) {
            $optionsArr[] = $fkObj->getKeyName();
        }
        $toRemove = $helper->select('Select the key that you would like to remove:', $optionsArr);
        $tableObj->removeReference($toRemove);

        $this->setClassInfo(get_class($tableObj));
        
        $helper->getWriter()->writeClass();
        $this->success('Table updated.');
    }
    public function updateColumn() {
        $tableObj = $this->getTable();
        $colsKeys = $tableObj->getColsKeys();
        $helper = $this->getCreateHelper();
        
        if (count($colsKeys) == 0) {
            $helper->info('The table has no columns. Nothing to update.');

            return;
        }
        $colToUpdate = $helper->select('Which column would you like to update?', $colsKeys);
        $col = $tableObj->removeColByKey($colToUpdate);

        $colKey = $helper->getInput('Enter a new name for column key:', $colToUpdate);
        $isAdded = $tableObj->addColumn($colKey, $col);

        if ($colKey != $colToUpdate) {
            $col->setName(str_replace('-', '_', $colKey));
        } else {
            $isAdded = true;
        }

        if (!$isAdded) {
            $helper->warning('The column was not added. Mostly, key name is invalid.');
        } else {
            $colDatatype = $helper->select('Select column data type:', $col->getSupportedTypes(), 0);
            $col->setDatatype($colDatatype);
            $this->setSize($col);
            $this->isPrimaryCheck($col);
            $this->addColComment($col);
        }
        
        $this->setClassInfo(get_class($tableObj));
        $this->getCreateHelper()->writeClass(false);
        $helper->success('Column updated.');
    }
    /**
     * 
     * @return Column
     */
    public function dropColumn() {
        $colsKeys = $this->getTable()->getColsKeys();

        if (count($colsKeys) == 0) {
            $this->info('The table has no columns. Nothing to drop.');

            return;
        }
        $colToDrop = $this->getCreateHelper()->select('Which column would you like to drop?', $colsKeys);
        $this->getTable()->removeColByKey($colToDrop);
        $class = get_class($this->getTable());
        $this->setClassInfo($class);
        $this->getCreateHelper()->writeClass(false);
        $this->success('Column dropped.');
        return $colToDrop;
    }
    private function setClassInfo($class) {
        $createHelper = $this->getCreateHelper();
        $split = explode('\\', $class);
        $cName = $split[count($split) - 1];
        $ns = implode('\\', array_slice($split, 0, count($split) - 1));

        $path = AutoLoader::getClassPath($cName, $ns)[0];
        $createHelper->setClassName($cName);
        $createHelper->setNamespace($ns);
        $createHelper->setPath($path);
    }
    private function getFkCols() {
        $colNumber = 1;
        $keys = $this->getTable()->getColsKeys();
        $fkCols = [];
        $helper = $this->getCreateHelper();
        
        do {
            $colKey = $helper->select('Select column #'.$colNumber.':', $keys);

            if (in_array($colKey, $fkCols)) {
                $helper->error('The column is already added.');
                continue;
            }
            $fkCols[] = $colKey;
            $colNumber++;
        } while ($helper->confirm('Would you like to add another column to the foreign key?', false));

        return $fkCols;
    }
}
