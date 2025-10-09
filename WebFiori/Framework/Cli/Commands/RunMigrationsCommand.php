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
namespace WebFiori\Framework\Cli\Commands;

use Throwable;
use WebFiori\Cli\Argument;
use WebFiori\Cli\Command;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Database;
use WebFiori\Database\migration\AbstractMigration;
use WebFiori\Database\Schema\SchemaRunner;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLIUtils;

/**
 * Command for executing database migrations.
 * 
 * @author Ibrahim
 */
class RunMigrationsCommand extends Command {
    
    private const EXIT_SUCCESS = 0;
    private const EXIT_ERROR = 1;
    
    private ?SchemaRunner $runner = null;
    private ?ConnectionInfo $connection = null;
    
    public function __construct() {
        parent::__construct('migrations', [
            new Argument('--connection', 'The name of database connection to be used in executing the migrations.', true),
            new Argument('--runner', 'A class that extends the class "WebFiori\Database\Schema\SchemaRunner".', true),
            new Argument('--init', 'Creates migrations table in database if not exist.', true),
            new Argument('--rollback', 'Rollback migrations.', true),
            new Argument('--all', 'If provided with --rollback, all migrations will be rolled back.', true),
        ], 'Execute database migrations.');
    }
    
    /**
     * Execute the command.
     */
    public function exec(): int {
        try {
            if (!$this->initializeCommand()) {
                return self::EXIT_ERROR;
            }
            
            if ($this->isArgProvided('--init')) {
                return $this->initializeMigrationsTable();
            }
            
            if ($this->isArgProvided('--rollback')) {
                return $this->executeRollback();
            }
            
            return $this->executeMigrations();
            
        } catch (Throwable $e) {
            $this->error('An exception was thrown.');
            $this->println('Exception Message: ' . $e->getMessage());
            $this->println('Code: ' . $e->getCode());
            $this->println('At: ' . $e->getFile());
            $this->println('Line: ' . $e->getLine());
            $this->println('Stack Trace: ');
            $this->println($e->getTraceAsString());
            return self::EXIT_ERROR;
        }
    }
    
    /**
     * Initialize command dependencies.
     */
    private function initializeCommand(): bool {
        $this->connection = $this->resolveConnection();
        if ($this->connection === null) {
            return false;
        }
        
        $this->runner = $this->createRunner();
        if ($this->runner === null) {
            $runnerClass = $this->getArgValue('--runner');
            if ($runnerClass !== null) {
                // Runner creation failed, return false
                return false;
            }
            // Create default runner with connection
            $this->runner = new SchemaRunner($this->connection);
        }
        
        // Set connection on runner if it doesn't have one
        if ($this->runner->getConnectionInfo() === null) {
            $this->runner = new SchemaRunner($this->connection);
        }
        
        return true;
    }
    
    /**
     * Create SchemaRunner instance from --runner argument.
     */
    private function createRunner(): ?SchemaRunner {
        $runnerClass = $this->getArgValue('--runner');
        
        if ($runnerClass === null) {
            return null; // Will be created later with connection
        }
        
        if (!class_exists($runnerClass)) {
            $this->error("The argument --runner has invalid value: Class \"$runnerClass\" does not exist.");
            return null;
        }
        
        try {
            $runner = new $runnerClass();
        } catch (Throwable $e) {
            $this->error("The argument --runner has invalid value: Exception: \"{$e->getMessage()}\".");
            return null;
        }
        
        if (!($runner instanceof SchemaRunner)) {
            $this->error("The argument --runner has invalid value: \"$runnerClass\" is not an instance of \"SchemaRunner\".");
            return null;
        }
        
        return $runner;
    }
    
    /**
     * Resolve database connection.
     */
    private function resolveConnection(): ?ConnectionInfo {
        // Check if runner already has a connection
        if ($this->runner !== null && $this->runner->getConnectionInfo() !== null) {
            return $this->runner->getConnectionInfo();
        }
        
        $connections = App::getConfig()->getDBConnections();
        if (empty($connections)) {
            $this->info('No connections were found in application configuration.');
            return null;
        }
        
        $connectionName = $this->getArgValue('--connection');
        
        if ($connectionName !== null) {
            $connection = App::getConfig()->getDBConnection($connectionName);
            if ($connection === null) {
                $this->error("No connection was found which has the name '$connectionName'.");
                return null;
            }
            return $connection;
        }
        
        return CLIUtils::getConnectionName($this);
    }
    
    /**
     * Initialize migrations table.
     */
    private function initializeMigrationsTable(): int {
        try {
            $this->println("Initializing migrations table...");
            $this->runner->createSchemaTable();
            $this->success("Migrations table successfully created.");
            return self::EXIT_SUCCESS;
        } catch (Throwable $e) {
            $this->error('Unable to create migrations table due to following:');
            $this->println($e->getMessage());
            return self::EXIT_ERROR;
        }
    }
    
    /**
     * Execute migrations rollback.
     */
    private function executeRollback(): int {
        $migrations = $this->runner->getChanges();
        if (empty($migrations)) {
            $this->info("No migrations found.");
            return self::EXIT_SUCCESS;
        }
        
        $this->println("Rolling back migrations...");
        
        try {
            if ($this->isArgProvided('--all')) {
                $rolledBack = $this->runner->rollbackUpTo(null);
            } else {
                $rolledBack = $this->rollbackLast();
            }
            
            if (empty($rolledBack)) {
                $this->info("No migrations were rolled back.");
            } else {
                foreach ($rolledBack as $migration) {
                    $this->success("Migration '{$migration->getName()}' was successfully rolled back.");
                }
            }
            
            return self::EXIT_SUCCESS;
            
        } catch (Throwable $e) {
            $this->error('Failed to execute migration due to following:');
            $this->println($e->getMessage() . ' (Line ' . $e->getLine() . ')');
            $this->warning('Execution stopped.');
            return self::EXIT_ERROR;
        }
    }
    
    /**
     * Rollback the last applied migration.
     */
    private function rollbackLast(): array {
        $changes = $this->runner->getChanges();
        $lastApplied = null;
        
        // Find the last applied migration
        foreach ($changes as $change) {
            if ($this->runner->isApplied($change->getName())) {
                $lastApplied = $change;
            }
        }
        
        if ($lastApplied === null) {
            return [];
        }
        
        return $this->runner->rollbackUpTo($lastApplied->getName());
    }
    
    /**
     * Execute migrations.
     */
    private function executeMigrations(): int {
        $migrations = $this->runner->getChanges();
        if (empty($migrations)) {
            $this->info("No migrations found.");
            return self::EXIT_SUCCESS;
        }
        
        $this->println("Starting to execute migrations...");
        $appliedMigrations = [];
        
        try {
            while (($migration = $this->getNextMigration()) !== null) {
                $applied = $this->runner->applyOne();
                if ($applied !== null) {
                    $this->success("Migration '{$applied->getName()}' applied successfully.");
                    $appliedMigrations[] = $applied;
                } else {
                    break;
                }
            }
            
            if (empty($appliedMigrations)) {
                $this->info("No migrations were executed.");
            } else {
                $this->info("Number of applied migrations: " . count($appliedMigrations));
                $this->println("Names of applied migrations:");
                $names = array_map(fn($m) => $m->getName(), $appliedMigrations);
                $this->printList($names);
            }
            
            return self::EXIT_SUCCESS;
            
        } catch (Throwable $e) {
            $this->error('Failed to execute migration due to following:');
            $this->println($e->getMessage() . ' (Line ' . $e->getLine() . ')');
            $this->warning('Execution stopped.');
            return self::EXIT_ERROR;
        }
    }
    
    /**
     * Get the next migration to apply.
     */
    private function getNextMigration(): ?AbstractMigration {
        foreach ($this->runner->getChanges() as $migration) {
            if (!$this->runner->isApplied($migration->getName())) {
                return $migration;
            }
        }
        return null;
    }
}
