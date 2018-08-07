<?php
class GreenyTheme{
    /**
     * 
     * @param type $pageTitle
     */
    public static function createPageTitle($pageTitle){
        $titleRow = self::createRowNode();
        $h1 = new HTMLNode('h1');
        $h1->addTextNode($pageTitle);
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
    /**
     * Creates new input element.
     * @param type $lbl
     * @param type $inputType
     * @param type $placeholder
     * @param type $selectArr
     * @return HTMLNode
     */
    public static function createInputRow($lbl,$inputType='text',$placeholder='',$selectArr=array()){
        $row = self::createRowNode(FALSE, FALSE);
        $labelNode = new Label($lbl);
        $labelNode->setClassName('pa-'.Page::dir().'-col-12-nm-np');
        $row->addChild($labelNode);
        if($inputType == 'textarea'){
            $input = new HTMLNode('textarea');
            $input->setAttribute('placeholder', $placeholder);
        }
        else if($inputType == 'select'){
            $input = new HTMLNode('select');
            foreach ($selectArr as $option => $val){
                $o = new HTMLNode('option');
                $o->setAttribute('value', $option);
                $o->addTextNode($val);
                $input->addChild($o);
            }
        }
        else{
            $input = new Input($inputType);
            $input->setAttribute('placeholder', $placeholder);
        }
        $input->setClassName('pa-'.Page::dir().'-col-3');
        $row->addChild($input);
        return $row;
    }
    /**
     * 
     * @param type $colNum
     * @param type $lbl
     * @param type $inputType
     * @param type $placeholder
     * @param type $selectArr
     * @return HTMLNode
     */
    public static function createInputCol($colNum,$lbl,$inputType='text',$placeholder='',$selectArr=array()){
        $col = self::createColNode($colNum,FALSE, FALSE);
        $labelNode = new Label($lbl);
        $labelNode->setClassName('pa-'.Page::dir().'-col-12-nm-np');
        $col->addChild($labelNode);
        if($inputType == 'textarea'){
            $input = new HTMLNode('textarea');
            $input->setAttribute('placeholder', $placeholder);
        }
        else if($inputType == 'select'){
            $input = new HTMLNode('select');
            foreach ($selectArr as $option => $val){
                $o = new HTMLNode('option');
                $o->setAttribute('value', $option);
                $o->addTextNode($val);
                $input->addChild($o);
            }
        }
        else{
            $input = new Input($inputType);
            $input->setAttribute('placeholder', $placeholder);
        }
        $input->setClassName('pa-'.Page::dir().'-col-12');
        $col->addChild($input);
        return $col;
    }

}