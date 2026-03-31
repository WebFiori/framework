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
 * Command for previewing pending migrations without executing.
 * 
 * @author Ibrahim
 */
class DryRunMigrationsCommand extends Command {
    
    private ?SchemaRunner $runner = null;
    
    public function __construct() {
        parent::__construct('migrations:dry-run', [
            new Argument('--connection', 'The name of database connection to use.', true),
            new Argument('--env', 'Environment name (dev, staging, production). Default: dev', true),
        ], 'Preview pending migrations without executing.');
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
            
            // Discover seeders
            $seedersPath = APP_PATH.'Database'.DS.'Seeders';
            $seedersNs = APP_DIR.'\\Database\\Seeders';
            $count += $this->runner->discoverFromPath($seedersPath, $seedersNs);

            if ($count === 0) {
                $this->info('No migrations/seeders found.');
                return 0;
            }
            
            return $this->dryRun();
            
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
    
    private function dryRun(): int {
        $pending = $this->runner->getPendingChanges(true);
        
        if (empty($pending)) {
            $this->info('No pending migrations/seeders.');
            return 0;
        }
        
        $this->println('Pending migrations/seeders:');
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
}
