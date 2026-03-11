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
 * Command for rolling back database migrations.
 * 
 * @author Ibrahim
 */
class RollbackMigrationsCommand extends Command {
    
    private ?SchemaRunner $runner = null;
    
    public function __construct() {
        parent::__construct('migrations:rollback', [
            new Argument('--connection', 'The name of database connection to use.', true),
            new Argument('--env', 'Environment name (dev, staging, production). Default: dev', true),
            new Argument('--batch', 'Rollback specific batch number.', true),
            new Argument('--all', 'Rollback all migrations.', true),
        ], 'Rollback database migrations.');
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
            $this->runner->discoverFromPath($migrationsPath, $namespace);
            
            return $this->rollback();
            
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
}
