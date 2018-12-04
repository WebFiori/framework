<?php
namespace phpStructs\html;
/**
 * A class that represents the table HTML element (Under implementation).
 *
 * @author Ibrahim Ali <ibinshikh@hotmail.com>
 * @version 1.0
 */
class HTMLTable extends HTMLNode{
    private $tBody;
    private $cols;
    public function __construct($options=array(
        'rows'=>5,
        'cols'=>5
    )) {
        parent::__construct('table');
        $this->tBody = new HTMLNode('tbody');
        if(isset($options['rows']) && $options['rows'] > 0){
            $rows = $options['rows'];
        }
        else{
            $rows = 5;
        }
        if(isset($options['cols']) && $options['cols'] > 0){
            $this->cols = $options['cols'];
        }
        else{
            $this->cols = 5;
        }
        for($x = 0 ; $x < $rows ; $x++){
            $row = new HTMLNode('tr');
            for($y = 0 ; $y < $this->columnsCount() ; $y++){
                $cell = new TabelCell();
                $row->addChild($cell);
            }
            $this->tBody->addChild($row);
        }
        
        $this->addChild($this->tBody);
    }
    public function &getTBody() {
        return $this->tBody;
    }
    public function set($row,$col,$data){
        if($row > -1 && $row < $this->rowsCount()){
            if($col > -1 && $col < $this->columnsCount()){
                $this->tBody->children()->get($row)->children()->get($col)->removeAllChildNodes();
                if($data instanceof HTMLNode){
                    $this->tBody->children()->get($row)->children()->get($col)->addChild($data);
                }
                else{
                    $this->tBody->children()->get($row)->children()->get($col)->addTextNode($data);
                }
                return TRUE;
            }
        }
        return FALSE;
    }
    
    public function get($row,$col) {
        if($row > -1 && $row < $this->rowsCount()){
            if($col > -1 && $col < $this->columnsCount()){
                $this->tBody->children()->get($row)->children()->get($col)->removeAllChildNodes();
                $cellData = $this->tBody->children()->get($row)->children()->get($col)->children()->get(0);
                return $cellData;
            }
        }
        return NULL;
    }
    
    public function rowsCount() {
        return $this->tBody->childrenCount();
    }
    
    public function columnsCount() {
        return $this->cols;
    }
}
