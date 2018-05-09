<?php
/**
 * A class that represents MySQL table.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.4
 */
class Table {
    /**
     * A constant that is returned by some functions to tell that the 
     * table does not have a given column name.
     * @var string 
     * @since 1.4
     */
    const NO_SUCH_COL = 'no_such_col';
    /**
     * A constant that is returned by some functions to tell that the 
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
    /**
     * Creates a new instance of the class.
     * @param string $tName The name of the table. It must be a 
     * string and its not empty. Also it must not contain any spaces or any 
     * characters other than A-Z, a-z and underscore. If the given name is invalid, 
     * 'table' will be used as default.
     */
    public function __construct($tName = 'table') {
        if($this->setName($tName)== Table::INV_TABLE_NAME){
            $this->setName('table');
        }
        $this->engin = 'InnoDB';
        $this->charSet = 'utf8';
        $this->order = 0;
    }
    /**
     * Sets the order of the table in the database.
     * @param int $val The order of the table in the database. The value 
     * of this attributes describes the dependencies between tables. For example, 
     * if we have three tables, 'A', 'B' and 'C'. Let's assume that table 'B' 
     * references table 'A' and Table 'A' references table 'C'. In this case, 
     * table 'C' will have order 0, Table 'A' have order 1 and table 'B' have order 
     * 2.
     * @since 1.3 
     * @return boolean <b>TRUE</b> if the value of the attribute is set. 
     * <b>FALSE</b> if not.
     */
    public function setOrder($val){
        if(gettype($val) == 'integer'){
            if($val > -1){
                $this->order = $val;
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Returns the order of the table in the database.
     * @return int The order of the table in the database. The value 
     * of this attributes describes the dependencies between tables. For example, 
     * if we have three tables, 'A', 'B' and 'C'. Let's assume that table 'B' 
     * references table 'A' and Table 'A' references table 'C'. In this case, 
     * table 'C' will have order 0, Table 'A' have order 1 and table 'B' have order 
     * 2.
     * @since 1.3 
     */
    public function getOrder() {
        return $this->order;
    }
    /**
     * Adds a foreign key to the table.
     * @param ForeignKey $key an object of type <b>ForeignKey</b>. Note that it 
     * will be added only if no key was added to the table which has the same name 
     * as the given key.
     * @since 1.1
     * @return boolean <b>TRUE</b> if the key is added. <b>FALSE</b> otherwise.
     * @see ForeignKey
     */
    public function addForeignKey($key){
        if($key instanceof ForeignKey){
            foreach ($this->forignKeys() as $val){
                if($key->getKeyName() == $val->getKeyName()){
                    return FALSE;
                }
            }
            $key->setSourceTable($this->getName());
            array_push($this->foreignKeys, $key);
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Adds a foreign key to the table.
     * @param Table $refTable The table that will be referenced.
     * @param string $refColName The name of the column that will be referenced. It must 
     * be a column in the referenced table.
     * @param string $targetCol The target column. It must be a column in the current 
     * instance.
     * @param string $keyname The name of the foreign key.
     * @return boolean <b>TRUE</b> if the key is added. <b>FALSE</b> otherwise.
     * @see ForeignKey
     */
    public function addReference($refTable,$refColName,$targetCol,$keyname){
        if($refTable instanceof Table){
            $fk = new ForeignKey();
            if($fk->setKeyName($keyname) === TRUE){
                if($fk->setReferenceCol($refColName) === TRUE){
                    if($fk->setReferenceTable($refTable->getName()) === TRUE){
                        if($this->hasColumn($targetCol)){
                            $fk->setSourceCol($targetCol);
                            $fk->setReferenceTable($this->getName());
                            return $this->addForeignKey($fk);
                        }
                    }
                }
            }
        }
        return FALSE;
    }
    /**
     * Checks if a key with the given name exist on the table or not.
     * @param string $keyName The name of the key.
     * @return boolean <b>TRUE</b> if the table has a key. <b>FALSE</b> if not.
     * @since 1.4
     */
    public function hasForeignKey($keyName){
        foreach ($this->forignKeys() as $val){
            if($keyName == $val->getKeyName()){
                return TRUE;
            }
        }
        return FALSE;
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
     * @param string $param The name of the table (such as 'users'). It must be a 
     * string and its not empty. Also it must not contain any spaces or any 
     * characters other than A-Z, a-z and underscore.
     * @return boolean <b>TRUE</b> if the name of the table is set. <b>FALSE</b> 
     * in case the given name is invalid.
     * @since 1.0
     */
    public function setName($param) {
        if(gettype($param) == 'string'){
            if(strlen($param) != 0){
                if(strpos($param, ' ') === FALSE){
                    for ($x = 0 ; $x < strlen($param) ; $x++){
                        $ch = $param[$x];
                        if($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z')){
                            
                        }
                        else{
                            return FALSE;
                        }
                    }
                    $this->tableName = $param;
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    /**
     * Adds new column to the table.
     * @param string $key The index at which the column will be added to.
     * @param Column $col An object of type <b>Column</b>. Note that the column will 
     * be added only if no column was found in the table which has the same name 
     * as the given column.
     * @return boolean <b>TRUE</b> if the column is added. <b>FALSE</b> otherwise.
     * @since 1.0
     */
    public function addColumn($key,$col) {
        if(strlen($key) != 0){
            if($col instanceof Column){
                foreach ($this->columns() as $val){
                    if($val->getName() == $col->getName()){
                        return FALSE;
                    }
                }
                $this->colSet[$key] = $col;
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Checks if the table has a column or not.
     * @param @param string $key The index at which the column might be exist.
     * @return boolean <b>TRUE</b> if the column exist. <b>FALSE</b> otherwise.
     * @since 1.4
     */
    public function hasColumn($colKey) {
        return isset($this->colSet[$colKey]);
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
     * @since 1.2
     */
    public function colsKeys(){
        return array_keys($this->colSet);
    }

    /**
     * Returns an array that contains all the set of keys the columns was stored in.
     * @return array an array that contains all the set of keys.
     * @since 1.0
     * @deprecated since version 1.2 Use <b>Table::colsKeys()</b>
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
