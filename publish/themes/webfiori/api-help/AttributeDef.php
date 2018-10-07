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
    private $accessMofifier;
    private $pageUrl;
    /**
     * Sets attribute access modifier.
     * @param string $mod Attribute access modifier (e.g. 'public', 'protected', 'const').
     */
    public function setAccessModifier($mod) {
        $this->accessMofifier = $mod;
    }
    /**
     * Returns attribute access modifier.
     * @return string attribute access modifier (e.g. 'public', 'protected', 'const').
     */
    public function getAccessModofier() {
        return $this->accessMofifier;
    }
    /**
     * Sets the long description of the attribute.
     * @param string $desc The long description of the attribute.
     */
    public function setLongDescription($desc) {
        $this->longDescription = $desc;
    }
    public function setPageURL($url){
        $this->pageUrl = $url;
    }
    public function getPageURL(){
        return $this->pageUrl;
    }
    /**
     * Returns the long description of the attribute.
     * @return string The long description of the attribute.
     */
    public function getLongDescription() {
        return $this->longDescription;
    }
    /**
     * Sets the short description of the attribute.
     * @param string $desc The short description of the attribute.
     */
    public function setShortDescription($desc) {
        $this->shortDescription = $desc;
    }
    /**
     * Returns the short description of the attribute.
     * @return string The short description of the attribute.
     */
    public function getShortDescription() {
        return $this->shortDescription;
    }
    /**
     * Sets the datatype of the attribute.
     * @param string $type Datatype of the attribute.
     */
    public function setType($type) {
        $this->type = $type;
    }
    /**
     * Returns datatype of the attribute.
     * @return string Datatype of the attribute.
     */
    public function getType(){
        return $this->type;
    }
    /**
     * Sets the name of the attribute.
     * @param string $name The name of the attribute.
     */
    public function setName($name) {
        $this->name = $name;
    }
    /**
     * Returns the name of the attribute.
     * @return string The name of the attribute.
     */
    public function getName() {
        return $this->name;
    }
    /**
     * Returns HTML node that contains the summary part of the attribute.
     * @return HTMLNode The node will contain attribute name and short description.
     */
    public function summaryHTMLNode() {
        $node = WebFioriGUI::createRowNode(TRUE, FALSE);
        $node->setAttribute('style', 'border: 1px solid;');
        $node->setClassName($node->getAttributeValue('class').' attribute-summary');
        $attrNameNode = WebFioriGUI::createColNode(12, FALSE, FALSE);
        $attrNameNode->setClassName('class-attribute');
        $nodeText = $this->getAccessModofier().' <a class="class-attribute" href="'.$this->getPageURL().'#'.$this->getName().'">'.$this->getName().'</a>';
        $attrNameNode->addTextNode($nodeText);
        $node->addChild($attrNameNode);
        $descNode = new PNode();
        $descNode->addText($this->getShortDescription());
        $node->addChild($descNode);
        return $node;
    }
    
    /**
     * Returns HTML node that contains the details part of the attribute.
     * @return HTMLNode The node will contain attribute name and long description.
     */
    public function asHTMLNode() {
        $node = WebFioriGUI::createRowNode(TRUE, FALSE);
        $node->setAttribute('style', 'border: 1px solid;');
        $node->setClassName($node->getAttributeValue('class').' attribute-summary');
        $attrNameNode = WebFioriGUI::createColNode(12, FALSE, FALSE);
        $attrNameNode->setClassName('class-attribute');
        $attrNameNode->setID($this->getName());
        $nodeText = $this->getAccessModofier().' '.$this->getName();
        $attrNameNode->addTextNode($nodeText);
        $node->addChild($attrNameNode);
        $descNode = new PNode();
        $descNode->addText($this->getLongDescription());
        $node->addChild($descNode);
        return $node;
    }
}
