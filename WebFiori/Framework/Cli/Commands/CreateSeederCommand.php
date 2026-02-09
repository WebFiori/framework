<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2025 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cli\Argument;
use WebFiori\Cli\Command;
use WebFiori\Cli\InputValidator;
use WebFiori\Framework\Writers\SeederClassWriter;

/**
 * A command which is used to create a seeder class.
 *
 * @author Ibrahim
 */
class CreateSeederCommand extends Command {
    public function __construct() {
        parent::__construct('create:seeder', [
            new Argument('--class-name', 'The name of the seeder class.', true),
            new Argument('--description', 'A description of what the seeder does.', true)
        ], 'Create a new database seeder class.');
    }
    
    public function exec(): int {
        $className = $this->getArgValue('--class-name');
        
        if ($className === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Class name cannot be empty.');
            
            $className = $this->getInput('Enter seeder class name:', null, $validator);
        } else if (empty(trim($className))) {
            $this->error('--class-name cannot be empty string.');
            
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Class name cannot be empty.');
            
            $className = $this->getInput('Enter seeder class name:', null, $validator);
        }
        
        $className = trim($className);
        
        $description = $this->getArgValue('--description');
        
        if ($description === null && $this->getArgValue('--class-name') === null) {
            // Only prompt if running interactively (no --class-name provided)
            $description = $this->getInput('Enter seeder description:', 'No description');
        } else if ($description === null) {
            $description = 'No description';
        }
        
        $writer = new SeederClassWriter($className, trim($description));
        $writer->writeClass();
        
        $this->success('Seeder class created at: '.$writer->getAbsolutePath());
        
        return 0;
    }
}
