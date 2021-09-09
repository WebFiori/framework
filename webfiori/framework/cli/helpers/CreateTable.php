<?php
/*
 * The MIT License
 *
 * Copyright 2020 Ibrahim, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace webfiori\framework\cli\helpers;

use Exception;
use webfiori\database\Table;
use webfiori\framework\DB;
use webfiori\framework\WebFioriApp;
use webfiori\framework\cli\commands\CreateCommand;

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
            $db->table($tableObj->getName())->createTable();

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
