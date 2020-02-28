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
/**
 * A class that represents a column in MySQL table.
 * @author Ibrahim
 * @version 1.6.6
 */
class MySQLColumn{
    /**
     *
     * @var type 
     * @since 1.6.6
     */
    private $alias;
    /**
     * A boolean which can be set to true in order to update column timestamp.
     * @var boolean 
     */
    private $autoUpdate;
    /**
     * A comment to add to the column.
     * @var string|null 
     * @since 1.6.3
     */
    private $comment;
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
     * A constant that is returned by some methods to tell that the 
     * name of a column is invalid.
     * @var string 
     * @since 1.2
     */
    const INV_COL_NAME = 'inv_col_nm';
    /**
     * A constant that is returned by some methods to tell that the 
     * datatype of a column is invalid.
     * @var string 
     * @since 1.2
     */
    const INV_COL_DATATYPE = 'inv_col_datatype';
    /**
     * A constant that is returned by some methods to tell that the 
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
     * <li><b>decimal</b> Used to store exact numeric values.</li>
     * <li><b>float</b> Used to store numbers in a single precision notation (approximate values).</li>
     * <li><b>double</b> Used to store numbers in a double precision notation (approximate values).</li>
     * </ul>
     * @var array 
     * @since 1.0
     */
    const DATATYPES = [
        'int','varchar','timestamp','tinyblob','blob','mediumblob','longblob',
        'datetime','text','mediumtext','decimal','double','float'
    ];
    
    /**
     * A boolean value. Set to true if column is unique.
     * @var boolean
     * @since 1.0 
     */
    private $isUnique;
    /**
     * A boolean value. Set to true if column is primary and auto increment.
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
     * A boolean value. Set to true if column allow null values. Default 
     * is false.
     * @var boolean 
     */
    private $isNull;
    /**
     * A boolean value. Set to true if the column is a primary key. Default 
     * is false.
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
     * The number of numbers that will appear after the decimal point.
     * @var int 
     * @since 1.6.2
     */
    private $scale;
    /**
     * Creates new instance of the class.
     * This method is used to initialize basic attributes of the column. 
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
     * 'varachar', 'int' or decimal. If the given size is invalid, 1 will be used as default 
     * value. Note that in case of decimal, if this value is 1, scale is set to 
     * 0. If this value is 2, scale is set to 1. If this value is greater than 
     * or equal to 3, scale is set to 2 by default.
     * @since 1.0
     */
    public function __construct($colName='col',$datatype='varchar',$size=1) {
        $this->mySqlVersion = '5.5';
        $this->autoUpdate = false;
        $this->isPrimary = false;
        if($this->setName($colName) !== true){
            $this->setName('col');
        }
        if(!$this->setType($datatype)){
            $this->setType('varchar');
        }
        $realDatatype = $this->getType();
        if($realDatatype == 'varchar' || $realDatatype == 'int' || $realDatatype == 'text'){
            if(!$this->setSize($size)){
                $this->setSize(1);
            }
        }
        if($realDatatype == 'decimal' || $realDatatype == 'float' || $realDatatype == 'double'){
            if(!$this->setSize($size)){
                $this->setSize(10);
                $this->setScale(2);
            }
            else{
                $size = $this->getSize();
                if($size == 0 || $size == 1){
                    $this->setScale(0);
                }
                else if($size == 2){
                    $this->setScale(1);
                }
                else{
                    $this->setScale(2);
                }
            }
        }
        
        $this->setIsNull(false);
        $this->setIsUnique(false);
    }
    /**
     * 
     * @return type
     * @since 1.6.6
     */
    public function getAlias($tablePrefix=false){
        if($tablePrefix === true && $this->getOwner() !== null){
            return $this->getOwner()->getName().'.'.$this->alias;
        }
        return $this->alias;
    }
    /**
     * Sets an optional alias name for the column.
     * @param string|null $name A string that represents the alias. If null 
     * is given, it means the alias will be unset.
     * @return boolean If the property value is updated, the method will return 
     * true. Other than that, the method will return false.
     * @since 1.6.6
     */
    public function setAlias($name) {
        if($name === null){
            $this->alias = null;
            return true;
        }
        $trimmed = trim($name);
        if(strlen($trimmed) != 0){
            if($this->_validateName($trimmed)){
                $this->alias = $trimmed;
                return true;
            }
        }
        return false;
    }
    /**
     * Clean and validates a value against the datatype of the column.
     * @param mixed $val The value that will be cleaned. It can be a single value or 
     * an array of values.
     * @param boolean $dateEndOfDay If the datatype of the column is 'datetime' 
     * or 'timestamp' and time is not specified in the passed value and this 
     * attribute is set to true, The time will be set to '23:59:59'. Default is 
     * false.
     * @return int|string|null The return type of the method will depend on 
     * the type of the column as follows:
     * <ul>
     * <li>If no default is set or type does not support default values, null is returned.</li>
     * <li><b>int</b>: The method will return an integer.</li>
     * <li><b>decimal, float and double</b>: A quoted string (such as '1.06')</li>
     * <li><b>varchar, text and mediumtext</b>: A quoted string (such as 'It is fun'). 
     * Note that any single quot inside the string will be escaped.</li>
     * <li><b>datetime and timestamp</b>: A quoted string (such as '2019-11-09 00:00:00')</li>
     * </ul>
     * @since 1.6.4
     */
    public function cleanValue($val,$dateEndOfDay=false) {
        $valType = gettype($val);
        if($valType == 'array'){
            $retVal = [];
            foreach ($val as $arrVal){
                $retVal[] = $this->_cleanValueHelper($arrVal, $dateEndOfDay);
            }
            return $retVal;
        }
        else{
            return $this->_cleanValueHelper($val, $dateEndOfDay);
        }
    }
    private function _cleanValueHelper($val,$dateEndOfDay=false){
        $colDatatype = $this->getType();
        if($val === null){
            return null;
        }
        else if($colDatatype == 'int'){
            return intval($val);
        }
        else if($colDatatype == 'decimal' || $colDatatype == 'float' || $colDatatype == 'double'){
            return '\''.floatval($val).'\'';
        }
        else if($colDatatype == 'varchar' || $colDatatype == 'text' || $colDatatype == 'mediumtext'){
            return '\''.str_replace("'", "\'", $val).'\'';
        }
        else if($colDatatype == 'datetime' || $colDatatype == 'timestamp'){
            $trimmed = strtolower(trim($val));
            if($trimmed == 'current_timestamp'){
                return 'current_timestamp';
            }
            else if($trimmed == 'now()'){
                return 'now()';
            }
            else if($this->_validateDateAndTime($trimmed)){
                return '\''.$trimmed.'\'';
            }
            else if($this->_validateDate($trimmed)){
                if($dateEndOfDay === true){
                    return '\''.$trimmed.' 23:59:59\'';
                }
                else{
                    return '\''.$trimmed.' 00:00:00\'';
                }
            }
            else{
                return '';
            }
        }
        else{
            return '';
        }
    }
    /**
     * Sets a comment which will appear with the column.
     * @param string|null $comment Comment text. It must be non-empty string 
     * in order to set. If null is passed, the comment will be removed.
     * @since 1.6.3
     */
    public function setComment($comment) {
        if($comment == null || strlen($comment) != 0){
            $this->comment = $comment;
        }
    }
    /**
     * Returns a string that represents a comment which was added with the column.
     * @return string|null Comment text. If it is not set, the method will return 
     * null.
     * @since 1.6.3
     */
    public function getComment() {
        return $this->comment;
    }
    /**
     * Sets the value of Scale.
     * Scale is simply the number of digits that will appear to the right of 
     * decimal point. Only applicable if the datatype of the column is decimal, 
     * float and double.
     * @param int $val Number of numbers after the decimal point. It must be a 
     * positive number.
     * @return boolean If scale value is set, the method will return true. 
     * false otherwise. The method will not set the scale in the following cases:
     * <ul>
     * <li>Datatype of the column is not decimal, float or double.</li>
     * <li>Size of the column is 0.</li>
     * <li>Given scale value is greater than the size of the column.</li>
     * </ul>
     * @since 1.6.2
     */
    public function setScale($val) {
        $type = $this->getType();
        if($type == 'decimal' || $type == 'float' || $type == 'double'){
            $size = $this->getSize();
            if($size != 0 && $val >= 0 && ($size - $val > 0)){
                $this->scale = $val;
                return true;
            }
        }
        return false;
    }
    /**
     * Returns the value of scale.
     * Scale is simply the number of digits that will appear to the right of 
     * decimal point. Only applicable if the datatype of the column is decimal, 
     * float and double.
     * @return int The number of numbers after the decimal point. Note that 
     * if the size of datatype of the column is 1, scale is set to 
     * 0 by default. If if the size of datatype of the column is 2, scale is 
     * set to 1. If if the size of datatype of the column is greater than 
     * or equal to 3, scale is set to 2 by default.
     * @since 1.6.2
     */
    public function getScale() {
        return $this->scale;
    }
    /**
     * Checks if a date-time string is valid or not.
     * @param string $date A date string in the format 'YYYY-MM-DD HH:MM:SS'.
     * @return boolean If the string represents correct date and time, the 
     * method will return true. False if it is not valid.
     */
    private function _validateDateAndTime($date) {
        $trimmed = trim($date);
        if(strlen($trimmed) == 19){
            $dateAndTime = explode(' ', $trimmed);
            if(count($dateAndTime) == 2){
                return $this->_validateDate($dateAndTime[0]) && $this->_validateTime($dateAndTime[1]);
            }
        }
        return false;
    }
    /**
     * 
     * @param type $time
     */
    private function _validateTime($time) {
        if(strlen($time) == 8){
            $split = explode(':', $time);
            if(count($split) == 3){
                $hours = intval($split[0]);
                $minutes = intval($split[1]);
                $sec = intval($split[2]);
                return $hours >= 0 && $hours <= 23 && $minutes >= 0 && $minutes < 60 && $sec >= 0 && $sec < 60;
            }
        }
        return false;
    }
    /**
     * 
     * @param type $date
     */
    private function _validateDate($date) {
        if(strlen($date) == 10){
            $split = explode('-', $date);
            if(count($split) == 3){
                $year = intval($split[0]);
                $month = intval($split[1]);
                $day = intval($split[2]);
                return $year > 1969 && $month > 0 && $month < 13 && $day > 0 && $day < 32;
            }
        }
        return false;
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
     * Note that the developer should not call this method manually. It is 
     * used only if the column is added or removed from MySQLTable object.
     * @param MySQLTable|null $table The owner of the column. If null is given, 
     * The owner will be unset.
     * @since 1.5
     */
    public function setOwner($table) {
        if($table instanceof MySQLTable){
            $this->ownerTable = $table;
            $colsCount = count($table->columns());
            $this->columnIndex = $colsCount == 0 ? 0 : $colsCount;
            $this->setMySQLVersion($table->getMySQLVersion());
        }
        else if($table === null){
            $this->ownerTable = null;
            $this->columnIndex = -1;
        }
    }
    /**
     * Returns the index of the column in its parent table.
     * @return int The index of the column in its parent table starting from 0. 
     * If the column has no parent table, the method will return -1.
     * @since 1.6
     */
    public function getIndex() {
        return $this->columnIndex;
    }
    /**
     * Returns the table which owns this column.
     * @return MySQLTable|null The owner table of the column. 
     * If the column has no owner, the method will return null.
     * @since 1.5
     */
    public function getOwner() {
        return $this->ownerTable;
    }
    /**
     * Sets the value of the property 'autoUpdate'.
     * It is used in case the user want to update the date of a column 
     * that has the type 'datetime' or 'timestamp' automatically if a record is updated. 
     * This method has no effect for other datatypes.
     * @param boolean $bool If true is passed, then the value of the column will 
     * be updated in case an update query is constructed. 
     * @since 1.1
     */
    public function setAutoUpdate($bool){
        if($this->getType() == 'datetime' || $this->getType() == 'timestamp'){
            $this->autoUpdate = $bool === true;
        }
    }
    /**
     * Returns the value of the property 'autoUpdate'.
     * @return boolean If the column type is 'datetime' or 'timestamp' and the 
     * column is set to auto update in case of update query, the method will 
     * return true. Default return value is valse.
     * @since 1.6.5
     */
    public function isAutoUpdate() {
        return $this->autoUpdate;
    }
    /**
     * Sets the value of the property $isUnique.
     * @param boolean $bool True if the column value is unique. false 
     * if not.
     * @since 1.0
     */
    public function setIsUnique($bool){
        $this->isUnique = $bool === true;
    }
    /**
     * Returns the value of the property $isUnique.
     * @return boolean true if the column value is unique. 
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
     * @param string $name The name of the table as it appears in the database.
     * @return boolean The method will return true if the column name updated. 
     * If the given value is null or invalid string, the method will return 
     * false.
     * @since 1.0
     */
    public function setName($name){
        $trimmed = trim($name);
        if(strlen($trimmed) != 0){
            if($this->_validateName($trimmed)){
                $this->name = $trimmed;
                return true;
            }
        }
        return false;
    }
    /**
     * 
     * @param type $name
     * @return boolean
     * @since 1.6.6
     */
    private function _validateName($name) {
        if(strpos($name, ' ') === false){
            for ($x = 0 ; $x < strlen($name) ; $x++){
                $ch = $name[$x];
                if($x == 0 && ($ch >= '0' && $ch <= '9')){
                    return false;
                }
                if($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){

                }
                else{
                    return false;
                }
            }
            return true;
        }
        return false;
    }
    /**
     * Returns the name of the column.
     * @param boolean $tablePrefix If this parameter is set to true and the column 
     * has an owner table, the name of the column will be prefixed with the owner 
     * table name. Default value is false.
     * @return string The name of the column. If the name is not set, the method 
     * will return the value 'col'.
     * @since 1.0
     */
    public function getName($tablePrefix=false){
        if($tablePrefix === true && $this->getOwner() !== null){
            return $this->getOwner()->getName().'.'.$this->name;
        }
        return $this->name;
    }
    /**
     * Updates the value of the property $isNull.
     * This property can be set to true if the column allow the insertion of 
     * null values. Note that if the column is set as a primary, the property 
     * will not be updated.
     * @param boolean $bool true if the column allow null values. false 
     * if not.
     * @return boolean true If the property value is updated. If the given 
     * value is not a boolean, the method will return false. Also if 
     * the column represents a primary key, the method will always return false.
     * @since 1.0
     */
    public function setIsNull($bool){
        if(gettype($bool) == 'boolean'){
            if(!$this->isPrimary()){
                $this->isNull = $bool;
                return true;
            }
        }
        return false;
    }
    /**
     * Checks if the column allows null values.
     * @return boolean true if the column allows null values.
     * @since 1.0
     */
    public function isNull(){
        return $this->isNull;
    }
    /**
     * Updates the value of the property <b>$isPrimary</b>.
     * Note that once the column become primary, it becomes unique by default.
     * @param boolean $bool <b>true</b> if the column is primary key. false 
     * if not.
     * @since 1.0
     */
    public function setIsPrimary($bool){
        $this->isPrimary = $bool === true;
        if($this->isPrimary() === true){
            $this->setIsNull(false);
            $this->setIsUnique(true);
        }
    }
    /**
     * Checks if the column is a primary key or not.
     * @return boolean true if the column is primary.
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
     * @param int $size Size of column data (for 'int', 'varchar', 'float', 'double' and 
     * 'decimal'). If the passed size is invalid, 1 will be used as a default value.
     * @param mixed $default Default value for the column to set in case no value is 
     * given in case of insert.
     * @return boolean The method will return true if the data type is set. False otherwise.
     * @since 1.0
     */
    public function setType($type,$size=1,$default=null){
        $s_type = strtolower(trim($type));
        if(in_array($s_type, self::DATATYPES)){
            if($s_type != 'int'){
                $this->setIsAutoInc(false);
            }
            $this->type = $s_type;
            if($s_type == 'varchar' || $s_type == 'int' || 
               $s_type == 'double' || $s_type == 'float' || $s_type == 'decimal'){
                if(!$this->setSize($size)){
                    $this->setSize(1);
                }
            }
            else{
                $this->setSize(1);
            }
            $this->default = null;
            if($default !== null){
                $this->setDefault($default);
            }
            return true;
        }
        return false;
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
     * For integer data type, the passed value must be an integer. For string types such as 
     * 'varchar' or 'text', the passed value must be a string. If the datatype 
     * is 'timestamp', the default will be set to current time and date 
     * if non-null value is passed (the value which is returned by the 
     * function date('Y-m-d H:i:s). If the passed 
     * value is a date string in the format 'YYYY-MM-DD HH:MM:SS', then it 
     * will be set to the given value. If the passed 
     * value is a date string in the format 'YYYY-MM-DD', then the default 
     * will be set to 'YYYY-MM-DD 00:00:00'. same applies to 'datetime' datatype. If 
     * null is passed, it implies that no default value will be used.
     * @param mixed $default The default value which will be set.
     * @since 1.0
     */
    public function setDefault($default=null){
        $this->default = $this->cleanValue($default);
        $type = $this->getType();
        if($type == 'datetime' || $type == 'timestamp'){
            if($this->default == 'now()' || $default == 'current_timestamp'){
                //$this->default = '\''.date('Y-m-d H:i:s').'\'';
            }
            else if(strlen($this->default) == 0 && $this->default !== null){
                $this->default = null;
            }
        }
    }
    
    /**
     * Returns the default value of the column.
     * @return mixed The default value of the column.
     * @since 1.0
     */
    public function getDefault(){
        $defaultVal = $this->default;
        if($defaultVal !== null){
            $dt = $this->getType();
            if($dt == 'varchar' || $dt == 'text' || $dt == 'mediumtext' || 
                    //$dt == 'timestamp' || $dt == 'datetime' || 
                    $dt == 'tinyblob' || $dt == 'blob' || $dt == 'mediumblob' || 
                    $dt == 'longblob' || $dt == 'decimal' || $dt == 'float' || $dt == 'double'
                    ){
                $retVal = substr($defaultVal, 1, strlen($defaultVal) - 2);
                if($dt == 'decimal' || $dt == 'float' || $dt == 'double'){
                    return floatval($retVal);
                }
                return $retVal;
            }
            else if(($this->default == 'now()' || $this->default == 'current_timestamp') &&
                    ($dt == 'datetime' || $dt == 'timestamp')){
                return date('Y-m-d H:i:s');
            }
            else if($dt == 'timestamp' || $dt == 'datetime'){
                return substr($defaultVal, 1, strlen($defaultVal) - 2);
            }
            else if($dt == 'int'){
                return intval($defaultVal);
            }
        }
        return $this->default;
    }
    /**
     * Sets the size of data (for 'int' and 'varchar' only). 
     * If the data type of the column is 'int', the maximum size is 11. If a 
     * number greater than 11 is given, the value will be set to 11. The 
     * maximum size for the 'varchar' is 21845. If a value greater that that is given, 
     * the datatype of the column will be changed to 'mediumtext'.
     * For decimal, double and float data types, the value will represent 
     * the  precision. If zero is given, then no specific value for precision 
     * and scale will be used.
     * @param int $size The size to set.
     * @return boolean true if the size is set. The method will return 
     * false in case the size is invalid or datatype does not support 
     * size attribute. Also The method will return 
     * false in case the datatype of the column does not 
     * support size.
     * @since 1.0
     */
    public function setSize($size){
        $type = $this->getType();
        if($type == 'varchar' || $type == 'text'){
            if($size > 0){
                $this->size = $size;
                if($type == 'varchar' && $size > 21845){
                    $this->setType('mediumtext');
                }
                return true;
            }
        }
        else if($type == 'int'){
            if($size > 0 && $size < 12){
                $this->size = $size;
                return true;
            }
            else if($size > 11){
                $this->size = 11;
                return true;
            }
        }
        else if($type == 'decimal' || $type == 'float' || $type == 'double'){
            if($size >= 0){
                $this->size = $size;
                return true;
            }
        }
        else{
            return false;
        }
        return false;
    }
    /**
     * Sets the value of the property <b>$isAutoInc</b>.
     * This attribute can be set only if the column is primary key and the 
     * datatype of the column is set to 'int'.
     * @param boolean $bool true or false.
     * @return boolean <b>true</b> if the property value changed. false 
     * otherwise.
     * @since 1.0
     */
    public function setIsAutoInc($bool){
        if($this->isPrimary()){
            if(gettype($bool) == 'boolean'){
                if($this->getType() == 'int'){
                    $this->isAutoInc = $bool;
                    return true;
                }
            }
        } 
        return false;
    }
    /**
     * Checks if the column is auto increment or not.
     * @return boolean true if the column is auto increment.
     * @since 1.1
     */
    public function isAutoInc(){
        return $this->isAutoInc;
    }
    /**
     * Returns the size of the column.
     * @return int The size of the column. If column data type is int, decimal, double 
     * or float, the value will represents the overall number of digits in the 
     * number (Precision) (e.g: size of 54.323 is 5). If the datatype is varchar, then the 
     * number will represents number of characters. Default value is 1 for 
     * all types including datetime and timestamp.
     * @since 1.0
     */
    public function getSize(){
        return $this->size;
    }
    /**
     * Returns the value of column collation.
     * @return string If MySQL version is '5.5' or lower, the method will 
     * return 'utf8mb4_unicode_ci'. Other than that, the method will return 
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
        else if($type == 'decimal' || $type == 'float' || $type == 'double'){
            if($this->getSize() != 0){
                $retVal .= $type.'('.$this->getSize().','.$this->getScale().') ';
            }
            else{
                $retVal .= $type.' ';
            }
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
        if($this->isUnique()){
            $retVal .= 'unique ';
        }
        $default = $this->default;
        if($default !== null){
            $retVal .= 'default '.$default.' ';
        }
        if($type == 'varchar' || $type == 'text' || $type == 'mediumtext'){
            $retVal .= 'collate '.$this->getCollation().' ';
        }
        $comment = $this->getComment();
        if($comment !== null){
            $retVal .= 'comment \''.$comment.'\'';
        }
        return trim($retVal);
    }
}