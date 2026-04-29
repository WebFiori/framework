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

use WebFiori\Cli\Argument;
use WebFiori\Cli\Command;
use WebFiori\Framework\Cli\CLIUtils;
use WebFiori\Framework\Scheduler\TasksManager;

/**
 * A CLI command that runs the scheduler in a loop for a configurable duration.
 *
 * This command is intended for local development and testing. It calls
 * the scheduler check every 60 seconds and self-terminates after the
 * specified maximum number of minutes.
 *
 * Usage:
 *   php webfiori scheduler:daemon                  # runs for 60 minutes (default)
 *   php webfiori scheduler:daemon --max-minutes=5  # runs for 5 minutes
 *
 * For production environments, use `scheduler:run` with OS-level cron instead.
 *
 * @author Ibrahim
 */
class SchedulerDaemonCommand extends Command {
    /**
     * Creates a new instance of the command.
     *
     * Registers the `--max-minutes` argument with a default of 60 and
     * optionally the scheduler password argument if a password is configured.
     */
    public function __construct() {
        parent::__construct('scheduler:daemon', [
            new Argument('--max-minutes', 'Maximum number of minutes to keep the daemon running. Default: 60.', true),
            new Argument('--show-log', 'If set, execution log will be shown after each run.', true),
        ], 'Run the scheduler in a loop for a limited duration.');

        if (TasksManager::getPassword() != 'NO_PASSWORD') {
            $this->addArg('p', [
                'optional' => false,
                'description' => 'Scheduler password.'
            ]);
        }
    }

    /**
     * Executes the daemon loop.
     *
     * The scheduler check is invoked every 60 seconds. The loop exits
     * when the elapsed time exceeds the value of `--max-minutes`.
     *
     * @return int 0 on success, -1 if the password is incorrect or no tasks exist.
     */
    public function exec(): int {
        $count = count(TasksManager::getTasks());

        if ($count == 0) {
            $this->info('There are no scheduled tasks.');
            return -1;
        }

        $maxMinutes = $this->getArgValue('--max-minutes');
        $maxMinutes = $maxMinutes !== null ? intval($maxMinutes) : 60;

        if ($maxMinutes <= 0) {
            $this->error('--max-minutes must be a positive integer.');
            return -1;
        }

        $pass = CLIUtils::resolvePassword($this->getArgValue('p')) ?? '';
        $maxSeconds = $maxMinutes * 60;
        $startTime = time();

        $this->println("Scheduler daemon started. Will run for $maxMinutes minute(s).");
        $this->println('Press Ctrl+C to stop.');
        $this->println('---');

        while ((time() - $startTime) < $maxSeconds) {
            $this->println('[' . date('Y-m-d H:i:s') . '] Running scheduler check...');
            $result = TasksManager::run($pass, null, false, $this);

            if ($result == 'INV_PASS') {
                $this->error('Provided password is incorrect.');
                return -1;
            }

            $this->println('Executed: ' . $result['executed-count'] . '/' . $result['total-tasks'] . ' tasks.');

            $remaining = $maxSeconds - (time() - $startTime);

            if ($remaining <= 0) {
                break;
            }

            $sleepTime = min(60, $remaining);
            $this->println("Next check in $sleepTime second(s)...");
            $this->println('---');
            sleep($sleepTime);
        }

        $this->println('---');
        $this->success('Daemon stopped after ' . $maxMinutes . ' minute(s).');
        return 0;
    }
}
