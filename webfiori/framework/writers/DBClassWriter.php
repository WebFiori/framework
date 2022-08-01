<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2020 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\writers;

use webfiori\database\Table;
use webfiori\framework\DB;
use webfiori\framework\writers\ClassWriter;
/**
 * A class which is used to create a database access controller.
 *
 * @author Ibrahim
 */
class DBClassWriter extends ClassWriter {
    /**
     * 
     * @var Table
     */
    private $associatedTable;
    private $entityName;
    /**
     * 
     * @return Table
     */
    public function getTable() : Table {
        return $this->associatedTable;
    }
    public function __construct($className, $ns, Table $table) {
        parent::__construct($className, $ns, $ns);
        $this->associatedTable = $table;
        $this->addUseStatement(DB::class);
        $mapper = $this->getTable()->getEntityMapper();
        $this->entityName = $mapper->getEntityName();
        $this->addUseStatement($mapper->getNamespace().'\\'.$mapper->getEntityName());
    }
    public function getEntityName() : string {
        return $this->entityName;
    }
    public function writeClassBody() {
        $this->append([
            "/**",
            " * Creates new instance of the class.",
            " */",
            $this->f('__construct')
        ], 1);

        $this->append([
            'parent::__construct();',
        ], 2);
        $this->append('}', 1);
        
        $this->append([
            "/**",
            " * Adds new record to the table '".$this->getTable()->getName()."'.",
            " *",
            " * @param ".$this->getEntityName().' $entity An object that holds record information.',
            " *",
            " */",
            $this->f('addRecord', [$this->getEntityName() => 'entity'])
        ], 1);
        $this->append('}', 1);
        $this->append([
            "/**",
            " * Deletes a record from the table '".$this->getTable()->getName()."'.",
            " *",
            " * @param ".$this->getEntityName().' $entity An object that holds record information.',
            " *",
            " */",
            $this->f('deleteRecord', [$this->getEntityName() => 'entity']),
        ], 1);
        $this->append('}', 1);
        $this->writeGetRecord();
        $this->append([
            "/**",
            " * Returns all the records from the table '".$this->getTable()->getName()."'.",
            " *",
            " * @return array An array that holds all table records as objects",
            " *",
            " */",
            'public function getRecords() {'
        ], 1);
        $this->append('}', 1);
        $this->append([
            "/**",
            " * Updates a record on the table '".$this->getTable()->getName()."'.",
            " *",
            " * @param ".$this->getEntityName().' $entity An object that holds updated record information.',
            " *",
            " */",
            'public function updateRecord('.$this->getEntityName().' $entity) {'
        ], 1);
        $this->append('}', 1);
        
        $this->append('}', 0);
    }
    private function writeGetRecord() {
        $cols = $this->getUniqueColsKeys();
        $paramsArr = [];
        $where = [];
        foreach ($cols as $key) {
            $colObj = $this->getTable()->getColByKey($key);
            $paramsArr[$colObj->getPHPType()] = $colObj->getNormalName();
            $where[] = count($where) == 0 ? "->where('$key', '=', $".$colObj->getNormalName().")"
                    : "->andWhere('$key', '=', $'.$colObj->getNormalName())" ;
        }
        $this->append([
            "/**",
            " * Returns the information of a record from the table '".$this->getTable()->getName()."'.",
            " *",
            " * @return ".$this->getEntityName().'|null If a record with given information exist,',
            " * The method will return an object which holds all record information.",
            " * Other than that, null is returned.",
            " */",
            $this->f('getRecord', $paramsArr, $this->getEntityName())
        ], 1);
        if (count($paramsArr) != 0) {
            $this->append("return \$this->table('".$this->getTable()->getNormalName()."')", 2);
            $this->append("->select()", 3);
            $this->append($where, 3);
            $this->append("->execute()", 3);
            $this->append("->map(function (array \$records) {", 3);
            $this->append("if (count(\$records) == 1) {", 4);
            $this->append("return [".$this->getEntityName().'::map($records[0])];', 5);
            $this->append("}", 4);
            $this->append("return [];", 4);
            $this->append("});", 3);
        }
        $this->append('}', 1);
    }
    
    private function getUniqueColsKeys() {
        $table = $this->getTable();
        $recordUniqueCols  = $table->getPrimaryKeyColsKeys();
        
        if (count($recordUniqueCols ) == 0) {
            $recordUniqueCols = $table->getUniqueColsKeys();
        } 
        return $recordUniqueCols;
    }

    public function writeClassComment() {
        $this->append([
            "/**",
            " * A class which is used to perform operations on the table '".$this->getTable()->getNormalName()."'",
            " */"
        ]);
    }

    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends DB {');
    }

}
