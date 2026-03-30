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
 * A CLI command for running the tasks scheduler check.
 *
 * Intended to be invoked by a cron job, e.g.:
 *   * * * * * php webfiori scheduler:run
 *
 * @author Ibrahim
 */
class SchedulerRunCommand extends Command {

    public function __construct() {
        parent::__construct('scheduler:run', [
            new Argument('--show-log', 'If set, execution log will be shown after execution is completed.', true),
        ], 'Run the tasks scheduler check.');

        if (TasksManager::getPassword() != 'NO_PASSWORD') {
            $this->addArg('p', [
                'optional' => false,
                'description' => 'Scheduler password.'
            ]);
        }
    }

    public function exec(): int {
        $count = count(TasksManager::getTasks());

        if ($count == 0) {
            $this->info('There are no scheduled tasks.');
            return 0;
        }

        $pass = CLIUtils::resolvePassword($this->getArgValue('p')) ?? '';
        $result = TasksManager::run($pass, null, false, $this);

        if ($result == 'INV_PASS') {
            $this->error('Provided password is incorrect.');
            return -1;
        }

        $this->printResult($result);
        return 0;
    }

    private function printResult(array $result): void {
        $this->println('Total number of tasks: ' . $result['total-tasks']);
        $this->println('Executed Tasks: ' . $result['executed-count']);

        $this->println('Successfully finished tasks:');
        if (count($result['successfully-completed']) == 0) {
            $this->println('    <NONE>');
        } else {
            foreach ($result['successfully-completed'] as $taskName) {
                $this->println('    ' . $taskName);
            }
        }

        $this->println('Failed tasks:');
        if (count($result['failed']) == 0) {
            $this->println('    <NONE>');
        } else {
            foreach ($result['failed'] as $taskName) {
                $this->println('    ' . $taskName);
            }
        }
    }
}
