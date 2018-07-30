<?php
/**
 * A class that represents a foreign key.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.3
 */
class ForeignKey {
    /**
     * A constant that is returned by some functions to tell that the 
     * name of the foreign key is invalid.
     * @var string 
     * @since 1.2
     */
    const INV_KEY_NAME = 'inv_key_nm';
    /**
     * An array of allowed conditions for 'on delete' and 'on update'.
     * @var array 
     * @since 1.0 
     */
    const CONDITIONS = array(
        'set null','restrict','set default',
        'no action','cascade'
    );
    /**
     * The name of the table that will contain the foreign key.
     * @var string 
     * @since 1.0  
     */
    private $sourceTable;
    /**
     * The name of the column that will reference the other table.
     * @var string 
     * @since 1.0  
     * @deprecated since version 1.3
     */
    private $sourceTableCol;
    /**
     * An array that contains the names of sources columns. 
     * @var array 
     * @since 1.3
     */
    private $sourceTableCols;
    /**
     * The name of the table that will be referenced.
     * @var string  
     * @since 1.0 
     */
    private $referencedTable;
    /**
     * The name of the column in the referenced table.
     * @var string  
     * @since 1.0 
     * @deprecated since version 1.3
     */
    private $referencedTableCol;
    /**
     * An array that contains the names of referenced columns. 
     * @var array 
     * @since 1.3
     */
    private $referencedTableCols;
    /**
     * The 'on delete' condition.
     * @var string 
     * @since 1.0  
     */
    private $onDeleteCondition;
    /**
     * The 'on update' condition.
     * @var string 
     * @since 1.0  
     */
    private $onUpdateCondition;
    /**
     * The name of the key.
     * @var string 
     * @since 1.0 
     */
    private $keyName;
    /**
     * Sets the name of the key.
     * @param string $name The name of the key. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @return boolean|string TRUE if the name of the key is set. The function will 
     * return the constant ForeignKey::INV_KEY_NAME in 
     * case if the given key name is invalid.
     * @since 1.1
     */
    public function setKeyName($name) {
        if($this->validateAttr($name) == TRUE){
            $this->keyName = $name;
            return TRUE;
        }      
        return ForeignKey::INV_KEY_NAME;
    }
    /**
     * A function that is used to validate the names of the key attributes (such as source column 
     * name or source table name)
     * @param string $name The string to validate. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @return boolean TRUE if the given parameter is valid. FALSE in 
     * case if the given parameter is invalid.
     */
    private function validateAttr($name){
        if(strlen($name) != 0){
            if(strpos($name, ' ') === FALSE){
                for ($x = 0 ; $x < strlen($name) ; $x++){
                    $ch = $name[$x];
                    if($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){

                    }
                    else{
                        return FALSE;
                    }
                }
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Returns the name of the key.
     * @return string The name of the key.
     * @since 1.0
     */
    public function getKeyName() {
        return $this->keyName;
    }
    /**
     * Returns an array which contains the names of the columns 
     * that will contain the value of the referenced columns.
     * @return array An array which contains the names of the columns 
     * that will contain the value of the referenced columns.
     * @return array the name of the source column.
     * @since 1.3
     */
    public function getSourceCols() {
        return $this->sourceTableCols;
    }
    /**
     * Returns the name of the column that will contain the value of the reference column.
     * @return string the name of the source column.
     * @since 1.0
     * @deprecated since version 1.3
     */
    public function getSourceCol(){
        return $this->sourceTableCols[0];
    }
    /**
     * Adds new source column.
     * @param string $colName The name of the column. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @return boolean TRUE if the source column name is added. FALSE in 
     * case if the given name is invalid.
     * @since 1.3
     */
    public function addSourceCol($colName) {
        if($this->validateAttr($colName)){
            $this->sourceTableCols[] = $colName;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Sets the name of the source column.
     * @param string $col The name of the column. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @return boolean TRUE if the source column name is set. FALSE in 
     * case if the given name is invalid.
     * @since 1.0
     * @deprecated since version 1.3
     */
    public function setSourceCol($col) {
        if($this->validateAttr($col)){
            $this->sourceTableCols[0] = $col;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns an array that contains the names of the referenced columns.
     * @return string An array that contains the names of the referenced columns.
     * @since 1.3
     */
    public function getRefrenceCols(){
        return $this->referencedTableCols;
    }
    /**
     * Returns the name of the referenced column.
     * @return string the name of the referenced column.
     * @since 1.0
     * @deprecated since version 1.3
     */
    public function getRefrenceCol(){
        return $this->referencedTableCols[0];
    }
    /**
     * Sets the name of the referenced column.
     * @param string $col The name of the column. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @return boolean TRUE if the reference column name is set. FALSE in 
     * case if the given name is invalid.
     * @since 1.0
     * @deprecated since version 1.3
     */
    public function setReferenceCol($col) {
        if($this->validateAttr($col)){
            $this->referencedTableCols[0] = $col;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Adds new reference column.
     * @param string $colName The name of the column. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @return boolean TRUE if the reference column name is added. FALSE in 
     * case if the given name is invalid.
     * @since 1.3
     */
    public function addReferenceCol($colName){
        if($this->validateAttr($colName)){
            $this->referencedTableCols[] = $colName;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns the name of the table that contains the foreign key.
     * @return string The name of the table that contains the foreign key.
     * @since 1.0
     */
    public function getSourceTable(){
        return $this->sourceTable;
    }
    /**
     * Sets the name of the table that will contain the key.
     * @param string $name The name of the source table. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @return boolean TRUE if the source table name is set. FALSE in 
     * case if the given name is invalid.
     * @since 1.1
     */
    public function setSourceTable($name) {
        if($this->validateAttr($name)){
            $this->sourceTable = $name;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns the condition that will happen if the value of the column in the 
     * reference table is deleted.
     * @return string|NULL The on delete condition as string or NULL in 
     * case it is not set.
     * @since 1.0 
     */
    public function getOnDelete(){
        return $this->onDeleteCondition;
    }
    /**
     * Sets the value of the property $onUpdateCondition.
     * @param string $val A value from the array ForeignKey::CONDITIONS. 
     * If the given value is NULL, the condition will be set to NULL.
     * @since 1.0
     */
    public function setOnDelete($val){
        if(in_array(strtolower($val), self::CONDITIONS)){
            $this->onDeleteCondition = $val;
        }
        elseif ($val == NULL) {
            $this->onDeleteCondition = NULL;
        }
    }
    /**
     * Returns the condition that will happen if the value of the column in the 
     * reference table is updated.
     * @return string|NULL The on update condition as string or <b>NULL</b> in 
     * case it is not set.
     * @since 1.0 
     */
    public function getOnUpdate(){
        return $this->onUpdateCondition;
    }
    /**
     * Sets the value of the property <b>$onUpdateCondition</b>.
     * @param string $val A value from the array <b>ForeignKey::CONDITIONS</b>. 
     * If the given value is <b>NULL</b>, the condition will be set to <b>NULL</b>.
     * @since 1.0
     */
    public function setOnUpdate($val){
        if(in_array(strtolower($val), self::CONDITIONS)){
            $this->onUpdateCondition = $val;
        }
        elseif ($val == NULL) {
            $this->onUpdateCondition = NULL;
        }
    }

    /**
     * Returns the name of the table that is referenced by the key.
     * @return string The name of the table that is referenced by the key..
     * @since 1.0
     */
    public function getReferenceTable(){
        return $this->referencedTable;
    }
    /**
     * Creates new foreign key.
     * @param string $name The name of the key. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore. The default value is 'key_name'.
     * @param string $sourceTblName The name of the table that will contain the key. 
     * It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore. The default value is 'NULL'.
     * @param array|string $sourceCol A name or an array of names that contain 
     * source columns names. The name or names must not contain any spaces 
     * or any characters other than A-Z, a-z and underscore. The default 
     * value is 'NULL'.
     * @param string $refTblName The name of the referenced table. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore. The default value is 'NULL'.
     * @param string $refCol A name or an array of names that contain 
     * referenced columns names. The name or names must not contain any spaces 
     * or any characters other than A-Z, a-z and underscore. The default 
     * value is 'NULL'.
     */
    public function __construct(
            $name='key_name',
            $sourceTblName=null,
            $sourceCol=null,
            $refTblName=null,
            $refCol=null) {
        $this->referencedTableCols = array();
        $this->sourceTableCols = array();
        if($this->setKeyName($name) !== TRUE){
            $this->setKeyName('key_name');
        }
        if(gettype($sourceCol) == 'array'){
            foreach ($sourceCol as $col){
                $this->addSourceCol($col);
            }
        }
        else if($this->validateAttr($sourceCol)){
            $this->sourceTableCols[] = $sourceCol;
        }
        if(gettype($refCol) == 'array'){
            foreach ($refCol as $col){
                $this->addReferenceCol($col);
            }
        }
        else{
            if($this->validateAttr($refCol)){
                $this->referencedTableCols[] = $refCol;
            }
        }
        if(!$this->setSourceTable($sourceTblName)){
            $this->setSourceTable('source_table');
        }
        if(!$this->setReferenceTable($refTblName)){
            $this->setReferenceTable('referenced_table');
        }
    }
    /**
     * Sets the name of the table that is referenced by the key.
     * @param string $name The name of the referenced table. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @return boolean TRUE if the referenced table name is set. FALSE in 
     * case if the given name is invalid.
     * @since 1.0
     */
    public function setReferenceTable($name) {
        if($this->validateAttr($name)){
            $this->referencedTable = $name;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns a query that can be used to add the key to the source table.
     * @return string alter table query to add the key to the source table.
     * @since 1.0
     */
    public function getAlterStatement(){
        $retVal = 'alter table '.$this->getSourceTable().' ';
        $retVal .= 'add constraint '.$this->getKeyName().' ';
        $retVal .= 'foreign key (';
        $sourceCount = count($this->sourceTableCols);
        $i = 0;
        foreach ($this->sourceTableCols as $col){
            if($i + 1 == $sourceCount){
                $retVal .= $col.') ';
            }
            else{
                $retVal .= $col.', ';
            }
            $i++;
        }
        $retVal .= 'references '.$this->getReferenceTable().'(';
        $refCount = count($this->referencedTableCols);
        $i = 0;
        foreach ($this->referencedTableCols as $col){
            if($i + 1 == $refCount){
                $retVal .= $col.') ';
            }
            else{
                $retVal .= $col.', ';
            }
            $i++;
        }
        $onDelete = $this->getOnDelete();
        if($onDelete != NULL){
            $retVal .= 'on delete '.$onDelete.' ';
        }
        $onUpdate = $this->getOnUpdate();
        if($onUpdate != NULL){
            $retVal .= 'on update '.$onUpdate;
        }
        return $retVal;
    }
}
