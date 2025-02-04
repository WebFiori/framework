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

use Throwable;
use webfiori\cli\CLICommand;
use webfiori\cli\InputValidator;
use webfiori\database\Column;
use webfiori\database\mssql\MSSQLColumn;
use webfiori\database\mysql\MySQLColumn;
use webfiori\database\mysql\MySQLTable;
use webfiori\database\Table;
use webfiori\framework\App;
use webfiori\framework\cli\commands\UpdateTableCommand;
use webfiori\framework\DB;

/**
 * A CLI class helper which has methods to help in creating and
 * modifying table classes.
 *
 * @author Ibrahim BinAlshikh
 */
class TableObjHelper {
    private $createHelper;
    private $table;
    /**
     * Creates new instance of the class.
     *
     * @param CLICommand $c
     *
     * @param Table $t An instance at which the class will use to create or
     * modify as class.
     */
    public function __construct(CreateTableObj $c, Table $t) {
        $this->createHelper = $c;
        $this->table = $t;
    }
    /**
     * Returns the objects at which the class is using to perform modifications.
     *
     * @return Table
     */
    public function &getTable() : Table {
        return $this->table;
    }
    /**
     * Adds new column to associated table.
     *
     * The method will prompt the user to specify all information of the column
     * including its name, data type, size and so on.
     */
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

            if ($helper->getCommand() instanceof UpdateTableCommand) {
                $this->copyCheck();
            } else {
                $this->getCreateHelper()->writeClass(false);
            }
            $helper->success('Column added.');
        }
    }
    /**
     * Prompt the user to add multiple columns.
     *
     * First, the method will add one column, after that, it will ask if extra
     * column should be added or not. If yes, it will ask for new column information.
     * If not, the loop will stop.
     */
    public function addColumns() {
        do {
            $this->addColumn();
        } while ($this->getCreateHelper()->confirm('Would you like to add another column?', false));
    }
    public function addForeignKey() {
        $refTable = null;
        $helper = $this->getCreateHelper();

        $refTableName = $helper->getInput('Enter the name of the referenced table class (with namespace):');
        try {
            $refTable = new $refTableName();
        } catch (Throwable $ex) {
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

            if ($helper->getCommand() instanceof UpdateTableCommand) {
                $this->copyCheck();
            } else {
                $helper->getWriter()->writeClass(false);
            }
            $helper->success('Foreign key added.');
        } catch (Throwable $ex) {
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
            $dbConnections = array_keys(App::getConfig()->getDBConnections());

            if (count($dbConnections) == 0) {
                $this->error('No database connections available. Add connections inside the class \'AppConfig\' or use the command "add".');

                return null;
            }
            $dbConn = $this->select('Select database connection:', $dbConnections, 0);

            return new DB($dbConn);
        }
    }
    public function copyCheck() {
        $helper = $this->getCreateHelper();

        if ($helper->confirm('Would you like to update same class or create a copy with the update?', false)) {
            $info = $helper->getClassInfo(APP_DIR.'\\database', 'Table');
            $helper->setClassName($info['name']);
            $helper->setNamespace($info['namespace']);
            $helper->setPath($info['path']);
            $helper->getWriter()->writeClass(false);
        } else {
            $helper->getWriter()->writeClass(false);
        }
    }
    /**
     * Creates entity class based on associated table object.
     */
    public function createEntity() {
        $helper = $this->getCreateHelper();
        $entityInfo = $helper->getClassInfo(APP_DIR.'\\entity');
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
        $this->copyCheck();
        $this->getCreateHelper()->success('Column dropped.');

        return $colToDrop;
    }
    /**
     *
     * @return CreateTableObj
     */
    public function getCreateHelper() : CreateTableObj {
        return $this->createHelper;
    }
    /**
     * Extract and return the name of table class based on associated table object.
     *
     * @return string The name of table class based on associated table object.
     */
    public function getTableClassName() :string {
        $clazz = get_class($this->getTable());
        $split = explode('\\', $clazz);

        if (count($split) > 1) {
            return $split[count($split) - 1];
        }

        return $split[0];
    }
    /**
     * Removes a foreign key from associated table object.
     *
     * The method will simply ask the user which key he would like to remove.
     *
     */
    public function removeFk() {
        $tableObj = $this->getTable();
        $helper = $this->getCreateHelper();

        if ($tableObj->getForeignKeysCount() == 0) {
            $helper->info('Selected table has no foreign keys.');

            return;
        }
        $fks = $tableObj->getForeignKeys();
        $optionsArr = [];

        foreach ($fks as $fkObj) {
            $optionsArr[] = $fkObj->getKeyName();
        }
        $toRemove = $helper->select('Select the key that you would like to remove:', $optionsArr);
        $tableObj->removeReference($toRemove);

        $this->getCreateHelper()->writeClass(false);
        $helper->success('Table updated.');
    }
    public function removeForeignKey() {
        $tableObj = $this->getTable();
        $helper = $this->getCreateHelper();

        if ($tableObj->getForeignKeysCount() == 0) {
            $helper->info('Selected table has no foreign keys.');

            return;
        }
        $fks = $tableObj->getForeignKeys();
        $optionsArr = [];

        foreach ($fks as $fkObj) {
            $optionsArr[] = $fkObj->getKeyName();
        }
        $toRemove = $helper->select('Select the key that you would like to remove:', $optionsArr);
        $tableObj->removeReference($toRemove);

        $this->setClassInfo(get_class($tableObj));

        $this->copyCheck();
        $helper->success('Table updated.');
    }
    /**
     * Sets a comment for associated table.
     *
     * The method will prompt the user for optional comment.
     * If empty string provided, the comment will not be set.
     */
    public function setTableComment() {
        $helper = $this->getCreateHelper();
        $tableComment = $helper->getInput('Enter your optional comment about the table:');

        if (strlen($tableComment) != 0) {
            $this->getTable()->setComment($tableComment);
        }
    }
    /**
     * Sets the name of the table in the database.
     *
     * The method will prompt the user to set the name of the table as it will
     * appear in the database. This name may not be same as class name
     * of the table.
     *
     * @param string $defaultName A string to set as default table name in case
     * of hitting 'enter' without providing a value.
     */
    public function setTableName(?string $defaultName = null) {
        $invalidTableName = true;
        $helper = $this->getCreateHelper();

        do {
            $tableName = $helper->getInput('Enter database table name:', $defaultName);
            $invalidTableName = !$this->getTable()->setName($tableName);

            if ($invalidTableName) {
                $helper->error('The given name is invalid.');
            }
        } while ($invalidTableName);
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
        $this->copyCheck();
        $helper->success('Column updated.');
    }
    /**
     * Prompt the user to set an optional comment for table column.
     *
     * @param Column $colObj The object that the comment will be associated with.
     */
    private function addColComment(Column $colObj) {
        $comment = $this->getCreateHelper()->getInput('Enter your optional comment about the column:');

        if (strlen($comment) != 0) {
            $colObj->setComment($comment);
        }
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
     * @param Column $colObj
     */
    private function isPrimaryCheck(Column $colObj) {
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
    private function setClassInfo($class) {
        $createHelper = $this->getCreateHelper();
        $split = explode('\\', $class);
        $cName = $split[count($split) - 1];
        $ns = implode('\\', array_slice($split, 0, count($split) - 1));

        $path = ROOT_PATH.DS.$ns.DS.$cName.'.php';
        $createHelper->setClassName($cName);
        $createHelper->setNamespace($ns);
        $createHelper->setPath(substr($path, 0, strlen($path) - strlen($cName.'.php')));
    }
    /**
     *
     * @param Column $colObj
     */
    private function setDefaultValue(Column $colObj) {
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
     * @param Column $colObj
     */
    private function setScale(Column $colObj) {
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
    /**
     *
     * @param Column $colObj
     */
    private function setSize(Column $colObj) {
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
}
