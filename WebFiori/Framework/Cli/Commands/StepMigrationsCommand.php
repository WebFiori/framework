<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cli\Argument;
use WebFiori\Cli\Attributes\SingleInstance;
use WebFiori\Cli\Command;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Schema\SchemaRunner;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLIUtils;

/**
 * Command for stepping through migrations one at a time with user confirmation.
 *
 * @author Ibrahim
 */
#[SingleInstance]
class StepMigrationsCommand extends Command {
    public function __construct() {
        parent::__construct('migrations:step', [
            new Argument('--connection', 'The name of database connection to use.', true),
            new Argument('--env', 'Environment name (dev, staging, production). Default: dev', true),
        ], 'Interactively apply or skip migrations one at a time.');
    }

    public function exec(): int {
        $connection = $this->getConnection();

        if ($connection === null) {
            return 1;
        }

        $env = $this->getArgValue('--env') ?? 'dev';
        $runner = new SchemaRunner($connection, $env);
        $runner->discoverFromPath(APP_PATH.'Database'.DS.'Migrations', APP_DIR.'\\Database\\Migrations', true);

        $pending = $runner->getPendingChanges(true);

        if (empty($pending)) {
            $this->info('No pending migrations.');

            return 0;
        }

        $applied = 0;
        $skipped = 0;

        foreach ($pending as $item) {
            $change = $item['change'];
            $queries = $item['queries'];

            $this->println('');
            $this->println('Migration: '.$change->getName());
            $this->println('');

            if (!empty($queries)) {
                $this->println('SQL:');

                foreach ($queries as $q) {
                    $this->println('  '.$q);
                }

                $this->println('');
            }

            $action = $this->select('Action:', ['Apply', 'Skip', 'Quit'], 0);

            if ($action === 'Apply') {
                $runner->applyOne();
                $applied++;
                $this->success('Applied: '.$change->getName());
            } else if ($action === 'Skip') {
                $runner->skip($change);
                $skipped++;
                $this->warning('Skipped: '.$change->getName());
            } else {
                break;
            }
        }

        $this->println('');
        $this->info("Summary: $applied applied, $skipped skipped.");

        return 0;
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
}
