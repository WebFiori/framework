<?php
namespace phMysql;
/**
 * A class that represents a column in MySQL table.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.6.1
 */
class Column{
    /**
     * Version number of MySQL server.
     * @var string 
     */
    private $mySqlVersion;
    /**
     * The table that this column belongs to.
     * @var MySQLTable
     * @since 1.5 
     */
    private $ownerTable;
    /**
     * The index of the column in owner table.
     * @var int
     * @since 1.6 
     */
    private $columnIndex;
    /**
     * A constant that indicates the datatype of the 
     * column does not support size.
     * @var string
     * @since 1.4
     */
    const SIZE_NOT_SUPPORTED = 'TYPE_DOES_NOT_SUPPORT_SIZE';
    /**
     * A constant that is returned by some functions to tell that the 
     * name of a column is invalid.
     * @var string 
     * @since 1.2
     */
    const INV_COL_NAME = 'inv_col_nm';
    /**
     * A constant that is returned by some functions to tell that the 
     * datatype of a column is invalid.
     * @var string 
     * @since 1.2
     */
    const INV_COL_DATATYPE = 'inv_col_datatype';
    /**
     * A constant that is returned by some functions to tell that the 
     * size datatype of a column is invalid (for 'varchar' and 'int').
     * @var string 
     * @since 1.2
     */
    const INV_DATASIZE = 'inv_col_datatype';
    /**
     * An array of supported data types.
     * <p>The supported types are:</p>
     * <ul>
     * <li><b>int</b>: Used to store integers. Maximum size is 11.</li>
     * <li><b>varchar</b>: Used to store strings.</li>
     * <li><b>timestamp</b>: Used to store changes on the record. Note that only one column 
     * in the table can have this type.</li>
     * <li><b>date</b>: Used to store date in the formate 'YYYY-MM-DD' The range is '1000-01-01' to '9999-12-31'.</li>
     * <li><b>datetime</b>: Used to store a point in time. Somehow, similar to timestamp.</li>
     * <li><b>text</b>: Used to store text.</li>
     * <li><b>mediumtext</b>: Used to store text.</li>
     * <li><b>tinyblob</b>: Used to store up to 256 bytes of raw binary data.</li>
     * <li><b>blob</b> Used to store up to 16 kilobytes of raw binary data.</li>
     * <li><b>mediumblob</b> Used to store up to 16 megabytes of raw binary data.</li>
     * <li><b>longblob</b> Used to store up to 4 gigabytes of raw binary data.</li>
     * </ul>
     * @var array 
     * @since 1.0
     */
    const DATATYPES = array(
        'int','varchar','timestamp','tinyblob','blob','mediumblob','longblob',
        'datetime','text','mediumtext'
    );

    /**
     * A boolean value. Set to TRUE if column is unique.
     * @var boolean
     * @since 1.0 
     */
    private $isUnique;
    /**
     * A boolean value. Set to TRUE if column is primary and auto increment.
     * @var boolean 
     * @since 1.0
     */
    private $isAutoInc;
    /**
     * The name of the column.
     * @var string
     * @since 1.0 
     */
    private $name;
    /**
     * The size of the data in the column (for 'int' and 'varchar'). It must be 
     * a positive value.
     * @var int 
     * @since 1.0
     */
    private $size;
    /**
     * The type of the data that the column will have.
     * @var string 
     * @since 1.0
     */
    private $type;
    /**
     * A boolean value. Set to TRUE if column allow null values. Default 
     * is FALSE.
     * @var boolean 
     */
    private $isNull;
    /**
     * A boolean value. Set to TRUE if the column is a primary key. Default 
     * is FALSE.
     * @var boolean 
     * @since 1.0
     */
    private $isPrimary;
    /**
     * Default value for the column.
     * @var mixed 
     * @since 1.0
     */
    private $default;
    /**
     * This value is used in case of the datatype is 'datetime' or 'timestamp'.
     * @var string
     * @since 1.1 
     */
    private $onColUpdate;
    /**
     * Creates new instance of the class.
     * This function is used to initialize basic attributes of the column. 
     * First of all, it sets MySQL version number to 5.5. Then it validates the 
     * given column name and datatype and size.
     * @param string $colName It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore. If the given column name is invalid the value 'col' will be 
     * set as an initial name for the column.
     * @param string $datatype The type of column data. It must be a value from the 
     * array Column::DATATYPES. If the given datatype is invalid, 'varchar' 
     * will be used as default type for the column.
     * @param int $size The size of the column. Used only in case of 
     * 'varachar' and 'int'. If the given size is invalid, 1 will be used as default 
     * value.
     * @since 1.0
     */
    public function __construct($colName='col',$datatype='varchar',$size=1) {
        $this->mySqlVersion = '5.5';
        if($this->setName($colName) !== TRUE){
            $this->setName('col');
        }
        if(!$this->setType($datatype)){
            $this->setType('varchar');
        }
        if($this->getType() == 'varchar' || $this->getType() == 'int' || $this->getType() == 'text'){
            if(!$this->setSize($size)){
                $this->setSize(1);
            }
        }
        if($datatype == 'varchar' && $size > 21845){
            $this->setType('mediumtext');
        }
        $this->setIsNull(FALSE);
        $this->setIsUnique(FALSE);
    }
    /**
     * Sets version number of MySQL server.
     * Version number of MySQL is used to set the correct collation for the column 
     * in case of varchar or text data types. If MySQL version is '5.5' or lower, 
     * collation will be set to 'utf8mb4_unicode_ci'. Other than that, the 
     * collation will be set to 'utf8mb4_unicode_520_ci'.
     * @param string $vNum MySQL version number (such as '5.5').
     * @since 1.6.1
     */
    public function setMySQLVersion($vNum) {
        if(strlen($vNum) > 0){
            $split = explode('.', $vNum);
            if(count($split) >= 2){
                $major = intval($split[0]);
                $minor = intval($split[1]);
                if($major >= 0 && $minor >= 0){
                    $this->mySqlVersion = $vNum;
                }
            }
        }
    }
    /**
     * Returns version number of MySQL server.
     * @return string MySQL version number (such as '5.5'). If version number 
     * is not set, The default return value is '5.5'.
     * @since 1.6.1
     */
    public function getMySQLVersion() {
        return $this->mySqlVersion;
    }
    /**
     * Sets or unset the owner table of the column.
     * Note that the developer should not call this function manually. It is 
     * used only if the column is added or removed from MySQLTable object.
     * @param MySQLTable|NULL $table The owner of the column. If NULL is given, 
     * The owner will be unset.
     * @since 1.5
     */
    public function setOwner(&$table) {
        if($table instanceof MySQLTable){
            $this->ownerTable = $table;
            $colsCount = count($table->columns());
            $this->columnIndex = $colsCount == 0 ? 0 : $colsCount;
            $this->setMySQLVersion($table->getMySQLVersion());
        }
        else if($table === NULL){
            $this->ownerTable = NULL;
            $this->columnIndex = -1;
        }
    }
    /**
     * Returns the index of the column in its parent table.
     * @return int The index of the column in its parent table starting from 0. 
     * If the column has no parent table, the function will return -1.
     * @since 1.6
     */
    public function getIndex() {
        return $this->columnIndex;
    }
    /**
     * Returns the table which owns this column.
     * @return MySQLTable|NULL The owner table of the column. 
     * If the column has no owner, the function will return NULL.
     * @since 1.5
     */
    public function &getOwner() {
        return $this->ownerTable;
    }
    /**
     * Adds the statement "on update now()" in column creation string.
     * It is used in case the user want to update the date of a column 
     * that has the type 'datetime' or 'timestamp' automatically if a record is updated.
     * @since 1.1
     */
    public function autoUpdate(){
        if($this->getType() == 'datetime' || $this->getType() == 'timestamp'){
            $this->onColUpdate = 'on update now()';
        }
    }
    /**
     * Sets the value of the property $isUnique.
     * @param boolean $bool TRUE if the column value is unique. FALSE 
     * if not.
     * @return boolean TRUE if the value of the property is updated. The only case 
     * at which the function will return FALSE is when the passed parameter is 
     * not a boolean.
     * @since 1.0
     */
    public function setIsUnique($bool){
        if(gettype($bool) == 'boolean'){
            $this->isUnique = $bool;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns the value of the property $isUnique.
     * @return boolean TRUE if the column value is unique. 
     * @since 1.0
     */
    public function isUnique(){
        return $this->isUnique;
    }
    /**
     * Sets the name of the column.
     * The name of the column must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @param string $name The name to set.
     * @return boolean The function will return TRUE if the column name updated. 
     * If the given value is NULL or invalid string, the function will return 
     * Column::INV_COL_NAME.
     * @since 1.0
     */
    public function setName($name){
        if(gettype($name) == 'string'){
            if(strlen($name) != 0){
                if(strpos($name, ' ') === FALSE){
                    for ($x = 0 ; $x < strlen($name) ; $x++){
                        $ch = $name[$x];
                        if($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){
                            
                        }
                        else{
                            return Column::INV_COL_NAME;
                        }
                    }
                    $this->name = $name;
                    return TRUE;
                }
            }
        }
        return Column::INV_COL_NAME;
    }
    /**
     * Returns the name of the column.
     * @return string The name of the column. If the name is not set, the function 
     * will return the value 'col'.
     * @since 1.0
     */
    public function getName(){
        return $this->name;
    }
    /**
     * Updates the value of the property <b>$isNull</b>.
     * This property can be set to TRUE if the column allow the insertion of 
     * null values. Note that if the column is set as a primary, the property 
     * will not be updated.
     * @param boolean $bool TRUE if the column allow null values. FALSE 
     * if not.
     * @return boolean TRUE If the property value is updated. If the given 
     * value is not a boolean, the function will return FALSE. Also if 
     * the column represents a primary key, the function will always return FALSE.
     * @since 1.0
     */
    public function setIsNull($bool){
        if(gettype($bool) == 'boolean'){
            if(!$this->isPrimary()){
                $this->isNull = $bool;
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Checks if the column allows null values.
     * @return boolean TRUE if the column allows null values.
     * @since 1.0
     */
    public function isNull(){
        return $this->isNull;
    }
    /**
     * Updates the value of the property <b>$isPrimary</b>.
     * Note that once the column become primary, it becomes unique by default.
     * @param boolean $bool <b>TRUE</b> if the column is primary key. FALSE 
     * if not.
     * @return boolean The function will return TRUE If the property value is 
     * updated. If the given value is not a boolean, the function will return 
     * FALSE.
     * @since 1.0
     */
    public function setIsPrimary($bool){
        if(gettype($bool) == 'boolean'){
            $this->isPrimary = $bool;
            if($bool === TRUE){
                $this->setIsNull(FALSE);
                $this->setIsUnique(TRUE);
            }
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Checks if the column is a primary key or not.
     * @return boolean TRUE if the colum is primary.
     * @since 1.0
     */
    public function isPrimary(){
        return $this->isPrimary;
    }
    /**
     * Sets the type of column data.
     * The datatype must be a value from the array <b>Column::DATATYPES</b>. It 
     * can be in lower case or upper case.
     * @param string $type The type of column data.
     * @param int $size Size of column data (for 'int' and 'varchar'). If the passed 
     * size is invalid, 1 will be used.
     * @param mixed $default Default value for the column to set in case no value is 
     * given in case of insert.
     * @return boolean TRUE if the data type is set. Column::INV_COL_DATATYPE otherwise.
     * @since 1.0
     */
    public function setType($type,$size=1,$default=null){
        $s_type = strtolower($type);
        if(in_array($s_type, self::DATATYPES)){
            $this->type = $type;
            if($type == 'varchar' || $type == 'int'){
                if(!$this->setSize($size)){
                    $this->setSize(1);
                }
            }
            $this->onColUpdate = NULL;
            $this->default = NULL;
            if($default != NULL){
                if($s_type == 'varchar'){
                    $this->setDefault($default.'');
                }
                else if($s_type == 'int'){
                    $this->setDefault($default);
                }
            }
            return TRUE;
        }
        return Column::INV_COL_DATATYPE;
    }
    /**
     * Returns the type of column data (such as 'varchar').
     * @return string The type of column data. Default return value is 'varchar' 
     * if the column data type is not set.
     * @since 1.0
     */
    public function getType(){
        return $this->type;
    }
    /**
     * Sets the default value for the column to use in case of insert.
     * For integer data type, the passed value must be an integer. For 'varchar', 
     * the passed value must be a string. If the datatype is 'timestamp', the 
     * default will be 'current_timestamp' regardless of the passed value. If the 
     * datatype is 'datetime', the default will be 'now()' regardless 
     * of the passed value.
     * @param mixed $default The default value.
     * @return boolean TRUE if the value is set. FALSE otherwise.
     * @since 1.0
     */
    public function setDefault($default){
        $type = $this->getType();
        if($type == 'varchar' || $type == 'text' || $type == 'mediumtext'){
            if(gettype($default) == 'string'){
                $this->default = MySQLQuery::escapeMySQLSpeciarChars($default);
                return TRUE;
            }
        }
        else if($type == 'int'){
            if(gettype($default) == 'integer'){
                $this->default = $default;
                return TRUE;
            }
        }
        else if($type == 'timestamp'){
            $this->default = 'current_timestamp';
            return TRUE;
        }
        else if($type == 'datetime'){
            $this->default = 'now()';
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Returns the default value of the column.
     * @return mixed The default value of the column.
     * @since 1.0
     */
    public function getDefault(){
        return $this->default;
    }
    /**
     * Sets the size of data (for 'int' and 'varchar' only). 
     * If the data type of the column is 'int', the maximum size is 11. If a 
     * number greater than 11 is given, the value will be set to 11. The 
     * maximum size for the 'varchar' is not specified.
     * @param int $size The size to set.
     * @return boolean TRUE if the size is set. The function will return 
     * Column::INV_DATASIZE in case the size is invalid or datatype does not support 
     * size attribute. Also The function will return 
     * Column::SIZE_NOT_SUPPORTED in case the datatype of the column does not 
     * support size.
     * @since 1.0
     */
    public function setSize($size){
        $type = $this->getType();
        if($type == 'varchar' || $type == 'text'){
            if($size > 0){
                $this->size = $size;
                return TRUE;
            }
        }
        else if($type == 'int'){
            if($size > 0 && $size < 12){
                $this->size = $size;
                return TRUE;
            }
            else if($size > 11){
                $this->size = 11;
                return TRUE;
            }
        }
        else{
            return Column::SIZE_NOT_SUPPORTED;
        }
        return Column::INV_DATASIZE;
    }
    /**
     * Sets the value of the property <b>$isAutoInc</b>.
     * This attribute can be set only if the column is primary key and the 
     * datatype of the column is set to 'int'.
     * @param boolean $bool TRUE or FALSE.
     * @return boolean <b>TRUE</b> if the property value changed. FALSE 
     * otherwise.
     * @since 1.0
     */
    public function setIsAutoInc($bool){
        if($this->isPrimary()){
            if(gettype($bool) == 'boolean'){
                if($this->getType() == 'int'){
                    $this->isAutoInc = $bool;
                    return TRUE;
                }
            }
        } 
        return FALSE;
    }
    /**
     * Checks if the column is auto increment or not.
     * @return boolean TRUE if the column is auto increment.
     * @since 1.1
     */
    public function isAutoInc(){
        return $this->isAutoInc;
    }
    /**
     * Returns the size of the column.
     * @return int The size of the column. Apply only to 'varchar' and 'int'.
     * @since 1.0
     */
    public function getSize(){
        return $this->size;
    }
    /**
     * Returns the value of column collation.
     * @return string If MySQL version is '5.5' or lower, the function will 
     * return 'utf8mb4_unicode_ci'. Other than that, the function will return 
     * 'utf8mb4_unicode_520_ci'.
     * @since 1.0
     */
    public function getCollation(){
        $split = explode('.', $this->getMySQLVersion());
        if(isset($split[0]) && intval($split[0]) <= 5 && isset($split[1]) && intval($split[1]) <= 5){
            return 'utf8mb4_unicode_ci';
        }
        return 'utf8mb4_unicode_520_ci';
    }
    /**
     * Constructs a string that can be used to create the column in a table.
     * @return string A string that can be used to create the column in a table.
     */
    public function __toString() {
        $retVal = $this->getName().' ';
        $type = $this->getType();
        if($type == 'int' || $type == 'varchar' || $type == 'text'){
            $retVal .= $type.'('.$this->getSize().') ';
        }
        else{
            $retVal .= $type.' ';
        }
        if(!$this->isNull()){
            $retVal .= 'not null ';
        }
        else{
            $retVal .= 'null ';
        }
        if($this->isPrimary()){
            $t = &$this->getOwner();
            if($t != NULL){
                if($t->primaryKeyColsCount() == 1){
                    $retVal .= 'primary key ';
                    if($this->isAutoInc()){
                        $retVal .= 'auto_increment ';
                    }
                }
            }
            else{
                $retVal .= 'primary key ';
                if($this->isAutoInc()){
                    $retVal .= 'auto_increment ';
                }
            }
        }
        if($this->isUnique()){
            $retVal .= 'unique ';
        }
        $default = $this->getDefault();
        if($type == 'varchar'){
            $retVal .= 'collate '.$this->getCollation().' ';
        }
        if($default != NULL){
            if($this->getType() == 'varchar'){
                $retVal .= 'default \''.$default.'\' ';
            }
            else if($this->getType() == 'timestamp' || $this->getType() == 'datetime'){
                if($default == 'current_timestamp' || $default == 'now()'){
                    $retVal .= 'default '.$default.' ';
                }
                else{
                    $retVal .= 'default \''.$default.'\' ';
                }
                $retVal .= $this->onColUpdate;
            }
            else{
                $retVal .= 'default '.$default.' ';
            }
        }
        return $retVal;
    }
}