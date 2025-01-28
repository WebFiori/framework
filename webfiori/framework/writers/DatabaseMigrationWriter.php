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

use webfiori\database\Database;
use webfiori\database\migration\AbstractMigration;
use webfiori\database\migration\MigrationsRunner;

/**
 * A writer class which is used to create new database migration.
 *
 * @author Ibrahim
 */
class DatabaseMigrationWriter extends ClassWriter {
    private $order;
    private $name;
    /**
     * Creates new instance of the class.
     *
     */
    public function __construct(MigrationsRunner $runner) {
        $count = count($runner->getMigrations());
        $this->setMigrationOrder($count);
        if ($count < 10) {
            $name = 'Migration00'.$count;
        } else if ($count < 100) {
            $name = 'Migration0'.$count;
        } else {
            $name = 'Migration'.$count;
        }
        
        $this->setMigrationName($name);
        
        parent::__construct($name, APP_PATH.'database'.DS.'migrations', APP_DIR.'\\database\\migrations');
        $this->addUseStatement([
            Database::class,
            AbstractMigration::class,
        ]);
        
    }
    public function getMigrationName() : string {
        return $this->name;
    }
    public function getMigrationOrder() : int {
        return $this->order;
    }
    public function setMigrationName(string $name) {
        $this->name = $name;
    }
    public function setMigrationOrder(int $order) {
        $this->order = $order;
    }

    public function writeClassBody() {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            $this->f('__construct'),

        ], 1);
        $this->append("parent::__construct('".$this->getMigrationName()."', ".$this->getMigrationOrder().");", 2);
        $this->append('}', 1);
        $this->append('/**', 1);
        $this->append(' * Performs the action that will apply the migration.', 1);
        $this->append(' * ', 1);
        $this->append(' * @param Database $schema The database at which the migration will be applied to.', 1);
        $this->append(' */', 1);
        $this->append($this->f('up', ['schema' => 'Database']), 1);
        $this->append('//TODO: Implement the action which will apply the migration to database.', 2);
        $this->append('}', 1);
        $this->append('/**', 1);
        $this->append(' * Performs the action that will revert back the migration.', 1);
        $this->append(' * ', 1);
        $this->append(' * @param Database $schema The database at which the migration will be applied to.', 1);
        $this->append(' */', 1);
        $this->append($this->f('down', ['schema' => 'Database']), 1);
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
