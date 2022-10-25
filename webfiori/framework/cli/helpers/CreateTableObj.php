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

use webfiori\cli\CLICommand;
use webfiori\database\ConnectionInfo;
use webfiori\database\mssql\MSSQLTable;
use webfiori\database\mysql\MySQLTable;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\cli\helpers\CreateClassHelper;
use webfiori\framework\cli\helpers\TableObjHelper;
use webfiori\framework\writers\TableClassWriter;
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
    public function __construct(CLICommand $command) {
        parent::__construct($command, new TableClassWriter());
        
        $dbType = $this->select('Database type:', ConnectionInfo::SUPPORTED_DATABASES);

        $this->setClassInfo(APP_DIR_NAME.'\\database', 'Table');


        if ($dbType == 'mysql') {
            $tempTable = new MySQLTable();
        } else if ($dbType == 'mssql') {
            $tempTable = new MSSQLTable();
        }
        $this->getWriter()->setTable($tempTable);
        $tableHelper = new TableObjHelper($this, $tempTable);
        $tableHelper->setTableName();
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
