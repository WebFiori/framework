<?php

namespace webfiori\entity\cli;

use InvalidArgumentException;
use phMysql\MySQLQuery;
use phMysql\MySQLColumn;
use webfiori\entity\File;

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
     * @var MySQLQuery 
     */
    private $queryObj;
    public function __construct($queryObj, $classInfoArr) {
        if (!$queryObj instanceof MySQLQuery) {
            throw new InvalidArgumentException('The given object is not an instance of the class \'MySQLQuery\'');
        }
        $this->classAsStr = '';
        $this->queryObj = $queryObj;
        if (strlen($classInfoArr['namespace']) != 0) {
            $this->a("<?php\n");
            $this->a('namespace '.$classInfoArr['namespace'].";\n");
            $this->ns = $classInfoArr['namespace'];
        } else {
            $this->ns = 'phMySql\\entity';
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
        $this->a('}');
    }
    private function _writeConstructor() {
        $this->a('public function __construct(){', 1);
        $this->a('parent::__construct(\''.$this->queryObj->getTableName().'\');', 1);
        $this->_addCols();
        $this->a('}', 1);
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
            $this->_appendColObj($key, $colObj);
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
            $this->a("'comment' => '".$colObj->getComment()."',");
        }
        $this->a("],", 3);
    }
    private function _writeHeaderSec() {
        $this->a('namespace '.$this->ns.";\n");
        $this->a("use phMysql\MySQLQuery;\n");
        $this->a('class '.$this->className.' extends MySQLQuery {');
    }
    public function writeClass() {
        $queryFile = new File($this->className.'.php', $this->path);
        $queryFile->setRawData($this->classAsStr);
        $queryFile->write(false, true);
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
