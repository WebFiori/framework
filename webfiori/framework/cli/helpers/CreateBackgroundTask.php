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
namespace webfiori\framework\cli\helpers;

use InvalidArgumentException;
use WebFiori\Cli\InputValidator;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\scheduler\BaseTask;
use webfiori\framework\scheduler\TaskArgument;
use webfiori\framework\writers\SchedulerTaskClassWriter;
/**
 * A helper class which is used to help in creating scheduler tasks classes using CLI.
 *
 * @author Ibrahim
 *
 * @version 1.0
 */
class CreateBackgroundTask extends CreateClassHelper {
    /**
     * @var SchedulerTaskClassWriter
     */
    private $taskWriter;
    /**
     * Creates new instance of the class.
     *
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        parent::__construct($command, new SchedulerTaskClassWriter());
        $this->taskWriter = $this->getWriter();
    }
    public function readClassInfo() {
        $this->setClassInfo(APP_DIR.'\\tasks', 'Task');
        $taskName = $this->getTaskName();
        $taskDesc = $this->getTaskDesc();

        if ($this->confirm('Would you like to add arguments to the task?', false)) {
            $this->getArgsHelper();
        }

        $this->taskWriter->setTaskName($taskName);
        $this->taskWriter->setTaskDescription($taskDesc);

        $this->writeClass();
    }
    private function getArgsHelper() {
        $addToMore = true;

        while ($addToMore) {
            try {
                $argObj = new TaskArgument($this->getInput('Enter argument name:'));
                $argObj->setDescription($this->getInput('Describe the use of the argument:', ''));
                $argObj->setDefault($this->getInput('Default value:', ''));

                $this->taskWriter->addArgument($argObj);
            } catch (InvalidArgumentException $ex) {
                $this->error($ex->getMessage());
            }
            $addToMore = $this->confirm('Would you like to add more arguments?', false);
        }
    }
    private function getTaskDesc(): string {
        return $this->getInput('Provide short description of what does the task will do:', null, new InputValidator(function ($val)
        {
            if (strlen($val) > 0) {
                return true;
            }

            return false;
        }));
    }
    private function getTaskName() : string {
        return $this->getInput('Enter a name for the task:', null, new InputValidator(function ($val)
        {
            $temp = new BaseTask();

            if ($temp->setTaskName($val)) {
                return true;
            }

            return false;
        }, 'Provided name is invalid!'));
    }
}
