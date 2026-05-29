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

use WebFiori\Cli\Command;
use WebFiori\Queue\QueueFacade;

/**
 * CLI command to process queue jobs continuously.
 */
class QueueWorkCommand extends Command {
    public function __construct() {
        parent::__construct('queue:work', [], 'Process queue jobs continuously.');
    }

    public function exec(): int {
        $this->println('Processing queue jobs. Press Ctrl+C to stop.');

        while (true) {
            $processed = QueueFacade::process(10);

            if ($processed > 0) {
                $this->println("Processed $processed jobs.");
            }

            sleep(1);
        }
    }
}
