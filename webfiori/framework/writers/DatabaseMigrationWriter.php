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

use Override;
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

    #[Override]
    public function writeClassBody() {
        $this->append([
            '/**',
            ' * Creates new instance of the class.',
            ' */',
            $this->f('__construct'),

        ], 1);
        $this->append("parent::__construct('".$this->getMigrationName()."', ".$this->getMigrationOrder().");", 2);
        $this->append('}', 1);
        $this->f('up', ['schema' => 'Database']);
        $this->f('down', ['schema' => 'Database']);
    }

    #[Override]
    public function writeClassComment() {
        $classTop = [
            '/**',
            ' * A database migration which is created using the command "create".',
            ' *',
            ' */'
        ];
        $this->append($classTop);
    }

    #[Override]
    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends AbstractMigration {');
    }
}
