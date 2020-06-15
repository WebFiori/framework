<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace webfiori\entity\cli;

/**
 * A class which is used to write basic controller class skeleton.
 *
 * @author Ibrahim
 * @version 1.0
 */
class ControllerClassWriter extends ClassWriter{
    private $associatedQuery;
    private $dbConnection;
    private $associatedQueryName;
    /**
     * Creates new instance of the class.
     * @param array $classInfo An associative array that contains the information 
     * of the class that will be created. The array must have the following indices: 
     * <ul>
     * <li><b>name</b>: The name of the class that will be created. If not provided, the 
     * string 'NewClass' is used.</li>
     * <li><b>namespace</b>: The namespace that the class will belong to. If not provided, 
     * the namespace 'webfiori' is used.</li>
     * <li><b>path</b>: The location at which the query will be created on. If not 
     * provided, the constant ROOT_DIR is used. </li>
     * <li><b>linked-query</b>: An optional namespace that points to a query class. If set, 
     * the query class will be associated with the controller.</li>
     * <li><b>db-connection</b>: A database connection name. If set, the controller will use the 
     * specified connection.</li>
     * </ul>
     */
    public function __construct($classInfo) {
        parent::__construct($classInfo);
        $this->associatedQuery = isset($classInfo['linked-query']) ? $classInfo['linked-query'] : null;
        $this->dbConnection = isset($classInfo['db-connection']) ? $classInfo['db-connection'] : null;
        if ($this->associatedQuery !== null) {
            $exp = explode("\\", $this->associatedQuery);
            $this->associatedQueryName = $exp[count($exp) - 1];
        }
        $this->_write();
    }
    private function _write() {
        $this->append("<?php\n");
        $this->append('namespace '.$this->getNamespace().";\n");
        $this->append("use webfiori\logic\Controller;");
        if ($this->associatedQuery !== null) {
            $this->append('use '.$this->associatedQuery.';');
        }
        $this->append('');
        $this->append("/**\n"
                . " * An auto-generated controller.\n"
                . " */");
        $this->append('class '.$this->getName().' extends Controller {');
        $this->append('/**', 1);
        $this->append(' * An instance of the class.', 1);
        $this->append(' * @var '.$this->getName(), 1);
        $this->append(' */', 1);
        $this->append('private static $instance;', 1);
        $this->append('/**', 1);
        $this->append(" * Creates and returns an instance of the class.", 1);
        $this->append(" * This method will return a single instance of the class on every call.", 1);
        $this->append(" * @return ".$this->getName().' An instance of the class.', 1);
        $this->append(' */', 1);
        $this->append('public static function get(){ ', 1);
        $this->append('if (self::$instance === null) {', 2);
        $this->append('self::$instance = new '.$this->getName().'();', 3);
        $this->append('}', 2);
        $this->append('return self::$instance;', 2);
        $this->append('}', 1);
        $this->append('public function __construct(){ ', 1);
        $this->append('parent::__construct();', 2);
        if ($this->associatedQueryName !== null) {
            $this->append('$this->setQueryObject(new '.$this->associatedQueryName.'());', 2);
        } else {
            $this->append('//TODO: Associate query object with the controller.', 2);
            $this->append('//$this->setQueryObject(new MySQLQuery());', 2);
        }
        if ($this->dbConnection !== null) {
            $this->append('$this->setConnection(\''.$this->dbConnection.'\');', 2);
        } else {
            $this->append('//TODO: set database connection.', 2);
            $this->append('//$this->setConnection(\'db-connection-name\');', 2);
        }
        $this->append('}', 1);
        $this->append('/**', 1);
        $this->append(' * Adds new record to the database.', 1);
        $this->append(' * @param object $entity An object that holds record information.', 1);
        $this->append(' */', 1);
        $this->append('public function add($entity){ ', 1);
        $this->append('//TODO: Implement add record.', 2);
        $this->append('}', 1);
        $this->append('/**', 1);
        $this->append(' * Updates existing database record based on values taken from an object.', 1);
        $this->append(' * @param object $entity An object that holds record information.', 1);
        $this->append(' */', 1);
        $this->append('public function update($entity){ ', 1);
        $this->append('//TODO: Implement update record.', 2);
        $this->append('}', 1);
        $this->append('/**', 1);
        $this->append(' * Returns an array that contains a set of database records.', 1);
        $this->append(' * @param int $limit The number of records that will be fetched.', 1);
        $this->append(' * @param int $offset An offset starting from the first record of the table.', 1);
        $this->append(' * @return array Returns an array that contains all fetched records.', 1);
        $this->append(' */', 1);
        $this->append('public function getData($limit = -1, $offset = -1){ ', 1);
        $this->append('$returnValue = [];', 2);
        $this->append('//TODO: Implement get records.', 2);
        $this->append('return $returnValue;', 2);
        $this->append('}', 1);
        $this->append('/**', 1);
        $this->append(' * Removes a record from the database.', 1);
        $this->append(' * @param object $entity An object that holds record information.', 1);
        $this->append(' */', 1);
        $this->append('public function delete($entity){ ', 1);
        $this->append('//TODO: Implement remove record.', 2);
        $this->append('}', 1);
        if ($this->associatedQueryName !== null) {
            $this->append("/**", 1);
            $this->append(' * Returns the linked query object which is used to create MySQL quires.', 1);
            $this->append(' * @return '.$this->associatedQueryName.' If the query object is set, the method will ', 1);
            $this->append(' * return an object of type \''.$this->associatedQueryName.'\'. If not set, the method will return', 1);
            $this->append(' * null.', 1);
            $this->append(" */", 1);
            $this->append('public function getQueryObject() {', 1);
            $this->append("return parent::getQueryObject();", 2);
            $this->append('}', 1);
        }
        $this->append('}');
    }
}
