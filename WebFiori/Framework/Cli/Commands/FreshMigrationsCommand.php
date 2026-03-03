<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026 WebFiori Framework
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
 * Command for rolling back all migrations and running them fresh.
 * 
 * @author Ibrahim
 */
class FreshMigrationsCommand extends Command {
    
    private ?SchemaRunner $runner = null;
    
    public function __construct() {
        parent::__construct('migrations:fresh', [
            new Argument('--connection', 'The name of database connection to use.', true),
            new Argument('--env', 'Environment name (dev, staging, production). Default: dev', true),
        ], 'Rollback all migrations and run them fresh.');
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

            $seedersPath = APP_PATH.'Database'.DS.'Seeders';
            $seedersNamespace = APP_DIR.'\\Database\\Seeders';
            $count += $this->runner->discoverFromPath($seedersPath, $seedersNamespace);
            
            if ($count === 0) {
                $this->info('No migrations found.');
                return 0;
            }
            
            // Rollback all
            $this->println('Rolling back all migrations...');
            $rolled = $this->runner->rollbackUpTo(null);
            $this->runner->getRepository()->clearAll();
            
            if (!empty($rolled)) {
                foreach ($rolled as $change) {
                    $this->success('Rolled back: ' . $change->getName());
                }
                $this->info('Total rolled back: ' . count($rolled));
            } else {
                $this->info('No migrations to rollback.');
            }
            
            $this->println('');
            
            // Run all
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
    
    private function runMigrations(): int {
        $this->println('Running migrations...');
        
        $result = $this->runner->apply();
        
        $applied = $result->getApplied();
        if (!empty($applied)) {
            foreach ($applied as $change) {
                $this->success('Applied: ' . $change->getName());
            }
        }
        
        $skipped = $result->getSkipped();
        if (!empty($skipped)) {
            foreach ($skipped as $item) {
                $this->warning('Skipped: ' . $item['change']->getName() . ' (' . $item['reason'] . ')');
            }
        }
        
        $failed = $result->getFailed();
        if (!empty($failed)) {
            foreach ($failed as $item) {
                $this->error('Failed: ' . $item['change']->getName());
                $this->println('  Error: ' . $item['error']->getMessage());
            }
        }
        
        $migrationsCount = count(array_filter($result->getApplied(), fn($c) => $c->getType() === 'migration'));
        $seedersCount = count(array_filter($result->getApplied(), fn($c) => $c->getType() === 'seeder'));

        if ($migrationsCount > 0) {
            $this->info('Applied: ' . $migrationsCount . ' migration(s)');
        }

        if ($seedersCount > 0) {
            $this->info('Applied: ' . $seedersCount . ' seeder(s)');
        }

        if ($migrationsCount === 0 && $seedersCount === 0) {
            $this->info('Applied: 0 migrations');
        }

        $this->info('Time: ' . round($result->getTotalTime(), 2) . 'ms');
        
        return !empty($failed) ? 1 : 0;
    }
}
