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
    private array $environments;
    private array $dependencies;
    
    public function __construct(string $className, string $description = 'No description', array $environments = [], array $dependencies = []) {
        parent::__construct($className, APP_PATH.'Database'.DS.'Migrations', APP_DIR.'\\Database\\Migrations');
        $this->description = $description;
        $this->environments = $environments;
        $this->dependencies = $dependencies;
        $this->addUseStatement([
            'WebFiori\\Database\\Database',
            'WebFiori\\Database\\Schema\\AbstractMigration'
        ]);
        
        foreach ($dependencies as $dep) {
            $this->addUseStatement($dep);
        }
    }
    
    public function writeClassBody() {
        if (!empty($this->environments)) {
            $this->writeGetEnvironments();
        }
        
        if (!empty($this->dependencies)) {
            $this->writeGetDependencies();
        }
        
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
    
    private function writeGetEnvironments() {
        $this->append('    /**', 1);
        $this->append('     * Get the environments where this migration should be executed.', 1);
        $this->append('     *', 1);
        $this->append('     * @return array Array of environment names.', 1);
        $this->append('     */', 1);
        $this->append('    public function getEnvironments(): array {', 1);
        $this->append('        return [', 2);
        
        foreach ($this->environments as $env) {
            $this->append("            '$env',", 0);
        }
        
        $this->append('        ];', 2);
        $this->append('    }', 1);
        $this->append('', 1);
    }
    
    private function writeGetDependencies() {
        $this->append('    /**', 1);
        $this->append('     * Get the list of changes this migration depends on.', 1);
        $this->append('     *', 1);
        $this->append('     * @return array Array of class names.', 1);
        $this->append('     */', 1);
        $this->append('    public function getDependencies(): array {', 1);
        $this->append('        return [', 2);
        
        foreach ($this->dependencies as $dep) {
            $shortName = basename(str_replace('\\', '/', $dep));
            $this->append("            $shortName::class,", 0);
        }
        
        $this->append('        ];', 2);
        $this->append('    }', 1);
        $this->append('', 1);
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
