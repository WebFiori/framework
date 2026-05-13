<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026-present WebFiori Framework
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
 * Command for skipping (baselining) database migrations without executing them.
 * 
 * @author Ibrahim
 */
class SkipMigrationsCommand extends Command {
    
    private ?SchemaRunner $runner = null;
    
    public function __construct() {
        parent::__construct('migrations:skip', [
            new Argument('--connection', 'The name of database connection to use.', true),
            new Argument('--env', 'Environment name (dev, staging, production). Default: dev', true),
            new Argument('--name', 'Fully qualified class name of a single migration to skip.', true),
            new Argument('--all', 'Skip all pending migrations.', true),
            new Argument('--up-to', 'Skip all migrations up to and including the named one.', true),
        ], 'Mark migrations as applied without executing them (baseline).');
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
            $count = $this->runner->discoverFromPath($migrationsPath, $namespace, true);

            $seedersPath = APP_PATH.'Database'.DS.'Seeders';
            $seedersNamespace = APP_DIR.'\\Database\\Seeders';
            $count += $this->runner->discoverFromPath($seedersPath, $seedersNamespace, true);
            
            if ($count === 0) {
                $this->info('No migrations found.');
                return 0;
            }
            
            return $this->skip();
            
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, ".schema_changes' doesn't exist") && $e->getCode() == 1146) {
                $this->warning('Table "schema_changes" does not exist.');
                $this->info('Run "migrations:ini" to create the table.');
                return 1;
            }
            $this->error('An exception was thrown.');
            $this->println('Message: ' . $e->getMessage());
            $this->println('File: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        } finally {
            if ($this->runner !== null) {
                $this->runner->close();
            }
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
    
    private function skip(): int {
        if ($this->isArgProvided('--all')) {
            return $this->skipAll();
        } else if ($this->isArgProvided('--name')) {
            return $this->skipSingle();
        } else if ($this->isArgProvided('--up-to')) {
            return $this->skipUpTo();
        }
        
        $this->error('Provide --name, --all, or --up-to.');
        return 1;
    }
    
    private function skipAll(): int {
        $skipped = $this->runner->skipAll();
        
        if (empty($skipped)) {
            $this->info('No pending migrations to skip.');
            return 0;
        }
        
        foreach ($skipped as $change) {
            $this->success('Skipped: ' . $change->getName());
        }
        $this->info('Total skipped: ' . count($skipped));
        
        return 0;
    }
    
    private function skipSingle(): int {
        $name = $this->getArgValue('--name');
        $result = $this->runner->skip($name);
        
        if ($result) {
            $this->success('Skipped: ' . $name);
            return 0;
        }
        
        $this->warning('Could not skip: ' . $name . ' (not found or already applied)');
        return 1;
    }
    
    private function skipUpTo(): int {
        $name = $this->getArgValue('--up-to');
        $skipped = $this->runner->skipUpTo($name);
        
        if (empty($skipped)) {
            $this->info('No pending migrations to skip.');
            return 0;
        }
        
        foreach ($skipped as $change) {
            $this->success('Skipped: ' . $change->getName());
        }
        $this->info('Total skipped: ' . count($skipped));
        
        return 0;
    }
}
