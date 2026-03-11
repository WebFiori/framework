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
 * Command for showing migration status.
 * 
 * @author Ibrahim
 */
class MigrationsStatusCommand extends Command {
    
    private ?SchemaRunner $runner = null;
    
    public function __construct() {
        parent::__construct('migrations:status', [
            new Argument('--connection', 'The name of database connection to use.', true),
            new Argument('--env', 'Environment name (dev, staging, production). Default: dev', true),
        ], 'Show migration status (applied and pending).');
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
            
            return $this->showStatus();
            
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
    
    private function showStatus(): int {
        $allChanges = $this->runner->getChanges();
        $pending = $this->runner->getPendingChanges(false);
        
        // Separate applied and pending
        $pendingNames = array_map(fn($item) => $item['change']->getName(), $pending);
        $applied = array_filter($allChanges, fn($change) => !in_array($change->getName(), $pendingNames));
        
        if (!empty($applied)) {
            $this->println('Applied migrations:');
            foreach ($applied as $change) {
                $this->success('  - ' . $change->getName());
            }
        } else {
            $this->info('No applied migrations.');
        }
        
        $this->println('');
        
        if (!empty($pending)) {
            $this->println('Pending migrations:');
            foreach ($pending as $item) {
                $this->warning('  - ' . $item['change']->getName());
            }
        } else {
            $this->info('No pending migrations.');
        }
        
        return 0;
    }
}
