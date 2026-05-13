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
            new Argument('--all-connections', 'Run migrations against all registered connections.', true),
        ], 'Execute pending database migrations.');
    }

    public function exec(): int {
        if ($this->isArgProvided('--all-connections') && $this->isArgProvided('--connection')) {
            $this->error('Cannot use --all-connections and --connection together.');

            return 1;
        }

        if ($this->isArgProvided('--all-connections')) {
            return $this->runAllConnections();
        }

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

            $this->validateTargetConnections($this->runner, App::getConfig()->getDBConnections());

            return $this->runMigrations();
        } catch (Throwable $e) {
            $msg = $e->getMessage();

            if ((str_contains($msg, ".schema_changes' doesn't exist") && $e->getCode() == 1146)) {
                $this->warning('Table "schema_changes" does not exist. No migrations executed.');
                $this->info('Run "migrations:ini" to create the table.');

                return 1;
            }
            $this->error('An exception was thrown.');
            $this->println('Message: '.$e->getMessage());
            $this->println('File: '.$e->getFile().':'.$e->getLine());

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

    private function runAllConnections(): int {
        $connections = App::getConfig()->getDBConnections();

        if (empty($connections)) {
            $this->info('No database connections configured.');

            return 0;
        }

        $env = $this->getArgValue('--env') ?? 'dev';
        $hasFailure = false;

        foreach ($connections as $name => $connection) {
            $this->println('');
            $this->println('=== Connection: '.$name.' ===');

            try {
                $runner = new SchemaRunner($connection, $env);

                $migrationsPath = APP_PATH.'Database'.DS.'Migrations';
                $namespace = APP_DIR.'\\Database\\Migrations';
                $count = $runner->discoverFromPath($migrationsPath, $namespace, true);

                $seedersPath = APP_PATH.'Database'.DS.'Seeders';
                $seedersNamespace = APP_DIR.'\\Database\\Seeders';
                $count += $runner->discoverFromPath($seedersPath, $seedersNamespace, true);

                if ($count === 0) {
                    $this->info('No migrations found.');
                    $runner->close();
                    continue;
                }

                $this->validateTargetConnections($runner, $connections);

                $runner->createSchemaTable();
                $result = $runner->apply();

                foreach ($result->getApplied() as $change) {
                    $this->success('Applied: '.$change->getName());
                }

                foreach ($result->getSkipped() as $item) {
                    $this->warning('Skipped: '.$item['change']->getName().' ('.$item['reason'].')');
                }

                foreach ($result->getFailed() as $item) {
                    $this->error('Failed: '.$item['change']->getName());
                    $this->println('  Error: '.$item['error']->getMessage());
                    $hasFailure = true;
                }

                $applied = count($result->getApplied());
                $this->info("Applied: $applied change(s). Time: ".round($result->getTotalTime(), 2).'ms');

                $runner->close();
            } catch (Throwable $e) {
                $this->error('Error on connection '.$name.': '.$e->getMessage());
                $hasFailure = true;
            }
        }

        return $hasFailure ? 1 : 0;
    }

    private function runMigrations(): int {
        $this->println('Running migrations...');

        $result = $this->runner->apply();

        $applied = $result->getApplied();

        if (!empty($applied)) {
            foreach ($applied as $change) {
                $this->success('Applied: '.$change->getName());
            }
        }

        $skipped = $result->getSkipped();

        if (!empty($skipped)) {
            foreach ($skipped as $item) {
                $this->warning('Skipped: '.$item['change']->getName().' ('.$item['reason'].')');
            }
        }

        $failed = $result->getFailed();

        if (!empty($failed)) {
            foreach ($failed as $item) {
                $this->error('Failed: '.$item['change']->getName());
                $this->println('  Error: '.$item['error']->getMessage());
            }
        }

        $migrationsCount = count(array_filter($result->getApplied(), fn($c) => $c->getType() === 'migration'));
        $seedersCount = count(array_filter($result->getApplied(), fn($c) => $c->getType() === 'seeder'));

        if ($migrationsCount > 0) {
            $this->info('Applied: '.$migrationsCount.' migration(s)');
        }

        if ($seedersCount > 0) {
            $this->info('Applied: '.$seedersCount.' seeder(s)');
        }

        if ($migrationsCount === 0 && $seedersCount === 0) {
            $this->info('Applied: 0 migrations');
        }

        $this->info('Time: '.round($result->getTotalTime(), 2).'ms');

        return !empty($failed) ? 1 : 0;
    }

    private function validateTargetConnections(SchemaRunner $runner, array $registeredConnections): void {
        $registeredNames = array_keys($registeredConnections);
        $currentName = $runner->getConnectionInfo()->getName();

        $hasTargeted = false;

        foreach ($runner->getChanges() as $change) {
            $targets = $change->getTargetConnections();

            if (!empty($targets)) {
                $hasTargeted = true;

                foreach ($targets as $target) {
                    if (!in_array($target, $registeredNames)) {
                        $this->warning('Migration '.$change->getName().' targets unknown connection: '.$target);
                    }
                }
            }
        }

        if ($hasTargeted && $currentName === 'New_Connection') {
            $this->warning('Connection has default name "New_Connection". Connection-targeted migrations may not filter correctly. Set a name via ConnectionInfo::setName().');
        }
    }
}
