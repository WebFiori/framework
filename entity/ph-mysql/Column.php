<?php
/**
 * A class that represents a column in MySQL table.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.5
 */
class Column{
    /**
     * The table that this column belongs to.
     * @var Table
     * @since 1.5 
     */
    private $ownerTable;
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
     * <li><b>datetime</b>: Used to store a point in time.</li>
     * <li><b>tinyblob</b></li>
     * <li><b>mediumblob</b></li>
     * <li><b>longblob</b></li>
     * </ul>
     * @var array 
     * @since 1.0
     */
    const DATATYPES = array(
        'int','varchar','timestamp','tinyblob','mediumblob','longblob',
        'datetime','text','mediumtext'
    );
    /**
     * A boolean value. Set to <b>TRUE</b> if column is unique.
     * @var boolean
     * @since 1.0 
     */
    private $isUnique;
    /**
     * A boolean value. Set to <b>TRUE</b> if column is primary and auto increment.
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
     * A boolean value. Set to <b>TRUE</b> if column allow null values. Default 
     * is <b>FALSE</b>.
     * @var boolean 
     */
    private $isNull;
    /**
     * A boolean value. Set to <b>TRUE</b> if the column is a primary key. Default 
     * is <b>FALSE</b>.
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
     * 
     * @param string $colName It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore. If the given column name is invalid the value 'col' will be 
     * set as an initial name for the column.
     * @param string $datatype The type of column data. It must be a value from the 
     * array <b>Column::DATATYPES</b>. If the given datatype is invalid, 'varchar' 
     * will be used as default type for the column.
     * @param int $size [optional] The size of the column. Used only in case of 
     * 'varachar' and 'int'. If the given size is invalid, 1 will be used as default 
     * value.
     */
    public function __construct($colName='col',$datatype='varchar',$size=1) {
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
     * Sets or unset the owner table of the column.
     * @param Table|NULL $table The owner of the column. If NULL is given, 
     * The owner will be unset. This function should 
     * not be called manually.
     * @since 1.5
     */
    public function setOwner(&$table) {
        if($table instanceof Table){
            $this->ownerTable = $table;
        }
        else if($table === NULL){
            $this->ownerTable = NULL;
        }
    }
    /**
     * Returns the table which owns this column.
     * @return Table|NULL The owner table of the column. 
     * If the column has no owner, the function will return NULL.
     * @since 1.5
     */
    public function &getOwner() {
        return $this->ownerTable;
    }
    /**
     * A function to call in case the user want to update the date of a column 
     * that has the type 'datetime' or 'timestamp' automatically if a record is updated.
     * @since 1.1
     */
    public function autoUpdate(){
        if($this->getType() == 'datetime' || $this->getType() == 'timestamp'){
            $this->onColUpdate = 'on update now()';
        }
    }
    /**
     * Sets the value of the property <b>$isUnique</b>.
     * @param boolean $bool <b>TRUE</b> if the column value is unique. <b>FALSE</b> 
     * if not.
     * @return boolean <b>TRUE</b> if the value of the property is updated.
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
     * Returns the value of the property <b>$isUnique</b>.
     * @return boolean <b>TRUE</b> if the column value is unique. 
     * @since 1.0
     */
    public function isUnique(){
        return $this->isUnique;
    }
    /**
     * Sets the name of the column.
     * @param string $name The name to set. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @return boolean <b>TRUE</b> if the column name updated. If the given value 
     * is <b>NULL</b> or invalid string, the method will return <b>Column::INV_COL_NAME</b>.
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
     * @return string The name of the column.
     * @since 1.0
     */
    public function getName(){
        return $this->name;
    }
    /**
     * Updates the value of the property <b>$isNull</b>.
     * @param boolean $bool <b>TRUE</b> if the column allow null values. <b>FALSE</b> 
     * if not. If the column is set as a primary, the property will not be updated.
     * @return boolean <b>TRUE</b> If the property value is updated. If the given 
     * value is not a boolean, the function will return <b>FALSE</b>. Also if 
     * the value of the property <b>$isPrimary</b> is <b>TRUE</b>, the function 
     * will return <b>FALSE</b>.
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
     * @return boolean <b>TRUE</b> if the column allows null values.
     * @since 1.0
     */
    public function isNull(){
        return $this->isNull;
    }
    /**
     * Updates the value of the property <b>$isPrimary</b>.
     * @param boolean $bool <b>TRUE</b> if the column is primary key. <b>FALSE</b> 
     * if not. Note that once the column become primary, it becomes unique.
     * @return boolean <b>TRUE</b> If the property value is updated. If the given 
     * value is not a boolean, the function will return <b>FALSE</b>.
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
     * @return boolean <b>TRUE</b> if the given colum is primary.
     * @since 1.0
     */
    public function isPrimary(){
        return $this->isPrimary;
    }
    /**
     * Sets the type of column data.
     * @param string $type The type of column data. It must be a value from the 
     * array <b>Column::DATATYPES</b>.
     * @param int $size Size of column data (for 'int' and 'varchar'). If the passed 
     * size is invalid, 1 will be used.
     * @param mixed $default Default value for the column.
     * @return boolean <b>TRUE</b> if the data type is set. <b>Column::INV_COL_DATATYPE</b> otherwise.
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
     * @return string
     * @since 1.0
     */
    public function getType(){
        return $this->type;
    }
    /**
     * 
     * @param mixed $default For integer data type, the passed value must be 
     * an integer. For 'varchar', the passed value must be a string. If the 
     * datatype is 'timestamp', the default will be 'current_timestamp' regardless 
     * of the passed value. If the 
     * datatype is 'datetime', the default will be 'now()' regardless 
     * of the passed value.
     * @return boolean <b>TRUE</b> if the value is set. <b>FALSE</b> otherwise.
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
     * Sets the size of data (for 'int' and 'varchar'). 
     * @param int $size The size to set. If the data type of the column is 'int', 
     * the maximum size is 11. If a number greater than 11 is given, the value will 
     * be set to 11. The maximum size for the 'varchar' is not specified.
     * @return boolean <b>TRUE</b> if the size is set. The function will return 
     * <b>Column::INV_DATASIZE</b> in case the size is invalid or datatype does not support 
     * size attribute. Also The function will return 
     * <b>Column::SIZE_NOT_SUPPORTED</b> in case the datatype of the column does not 
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
     * @param boolean $bool <b>TRUE</b> or <b>FALSE</b>. The property value will 
     * be set only if the column is set as primary key and the data type of 
     * the column is 'int'.
     * @return boolean <b>TRUE</b> if the property value changed.
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
     * @return boolean <b>TRUE</b> if the column is auto increment.
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
     * @return string The string 'utf8mb4_unicode_520_ci'.
     * @since 1.0
     */
    public function getCollation(){
        return 'utf8mb4_unicode_520_ci';
    }
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