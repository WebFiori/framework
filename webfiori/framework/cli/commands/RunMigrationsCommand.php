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
        } catch (\Throwable $ex) {
            $this->error("Unable to create migrations table due to following:");
            $this->println($ex->getMessage());
            return false;
        }
        $this->success("Migrations table succesfully created.");
        return true;
    }
    /**
     * Checks if the argument '--ini' is provided or not and initialize
     * migrations table if provided.
     * 
     * @param MigrationsRunner|null $runner An optional instance which will be
     * used to run the migrations. If provided, the table will be created based
     * on the connection of the runner.
     * 
     * @return bool If the argument '--ini' is not provided, true is returned.
     * Other than that, an attempt to create the migrations table will be made.
     * If created, true is returned. Other than that, false is returned.
     */
    private function checkMigrationsTable(?MigrationsRunner $runner, $conn = null) {
        if (!$this->isArgProvided('--ini')) {
            return 0;
        }
        if ($conn === null) {
            $conn = $this->getDBConnection($runner);
        }
        if ($conn !== null) {
            $temp = $runner !== null ? $runner : new MigrationsRunner(APP_PATH, '\\'.APP_DIR, $conn);
            try {
                $this->println("Initializing migrations table...");
                $temp->createMigrationsTable();
                $this->success("Migrations table succesfully created.");
            } catch (Throwable $ex) {
                $this->error('Unable to create migrations table: '.$ex->getMessage());
                return -1;
            }
            return 0;
        }
        return 0;
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
        
        if (!($runner instanceof MigrationsRunner) && $runner !== null) {
            return -1;
        }
        $ns = $this->getNS($runner);
        
        if (!$this->hasMigrations($ns)) {
            return 0;
        }
        
        $connection = $this->getDBConnection($runner);
        if (!($connection instanceof ConnectionInfo)) {
            return -1;
        }
        
        if ($this->checkMigrationsTable($runner, $connection) == -1) {
            return -1;
        }

        
        try {
            $runner = new MigrationsRunner(ROOT_PATH.DS. str_replace('\\', DS, $ns), $ns, $connection);
        } catch (Throwable $ex) {
            $this->error($ex->getMessage());
            return -1;
        }
        return $this->executeMigrations($runner);
    }
    private function executeMigrations(MigrationsRunner $runner) {
        $this->println("Starting to execute migrations...");
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
    /**
     * Returns the connection that will be used in running the migrations.
     * 
     * The method will first check on the provided runner. If it has connection,
     * it will be returned. Then it will check if the argument '--connection' is
     * provided or not. If provided, the method will check if such connection
     * exist in application configuration. If no connection was found, null
     * is returned. If the argument '--connection' is not provided, the method will
     * ask the user to select a connection from the connections which
     * exist in application configuration.
     * 
     * @param MigrationsRunner|null $runner If given and the connection is set
     * on the instance, it will be returned.
     * 
     * @return ConnectionInfo|null
     */
    private function getDBConnection(?MigrationsRunner $runner = null) {
        
        if ($runner !== null) {
            if ($runner->getConnectionInfo() !== null) {
                return $runner->getConnectionInfo();
            }
        }
        if (!$this->hasConnections()) {
            return -1;
        }
        $dbConnections = array_keys(App::getConfig()->getDBConnections());
        
        if ($this->isArgProvided('--connection')) {
            $connection = $this->getArgValue('--connection');

            if (!in_array($connection, $dbConnections)) {
                $this->error("No connection was found which has the name '$connection'.");
                return -1;
            } else {
                return App::getConfig()->getDBConnection($connection);
            }
        } else {
            return CLIUtils::getConnectionName($this);
        }
    }
    private function applyNext(MigrationsRunner $runner, &$listOfApplied) : bool {
        try {
            //$this->println("Executing migration...");
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
            $this->println($ex->getMessage().' (Line '.$ex->getLine().')');
            $this->warning('Execution stopped.');
            return false;
        }
    }
    /**
     * 
     * @return MigrationsRunner|int|null
     */
    private function getRunnerArg() {
        $runner = $this->getArgValue('--runner');
        
        if ($runner === null) {
            return null;
        }
        
        if (class_exists($runner)) {
            try {
                $runnerInst = new $runner();
            } catch (Throwable $exc) {
                $this->error('The argument --runner has invalid value: Exception: "'.$exc->getMessage().'".');
                return -1;
            }

            if (!($runnerInst instanceof MigrationsRunner)) {
                $this->error('The argument --runner has invalid value: "'.$runner.'" is not an instance of "MigrationsRunner".');
                return -1;
            } else {
                return $runnerInst;
            }
        } else {
            $this->error('The argument --runner has invalid value: Class "'.$runner.'" does not exist.');
            return -1;
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
