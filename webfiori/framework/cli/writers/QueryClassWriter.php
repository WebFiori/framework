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
namespace webfiori\framework\cli;

use InvalidArgumentException;
use webfiori\database\EntityMapper;
use webfiori\database\mysql\MySQLColumn;
use webfiori\database\Table;

/**
 * A class which is used to write query class from an instance of the class 
 * 'MySQLQuery'.
 * 
 * This class is used to write new query class based on a temporary 
 * query object. It is used as a helper class if the command 'create' is executed 
 * from CLI and the option 'Query class' is selected. 
 *
 * @author Ibrahim
 * 
 * @version 1.0
 */
class QueryClassWriter extends ClassWriter {
    /**
     *
     * @var array
     * @since 1.0 
     */
    private $classInfoArr;
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
     * @throws InvalidArgumentException If the first parameter is not an object of 
     * type 'webfiori\database\Table'.
     * 
     * @since 1.0
     */
    public function __construct($tableObj, $classInfoArr) {
        parent::__construct($classInfoArr);

        if (!$tableObj instanceof Table) {
            throw new InvalidArgumentException('The given object is not an instance of the class "webfiori\database\Table".');
        }
        $this->tableObj = $tableObj;
        $this->classInfoArr = $classInfoArr;

        if (isset($classInfoArr['entity-info'])) {
            $this->entityMapper = new EntityMapper($this->tableObj, 
                    $classInfoArr['entity-info']['name'], 
                    $classInfoArr['entity-info']['path'], 
                    $classInfoArr['entity-info']['namespace']);
            $this->entityMapper->setUseJsonI($classInfoArr['entity-info']['implement-jsoni']);
        }
        $this->_writeHeaderSec();
        $this->_writeConstructor();
        $this->append('}');
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

            if ($cName == $refTableNs) {
                $refTableClassName = '$this';
            } else {
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

        if ($dataType == 'int' || $dataType == 'varchar' || $dataType == 'decimal' || 
                $dataType == 'float' || $dataType == 'double') {
            $this->append("'size' => '".$colObj->getSize()."',", 4);
        }

        if ($colObj->isPrimary()) {
            $this->append("'primary' => true,", 4);

            if ($colObj->isAutoInc()) {
                $this->append("'auto-inc' => true,", 4);
            }
        }

        if ($colObj->isUnique()) {
            $this->append("'is-unique' => true,", 4);
        }

        if ($colObj->getDefault() !== null) {
            if ($colObj->getDatatype() == 'bool' || $colObj->getDatatype() == 'boolean') {
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
        $this->append('parent::__construct(\''.$this->tableObj->getName().'\');', 2);

        if ($this->tableObj->getComment() !== null) {
            $this->append('$this->setComment(\''.$this->tableObj->getComment().'\');', 2);
        }
        $this->_addCols();
        $this->_addFks();
        $this->append('}', 1);
    }
    private function _writeHeaderSec() {
        $this->append("<?php\n");
        $this->append('namespace '.$this->getNamespace().";\n");
        $this->append("use webfiori\database\mysql\MySQLTable;");
        $this->addFksTables();

        $this->append('');
        $this->append("/**\n"
                ." * A class which represents the database table '".$this->tableObj->getName()."'.\n"
                ." * The table which is associated with this class will have the following columns:\n"
                ." * <ul>"
                );

        foreach ($this->tableObj->getCols() as $key => $colObj) {
            $this->append(" * <li><b>$key</b>: Name in database: '".$colObj->getName()."'. Data type: '".$colObj->getDatatype()."'.</li>");
        }
        $this->append(" * </ul>\n */");
        $this->append('class '.$this->getName().' extends MySQLTable {');
    }
    private function addFksTables() {
        $fks = $this->tableObj->getForignKeys();
        $addedRefs = [];
        foreach ($fks as $fkObj) {
            $refTableNs = get_class($fkObj->getSource());
            
            if (!in_array($refTableNs, $addedRefs)) {
                $this->append('use '.$refTableNs.';');
                $addedRefs[] = $refTableNs;
            }
        }
    }
}
