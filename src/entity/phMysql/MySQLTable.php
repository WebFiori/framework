<?php
/**
 * MIT License
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh, phMysql library.
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
namespace phMysql;
use phMysql\MySQLQuery;
use phMysql\MySQLColumn;
/**
 * A class that represents MySQL table.
 *
 * @author Ibrahim
 * @version 1.6.5
 */
class MySQLTable {
    /**
     * The namespace of the auto-generated entity.
     * @var string|null
     * @since 1.6.5 
     */
    private $entityNamespace;
    /**
     * The location of the auto-generated entity.
     * @var string|null
     * @since 1.6.5 
     */
    private $entityPath;
    /**
     * The instance of type MySQLQuery at which this table is linked to.
     * @var MySQLQuery 
     * @since 1.6.4
     */
    private $ownerQuery;
    /**
     * The name of database schema that the table belongs to.
     * @var string 
     * @since 1.6.1
     */
    private $schema;
    /**
     * A comment to add to the column.
     * @var string|null 
     * @since 1.6.1
     */
    private $comment;
    /**
     * Version number of MySQL server.
     * @var string 
     */
    private $mysqlVnum;
    /**
     * A constant that is returned by some methods to tell that the 
     * table does not have a given column name.
     * @var string 
     * @since 1.4
     */
    const NO_SUCH_COL = 'no_such_col';
    /**
     * A constant that is returned by some methods to tell that the 
     * name of the table is invalid.
     * @var string 
     * @since 1.4
     */
    const INV_TABLE_NAME = 'inv_table_name';
    /**
     * The order of the table in the database.
     * @var int The order of the table in the database. The value 
     * of this attributes describes the dependencies between tables. For example, 
     * if we have three tables, 'A', 'B' and 'C'. Let's assume that table 'B' 
     * references table 'A' and Table 'A' references table 'C'. In this case, 
     * table 'C' will have order 0, Table 'A' have order 1 and table 'B' have order 
     * 2.
     * @since 1.3 
     */
    private $order;
    /**
     * An array that contains all table foreign keys.
     * @var array 
     * @since 1.0
     */
    private $foreignKeys = [];
    /**
     * The name of the table.
     * @var string
     * @since 1.0 
     */
    private $tableName;
    /**
     * An array of table columns.
     * @var array
     * @since 1.0 
     */
    private $colSet = [];
    /**
     * The engine that will be used by the table.
     * @var string
     * @since 1.0 
     */
    private $engin;
    /**
     * Character set of the table.
     * @var string
     * @since 1.0 
     */
    private $charSet;
    /**
     * An array of booleans which indicates which default columns has been added
     * @var array 
     */
    private $defaultColsKeys;
    /**
     * Creates a new instance of the class.
     * This method will initialize the basic settings of the table. It will 
     * set MySQL version to 5.5, the engine to 'InnoDB', char set to 
     * 'utf8mb4' and the order to 0.  
     * @param string $tName The name of the table. It must be a 
     * string and its not empty. Also it must not contain any spaces or any 
     * characters other than A-Z, a-z and underscore. If the given name is invalid 
     * or not provided, 'table' will be used as default.
     */
    public function __construct($tName = 'table') {
        if($this->setName($tName) !== true){
            $this->setName('table');
        }
        $this->mysqlVnum = '5.5';
        $this->engin = 'InnoDB';
        $this->charSet = 'utf8mb4';
        $this->order = 0;
        $this->defaultColsKeys = [
            'id'=>null,
            'created-on'=>null,
            'last-updated'=>null
        ];
        $this->schema = null;
        $this->ownerQuery = null;
    }
    /**
     * Returns the query object which owns the table.
     * @return MySQLQuery|null If the owner is set, the method will return an 
     * object of type 'MySQLQuery'. Other than that, null is returned.
     * @since 1.6.5
     */
    public function getOwnerQuery() {
        return $this->ownerQuery;
    }
    /**
     * Returns the namespace at which the auto-generated entity class belongs to.
     * @return string|null If no entity class is generated, the method will return 
     * null. Other than that, the method will return a string that represents 
     * the namespace that the entity class belongs to.
     * @since 1.6.5
     */
    public function getEntityNamespace() {
        return $this->entityNamespace;
    }
    /**
     * Returns the name of the directory at which the auto-generated entity class 
     * was created on.
     * @return string|null If no entity class is generated, the method will return 
     * null. Other than that, the method will return a string that represents 
     * the name of the directory at which the auto-generated entity class 
     * was created on.
     * @since 1.6.5
     */
    public function getEntityPath() {
        return $this->entityPath;
    }
    /**
     * Sets the query object at which the table is belonging to.
     * The developer does not have to call this method. It is used automatically 
     * my the class 'MySQLQuery' to set the owner query.
     * @param MySQLQuery $qObj An instance of the class 'MySQLQuery'.
     * @since 1.6.4
     */
    public function setOwnerQuery($qObj) {
        if($qObj instanceof MySQLQuery){
            $this->ownerQuery = $qObj;
            $this->setSchemaName($qObj->getSchemaName());
        }
        else if($qObj === null){
            $this->ownerQuery = null;
        }
    }
    /**
     * Adds multiple columns at once.
     * @param array $colsArr An associative array. The keys will act as column 
     * key in the table. The value of the key should be an associative array of 
     * column options. For supported options, check the method 
     * MySQLTable::addColumn().
     * @since 1.6.4
     */
    public function addColumns($colsArr) {
        if(gettype($colsArr) === 'array'){
            foreach ($colsArr as $key => $options){
                $this->addColumn($key, $options);
            }
        }
    }
    /**
     * Returns the name of the database that the table belongs to.
     * @return string|null The name of the database that the table belongs to.
     * If it is not set, the method will return null.
     * @since 1.6.1
     */
    public function getDatabaseName() {
        return $this->schema;
    }
    /**
     * Sets the name of the database that the table belongs to.
     * Note that if the owner query object is set, the method will always return 
     * true and the name of the schema will be taken from the query object regardless 
     * of the passed value.
     * @param string $name Schema name (or database name). A valid name 
     * must have the following conditions:
     * <ul>
     * <li>Must not be an empty string.</li>
     * <li>Cannot start with a number.</li>
     * <li>Can have numbers in the middle.</li>
     * <li>Consist of the following characters: [A-Z][a-z] and underscore only.</li>
     * </ul>
     * @return boolean If it was set, the method will return true. If not, 
     * it will return false.
     * @since 1.6.1
     */
    public function setSchemaName($name) {
        if($this->ownerQuery !== null){
            $this->schema = $this->ownerQuery->getSchemaName();
            return true;
        }
        else{
            $trimmed = trim($name);
            $len = strlen($trimmed);
            if($len > 0){
                for ($x = 0 ; $x < $len ; $x++){
                    $ch = $trimmed[$x];
                    if($x == 0 && ($ch >= '0' && $ch <= '9')){
                        return false;
                    }
                    if($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){

                    }
                    else{
                        return false;
                    }
                }
                $this->schema = $trimmed;
                return true;
            }
        }
        return false;
    }
    /**
     * Sets version number of MySQL server.
     * Version number of MySQL is used to set the correct collation for table columns 
     * in case of varchar or text data types. If MySQL version is '5.5' or lower, 
     * collation will be set to 'utf8mb4_unicode_ci'. Other than that, the 
     * collation will be set to 'utf8mb4_unicode_520_ci'.
     * @param string $vNum MySQL version number (such as '5.5'). It must be in 
     * the format 'X.X.X' or the version won't be set. The last 'X' is optional.
     * @since 1.6.1
     */
    public function setMySQLVersion($vNum) {
        if(strlen($vNum) > 0){
            $split = explode('.', $vNum);
            if(count($split) >= 2){
                $major = intval($split[0]);
                $minor = intval($split[1]);
                if($major >= 0 && $minor >= 0){
                    $this->mysqlVnum = $vNum;
                }
            }
        }
    }
    /**
     * Adds default columns to the table.
     * Default columns are the following columns:
     * <ul>
     * <li>ID Column.</li>
     * <li>The timestamp at which the record was created on.</li>
     * <li>The date and time at which the record was last updated.</li>
     * </ul>
     * Depending on the provided options, none of the 3 might be added or 
     * one of them or two or all.
     * @param array $options An associative array that can be used to 
     * customize default columns. Each option must have a sub-associative 
     * array with two indices: 'key-name' and 'db-name'. 'key-name' is 
     * simply the name of the column in the table instance. While 'db-name' 
     * is the name of the column in database schema. Available options are:
     * <ul>
     * <li><b>id</b>: If provided, the column ID will be added. Default value is 
     * the following array:
     * <ul>
     * <li>'key-name'=>'id'</li>
     * <li>'db-name'=>'id'</li>
     * </ul>
     * </li>
     * <li><b>created-on</b>: If provided, the column created on will be added. Default value is 
     * the following array:
     * <ul>
     * <li>'key-name'=>'created-on'</li>
     * <li>'db-name'=>'created_on'</li>
     * </ul>
     * </li>
     * <li><b>last-updated</b>: If provided, the column last updated will be added. Default value is 
     * the following array:
     * <ul>
     * <li>'key-name'=>'last-updated'</li>
     * <li>'db-name'=>'last_updated'</li>
     * </ul>
     * </li>
     * </ul>
     * @since 1.6.1
     */
    public function addDefaultCols($options=[
        'id'=>[],
        'created-on'=>[],
        'last-updated'=>[]
    ]) {
        if(gettype($options) == 'array'){
            if(isset($options['id']) && $this->defaultColsKeys['id'] === null){
                $id = $options['id'];
                $key = isset($id['key-name']) ? trim($id['key-name']) : 'id';
                if(!$this->_isKeyNameValid($key)){
                    $key = 'id';
                }
                $inDbName = isset($id['db-name']) ? $id['db-name'] : 'id';
                $colObj = new MySQLColumn($inDbName, 'int', 11);
                $colObj->setIsPrimary(true);
                $colObj->setIsAutoInc(true);
                if(!($colObj->getName() == $inDbName)){
                    $colObj->setName('id');
                }
                if($this->addColumn($key, $colObj)){
                    $this->defaultColsKeys['id'] = $key;
                }
            }
            if(isset($options['created-on']) && $this->defaultColsKeys['created-on'] === null){
                $createdOn = $options['created-on'];
                $key = isset($createdOn['key-name']) ? trim($createdOn['key-name']) : 'created-on';
                if(!$this->_isKeyNameValid($key)){
                    $key = 'created-on';
                }
                $inDbName = isset($createdOn['db-name']) ? $createdOn['db-name'] : 'created_on';
                $colObj = new MySQLColumn($inDbName, 'timestamp');
                if(!($colObj->getName() == $inDbName)){
                    $colObj->setName('created_on');
                }
                $colObj->setDefault('current_timestamp');
                if($this->addColumn($key, $colObj)){
                    $this->defaultColsKeys['created-on'] = $key;
                }
            }
            if(isset($options['last-updated']) && $this->defaultColsKeys['last-updated'] === null){
                $lastUpdated = $options['last-updated'];
                $key = isset($lastUpdated['key-name']) ? trim($lastUpdated['key-name']) : 'last-updated';
                if(!$this->_isKeyNameValid($key)){
                    $key = 'last-updated';
                }
                $inDbName = isset($lastUpdated['db-name']) ? $lastUpdated['db-name'] : 'last_updated';
                $colObj = new MySQLColumn($inDbName, 'datetime');
                if(!($colObj->getName() == $inDbName)){
                    $colObj->setName('last_update');
                }
                $colObj->setAutoUpdate(true);
                $colObj->setIsNull(true);
                if($this->addColumn($key, $colObj)){
                    $this->defaultColsKeys['last-updated'] = $key;
                }
            }
        }
    }
    /**
     * Returns an associative array that contains default columns keys.
     * @return array An associative array which has the following indices: 
     * <ul>
     * <li>id</li>
     * <li>created-on</li>
     * <li>last-updated</li>
     * </ul>
     * If the table does not have the specified column, the value of the index 
     * will beset to null.
     * @since 1.6.3
     */
    public function getDefaultColsKeys() {
        return $this->defaultColsKeys;
    }
    /**
     * Returns an associative array that maps possible entity methods names with 
     * table columns names in the database.
     * Assuming that the table has two columns. The first one has a key = 'user-id' 
     * and the second one has a key 'password'. Also, let's assume that the first column 
     * has the name 'id' in the database and the second one has the name 'user_pass'. 
     * If this is the case, the method will return something like the following array:
     * <p>
     * <code>[<br/>
     * 'setUserId'=>'id',<br/>
     * 'setPassword'=>'user_pass'<br/>
     * ]</code>
     * </p>
     * @return array An associative array. The indices represents the names of 
     * the methods in the entity class and the values are the names of table 
     * columns as they appear in the database.
     * @since 1.6.5
     */
    public function getSettersMap() {
        $keys = array_keys($this->getColumns());
        $retVal = [];
        foreach ($keys as $keyName){
            $split = explode('-', $keyName);
            $methodName = '';
            foreach ($split as $namePart){
                if(strlen($namePart) == 1){
                    $methodName .= strtoupper($namePart);
                }
                else{
                    $firstChar = $namePart[0];
                    $methodName .= strtoupper($firstChar).substr($namePart, 1);
                }
            }
            $mappedCol = $this->getCol($keyName)->getName();
            $retVal['set'.$methodName] = $mappedCol;
        }
        return $retVal;
    }
    /**
     * Returns an array that contains the names of attributes mapped from columns 
     * names.
     * Attributes names are generated based on the names of keys. For example, 
     * if we have two columns one with key 'user-id' and the second one with 
     * name 'user-PASS', then the two attributes which represents the two columns 
     * will have the names 'userId' and 'userPASS'.
     * @return array An indexed array that contains attributes names. 
     * @since 1.6.5
     */
    public function getAttribitesNames() {
        $keys = array_keys($this->getColumns());
        $retVal = [];
        foreach ($keys as $keyName){
            $split = explode('-', $keyName);
            $attrName = '';
            $index = 0;
            foreach ($split as $namePart){
                if(strlen($namePart) == 1){
                    $attrName .= strtolower($namePart);
                    $index++;
                }
                else{
                    if($index != 0){
                        $firstChar = $namePart[0];
                        $attrName .= strtoupper($firstChar).substr($namePart, 1);
                    }
                    else{
                        $index++;
                        $attrName .= strtolower($namePart);
                    }
                }
            }
            $retVal[] = $attrName;
        }
        return $retVal;
    }
    /**
     * Returns an associative array that contains the possible names 
     * of the methods which exist in the entity class that the result 
     * of a select query on the table will be mapped to.
     * The names of the methods are constructed from the names of columns 
     * keys. For example, if the name of the column key is 'user-id', the 
     * name of setter method will be 'setUserId' and the name of setter 
     * method will be 'setUserId'.
     * @return array An associative array. The array will have two indices. 
     * The first index has the name 'setters' which will contain the names 
     * of setters and the second index is 'getters' which contains the names 
     * of the getters.
     * @since 1.6.5
     */
    public function getEntityMethods() {
        $keys = array_keys($this->getColumns());
        $retVal = [
            'setters'=>[],
            'getters'=>[]
        ];
        foreach ($keys as $keyName){
            $split = explode('-', $keyName);
            $methodName = '';
            foreach ($split as $namePart){
                if(strlen($namePart) == 1){
                    $methodName .= strtoupper($namePart);
                }
                else{
                    $firstChar = $namePart[0];
                    $methodName .= strtoupper($firstChar).substr($namePart, 1);
                }
            }
            $retVal['getters'][] = 'get'.$methodName;
            $retVal['setters'][] = 'set'.$methodName;
        }
        return $retVal;
    }
    /**
     * Create a new entity class that can be used to store table records
     * @param array $options An associative array that contains entity class 
     * options. Available options are:
     * <ul>
     * <li>store-path: The directory at which the entity class will be created 
     * on. It must be provided.</li>
     * <li>class-name: The name of the entity class that will be created. It 
     * must be provided.</li>
     * <li>namespace: An optional namespace at which the entity class can be 
     * added to. If not given, the namespace <code>phMysql\entity</code> will 
     * be used as a default namespace.</li>
     * </ul>
     * <li>override: A boolean value. If the entity was already created and this 
     * parameter is set to true, the entity class will be replaced with new one.</li>
     * </ul>
     * @return boolean If the entity class is created, the method will return 
     * true. If not created, it will return false.
     */
    public function createEntityClass($options) {
        $retVal = false;
        $path = isset($options['store-path']) ? $options['store-path'] : '';
        $entityName = isset($options['class-name']) ? trim($options['class-name']) : '';
        if(strlen($entityName) != 0 && !strpos($entityName, ' ')){
            $namespace = isset($options['namespace']) && strlen($options['namespace']) != 0 
                    ? trim($options['namespace']) : __NAMESPACE__.'\\entity';
            $override = isset($options['override']) ? $options['override'] === true : false;
            if(is_dir($path.DIRECTORY_SEPARATOR.$entityName) && class_exists($namespace.'\\'.$entityName) && !$override){
                $this->entityNamespace = $namespace.'\\'.$entityName;
                $this->entityPath = $path.DIRECTORY_SEPARATOR.$entityName.'.php';
                $retVal = true;
            }
            else{
                $file = fopen($path.DIRECTORY_SEPARATOR.$entityName.'.php', 'w+');
                if(is_resource($file)){
                    $entityAttrs = $this->getAttribitesNames();
                    $attrsCount = count($entityAttrs);
                    $settersGettersMap = $this->getEntityMethods();
                    fwrite($file, "<?php\nnamespace ".$namespace.";\n");
                    fwrite($file, "/**\n"
                            . " * An auto-generated entity class which maps to a record in the\n"
                            . " * table '".$this->getName()."'\n"
                            . " **/\n");
                    fwrite($file, "class ".$entityName."{\n");
                    $colsNames = $this->getColsNames();
                    $index = 0;
                    foreach ($entityAttrs as $attrName){
                        $colName = $colsNames[$index];
                        fwrite($file, "    /**\n"
                                    . "     * The attribute which is mapped to the column '".$colName."'.\n"
                                    . "     * @var ".$this->_getPHPType($index)."\n"
                                    . "     **/\n");
                        fwrite($file, '    private $'.$attrName.";\n");
                        $index++;
                    }
                    for($x = 0 ; $x < $attrsCount ; $x++){
                        $colName = $colsNames[$x];
                        $setterName = $settersGettersMap['setters'][$x];
                        $attrName = $entityAttrs[$x];
                        fwrite($file, "    /**\n"
                                    . "     * Sets the value of the attribute '".$attrName."'.\n"
                                    . "     * The value of the attribute is mapped to the column which has\n"
                                    . "     * the name '$colName'.\n"
                                    . "     * @param \$$entityAttrs[$x] ".$this->_getPHPType($x)." The new value of the attribute.\n"
                                    . "     **/\n");
                        fwrite($file, '    public function '.$setterName.'($'.$entityAttrs[$x]."){\n");
                        fwrite($file, '        $this->'.$entityAttrs[$x].' = $'.$entityAttrs[$x].";\n");
                        fwrite($file, "    }\n");
                        $getterName = $settersGettersMap['getters'][$x];
                        fwrite($file, "    /**\n"
                                    . "     * Returns the value of the attribute '".$attrName."'.\n"
                                    . "     * The value of the attribute is mapped to the column which has\n"
                                    . "     * the name '$colName'.\n"
                                    . "     * @return ".$this->_getPHPType($x)." The value of the attribute.\n"
                                    . "     **/\n");
                        fwrite($file, '    public function '.$getterName."(){\n");
                        fwrite($file, '        return $this->'.$entityAttrs[$x].";\n");
                        fwrite($file, "    }\n");
                    }
                    fwrite($file, "}\n");
                    fclose($file);
                    $this->entityNamespace = $namespace.'\\'.$entityName;
                    $this->entityPath = $path.DIRECTORY_SEPARATOR.$entityName.'.php';
                    $retVal = true;
                }
                else{
                    $retVal = false;
                }
            }
        }
        return $retVal;
    }
    private function _getPHPType($colIndex){
        $colObj = array_values($this->getColumns())[$colIndex];
        $isNull = $colObj->isNull() ? '|null' : '';
        $type = $colObj->getType();
        if($type == 'int'){
            return 'int'.$isNull;
        }
        else if($type == 'decimal' || $type == 'double' || $type == 'float'){
            return 'double'.$isNull;
        }
        else if($type == 'boolean'){
            return 'boolean'.$isNull;
        }
        else{
            return 'string'.$isNull;
        }
    }
    /**
     * Returns version number of MySQL server.
     * @return string MySQL version number (such as '5.5'). If version number 
     * is not set, The default return value is '5.5'.
     * @since 1.6.1
     */
    public function getMySQLVersion() {
        return $this->mysqlVnum;
    }
    /**
     * Returns an array that contains all columns names as they will appear in 
     * the database.
     * @return array An array that contains all columns names as they will appear in 
     * the database.
     * @since 1.6.2
     */
    public function getColsNames() {
        $columns = $this->getColumns();
        $retVal = [];
        foreach ($columns as $colObj){
            $retVal[] = $colObj->getName();
        }
        return $retVal;
    }
    /**
     * Sets the order of the table in the database.
     * The order of the table describes the dependencies between tables. For example, 
     * if we have three tables, 'A', 'B' and 'C'. Let's assume that table 'B' 
     * references table 'A' and Table 'A' references table 'C'. In this case, 
     * table 'C' will have order 0, Table 'A' have order 1 and table 'B' have order 
     * 2.
     * @param int $val The order of the table in the database.
     * @since 1.3 
     * @return boolean true if the value of the attribute is set. 
     * false if not.
     */
    public function setOrder($val){
        if(gettype($val) == 'integer'){
            if($val > -1){
                $this->order = $val;
                return true;
            }
        }
        return false;
    }
    /**
     * Returns the order of the table in the database.
     * @return int The order of the table in the database.
     * @since 1.3 
     */
    public function getOrder() {
        return $this->order;
    }
    /**
     * Returns the value of table collation.
     * If MySQL version is '5.5' or lower, the method will 
     * return 'utf8mb4_unicode_ci'. Other than that, the method will return 
     * 'utf8mb4_unicode_520_ci'.
     * @return string Table collation.
     * @since 1.6
     */
    public function getCollation(){
        $split = explode('.', $this->getMySQLVersion());
        if(isset($split[0]) && intval($split[0]) <= 5 && isset($split[1]) && intval($split[1]) <= 5){
            return 'utf8mb4_unicode_ci';
        }
        return 'utf8mb4_unicode_520_ci';
    }
    /**
     * Adds a foreign key to the table.
     * Note that it will be added only if no key was added to the table which 
     * has the same name as the given key.
     * @param ForeignKey $key an object of type 'ForeignKey'.
     * @since 1.1
     * @return boolean true if the key is added. false otherwise.
     * @see ForeignKey
     * @since 1.0
     */
    public function addForeignKey($key){
        if($key instanceof ForeignKey){
            foreach ($this->forignKeys() as $val){
                if($key->getKeyName() == $val->getKeyName()){
                    return false;
                }
            }
            $key->setOwner($this);
            array_push($this->foreignKeys, $key);
            return true;
        }
        return false;
    }
    /**
     * Returns the name of table primary key.
     * @return string The returned value will be the name of the table added 
     * to it the suffix '_pk'.
     * @since 1.5
     */
    public function getPrimaryKeyName() {
        return $this->getName().'_pk';
    }
    /**
     * Returns the number of columns that will act as one primary key.
     * @return int The number of columns that will act as one primary key. If 
     * the table has no primary key, the method will return 0. If one column 
     * is used as primary, the method will return 1. If two, the method 
     * will return 2 and so on.
     * @since 1.5
     */
    public function primaryKeyColsCount(){
        $count = 0;
        foreach ($this->colSet as $col){
            if($col->isPrimary()){
                $count++;
            }
        }
        return $count;
    }
    /**
     * Returns an array which contains all added foreign keys.
     * @return array An array which contains all added foreign keys. The keys 
     * are added as an objects of type 'ForeignKey'.
     * @since 1.6.1
     */
    public function getForeignKeys() {
        return $this->foreignKeys;
    }
    /**
     * Returns an associative array that contains table columns.
     * @return array An associative array that contains table columns. The indices 
     * will be columns names and the values are objects of type 'Column'.
     * @since 1.6.1
     */
    public function getColumns() {
        return $this->colSet;
    }
    /**
     * Sets a comment which will appear with the table.
     * @param string|null $comment Comment text. It must be non-empty string 
     * in order to set. If null is passed, the comment will be removed.
     * @since 1.6.1
     */
    public function setComment($comment) {
        if($comment == null || strlen($comment) != 0){
            $this->comment = $comment;
        }
    }
    /**
     * Returns a string that represents a comment which was added with the table.
     * @return string|null Comment text. If it is not set, the method will return 
     * null.
     * @since 1.6.1
     */
    public function getComment() {
        return $this->comment;
    }
    /**
     * Adds a foreign key to the table.
     * @param MySQLTable|MySQLQuery|string $refTable The referenced table. It is the table that 
     * will contain original values. This value can be an object of type 
     * 'MySQLTable', an object of type 'MySQLQuery' or the namespace of a class which is a sub-class of 
     * the class 'MySQLQuery'.
     * @param array $cols An associative array that contains key columns. 
     * The indices must be names of columns which exist in 'this' table and 
     * the values must be columns from referenced table. It is possible to 
     * provide an indexed array. If an indexed array is given, the method will 
     * assume that the two tables have same column key. 
     * @param string $keyname The name of the key.
     * @param string $onupdate The 'on update' condition for the key. it can be one 
     * of the following: 
     * <ul>
     * <li>set null</li>
     * <li>cascade</li>
     * <li>restrict</li>
     * <li>set default</li>
     * <li>no action</li>
     * </ul>
     * Default value is 'set null'.
     * @param string $ondelete The 'on delete' condition for the key. it can be one 
     * of the following: 
     * <ul>
     * <li>set null</li>
     * <li>cascade</li>
     * <li>restrict</li>
     * <li>set default</li>
     * <li>no action</li>
     * </ul>
     * Default value is 'set null'.
     * @return boolean
     * @since 1.5
     */
    public function addReference($refTable,$cols,$keyname,$onupdate='set null',$ondelete='set null') {
        if(!($refTable instanceof MySQLTable)){
            if($refTable instanceof MySQLQuery){
                $refTable = $refTable->getStructure();
            }
            else{
                if(class_exists($refTable)){
                    $q = new $refTable();
                    if($q instanceof MySQLQuery){
                        $refTable = $q->getStructure();
                    }
                }
            }
        }
        if($refTable instanceof MySQLTable){
            $fk = new ForeignKey();
            $fk->setOwner($this);
            $fk->setSource($refTable);
            if($fk->setKeyName($keyname) === true){
                foreach ($cols as $target => $source){
                    if(gettype($target) == 'integer'){
                        //indexed array. 
                        //It means source and target columns have same name.
                        $fk->addReference($source, $source);
                    }
                    else{
                        //Associative. Probably two columns with different names.
                        $fk->addReference($target, $source);
                    }
                }
                if(count($fk->getSourceCols()) != 0){
                    $fk->setOnUpdate($onupdate);
                    $fk->setOnDelete($ondelete);
                    $this->foreignKeys[] = $fk;
                    return true;
                }
            }
            else{
                trigger_error('Invalid FK name: \''.$keyname.'\'.');
            }
        }
        else{
            trigger_error('Referenced table is not an instance of the class \'MySQLTable\'.');
        }
        return false;
    }
    /**
     * Returns the columns of the table which are a part of the primary key.
     * @return array An array which contains an objects of type 'MySQLColumn'. If 
     * the table has no primary key, the array will be empty.
     * @since 1.5.1
     */
    public function getPrimaryKeyCols() {
        $arr = [];
        foreach ($this->columns() as $col){
            if($col->isPrimary()){
                $arr[] = $col;
            }
        }
        return $arr;
    }
    /**
     * Checks if a foreign key with the given name exist on the table or not.
     * @param string $keyName The name of the key.
     * @return boolean true if the table has a foreign key with the given name. 
     * false if not.
     * @since 1.4
     */
    public function hasForeignKey($keyName){
        foreach ($this->forignKeys() as $val){
            if($keyName == $val->getKeyName()){
                return true;
            }
        }
        return false;
    }
    /**
     * Returns an array that contains all table foreign keys.
     * @return array An array of FKs.
     * @since 1.1
     */
    public function forignKeys() {
        return $this->foreignKeys;
    }
    /**
     * Returns an array of all the columns in the table.
     * @return array An array that contains an objects of type <b>Column</b>
     * @since 1.0
     */
    public function columns(){
        return $this->colSet;
    }

    /**
     * Returns the name of the table.
     * @param boolean $dbPrefix A boolean. If its set to true, the name of 
     * the table will be prefixed with the name of the database that the 
     * table belongs to. Default is true.
     * @return string The name of the table. Default return value is 'table'.
     * @since 1.0
     */
    public function getName($dbPrefix=true){
        if($dbPrefix === true && $this->getDatabaseName() !== null){
            return $this->getDatabaseName().'.'.$this->tableName;
        }
        return $this->tableName;
    }
    /**
     * Sets the name of the table.
     * @param string $param The name of the table (such as 'users'). A valid table 
     * name must follow the following rules:
     * <ul>
     * <li>Must not be an empty string.</li>
     * <li>Cannot starts with numbers.</li>
     * <li>Only contain the following sets of characters: [A-Z], [a-z], [0-9] and underscore.</li>
     * </ul>
     * @return boolean If the name of the table is updated, then the method will return true. 
     * other than that, it will return false.
     * @since 1.0
     */
    public function setName($param) {
        $trimmedName = trim($param);
        $len = strlen($trimmedName);
        if($len == 0){
            return false;
        }
        for ($x = 0 ; $x < $len ; $x++){
            $ch = $trimmedName[$x];
            if($x == 0){
                if($ch >= '0' && $ch <= '9'){
                    return false;
                }
            }
            if($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){

            }
            else{
                return false;
            }
        }
        $this->tableName = $trimmedName;
        return true;
    }
    /**
     * Removes a column given its key or index in the table.
     * The method will first assume that the given value is column key. If 
     * no column found, then it will assume that the given value is column 
     * index.
     * @param string|int $colKeyOrIndex Column key or index.
     * @return boolean If the column was removed, the method will return true. 
     * Other than that, the method will return false.
     * @since 1.6.1
     */
    public function removeColumn($colKeyOrIndex) {
        $col = $this->getCol($colKeyOrIndex);
        if(!($col instanceof MySQLColumn)){
            foreach ($this->colSet as $key => $col){
                if($col->getIndex() == $colKeyOrIndex){
                    unset($this->colSet[$key]);
                    $this->_checkPKs();
                    return true;
                }
            }
            return false;
        }
        else{
            unset($this->colSet[$colKeyOrIndex]);
            $this->_checkPKs();
            return true;
        }
    }
    /**
     * Creates an instance of the class 'Column' given an array of options.
     * @param array $options An associative array of options. The available options 
     * are: 
     * <ul>
     * <li><b>name</b>: Required. The name of the column in the database.</li>
     * <li><b>datatype</b>: The datatype of the column. If not provided, 'varchar' 
     * will be used.</li>
     * <li><b>size</b>: Size of the column (if datatype does support size). 
     * If not provided, 1 will be used.</li>
     * <li><b>default</b>: A default value for the column if its value 
     * is not present in case of insert.</li>
     * <li><b>is-null</b>: A boolean. If the column allows null values, this should 
     * be set to true. Default is false.</li>
     * <li><b>is-primary</b>: A boolean. It must be set to true if the column 
     * represents a primary key. Note that the column will be set as unique 
     * once its set as a primary.</li>
     * <li><b>auto-inc</b>: A boolean. Only applicable if the column is a 
     * primary key. Set to true to auto-increment column value by 1 for every 
     * insert.</li>
     * <li><b>is-unique</b>: A boolean. If set to true, a unique index will 
     * be created for the column.</li>
     * <li><b>auto-update</b>: A boolean. If the column datatype is 'timestamp' or 
     * 'datetime' and this parameter is set to true, the time of update will 
     * change automatically without having to change it manually.</li>
     * <li><b>scale</b>: Number of numbers to the left of the decimal 
     * point. Only supported for decimal datatype.</li>
     * <li><b>comment</b> A comment which can be used to describe the column.</li>
     * </ul>
     * 
     * @return MySQLColumn
     * @since 1.6.4
     */
    private function _createColObj($options) {
        if(isset($options['name'])){
            $datatype = isset($options['datatype']) ? $options['datatype'] : 'varchar';
            $col = new MySQLColumn($options['name'], $datatype);
            $size = isset($options['size']) ? intval($options['size']) : 1;
            $col->setSize($size);
            $scale = isset($options['scale']) ? intval($options['scale']) : 2;
            $col->setScale($scale);
            if(isset($options['default'])){
                $col->setDefault($options['default']);
            }
            $isNull = isset($options['is-null']) ? $options['is-null'] === true : false;
            $col->setIsNull($isNull);
            if(isset($options['is-primary'])){
                $col->setIsPrimary($options['is-primary']);
                if(isset($options['auto-inc'])){
                    $col->setIsAutoInc($options['auto-inc']);
                }
            }
            if(isset($options['is-unique'])){
                $col->setIsUnique($options['is-unique']);
            }
            if(isset($options['auto-update'])){
                $col->setAutoUpdate($options['auto-update']);
            }
            if(isset($options['comment'])){
                $col->setComment($options['comment']);
            }
            return $col;
        }
        return null;
    }
    private function _checkPKs() {
        $primaryCount = $this->primaryKeyColsCount();
        if($primaryCount > 1){
            foreach ($this->getPrimaryKeyCols() as $col){
                if($col->isPrimary()){
                    $col->setIsUnique(false);
                }
            }
        }
        else{
            foreach ($this->getPrimaryKeyCols() as $col){
                if($col->isPrimary()){
                    $col->setIsUnique(true);
                }
            }
        }
    }
    /**
     * Adds new column to the table.
     * @param string $key The index at which the column will be added to. The name 
     * of the key can only have the following characters: [A-Z], [a-z], [0-9] 
     * and '-'.
     * @param MySQLColumn|array $col An object of type Column. Also, it can be 
     * an associative array of column options. The available options 
     * are: 
     * <ul>
     * <li><b>name</b>: The name of the column in the database. If not provided, 
     * the name of the key will be used but with every '-' replaced by '_'.</li>
     * <li><b>datatype</b>: The datatype of the column.  If not provided, 'varchar' 
     * will be used.</li>
     * <li><b>size</b>: Size of the column (if datatype does support size). 
     * If not provided, 1 will be used.</li>
     * <li><b>default</b>: A default value for the column if its value 
     * is not present in case of insert.</li>
     * <li><b>is-null</b>: A boolean. If the column allows null values, this should 
     * be set to true. Default is false.</li>
     * <li><b>is-primary</b>: A boolean. It must be set to true if the column 
     * represents a primary key. Note that the column will be set as unique 
     * once its set as a primary.</li>
     * <li><b>auto-inc</b>: A boolean. Only applicable if the column is a 
     * primary key. Set to true to auto-increment column value by 1 for every 
     * insert.</li>
     * <li><b>is-unique</b>: A boolean. If set to true, a unique index will 
     * be created for the column.</li>
     * <li><b>auto-update</b>: A boolean. If the column datatype is 'timestamp' or 
     * 'datetime' and this parameter is set to true, the time of update will 
     * change automatically without having to change it manually.</li>
     * <li><b>scale</b>: Number of numbers to the left of the decimal 
     * point. Only supported for decimal datatype.</li>
     * <li><b>comment</b> A comment which can be used to describe the column.</li>
     * </ul>
     * Note that the column will be added only if no column was found in the table which has the same name 
     * as the given column (key name and database name).
     * @return boolean true if the column is added. false otherwise.
     * @since 1.0
     */
    public function addColumn($key,$col) {
        $trimmedKey = trim($key);
        $keyLen = strlen($trimmedKey);
        if(strlen($keyLen) != 0 && $this->_isKeyNameValid($trimmedKey)){
            if(gettype($col) === 'array'){
                if(!isset($col['name'])){
                    $col['name'] = str_replace('-', '_', $trimmedKey);
                }
                $col = $this->_createColObj($col);
            }
            if($col instanceof MySQLColumn){
                if(!isset($this->colSet[$trimmedKey])){
                    $givanColName = $col->getName();
                    foreach ($this->columns() as $val){
                        $inTableColName = $val->getName();
                        if($inTableColName == $givanColName){
                            return false;
                        }
                    }
                    if($this->_isKeyNameValid($trimmedKey)){
                        $col->setOwner($this);
                        $this->colSet[$trimmedKey] = $col;
                        $this->_checkPKs();
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * 
     * @param type $key
     * @return boolean
     * @since 1.6.1
     */
    private function _isKeyNameValid($key) {
        $keyLen = strlen($key);
        $actualKeyLen = $keyLen;
        for ($x = 0 ; $x < $keyLen ; $x++){
            $ch = $key[$x];
            if($ch == '-' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){
                if($ch == '-'){
                    $actualKeyLen--;
                }
            }
            else{
                return false;
            }
        }
        return $actualKeyLen != 0;
    }

    /**
     * Checks if the table has a column or not.
     * @param string $colKey The index at which the column might be exist.
     * @return boolean true if the column exist. false otherwise.
     * @since 1.4
     */
    public function hasColumn($colKey) {
        return isset($this->colSet[trim($colKey)]);
    }
    /**
     * Returns the column object given the key that it was stored in.
     * @param string $key The name of the column key.
     * @return MySQLColumn|null MySQLColumn|null An object of type Column is returned if the given 
     * column was found. null in case of no column was found.
     * @since 1.0
     */
    public function getCol($key){
        $trimmed = trim($key);
        if(isset($this->colSet[$trimmed])){
            return $this->colSet[$trimmed];
        }
        $null = null;
        return $null;
    }
    /**
     * Returns the index of a column given its key.
     * @param string $key The name of the column key.
     * @return MySQLColumn|null The index of the column if a column was 
     * found which has the given key. -1 in case of no column was found.
     * @since 1.6
     */
    public function getColIndex($key){
        $trimmed = trim($key);
        if(isset($this->colSet[$trimmed])){
            return $this->colSet[$trimmed]->getIndex();
        }
        return -1;
    }
    /**
     * Returns a column given its index.
     * @param int $index The index of the column.
     * @return MySQLColumn|null If a column was found which has the specified index, 
     * it is returned. Other than that, The method will return null.
     * @since 1.6
     */
    public function getColByIndex($index){
        foreach ($this->colSet as $k => $col){
            if($col->getIndex() == $index){
                return $col;
            }
        }
        $null = null;
        return $null;
    }
    /**
     * Returns an array that contains all the keys the columns was stored in 
     * the table.
     * @return array an array that contains all the set of keys.
     * @since 1.2
     */
    public function colsKeys(){
        return array_keys($this->colSet);
    }
    /**
     * Returns the name of the storage engine used by the table.
     * @return string The name of the storage engine used by the table. The default 
     * value is 'InnoDB'.
     * @since 1.0
     */
    public function getEngine(){
        return $this->engin;
    }
    /**
     * Returns the character set that is used by the table.
     * @return string The character set that is used by the table.. The default 
     * value is 'utf8'.
     * @since 1.0
     */
    public function getCharSet(){
        return $this->charSet;
    }
}
