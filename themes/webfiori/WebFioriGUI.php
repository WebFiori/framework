<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class WebFioriGUI{
    public static function createTitleNode($title=null) {
        $titleRow = self::createRowNode(FALSE,FALSE);
        $h1 = new HTMLNode('h2');
        if($title != NULL){
            $h1->addTextNode($title);
        }
        else{
            $h1->addTextNode(Page::title());
        }
        $h1->setClassName('pa-'.Page::dir().'-col-10-nm-np');
        $titleRow->addChild($h1);
        Page::document()->getChildByID('main-content-area')->addChild($titleRow);
    }
    public static function functionDescription($arr=array(
        'long-name'=>'public static function getX()',
        'f-description'=>'Function Description.'
    )) {
        $row = self::createRowNode();
        $row->setAttribute('style', 'border: 1px solid;');
        $nameNode = self::createColNode(12,FALSE,FALSE);
        $nameNode->addTextNode('<b>'.$arr['long-name'].'</b>');
        $row->addChild($nameNode);
        
        $descNode = new PNode();
        $descNode->addText($arr['f-description']);
        $row->addChild($descNode);
        
        $ul = new UnorderedList();
        $paramsLi = new ListItem();
        $paramsLi->setText('<b>Parameters:</b>');
        $ul->addChild($paramsLi);
        $returnsLi = new ListItem();
        $returnsLi->setText('<b>Returns:</b>');
        $ul->addChild($returnsLi);
        $sinceLi = new ListItem();
        $sinceLi->setText('<b>Since:</b>');
        $ul->addChild($sinceLi);
        $throwsLi = new ListItem();
        $throwsLi->setText('<b>Throws:</b>');
        $ul->addChild($throwsLi);
        $row->addChild($ul);
        
        return $row;
    }
    public static function createConstDefNode($options=array()){
        
    } 
    public static function createFunctionReturnNode($returns=array()) {
        
    }
    public static function createFunctionParamsNode($paramsArr=array()) {
        
    }
    /**
     * 
     * @param type $colNum
     * @param type $withPadding
     * @param type $withMargin
     * @return HTMLNode
     */
    public static function createColNode($colNum=1,$withPadding=true,$withMargin=true){
        $wp = $withPadding === TRUE ? '' : '-np';
        $wm = $withMargin === TRUE ? '' : '-nm';
        $node = new HTMLNode();
        $node->setClassName('pa-'.Page::get()->getWritingDir().'-col-'.$colNum.$wm.$wp);
        return $node;
    }
    /**
     * 
     * @param type $withPadding
     * @param type $withMargin
     * @return HTMLNode
     */
    public static function createRowNode($withPadding=true,$withMargin=true){
        $wp = $withPadding === TRUE ? '' : '-np';
        $wm = $withMargin === TRUE ? '' : '-nm';
        $node = new HTMLNode();
        $node->setClassName('pa-row'.$wm.$wp);
        return $node;
    }
}