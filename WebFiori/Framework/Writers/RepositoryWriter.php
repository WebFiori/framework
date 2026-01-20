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
 * Writer for creating repository classes.
 *
 * @author Ibrahim
 */
class RepositoryWriter extends ClassWriter {
    private $entityClass;
    private $tableName;
    private $idField;
    private $properties = [];
    
    public function __construct() {
        parent::__construct('NewRepository', APP_PATH.'Infrastructure'.DS.'Repository', APP_DIR.'\\Infrastructure\\Repository');
        $this->addUseStatement([
            'WebFiori\\Database\\Repository\\AbstractRepository'
        ]);
    }
    
    public function setEntityClass(string $class) {
        $this->entityClass = $class;
        $this->addUseStatement($class);
    }
    
    public function setTableName(string $name) {
        $this->tableName = $name;
    }
    
    public function setIdField(string $field) {
        $this->idField = $field;
    }
    
    public function addProperty(string $name, string $type) {
        $this->properties[] = ['name' => $name, 'type' => $type];
    }
    
    public function writeClassBody() {
        $this->writeGetTableName();
        $this->writeGetIdField();
        $this->writeToEntity();
        $this->writeToArray();
        $this->append('}');
    }
    
    public function writeClassComment() {
        $this->append([
            '/**',
            ' * Repository for '.$this->entityClass.' entities.',
            ' */'
        ]);
    }
    
    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends AbstractRepository {');
    }
    
    private function writeGetTableName() {
        $this->append($this->f('getTableName', [], 'string'), 1);
        $this->append('return \''.$this->tableName.'\';', 2);
        $this->append('}', 1);
        $this->append('', 1);
    }
    
    private function writeGetIdField() {
        $this->append($this->f('getIdField', [], 'string'), 1);
        $this->append('return \''.$this->idField.'\';', 2);
        $this->append('}', 1);
        $this->append('', 1);
    }
    
    private function writeToEntity() {
        $entityShortName = basename(str_replace('\\', '/', $this->entityClass));
        $this->append($this->f('toEntity', ['row' => 'array'], $entityShortName), 1);
        $this->append('return new '.$entityShortName.'(', 2);
        
        $params = [];
        foreach ($this->properties as $prop) {
            $cast = $prop['type'] === 'int' ? '(int) ' : '';
            $params[] = "            {$cast}\$row['{$prop['name']}']";
        }
        
        $this->append(implode(",\n", $params));
        $this->append('        );', 0);
        $this->append('}', 1);
        $this->append('', 1);
    }
    
    private function writeToArray() {
        $this->append($this->f('toArray', ['entity' => 'object'], 'array'), 1);
        $this->append('return [', 2);
        
        foreach ($this->properties as $prop) {
            $this->append("'{$prop['name']}' => \$entity->{$prop['name']},", 3);
        }
        
        $this->append('];', 2);
        $this->append('}', 1);
    }
}
