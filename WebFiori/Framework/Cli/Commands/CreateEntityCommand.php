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
use WebFiori\Framework\Writers\DomainEntityWriter;

/**
 * A command which is used to create a domain entity class.
 *
 * @author Ibrahim
 *
 */
class CreateEntityCommand extends Command {
    public function __construct() {
        parent::__construct('create:entity', [
            new Argument('--class-name', 'The name of the entity class.', true),
            new Argument('--properties', 'JSON string of entity properties. Format: [{"name":"prop1","type":"string","nullable":false}]', true)
        ], 'Create a new domain entity class.');
    }
    private function getEntityProperties() : array {
        $properties = [];
        $propsJson = $this->getArgValue('--properties');
        
        if ($propsJson !== null) {
            $propsData = json_decode($propsJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON format for --properties parameter.');
                return $properties;
            }
            
            if (is_array($propsData)) {
                foreach ($propsData as $propData) {
                    if (isset($propData['name']) && isset($propData['type'])) {
                        $properties[] = [
                            'name' => $propData['name'],
                            'type' => $propData['type'],
                            'nullable' => $propData['nullable'] ?? false
                        ];
                    }
                }
            }
        } elseif ($this->getArgValue('--class-name') === null) {
            if ($this->confirm('Add properties to the entity?', false)) {
                while (true) {
                    $propName = $this->getInput('Enter property name (leave empty to finish):');
                    if (empty(trim($propName))) {
                        break;
                    }
                    
                    $propType = $this->getInput('Enter property type:', 'string');
                    $nullable = $this->confirm('Is this property nullable?', false);
                    
                    $properties[] = [
                        'name' => trim($propName),
                        'type' => trim($propType),
                        'nullable' => $nullable
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
            
            $className = $this->getInput('Enter entity class name:', null, $validator);
        }
        
        $className = trim($className);
        
        if (empty($className)) {
            $this->error('Class name cannot be empty.');
            return -1;
        }

        $properties = $this->getEntityProperties();

        $writer = new DomainEntityWriter();
        $writer->setClassName($className);
        
        foreach ($properties as $prop) {
            $writer->addProperty($prop['name'], $prop['type'], $prop['nullable']);
        }
        
        $writer->writeClass();

        $this->success('Entity class created at: '.$writer->getAbsolutePath());
        
        return 0;
    }
}
