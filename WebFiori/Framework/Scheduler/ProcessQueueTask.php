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
namespace WebFiori\Framework\Scheduler;

use WebFiori\Queue\QueueFacade;

/**
 * A scheduler task that processes pending queue jobs every minute.
 */
class ProcessQueueTask extends BaseTask {
    public function __construct() {
        parent::__construct('process-queue');
        $this->everyMinute();
    }
    /**
     * Process up to 50 pending jobs.
     */
    public function execute(): void {
        QueueFacade::process(50);
    }

    public function afterExec(): void {
    }

    public function onFail(): void {
    }
}
