<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ClassAPI
 *
 * @author Ibrahim
 */
class ClassAPI {
    private $cName;
    private $desc;
    private $longCName;
    private $classAttributes;
    private $classMethods;
    public function setName($className) {
        $this->cName = $className;
        $this->classMethods = array();
    }
    public function getName() {
        return $this->cName;
    }
    public function addFunction($func) {
        if($func instanceof FunctionDef){
            $this->classMethods[] = $func;
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
    public function getFunctionsSummaryNode() {
        if(count($this->classMethods) != 0){
            $summaryNode = $this->createBox('Class Functions Summary');
            foreach ($this->classMethods as $method){
                $summaryNode->addChild($method->summaryHTMLNode());
            }
            return $summaryNode;
        }
    }
    public function getFunctionsDetailsNode() {
        if(count($this->classMethods) != 0){
            $summaryNode = $this->createBox('Class Functions Details');
            foreach ($this->classMethods as $method){
                $summaryNode->addChild($method->asHTMLNode());
            }
            return $summaryNode;
        }
    }
    public function setLongName($className) {
        $this->longCName = $className;
    }
    public function getLongName() {
        return $this->longCName;
    }
    public function setDescription($desc) {
        $this->desc = $desc;
    }
    public function getDescription() {
        return $this->desc;
    }
}
