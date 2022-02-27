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
use webfiori\framework\cli\writers\TableClassWriter;
use webfiori\framework\cli\helpers\CreateClassHelper;
/**
 * A helper class for creating database tables classes.
 *
 * @author Ibrahim
 */
class CreateTableObj extends CreateClassHelper {
    /**
     * Creates new instance of the class.
     * 
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        parent::__construct($command, new TableClassWriter());
        
        $dbType = $this->select('Database type:', ConnectionInfo::SUPPORTED_DATABASES);

        $this->setClassInfo(APP_DIR_NAME.'\\database', 'Table');


        if ($dbType == 'mysql') {
            $tempTable = new MySQLTable();
        } else if ($dbType == 'mssql') {
            $tempTable = new MSSQLTable();
        }
        $this->getWriter()->setTable($tempTable);
        $this->_setTableName($tempTable);
        $this->_setTableComment($tempTable);
        $this->println('Now you have to add columns to the table.');

        do {
            $colKey = $this->getInput('Enter a name for column key:');

            if ($tempTable->hasColumnWithKey($colKey)) {
                $this->warning("The table already has a key with name '$colKey'.");
                continue;
            }

            if ($tempTable instanceof MySQLTable) {
                $col = new MySQLColumn();
            } else {
                $col = new MSSQLColumn();
            }
            $col->setName(str_replace('-', '_', str_replace(' ', '_', $colKey)));
            $colDatatype = $this->select('Select column data type:', $col->getSupportedTypes(), 0);
            $col->setDatatype($colDatatype);
            $isAdded = $tempTable->addColumn($colKey, $col);

            if (!$isAdded) {
                $this->warning('The column was not added. Mostly, key name is invalid. Try again.');
            } else {
                $colObj = $tempTable->getColByKey($colKey);
                $this->_setSize($colObj);
                $this->_isPrimaryCheck($colObj);
                $this->_addColComment($colObj);
            }
            $this->getWriter()->writeClass();
        } while ($this->confirm('Would you like to add another column?', false));

        if ($this->confirm('Would you like to add foreign keys to the table?', false)) {
            $classInfo['fk-info'] = $this->_addFks($tempTable);
        }

        if ($this->confirm('Would you like to create an entity class that maps to the database table?', false)) {
            $entityInfo = $this->getClassInfo(APP_DIR_NAME.'\\entity');
            $entityInfo['implement-jsoni'] = $this->confirm('Would you like from your entity class to implement the interface JsonI?', true);
            $classInfo['entity-info'] = $entityInfo;
            
            if ($this->confirm('Would you like to add extra attributes to the entity?', false)) {
                $addExtra = true;

                while ($addExtra) {
                    
                    if ($tempTable->getEntityMapper()->addAttribute($this->getInput('Enter attribute name:'))) {
                        $this->success('Attribute successfully added.');
                    } else {
                        $this->warning('Unable to add attribute.');
                    }
                    $addExtra = $this->confirm('Would you like to add another attribute?', false);
                }
            }
        }

        if (strlen($classInfo['namespace']) == 0) {
            $classInfo['namespace'] = APP_DIR_NAME.'\database';
            $this->warning('The table class will be added to the namespace "'.$classInfo['namespace'].'" since no namespace was provided.');
        }
        
        if (isset($classInfo['entity-info']) && strlen($classInfo['entity-info']['namespace']) == 0) {
            $classInfo['entity-info']['namespace'] = APP_DIR_NAME.'\database';
            $this->warning('The entity class will be added to the namespace "'.$classInfo['entity-info']['namespace'].'" since no namespace was provided.');
        }
        $this->writeClass();
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
     */
    private function _addFks($tableObj) {
        $refTable = null;
        $fksNs = [];

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
                    $this->getWriter()->writeClass();
                    $this->success('Foreign key added.');
                    $fksNs[$fkName] = $refTableName;
                } catch (Exception $ex) {
                    $this->error($ex->getMessage());
                } catch (Error $ex) {
                    $this->error($ex->getMessage());
                }
            } else {
                $this->error('The given class is not an instance of the class \'webfiori\\database\\Table\'.');
            }
        } while ($this->confirm('Would you like to add another foreign key?', false));

        return $fksNs;
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
        } else if ($colObj->getDatatype() == 'int' && $colObj instanceof MySQLColumn) {
            $colObj->setIsAutoInc($this->confirm('Is this column auto increment?', false));
        }
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
                $dataSize = $this->getInput('Enter column size:');

                if ($colObj instanceof MySQLColumn && $colObj->getDatatype() == 'varchar' && $dataSize > 21845) {
                    $this->warning('The data type "varchar" has a maximum size of 21845. The '
                            .'data type of the column will be changed to "mediumtext" if you continue.');

                    if (!$this->confirm('Would you like to change data type?', false)) {
                        $valid = true;
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
     * @param Table $tempTable
     */
    private function _setTableComment($tempTable) {
        $incComment = $this->confirm('Would you like to add your comment about the table?', false);

        if ($incComment) {
            $tableComment = $this->getInput('Enter your comment:');

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
            $tableName = $this->getInput('Enter database table name:');
            $invalidTableName = !$tableObj->setName($tableName);

            if ($invalidTableName) {
                $this->error('The given name is invalid.');
            }
        } while ($invalidTableName);
    }
}
