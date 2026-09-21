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
use WebFiori\Cli\Attributes\SingleInstance;
use WebFiori\Cli\Command;
use WebFiori\Error\Handler;
use WebFiori\Queue\Job;
use WebFiori\Queue\QueueFacade;

/**
 * CLI command to process queue jobs continuously.
 *
 * Job processing exceptions are forwarded to the globally registered error
 * handler (webfiori/err) so background job failures are visible in the same
 * centralized error channel as HTTP and CLI failures.
 */
#[SingleInstance]
#[Group('queue')]
class QueueWorkCommand extends Command {
    public function __construct() {
        parent::__construct('queue:work', [], 'Process queue jobs continuously.');
    }

    public function exec(): int {
        $this->println('Processing queue jobs. Press Ctrl+C to stop.');

        // Bridge job failures into the framework's centralized error handler.
        // The callback fires for every caught Throwable — both terminal failures
        // and intermediate retries — so all background job errors are visible
        // in the same channel as HTTP/CLI errors.
        QueueFacade::setOnError(function (?Job $job, \Throwable $e, int $attempts, bool $willRetry): void
        {
            $class = $job !== null ? get_class($job) : 'InvalidJobPayload';
            $status = $willRetry ? "retry $attempts" : "terminal (attempt $attempts)";
            $this->error("Job [$class] failed ($status): ".$e->getMessage());
            Handler::get()->invokeExceptionsHandler($e);
        });

        while (true) {
            $processed = QueueFacade::process(10);

            if ($processed > 0) {
                $this->println("Processed $processed jobs.");
            }

            sleep(1);
        }
    }
}
