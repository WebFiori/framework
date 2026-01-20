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
use WebFiori\Framework\Writers\DomainEntityWriter;

/**
 * Helper for creating pure domain entities.
 *
 * @author Ibrahim
 */
class CreateDomainEntity extends CreateClassHelper {
    public function __construct(CreateCommand $command) {
        parent::__construct($command, new DomainEntityWriter());
        
        $ns = APP_DIR.'\\Domain';
        if (!$command->isArgProvided('--defaults')) {
            $ns = $this->getCommand()->getInput('Entity namespace: Enter = \''.$ns.'\'') ?: $ns;
        }
        
        $this->setNamespace($ns);
        $this->setClassName($command->readClassName('Enter entity class name:'));
        
        if (!$command->isArgProvided('--defaults')) {
            $this->readProperties();
        }
    }
    
    private function readProperties() {
        $this->println('Add properties to the entity:');
        
        while (true) {
            $name = $this->getInput('Property name (or press Enter to finish):');
            if (empty($name)) {
                break;
            }
            
            $type = $this->select('Property type:', ['int', 'string', 'bool', 'float', 'array'], 1);
            $nullable = $this->confirm('Is nullable?', false);
            
            $this->getWriter()->addProperty($name, $type, $nullable);
            $this->success("Added property: $name");
        }
    }
}
