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
namespace WebFiori\Framework\Writers;

use WebFiori\Database\Column;
use WebFiori\Database\Entity\EntityMapper;
use WebFiori\Database\MsSql\MSSQLColumn;
use WebFiori\Database\MsSql\MSSQLTable;
use WebFiori\Database\Table;
use WebFiori\Framework\DB;
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
    private $connName;
    private $entityName;
    private $includeUpdate;
    private $paramsArr;
    private $whereArr;
    /**
     * Creates new instance of the class.
     *
     * @param string $className The name of the class that will be created.
     *
     * @param string $ns The namespace at which the class will belong to.
     *
     * @param Table $table The table instance at which the class will build
     * database operations based on.
     */
    public function __construct(?string $className = 'NewDBOperationsClass', string $ns = '\\', ?Table $table = null) {
        parent::__construct($className, ROOT_PATH.DS.$ns, $ns);

        if ($table !== null) {
            $this->setTable($table);
        }
        $this->addUseStatement(DB::class);
        $this->includeUpdate = false;
    }
    /**
     * Returns the name of the connection at which the generated class will use to connect
     * to database.
     *
     * @return string|null The name of the connection. If not set, null is returned.
     */
    public function getConnectionName() {
        return $this->connName;
    }
    /**
     * Returns the name of the entity at which the class will use to map records.
     *
     * The name of the entity is taken from entity mapper which is associated
     * with the table at which database operations are based on.
     *
     * @return string Class name of the entity.
     */
    public function getEntityName() : string {
        return $this->entityName;
    }
    /**
     * Returns the table instance at which the class will build
     * database operations based on.
     *
     * @return Table|null If the table is set, it will be returned as an object.
     * If not set, null is returned.
     */
    public function getTable() {
        return $this->associatedTable;
    }
    /**
     * Returns an array that contains the keys of columns which are set as primary
     * unique or identity.
     *
     * Note that if the table has identity column, only the key of this column
     * is returned. Other than that, the keys of the primary columns and
     * unique columns are returned.
     *
     * @return array An array that contains the keys of columns which are set as primary
     * or unique.
     */
    public function getUniqueColsKeys() : array {
        $table = $this->getTable();

        if ($table instanceof MSSQLTable && $table->hasIdentity()) {
            $cols = [];

            foreach ($table->getCols() as $key => $col) {
                if ($col->isIdentity()) {
                    $cols[] = $key;
                    break;
                }
            }

            return $cols;
        }

        $recordUniqueCols = $table->getPrimaryKeyColsKeys();

        if (count($recordUniqueCols) == 0) {
            $recordUniqueCols = $table->getUniqueColsKeys();
        }

        return $recordUniqueCols;
    }
    /**
     * Include update methods for each single column in the table that
     * is not unique.
     *
     * If this method is called, the writer will write one method for every
     * column in the table to update its value.
     */
    public function includeColumnsUpdate() {
        $this->includeUpdate = true;
    }
    /**
     * Checks if each non-unique table column will have its own update method.
     *
     * @return bool If each column will have its own update method, true is
     * returned. False otherwise.
     */
    public function isColumnUpdateIncluded() : bool {
        return $this->includeUpdate;
    }
    /**
     * Sets the name of the connection at which the generated class will use to connect
     * to database.
     *
     * @param string $connName The name of the connection as it was set in the
     * class 'AppConfig' of the application.
     */
    public function setConnection(string $connName) {
        $trimmed = trim($connName);

        if (strlen($trimmed) != 0) {
            $this->connName = $trimmed;
        }
    }
    /**
     * Sets the table at which the class will create logic to perform operations
     * on.
     *
     * @param Table $t
     */
    public function setTable(Table $t) {
        $temp = $this->getTable();

        if ($temp !== null) {
            $this->removeUseStatement($temp->getEntityMapper()->getEntityName(true));
        }
        $this->associatedTable = $t;
        $mapper = $t->getEntityMapper();
        $this->entityName = $mapper->getEntityName();
        $this->addUseStatement($mapper->getNamespace().'\\'.$mapper->getEntityName());
        $this->createParamsAndWhereArr();
    }
    /**
     * Maps key name to entity method name.
     *
     * @param string $colKey The name of column key such as 'user-id'.
     *
     * @param string $prefix The type of the method. This one can have only two values,
     * 's' for setter method and 'g' for getter method. Default is 'g'.
     *
     * @return string The name of the mapped method name. If the passed column
     * key is empty string, the method will return empty string.
     *
     * @since 1.0
     */
    public static function toMethodName(string $colKey, $prefix = 'g') {
        $trimmed = trim($colKey);


        $split = explode('-', $trimmed);
        $methodName = '';

        foreach ($split as $namePart) {
            if (strlen($namePart) == 1) {
                $methodName .= strtoupper($namePart);
            } else {
                $firstChar = $namePart[0];
                $methodName .= strtoupper($firstChar).substr($namePart, 1);
            }
        }

        return $prefix.$methodName;
    }
    /**
     * Writes the body of the class.
     */
    public function writeClassBody() {
        $this->append([
            'private static $instance;',
            '/**',
            ' * Returns an instance of the class.',
            ' * ',
            ' * Calling this method multiple times will return same instance.',
            ' * ',
            ' * @return '.$this->getName().' An instance of the class.',
            ' */',
            'public static function get() : '.$this->getName().' {'
        ], 1);
        $this->append('');
        $this->append('if (self::$instance === null) {', 2);
        $this->append('self::$instance = new '.$this->getName().'();', 3);
        $this->append('}', 2);
        $this->append('');
        $this->append('return self::$instance;', 2);
        $this->append('}', 1);
        $this->append([
            "/**",
            " * Creates new instance of the class.",
            " */",
            $this->f('__construct')
        ], 1);

        if ($this->getConnectionName() !== null) {
            $this->append([
                "parent::__construct('".$this->getConnectionName()."');",
            ], 2);
        } else {
            $this->append([
                '//TODO: Specify the name of database connection to use in performing operations.',
                "parent::__construct('');",
            ], 2);
        }
        $this->append([
            "\$this->register('".str_replace("\\", "\\\\", $this->getNamespace())."');",
        ], 2);
        $this->append('}', 1);

        $this->writeAddRecord();

        $this->writeDeleteRecord();
        $this->writeGetRecord();
        $this->writeGetRecords();
        $this->writeGetRecordsCount();
        $this->writeUpdateRecord();

        if ($this->isColumnUpdateIncluded()) {
            $this->writeUpdateRecordMethods();
        }

        $this->append('}', 0);
    }
    /**
     * Writes the comment that will appear at the top of the class.
     */
    public function writeClassComment() {
        $t = $this->getTable();

        if ($t === null) {
            return;
        }
        $this->append([
            "/**",
            " * A class which is used to perform operations on the table '".$t->getNormalName()."'",
            " */"
        ]);
    }
    /**
     * Writes the string that represent class declaration.
     */
    public function writeClassDeclaration() {
        $this->append('class '.$this->getName().' extends DB {');
    }
    private function createParamsAndWhereArr() {
        $t = $this->getTable();

        if ($t === null) {
            return;
        }
        $cols = $this->getUniqueColsKeys();
        $this->paramsArr = [];
        $this->whereArr = [];

        foreach ($cols as $key) {
            $colObj = $t->getColByKey($key);
            $this->paramsArr[$colObj->getNormalName()] = $colObj->getPHPType();
            $this->whereArr[] = count($this->whereArr) == 0 ? "->where('$key', $".$colObj->getNormalName().")"
                    : "->andWhere('$key', $".$colObj->getNormalName().")";
        }
    }

    private function writeAddRecord() {
        $t = $this->getTable();

        if ($t === null) {
            return;
        }
        $this->append([
            "/**",
            " * Adds new record to the table '".$t->getNormalName()."'.",
            " *",
            " * @param ".$this->getEntityName().' $entity An object that holds record information.',
            " */",
            $this->f('add'.$this->getEntityName(), ['entity' => $this->getEntityName()])
        ], 1);
        $recordsArr = [];

        foreach ($t->getEntityMapper()->getGettersMap(true) as $methName => $col) {
            $colObj = $t->getColByKey($col);

            if ($colObj instanceof MSSQLColumn) {
                if (!$colObj->isIdentity() && !($colObj->getDatatype() == 'datetime2' && $colObj->getDefault() !== null)) {
                    $recordsArr[] = "'$col' => \$entity->$methName(),";
                }
            } else {
                if (!$colObj->isAutoInc() && !($colObj->getDatatype() == 'timestamp' && $colObj->getDefault() !== null)) {
                    $recordsArr[] = "'$col' => \$entity->$methName(),";
                }
            }
        }
        $this->append([
            "\$this->table('".$t->getNormalName()."')->insert(["
        ], 2);
        $this->append($recordsArr, 3);
        $this->append('])->execute();', 2);

        $this->append('}', 1);
    }
    private function writeColUpdate(Column $colObj, $key) {
        $phpType = $colObj->getPHPType();
        $t = $this->getTable();
        $this->append([
            "/**",
            " * Updates the value of the column '".$colObj->getNormalName()."' on the table '".$t->getNormalName()."'.",
        ], 1);

        if (count($this->paramsArr) != 0) {
            foreach ($this->paramsArr as $name => $type) {
                $paramsComment[] = ' *';
                $paramsComment[] = " * @param $type \$$name One of the values which are used in 'where' condition.";
            }
        }

        $firstParamName = $colObj->isNull() ? 'newVal = null' : 'newVal';
        $paramsComment[] = ' *';
        $paramsComment[] = " * @param $phpType \$newVal The new value for the column.";
        $this->append($paramsComment, 1);

        if (strpos($phpType, '|null') !== false) {
            $phpType = '?'.substr($phpType,0, strlen($phpType) - strlen('|null'));
        }
        $this->append([
            " */",

            $this->f(self::toMethodName($key, 'update'), array_merge(
                $this->paramsArr,
                [$firstParamName => $phpType]
            ))
        ], 1);
        $this->append("\$this->table('".$t->getNormalName()."')->update([", 2);
        $this->append("'$key' => \$newVal", 4);

        if (count($this->whereArr) == 0) {
            $this->append("])->execute();", 3);
            $this->append("//TODO: Specify conditions for updating the value of the record '".$colObj->getNormalName()."'", 3);
        } else {
            $this->append("])", 3);
            $this->append($this->whereArr, 3);
            $this->append('->execute();', 3);
        }

        $this->append('}', 1);
    }
    private function writeDeleteRecord() {
        $t = $this->getTable();

        if ($t === null) {
            return;
        }
        $this->append([
            "/**",
            " * Deletes a record from the table '".$t->getNormalName()."'.",
            " *",
            " * @param ".$this->getEntityName().' $entity An object that holds record information.',
            " */",
            $this->f('delete'.$this->getEntityName(), ['entity' => $this->getEntityName()]),
        ], 1);
        $this->append("\$this->table('".$t->getNormalName()."')", 2);

        if (count($this->paramsArr) != 0) {
            $this->append("->delete()", 4);
            $cols = [];

            foreach ($this->getUniqueColsKeys() as $key) {
                $cols[] = count($cols) == 0 ?
                        "->where('$key', \$entity->".EntityMapper::mapToMethodName($key).'())'
                        : "->andWhere('$key', \$entity->".EntityMapper::mapToMethodName($key).'())';
            }
            $this->append($cols, 4);
            $this->append("->execute();", 4);
        } else {
            $this->append("->delete();", 4);
            $this->append('//TODO: Specify delete record condition(s).', 3);
        }
        $this->append('}', 1);
    }

    private function writeGetRecord() {
        $t = $this->getTable();

        if ($t === null) {
            return;
        }
        $this->append([
            "/**",
            " * Returns the information of a record from the table '".$t->getNormalName()."'.",
            " *",
            " * @return ".$this->getEntityName().'|null If a record with given information exist,',
            " * The method will return an object which holds all record information.",
            " * Other than that, null is returned.",
            " */",
            $this->f('get'.$this->getEntityName(), $this->paramsArr)
        ], 1);
        $this->append("\$mappedRecords = \$this->table('".$t->getNormalName()."')", 2);
        $this->append("->select()", 4);

        if (count($this->paramsArr) != 0) {
            $this->append($this->whereArr, 4);
        } else {
            $this->append('//TODO: Specify select condition for retrieving one record.', 4);
        }
        $this->append("->execute()", 4);
        $this->append("->map(function (array \$record) {", 4);
        $this->append("return ".$this->getEntityName().'::map($record);', 5);
        $this->append("});", 4);
        $this->append('if ($mappedRecords->getRowsCount() == 1) {', 2);
        $this->append('return $mappedRecords->getRows()[0];', 3);
        $this->append('}', 2);
        $this->append('}', 1);
    }
    private function writeGetRecords() {
        $t = $this->getTable();

        if ($t === null) {
            return;
        }
        $this->append([
            "/**",
            " * Returns all the records from the table '".$t->getNormalName()."'.",
            " *",
            " * @param int \$pageNum The number of page to fetch. Default is 0.",
            " *",
            " * @param int \$pageSize Number of records per page. Default is 10.",
            " *",
            " * @return array An array that holds all table records as objects",
            " */",
            $this->f('get'.$this->getEntityName().'s', [
                'pageNum = 0' => 'int',
                'pageSize = 10' => 'int'
            ], 'array')
        ], 1);
        $this->append("return \$this->table('".$t->getNormalName()."')", 2);
        $this->append("->select()", 4);
        $this->append('->page($pageNum, $pageSize)', 4);
        $this->append('->orderBy(["id"])', 4);
        $this->append("->execute()", 4);
        $this->append("->map(function (array \$record) {", 4);
        $this->append("return ".$this->getEntityName().'::map($record);', 5);
        $this->append("})->toArray();", 4);
        $this->append('}', 1);
    }
    private function writeGetRecordsCount() {
        $t = $this->getTable();

        if ($t === null) {
            return;
        }
        $this->append([
            "/**",
            " * Returns number of records on the table '".$t->getNormalName()."'.",
            " *",
            " * The main use of this method is to compute number of pages.",
            " *",
            " * @return int Number of records on the table '".$t->getNormalName()."'.",
            " */",
            $this->f('get'.$this->getEntityName().'sCount', [], 'int')
        ], 1);
        $this->append("return \$this->table('".$t->getNormalName()."')", 2);
        $this->append("->selectCount()", 4);
        $this->append("->execute()", 4);
        $this->append("->getRows()[0]['count'];", 4);

        $this->append('}', 1);
    }
    private function writeUpdateRecord() {
        $t = $this->getTable();

        if ($t === null) {
            return;
        }
        $this->append([
            "/**",
            " * Updates a record on the table '".$t->getNormalName()."'.",
            " *",
            " * @param ".$this->getEntityName().' $entity An object that holds updated record information.',
            " */",
            $this->f('update'.$this->getEntityName(), ['entity' => $this->getEntityName()])
        ], 1);
        $this->append("\$this->table('".$t->getNormalName()."')", 2);
        $this->append("->update([", 3);
        $keys = $t->getColsKeys();

        if (count($this->paramsArr) != 0) {
            $updateCols = [];
            $whereCols = [];
            $uniqueCols = $this->getUniqueColsKeys();

            foreach ($uniqueCols as $key) {
                $whereCols[] = count($whereCols) == 0 ?
                        "->where('$key', \$entity->".EntityMapper::mapToMethodName($key).'())'
                        : "->andWhere('$key', \$entity->".EntityMapper::mapToMethodName($key).'())';
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
    private function writeUpdateRecordMethods() {
        $t = $this->getTable();

        if ($t === null) {
            return;
        }
        $uniqueKeys = $this->getUniqueColsKeys();

        foreach ($t->getCols() as $key => $colObj) {
            if (!in_array($key, $uniqueKeys)) {
                $this->writeColUpdate($colObj, $key);
            }
        }
    }
}
