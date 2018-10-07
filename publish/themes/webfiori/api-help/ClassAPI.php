<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * A class that is used to construct a GUI to view class API.
 *
 * @author Ibrahim
 */
class ClassAPI {
    private $cName;
    private $desc;
    private $longCName;
    private $classAttributes;
    private $classMethods;
    /**
     * Sets the name of the class.
     * @param string $className The name of the class (e.g. 'SessionManager').
     */
    public function setName($className) {
        $this->cName = $className;
        $this->classMethods = array();
    }
    /**
     * Returns the name of the class.
     * @return string The name of the class.
     */
    public function getName() {
        return $this->cName;
    }
    /**
     * Adds new function definition object to the class.
     * @param FunctionDef $func
     */
    public function addFunction($func) {
        if($func instanceof FunctionDef){
            $this->classMethods[] = $func;
        }
    }
    /**
     * Adds new attribute definition object to the class.
     * @param AttributeDef $attr
     */
    public function addAttribute($attr) {
        if($attr instanceof AttributeDef){
            $this->classAttributes[] = $attr;
        }
    }
    private function createBox($boxTitle) {
        $summaryNode = WebFioriGUI::createRowNode(FALSE);
        $summaryNode->setAttribute('style', 'border: 1px solid;');
        $titleNode = new PNode();
        $titleNode->addText($boxTitle);
        $titleNode->setClassName('summary-box-title');
        $summaryNode->addChild($titleNode);
        return $summaryNode;
    }
    /**
     * Returns HTMLNode which contains all class functions summaries.
     * @return HTMLNode HTMLNode which contains all class functions summaries.
     */
    public function getFunctionsSummaryNode() {
        if(count($this->classMethods) != 0){
            $summaryNode = $this->createBox('Class Functions Summary');
            foreach ($this->classMethods as $method){
                $summaryNode->addChild($method->summaryHTMLNode());
            }
            return $summaryNode;
        }
    }
    /**
     * Returns HTMLNode which contains all class functions details.
     * @return HTMLNode HTMLNode which contains all class functions details.
     */
    public function getFunctionsDetailsNode() {
        if(count($this->classMethods) != 0){
            $detailsNode = $this->createBox('Class Functions Details');
            foreach ($this->classMethods as $method){
                $detailsNode->addChild($method->asHTMLNode());
            }
            return $detailsNode;
        }
    }
    
    
    /**
     * Returns HTMLNode which contains all class attributes summaries.
     * @return HTMLNode HTMLNode which contains all class attributes summaries.
     */
    public function getAttributesSummaryNode() {
        if(count($this->classMethods) != 0){
            $summaryNode = $this->createBox('Class Attributes Summary');
            foreach ($this->classMethods as $method){
                $summaryNode->addChild($method->summaryHTMLNode());
            }
            return $summaryNode;
        }
    }
    /**
     * Returns HTMLNode which contains all class attributes details.
     * @return HTMLNode HTMLNode which contains all class attributes details.
     */
    public function getAttributesDetailsNode() {
        if(count($this->classMethods) != 0){
            $detailsNode = $this->createBox('Class Attributes Details');
            foreach ($this->classMethods as $method){
                $detailsNode->addChild($method->asHTMLNode());
            }
            return $detailsNode;
        }
    }
    /**
     * Sets the long name of the class
     * @param string $className The long name of the class (e.g. 
     * 'abstract class SessionManager implements JsonX').
     */
    public function setLongName($className) {
        $this->longCName = $className;
    }
    /**
     * Returns the long name of the class (e.g. 
     * 'abstract class SessionManager implements JsonX').
     * @return string
     */
    public function getLongName() {
        return $this->longCName;
    }
    /**
     * Sets the description of the class.
     * @param string $desc The description of the class.
     */
    public function setDescription($desc) {
        $this->desc = $desc;
    }
    /**
     * Returns the description of the class.
     * @return string The description of the class.
     */
    public function getDescription() {
        return $this->desc;
    }
}
