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
use WebFiori\Cli\Argument;
use WebFiori\Cli\Command;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\migration\AbstractMigration;
use WebFiori\Database\Schema\SchemaRunner;
use webfiori\framework\App;
use webfiori\framework\cli\CLIUtils;
/**
 *
 * @author Ibrahim
 */
class RunMigrationsCommand extends Command {
    /**
     * 
     * @var SchemaRunner
     */
    private $migrationsRunner;
    /**
     * 
     * @var ConnectionInfo
     */
    private $connectionInfo;
    public function __construct() {
        parent::__construct('migrations', [
            new Argument('--connection', 'The name of database connection to be used in executing the migrations.', true),
            new Argument('--runner', 'A class that extends the class "WebFiori\Database\Schema\SchemaRunner".', true),
            new Argument('--ini', 'Creates migrations table in database if not exist.', true),
            new Argument('--rollback', 'Rollback last applied migration.', true),
            new Argument('--all', 'If provided with the option --rollback, all migrations will be rolled back.', true),
        ], 'Execute database migrations.');
    }
    
    /**
     * Checks if the argument '--ini' is provided or not and initialize
     * migrations table if provided.
     * 
     * @param SchemaRunner|null $runner An optional instance which will be
     * used to run the migrations. If provided, the table will be created based
     * on the connection of the runner.
     * 
     * @return bool If the argument '--ini' is not provided, true is returned.
     * Other than that, an attempt to create the migrations table will be made.
     * If created, true is returned. Other than that, false is returned.
     */
    private function checkMigrationsTable(?SchemaRunner $runner, $conn = null) {
        if (!$this->isArgProvided('--ini')) {
            return 0;
        }
        if ($conn === null) {
            $conn = $this->getDBConnection($runner);
        }
        if ($conn !== null) {
            
            try {
                $this->println("Initializing migrations table...");
                $temp = $runner !== null ? $runner : new SchemaRunner($conn);
                
                $temp->createSchemaTable();
                $this->success("Migrations table succesfully created.");
            } catch (\Throwable $ex) {
                $this->error('Unable to create migrations table due to following:');
                $this->println($ex->getMessage());
                return -1;
            }
            return 0;
        }
        return 0;
    }

    /**
     * Execute the command.
     *
     * @return int 0 in case of success. Other value if failed.
     */
    public function exec() : int {
        
        $runner = $this->getRunnerArg();
        
        if (!($runner instanceof SchemaRunner) && $runner !== null) {
            return -1;
        }
        
        $connection = $this->getDBConnection($runner);
        if (!($connection instanceof ConnectionInfo)) {
            return -1;
        }
        
        if ($this->checkMigrationsTable($runner, $connection) == -1) {
            return -1;
        }

        
        if ($this->isArgProvided("--rollback")) {
            return $this->rollbackMigration($runner);
        } else {
            return $this->executeMigrations($runner);
        }
    }
    private function rollbackMigration(SchemaRunner $runner) {
        $isAll = $this->isArgProvided('--all');
        $rolledCount = 0;
        if ($isAll) {
            $this->println("Rolling back migrations...");
            $migrations = $runner->rollbackUpTo(null);
            foreach ($migrations as $migration) {
                $this->printInfo($migration, $rolledCount);
            }
        } else {
            $changes = $runner->getChanges();
            $change = null;
            $applied = null;

            foreach ($changes as $change) {
                if (!$runner->isApplied($change->getName())) {
                    
                    break;
                }
                $applied = $change;
            }
            if ($applied !== null) {
                $runner->rollbackUpTo($applied->getName());
            }
        }
        if ($rolledCount == 0) {
            $this->info("No migration rolled back.");
        }
        
        
        return 0;
    }
    private function doRollback(SchemaRunner $runner) : array {
        try {
            return $runner->rollbackUpTo(null);
            
        } catch (Throwable $ex) {
            $this->error('Failed to execute migration due to following:');
            $this->println($ex->getMessage().' (Line '.$ex->getLine().')');
            $this->warning('Execution stopped.');
        }
        return [];
    }
    private function printInfo(?AbstractMigration $migration, &$rolledCount = 0) {
        if ($migration !== null) {
            $rolledCount++;
            $this->success("Migration '".$migration->getName()."' was successfully rolled back.");
        }
    }
    private function executeMigrations(SchemaRunner $runner) {
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
     * @param SchemaRunner|null $runner If given and the connection is set
     * on the instance, it will be returned.
     * 
     * @return ConnectionInfo|null
     */
    private function getDBConnection(?SchemaRunner $runner = null) {
        
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
    public function getNext(SchemaRunner $runner) : ?AbstractMigration {
        foreach ($runner->getChanges() as $m) {
            if ($runner->isApplied($m->getName())) {
                continue;
            } else {
                return $m;
            }
        }
        return null;
    }
    private function applyNext(SchemaRunner $runner, &$listOfApplied) : bool {
        $toBeApplied = $this->getNext($runner);
        
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
            $this->warning('Rolling back changes...');
            
            try {
                $toBeApplied->down($runner);
            } catch (Throwable $exc) {
                $this->error('Failed to rollback due to following:');
                $this->println($ex->getMessage().' (Line '.$ex->getLine().')');
            }

            return false;
        }
    }
    /**
     * 
     * @return SchemaRunner|int|null
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

            if (!($runnerInst instanceof SchemaRunner)) {
                $this->error('The argument --runner has invalid value: "'.$runner.'" is not an instance of "SchemaRunner".');
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
        $tmpRunner = new SchemaRunner(null);
        $this->println("Checking namespace '$namespace' for migrations...");
        $count = count($tmpRunner->getChanges());
        if ($count == 0) {
            $this->info("No migrations found in the namespace '$namespace'.");
            return false;
        }
        $this->info("Found $count migration(s) in the namespace '$namespace'.");
        return true;
    }
}
