<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Definition of class attribute
 *
 * @author Ibrahim
 */
class AttributeDef {
    private $name;
    private $type;
    private $shortDescription;
    private $longDescription;
    private $htmlId;
    
    public function setLongDescription($desc) {
        $this->longDescription = $desc;
    }
    
    public function getLongDescription() {
        return $this->longDescription;
    }
    
    public function setShortDescription($desc) {
        $this->shortDescription = $desc;
    }
    public function getShortDescription() {
        return $this->shortDescription;
    }
    public function setType($type) {
        $this->type = $type;
    }
    public function getType(){
        return $this->type;
    }

    public function setName($name) {
        $this->name = $name;
    }
    
    public function getName() {
        return $this->name;
    }
    public function getHTMLID() {
        return $this->htmlId;
    }
    public function setHTMLID($id) {
        $this->htmlId = $id;
    }
    public function summaryHTMLNode() {
        
    }
    public function asHTMLNode() {
        
    }
}
