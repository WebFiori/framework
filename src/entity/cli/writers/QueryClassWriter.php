<?php
/**
 * MIT License
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh, phMysql library.
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace webfiori\entity\cli;

use InvalidArgumentException;
use phMysql\EntityMapper;
use phMysql\MySQLColumn;
use phMysql\MySQLQuery;

/**
 * A class which is used to write query class from an instance of the class 
 * 'MySQLQuery'.
 * This class is used to write new query class based on a temporary 
 * query object. It is used as a helper class if the command 'create' is executed 
 * from CLI and the option 'Query class' is selected. 
 *
 * @author Ibrahim
 * @version 1.0
 */
class QueryClassWriter extends ClassWriter {
    /**
     *
     * @var EntityMapper|null
     * @since 1.0 
     */
    private $entityMapper;
    /**
     *
     * @var MySQLQuery 
     */
    private $queryObj;
    /**
     * Creates new instance of the class.
     * @param MySQLQuery $queryObj An object of type 'MySQLQuery' which contains the 
     * information of the query class that will be created.
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
     * @throws InvalidArgumentException If the first parameter is not an object of 
     * type 'MySQLQuery'.
     * @since 1.0
     */
    public function __construct($queryObj, $classInfoArr) {
        parent::__construct($classInfoArr);

        if (!$queryObj instanceof MySQLQuery) {
            throw new InvalidArgumentException('The given object is not an instance of the class \'MySQLQuery\'');
        }
        $this->queryObj = $queryObj;

        if (isset($classInfoArr['entity-info'])) {
            $this->entityMapper = new EntityMapper($this->queryObj->getTable(), 
                    $classInfoArr['entity-info']['name'], 
                    $classInfoArr['entity-info']['path'], 
                    $classInfoArr['entity-info']['namespace']);
            $this->entityMapper->setUseJsonI($classInfoArr['entity-info']['implement-jsoni']);
        }
        $this->_writeHeaderSec();
        $this->_writeConstructor();
        $this->_addQueries();
        $this->append('}');
    }
    /**
     * Returns the name entity class will be created.
     * @return string|null If the entity class information is set, the method will 
     * return a string that represents the name of the entity class. 
     * Other than that, the method will return null.
     * @since 1.0
     */
    public function getEntityName() {
        if ($this->entityMapper !== null) {
            return $this->entityMapper->getEntityName();
        }
    }
    /**
     * Returns the namespace that the associated entity class belongs to.
     * @return string|null If the entity class information is set, the method will 
     * return a string that represents the namespace that the entity belongs to. 
     * Other than that, the method will return null.
     * @since 1.0
     */
    public function getEntityNamespace() {
        if ($this->entityMapper !== null) {
            return $this->entityMapper->getNamespace();
        }
    }

    /**
     * Returns the location at which the entity class will be created on.
     * @return string|null If the entity class information is set, the method will 
     * return a string that represents the path that the entity will be created on. 
     * Other than that, the method will return null.
     * @since 1.0
     */
    public function getEntityPath() {
        if ($this->entityMapper !== null) {
            return $this->entityMapper->getPath();
        }
    }
    /**
     * Write the query class.
     * This method will first attempt to create the query class. If it was created, 
     * it will create the entity class which is associated with it (if any 
     * entity is associated).
     * @since 1.0
     */
    public function writeClass() {
        parent::writeClass();

        if ($this->entityMapper !== null) {
            $this->entityMapper->create();
        }
    }
    private function _addCols() {
        $defaultColsKeys = $this->queryObj->getTable()->getDefaultColsKeys();
        $hasDefault = false;
        $defaultKeysArr = [];

        foreach ($defaultColsKeys as $val) {
            if ($val !== null) {
                $hasDefault = true;
            }
        }

        if ($hasDefault) {
            $this->append('$this->getTable()->addDefaultCols([', 2);

            foreach ($defaultColsKeys as $key => $val) {
                if ($val !== null) {
                    $defaultKeysArr[] = $key;
                    $this->append("'$key' => [],", 3);
                }
            }
            $this->append(']);', 2);
        }
        $this->append('$this->getTable()->addColumns([', 2);

        foreach ($this->queryObj->getTable()->getColumns() as $key => $colObj) {
            if (!in_array($key, $defaultKeysArr)) {
                $this->_appendColObj($key, $colObj);
            }
        }
        $this->append(']);', 2);
    }
    private function _addFks() {
        $fks = $this->queryObj->getTable()->getForeignKeys();

        foreach ($fks as $fkObj) {
            $this->append('$this->getTable()->addReference($this, [', 2);
            $ownerCols = array_keys($fkObj->getOwnerCols());
            $sourceCols = array_keys($fkObj->getSourceCols());

            for ($x = 0 ; $x < count($ownerCols) ; $x ++) {
                $this->append("'$ownerCols[$x]' => '$sourceCols[$x]',", 3);
            }
            $this->append("], '".$fkObj->getKeyName()."', '".$fkObj->getOnUpdate()."', '".$fkObj->getOnDelete()."');", 2);
        }
    }
    private function _addQueries() {
        $colsKeys = $this->queryObj->getTable()->colsKeys();
        $defaultColsKeys = $this->queryObj->getTable()->getDefaultColsKeys();

        if ($this->entityMapper !== null) {
            $this->append('/**', 1);
            $this->append(' * Constructs a query that can be used to add new record to the table.', 1);
            $this->append(' * @param '.$this->getEntityName().' $entity An instance of the class "'.$this->getEntityName().'" that contains record information.', 1);
            $this->append(' */', 1);
            $this->append('public function add($entity) {', 1);
            $this->append('$this->insertRecord([', 2);
            $index = 0;

            foreach ($colsKeys as $colKey) {
                if (!(isset($defaultColsKeys[$colKey]) && $defaultColsKeys[$colKey] !== null)) {
                    $this->append("'$colKey' => \$entity->".$this->entityMapper->mapToMethodName($colKey).'(),', 3);
                }
                $index++;
            }
            $this->append(']);', 2);
            $this->append('}', 1);

            $primaryKeys = $this->queryObj->getTable()->getPrimaryColsKeys();

            if (count($primaryKeys) == 0) {
                $primaryKeys = $this->queryObj->getTable()->getUniqueColsKeys();
            }

            if (count($primaryKeys) !== 0) {
                $this->_writeUpdateQuery($colsKeys, $primaryKeys);
                $this->_writeDeleteQuery($colsKeys, $primaryKeys);
                $this->_writeSelectAllQuery();
            }
        }
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _appendColObj($key, $colObj) {
        $dataType = $colObj->getType();
        $this->append("'$key' => [", 3);
        $this->append("'type' => '".$colObj->getType()."',", 4);

        if ($dataType == 'int' || $dataType == 'varchar' || $dataType == 'decimal' || 
                $dataType == 'float' || $dataType == 'double') {
            $this->append("'size' => '".$colObj->getSize()."',", 4);
        }

        if ($colObj->isPrimary()) {
            $this->append("'primary' => true,", 4);

            if ($colObj->isAutoInc()) {
                $this->append("'is-unique' => true,", 4);
            }
        }

        if ($colObj->isUnique()) {
            $this->append("'is-unique' => true,", 4);
        }

        if ($colObj->getDefault() !== null) {
            if ($colObj->getType() == 'bool' || $colObj->getType() == 'boolean') {
                if ($colObj->getDefault() === true) {
                    $this->append("'default' => true,", 4);
                } else {
                    $this->append("'default' => false,", 4);
                }
            } else {
                $this->append("'default' => '".$colObj->getDefault()."',", 4);
            }
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
        $this->append("/**", 1);
        $this->append(" * Creates new instance of the class.", 1);
        $this->append(" */", 1);
        $this->append('public function __construct(){', 1);
        $this->append('parent::__construct(\''.$this->queryObj->getTableName().'\');', 2);

        if ($this->queryObj->getTable()->getComment() !== null) {
            $this->append('$this->getTable()->setComment(\''.$this->queryObj->getTable()->getComment().'\');', 2);
        }
        $this->_addCols();
        $this->_addFks();
        $this->append('}', 1);
    }
    private function _writeDeleteQuery($colsKeys, $primaryKeys) {
        $this->append('/**', 1);
        $this->append(' * Constructs a query that can be used to remove a record.', 1);
        $this->append(' * @param '.$this->getEntityName().' $entity An instance of the class "'.$this->getEntityName().'" that contains record information.', 1);
        $this->append(' */', 1);
        $this->append("public function delete(\$entity) {",1);
        $this->append('$this->deleteRecord([', 2);

        foreach ($colsKeys as $colKey) {
            if (in_array($colKey, $primaryKeys)) {
                $this->append("'$colKey' => \$entity->".$this->entityMapper->mapToMethodName($colKey, 'g').'(),', 3);
            }
        }
        $this->append(']);', 2);
        $this->append('}',1);
    }
    private function _writeHeaderSec() {
        $this->append("<?php\n");
        $this->append('namespace '.$this->getNamespace().";\n");
        $this->append("use phMysql\MySQLQuery;");

        if ($this->entityMapper !== null) {
            $this->append('use '.$this->getEntityNamespace().'\\'.$this->getEntityName().';');
        }
        $this->append('');
        $this->append("/**\n"
                ." * A query class which represents the database table '".$this->queryObj->getTableName()."'.\n"
                ." * The table which is associated with this class will have the following columns:\n"
                ." * <ul>"
                );

        foreach ($this->queryObj->getTable()->getColumns() as $key => $colObj) {
            $this->append(" * <li><b>$key</b>: Name in database: '".$colObj->getName()."'. Data type: '".$colObj->getType()."'.</li>");
        }
        $this->append(" * </ul>\n */");
        $this->append('class '.$this->getName().' extends MySQLQuery {');
    }
    private function _writeSelectAllQuery() {
        $this->append("/**", 1);
        $this->append(" * Constructs a query that can be used to select all records from the table.", 1);
        $this->append(" * @param int \$limit The number of records that will be selected. Default is -1", 1);
        $this->append(" * @param int \$offset The number of records that will be skipped from the first row. Default is -1.", 1);
        $this->append(" */", 1);
        $this->append('public function selectAll($limit = -1, $offset = -1) {', 1);
        $this->append('$this->select([', 2);
        $this->append("'limit' => \$limit,", 3);
        $this->append("'offset' => \$offset,", 3);

        if (strlen($this->getEntityName()) != 0) {
            $this->append("'map-result-to' => '".$this->getEntityNamespace().'\\'.$this->getEntityName()."',", 3);
        }
        $this->append("]);", 2);
        $this->append('}', 1);
    }
    private function _writeUpdateQuery($colsKeys, $primaryKeys) {
        $this->append('/**', 1);
        $this->append(' * Constructs a query that can be used to update a record.', 1);
        $this->append(' * @param '.$this->getEntityName().' $entity An instance of the class "'.$this->getEntityName().'" that contains record information.', 1);
        $this->append(' */', 1);
        $this->append("public function update(\$entity) {", 1);
        $this->append('$this->updateRecord([', 2);

        foreach ($colsKeys as $colKey) {
            if (!in_array($colKey, $primaryKeys)) {
                $this->append("'$colKey' => \$entity->".$this->entityMapper->mapToMethodName($colKey, 'g').'(),', 3);
            }
        }
        $this->append('], [', 2);

        foreach ($colsKeys as $colKey) {
            if (in_array($colKey, $primaryKeys)) {
                $this->append("'$colKey' => \$entity->".$this->entityMapper->mapToMethodName($colKey, 'g').'(),', 3);
            }
        }
        $this->append(']);', 2);
        $this->append('}', 1);
    }
}
