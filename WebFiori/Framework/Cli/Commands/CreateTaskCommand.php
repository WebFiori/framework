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
use WebFiori\Cli\InputValidator;
use WebFiori\Framework\Scheduler\TaskArgument;
use WebFiori\Framework\Writers\SchedulerTaskClassWriter;

/**
 * A command which is used to create a scheduler task class.
 *
 * @author Ibrahim
 *
 */
class CreateTaskCommand extends Command {
    public function __construct() {
        parent::__construct('create:task', [
            new Argument('--class-name', 'The name of the task class.', true),
            new Argument('--name', 'The name of the task.', true),
            new Argument('--description', 'A description of what the task does.', true),
            new Argument('--args', 'JSON string of task arguments. Format: [{"name":"arg1","description":"desc","default":"val"}]', true)
        ], 'Create a new scheduler task class.');
    }
    private function getTaskName(string $className) : string {
        $taskName = $this->getArgValue('--name');
        
        if ($taskName === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Task name cannot be empty.');
            
            $taskName = $this->getInput('Enter task name:', $className, $validator);
        }
        return $taskName;
    }
    private function getTaskDescription() : string {
        $description = $this->getArgValue('--description');
        
        if ($description === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Task description cannot be empty.');
            
            $description = $this->getInput('Enter task description:', 'No Description', $validator);
        }
        return $description;
    }
    private function getTaskArguments() : array {
        $args = [];
        $argsJson = $this->getArgValue('--args');
        
        if ($argsJson !== null) {
            // Parse JSON arguments
            $argsData = json_decode($argsJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON format for --args parameter.');
                return $args;
            }
            
            if (is_array($argsData)) {
                foreach ($argsData as $argData) {
                    if (isset($argData['name'])) {
                        $taskArg = new TaskArgument(
                            $argData['name'],
                            $argData['description'] ?? 'No description'
                        );
                        
                        if (isset($argData['default'])) {
                            $taskArg->setDefault($argData['default']);
                        }
                        
                        $args[] = $taskArg;
                    }
                }
            }
        } elseif ($this->getArgValue('--class-name') === null) {
            // Only prompt if running interactively (no --class-name provided)
            if ($this->confirm('Add execution arguments to the task?', false)) {
                while (true) {
                    $argName = $this->getInput('Enter argument name (leave empty to finish):');
                    if (empty(trim($argName))) {
                        break;
                    }
                    
                    $argDesc = $this->getInput('Enter argument description:', 'No description');
                    $argDefault = $this->getInput('Enter default value (leave empty for none):');
                    
                    $taskArg = new TaskArgument(trim($argName), trim($argDesc));
                    if (!empty(trim($argDefault))) {
                        $taskArg->setDefault(trim($argDefault));
                    }
                    
                    $args[] = $taskArg;
                }
            }
        }
        
        return $args;
    }
    /**
     * Execute the command.
     *
     * @return int
     */
    public function exec() : int {
        $className = $this->getArgValue('--class-name');
        
        if ($className === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Class name cannot be empty.');
            
            $className = $this->getInput('Enter task class name:', null, $validator);
        }
        
        $className = trim($className);
        
        if (empty($className)) {
            $this->error('Class name cannot be empty.');
            return -1;
        }

        $taskName = $this->getTaskName($className);
        $description = $this->getTaskDescription();
        $args = $this->getTaskArguments();

        $writer = new SchedulerTaskClassWriter($className, $taskName, $description, $args);
        $writer->writeClass();

        $this->success('Task class created at: '.$writer->getAbsolutePath());
        
        return 0;
    }
}
