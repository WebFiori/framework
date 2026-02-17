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
namespace WebFiori\Framework\Cli\Helpers;

use WebFiori\Database\Schema\DatabaseChangeGenerator;
use WebFiori\Database\Schema\GeneratorOption;
use WebFiori\Framework\Cli\CLIUtils;
use WebFiori\Framework\Cli\Commands\CreateCommand;
/**
 * A helper class which is used to help in creating migration classes using CLI.
 *
 * @author Ibrahim
 *
 * @version 1.0
 */
class CreateMigration {
    private $command;
    private $generator;
    private $className;
    private $dependencies = [];
    
    /**
     * Creates new instance of the class.
     *
     * @param CreateCommand $command A command that is used to call the class.
     */
    public function __construct(CreateCommand $command) {
        $this->command = $command;
        $this->generator = new DatabaseChangeGenerator();
        
        $ns = APP_DIR.'\\Database\\Migrations';
        if (!$command->isArgProvided('--defaults')) {
            $ns = CLIUtils::readNamespace($command, $ns , 'Migration namespace:');
        }

        $this->generator->setNamespace($ns);
        $this->generator->setPath(APP_PATH.'Database'.DS.'Migrations');
        
        $this->className = $command->readClassName('Provide a name for the class that will have migration logic:', null);
        
        if (!$command->isArgProvided('--defaults')) {
            $this->readDependencies();
        }
    }
    
    public function writeClass() {
        $options = [];
        
        if (!empty($this->dependencies)) {
            $options[GeneratorOption::DEPENDENCIES] = $this->dependencies;
        }
        
        $filePath = $this->generator->createMigration($this->className, $options);
        $this->command->info('New class was created at "'.dirname($filePath).'".');
    }
    
    private function readDependencies() {
        if (!$this->command->confirm('Does this migration depend on other migrations?', false)) {
            return;
        }
        
        $migrations = $this->getExistingMigrations();
        
        if (empty($migrations)) {
            $this->command->warning('No existing migrations found.');
            return;
        }
        
        $this->command->println('Available migrations:');
        foreach ($migrations as $idx => $migration) {
            $this->command->println("$idx: $migration");
        }
        
        while (true) {
            $input = $this->command->getInput('Enter migration number (or press Enter to finish):');
            
            if (empty($input)) {
                break;
            }
            
            $idx = (int)$input;
            if (isset($migrations[$idx])) {
                $fullClass = '\\'.$this->generator->getNamespace().'\\'.$migrations[$idx];
                $this->dependencies[] = $fullClass;
                $this->command->success("Added dependency: {$migrations[$idx]}");
            } else {
                $this->command->error('Invalid migration number.');
            }
        }
    }
    
    private function getExistingMigrations() : array {
        $migrationsDir = APP_PATH.'Database'.DS.'Migrations';
        
        if (!is_dir($migrationsDir)) {
            return [];
        }
        
        $files = scandir($migrationsDir);
        $migrations = [];
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $migrations[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }
        
        return $migrations;
    }
}
