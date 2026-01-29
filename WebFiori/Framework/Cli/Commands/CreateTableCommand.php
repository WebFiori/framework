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

/**
 * A command which is used to create a database table schema class.
 *
 * @author Ibrahim
 *
 */
class CreateTableCommand extends Command {
    public function __construct() {
        parent::__construct('create:table', [
            new Argument('--class-name', 'The name of the table class.', true),
            new Argument('--table-name', 'The name of the database table.', true),
            new Argument('--columns', 'JSON string of table columns. Format: [{"name":"id","type":"INT","size":11,"primary":true,"autoIncrement":true}]', true)
        ], 'Create a new database table schema class.');
    }
    private function getTableName(string $className) : string {
        $tableName = $this->getArgValue('--table-name');
        
        if ($tableName === null) {
            $validator = new InputValidator(function($input) {
                return !empty(trim($input));
            }, 'Table name cannot be empty.');
            
            $tableName = $this->getInput('Enter table name:', strtolower($className), $validator);
        }
        return $tableName;
    }
    private function getTableColumns() : array {
        $columns = [];
        $columnsJson = $this->getArgValue('--columns');
        
        if ($columnsJson !== null) {
            $columnsData = json_decode($columnsJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON format for --columns parameter.');
                return $columns;
            }
            
            if (is_array($columnsData)) {
                $columns = $columnsData;
            }
        } elseif ($this->getArgValue('--class-name') === null) {
            if ($this->confirm('Add columns to the table?', false)) {
                while (true) {
                    $colName = $this->getInput('Enter column name (leave empty to finish):');
                    if (empty(trim($colName))) {
                        break;
                    }
                    
                    $colType = $this->select('Select column type:', DataType::getTypes());
                    
                    $column = [
                        'name' => trim($colName),
                        'type' => $colType
                    ];
                    
                    if (in_array($colType, [DataType::VARCHAR, DataType::TEXT, DataType::INT])) {
                        $size = $this->getInput('Enter column size (leave empty for default):');
                        if (!empty(trim($size))) {
                            $column['size'] = (int)$size;
                        }
                    }
                    
                    if ($this->confirm('Is this a primary key?', false)) {
                        $column['primary'] = true;
                        
                        if ($colType === DataType::INT) {
                            $column['autoIncrement'] = $this->confirm('Auto increment?', true);
                        }
                    }
                    
                    $column['nullable'] = $this->confirm('Is this column nullable?', false);
                    
                    $columns[] = $column;
                }
            }
        }
        
        return $columns;
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
            
            $className = $this->getInput('Enter table class name:', null, $validator);
        }
        
        $className = trim($className);
        
        if (empty($className)) {
            $this->error('Class name cannot be empty.');
            return -1;
        }

        $tableName = $this->getTableName($className);
        $columns = $this->getTableColumns();

        $writer = new AttributeTableWriter();
        $writer->setClassName($className);
        $writer->setTableName($tableName);
        
        foreach ($columns as $col) {
            $options = [];
            if (isset($col['size'])) {
                $options['size'] = $col['size'];
            }
            if (isset($col['primary'])) {
                $options['primary'] = $col['primary'];
            }
            if (isset($col['autoIncrement'])) {
                $options['autoIncrement'] = $col['autoIncrement'];
            }
            if (isset($col['nullable'])) {
                $options['nullable'] = $col['nullable'];
            }
            
            $writer->addColumn($col['name'], $col['type'], $options);
        }
        
        $writer->writeClass();

        $this->success('Table class created at: '.$writer->getAbsolutePath());
        
        return 0;
    }
}
