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
use WebFiori\Framework\Writers\CommandClassWriter;

/**
 * A command which is used to create a CLI command class.
 *
 * @author Ibrahim
 *
 */
class CreateCommandCommand extends Command {
    public function __construct() {
        parent::__construct('create:command', [
            new Argument('--class-name', 'The name of the command class.', true),
            new Argument('--name', 'The name of the command (used to execute it).', true),
            new Argument('--description', 'A description of what the command does.', true),
            new Argument('--args', 'JSON string of command arguments. Format: [{"name":"arg1","description":"desc","optional":true,"values":["val1"]}]', true)
        ], 'Create a new CLI command class.');
    }
    private function getCommandName(string $className) : string {
        $commandName = $this->getArgValue('--name');
        
        if ($commandName === null) {
            $validator = new InputValidator(function($input) {
                $trimmed = trim($input);
                return !empty($trimmed) && strpos($trimmed, ' ') === false;
            }, 'Command name cannot be empty or contain spaces.');
            
            $commandName = $this->getInput('Enter command name:', strtolower($className), $validator);
        }
        return $commandName;
    }
    private function getCommandDescription() : string {
        $description = $this->getArgValue('--description');
        
        if ($description === null) {
            $description = $this->getInput('Enter command description:', '');
        }
        return $description;
    }
    private function getCommandArguments() : array {
        $args = [];
        $argsJson = $this->getArgValue('--args');
        
        if ($argsJson !== null) {
            $argsData = json_decode($argsJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON format for --args parameter.');
                return $args;
            }
            
            if (is_array($argsData)) {
                foreach ($argsData as $argData) {
                    if (isset($argData['name'])) {
                        $arg = new Argument(
                            $argData['name'],
                            $argData['description'] ?? '',
                            $argData['optional'] ?? true
                        );
                        
                        if (isset($argData['values']) && is_array($argData['values'])) {
                            foreach ($argData['values'] as $val) {
                                $arg->addAllowedValue($val);
                            }
                        }
                        
                        $args[] = $arg;
                    }
                }
            }
        } elseif ($this->getArgValue('--class-name') === null) {
            if ($this->confirm('Add arguments to the command?', false)) {
                while (true) {
                    $argName = $this->getInput('Enter argument name (leave empty to finish):');
                    if (empty(trim($argName))) {
                        break;
                    }
                    
                    $argDesc = $this->getInput('Enter argument description:', '');
                    $isOptional = $this->confirm('Is this argument optional?', true);
                    
                    $arg = new Argument(trim($argName), trim($argDesc), $isOptional);
                    
                    if ($this->confirm('Add allowed values for this argument?', false)) {
                        while (true) {
                            $value = $this->getInput('Enter allowed value (leave empty to finish):');
                            if (empty(trim($value))) {
                                break;
                            }
                            $arg->addAllowedValue(trim($value));
                        }
                    }
                    
                    $args[] = $arg;
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
            
            $className = $this->getInput('Enter command class name:', null, $validator);
        }
        
        $className = trim($className);
        
        if (empty($className)) {
            $this->error('Class name cannot be empty.');
            return -1;
        }

        $commandName = $this->getCommandName($className);
        
        if (empty(trim($commandName)) || strpos($commandName, ' ') !== false) {
            $this->error('Command name cannot be empty or contain spaces.');
            return -1;
        }
        
        $description = $this->getCommandDescription();
        $args = $this->getCommandArguments();

        $writer = new CommandClassWriter();
        $writer->setClassName($className);
        $writer->setCommandName($commandName);
        $writer->setCommandDescription($description);
        $writer->setArgs($args);
        $writer->writeClass();

        $this->success('Command class created at: '.$writer->getAbsolutePath());
        
        return 0;
    }
}
