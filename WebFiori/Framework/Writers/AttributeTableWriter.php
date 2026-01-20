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

use WebFiori\Database\DataType;

/**
 * Writer for creating attribute-based table schemas.
 *
 * @author Ibrahim
 */
class AttributeTableWriter extends ClassWriter {
    private $tableName;
    private $columns = [];
    
    public function __construct() {
        parent::__construct('NewTable', APP_PATH.'Infrastructure'.DS.'Schema', APP_DIR.'\\Infrastructure\\Schema');
        $this->addUseStatement([
            'WebFiori\\Database\\Attributes\\Column',
            'WebFiori\\Database\\Attributes\\Table',
            'WebFiori\\Database\\DataType'
        ]);
    }
    
    public function setTableName(string $name) {
        $this->tableName = $name;
    }
    
    public function addColumn(string $name, string $type, array $options = []) {
        $this->columns[] = array_merge([
            'name' => $name,
            'type' => $type
        ], $options);
    }
    
    public function writeClassBody() {
        $this->append('}');
    }
    
    public function writeClassComment() {
        $this->append('/**');
        $this->append(' * Table definition using PHP 8 attributes.');
        $this->append(' */');
        
        // Add Table attribute
        $this->append("#[Table(name: '{$this->tableName}')]", 0);
        
        // Add Column attributes
        foreach ($this->columns as $col) {
            $attr = "#[Column(name: '{$col['name']}', type: DataType::{$col['type']}";
            
            if (isset($col['size'])) {
                $attr .= ", size: {$col['size']}";
            }
            if (isset($col['primary']) && $col['primary']) {
                $attr .= ", primary: true";
            }
            if (isset($col['autoIncrement']) && $col['autoIncrement']) {
                $attr .= ", autoIncrement: true";
            }
            if (isset($col['nullable']) && $col['nullable']) {
                $attr .= ", nullable: true";
            }
            
            $attr .= ')]';
            $this->append($attr, 0);
        }
    }
    
    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' {');
    }
}
