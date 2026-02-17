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
 * Command for initializing migrations tracking table.
 * 
 * @author Ibrahim
 */
class InitMigrationsCommand extends Command {
    
    private ?SchemaRunner $runner = null;
    
    public function __construct() {
        parent::__construct('migrations:ini', [
            new Argument('--connection', 'The name of database connection to use.', true),
            new Argument('--env', 'Environment name (dev, staging, production). Default: dev', true),
        ], 'Create migrations tracking table.');
    }
    
    public function exec(): int {
        try {
            $connection = $this->getConnection();
            if ($connection === null) {
                return 1;
            }
            
            $env = $this->getArgValue('--env') ?? 'dev';
            $this->runner = new SchemaRunner($connection, $env);
            
            return $this->initTable();
            
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
}
