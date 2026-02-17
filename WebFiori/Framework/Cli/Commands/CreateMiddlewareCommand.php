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
use WebFiori\Framework\Writers\MiddlewareClassWriter;

/**
 * A command which is used to create a middleware class.
 *
 * @author Ibrahim
 *
 */
class CreateMiddlewareCommand extends Command {
    public function __construct() {
        parent::__construct('create:middleware', [
            new Argument('--class-name', 'The name of the middleware class.', true),
            new Argument('--name', 'The display name of the middleware.', true),
            new Argument('--priority', 'The priority of the middleware (lower = higher priority).', true),
            new Argument('--groups', 'Comma-separated list of groups to add the middleware to.', true)
        ], 'Create a new middleware class.');
    }
    private function getPriority() : int {
        $priority = $this->getArgValue('--priority');
        
        if ($priority === null) {
            $validator = new InputValidator(function($input) {
                return is_numeric($input);
            }, 'Priority must be a number.');
            
            $priority = (int)$this->getInput('Enter middleware priority:', 0, $validator);
        } else {
            if (!is_numeric($priority)) {
                $this->error('Priority must be a number.');
                return -1;
            }
            $priority = (int)$priority;
        }
        return $priority;
    }
    public function getGroups() : array {
        $groupsArg = $this->getArgValue('--groups');
        $groups = [];

        if ($groupsArg !== null) {
            if (!empty($groupsArg)) {
                $groups = array_map('trim', explode(',', $groupsArg));
                $groups = array_filter($groups, fn($g) => !empty($g));
            }
        } else {
            if ($this->confirm('Add middleware to groups?', false)) {
                while (true) {
                    $group = $this->getInput('Enter group name (leave empty to finish):');
                    if (empty(trim($group))) {
                        break;
                    }
                    $groups[] = trim($group);
                }
            }
        }
        return $groups;
    }
    private function getMDName(string $className) : string {
        $middlewareName = $this->getArgValue('--name');
        
        if ($middlewareName === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Middleware name cannot be empty.');
            
            $middlewareName = $this->getInput('Enter middleware name:', $className, $validator);
        }
        return $middlewareName;
    }
    /**
     * Execute the command.
     *
     * @return int
     */
    public function exec() : int {
        $className = $this->getArgValue('--class-name');
        if ($className !== null && strlen($className) == 0) {
            $this->error('--class-name cannot be empty string.');
            $className = null;
        }
        if ($className === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Class name cannot be empty.');
            
            $className = trim($this->getInput('Enter middleware class name:', null, $validator));
        }
        

        
        $middlewareName = $this->getMDName($className);
        $priority = $this->getPriority();
        
        if ($priority === -1) {
            return -1;
        }
        
        $groups = $this->getGroups();
        

        $writer = new MiddlewareClassWriter($middlewareName, $priority, $groups);
        $writer->setClassName($className);
        $writer->writeClass();

        $this->success('Middleware class created at: '.$writer->getAbsolutePath());
        
        return 0;
    }
}
