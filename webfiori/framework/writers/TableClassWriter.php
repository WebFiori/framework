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
use webfiori\database\mssql\MSSQLColumn;
use webfiori\database\mssql\MSSQLTable;
use webfiori\database\mysql\MySQLColumn;
use webfiori\database\mysql\MySQLTable;
use webfiori\database\Table;
use webfiori\framework\writers\ClassWriter;

/**
 * A class which is used to write database table classes.
 * 
 * This class is used to write new table class based on a temporary 
 * table object. It is used as a helper class if the command 'create' is executed 
 * from CLI and the option 'Database Table Class' is selected. 
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class TableClassWriter extends ClassWriter {
    /**
     *
     * @var EntityMapper|null
     * @since 1.0 
     */
    private $entityMapper;
    /**
     *
     * @var Table 
     */
    private $tableObj;
    /**
     * Creates new instance of the class.
     * 
     * @param Table $tableObj An object of type 'webfiori\database\Table' which contains the 
     * information of the table class that will be created.
     * @param array $classInfoArr An associative array that contains the information 
     * of the class that will be created. The array must have the following indices: 
     * <ul>
     * <li><b>name</b>: The name of the class that will be created. If not provided, the 
     * string 'NewClass' is used.</li>
     * <li><b>namespace</b>: The namespace that the class will belong to. If not provided, 
     * the namespace 'webfiori' is used.</li>
     * <li><b>path</b>: The location at which the query will be created on. If not 
     * provided, the constant ROOT_DIR is used. </li>
     * <li><b>entity-info</b>: A sub associative array that contains information about the entity 
     * at which the class is mapped to (if any). The array must have the following indices:
     * <ul>
     * <li><b>name</b>: The name of the entity class that will be created.</li>
     * <li><b>path</b>: The location at which the entity class will be created on.</li>
     * <li><b>namespace</b>: The namespace at which the entity belongs to.</li>
     * <li><b>implement-jsoni</b>: A bollean which is set to true if the entity 
     * class will implement the interface 'JsonI'.</li>
     * </ul>
     * </li>
     * </ul>
     * 
     * 
     * @since 1.0
     */
    public function __construct($tableObj = null) {
        parent::__construct('NewTable', ROOT_DIR.DS.APP_DIR.DS.'database', APP_DIR.'\\database');
        $this->setSuffix('Table');
        if ($tableObj === null) {
            $this->setTableType('mysql');
            return;
        }
        $this->setTable($tableObj);
        
    }
    /**
     * Extract and return the name of table class based on associated table object.
     * 
     */
    private function extractAndSetTableClassName() {
        $clazz = get_class($this->getTable());
        
        $split = explode('\\', $clazz);
        $count = count($split);
        if ($count > 1) {
            $this->setClassName($split[$count - 1]);
            array_pop($split);
            $this->setNamespace(implode('\\', $split));
        } else {
            $this->setClassName($split[0]);
        }
    }
    /**
     * Returns the table object which was associated with the writer.
     * 
     * @return Table
     */
    public function getTable() : Table {
        return $this->tableObj;
    }
    /**
     * Sets the entity class info which mapps to a record in the table.
     * 
     * @param string $className The name of the entity class.
     * 
     * @param string $namespace The namespace at which the entity class will
     * belongs to.
     * 
     * @param string $path The location at which the entity class will be
     * created at.
     * 
     * @param bool $imlJsonI If set to true, the entity class will implement the
     * interface JsonI.
     */
    public function setEntityInfo(string $className, string $namespace, string $path, bool $imlJsonI) {
        $this->entityMapper = new EntityMapper($this->tableObj, 
                    $className, 
                    $path, 
                    $namespace);
            $this->entityMapper->setUseJsonI($imlJsonI);
    }
    /**
     * Sets the table that the writer will use in writing the table class.
     * 
     * @param Table $table
     */
    public function setTable(Table $table) {
        $this->tableObj = $table;
        if ($table !== null) {
            $this->extractAndSetTableClassName();
        }
    }
    /**
     * Returns the name entity class will be created.
     * 
     * @return string|null If the entity class information is set, the method will 
     * return a string that represents the name of the entity class. 
     * 
     * Other than that, the method will return null.
     * 
     * @since 1.0
     */
    public function getEntityName() {
        if ($this->entityMapper !== null) {
            return $this->entityMapper->getEntityName();
        }
    }
    /**
     * Returns the namespace that the associated entity class belongs to.
     * 
     * @return string|null If the entity class information is set, the method will 
     * return a string that represents the namespace that the entity belongs to. 
     * Other than that, the method will return null.
     * 
     * @since 1.0
     */
    public function getEntityNamespace() {
        if ($this->entityMapper !== null) {
            return $this->entityMapper->getNamespace();
        }
    }

    /**
     * Returns the location at which the entity class will be created on.
     * 
     * @return string|null If the entity class information is set, the method will 
     * return a string that represents the path that the entity will be created on. 
     * Other than that, the method will return null.
     * 
     * @since 1.0
     */
    public function getEntityPath() {
        if ($this->entityMapper !== null) {
            return $this->entityMapper->getPath();
        }
    }
    /**
     * Write the query class.
     * 
     * This method will first attempt to create the query class. If it was created, 
     * it will create the entity class which is associated with it (if any 
     * entity is associated).
     * 
     * @since 1.0
     */
    public function writeClass() {
        $this->addAllUse();
        parent::writeClass();

        if ($this->entityMapper !== null) {
            $this->entityMapper->create();
        }
    }
    private function _addCols() {
        $this->append('$this->addColumns([', 2);

        foreach ($this->tableObj->getCols() as $key => $colObj) {
            $this->_appendColObj($key, $colObj);
        }
        $this->append(']);', 2);
    }
    private function _addFks() {
        $fks = $this->tableObj->getForignKeys();

        foreach ($fks as $fkObj) {
            $refTableNs = get_class($fkObj->getSource());
            $cName = $this->getNamespace().'\\'.$this->getName();
            $refTableClassName = '$this';
            
            if ($cName != $refTableNs) {
                $nsSplit = explode('\\', $refTableNs);
                $refTableClassName = 'new '.$nsSplit[count($nsSplit) - 1].'()';
            }

            $this->append('$this->addReference('.$refTableClassName.', [', 2);
            $ownerCols = array_keys($fkObj->getOwnerCols());
            $sourceCols = array_keys($fkObj->getSourceCols());

            for ($x = 0 ; $x < count($ownerCols) ; $x ++) {
                $this->append("'$ownerCols[$x]' => '$sourceCols[$x]',", 3);
            }
            $this->append("], '".$fkObj->getKeyName()."', '".$fkObj->getOnUpdate()."', '".$fkObj->getOnDelete()."');", 2);
        }
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _appendColObj($key, $colObj) {
        $dataType = $colObj->getDatatype();
        $this->append("'$key' => [", 3);
        $this->append("'type' => '".$colObj->getDatatype()."',", 4);

        if (($dataType == 'int' && $colObj instanceof MySQLColumn) 
                || $dataType == 'varchar' 
                || $dataType == 'decimal' 
                || $dataType == 'float' 
                || $dataType == 'double'
                || $dataType == 'binary'
                || $dataType == 'varbinary'
                || $dataType == 'char'
                || $dataType == 'nchar'
                || $dataType == 'nvarchar') {
            $this->append("'size' => '".$colObj->getSize()."',", 4);

            if ($dataType == 'decimal') {
                $this->append("'scale' => '".$colObj->getScale()."',", 4);
            }
        }
        if ($colObj instanceof MSSQLColumn && $colObj->isIdentity()) {
            $this->append("'identity' => true,", 4);
        }
        if ($colObj->isPrimary()) {
            $this->append("'primary' => true,", 4);

            if ($colObj instanceof MySQLColumn && $colObj->isAutoInc()) {
                $this->append("'auto-inc' => true,", 4);
            }
        }

        if ($colObj->isUnique()) {
            $this->append("'is-unique' => true,", 4);
        }

        if ($colObj->getDefault() !== null) {
            $defaultVal = "'default' => '".$colObj->getDefault()."',";
            if ($dataType == 'bool' || $dataType == 'boolean') {
                $defaultVal = $colObj->getDefault() === true ? "'default' => true," : "'default' => false,";
            } else if ($dataType == 'int' || $dataType == 'bigint' || $dataType == 'decimal' || $dataType == 'money') {
                $defaultVal = "'default' => ".$colObj->getDefault().","; 
            }
            $this->append($defaultVal, 4);
        }

        if ($colObj->isNull()) {
            $this->append("'is-null' => true,", 4);
        }

        if ($colObj->getComment() !== null) {
            $this->append("'comment' => '".$colObj->getComment()."',", 4);
        }
        $this->append("],", 3);
    }
    private function _writeConstructor() {
        $this->append([
            "/**",
            " * Creates new instance of the class.",
            " */",
            $this->f('__construct'),
        ], 1);
        $this->append('parent::__construct(\''.$this->tableObj->getNormalName().'\');', 2);

        if ($this->tableObj->getComment() !== null) {
            $this->append('$this->setComment(\''.$this->tableObj->getComment().'\');', 2);
        }
        $this->_addCols();
        $this->_addFks();
        $this->append('}', 1);
    }
    /**
     * Sets the type of database table engine.
     * 
     * @param string $type The name of database server. It can have one of the
     * following values:
     * <ul>
     * <li>mssql</li>
     * <li>mysql</li>
     * </ul>
     * 
     */
    public function setTableType(string $type) {
        if ($type == 'mssql') {
            $this->tableObj = new MSSQLTable();
        } else if ($type == 'mysql') {
            $this->tableObj = new MySQLTable();
        }
    }
    private function addAllUse() {

        if ($this->tableObj instanceof MySQLTable) {
            $this->addUseStatement("webfiori\database\mysql\MySQLTable");
        } else if ($this->tableObj instanceof MSSQLTable) {
            $this->addUseStatement("webfiori\database\mssql\MSSQLTable");
        }
        $this->addFksUseTables();
    }
    private function addFksUseTables() {
        if ($this->tableObj !== null) {
            $fks = $this->tableObj->getForignKeys();
            $addedRefs = [];

            foreach ($fks as $fkObj) {
                $refTableNs = get_class($fkObj->getSource());

                if (!in_array($refTableNs, $addedRefs)) {
                    $this->addUseStatement($refTableNs);
                    $addedRefs[] = $refTableNs;
                }
            }
        }
    }

    public function writeClassBody() {
        $this->_writeConstructor();
        $this->append('}');
    }

    public function writeClassComment() {
        $this->append("/**\n"
                ." * A class which represents the database table '".$this->tableObj->getNormalName()."'.\n"
                ." * The table which is associated with this class will have the following columns:\n"
                ." * <ul>"
                );

        foreach ($this->tableObj->getCols() as $key => $colObj) {
            $this->append(" * <li><b>$key</b>: Name in database: '".$colObj->getNormalName()."'. Data type: '".$colObj->getDatatype()."'.</li>");
        }
        $this->append(" * </ul>\n */");
    }

    public function writeClassDeclaration() {
        if ($this->tableObj instanceof MySQLTable) {
            $this->append('class '.$this->getName().' extends MySQLTable {');
        } else if ($this->tableObj instanceof MSSQLTable) {
            $this->append('class '.$this->getName().' extends MSSQLTable {');
        }
    }

}
