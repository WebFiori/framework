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
use WebFiori\Framework\Writers\MigrationClassWriter;

/**
 * A command which is used to create a migration class.
 *
 * @author Ibrahim
 */
class CreateMigrationCommand extends Command {
    public function __construct() {
        parent::__construct('create:migration', [
            new Argument('--class-name', 'The name of the migration class.', true),
            new Argument('--description', 'A description of what the migration does.', true),
            new Argument('--environments', 'Comma-separated list of environments (e.g., dev,test).', true),
            new Argument('--depends-on', 'Comma-separated list of class names this depends on.', true)
        ], 'Create a new database migration class.');
    }
    
    public function exec(): int {
        $className = $this->getClassName();
        $description = $this->getMigrationDescription();
        $environments = $this->getEnvironments();
        $dependencies = $this->getDependencies();
        
        $writer = new MigrationClassWriter($className, $description, $environments, $dependencies);
        $writer->writeClass();
        
        $this->success('Migration class created at: '.$writer->getAbsolutePath());
        
        return 0;
    }
    
    private function getClassName(): string {
        $className = $this->getArgValue('--class-name');
        
        if ($className === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Class name cannot be empty.');
            
            $className = $this->getInput('Enter migration class name:', null, $validator);
        } else if (empty(trim($className))) {
            $this->error('--class-name cannot be empty string.');
            
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Class name cannot be empty.');
            
            $className = $this->getInput('Enter migration class name:', null, $validator);
        }
        
        return trim($className);
    }
    
    private function getMigrationDescription(): string {
        $description = $this->getArgValue('--description');
        
        if ($description === null && $this->getArgValue('--class-name') === null) {
            $description = $this->getInput('Enter migration description:', 'No description');
        } else if ($description === null) {
            $description = 'No description';
        }
        
        return trim($description);
    }
    
    private function getEnvironments(): array {
        $environments = $this->getArgValue('--environments');
        
        if ($environments !== null) {
            return array_map('trim', explode(',', $environments));
        }
        
        if ($this->getArgValue('--class-name') === null && $this->confirm('Restrict to specific environments?', false)) {
            $envs = [];
            while (true) {
                $env = $this->getInput('Enter environment name (leave empty to finish):');
                if (empty(trim($env))) {
                    break;
                }
                $envs[] = trim($env);
            }
            return $envs;
        }
        
        return [];
    }
    
    private function getDependencies(): array {
        $dependsOn = $this->getArgValue('--depends-on');
        
        if ($dependsOn !== null) {
            return $this->resolveDependencies(array_map('trim', explode(',', $dependsOn)));
        }
        
        if ($this->getArgValue('--class-name') === null && $this->confirm('Add dependencies?', false)) {
            return $this->selectDependenciesInteractive();
        }
        
        return [];
    }
    
    private function selectDependenciesInteractive(): array {
        $available = $this->scanDatabaseChanges();
        
        if (empty($available)) {
            $this->info('No existing database changes found.');
            return [];
        }
        
        $this->println('Available database changes:');
        foreach ($available as $index => $class) {
            $this->println('  '.($index + 1).'. '.$class);
        }
        
        $selected = [];
        while (true) {
            $input = $this->getInput('Select dependency (enter number, empty to finish):');
            if (empty(trim($input))) {
                break;
            }
            
            $index = (int)$input - 1;
            if (isset($available[$index])) {
                $selected[] = $available[$index];
                $this->success('Added: '.$available[$index]);
            } else {
                $this->error('Invalid selection.');
            }
        }
        
        return $selected;
    }
    
    private function scanDatabaseChanges(): array {
        $changes = [];
        
        // Scan migrations
        $migrationsPath = APP_PATH.'Database'.DS.'Migrations';
        if (is_dir($migrationsPath)) {
            foreach (glob($migrationsPath.DS.'*.php') as $file) {
                $className = basename($file, '.php');
                $changes[] = APP_DIR.'\\Database\\Migrations\\'.$className;
            }
        }
        
        // Scan seeders
        $seedersPath = APP_PATH.'Database'.DS.'Seeders';
        if (is_dir($seedersPath)) {
            foreach (glob($seedersPath.DS.'*.php') as $file) {
                $className = basename($file, '.php');
                $changes[] = APP_DIR.'\\Database\\Seeders\\'.$className;
            }
        }
        
        return $changes;
    }
    
    private function resolveDependencies(array $shortNames): array {
        $resolved = [];
        $available = $this->scanDatabaseChanges();
        
        foreach ($shortNames as $shortName) {
            $found = false;
            foreach ($available as $fullClass) {
                if (basename(str_replace('\\', '/', $fullClass)) === $shortName) {
                    $resolved[] = $fullClass;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $this->warning("Dependency '$shortName' not found, skipping.");
            }
        }
        
        return $resolved;
    }
}
