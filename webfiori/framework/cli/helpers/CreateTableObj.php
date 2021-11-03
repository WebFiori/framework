<?php
namespace webfiori\framework\cli\helpers;

use Error;
use Exception;
use webfiori\database\ConnectionInfo;
use webfiori\database\mssql\MSSQLColumn;
use webfiori\database\mssql\MSSQLTable;
use webfiori\database\mysql\MySQLColumn;
use webfiori\database\mysql\MySQLTable;
use webfiori\database\Table;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\writers\QueryClassWriter;
/**
 * A helper class for creating database tables classes.
 *
 * @author Ibrahim
 */
class CreateTableObj {
    /**
     *
     * @var CLICommand 
     */
    private $command;
    /**
     * Creates new instance of the class.
     * 
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        $this->command = $command;
        $dbType = $this->_getCommand()->select('Database type:', ConnectionInfo::SUPPORTED_DATABASES);

        $classInfo = $this->_getCommand()->getClassInfo(APP_DIR_NAME.'\\database');


        if ($dbType == 'mysql') {
            $tempTable = new MySQLTable();
        } else if ($dbType == 'mssql') {
            $tempTable = new MSSQLTable();
        }
        $this->_setTableName($tempTable);
        $this->_setTableComment($tempTable);
        $this->_getCommand()->println('Now you have to add columns to the table.');

        do {
            $colKey = $this->_getCommand()->getInput('Enter a name for column key:');

            if ($tempTable->hasColumnWithKey($colKey)) {
                $this->_getCommand()->warning("The table already has a key with name '$colKey'.");
                continue;
            }

            if ($tempTable instanceof MySQLTable) {
                $col = new MySQLColumn();
            } else {
                $col = new MSSQLColumn();
            }
            $col->setName(str_replace('-', '_', str_replace(' ', '_', $colKey)));
            $colDatatype = $this->_getCommand()->select('Select column data type:', $col->getSupportedTypes(), 0);
            $col->setDatatype($colDatatype);
            $isAdded = $tempTable->addColumn($colKey, $col);

            if (!$isAdded) {
                $this->_getCommand()->warning('The column was not added. Mostly, key name is invalid. Try again.');
            } else {
                $colObj = $tempTable->getColByKey($colKey);
                $this->_setSize($colObj);
                $this->_isPrimaryCheck($colObj);
                $this->_addColComment($colObj);
            }
        } while ($this->_getCommand()->confirm('Would you like to add another column?', false));

        if ($this->_getCommand()->confirm('Would you like to add foreign keys to the table?', false)) {
            $classInfo['fk-info'] = $this->_addFks($tempTable);
        }

        if ($this->_getCommand()->confirm('Would you like to create an entity class that maps to the database table?', false)) {
            $entityInfo = $this->_getCommand()->getClassInfo(APP_DIR_NAME.'\\entity');
            $entityInfo['implement-jsoni'] = $this->_getCommand()->confirm('Would you like from your entity class to implement the interface JsonI?', true);
            $classInfo['entity-info'] = $entityInfo;
        }

        if (strlen($classInfo['namespace']) == 0) {
            $classInfo['namespace'] = APP_DIR_NAME.'\database';
            $this->_getCommand()->warning('The table class will be added to the namespace "'.$classInfo['namespace'].'" since no namespace was provided.');
        }

        if (isset($classInfo['entity-info']) && strlen($classInfo['entity-info']['namespace']) == 0) {
            $classInfo['entity-info']['namespace'] = APP_DIR_NAME.'\database';
            $this->_getCommand()->warning('The entity class will be added to the namespace "'.$classInfo['entity-info']['namespace'].'" since no namespace was provided.');
        }
        $writer = new QueryClassWriter($tempTable, $classInfo);
        $writer->writeClass();
        $this->_getCommand()->success('New class created.');
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _addColComment($colObj) {
        if ($this->_getCommand()->confirm('Would you like to add your own comment about the column?', false)) {
            $comment = $this->_getCommand()->getInput('Enter your comment:');

            if (strlen($comment) != 0) {
                $colObj->setComment($comment);
            }
        }
        $this->_getCommand()->success('Column added.');
    }
    /**
     * 
     * @param Table $tableObj
     */
    private function _addFks($tableObj) {
        $refTable = null;
        $fksNs = [];

        do {
            $refTableName = $this->_getCommand()->getInput('Enter the name of the referenced table class (with namespace):');
            try {
                $refTable = new $refTableName();
            } catch (Error $ex) {
                $this->_getCommand()->error($ex->getMessage());
                continue;
            } catch (Exception $ex) {
                $this->_getCommand()->error($ex->getMessage());
                continue;
            }

            if ($refTable instanceof Table) {
                $fkName = $this->_getCommand()->getInput('Enter a name for the foreign key:', null, function ($val)
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
                    $fkColsArr[$colKey] = $this->_getCommand()->select('Select the column that will be referenced by the column \''.$colKey.'\':', $refTable->getColsKeys());
                }
                $onUpdate = $this->_getCommand()->select('Choose on update condition:', [
                    'cascade', 'restrict', 'set null', 'set default', 'no action'
                ], 1);
                $onDelete = $this->_getCommand()->select('Choose on delete condition:', [
                    'cascade', 'restrict', 'set null', 'set default', 'no action'
                ], 1);

                try {
                    $tableObj->addReference($refTable, $fkColsArr, $fkName, $onUpdate, $onDelete);
                    $this->_getCommand()->success('Foreign key added.');
                    $fksNs[$fkName] = $refTableName;
                } catch (Exception $ex) {
                    $this->_getCommand()->error($ex->getMessage());
                } catch (Error $ex) {
                    $this->_getCommand()->error($ex->getMessage());
                }
            } else {
                $this->_getCommand()->error('The given class is not an instance of the class \'webfiori\\database\\Table\'.');
            }
        } while ($this->_getCommand()->confirm('Would you like to add another foreign key?', false));

        return $fksNs;
    }
    /**
     * 
     * @return CreateCommand
     */
    private function _getCommand() {
        return $this->command;
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
            $colKey = $this->_getCommand()->select('Select column #'.$colNumber.':', $keys);

            if (!in_array($colKey, $fkCols)) {
                $fkCols[] = $colKey;
                $colNumber++;
            } else {
                $this->_getCommand()->error('The column is already added.');
            }
        } while ($this->_getCommand()->confirm('Would you like to add another column to the foreign key?', false));

        return $fkCols;
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _isPrimaryCheck($colObj) {
        $colObj->setIsPrimary($this->_getCommand()->confirm('Is this column primary?', false));
        $type = $colObj->getDatatype();

        if (!$colObj->isPrimary()) {
            if (!($type == 'bool' || $type == 'boolean')) {
                $colObj->setIsUnique($this->_getCommand()->confirm('Is this column unique?', false));
            }
            $this->_setDefaultValue($colObj);
            $colObj->setIsNull($this->_getCommand()->confirm('Can this column have null values?', false));
        } else if ($colObj->getDatatype() == 'int' && $colObj instanceof MySQLColumn) {
            $colObj->setIsAutoInc($this->_getCommand()->confirm('Is this column auto increment?', false));
        }
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _setDefaultValue($colObj) {
        if ($colObj->getDatatype() == 'bool' || $colObj->getDatatype() == 'boolean') {
            $defaultVal = trim($this->_getCommand()->getInput('Enter default value (true or false) (Hit "Enter" to skip):', ''));

            if ($defaultVal == 'true') {
                $colObj->setDefault(true);
            } else if ($defaultVal == 'false') {
                $colObj->setDefault(false);
            }
        } else {
            $defaultVal = trim($this->_getCommand()->getInput('Enter default value (Hit "Enter" to skip):', ''));

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
                $scale = $this->_getCommand()->getInput('Enter the scale (number of numbers to the right of decimal point):');
                $validScale = $colObj->setScale($scale);

                if (!$validScale) {
                    $this->_getCommand()->error('Invalid scale value.');
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
                $dataSize = $this->_getCommand()->getInput('Enter column size:');

                if ($colObj instanceof MySQLColumn && $colObj->getDatatype() == 'varchar' && $dataSize > 21845) {
                    $this->_getCommand()->warning('The data type "varchar" has a maximum size of 21845. The '
                            .'data type of the column will be changed to "mediumtext" if you continue.');

                    if (!$this->_getCommand()->confirm('Would you like to change data type?', false)) {
                        $valid = true;
                        continue;
                    }
                }

                if ($colDataType == 'int' && $dataSize > 11) {
                    $this->_getCommand()->warning('Size is set to 11 since this is the maximum size for "int" type.');
                }
                $valid = $colObj->setSize($dataSize);

                if (!$valid) {
                    $this->_getCommand()->error('Invalid size is given.');
                } else {
                    $this->_setScale($colObj);
                }
            } while (!$valid);
        }
    }
    /**
     * 
     * @param Table $tempTable
     */
    private function _setTableComment($tempTable) {
        $incComment = $this->_getCommand()->confirm('Would you like to add your comment about the table?', false);

        if ($incComment) {
            $tableComment = $this->_getCommand()->getInput('Enter your comment:');

            if (strlen($tableComment) != 0) {
                $tempTable->setComment($tableComment);
            }
        }
    }
    /**
     * 
     * @param Table $tableObj
     */
    private function _setTableName($tableObj) {
        $invalidTableName = true;

        do {
            $tableName = $this->_getCommand()->getInput('Enter database table name:');
            $invalidTableName = !$tableObj->setName($tableName);

            if ($invalidTableName) {
                $this->_getCommand()->error('The given name is invalid.');
            }
        } while ($invalidTableName);
    }
}
