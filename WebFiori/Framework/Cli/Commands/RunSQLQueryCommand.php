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
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cli\Argument;
use WebFiori\Cli\Command;
use WebFiori\Database\Database;
use WebFiori\Database\DatabaseException;
use WebFiori\Database\Table;
use WebFiori\File\File;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLIUtils;
use WebFiori\Framework\DB;
/**
 * A command which can be used to execute SQL queries on
 * specific database.
 *
 * @author Ibrahim
 *
 * @version 1.0
 */
class RunSQLQueryCommand extends Command {
    public function __construct() {
        parent::__construct('run-query', [
            new Argument('--connection', 'Database connection that the query will be executed on.', true),
            new Argument('--schema', 'The namespace of a class that extends the class "WebFiori\\Framework\\DB" which represents database schema.', true),
            new Argument('--create', 'This option is used alongside the option --table and --schema. If it is provided, this means initiate the process of creating the database or the Table.', true),
            new Argument('--table', 'Table class to run query on.', true),
            new Argument('--file', 'The path to SQL file that holds SQL query.', true),
            new Argument('--no-confirm', 'If this argument is provided, the query will be executed without confirmation step.', true),
            new Argument('--show-sql', 'If this argument is provided, SQL statement will be shown in the console. This option is ignored if option --no-confirm is not provided.', true),
        ], 'Execute SQL query on specific database.');
    }
    /**
     * Execute the command.
     *
     * @return int 0 in case of success. Other value if failed.
     */
    public function exec() : int {
        $dbConnections = array_keys(App::getConfig()->getDBConnections());
        $schema = $this->getArgValue('--schema');

        if (count($dbConnections) != 0) {
            if ($schema !== null) {
                if (class_exists($schema)) {
                    return $this->schemaBased($schema);
                } else {
                    $this->warning('Schema not found: '.$schema);

                    return $this->connectionBased($dbConnections);
                }
            } else {
                return $this->connectionBased($dbConnections);
            }
        } else {
            $this->error('No database connections available. Add connections to application configuration or use the command "add".');

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
    private function confirmExecute(Database $schema) {
        $noConfirmExec = $this->isArgProvided('--no-confirm');
        $dbName = $schema->getConnectionInfo()->getDBName();

        if ($this->isArgProvided('--show-sql') || !$noConfirmExec) {
            $this->println("The following query will be executed on the database '$dbName':");
            $this->println($schema->getLastQuery(), [
                'color' => 'blue'
            ]);
        }

        if ($noConfirmExec) {
            return $this->executeQ($schema);
        }

        if ($this->confirm('Continue?', true)) {
            return $this->executeQ($schema);
        } else {
            $this->info('Nothing to execute.');

            return 0;
        }
    }
    private function connectionBased($dbConnections) : int {
        $connName = $this->getArgValue('--connection');
        $file = $this->getArgValue('--file');

        if ($connName === null) {
            $connName = $this->select('Select database connection:', $dbConnections, 0);
        } else if (!in_array($connName, $dbConnections)) {
            $this->error('No connection with name "'.$connName.'" was found!');

            return -1;
        }
        $schema = new DB($connName);

        if ($file !== null) {
            $fileObj = new File(ROOT_PATH.DS.$file);

            if (!$fileObj->isExist()) {
                $fileObj = new File($file);
            }

            if ($fileObj->isExist()) {
                $fileObj->read();
                $mime = $fileObj->getMIME();

                if ($mime == 'application/sql' || $mime == 'application/x-sql') {
                    return $this->runFileQuery($schema, $fileObj);
                } else {
                    $this->error('Provided file is not SQL file!');

                    return -1;
                }
            } else {
                $path = $fileObj->getAbsolutePath();
                
                if (strlen($path) == 0) {
                    $path = $file;
                }
                $this->error('No such file: '.$path);

                return -1;
            }
        }


        return $this->generalQuery($schema);
    }
    private function executeQ(DB $schema) {
        $this->info('Executing query on database '.$schema->getConnectionInfo()->getDBName().'...');
        try {
            $schema->execute();
            $this->success('Query executed without errors.');

            return 0;
        } catch (DatabaseException $ex) {
            $this->error($ex->getMessage());

            return $ex->getCode();
        }
    }
    private function fkQuery($schema, $selectedQuery, Table $tableObj) {
        $keys = $tableObj->getForeignKeys();
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
            $query = $this->getInput('Please type in SQL query:');
            $schema->setQuery($query);

            return $this->confirmExecute($schema);
        } else if ($selected == 'Run query on table instance.') {
            $tableObj = CLIUtils::readTable($this);

            $schema->addTable($tableObj);

            return $this->tableQuery($schema, $tableObj);
        } else if ($selected == 'Run query from file.') {
            return $this->queryFromFile($schema);
        }
    }
    private function queryFromFile($schema) {
        $filePath = '';
        $file = null;

        while (!File::isFileExist($filePath)) {
            $filePath = $this->getInput('File path:');
            $modified = ROOT_PATH.DS.$filePath;

            if (File::isFileExist($modified)) {
                $filePath = $modified;
            } 
            
            if (File::isFileExist($filePath)) {
                $file = new File($filePath);
                $file->read();
                $mime = $file->getMIME();
                
                if ($mime == 'application/sql' || $mime == 'application/x-sql') {
                    break;
                } else {
                    $this->error('Provided file is not SQL file!');
                }
            } else {
                $this->error('No such file: '.$filePath);
            }
        }

        return $this->runFileQuery($schema, $file);
    }

    private function queryOnSchema(DB $schema) {
        if ($this->isArgProvided('--create')) {
            $schema->createTables();

            return $this->confirmExecute($schema);
        }
        $options = [
            'Create Database.',
            'Run Query on Specific Table.'
        ];
        $selected = $this->select('Select an option:', $options);

        if ($selected == 'Create Database.') {
            $schema->createTables();

            return $this->confirmExecute($schema);
        } else {
            $selectedTable = $this->select('Select database table:', array_keys($schema->getTables()));

            return $this->tableQuery($schema, $schema->getTable($selectedTable));
        }
    }
    private function runFileQuery(DB $schema, File $f) : int {
        $schema->setQuery($f->getRawData());

        return $this->confirmExecute($schema);
    }
    private function schemaBased($schema) {
        $schemaInst = new $schema();

        if ($schemaInst instanceof DB) {
            return $this->queryOnSchema($schemaInst);
        } else {
            $this->error('Given class is not an instance of "WebFiori\\Framework\\DB"!');

            return -1;
        }
    }
    /**
     *
     * @param DB $schema
     * @param Table $tableObj
     */
    private function tableQuery($schema, $tableObj) {
        if ($this->isArgProvided('--create')) {
            $schema->table($tableObj->getNormalName())->createTable();

            return $this->confirmExecute($schema);
        }
        $queryTypes = [
            'Create database table.',
            'Drop database table.',
            'Drop and create table.',
            'Add Column.',
            'Modify Column.',
            'Drop Column.'
        ];

        if ($tableObj->getForeignKeysCount() != 0) {
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
            $schema->getQueryGenerator()->setQuery($query1."\n".$schema->getLastQuery(), true);
        }

        return $this->confirmExecute($schema);
    }
}
