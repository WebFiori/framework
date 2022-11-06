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

use webfiori\database\DatabaseException;
use webfiori\database\Table;
use webfiori\cli\CLICommand;
use webfiori\framework\DB;
use webfiori\framework\WebFioriApp;
use webfiori\file\File;
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
            ],
            '--file' => [
                'description' => 'The path to SQL file that holds SQL query.',
                'optional' => true
            ]
        ], 'Execute SQL query on specific database.');
    }
    /**
     * Execute the command.
     * 
     * @return int 0 in case of success. Other value if failed.
     */
    public function exec() : int {
        $dbConnections = array_keys(WebFioriApp::getAppConfig()->getDBConnections());
        $schema = $this->getArgValue('--schema');

        if (count($dbConnections) != 0) {
            if ($schema !== null) {
                if (class_exists($schema)) {
                    return $this->_schemaBased($schema);
                } else {
                    $this->warning('Schema not found: '.$schema);

                    return $this->_connectionBased($dbConnections);
                }
            } else {
                return $this->_connectionBased($dbConnections);
            }
        } else {
            $this->error('No database connections available. Add connections inside the class \'AppConfig\' or use the command "add".');

            return -1;
        }
    }
    private function _connectionBased($dbConnections) {
        $connName = $this->getArgValue('--connection');
        $file = $this->getArgValue('--file');
        if ($connName === null) {
            $connName = $this->select('Select database connection:', $dbConnections, 0);
            $schema = new DB($connName);
            
            if ($file !== null) {
                $fileObj = new File($file);
                if ($fileObj->isExist()) {
                    $fileObj->read();
                    if ($fileObj->getMIME() == 'application/sql') {
                        return $this->runFileQuery($schema, $fileObj);
                    } else {
                        $this->error('Provided file is not SQL file!');
                        return -1;
                    }
                } else {
                    $this->error('No such file: '.$fileObj->getAbsolutePath());
                    return -1;
                }
            }
            

            return $this->generalQuery($schema);
        } else if (!in_array($connName, $dbConnections)) {
            $this->error('No connection with name "'.$connName.'" was found!');

            return -1;
        }
    }
    private function _schemaBased($schema) {
        $schemaInst = new $schema();

        if ($schemaInst instanceof DB) {
            return $this->queryOnSchema($schemaInst);
        } else {
            $this->error('Given class is not an instance of "webfiori\\framework\\DB"!');

            return -1;
        }
    }
    /**
     * 
     * @param type $schema
     * @param type $selectedQuery
     * @param type $colsKeys
     * @param Table $tableObj
     */
    private function colQuery(&$schema, $selectedQuery, $colsKeys, $tableObj) {
        $selectedCol = $this->select('Select the column:', $colsKeys);

        if ($selectedQuery == 'Add Column.') {
            $schema->table($tableObj->getNormalName())->addCol($selectedCol);
        } else if ($selectedQuery == 'Modify Column.') {
            $schema->table($tableObj->getNormalName())->modifyCol($selectedCol);
        } else if ($selectedQuery == 'Drop Column.') {
            $schema->table($tableObj->getNormalName())->dropCol($selectedCol);
        }
    }
    private function confirmExecute($schema) {
        $this->println('The following query will be executed on the database:');
        $this->println($schema->getLastQuery(), [
            'color' => 'blue'
        ]);

        if ($this->confirm('Continue?', true)) {
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
            $schema->table($tableObj->getNormalName())->addForeignKey($fkName);
        } else {
            $schema->table($tableObj->getNormalName())->dropForeignKey($fkName);
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
            return $this->queryFromFile($schema);
        }
    }
    private function queryFromFile($schema) {
        $filePath = '';
        $file = null;
        while (!File::isFileExist($filePath)) {
            $filePath = $this->getInput('File path:');
            
            if (File::isFileExist($filePath)) {
                $file = new File($filePath);
                $file->read();
                if ($file->getMIME() == 'application/sql') {
                    break;
                } else {
                    $this->error('Provided file is not SQL file!');
                }
            } else {
                $this->error('No such file!');
            }
        }
        return $this->runFileQuery($schema, $file);
    }
    private function runFileQuery(DB $schema, File $f) {
        $this->println('Executing the query...');
        $schema->setQuery($f->getRawData());
        try {
            $schema->execute();
        } catch (DatabaseException $ex) {
            $this->error('The query finished execution with an error: '.$ex->getMessage());

            return $ex->getCode();
        }
        $this->success('Query executed without errors.');
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
     * @param DB $schema
     * @param Table $tableObj
     */
    private function tableQuery($schema, $tableObj) {
        $queryTypes = [
            'Create database table.',
            'Drop database table.',
            'Drop and create table.',
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
        } else if ($selectedQuery == 'Drop and create table.') {
            $schema->table($tableObj->getNormalName())->drop();
            $query1 = $schema->getLastQuery();
            $schema->table($tableObj->getNormalName())->createTable();
            $schema->setQuery($query1."\n".$schema->getLastQuery());
        }     
    }
}
