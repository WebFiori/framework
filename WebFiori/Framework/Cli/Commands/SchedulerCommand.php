<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cli\Argument;
use WebFiori\Cli\Command;
use WebFiori\Framework\Scheduler\AbstractTask;
use WebFiori\Framework\Scheduler\TasksManager;
/**
 * A CLI command which is related to executing
 * background tasks or performing operations on them.
 *
 * @author Ibrahim
 * @version 1.0
 */
class SchedulerCommand extends Command {
    /**
     * Creates new instance of the class.
     * The command will have name '--scheduler'. This command is used to
     * perform operations on background tasks. In addition to that,
     * it will have the following arguments:
     * <ul>
     * <li><b>p</b>: Cron password.</li>
     * <li><b>check</b>: Run check if it is time to execute a task.</li>
     * <li><b>force</b>: Force execution of a task given its name.</li>
     * <li><b>task-name</b>: The task that will be forced to execute or
     * its arguments will be shown.</li>
     * <li><b>show-task-args</b>: Show arguments of a task.</li>
     * <li><b>show-log</b>: Display execution log after execution is finished.</li>
     * </ul>
     */
    public function __construct() {
        parent::__construct('scheduler', [
            new Argument('p', 'Scheduler password. If it is set, then it must be provided here.', true),
            new Argument('--list', 'List all scheduled tasks.', true),
            new Argument('--check', 'Run a check against all tasks to check if it is time to execute them or not.', true),
            new Argument('--force', 'Force a specific task to execute.', true),
            new Argument('--task-name', 'The name of the task that will be forced to execute or to show its arguments.', true),
            new Argument('--show-task-args', 'If this one is provided with task name and a task has custom execution args, they will be shown.', true),
            new Argument('--show-log', 'If set, execution log will be shown after execution is completed.', true),
        ], 'Run tasks scheduler.');

        if (TasksManager::getPassword() != 'NO_PASSWORD') {
            $this->addArg('p', [
                'optional' => false,
                'description' => 'Scheduler password.'
            ]);
        }
    }
    /**
     * Execute the command.
     * @return int If the command executed without any errors, the
     * method will return 0. Other than that, it will return false.
     * @since 1.0
     */
    public function exec() : int {
        $retVal = -1;
        $count = count(TasksManager::getTasks());
        if ($count == 0) {
            $this->info("There are no scheduled tasks.");
            $retVal = 0;
        } else if ($this->isArgProvided('--list')) {
            $this->listTasks();
            $retVal = 0;
        } else if ($this->isArgProvided('--check')) {
            $pass = $this->getArgValue('p');

            if ($pass !== null) {
                $result = TasksManager::run($pass, null, false, $this);

                if ($result == 'INV_PASS') {
                    $this->error("Provided password is incorrect");
                } else {
                    $this->printExcResult($result);
                    $retVal = 0;
                }
            } else {
                $this->error("The argument 'p' is missing. It must be provided if scheduler password is set.");
            }
        } else if ($this->isArgProvided('--force')) {
            $retVal = $this->force();
        } else if ($this->isArgProvided('--show-task-args')) {
            $this->showTaskArgs();
            $retVal = 0;
        } else {
            $this->info("At least one of the options '--check', '--force' or '--show-task-args' must be provided.");
        }

        return $retVal;
    }
    public function listTasks() {
        $tasksQueue = TasksManager::tasksQueue();
        $i = 1;
        $this->println("Number Of Scheduled Tasks: ".$tasksQueue->size());

        while ($task = $tasksQueue->dequeue()) {
            $num = $i < 10 ? '0'.$i : $i;
            $this->println("--------- Task #$num ---------", [
                'color' => 'light-blue',
                'bold' => true
            ]);
            $this->println("Task Name %".(18 - strlen('Task Name'))."s %s",[], ":",$task->getTaskName());
            $this->println("Cron Expression %".(18 - strlen('Cron Expression'))."s %s",[],":",$task->getExpression());
            $i++;
        }
    }
    private function checkTaskArgs($taskName) {
        $task = TasksManager::getTask($taskName);

        if ($task === null) {
            return;
        }
        $args = $task->getExecArgsNames();

        if (count($args) != 0 && $this->confirm('Would you like to customize execution arguments?', false)) {
            $this->setArgs($args, $task);
        }
    }
    private function force(): int {
        $taskName = $this->getArgValue('--task-name');
        $cPass = $this->getArgValue('p').'';
        $retVal = -1;
        $tasksNamesArr = TasksManager::getTasksNames();
        $tasksNamesArr[] = 'Cancel';

        if ($taskName === null) {
            $taskName = $this->select('Select one of the scheduled tasks to force:', $tasksNamesArr, count($tasksNamesArr) - 1);
        }

        if ($taskName == 'Cancel') {
            $retVal = 0;
        } else {
            $this->checkTaskArgs($taskName);
            $result = TasksManager::run($cPass,$taskName.'',true, $this);

            if ($result == 'INV_PASS') {
                $this->error("Provided password is incorrect.");
            } else if ($result == 'TASK_NOT_FOUND') {
                $this->error("No task was found which has the name '".$taskName."'");
            } else {
                $this->printExcResult($result);
                $retVal = 0;
            }
        }

        return $retVal;
    }
    private function printExcResult($result) {
        $this->println("Total number of tasks: ".$result['total-tasks']);
        $this->println("Executed Tasks: ".$result['executed-count']);
        $this->println("Successfully finished tasks:");
        $sTasks = $result['successfully-completed'];

        if (count($sTasks) == 0) {
            $this->println("    <NONE>");
        } else {
            foreach ($sTasks as $taskName) {
                $this->println("    ".$taskName);
            }
        }
        $this->println("Failed tasks:");
        $fTasks = $result['failed'];

        if (count($fTasks) == 0) {
            $this->println("    <NONE>");
        } else {
            foreach ($fTasks as $taskName) {
                $this->println("    ".$taskName);
            }
        }
    }
    private function setArgs($argsArr, AbstractTask $task) {
        $setArg = true;
        $index = 0;
        $count = count($argsArr);

        do {
            $val = $this->getInput('Enter a value for the argument "'.$argsArr[$index].'":', '');

            if (strlen($val) != 0) {
                $task->getArgument($argsArr[$index])->setValue($val);
            }

            if ($index + 1 == $count) {
                $setArg = false;
            }
            $index++;
        } while ($setArg);
    }
    private function showTaskArgs() {
        $taskName = $this->getArgValue('--task-name');

        if ($taskName === null) {
            $taskName = $this->select('Select one of the scheduled tasks to show supported args:', TasksManager::getTasksNames());
        }
        $task = TasksManager::getTask($taskName);

        $this->println("Task Args:");
        $customArgs = $task->getArguments();

        if (count($customArgs) != 0) {
            foreach ($customArgs as $argObj) {
                $this->println("    %s: %s", $argObj->getName(), $argObj->getDescription());
            }
        } else {
            $this->println("    <NO ARGS>");
        }
    }
}
