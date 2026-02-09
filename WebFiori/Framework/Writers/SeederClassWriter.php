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
 * Writer for creating seeder classes.
 *
 * @author Ibrahim
 */
class SeederClassWriter extends ClassWriter {
    private string $description;
    private array $environments;
    private array $dependencies;
    
    public function __construct(string $className, string $description = 'No description', array $environments = [], array $dependencies = []) {
        parent::__construct($className, APP_PATH.'Database'.DS.'Seeders', APP_DIR.'\\Database\\Seeders');
        $this->description = $description;
        $this->environments = $environments;
        $this->dependencies = $dependencies;
        $this->addUseStatement([
            'WebFiori\\Database\\Database',
            'WebFiori\\Database\\Schema\\AbstractSeeder'
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
        
        $this->writeRunMethod();
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
        $this->append('class '.$this->getName().' extends AbstractSeeder {');
    }
    
    private function writeGetEnvironments() {
        $this->append('    /**', 1);
        $this->append('     * Get the environments where this seeder should be executed.', 1);
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
        $this->append('     * Get the list of changes this seeder depends on.', 1);
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
    
    private function writeRunMethod() {
        $this->append('    /**', 1);
        $this->append('     * Run the seeder to populate the database with data.', 1);
        $this->append('     *', 1);
        $this->append('     * @param Database $db The database instance to execute seeding on.', 1);
        $this->append('     */', 1);
        $this->append('    public function run(Database $db): void {', 1);
        $this->append('        // TODO: Implement seeding logic', 2);
        $this->append('    }', 1);
    }
}
