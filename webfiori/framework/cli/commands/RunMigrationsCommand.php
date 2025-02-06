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

use Throwable;
use webfiori\cli\Argument;
use webfiori\cli\CLICommand;
use webfiori\database\DatabaseException;
use webfiori\database\migration\AbstractMigration;
use webfiori\database\migration\MigrationsRunner;
use webfiori\framework\App;
use webfiori\framework\cli\CLIUtils;
use webfiori\database\ConnectionInfo;
/**
 *
 * @author Ibrahim
 */
class RunMigrationsCommand extends CLICommand {
    /**
     * 
     * @var MigrationsRunner
     */
    private $migrationsRunner;
    /**
     * 
     * @var ConnectionInfo
     */
    private $connectionInfo;
    public function __construct() {
        parent::__construct('migrations', [
            new Argument('--ns', 'The namespace that holds the migrations', true),
            new Argument('--connection', 'The name of database connection to be used in executing the migrations.', true),
            new Argument('--runner', 'A class that extends the class "webfiori\database\migration\MigrationsRunner".', true),
            new Argument('--ini', 'Creates migrations table in database if not exist.', true),
            new Argument('--rollback', 'Rollback last applied migration.', true),
        ], 'Execute database migrations.');
    }
    private function createMigrationsTable() : bool {
        $this->println("Initializing migrations table...");
        try {
            if ($this->connectionInfo !== null && $this->migrationsRunner->getConnectionInfo() === null) {
                $this->migrationsRunner->setConnectionInfo($this->connectionInfo);
            }
            $this->migrationsRunner->table('migrations')->createTable()->execute();
        } catch (Throwable $ex) {
            $this->error("Unable to create migrations table due to following:");
            $this->println($ex->getMessage());
            return false;
        }
        $this->success("Migrations table succesfully created.");
        return true;
    }
    private function hasMigrationsTable(?MigrationsRunner $runner) : bool {
        if (!$this->isArgProvided('--ini')) {
            return true;
        }
        $conn = $this->getDBConnection($runner);
        if ($conn !== null) {
            $temp = $runner !== null ? $runner : new MigrationsRunner(APP_PATH, '\\'.APP_DIR, $conn);
            $temp->createMigrationsTable();
            return true;
        }
        return false;
    }
    private function getNS(?MigrationsRunner $runner = null) {
        if ($this->isArgProvided('--ns')) {
            return $this->getArgValue('--ns');
        } else if ($runner !== null) {
            return $runner->getMigrationsNamespace();
        } else {
            $this->info("Using default namespace for migrations.");
            return '\\'.APP_DIR.'\\database\\migrations';
        }
    }

    /**
     * Execute the command.
     *
     * @return int 0 in case of success. Other value if failed.
     */
    public function exec() : int {
        
        $runner = $this->getRunnerArg();
        $ns = $this->getNS($runner);
        $this->hasMigrationsTable($runner);
        
        if (!$this->hasMigrations($ns)) {
            return 0;
        }
        
        $this->executeMigrations($runner);
    }
    private function executeMigrations(MigrationsRunner $runner) {
        $listOfApplied = [];
        while ($this->applyNext($runner, $listOfApplied)){};
        
        if (count($listOfApplied) != 0) {
            $this->info("Number of applied migrations: ".count($listOfApplied));
            $this->println("Names of applied migrations:");
            $this->printList(array_map(function (AbstractMigration $migration) {
                return $migration->getName();
            }, $listOfApplied));
        } else {
            $this->info("No migrations were executed.");
        }
        return 0;
    }
    private function getDBConnection(?MigrationsRunner $runner = null) : ?ConnectionInfo {
        
        if ($runner !== null) {
            return $runner->getConnectionInfo();
        }
        if (!$this->hasConnections()) {
            return null;
        }
        $dbConnections = array_keys(App::getConfig()->getDBConnections());
        
        if ($this->isArgProvided('--connection')) {
            $connection = $this->getArgValue('--connection');

            if (!in_array($connection, $dbConnections)) {
                $this->error("No connection was found which has the name '$connection'.");
                return null;
            } else {
                return App::getConfig()->getDBConnection($connection);
            }
        } else {
            return CLIUtils::getConnectionName($this);
        }
    }
    private function applyNext(MigrationsRunner $runner, &$listOfApplied) : bool {
        try {
            $this->println("Executing migration...");
            $applied = $runner->applyOne();
            
            if ($applied !== null) {
                $this->success("Migration '".$applied->getName()."' applied successfuly.");
                $listOfApplied[] = $applied;
                return true;
            } else {
                return false;
            }
        } catch (Throwable $ex) {
            $this->error('Failed to execute migration due to following:');
            $this->println($ex->getMessage());
            $this->warning('Execution stopped.');
            return false;
        }
    }
    private function getRunnerArg() : ?MigrationsRunner {
        $runner = $this->getArgValue('--runner');
        
        if ($runner === null) {
            return null;
        }
        
        if (class_exists($runner)) {
            try {
                $runnerInst = new $runner();
            } catch (Throwable $exc) {
                $this->error('The argument --runner has invalid value: Exception: "'.$exc->getMessage().'".');
                return null;
            }

            if (!($runnerInst instanceof MigrationsRunner)) {
                $this->error('The argument --runner has invalid value: "'.$runner.'" is not an instance of "MigrationsRunner".');
                return null;
            } else {
                return $runnerInst;
            }
        } else {
            $this->error('The argument --runner has invalid value: Class "'.$runner.'" does not exist.');
            return null;
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
        $tmpRunner = new MigrationsRunner(ROOT_PATH.DS.str_replace('\\', DS, $namespace), $namespace, null);
        $this->println("Checking namespace '$namespace' for migrations...");
        $count = count($tmpRunner->getMigrations());
        if ($count == 0) {
            $this->info("No migrations found in the namespace '$namespace'.");
            return false;
        }
        $this->info("Found $count migration(s) in the namespace '$namespace'.");
        return true;
    }
}
