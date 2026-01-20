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
namespace WebFiori\Framework\Cli\Helpers;

use WebFiori\Framework\Cli\Commands\CreateCommand;
use WebFiori\Framework\Writers\RestServiceWriter;

/**
 * Helper for creating annotation-based REST services.
 *
 * @author Ibrahim
 */
class CreateRestService extends CreateClassHelper {
    public function __construct(CreateCommand $command) {
        parent::__construct($command, new RestServiceWriter());
        
        $ns = APP_DIR.'\\Apis';
        if (!$command->isArgProvided('--defaults')) {
            $ns = $this->getCommand()->getInput('Service namespace: Enter = \''.$ns.'\'') ?: $ns;
        }
        
        $this->setNamespace($ns);
        $this->setClassName($command->readClassName('Enter service class name:', 'Service'));
        
        $description = $this->getInput('Service description:');
        $this->getWriter()->setDescription($description);
        
        if (!$command->isArgProvided('--defaults')) {
            $this->readMethods();
        }
    }
    
    private function readMethods() {
        $this->println('Add HTTP methods to the service:');
        
        while (true) {
            $httpMethod = $this->select('HTTP method (or select Cancel):', ['GET', 'POST', 'PUT', 'DELETE', 'Cancel'], 0);
            
            if ($httpMethod === 'Cancel') {
                break;
            }
            
            $methodName = $this->getInput('Method name:');
            $params = [];
            
            if ($this->confirm('Add parameters?', false)) {
                $params = $this->readParameters();
            }
            
            $returnType = $this->select('Return type:', ['array', 'string', 'int', 'bool'], 0);
            
            $this->getWriter()->addMethod($httpMethod, $methodName, $params, $returnType);
            $this->success("Added method: $methodName");
            
            if (!$this->confirm('Add another method?', false)) {
                break;
            }
        }
    }
    
    private function readParameters(): array {
        $params = [];
        
        while (true) {
            $name = $this->getInput('Parameter name (or press Enter to finish):');
            if (empty($name)) {
                break;
            }
            
            $type = $this->select('Parameter type:', ['STRING', 'INT', 'DOUBLE', 'BOOL', 'EMAIL', 'URL', 'ARRAY'], 0);
            $description = $this->getInput('Parameter description:');
            
            $param = [
                'name' => $name,
                'type' => $type,
                'description' => $description
            ];
            
            if ($type === 'INT' || $type === 'DOUBLE') {
                if ($this->confirm('Set min/max values?', false)) {
                    $param['min'] = (int)$this->getInput('Minimum value:');
                    $param['max'] = (int)$this->getInput('Maximum value:');
                }
            }
            
            $params[] = $param;
            $this->success("Added parameter: $name");
        }
        
        return $params;
    }
}
