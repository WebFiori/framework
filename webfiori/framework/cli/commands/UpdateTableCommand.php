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
use Exception;
use webfiori\cli\CLICommand;
use webfiori\database\Table;
use webfiori\framework\cli\CLIUtils;
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
        
        $tableObj = CLIUtils::readTable($this);
        
        $create = new CreateTableObj($this);
        $create->getWriter()->setTable($tableObj);
        $tableHelper = new TableObjHelper($create, $tableObj);
        
        
        
        $whatToDo = $this->select('What operation whould you like to do with the table?', [
            'Add new column.',
            'Add foreign key.',
            'Update existing column.',
            'Drop column.',
            'Drop foreign key.'
        ]);

        if ($whatToDo == 'Add new column.') {
            $tableHelper->addColumn();
        } else if ($whatToDo == 'Drop column.') {
            $tableHelper->dropColumn();
        } else if ($whatToDo == 'Add foreign key.') {
            $tableHelper->addForeignKey();
        } else if ($whatToDo == 'Update existing column.') {
            $tableHelper->updateColumn();
        } else if ($whatToDo == 'Drop foreign key.') {
            $tableHelper->removeForeignKey();
        } else {
            $this->error('Option not implemented.');
        }
        return 0;
    }
    
}
