<?php
/**
 * A class that represents a column in MySQL table.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class Column{
    /**
     * An array of supported data types.
     * @var array 
     * @since 1.0
     */
    const DATATYPES = array(
        'int','varchar','timestamp','mediumblob'
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
     * 
     * @param string $colName The name of the column.
     * @param string $datatype The type of column data. It must be a value from the 
     * array <b>Column::DATATYPES</b>.
     * @param int $size [optional] The size of the column. Used only in case of 
     * 'varachar' and 'int'.
     */
    public function __construct($colName,$datatype,$size=1) {
        $this->setName($colName);
        $this->setType($datatype);
        $this->setSize($size);
        $this->setIsNull(FALSE);
        $this->setIsUnique(FALSE);
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
     * @return boolean <b>TRUE</b> if the column name updated. If the given value 
     * is <b>NULL</b> or empty string, the method will return <b>FALSE</b>.
     * @since 1.0
     */
    public function setName($name){
        if(gettype($name) == 'string'){
            if(strlen($name) != 0){
                $this->name = $name;
                return TRUE;
            }
        }
        return FALSE;
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
     * if not.
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
     * if not.
     * @return boolean <b>TRUE</b> If the property value is updated. If the given 
     * value is not a boolean, the function will return <b>FALSE</b>.
     * @since 1.0
     */
    public function setIsPrimary($bool){
        if(gettype($bool) == 'boolean'){
            $this->isPrimary = $bool;
            if($bool){
                $this->setIsNull(FALSE);
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
     * @param int $size Size of column data (for 'int' and 'varchar').
     * @param mixed $default Default value for the column.
     * @return boolean <b>TRUE</b> if the data type is set. <b>FALSE</b> otherwise.
     * @since 1.0
     */
    public function setType($type,$size=1,$default=null){
        $s_type = strtolower($type);
        if(in_array($s_type, self::DATATYPES)){
            $this->type = $type;
            $this->setSize($size);
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
        return FALSE;
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
     * of the passed value.
     * @return boolean <b>TRUE</b> if the value is set. <b>FALSE</b> otherwise.
     * @since 1.0
     */
    public function setDefault($default){
        $type = $this->getType();
        if($type == 'varchar'){
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
     * @return boolean <b>TRUE</b> if the size is set. <b>FALSE</b> otherwise.
     * @since 1.0
     */
    public function setSize($size){
        $type = $this->getType();
        if($type == 'varchar'){
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
        return false;
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
    public function isAutoInc(){
        return $this->isAutoInc;
    }
    /**
     * Returns the size of the column.
     * @return int
     * @since 1.0
     */
    public function getSize(){
        return $this->size;
    }
    /**
     * Returns the value of column collation.
     * @return string The string 'utf8_general_ci'.
     * @since 1.0
     */
    public function getCollation(){
        return 'utf8_general_ci';
    }
    public function __toString() {
        $retVal = $this->getName().' ';
        $type = $this->getType();
        if($type == 'int' || $type == 'varchar'){
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
            $retVal .= 'primary key ';
            if($this->isAutoInc()){
                $retVal .= 'auto_increment ';
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
            $retVal .= 'default '.$default.' ';
        }
        return $retVal;
    }
}

