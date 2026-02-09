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
class RunMigrationsCommandNew extends Command {
    
    private ?SchemaRunner $runner = null;
    
    public function __construct() {
        parent::__construct('migrations:run', [
            new Argument('--connection', 'The name of database connection to use.', true),
            new Argument('--env', 'Environment name (dev, staging, production). Default: dev', true),
        ], 'Execute pending database migrations.');
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
            
            if ($count === 0) {
                $this->info('No migrations found.');
                return 0;
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
