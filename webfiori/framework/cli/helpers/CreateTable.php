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
use webfiori\database\Table;
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

        $dbConnections = array_keys(WebFioriApp::getAppConfig()->getDBConnections());

        if (count($dbConnections) != 0) {
            $dbConn = $this->_getCommand()->select('Select database connection:', $dbConnections, 0);
            $tableClassNameValidity = false;
            $tableClassName = $this->_getCommand()->getArgValue('--table');

            do {
                if (strlen($tableClassName) == 0) {
                    $tableClassName = $this->_getCommand()->getInput('Enter database table class name (include namespace):');
                }

                if (!class_exists($tableClassName)) {
                    $this->_getCommand()->error('Class not found.');
                    $tableClassName = '';
                    continue;
                }
                $tableObj = new $tableClassName();

                if (!$tableObj instanceof Table) {
                    $this->_getCommand()->error('The given class is not a child of the class "webfiori\database\Table".');
                    $tableClassName = '';
                    continue;
                }
                $tableClassNameValidity = true;
            } while (!$tableClassNameValidity);

            $db = new DB($dbConn);
            $db->addTable($tableObj);
            $db->table($tableObj->getNormalName())->createTable();

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
        } else {
            $this->_getCommand()->error('No database connections available. Add connections inside the class \'AppConfig\' or use the command "add".');
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
