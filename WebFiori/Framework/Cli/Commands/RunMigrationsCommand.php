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
use WebFiori\Database\Schema\SchemaRunner;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLIUtils;

/**
 * Command for executing database migrations.
 * 
 * @author Ibrahim
 */
class RunMigrationsCommand extends Command {
    
    private ?SchemaRunner $runner = null;
    
    public function __construct() {
        parent::__construct('migrations', [
            new Argument('--connection', 'The name of database connection to use.', true),
            new Argument('--env', 'Environment name (dev, staging, production). Default: dev', true),
            new Argument('--init', 'Create migrations tracking table.', true),
            new Argument('--rollback', 'Rollback migrations.', true),
            new Argument('--batch', 'Rollback specific batch number.', true),
            new Argument('--all', 'Rollback all migrations.', true),
            new Argument('--dry-run', 'Preview changes without executing.', true),
        ], 'Execute database migrations.');
    }
    
    public function exec(): int {
        try {
            $connection = $this->getConnection();
            if ($connection === null) {
                return 1;
            }
            
            $env = $this->getArgValue('--env') ?? 'dev';
            $this->runner = new SchemaRunner($connection, $env);
            
            // Discover migrations
            $migrationsPath = APP_PATH.'Database'.DS.'Migrations';
            $namespace = APP_DIR.'\\Database\\Migrations';
            $count = $this->runner->discoverFromPath($migrationsPath, $namespace);
            
            if ($count === 0 && !$this->isArgProvided('--init')) {
                $this->info('No migrations found.');
                return 0;
            }
            
            if ($this->isArgProvided('--init')) {
                return $this->initTable();
            }
            
            if ($this->isArgProvided('--rollback')) {
                return $this->rollback();
            }
            
            if ($this->isArgProvided('--dry-run')) {
                return $this->dryRun();
            }
            
            return $this->runMigrations();
            
        } catch (Throwable $e) {
            $this->error('An exception was thrown.');
            $this->println('Message: ' . $e->getMessage());
            $this->println('File: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }
    }
    
    private function getConnection(): ?ConnectionInfo {
        $connections = App::getConfig()->getDBConnections();
        
        if (empty($connections)) {
            $this->info('No database connections configured.');
            return null;
        }
        
        $connectionName = $this->getArgValue('--connection');
        
        if ($connectionName !== null) {
            $connection = App::getConfig()->getDBConnection($connectionName);
            if ($connection === null) {
                $this->error("Connection '$connectionName' not found.");
                return null;
            }
            return $connection;
        }
        
        return CLIUtils::getConnectionName($this);
    }
    
    private function initTable(): int {
        try {
            $this->println('Creating migrations tracking table...');
            $this->runner->createSchemaTable();
            $this->success('Migrations table created successfully.');
            return 0;
        } catch (Throwable $e) {
            $this->error('Failed to create migrations table: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function rollback(): int {
        try {
            if ($this->isArgProvided('--all')) {
                $this->println('Rolling back all migrations...');
                $rolled = $this->runner->rollbackUpTo(null);
            } else if ($this->isArgProvided('--batch')) {
                $batch = (int)$this->getArgValue('--batch');
                $this->println("Rolling back batch $batch...");
                $rolled = $this->runner->rollbackBatch($batch);
            } else {
                $this->println('Rolling back last batch...');
                $rolled = $this->runner->rollbackLastBatch();
            }
            
            if (empty($rolled)) {
                $this->info('No migrations to rollback.');
            } else {
                foreach ($rolled as $change) {
                    $this->success('Rolled back: ' . $change->getName());
                }
                $this->info('Total rolled back: ' . count($rolled));
            }
            
            return 0;
        } catch (Throwable $e) {
            $this->error('Rollback failed: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function dryRun(): int {
        $pending = $this->runner->getPendingChanges(true);
        
        if (empty($pending)) {
            $this->info('No pending migrations.');
            return 0;
        }
        
        $this->println('Pending migrations:');
        foreach ($pending as $item) {
            $this->println('  - ' . $item['change']->getName());
            if (!empty($item['queries'])) {
                $this->println('    Queries:');
                foreach ($item['queries'] as $query) {
                    $this->println('      ' . $query);
                }
            }
        }
        
        return 0;
    }
    
    private function runMigrations(): int {
        $this->println('Running migrations...');
        
        $result = $this->runner->apply();
        
        if ($result->hasApplied()) {
            foreach ($result->getApplied() as $change) {
                $this->success('Applied: ' . $change->getName());
            }
        }
        
        if ($result->hasSkipped()) {
            foreach ($result->getSkipped() as $item) {
                $this->warning('Skipped: ' . $item['change']->getName() . ' (' . $item['reason'] . ')');
            }
        }
        
        if ($result->hasFailed()) {
            foreach ($result->getFailed() as $item) {
                $this->error('Failed: ' . $item['change']->getName());
                $this->println('  Error: ' . $item['error']->getMessage());
            }
        }
        
        $this->info('Applied: ' . $result->count() . ' migrations');
        $this->info('Time: ' . round($result->getTotalTime(), 2) . 'ms');
        
        return $result->hasFailed() ? 1 : 0;
    }
}
