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
use WebFiori\Framework\Writers\RestServiceWriter;
use WebFiori\Http\ParamType;
use WebFiori\Http\RequestMethod;

/**
 * A command which is used to create a REST service class.
 *
 * @author Ibrahim
 *
 */
class CreateServiceCommand extends Command {
    public function __construct() {
        parent::__construct('create:service', [
            new Argument('--class-name', 'The name of the service class.', true),
            new Argument('--description', 'A description of what the service does.', true),
            new Argument('--methods', 'JSON string of service methods. Format: [{"http":"GET","name":"getUser","params":[{"name":"id","type":"INT","description":"User ID"}],"return":"array"}]', true)
        ], 'Create a new REST service class.');
    }
    private function getServiceDescription() : string {
        $description = $this->getArgValue('--description');
        
        if ($description === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Service description cannot be empty.');
            
            $description = $this->getInput('Enter service description:', 'REST API Service', $validator);
        }
        return $description;
    }
    private function getServiceMethods() : array {
        $methods = [];
        $methodsJson = $this->getArgValue('--methods');
        
        if ($methodsJson !== null) {
            $methodsData = json_decode($methodsJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON format for --methods parameter.');
                return $methods;
            }
            
            if (is_array($methodsData)) {
                $methods = $methodsData;
            }
        } elseif ($this->getArgValue('--class-name') === null) {
            if ($this->confirm('Add methods to the service?', false)) {
                while (true) {
                    $methodName = $this->getInput('Enter method name (leave empty to finish):');
                    if (empty(trim($methodName))) {
                        break;
                    }
                    
                    $httpMethod = $this->select('Select HTTP method:', RequestMethod::getAll());
                    $returnType = $this->getInput('Enter return type:', 'array');
                    
                    $params = [];
                    if ($this->confirm('Add parameters to this method?', false)) {
                        while (true) {
                            $paramName = $this->getInput('Enter parameter name (leave empty to finish):');
                            if (empty(trim($paramName))) {
                                break;
                            }
                            
                            $paramType = $this->select('Select parameter type:', ParamType::getTypes());
                            $paramDesc = $this->getInput('Enter parameter description:', '');
                            
                            $param = [
                                'name' => trim($paramName),
                                'type' => $paramType,
                                'description' => trim($paramDesc)
                            ];
                            
                            if (in_array($paramType, [ParamType::INT, ParamType::DOUBLE])) {
                                if ($this->confirm('Add min/max constraints?', false)) {
                                    $param['min'] = (int)$this->getInput('Enter minimum value:');
                                    $param['max'] = (int)$this->getInput('Enter maximum value:');
                                }
                            }
                            
                            $params[] = $param;
                        }
                    }
                    
                    $methods[] = [
                        'http' => $httpMethod,
                        'name' => trim($methodName),
                        'params' => $params,
                        'return' => trim($returnType)
                    ];
                }
            }
        }
        
        return $methods;
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
            
            $className = $this->getInput('Enter service class name:', null, $validator);
        }
        
        $className = trim($className);
        
        if (empty($className)) {
            $this->error('Class name cannot be empty.');
            return -1;
        }

        $description = $this->getServiceDescription();
        $methods = $this->getServiceMethods();

        $writer = new RestServiceWriter();
        $writer->setClassName($className);
        $writer->setDescription($description);
        
        foreach ($methods as $method) {
            $writer->addMethod(
                $method['http'],
                $method['name'],
                $method['params'] ?? [],
                $method['return'] ?? 'array'
            );
        }
        
        $writer->writeClass();

        $this->success('Service class created at: '.$writer->getAbsolutePath());
        
        return 0;
    }
}
