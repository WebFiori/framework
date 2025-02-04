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
    public function __construct() {
        parent::__construct('migrations', [
            new Argument('--ns', 'The namespace that holds the migrations', true),
            new Argument('--connection', 'The name of database connection to be used in executing the migrations.', true),
            new Argument('--runner', 'A class that extends the class "webfiori\database\migration\MigrationsRunner".', true),
        ], 'Execute database migrations.');
    }
    /**
     * Execute the command.
     *
     * @return int 0 in case of success. Other value if failed.
     */
    public function exec() : int {
        
        if ($this->getRunnerArgValidity() == 0) {
            $ns = $this->isArgProvided('--ns') ? $this->getArgValue('--ns') : '\\'.APP_DIR.'\\database\\migrations';
            $connectionInfo = null;

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
                } else {
                    $connectionInfo = App::getConfig()->getDBConnection($connection);
                }
            } else {
                $connectionInfo = CLIUtils::getConnectionName($this);
            }


            $this->migrationsRunner->setConnectionInfo($connectionInfo);
        } else if ($this->migrationsRunner === null) {
            return -2;
        }
        
        if (count($this->migrationsRunner->getMigrations()) === 0) {
            $this->info("No migrations where found in the namespace '".$this->migrationsRunner->getMigrationsNamespace()."'.");
            return 0;
        }
        
        if (!$this->migrationsRunner->isConnected()) {
            $err = $this->migrationsRunner->getLastError();
            $this->error($err['message']);
            return -1;
        }
        $listOfApplied = [];
        while ($this->applyNext($listOfApplied)){};
        
        if (count($listOfApplied) != 0) {
            $this->info("Number of applied migrations: ".count($applied));
            $this->println("Names of applied migrations:");
            $this->printList(array_map(function (AbstractMigration $migration) {
                return $migration->getName();
            }, $applied));
        } else {
            $this->info("No migrations were executed.");
        }
        
    }
    private function applyNext(&$listOfApplied) {
        try {
            $applied = $this->migrationsRunner->applyOne();
            
            if ($applied !== null) {
                $this->success("Migration '".$applied->getName()."' applied successfuly.");
                $listOfApplied[] = $applied;
                return true;
            } else {
                return false;
            }
        } catch (DatabaseException $ex) {
            $this->error('Failed to execute migrations due to following error:');
            $this->println($ex->getCode().' - '.$ex->getMessage());
            return false;
        }
    }
    private function getRunnerArgValidity() {
        $runner = $this->getArgValue('--runner');
        
        if ($runner === null) {
            return 0;
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
                $this->migrationsRunner = $runnerInst;
                return 1;
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
        $this->migrationsRunner = new MigrationsRunner(ROOT_PATH.DS.str_replace('\\', DS, $namespace), $namespace, null);
        
        if (count($this->migrationsRunner->getMigrations()) == 0) {
            $this->info("No migrations were found in the namespace '$namespace'.");
            return false;
        }
        return true;
    }
}
