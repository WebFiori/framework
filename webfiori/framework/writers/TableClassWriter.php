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

use const APP_DIR;
use const APP_PATH;
use webfiori\database\ColOption;
use webfiori\database\Column;
use webfiori\database\DataType;
use webfiori\database\EntityMapper;
use webfiori\database\FK;
use webfiori\database\mssql\MSSQLColumn;
use webfiori\database\mssql\MSSQLTable;
use webfiori\database\mysql\MySQLColumn;
use webfiori\database\mysql\MySQLTable;
use webfiori\database\Table;

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
     * provided, the constant ROOT_PATH is used. </li>
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
    public function __construct(?Table $tableObj = null) {
        parent::__construct('NewTable', APP_PATH.'database', APP_DIR.'\\database');
        $this->setSuffix('Table');

        if ($tableObj === null) {
            $this->setTableType('mysql');

            return;
        }
        $this->setTable($tableObj);
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

    public function writeClassBody() {
        $this->writeConstructor();
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
    private function addAllUse() {
        if ($this->tableObj instanceof MySQLTable) {
            $this->addUseStatement("webfiori\database\mysql\MySQLTable");
        } else if ($this->tableObj instanceof MSSQLTable) {
            $this->addUseStatement("webfiori\database\mssql\MSSQLTable");
        }
        $this->addUseStatement(ColOption::class);
        $this->addUseStatement(DataType::class);
        $this->addFksUseTables();
    }
    private function addColsHelper() {
        $this->append('$this->addColumns([', 2);

        foreach ($this->tableObj->getCols() as $key => $colObj) {
            $this->appendColObj($key, $colObj);
        }
        $this->append(']);', 2);
    }
    private function addFKOption(Column $colObj) {
        $fks = $this->getTable()->getForeignKeys();

        foreach ($fks as $fk) {
            $sourceCols = array_values($fk->getOwnerCols());

            if (count($sourceCols) == 1 && $sourceCols[0]->getNormalName() == $colObj->getNormalName()) {
                $this->addFKOptionHelper($colObj, $fk);
            }
        }
    }
    private function addFKOptionHelper(Column $col, FK $fk) {
        $refTableNs = get_class($fk->getSource());
        $cName = $this->getNamespace().'\\'.$this->getName();
        $refTableClassName = '$this';

        if ($cName != $refTableNs) {
            $nsSplit = explode('\\', $refTableNs);
            $refTableClassName = 'new '.$nsSplit[count($nsSplit) - 1].'()';
        }
        $keyName = $fk->getKeyName();
        $sourceCol = array_keys($fk->getSourceCols())[0];
        $this->append("ColOption::FK => [", 4);
        $this->append("ColOption::FK_NAME => '".$keyName."',", 5);
        $this->append("ColOption::FK_TABLE => ".$refTableClassName.",", 5);
        $this->append("ColOption::FK_COL => '".$sourceCol."',", 5);
        $this->append("ColOption::FK_ON_UPDATE => ".$this->getFkCond($fk->getOnUpdate()).",", 5);
        $this->append("ColOption::FK_ON_DELETE => ".$this->getFkCond($fk->getOnDelete()).",", 5);
        $this->append("],", 4);
    }
    private function addFksHelper() {
        $fks = $this->tableObj->getForeignKeys();

        foreach ($fks as $fkObj) {
            if (count($fkObj->getSourceCols()) == 1) {
                continue;
            }
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
    private function addFksUseTables() {
        if ($this->tableObj !== null) {
            $fks = $this->tableObj->getForeignKeys();

            if (count($fks) != 0) {
                $this->addUseStatement(FK::class);
            }
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
    /**
     *
     * @param MySQLColumn $colObj
     */
    private function appendColObj($key, $colObj) {
        $dataType = $colObj->getDatatype();
        $this->append("'$key' => [", 3);
        $this->append("ColOption::TYPE => ".$this->getType($colObj->getDatatype()).",", 4);

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
            $this->append("ColOption::SIZE => '".$colObj->getSize()."',", 4);

            if ($dataType == 'decimal') {
                $this->append("ColOption::SCALE => '".$colObj->getScale()."',", 4);
            }
        }

        if ($colObj instanceof MSSQLColumn && $colObj->isIdentity()) {
            $this->append("ColOption::IDENTITY => true,", 4);
        }

        if ($colObj->isPrimary()) {
            $this->append("ColOption::PRIMARY => true,", 4);

            if ($colObj instanceof MySQLColumn && $colObj->isAutoInc()) {
                $this->append("ColOption::AUTO_INCREMENT => true,", 4);
            }
        }

        if ($colObj->isUnique()) {
            $this->append("ColOption::UNIQUE => true,", 4);
        }

        if ($colObj->getDefault() !== null) {
            $defaultVal = "ColOption::DEFAULT => '".$colObj->getDefault()."',";

            if (in_array($dataType, Column::BOOL_TYPES)) {
                $defaultVal = $colObj->getDefault() === true ? "ColOption::DEFAULT => true," : "ColOption::DEFAULT => false,";
            } else if ($dataType == 'int' || $dataType == 'bigint' || $dataType == 'decimal' || $dataType == 'money') {
                $defaultVal = "ColOption::DEFAULT => ".$colObj->getDefault().",";
            }
            $this->append($defaultVal, 4);
        }

        if ($colObj->isNull()) {
            $this->append("ColOption::NULL => true,", 4);
        }

        if ($colObj->getComment() !== null) {
            $this->append("ColOption::COMMENT => '".$colObj->getComment()."',", 4);
        }
        $this->addFKOption($colObj);
        $this->append("],", 3);
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
    private function getFkCond(string $txt) {
        switch ($txt) {
            case 'cascade' :{
                return 'FK::CASCADE';
            }
            case 'no action' :{
                return 'FK::NO_ACTION';
            }
            case 'restrict' :{
                return 'FK::RESTRICT';
            }
            case 'set default' :{
                return 'FK::SET_DEFAULT';
            }
            case 'set null' :{
                return 'FK::SET_NULL';
            }
        }
    }
    private function getType(string $dataType) {
        switch ($dataType) {
            case 'bigint' : {
                return 'DataType::BIGINT';
            }
            case 'binary' : {
                return 'DataType::BINARY';
            }
            case 'bit' : {
                return 'DataType::BIT';
            }
            case 'blob' : {
                return 'DataType::BLOB';
            }
            case 'longblob' : {
                return 'DataType::BLOB_LONG';
            }
            case 'mediumblob' : {
                return 'DataType::BLOB_MEDIUM';
            }
            case 'tinyblob' : {
                return 'DataType::BLOB_TINY';
            }
            case 'bool' : {
                return 'DataType::BOOL';
            }
            case 'boolean' : {
                return 'DataType::BOOL';
            }
            case 'char' : {
                return 'DataType::CHAR';
            }
            case 'date' : {
                return 'DataType::DATE';
            }
            case 'datetime' : {
                return 'DataType::DATETIME';
            }
            case 'datetime2' : {
                return 'DataType::DATETIME2';
            }
            case 'decimal' : {
                return 'DataType::DECIMAL';
            }
            case 'double' : {
                return 'DataType::DOUBLE';
            }
            case 'float' : {
                return 'DataType::FLOAT';
            }
            case 'int' : {
                return 'DataType::INT';
            }
            case 'money' : {
                return 'DataType::MONEY';
            }
            case 'nchar' : {
                return 'DataType::NCHAR';
            }
            case 'nvarchar' : {
                return 'DataType::NVARCHAR';
            }
            case 'text' : {
                return 'DataType::TEXT';
            }
            case 'medumtext' : {
                return 'DataType::TEXT_MEDIUM';
            }
            case 'time' : {
                return 'DataType::TIME';
            }
            case 'timestamp' : {
                return 'DataType::TIMESTAMP';
            }
            case 'varbinary' : {
                return 'DataType::VARBINARY';
            }
            case 'varchar' : {
                return 'DataType::VARCHAR';
            }
            default : {
                return "'mixed'";
            }
        }
    }
    private function writeConstructor() {
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
        $this->addColsHelper();
        $this->addFksHelper();
        $this->append('}', 1);
    }
}
