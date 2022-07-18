<?php
namespace webfiori\framework\cli\writers;
use webfiori\framework\writers\ClassWriter;
use webfiori\database\Table;
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
        $this->addUseStatement('\\webfiori\\framework\\DB');
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
            'public function __construct() {'
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
            'public function addRecord('.$this->getEntityName().' $entity) {'
        ], 1);
        $this->append('}', 1);
        $this->append([
            "/**",
            " * Deletes a record from the table '".$this->getTable()->getName()."'.",
            " *",
            " * @param ".$this->getEntityName().' $entity An object that holds record information.',
            " *",
            " */",
            'public function deleteRecord('.$this->getEntityName().' $entity) {'
        ], 1);
        $this->append([
            "/**",
            " * Returns the information of a record from the table '".$this->getTable()->getName()."'.",
            " *",
            " * @return ".$this->getEntityName().'|null If a record with given information exist,',
            " * The method will return an object which holds all record information.",
            " * Other than that, null is returned.",
            " */",
            'public function getRecord() {'
        ], 1);
        $this->append('}', 1);
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
