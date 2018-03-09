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
     * @param string $name The name of the key.
     * @since 1.1
     */
    public function setKeyName($name) {
        $this->keyName = $name;
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
     * @param type $col The name of the column in the source table.
     * @since 1.0
     */
    public function setSourceCol($col) {
        $this->sourceTableCol = $col;
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
     * @param type $col The name of the column in the referenced table.
     * @since 1.0
     */
    public function setReferenceCol($col) {
        $this->referencedTableCol = $col;
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
     * @param string $name The name of the table.
     * @since 1.1
     */
    public function setSourceTable($name) {
        $this->sourceTable = $name;
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
            $this->onUpdateCondition = $val;
        }
        elseif ($val == NULL) {
            $this->onUpdateCondition = NULL;
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
    public function __construct($name='key_name') {
        $this->setKeyName($name);
        $this->setSourceCol('source_col');
        $this->setReferenceCol('referenced_col');
        $this->setSourceTable('source_table');
        $this->setReferenceTable('referenced_table');
    }
    /**
     * Sets the name of the table that is referenced by the key.
     * @param type $name The name of the table.
     * @since 1.0
     */
    public function setReferenceTable($name) {
        $this->referencedTable = $name;
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
