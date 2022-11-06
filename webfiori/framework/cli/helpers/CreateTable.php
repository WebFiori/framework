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

use Exception;
use webfiori\cli\CLICommand;
use webfiori\framework\cli\CLIUtils;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\DB;
use webfiori\framework\WebFioriApp;

/**
 * A helper class for creating database table class.
 *
 * @author Ibrahim
 */
class CreateTable {
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

        
        $dbConn = CLIUtils::getConnectionName($this->_getCommand());
        
        if ($dbConn !== null) {
            $tableInst = CLIUtils::readTable($this->_getCommand());

            $db = new DB($dbConn);
            $db->addTable($tableInst);
            $db->table($tableInst->getNormalName())->createTable();

            $this->_getCommand()->prints('The following query will be executed on the database ');
            $this->_getCommand()->println($db->getConnectionInfo()->getDBName(),[
                'color' => 'yellow'
            ]);
            $this->_getCommand()->println($db->getLastQuery(), [
                'color' => 'light-blue'
            ]);

            if ($this->_getCommand()->confirm('Continue?', true)) {
                $this->_getCommand()->println('Creating your new table. Please wait a moment...');
                try {
                    $db->execute();
                    $this->_getCommand()->success('Database table created.');
                } catch (Exception $ex) {
                    $this->_getCommand()->error('Unable to create database table.');
                    $this->_getCommand()->error($ex->getMessage());
                }
            }
        }
    }
    /**
     * 
     * @return CreateCommand
     */
    private function _getCommand() {
        return $this->command;
    }
}
