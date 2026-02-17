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
use WebFiori\Framework\Writers\RepositoryWriter;

/**
 * Helper for creating repository classes.
 *
 * @author Ibrahim
 */
class CreateRepository extends CreateClassHelper {
    public function __construct(CreateCommand $command) {
        parent::__construct($command, new RepositoryWriter());
        
        $ns = APP_DIR.'\\Infrastructure\\Repository';
        if (!$command->isArgProvided('--defaults')) {
            $ns = $this->getCommand()->getInput('Repository namespace: Enter = \''.$ns.'\'') ?: $ns;
        }
        
        $this->setNamespace($ns);
        $this->setClassName($command->readClassName('Enter repository class name:', 'Repository'));
        
        $entityClass = $this->getInput('Enter entity class (e.g., App\\Domain\\User):');
        $this->getWriter()->setEntityClass($entityClass);
        
        $tableName = $this->getInput('Enter table name:');
        $this->getWriter()->setTableName($tableName);
        
        $idField = $this->getInput('Enter ID field name:', 'id');
        $this->getWriter()->setIdField($idField);
        
        if (!$command->isArgProvided('--defaults')) {
            $this->readProperties();
        }
    }
    
    private function readProperties() {
        $this->println('Add entity properties (for mapping):');
        
        while (true) {
            $name = $this->getInput('Property name (or press Enter to finish):');
            if (empty($name)) {
                break;
            }
            
            $type = $this->select('Property type:', ['int', 'string', 'bool', 'float'], 1);
            
            $this->getWriter()->addProperty($name, $type);
            $this->success("Added property: $name");
        }
    }
}
