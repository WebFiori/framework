<?php

namespace webfiori\entity\cli;

use InvalidArgumentException;
use phMysql\MySQLQuery;
use phMysql\MySQLColumn;
use webfiori\entity\File;
use phMysql\EntityMapper;

/**
 * Description of QueryClassCreator
 *
 * @author Eng.Ibrahim
 */
class QueryClassCreator {
    private $classAsStr;
    private $className;
    private $ns;
    private $path;
    /**
     *
     * @var EntityMapper|null 
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
     * string 'NewQuery' is used.</li>
     * <li><b>namespace</b>: The namespace that the class will belong to. If not provided, 
     * the namespace 'phMysql\query' is used.</li>
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
        if (!$queryObj instanceof MySQLQuery) {
            throw new InvalidArgumentException('The given object is not an instance of the class \'MySQLQuery\'');
        }
        $this->classAsStr = '';
        $this->queryObj = $queryObj;
        if (isset($classInfoArr['entity-info'])) {
            $this->entityMapper = new EntityMapper($this->queryObj->getTable(), 
                    $classInfoArr['entity-info']['name'], 
                    $classInfoArr['entity-info']['path'], 
                    $classInfoArr['entity-info']['namespace']);
            $this->entityMapper->setUseJsonI($classInfoArr['entity-info']['implement-jsoni']);
        }
        if (strlen($classInfoArr['namespace']) != 0) {
            $this->ns = $classInfoArr['namespace'];
        } else {
            $this->ns = 'phMySql\\query';
        }
        if (isset($classInfoArr['path'])) {
            $this->path = $classInfoArr['path'];
        } else {
            $this->path = ROOT_DIR;
        }
        if (isset($classInfoArr['name'])) {
            $this->className = $classInfoArr['name'];
        } else {
            $this->className = 'NewQuery';
        }
        $this->_writeHeaderSec();
        $this->_writeConstructor();
        $this->_addQueries();
        $this->a('}');
        
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
    private function _writeConstructor() {
        $this->a("/**", 1);
        $this->a(" * Creates new instance of the class.", 1);
        $this->a(" */", 1);
        $this->a('public function __construct(){', 1);
        $this->a('parent::__construct(\''.$this->queryObj->getTableName().'\');', 2);
        if ($this->queryObj->getTable()->getComment() !== null) {
            $this->a('$this->getTable()->setComment(\''.$this->queryObj->getTable()->getComment().'\');', 2);
        }
        $this->_addCols();
        $this->_addFks();
        $this->a('}', 1);
    }
    private function _addFks() {
        $fks = $this->queryObj->getTable()->getForeignKeys();
        foreach ($fks as $fkObj) {
            if ($fkObj instanceof \phMysql\ForeignKey);
            $this->a('$this->getTable()->addReference($this, [');
            $ownerCols = array_keys($fkObj->getOwnerCols());
            $sourceCols = array_keys($fkObj->getOwnerCols());
            for($x = 0 ; $x < count($ownerCols) ; $x ++) {
                $this->a("'$ownerCols[$x]' => '$sourceCols[$x]',");
            }
            $this->a("], '".$fkObj->getKeyName()."', '".$fkObj->getOnUpdate()."', '".$fkObj->getOnDelete()."');");
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
            $this->a('$this->getTable()->addDefaultCols([', 2);
            foreach ($defaultColsKeys as $key => $val) {
                if ($val !== null) {
                    $defaultKeysArr[] = $key;
                    $this->a("'$key' => [],", 3);
                } 
            }
            $this->a(']);', 2);
        }
        $this->a('$this->getTable()->addColumns([', 2);
        foreach ($this->queryObj->getTable()->getColumns() as $key => $colObj){
            if (!in_array($key, $defaultKeysArr)) {
                $this->_appendColObj($key, $colObj);
            }
        }
        $this->a(']);', 2);
    }
    /**
     * 
     * @param MySQLColumn $colObj
     */
    private function _appendColObj($key, $colObj) {
        $dataType = $colObj->getType();
        $this->a("'$key' => [", 3);
        $this->a("'type' => '".$colObj->getType()."',", 4);
        if ($dataType == 'int' || $dataType == 'varchar' || $dataType == 'decimal' || 
                $dataType == 'float' || $dataType == 'double') {
        $this->a("'size' => '".$colObj->getSize()."',", 4); 
        }
        if ($colObj->isPrimary()) {
            $this->a("'primary' => true,", 4); 
            if ($colObj->isAutoInc()) {
                $this->a("'is-unique' => true,", 4); 
            }
        }
        
        if ($colObj->isUnique()) {
            $this->a("'is-unique' => true,", 4); 
        }
        
        if ($colObj->getDefault() !== null) {
            $this->a("'default' => ".$colObj->cleanValue($colObj->getDefault()));
        }
        
        if ($colObj->isNull()) {
            $this->a("'is-null' => true,", 4); 
        }
        
        if ($colObj->getComment() !== null) {
            $this->a("'comment' => '".$colObj->getComment()."',", 4);
        }
        $this->a("],", 3);
    }
    private function _writeHeaderSec() {
        $this->a("<?php\n");
        $this->a('namespace '.$this->ns.";\n");
        $this->a("use phMysql\MySQLQuery;");
        if ($this->entityMapper !== null) {
            $this->a('use '.$this->getEntityNamespace().'\\'.$this->getEntityName().';');
        }
        $this->a('');
        $this->a("/**\n"
                . " * A query class which represents the database table '".$this->queryObj->getTableName()."'.\n"
                . " * The table which is associated with this class will have the following columns:\n"
                . " * <ul>"
                );
        foreach ($this->queryObj->getTable()->getColumns() as $key => $colObj){
            $this->a(" * <li><b>$key</b>: Name in database: '".$colObj->getName()."'. Data type: '".$colObj->getType()."'.</li>");
        }
        $this->a(" * </ul>\n */");
        $this->a('class '.$this->className.' extends MySQLQuery {');
    }
    private function _addQueries() {
        $colsKeys = $this->queryObj->getTable()->colsKeys();
        $defaultColsKeys = $this->queryObj->getTable()->getDefaultColsKeys();
        \webfiori\entity\Util::print_r($defaultColsKeys);
        if ($this->entityMapper !== null) {
            $this->a('/**', 1);
            $this->a(' * Constructs a query that can be used to add new record to the table.', 1);
            $this->a(' * @param '.$this->getEntityName().' $entity An instance of the class "'.$this->getEntityName().'" that contains record information.', 1);
            $this->a(' */', 1);
            $this->a('public function add($entity) {', 1);
            $this->a('$this->insertRecord([', 2);
            $index = 0;
            foreach ($colsKeys as $colKey) {
                if (!(isset($defaultColsKeys[$colKey]) && $defaultColsKeys[$colKey] !== null)) {
                    $this->a("'$colKey' => \$entity->".$this->entityMapper->mapToMethodName($colKey).'(),', 3);
                }
                $index++;
            }
            $this->a(']);', 2);
            $this->a('}', 1);
            
            $primaryKeys = $this->queryObj->getTable()->getPrimaryColsKeys();
            if (count($primaryKeys) == 0) {
                $primaryKeys = $this->queryObj->getTable()->getUniqueColsKeys();
            }
            if (count($primaryKeys) !== 0) {
                $this->a('/**', 1);
                $this->a(' * Constructs a query that can be used to update a record.', 1);
                $this->a(' * @param '.$this->getEntityName().' $entity An instance of the class "'.$this->getEntityName().'" that contains record information.', 1);
                $this->a(' */', 1);
                $this->a("public function update(\$entity) {", 1);
                $this->a('$this->updateRecord([', 2);
                foreach ($colsKeys as $colKey) {
                    if (!in_array($colKey, $primaryKeys)) {
                        $this->a("'$colKey' => \$entity->".$this->entityMapper->mapToMethodName($colKey, 'g').'(),', 3);
                    }
                }
                $this->a('], [', 2);
                foreach ($colsKeys as $colKey) {
                    if (in_array($colKey, $primaryKeys)) {
                        $this->a("'$colKey' => \$entity->".$this->entityMapper->mapToMethodName($colKey, 'g').'(),', 3);
                    }
                }
                $this->a(']);', 2);
                $this->a('}', 1);
                $this->a('/**', 2);
                $this->a(' * Constructs a query that can be used to remove a record.', 1);
                $this->a(' * @param '.$this->getEntityName().' $entity An instance of the class "'.$this->getEntityName().'" that contains record information.', 1);
                $this->a(' */', 1);
                $this->a("public function delete(\$entity) {",1);
                $this->a('$this->deleteRecord([', 2);
                foreach ($colsKeys as $colKey) {
                    if (in_array($colKey, $primaryKeys)) {
                        $this->a("'$colKey' => \$entity->".$this->entityMapper->mapToMethodName($colKey, 'g').'(),', 3);
                    }
                }
                $this->a(']);', 2);
                $this->a('}',1);
            }
        }
        $this->a("/**", 1);
        $this->a(" * Constructs a query that can be used to select all records from the table.", 1);
        $this->a(" * @param int \$limit The number of records that will be selected. Default is -1", 1);
        $this->a(" * @param int \$offset The number of records that will be skipped from the first row. Default is -1.", 1);
        $this->a(" */", 1);
        $this->a('public function selectAll($limit = -1, $offset = -1) {', 1);
        $this->a('$this->select([', 2);
        $this->a("'limit' => \$limit,", 3);
        $this->a("'offset' => \$offset,", 3);
        $this->a("'map-result-to' => '".$this->getEntityNamespace().'\\'.$this->getEntityName()."',", 3);
        $this->a("]);", 2);
        $this->a('}', 1);
        
    }
    /**
     * Write the query class.
     * This method will first attempt to create the query class. If it was created, 
     * it will create the entity class which is associated with it (if any 
     * entity is associated).
     * @since 1.0
     */
    public function writeClass() {
        $queryFile = new File($this->className.'.php', $this->path);
        $queryFile->remove();
        $queryFile->setRawData($this->classAsStr);
        $queryFile->write(false, true);
        if ($this->entityMapper !== null) {
            $this->entityMapper->create();
        }
    }
    /**
     * Appends a string to the string that represents the query class.
     * @param string $str The string that will be appended. At the end of the string 
     * a new line character will be appended.
     * @since 1.0
     */
    private function a($str, $tapsCount = 0) {
        $tabSpaces = '    ';
        $tabStr = '';
        for ($x = 0 ; $x < $tapsCount ; $x++) {
            $tabStr .= $tabSpaces;
        }
        $this->classAsStr .= $tabStr.$str."\n";
    }
}
