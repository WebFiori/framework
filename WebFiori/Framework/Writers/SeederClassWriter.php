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
    
    public function __construct(string $className, string $description = 'No description') {
        parent::__construct($className, APP_PATH.'Database'.DS.'Seeders', APP_DIR.'\\Database\\Seeders');
        $this->description = $description;
        $this->addUseStatement([
            'WebFiori\\Database\\Database',
            'WebFiori\\Database\\Schema\\AbstractSeeder'
        ]);
    }
    
    public function writeClassBody() {
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
