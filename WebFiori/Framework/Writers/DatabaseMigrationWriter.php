<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2024 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Writers;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;
use WebFiori\Database\Schema\SchemaRunner;

/**
 * A writer class which is used to create new database migration.
 *
 * @author Ibrahim
 */
class DatabaseMigrationWriter extends ClassWriter {
    private $runner;
    private $environments = [];
    private $dependencies = [];
    private static $migrationCounter = 0;
    
    /**
     * Creates new instance of the class.
     *
     */
    public function __construct(?SchemaRunner $runner) {
        $this->runner = $runner;
        $name = $this->generateMigrationName();
        
        $this->setClassName($name);
        
        parent::__construct($name, APP_PATH.'database'.DS.'migrations', APP_DIR.'\\database\\migrations');
        $this->addUseStatement([
            Database::class,
            AbstractMigration::class,
        ]);
        
    }
    
    private function generateMigrationName() {
        $name = 'Migration' . str_pad(self::$migrationCounter, 3, '0', STR_PAD_LEFT);
        self::$migrationCounter++;
        return $name;
    }
    
    /**
     * Add an environment where this migration should run.
     */
    public function addEnv(string $env) {
        $this->environments[] = $env;
    }
    
    /**
     * Add a dependency migration class name.
     */
    public function addDependency(string $dependency) : bool {
        if (class_exists($dependency)) {
            $this->dependencies[] = $dependency;
            $this->addUseStatement($dependency);
            return true;
        }
        return false;
    }
    
    /**
     * Reset the migration counter for testing purposes.
     */
    public static function resetCounter() {
        self::$migrationCounter = 0;
    }

    public function writeClassBody() {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            $this->f('__construct'),

        ], 1);
        $this->append("parent::__construct();", 2);
        $this->append('}', 1);
        
        $this->append('/**', 1);
        $this->append(' * Get the list of migrations this migration depends on.', 1);
        $this->append(' * ', 1);
        $this->append(' * @return array Array of migration class names that must be executed before this one.', 1);
        $this->append(' */', 1);
        $this->append($this->f('getDependencies', [], 'array'), 1);
        if (empty($this->dependencies)) {
            $this->append('return [];', 2);
        } else {
            $this->append('return [', 2);
            foreach ($this->dependencies as $dep) {
                $this->append("    $dep::class,", 2);
            }
            $this->append('];', 2);
        }
        $this->append('}', 1);
        
        $this->append('/**', 1);
        $this->append(' * Get the environments where this migration should be executed.', 1);
        $this->append(' * ', 1);
        $this->append(' * @return array Empty array means all environments.', 1);
        $this->append(' */', 1);
        $this->append($this->f('getEnvironments', [], 'array'), 1);
        if (empty($this->environments)) {
            $this->append('return [];', 2);
        } else {
            $this->append('return [', 2);
            foreach ($this->environments as $env) {
                $this->append("    '$env',", 2);
            }
            $this->append('];', 2);
        }
        $this->append('}', 1);
        
        $this->append('/**', 1);
        $this->append(' * Performs the action that will apply the migration.', 1);
        $this->append(' * ', 1);
        $this->append(' * @param Database $db The database at which the migration will be applied to.', 1);
        $this->append(' */', 1);
        $this->append($this->f('up', ['db' => 'Database'], 'void'), 1);
        $this->append('//TODO: Implement the action which will apply the migration to database.', 2);
        $this->append('}', 1);
        
        $this->append('/**', 1);
        $this->append(' * Performs the action that will revert back the migration.', 1);
        $this->append(' * ', 1);
        $this->append(' * @param Database $db The database at which the migration will be applied to.', 1);
        $this->append(' */', 1);
        $this->append($this->f('down', ['db' => 'Database'], 'void'), 1);
        $this->append('//TODO: Implement the action which will revert back the migration.', 2);
        $this->append('}', 1);
        $this->append('}');
    }
    public function writeClassComment() {
        $classTop = [
            '/**',
            ' * A database migration class.',
            ' */'
        ];
        $this->append($classTop);
    }

    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends AbstractMigration {');
    }
}
