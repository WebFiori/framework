<?php

/*
 * The MIT License
 *
 * Copyright 2021 Ibrahim, WebFiori Framework.
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

namespace webfiori\framework\cli;

use webfiori\framework\cli\CLICommand;
use webfiori\framework\DB;
use webfiori\framework\WebFioriApp;

/**
 * A command which can be used to execute SQL queries on 
 * specific database.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class RunSQLQueryCommand extends CLICommand {
    public function __construct() {
        parent::__construct('run-query', [
            'connection' => [
                'description' => 'Database connection that the query will '
                . 'be executed on.',
                'optional' => true,
            ],
            'schema' => [
                'description' => 'The namespace of a class that extends the class "webfiori\\framework\\DB" which represents '
                . 'database schema.',
                'optional' => true,
            ]
        ], 'Execute SQL query on specific database.');
    }
    public function exec() {
        $connName = $this->getArgValue('connection');
        $dbConnections = array_keys(WebFioriApp::getAppConfig()->getDBConnections());
        
        if (count($dbConnections) != 0) {
            if ($connName === null) {
                $connName = $connName = $this->select('Select database connection:', $connsArr, 0);
            } else if (!in_array($connName, $dbConnections)){
                $this->error('No connection with name "'.$connName.'" was found!');
                $connName = $connName = $this->select('Select database connection:', $connsArr, 0);
            }
        } else {
            $this->error('No database connections available. Add connections inside the class \'AppConfig\' or use the command "add".');
        }
    }
    
    private function getConnName($connsArr) {
        
    }

}
