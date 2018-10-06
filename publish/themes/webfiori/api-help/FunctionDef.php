<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FunctionDef
 *
 * @author Ibrahim
 */
class FunctionDef {
    private $fName;
    private $accessMofifier;
    private $funcParams;
    private $funcReturns;
    private $shortDescription;
    private $longDescription;
    public function __construct() {
        $this->funcParams = array();
        $this->funcReturns = array(
            'return-types'=>'NULL',
            'description'=>'The function will return <a style="font-family:monospace" href="http://php.net/manual/en/language.types.null.php" target="_blank">NULL</a> by default.'
        );
        
        $this->accessMofifier = '';
    }
    public function setAccessModifier($mod) {
        $this->accessMofifier = $mod;
    }
    public function getAccessModofier() {
        return $this->accessMofifier;
    }
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
    public function setName($name) {
        $this->fName = $name;
    }
    public function getName() {
        return $this->fName;
    }
    public function addFuncParam($varName,$varType,$description, $isOptional=false) {
        $paramNum = count($this->funcParams);
        $this->funcParams['param-'.$paramNum] = array(
            'var-name'=>$varName,
            'var-type'=>$varType,
            'var-desc'=>$description,
            'is-optional'=>$isOptional
        );
    }
    public function setReturns($returnTypes, $desc) {
        $this->funcReturns = array(
            'return-types'=>$returnTypes,
            'description'=>$desc
        );
    }
    public function summaryHTMLNode() {
        $node = WebFioriGUI::createRowNode(TRUE, FALSE);
        $node->setAttribute('style', 'border: 1px solid;');
        $node->setClassName($node->getAttributeValue('class').' function-summary');
        $methNameNode = WebFioriGUI::createColNode(12, FALSE, FALSE);
        $methNameNode->setClassName('function-name');
        $nodeText = $this->getAccessModofier().' function <a class="function-name" href="#'.$this->getName().'">'.$this->getName().'</a>(';
        $count = count($this->funcParams);
        for($x = 0 ; $x < $count ; $x++){
            $param = $this->funcParams['param-'.$x];
            if($x + 1 == $count){
                $nodeText .= $param['var-type'].' '.$param['var-name'];
            }
            else{
                $nodeText .= $param['var-type'].' '.$param['var-name'].', ';
            }
        }
        $nodeText .= ')';
        $methNameNode->addTextNode($nodeText);
        $node->addChild($methNameNode);
        $descNode = new PNode();
        $descNode->addText($this->getShortDescription());
        $node->addChild($descNode);
        return $node;
    }
    public function asHTMLNode() {
        $node = WebFioriGUI::createRowNode(TRUE, FALSE);
        $node->setAttribute('style', 'border: 1px solid;');
        $node->setClassName($node->getAttributeValue('class').' function-summary');
        $methNameNode = WebFioriGUI::createColNode(12, FALSE, FALSE);
        $methNameNode->setID($this->getName());
        $methNameNode->setClassName($methNameNode->getAttributeValue('class').' function-name');
        $nodeText = $this->getAccessModofier().' function '.$this->getName().'(';
        $count = count($this->funcParams);
        for($x = 0 ; $x < $count ; $x++){
            $param = $this->funcParams['param-'.$x];
            if($x + 1 == $count){
                $nodeText .= $param['var-type'].' '.$param['var-name'];
            }
            else{
                $nodeText .= $param['var-type'].' '.$param['var-name'].', ';
            }
        }
        $nodeText .= ')';
        $methNameNode->addTextNode($nodeText);
        $node->addChild($methNameNode);
        $descNode = new PNode();
        $descNode->addText($this->getLongDescription());
        $node->addChild($descNode);
        if($count != 0){
            $node->addChild($this->createParametersBox());
        }
        $node->addChild($this->createReturnsBox());
        return $node;
    }
    private function createReturnsBox() {
        $node = WebFioriGUI::createRowNode(FALSE,FALSE);
        $textNode = new PNode();
        $textNode->addText('Returns: <span style="font-family:monospace">'.$this->funcReturns['return-types'].'</span>');
        $node->addChild($textNode);
        $descNode = new PNode();
        $descNode->addText($this->funcReturns['description']);
        $node->addChild($descNode);
        return $node;
    }
    private function createParametersBox() {
        $node = WebFioriGUI::createRowNode(FALSE,FALSE);
        $textNode = new PNode();
        $textNode->addText('Parameters:');
        $node->addChild($textNode);
        $ul = new UnorderedList();
        $count = count($this->funcParams);
        for($x = 0 ; $x < $count ; $x++){
            $param = $this->funcParams['param-'.$x];
            $li = new ListItem(TRUE);
            $text = '<span style="font-family: monospace;">'.$param['var-type'].' '.$param['var-name'].'</span>';
            if($param['is-optional'] === TRUE){
                $text .= ' [Optional]';
            }
            $text .= ' '.$param['var-desc'];
            $li->setText($text);
            $ul->addChild($li);
        }
        $node->addChild($ul);
        return $node;
    }
}
