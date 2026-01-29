<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cli\Argument;
use WebFiori\Cli\Command;
use WebFiori\Cli\InputValidator;
use WebFiori\Framework\Writers\RepositoryWriter;

/**
 * A command which is used to create a repository class.
 *
 * @author Ibrahim
 *
 */
class CreateRepositoryCommand extends Command {
    public function __construct() {
        parent::__construct('create:repository', [
            new Argument('--class-name', 'The name of the repository class.', true),
            new Argument('--entity', 'The fully qualified entity class name.', true),
            new Argument('--table', 'The database table name.', true),
            new Argument('--id-field', 'The primary key field name.', true),
            new Argument('--properties', 'JSON string of entity properties. Format: [{"name":"id","type":"int"},{"name":"name","type":"string"}]', true)
        ], 'Create a new repository class.');
    }
    private function getEntityClass() : string {
        $entity = $this->getArgValue('--entity');
        
        if ($entity === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Entity class cannot be empty.');
            
            $entity = $this->getInput('Enter entity class (e.g., App\\Domain\\User):', null, $validator);
        }
        return $entity;
    }
    private function getTableName() : string {
        $table = $this->getArgValue('--table');
        
        if ($table === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Table name cannot be empty.');
            
            $table = $this->getInput('Enter table name:', null, $validator);
        }
        return $table;
    }
    private function getIdField() : string {
        $idField = $this->getArgValue('--id-field');
        
        if ($idField === null) {
            $idField = $this->getInput('Enter ID field name:', 'id');
        }
        return $idField;
    }
    private function getProperties() : array {
        $properties = [];
        $propsJson = $this->getArgValue('--properties');
        
        if ($propsJson !== null) {
            $propsData = json_decode($propsJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON format for --properties parameter.');
                return $properties;
            }
            
            if (is_array($propsData)) {
                $properties = $propsData;
            }
        } elseif ($this->getArgValue('--class-name') === null) {
            if ($this->confirm('Add properties to the repository?', false)) {
                while (true) {
                    $propName = $this->getInput('Enter property name (leave empty to finish):');
                    if (empty(trim($propName))) {
                        break;
                    }
                    
                    $propType = $this->select('Select property type:', ['string', 'int', 'float', 'bool', 'array']);
                    
                    $properties[] = [
                        'name' => trim($propName),
                        'type' => $propType
                    ];
                }
            }
        }
        
        return $properties;
    }
    /**
     * Execute the command.
     *
     * @return int
     */
    public function exec() : int {
        $className = $this->getArgValue('--class-name');
        
        if ($className === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Class name cannot be empty.');
            
            $className = $this->getInput('Enter repository class name:', null, $validator);
        }
        
        $className = trim($className);
        
        if (empty($className)) {
            $this->error('Class name cannot be empty.');
            return -1;
        }

        $entityClass = $this->getEntityClass();
        $tableName = $this->getTableName();
        $idField = $this->getIdField();
        $properties = $this->getProperties();

        $writer = new RepositoryWriter();
        $writer->setClassName($className);
        $writer->setEntityClass($entityClass);
        $writer->setTableName($tableName);
        $writer->setIdField($idField);
        
        foreach ($properties as $prop) {
            $writer->addProperty($prop['name'], $prop['type']);
        }
        
        $writer->writeClass();

        $this->success('Repository class created at: '.$writer->getAbsolutePath());
        
        return 0;
    }
}
