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
namespace webfiori\framework\cli\commands;

use webfiori\database\DatabaseException;
use webfiori\database\Table;
use webfiori\framework\cli\CLICommand;
use webfiori\framework\DB;
use webfiori\framework\WebFioriApp;
use webfiori\framework\File;
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
            '--connection' => [
                'description' => 'Database connection that the query will '
                .'be executed on.',
                'optional' => true,
            ],
            '--schema' => [
                'description' => 'The namespace of a class that extends the class "webfiori\\framework\\DB" which represents '
                .'database schema.',
                'optional' => true,
            ]
        ], 'Execute SQL query on specific database.');
    }
    /**
     * Execute the command.
     * 
     * @return int 0 in case of success. Other value if failed.
     */
    public function exec() {
        $dbConnections = array_keys(WebFioriApp::getAppConfig()->getDBConnections());
        $schema = $this->getArgValue('--schema');

        if (count($dbConnections) != 0) {
            if ($schema !== null && class_exists($schema)) {
                $schemaInst = new $schema();

                if ($schemaInst instanceof DB) {
                    return $this->queryOnSchema($schemaInst);
                } else {
                    $this->error('Given class is not an instance of "webfiori\\framework\\DB"!');

                    return -1;
                }
            } else {
                $connName = $this->getArgValue('--connection');

                if ($connName === null) {
                    $connName = $this->select('Select database connection:', $dbConnections, 0);
                    $schema = new DB($connName);

                    return $this->generalQuery($schema);
                } else {
                    if (!in_array($connName, $dbConnections)) {
                        $this->error('No connection with name "'.$connName.'" was found!');

                        return -1;
                    }
                }
            }
        } else {
            $this->error('No database connections available. Add connections inside the class \'AppConfig\' or use the command "add".');

            return -1;
        }
    }
    private function colQuery(&$schema, $selectedQuery, $colsKeys, $tableObj) {
        $selectedCol = $this->select('Select the column:', $colsKeys);

        if ($selectedQuery == 'Add Column.') {
            $schema->table($tableObj->getName())->addCol($selectedCol);
        } else if ($selectedQuery == 'Modify Column.') {
            $schema->table($tableObj->getName())->modifyCol($selectedCol);
        } else if ($selectedQuery == 'Drop Column.') {
            $schema->table($tableObj->getName())->dropCol($selectedCol);
        }
    }
    private function confirmExecute($schema) {
        $this->println('The following query will be executed on the database:');
        $this->println($schema->getLastQuery(), [
            'color' => 'blue'
        ]);

        if ($this->confirm('Continue?')) {
            $this->info('Executing the query...');
            try {
                $schema->execute();
                $this->success('Query executed without errors.');

                return 0;
            } catch (DatabaseException $ex) {
                $this->error($ex->getMessage());

                return $ex->getCode();
            }
        } else {
            $this->info('Nothing to execute.');

            return 0;
        }
    }
    private function fkQuery($schema, $selectedQuery, $tableObj) {
        $keys = $tableObj->getForignKeys();
        $keysNamesArr = [];

        foreach ($keys as $fkObj) {
            $keysNamesArr[] = $fkObj->getName();
        }
        $fkName = $this->select('Select the forign key:', $keysNamesArr);

        if ($selectedQuery == 'Add Forign Key.') {
            $schema->table($tableObj->getName())->addForeignKey($fkName);
        } else {
            $schema->table($tableObj->getName())->dropForeignKey($fkName);
        }
    }
    private function generalQuery(DB $schema) {
        $options = [
            'Run general query.',
            'Run query on table instance.',
            'Run query from file.'
        ];
        $selected = $this->select('What type of query you would like to run?', $options);

        if ($selected == 'Run general query.') {
            $query = $this->getInput('Please provide us with the query:');
            $this->println('Executing the query...');
            $schema->setQuery($query);
            try {
                $schema->execute();
            } catch (DatabaseException $ex) {
                $this->error('The query finished execution with an error: '.$ex->getMessage());

                return $ex->getCode();
            }
            $this->success('Query executed without errors.');

            return 0;
        } else if ($selected == 'Run query on table instance.') {
            $tableClassName = '';
            $tableClassNameValidity = false;

            do {
                if (strlen($tableClassName) == 0) {
                    $tableClassName = $this->getInput('Enter database table class name (include namespace):');
                }

                if (!class_exists($tableClassName)) {
                    $this->error('Class not found.');
                    $tableClassName = '';
                    continue;
                }
                $tableObj = new $tableClassName();

                if (!$tableObj instanceof Table) {
                    $this->error('The given class is not a child of the class "webfiori\database\Table".');
                    $tableClassName = '';
                    continue;
                }
                $tableClassNameValidity = true;
            } while (!$tableClassNameValidity);
            $schema->addTable($tableObj);
            $this->tableQuery($schema, $tableObj);

            return $this->confirmExecute($schema);
        } else if ($selected == 'Run query from file.') {
            $filePath = '';
            $file = null;
            while (!File::isFileExist($filePath)) {
                $filePath = $this->getInput('File path:');
                if (File::isFileExist($filePath)) {
                    $file = new File($filePath);
                    $file->read();
                    if ($file->getFileMIMEType() == 'application/sql') {
                        break;
                    } else {
                        $this->error('Provided file is not SQL file!');
                    }
                } else {
                    $this->error('No such file!');
                }
            }
            $this->println('Executing the query...');
            $schema->setQuery($file->getRawData());
            try {
                $schema->execute();
            } catch (DatabaseException $ex) {
                $this->error('The query finished execution with an error: '.$ex->getMessage());

                return $ex->getCode();
            }
            $this->success('Query executed without errors.');

            return 0;
        }
    }
    private function queryOnSchema(DB $schema) {
        $options = [
            'Create Database.',
            'Run Query on Specific Table.'
        ];
        $selected = $this->select('Select an option:', $options);

        if ($selected == 'Create Database.') {
            $schema->createTables();
        } else {
            $selectedTable = $this->select('Select database table:', array_keys($schema->getTables()));
            $this->tableQuery($schema, $schema->getTable($selectedTable));
        }

        return $this->confirmExecute($schema);
    }
    /**
     * 
     * @param type $schema
     * @param Table $tableObj
     */
    private function tableQuery($schema, $tableObj) {
        $queryTypes = [
            'Create database table.',
            'Drop database table.',
            'Add Column.',
            'Modify Column.',
            'Drop Column.'
        ];

        if ($tableObj->getForignKeysCount() != 0) {
            $queryTypes[] = 'Add Forign Key.';
            $queryTypes[] = 'Drop Forign Key.';
        }
        $selectedQuery = $this->select('Select query type:', $queryTypes);

        if ($selectedQuery == 'Add Column.' || $selectedQuery == 'Modify Column.' || $selectedQuery == 'Drop Column.') {
            $this->colQuery($schema, $selectedQuery, $tableObj->getColsKeys(), $tableObj);
        } else if ($selectedQuery == 'Add Forign Key.' || $selectedQuery == 'Drop Forign Key.') {
            $this->fkQuery($schema, $selectedQuery, $tableObj);
        } else if ($selectedQuery == 'Create database table.') {
            $schema->table($tableObj->getNormalName())->createTable();
        } else if ($selectedQuery == 'Drop database table.') {
            $schema->table($tableObj->getNormalName())->drop();
        }       
    }
}
