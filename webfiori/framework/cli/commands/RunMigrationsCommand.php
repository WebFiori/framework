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
        $dbConnections = array_keys(App::getConfig()->getDBConnections());

        if ($this->isArgProvided('--connection')) {
            $connection = $this->getArgValue('--connection');

            if (!in_array($connection, $dbConnections)) {
                $this->error("No connection was found which has the name '$connection'.");
                return -1;
            }
        } else {
            $connection = CLIUtils::getConnectionName($this);
            if ($connection === null) {
                return -2;
            }
        }
        $ns = $this->isArgProvided('--ns') ? $this->getArgValue('--ns') : '\\'.APP_DIR.'\database\\migrations';
        $connectionInfo = App::getConfig()->getDBConnection($connection);
        $runner = new MigrationsRunner(ROOT_PATH.DS.str_replace('\\', DS, $ns), $ns, $connectionInfo);
        $applied = $runner->apply();
        $this->info("Number of applied migrations: ".count($applied));
        if (count($applied) != 0) {
            $this->println("Names of applied migrations:");
        }
        $this->printList(array_map(function (AbstractMigration $migration) {
            return $migration->getName();
        }, $applied));
    }
}
