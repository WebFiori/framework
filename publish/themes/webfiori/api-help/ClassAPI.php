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
    private $pageUrl;
    private $package;
    private $extends;
    private $implements;
    private $classType;
    /**
     * Sets the name of the class.
     * @param string $className The name of the class (e.g. 'SessionManager').
     */
    public function setName($className) {
        $this->cName = $className;
        $this->classMethods = array();
        $this->implements = array();
        $this->pageUrl = Util::getRequestedURL();
        $this->classType = 'class';
    }
     /**
     * Sets the type of the class.
     * @param string $mod Class type (e.g. 'class', 'interface').
     */
    public function setClassType($mod) {
        $this->classType = $mod;
    }
    /**
     * Returns The type of the class.
     * @return string Class type (e.g. 'class', 'interface').
     */
    public function getClassType() {
        return $this->classType;
    }
    
    public function getInterfaces() {
        return $this->implements;
    }
    public function implementsInterface($interfaceName) {
        $this->implements[] = $interfaceName;
    }
    public function extendClass($className) {
        $this->extends = $className;
    }
    public function setPackage($package) {
        $this->package = $package;
    }
    public function getPackage() {
        return $this->package;
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
            $func->setPageURL($this->pageUrl);
            $this->classMethods[] = $func;
        }
    }
    /**
     * Adds new attribute definition object to the class.
     * @param AttributeDef $attr
     */
    public function addAttribute($attr) {
        if($attr instanceof AttributeDef){
            $attr->setPageURL($this->pageUrl);
            $this->classAttributes[] = $attr;
        }
    }
    private function createBox($boxTitle) {
        $summaryNode = WebFioriGUI::createRowNode();
        $summaryNode->setAttribute('style', 'border: 1px solid;');
        $titleNode = new PNode();
        $titleNode->setClassName('pa-ltr-col-4-nm-np summary-box-title');
        $titleNode->addText($boxTitle);
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
        if(count($this->classAttributes) != 0){
            $summaryNode = $this->createBox('Class Attributes Summary');
            foreach ($this->classAttributes as $attribute){
                $summaryNode->addChild($attribute->summaryHTMLNode());
            }
            return $summaryNode;
        }
    }
    /**
     * Returns HTMLNode which contains all class attributes details.
     * @return HTMLNode HTMLNode which contains all class attributes details.
     */
    public function getAttributesDetailsNode() {
        if(count($this->classAttributes) != 0){
            $detailsNode = $this->createBox('Class Attributes Details');
            foreach ($this->classAttributes as $attribute){
                $detailsNode->addChild($attribute->asHTMLNode());
            }
            return $detailsNode;
        }
    }
    /**
     * Returns the long name of the class (e.g. 
     * 'abstract class SessionManager implements JsonX').
     * @return string
     */
    public function getLongName() {
        $longNm = $this->getClassType().' '.$this->getName();
        if(strlen($this->extends)){
            $longNm .= ' extends '.$this->extends;
        }
        $intrfaces = $this->getInterfaces();
        $count = count($intrfaces);
        if($count != 0){
            $interfacesStr = ' implements ';
            $index = 0;
            foreach ($intrfaces as $interfaceNm){
                if($index + 1 == $count){
                    $interfacesStr .= $interfaceNm;
                }
                else{
                    $interfacesStr .= $interfaceNm.', ';
                }
                $index++;
            }
            $longNm .= $interfacesStr;
        }
        return $longNm;
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
