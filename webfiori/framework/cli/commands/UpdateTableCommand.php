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
namespace webfiori\framework\cli\commands;

use Error;
use ErrorException;
use Exception;
use Throwable;
use webfiori\cli\CLICommand;
use webfiori\database\Table;
use webfiori\framework\cli\helpers\CreateTableObj;
use webfiori\framework\cli\helpers\TableObjHelper;
use webfiori\framework\writers\TableClassWriter;
/**
 * Description of UpdateTableCommand
 *
 * @author Eng.Ibrahim
 */
class UpdateTableCommand extends CLICommand {
    public function __construct() {
        parent::__construct('update-table', [
            '--table' => [
                'description' => 'The namespace of the table class (including namespace).',
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
        $writer = new TableClassWriter($tableObj, $arr);
        $writer->writeClass();
        $db = $this->getDb('Would you like to run a query to '
                .'update the table in the database?');

        if ($db !== null) {
            $db->addTable($tableObj);
            $db->table($tableObj->getNormalName())->addForeignKey($fkName);
            $this->runQuery($db);
        }

        $this->success('Table updated.');
    }
    public function exec() : int {
        $tableClass = $this->getArgValue('--table');
        
        if ($tableClass === null) {
            $tableClass = $this->readInstance('Enter the name of table class (including namespace):', 'Given class name is invalid!');
        }

        try {
            $tableObj = new $tableClass();

            if (!($tableObj instanceof Table)) {
                $this->error('Given class is not an instance of "webfiori\database\Table".');

                return -1;
            }
        } catch (Throwable $ex) {
            $message = $ex->getMessage();

            if ($message == "Class \"'$tableClass'\" not found") {
                $this->error($ex->getMessage());

                return -1;
            }
            throw new ErrorException($ex->getMessage(), $ex->getCode(), E_ERROR, $ex->getFile(), $ex->getLine(), $ex->getPrevious());
        }
        
        $update = new TableObjHelper($this, $tableObj);
        
        $whatToDo = $this->select('What operation whould you like to do with the table?', [
            'Add new column.',
            'Add foreign key.',
            'Update existing column.',
            'Drop column.',
            'Drop foreign key.'
        ]);

        if ($whatToDo == 'Add new column.') {
            $update->addColumn();
        } else if ($whatToDo == 'Drop column.') {
            $update->dropColumn();
        } else if ($whatToDo == 'Add foreign key.') {
            $update->addForeignKey();
        } else if ($whatToDo == 'Update existing column.') {
            $update->updateColumn();
        } else if ($whatToDo == 'Drop foreign key.') {
            $update->removeForeignKey();
        } else {
            $this->error('Option not implemented.');
        }
        return 0;
    }
    
}
