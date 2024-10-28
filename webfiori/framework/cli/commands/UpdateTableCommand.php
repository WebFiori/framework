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

use webfiori\cli\Argument;
use webfiori\cli\CLICommand;
use webfiori\framework\cli\CLIUtils;
use webfiori\framework\cli\helpers\CreateTableObj;
use webfiori\framework\cli\helpers\TableObjHelper;
/**
 * A command which is used to update the properties of database table class.
 *
 * @author Ibrahim
 */
class UpdateTableCommand extends CLICommand {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('update-table', [
            new Argument('--table', 'The namespace of the table class (including namespace).', true),
        ], 'Update a database table.');
    }
    /**
     * Execute the command.
     *
     * @return int The method will return 0 if the command succsessfully
     * executed.
     */
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
        }

        return 0;
    }
}
