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
 * CLI command to show queue status.
 */
class QueueStatusCommand extends Command {
    public function __construct() {
        parent::__construct('queue:status', [], 'Show pending and failed job counts.');
    }

    public function exec(): int {
        $pending = QueueFacade::getPendingCount();
        $failed = count(QueueFacade::getFailed());
        $this->println("Pending jobs: $pending");
        $this->println("Failed jobs: $failed");

        return 0;
    }
}
