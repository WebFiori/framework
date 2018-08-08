<?php

/**
 * A class that represents the table HTML element (Under implementation).
 *
 * @author Ibrahim Ali <ibinshikh@hotmail.com>
 * @version 1.0
 */
class HTMLTable extends HTMLNode{
    const DEFAULT_OPTIONS = array(
        'with-header'=>false,
        'with-footer'=>false,
        'with-caption'=>false,
        'caption'=>'',
        'cols'=>3,
        'rows'=>3
    );
    private $tbody;
    private $thead;
    private $tfooter;
    private $tcaption;
    private $cols;
    /**
     * 
     * @param type $options
     * @since 1.0
     */
    public function __construct($options=self::DEFAULT_OPTIONS) {
        parent::__construct('table');
        $this->cols = new HTMLNode('colgroup');
        $this->tbody = new HTMLNode('tbody');
        $optionsValidated = $this->checkAndValidateOptions($options);
        if($optionsValidated['with-caption'] === TRUE){
            $this->tcaption = new HTMLNode('caption');
            $this->tcaption->addTextNode($optionsValidated['caption']);
            $this->addChild($this->tcaption);
        }
        if($optionsValidated['with-header'] === TRUE){
            $this->thead = new HTMLNode('thead');
            $this->addChild($this->thead);
        }
        $this->addChild($this->tbody);
        if($optionsValidated['with-footer'] === TRUE){
            $this->tfooter = new HTMLNode('tfoot');
            $this->addChild($this->tfooter);
        }
    }
    /**
     * 
     */
    public function addRow() {
        $row = new HTMLNode('tr');
        for($x = 0 ; $x < $this->columnsCount() ; $x++){
            $cell = new HTMLTableCell();
            $row->addChild($cell);
        }
        $this->tbody->addChild($row);
    }
    public function addColumn() {
        $col = new HTMLNode('col');
        
    }
    public function removeLastRow() {
        $this->tbody->children()->removeLast();
    }
    public function removeFirstRow() {
        $this->tbody->children()->removeFirst();
    }
    public function removeRow($index) {
        $this->tbody->children()->remove($index);
    }
    /**
     * 
     * @param type $row
     * @param type $col
     * @param HTMLNode $htmlNode
     * @return boolean Description
     */
    public function set($row, $col, $htmlNode){
        if($htmlNode instanceof HTMLNode){
            if($row < $this->rowsCount() && $col < $this->columnsCount()){
                $chToReplace = $this->tbody->children()->get($row)->children()->get($col);
                return $this->tbody->children()->get($row)->replace($chToReplace,$htmlNode);
            }
        }
        return FALSE;
    }
    /**
     * 
     * @param type $row
     * @param type $col
     * @return HTMLNode|NULL
     */
    public function &get($row,$col) {
        if($row < $this->rowsCount() && $col < $this->columnsCount()){
            $ch = &$this->tbody->children()->get($row)->children()->get($col);
            return $ch;
        }
        return NULL;
    }
    /**
     * 
     * @return int
     */
    public function rowsCount() {
        return $this->tbody->childrenCount();
    }
    /**
     * 
     * @return int
     */
    public function columnsCount() {
        return $this->cols->childrenCount();
    }
    /**
     * 
     * @param array $options
     * @return array
     */
    private function checkAndValidateOptions($options) {
        if(gettype($options) == 'array'){
            foreach (self::DEFAULT_OPTIONS as $option => $value){
                if(!isset($options[$option])){
                    $options[$option] = $value;
                }
            }
            return $options;
        }
        else{
            return self::DEFAULT_OPTIONS;
        }
    }
}

/**
 * Description of HTMLRow
 *
 * @author ibrah
 */
class HTMLRow extends HTMLNode{
    public function __construct() {
        parent::__construct('tr');
    }
    
}
/**
 * Description of HTMLTableCell
 *
 * @author ibrah
 */
class HTMLTableCell extends HTMLNode{
    public function __construct($th=false) {
        parent::__construct('td');
        if($th === TRUE){
            $this->setNodeName('th');
        }
    }
    public function setColSpan($num) {
        if($num > 0){
            $this->setAttribute('colspan', $num);
        }
        else if($num == 0){
            $this->removeAttribute('colspan');
        }
    }
}