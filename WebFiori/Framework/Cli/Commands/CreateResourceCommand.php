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
use WebFiori\Database\DataType;
use WebFiori\Framework\Writers\AttributeTableWriter;
use WebFiori\Framework\Writers\DomainEntityWriter;
use WebFiori\Framework\Writers\RepositoryWriter;
use WebFiori\Framework\Writers\RestServiceWriter;

/**
 * A command which creates a complete CRUD resource with clean architecture.
 *
 * @author Ibrahim
 *
 */
class CreateResourceCommand extends Command {
    public function __construct() {
        parent::__construct('create:resource', [
            new Argument('--name', 'The name of the resource (e.g., User, Product).', true),
            new Argument('--table', 'The database table name (defaults to pluralized lowercase).', true),
            new Argument('--id-field', 'The primary key field name.', true),
            new Argument('--properties', 'JSON string of properties. Format: [{"name":"id","type":"int","nullable":true,"primary":true,"autoIncrement":true}]', true)
        ], 'Create a complete CRUD resource (entity, table, repository, service).');
    }
    
    private function getResourceName() : string {
        $name = $this->getArgValue('--name');
        
        if ($name === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input)) && ctype_upper($input[0]);
            }, 'Resource name cannot be empty and must start with uppercase letter.');
            
            $name = $this->getInput('Enter resource name (e.g., User, Product):', null, $validator);
        }
        return trim($name);
    }
    
    private function getTableName(string $resourceName) : string {
        $table = $this->getArgValue('--table');
        
        if ($table === null) {
            $default = strtolower($resourceName) . 's';
            $table = $this->getInput('Enter table name:', $default);
        }
        return trim($table);
    }
    
    private function getIdField() : string {
        $idField = $this->getArgValue('--id-field');
        
        if ($idField === null) {
            $idField = $this->getInput('Enter ID field name:', 'id');
        }
        return trim($idField);
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
        } elseif ($this->getArgValue('--name') === null) {
            $this->println('Add properties to the resource:');
            
            while (true) {
                $propName = $this->getInput('Enter property name (leave empty to finish):');
                if (empty(trim($propName))) {
                    break;
                }
                
                $propType = $this->select('Select property type:', ['int', 'string', 'float', 'bool']);
                $nullable = $this->confirm('Is this property nullable?', false);
                $isPrimary = $this->confirm('Is this a primary key?', false);
                
                $property = [
                    'name' => trim($propName),
                    'type' => $propType,
                    'nullable' => $nullable,
                    'primary' => $isPrimary
                ];
                
                if ($isPrimary && $propType === 'int') {
                    $property['autoIncrement'] = $this->confirm('Auto increment?', true);
                }
                
                if ($propType === 'string') {
                    $size = $this->getInput('Enter string size:', '255');
                    $property['size'] = (int)$size;
                }
                
                $properties[] = $property;
            }
        }
        
        return $properties;
    }
    
    private function mapTypeToDataType(string $type) : string {
        return match($type) {
            'int' => DataType::INT,
            'string' => DataType::VARCHAR,
            'float' => DataType::DOUBLE,
            'bool' => DataType::BOOL,
            default => DataType::VARCHAR
        };
    }
    
    /**
     * Execute the command.
     *
     * @return int
     */
    public function exec() : int {
        $resourceName = $this->getResourceName();
        $tableName = $this->getTableName($resourceName);
        $idField = $this->getIdField();
        $properties = $this->getProperties();
        
        if (empty($properties)) {
            $this->error('At least one property is required.');
            return -1;
        }
        
        // 1. Create Domain Entity
        $entityWriter = new DomainEntityWriter();
        $entityWriter->setClassName($resourceName);
        foreach ($properties as $prop) {
            $entityWriter->addProperty($prop['name'], $prop['type'], $prop['nullable'] ?? false);
        }
        $entityWriter->writeClass();
        $this->success('✓ Created entity: '.$entityWriter->getAbsolutePath());
        
        // 2. Create Table Schema
        $tableWriter = new AttributeTableWriter();
        $tableWriter->setClassName($resourceName.'Table');
        $tableWriter->setTableName($tableName);
        foreach ($properties as $prop) {
            $options = [];
            if (isset($prop['size'])) {
                $options['size'] = $prop['size'];
            }
            if (isset($prop['primary']) && $prop['primary']) {
                $options['primary'] = true;
            }
            if (isset($prop['autoIncrement']) && $prop['autoIncrement']) {
                $options['autoIncrement'] = true;
            }
            if (isset($prop['nullable']) && $prop['nullable']) {
                $options['nullable'] = true;
            }
            
            $tableWriter->addColumn($prop['name'], $this->mapTypeToDataType($prop['type']), $options);
        }
        $tableWriter->writeClass();
        $this->success('✓ Created table: '.$tableWriter->getAbsolutePath());
        
        // 3. Create Repository
        $repoWriter = new RepositoryWriter();
        $repoWriter->setClassName($resourceName.'Repository');
        $repoWriter->setEntityClass(APP_DIR.'\\Domain\\'.$resourceName);
        $repoWriter->setTableName($tableName);
        $repoWriter->setIdField($idField);
        foreach ($properties as $prop) {
            $repoWriter->addProperty($prop['name'], $prop['type']);
        }
        $repoWriter->writeClass();
        $this->success('✓ Created repository: '.$repoWriter->getAbsolutePath());
        
        // 4. Create REST Service
        $serviceWriter = new RestServiceWriter();
        $serviceWriter->setClassName($resourceName.'Service');
        $serviceWriter->setDescription($resourceName.' management API');
        
        // Add CRUD methods
        $idParam = [['name' => 'id', 'type' => 'INT', 'description' => $resourceName.' ID']];
        
        $serviceWriter->addMethod('GET', 'get'.$resourceName, $idParam, 'array');
        $serviceWriter->addMethod('GET', 'getAll'.ucfirst($tableName), [], 'array');
        
        $createParams = [];
        foreach ($properties as $prop) {
            if (!($prop['primary'] ?? false) || !($prop['autoIncrement'] ?? false)) {
                $createParams[] = [
                    'name' => $prop['name'],
                    'type' => strtoupper($prop['type']),
                    'description' => ucfirst($prop['name'])
                ];
            }
        }
        $serviceWriter->addMethod('POST', 'create'.$resourceName, $createParams, 'array');
        $serviceWriter->addMethod('PUT', 'update'.$resourceName, array_merge($idParam, $createParams), 'array');
        $serviceWriter->addMethod('DELETE', 'delete'.$resourceName, $idParam, 'array');
        
        $serviceWriter->writeClass();
        $this->success('✓ Created service: '.$serviceWriter->getAbsolutePath());
        
        $this->println('');
        $this->println('Resource created successfully!');
        $this->println('');
        $this->println('Next steps:');
        $this->println('1. Run migrations to create the database table');
        $this->println('2. Implement business logic in the service methods');
        $this->println('3. Access API at: /api/'.strtolower($resourceName));
        
        return 0;
    }
}
