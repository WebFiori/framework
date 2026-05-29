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

use WebFiori\Cli\Argument;
use WebFiori\Cli\Command;
use WebFiori\Queue\QueueFacade;

/**
 * CLI command to retry failed jobs or flush them.
 */
class QueueRetryCommand extends Command {
    public function __construct() {
        parent::__construct('queue:retry', [
            new Argument('--id', 'The ID of the failed job to retry.', true),
            new Argument('--all', 'Retry all failed jobs.', true),
            new Argument('--flush', 'Remove all failed jobs.', true),
        ], 'Retry or flush failed queue jobs.');
    }

    public function exec(): int {
        if ($this->isArgProvided('--flush')) {
            QueueFacade::flush();
            $this->println('All failed jobs have been removed.');

            return 0;
        }

        if ($this->isArgProvided('--all')) {
            $failed = QueueFacade::getFailed();
            $count = 0;

            foreach ($failed as $job) {
                QueueFacade::retry($job->getId());
                $count++;
            }
            $this->println("Retried $count failed jobs.");

            return 0;
        }

        $id = $this->getArgValue('--id');

        if ($id !== null) {
            QueueFacade::retry($id);
            $this->println("Job '$id' has been re-queued.");

            return 0;
        }

        $this->println('Please provide --id, --all, or --flush.');

        return 1;
    }
}
