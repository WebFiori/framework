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

use WebFiori\Cli\Command;
use WebFiori\Framework\Router\ServiceRouter;

/**
 * CLI command to list all discovered services.
 *
 * @author Ibrahim
 */
class ServicesListCommand extends Command {
    public function __construct() {
        parent::__construct('services:list', [], 'List all auto-discovered API services.');
    }

    public function exec(): int {
        $discovered = ServiceRouter::getDiscovered();

        if (empty($discovered)) {
            $this->info('No services discovered. Use ServiceRouter::discover() to register services.');

            return 0;
        }

        $this->println('');
        $this->println(sprintf('  %-15s %-45s %-10s %s', 'Name', 'Class', 'Type', 'Path'));
        $this->println(str_repeat('-', 90));

        foreach ($discovered as $name => $entry) {
            $this->println(sprintf(
                '  %-15s %-45s %-10s %s',
                $name,
                $entry['class'],
                $entry['type'],
                $entry['path']
            ));
        }

        $this->println('');
        $this->info('Total: ' . count($discovered) . ' service(s).');

        return 0;
    }
}
