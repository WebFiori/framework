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

use webfiori\database\EntityMapper;
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
    private $whereArr;
    private $paramsArr;
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
        $this->createParamsAndWhereArr();
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
        
        $this->writeAddRecord();
        
        $this->writeDeleteRecord();
        $this->writeGetRecord();
        $this->writeGetRecords();
        $this->writeUpdateRecord();
        
        $this->append('}', 0);
    }
    private function writeAddRecord() {
        $this->append([
            "/**",
            " * Adds new record to the table '".$this->getTable()->getName()."'.",
            " *",
            " * @param ".$this->getEntityName().' $entity An object that holds record information.',
            " *",
            " */",
            $this->f('addRecord', ['entity' => $this->getEntityName()])
        ], 1);
        $recordsArr = [];
        foreach ($this->getTable()->getEntityMapper()->getGettersMap(true) as $methName => $col) {
            $recordsArr[] = "'$col' => \$entity->$methName(),";
        }
        $this->append([
            "\$this->table('".$this->getTable()->getNormalName()."')->insert(["
        ], 2);
        $this->append($recordsArr, 3);
        $this->append('])->execute();', 2);
        
        $this->append('}', 1);
    }
    private function createParamsAndWhereArr() {
        $cols = $this->getUniqueColsKeys();
        $this->paramsArr = [];
        $this->whereArr = [];
        foreach ($cols as $key) {
            $colObj = $this->getTable()->getColByKey($key);
            $this->paramsArr[$colObj->getNormalName()] = $colObj->getPHPType();
            $this->whereArr[] = count($this->whereArr) == 0 ? "->where('$key', '=', $".$colObj->getNormalName().")"
                    : "->andWhere('$key', '=', $'.$colObj->getNormalName())" ;
        }
    }
    private function writeUpdateRecord() {
        
        $this->append([
            "/**",
            " * Updates a record on the table '".$this->getTable()->getName()."'.",
            " *",
            " * @param ".$this->getEntityName().' $entity An object that holds updated record information.',
            " *",
            " */",
            $this->f('updateRecord', ['entity' => $this->getEntityName()])
        ], 1);
        $this->append("\$this->table('".$this->getTable()->getNormalName()."')", 2);
        $this->append("->update([", 3);
        $keys = $this->getTable()->getColsKeys();
        
        if (count($this->paramsArr) != 0) {
            $updateCols = [];
            $whereCols = [];
            $uniqueCols = $this->getUniqueColsKeys();
            foreach ($uniqueCols as $key) {
                $whereCols[] = count($whereCols) == 0 ? 
                        "->where('$key', '=', \$entity->".EntityMapper::mapToMethodName($key).'())' 
                        : "->andWhere('$key', '=', \$entity->".EntityMapper::mapToMethodName($key).'())';
            }
            foreach ($keys as $key) {
                if (!in_array($key, $uniqueCols)) {
                    $updateCols[] = "'$key' => \$entity->".EntityMapper::mapToMethodName($key).'(),';
                }
            }
            $this->append($updateCols, 4);
            $this->append('])', 3);
            $this->append($whereCols, 3);
            $this->append("->execute();", 3);
        } else {
            foreach ($keys as $key) {
                $updateCols[] = "'$key' => \$entity->".EntityMapper::mapToMethodName($key).'(),';
            }
            $this->append($updateCols, 4);
            $this->append(']);', 3);
            $this->append('//TODO: Specify update record condition(s).', 3);
        }
        $this->append('}', 1);
    }
    private function writeDeleteRecord() {
        
        $this->append([
            "/**",
            " * Deletes a record from the table '".$this->getTable()->getName()."'.",
            " *",
            " * @param ".$this->getEntityName().' $entity An object that holds record information.',
            " *",
            " */",
            $this->f('deleteRecord', ['entity' => $this->getEntityName()]),
        ], 1);
        $this->append("\$this->table('".$this->getTable()->getNormalName()."')", 2);
        
        if (count($this->paramsArr) != 0) {
            $this->append("->delete()", 3);
            $cols = [];
            foreach ($this->getUniqueColsKeys() as $key) {
                $cols[] = count($cols) == 0 ? 
                        "->where('$key', '=', \$entity->".EntityMapper::mapToMethodName($key).'())' 
                        : "->andWhere('$key', '=', \$entity->".EntityMapper::mapToMethodName($key).'())';
            }
            $this->append($cols, 3);
            $this->append("->execute();", 3);
        } else {
            $this->append("->delete();", 3);
            $this->append('//TODO: Specify delete record condition(s).', 3);
        }
        $this->append('}', 1);
    }

    private function writeGetRecord() {
        
        $this->append([
            "/**",
            " * Returns the information of a record from the table '".$this->getTable()->getName()."'.",
            " *",
            " * @return ".$this->getEntityName().'|null If a record with given information exist,',
            " * The method will return an object which holds all record information.",
            " * Other than that, null is returned.",
            " */",
            $this->f('getRecord', $this->paramsArr, $this->getEntityName())
        ], 1);
        if (count($this->paramsArr) != 0) {
            $this->append("\$mappedRecords = \$this->table('".$this->getTable()->getNormalName()."')", 2);
            $this->append("->select()", 3);
            $this->append($this->whereArr, 3);
            $this->append("->execute()", 3);
            $this->append("->map(function (array \$records) {", 3);
            $this->append("if (count(\$records) == 1) {", 4);
            $this->append("return [".$this->getEntityName().'::map($records[0])];', 5);
            $this->append("}", 4);
            $this->append("return [];", 4);
            $this->append("});", 3);
            $this->append('if (count($mappedRecords) == 1) {', 2);
            $this->append('return $mappedRecords[0];', 3);
            $this->append('}', 2);
        }
        $this->append('}', 1);
    }
    public function writeGetRecords() {
        $this->append([
            "/**",
            " * Returns all the records from the table '".$this->getTable()->getName()."'.",
            " *",
            " * @param int \$pageNum The number of page to fetch. Default is 0.",
            " *",
            " * @param int \$pageSize Number of records per page. Default is 10.",
            " *",
            " * @return array An array that holds all table records as objects",
            " *",
            " */",
            $this->f('getRecords', [
                'pageNum = 0' => 'int',
                'pageSize = 10' => 'int'
            ])
        ], 1);
        if (count($this->paramsArr) != 0) {
            $this->append("return \$this->table('".$this->getTable()->getNormalName()."')", 2);
            $this->append("->select()", 3);
            $this->append('->page($pageNum, $pageSize)', 3);
            $this->append("->execute()", 3);
            $this->append("->map(function (array \$records) {", 3);
            $this->append("\$retVal = [];", 4);
            $this->append("foreach (\$records as \$record) {", 4);
            $this->append("\$retVal[] = ".$this->getEntityName().'::map($record);', 5);
            $this->append("}", 4);
            $this->append("return \$retVal;", 4);
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
