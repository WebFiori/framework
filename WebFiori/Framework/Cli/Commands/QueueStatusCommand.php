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

use WebFiori\Cli\Attributes\Group;
use WebFiori\Cli\Command;
use WebFiori\Queue\QueueFacade;

/**
 * CLI command to show queue status.
 *
 * Displays pending job count, failed job count, and — when the storage
 * backend supports listing (ListableQueueStorage) — a detailed table of
 * pending jobs sorted by priority.
 */
#[Group('queue')]
class QueueStatusCommand extends Command {
    public function __construct() {
        parent::__construct('queue:status', [], 'Show pending and failed job counts.');
    }

    public function exec(): int {
        $pendingCount = QueueFacade::getPendingCount();
        $failedCount = count(QueueFacade::getFailed());

        $this->println("Pending jobs : $pendingCount");
        $this->println("Failed jobs  : $failedCount");

        if ($pendingCount === 0) {
            return 0;
        }

        // Attempt detailed listing — requires ListableQueueStorage backend.
        try {
            $jobs = QueueFacade::getPending();

            if (empty($jobs)) {
                return 0;
            }

            $this->println('');
            $this->println('Pending job details (sorted by priority, highest first):');
            $this->println(str_repeat('-', 72));
            $this->println(sprintf('%-36s %8s %8s %s', 'ID', 'Priority', 'Attempts', 'Available At'));
            $this->println(str_repeat('-', 72));

            foreach ($jobs as $job) {
                $availableAt = $job->getAvailableAt() > 0
                    ? date('Y-m-d H:i:s', $job->getAvailableAt())
                    : 'now';

                $this->println(sprintf(
                    '%-36s %8d %8d %s',
                    substr($job->getId(), 0, 36),
                    $job->getPriority(),
                    $job->getAttempts(),
                    $availableAt
                ));
            }

            $this->println(str_repeat('-', 72));
        } catch (\LogicException $e) {
            // Storage backend does not support listing — count-only is fine.
            $this->println('(Detailed listing not available for this storage backend.)');
        }

        return 0;
    }
}
