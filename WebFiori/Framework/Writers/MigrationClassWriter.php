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
namespace WebFiori\Framework\Writers;

/**
 * Writer for creating migration classes.
 *
 * @author Ibrahim
 */
class MigrationClassWriter extends ClassWriter {
    private string $description;
    
    public function __construct(string $className, string $description = 'No description') {
        parent::__construct($className, APP_PATH.'Database'.DS.'Migrations', APP_DIR.'\\Database\\Migrations');
        $this->description = $description;
        $this->addUseStatement([
            'WebFiori\\Database\\Database',
            'WebFiori\\Database\\Schema\\AbstractMigration'
        ]);
    }
    
    public function writeClassBody() {
        $this->writeUpMethod();
        $this->writeDownMethod();
        $this->append('}');
    }
    
    public function writeClassComment() {
        $this->append([
            '/**',
            ' * '.$this->description,
            ' *',
            ' * @author Ibrahim',
            ' */'
        ]);
    }
    
    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends AbstractMigration {');
    }
    
    private function writeUpMethod() {
        $this->append('    /**', 1);
        $this->append('     * Apply the migration changes to the database.', 1);
        $this->append('     *', 1);
        $this->append('     * @param Database $db The database instance to execute changes on.', 1);
        $this->append('     */', 1);
        $this->append('    public function up(Database $db): void {', 1);
        $this->append('        // TODO: Implement migration logic', 2);
        $this->append('    }', 1);
        $this->append('', 1);
    }
    
    private function writeDownMethod() {
        $this->append('    /**', 1);
        $this->append('     * Rollback the migration changes from the database.', 1);
        $this->append('     *', 1);
        $this->append('     * @param Database $db The database instance to execute rollback on.', 1);
        $this->append('     */', 1);
        $this->append('    public function down(Database $db): void {', 1);
        $this->append('        // TODO: Implement rollback logic', 2);
        $this->append('    }', 1);
    }
}
