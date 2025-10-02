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
namespace webfiori\framework\writers;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;
use WebFiori\Database\Schema\SchemaRunner;

/**
 * A writer class which is used to create new database migration.
 *
 * @author Ibrahim
 */
class DatabaseMigrationWriter extends ClassWriter {
    /**
     * Creates new instance of the class.
     *
     */
    public function __construct(?SchemaRunner $runner) {
        $name = 'Database';
        $this->setSuffix('Migration');
        
        $this->setClassName($name);
        
        parent::__construct($name, APP_PATH.'database'.DS.'migrations', APP_DIR.'\\database\\migrations');
        $this->addUseStatement([
            Database::class,
            AbstractMigration::class,
        ]);
        
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
