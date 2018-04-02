<?php
/**
 * A class that represents a foreign key.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1
 */
class ForeignKey {
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
     */
    private $sourceTableCol;
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
     */
    private $referencedTableCol;
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
     * @return boolean <b>TRUE</b> if the name of the key is set. <b>FALSE</b> in 
     * case if the given key name is invalid.
     * @since 1.1
     */
    public function setKeyName($name) {
        if($this->validateAttr($name) == TRUE){
            $this->keyName = $name;
            return TRUE;
        }      
        return FALSE;
    }
    /**
     * A function that is used to validate the names of the key attributes (such as source column 
     * name or source table name)
     * @param string $name The string to validate. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @return boolean <b>TRUE</b> if the given parameter is valid. <b>FALSE</b> in 
     * case if the given parameter is invalid.
     */
    private function validateAttr($name){
        if(strlen($name) != 0){
            if(strpos($name, ' ') === FALSE){
                for ($x = 0 ; $x < strlen($name) ; $x++){
                    $ch = $name[$x];
                    if($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z')){

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
     * Returns the name of the column that will contain the value of the reference column.
     * @return string the name of the source column.
     * @since 1.0
     */
    public function getSourceCol(){
        return $this->sourceTableCol;
    }
    /**
     * Sets the name of the source column.
     * @param string $col The name of the column. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @return boolean <b>TRUE</b> if the source column name is set. <b>FALSE</b> in 
     * case if the given name is invalid.
     * @since 1.0
     */
    public function setSourceCol($col) {
        if($this->validateAttr($col)){
            $this->sourceTableCol = $col;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns the name of the referenced column.
     * @return string the name of the referenced column.
     * @since 1.0
     */
    public function getRefrenceCol(){
        return $this->referencedTableCol;
    }
    /**
     * Sets the name of the referenced column.
     * @param string $col The name of the column. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     * @return boolean <b>TRUE</b> if the reference column name is set. <b>FALSE</b> in 
     * case if the given name is invalid.
     * @since 1.0
     */
    public function setReferenceCol($col) {
        if($this->validateAttr($col)){
            $this->referencedTableCol = $col;
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
     * @return boolean <b>TRUE</b> if the source table name is set. <b>FALSE</b> in 
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
     * @return string|NULL The on delete condition as string or <b>NULL</b> in 
     * case it is not set.
     * @since 1.0 
     */
    public function getOnDelete(){
        return $this->onDeleteCondition;
    }
    /**
     * Sets the value of the property <b>$onUpdateCondition</b>.
     * @param string $val A value from the array <b>ForeignKey::CONDITIONS</b>. 
     * If the given value is <b>NULL</b>, the condition will be set to <b>NULL</b>.
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
     * underscore. The default value is 'source_table'.
     * @param string $sourceCol The name of the source column. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore. The default value is 'source_col'.
     * @param string $refTblName The name of the referenced table. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore. The default value is 'referenced_col'.
     * @param string $refCol The name of the referenced column. It must be a string and its not empty. 
     * Also it must not contain any spaces or any characters other than A-Z, a-z and 
     * underscore.
     */
    public function __construct(
            $name='key_name',
            $sourceTblName='source_table',
            $sourceCol='source_col',
            $refTblName='referenced_table',
            $refCol='referenced_col') {
        if(!$this->setKeyName($name)){
            $this->setKeyName('key_name');
        }
        if(!$this->setSourceCol($sourceCol)){
            $this->setSourceCol('source_col');
        }
        if(!$this->setReferenceCol($refCol)){
            $this->setReferenceCol('referenced_col');
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
     * @return boolean <b>TRUE</b> if the referenced table name is set. <b>FALSE</b> in 
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
        $retVal .= 'foreign key ('.$this->getSourceCol().') ';
        $retVal .= 'references '.$this->getReferenceTable().'('.$this->getRefrenceCol().') ';
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
