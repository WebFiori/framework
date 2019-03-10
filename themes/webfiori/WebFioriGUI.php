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