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

use WebFiori\Database\DataType;
use WebFiori\Framework\Cli\Commands\CreateCommand;
use WebFiori\Framework\Writers\AttributeTableWriter;

/**
 * Helper for creating attribute-based table schemas.
 *
 * @author Ibrahim
 */
class CreateAttributeTable extends CreateClassHelper {
    public function __construct(CreateCommand $command) {
        parent::__construct($command, new AttributeTableWriter());
        
        $ns = APP_DIR.'\\Infrastructure\\Schema';
        if (!$command->isArgProvided('--defaults')) {
            $ns = $this->getCommand()->getInput('Table schema namespace: Enter = \''.$ns.'\'') ?: $ns;
        }
        
        $this->setNamespace($ns);
        $this->setClassName($command->readClassName('Enter table class name:', 'Table'));
        
        $tableName = $this->getInput('Enter database table name:');
        $this->getWriter()->setTableName($tableName);
        
        if (!$command->isArgProvided('--defaults')) {
            $this->readColumns();
        }
    }
    
    private function readColumns() {
        $this->println('Add columns to the table:');
        
        while (true) {
            $name = $this->getInput('Column name (or press Enter to finish):');
            if (empty($name)) {
                break;
            }
            
            $type = $this->select('Column type:', [
                'INT', 'VARCHAR', 'TEXT', 'DATETIME', 'TIMESTAMP', 'BOOL', 'DOUBLE', 'DECIMAL'
            ], 1);
            
            $options = [];
            
            if ($type === 'VARCHAR' || $type === 'DECIMAL') {
                $size = (int)$this->getInput('Size:', $type === 'VARCHAR' ? '255' : '10');
                $options['size'] = $size;
            }
            
            if ($this->confirm('Is primary key?', false)) {
                $options['primary'] = true;
                if ($type === 'INT' && $this->confirm('Auto increment?', true)) {
                    $options['autoIncrement'] = true;
                }
            }
            
            if ($this->confirm('Is nullable?', false)) {
                $options['nullable'] = true;
            }
            
            $this->getWriter()->addColumn($name, $type, $options);
            $this->success("Added column: $name");
        }
    }
}
