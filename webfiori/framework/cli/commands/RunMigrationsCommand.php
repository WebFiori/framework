<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2025 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework\cli\commands;

use webfiori\cli\Argument;
use webfiori\cli\CLICommand;
use webfiori\database\Database;
use webfiori\database\DatabaseException;
use webfiori\database\migration\AbstractMigration;
use webfiori\database\migration\MigrationsRunner;
use webfiori\database\Table;
use webfiori\file\File;
use webfiori\framework\App;
use webfiori\framework\cli\CLIUtils;
use webfiori\framework\DB;
/**
 *
 * @author Ibrahim
 */
class RunMigrationsCommand extends CLICommand {
    private $migrationsRunner;
    public function __construct() {
        parent::__construct('migrations', [
            new Argument('--ns', 'The namespace that holds the migrations', true),
            new Argument('--connection', 'The name of database connection to be used in executing the migrations.', true),
        ], 'Execute database migrations.');
    }
    /**
     * Execute the command.
     *
     * @return int 0 in case of success. Other value if failed.
     */
    public function exec() : int {
        $ns = $this->isArgProvided('--ns') ? $this->getArgValue('--ns') : '\\'.APP_DIR.'\\database\\migrations';
        if (!$this->hasMigrations($ns)) {
            return 0;
        }
        
        if (!$this->hasConnections()) {
            return 0;
        }
        $dbConnections = array_keys(App::getConfig()->getDBConnections());
                
        if ($this->isArgProvided('--connection')) {
            $connection = $this->getArgValue('--connection');

            if (!in_array($connection, $dbConnections)) {
                $this->error("No connection was found which has the name '$connection'.");
                return -1;
            }
        } else {
            $connection = CLIUtils::getConnectionName($this);
        }
        
        $connectionInfo = App::getConfig()->getDBConnection($connection);
        $this->migrationsRunner->setConnectionInfo($connectionInfo);
        
        $applied = $this->migrationsRunner->apply();
        
        if (count($applied) != 0) {
            $this->info("Number of applied migrations: ".count($applied));
            $this->println("Names of applied migrations:");
            $this->printList(array_map(function (AbstractMigration $migration) {
                return $migration->getName();
            }, $applied));
        } else {
            $this->info("No migrations were executed.");
        }
        
    }
    private function hasConnections() : bool {
        $dbConnections = App::getConfig()->getDBConnections();
        if (count($dbConnections) == 0) {
            $this->info('No connections were found in application configuration.');
            return false;
        }
        return true;
    }
    private function hasMigrations(string $namespace) : bool {
        $this->migrationsRunner = new MigrationsRunner(ROOT_PATH.DS.str_replace('\\', DS, $namespace), $namespace, null);
        
        if (count($this->migrationsRunner->getMigrations()) == 0) {
            $this->info("No migrations were found in the namespace '$namespace'.");
            return false;
        }
        return true;
    }
}
