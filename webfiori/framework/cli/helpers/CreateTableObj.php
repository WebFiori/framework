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

use WebFiori\Cli\Command;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\MSSql\MSSQLTable;
use WebFiori\Database\MySql\MySQLTable;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\writers\TableClassWriter;
use WebFiori\Json\CaseConverter;
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
     *
     * @param Table $t An optional table instance to associate the writer with.
     */
    public function __construct(Command $command) {
        parent::__construct($command, new TableClassWriter());
    }

    public function readClassInfo() {
        $databaseType = $this->select('Database type:', ConnectionInfo::SUPPORTED_DATABASES);

        if ($databaseType == 'mysql') {
            $tempTable = new MySQLTable();
        } else if ($databaseType == 'mssql') {
            $tempTable = new MSSQLTable();
        }
        $this->getWriter()->setTable($tempTable);
        $this->setClassInfo(APP_DIR.'\\database', 'Table');

        $tableHelper = new TableObjHelper($this, $tempTable);
        $tableHelper->setTableName(CaseConverter::toSnackCase($this->getWriter()->getName()));
        $tableHelper->setTableComment();

        $this->println('Now you have to add columns to the table.');
        $tableHelper->addColumns();



        if ($this->confirm('Would you like to add foreign keys to the table?', false)) {
            $tableHelper->addForeignKeys();
        }

        $withEntity = false;

        if ($this->confirm('Would you like to create an entity class that maps to the database table?', false)) {
            $tableHelper->createEntity();
            $withEntity = true;
        }

        $this->writeClass();

        if ($withEntity) {
            $this->info('Entity class was created at "'.$this->getWriter()->getEntityPath().'".');
        }
    }
}
