<?php
/**
 * A class that represents MySQL table.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1
 */
class Table {
    /**
     * An array that contains all table foreign keys.
     * @var array 
     * @since 1.0
     */
    private $foreignKeys = array();
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
    private $colSet = array();
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
    public function __construct($tName = 'table') {
        $this->setName($tName);
        $this->engin = 'InnoDB';
        $this->charSet = 'utf8';
    }
    /**
     * Adds a foreign key to the table.
     * @param ForeignKey $key an object of type <b>ForeignKey</b>
     * @since 1.1
     * @see ForeignKey
     */
    public function addForeignKey($key){
        if($key instanceof ForeignKey){
            $key->setSourceTable($this->getName());
            array_push($this->foreignKeys, $key);
        }
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
     * @return string The name of the table.
     * @since 1.0
     */
    public function getName(){
        return $this->tableName;
    }
    /**
     * Sets the name of the table.
     * @param string $param The name of the table (such as 'users').
     * @since 1.0
     */
    public function setName($param) {
        if(gettype($param) == 'string'){
            if(strlen($param) != 0){
                $this->tableName = $param;
            }
        }
    }
    /**
     * Adds new column to the table.
     * @param string $key The index at which the column will be added to.
     * @param Column $col An object of type <b>Column</b>
     * @since 1.0
     */
    public function addColumn($key,$col) {
        if(strlen($key) != 0){
            if($col instanceof Column){
                $this->colSet[$key] = $col;
            }
        }
    }
    /**
     * Returns the column object given the key that it was stored in.
     * @param string $key The name of the key.
     * @return Column|NULL An object of type <b>Column</b> if the given column 
     * was found. <b>NULL</b> in case of no column was found.
     * @since 1.0
     */
    public function getCol($key){
        if(isset($this->colSet[$key])){
            return $this->colSet[$key];
        }
        return NULL;
    }

    /**
     * Returns an array that contains all the set of keys the columns was stored in.
     * @return array an array that contains all the set of keys.
     * @since 1.0
     */
    public function keys(){
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
