<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Writers;

/**
 * Writer for creating pure domain entities.
 *
 * @author Ibrahim
 */
class DomainEntityWriter extends ClassWriter {
    private $properties = [];
    
    public function __construct() {
        parent::__construct('NewEntity', APP_PATH.'Domain', APP_DIR.'\\Domain');
    }
    
    public function addProperty(string $name, string $type, bool $nullable = false) {
        $this->properties[] = [
            'name' => $name,
            'type' => $type,
            'nullable' => $nullable
        ];
    }
    
    public function writeClassBody() {
        $this->writeConstructor();
        $this->append('}');
    }
    
    public function writeClassComment() {
        $this->append([
            '/**',
            ' * Domain entity - pure PHP, no framework dependencies.',
            ' */'
        ]);
    }
    
    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' {');
    }
    
    private function writeConstructor() {
        $this->append('public function __construct(', 1);
        
        $params = [];
        foreach ($this->properties as $prop) {
            $type = $prop['nullable'] ? '?'.$prop['type'] : $prop['type'];
            $params[] = "        public $type \${$prop['name']}";
        }
        
        $this->append(implode(",\n", $params));
        $this->append('    ) {}', 0);
    }
}
