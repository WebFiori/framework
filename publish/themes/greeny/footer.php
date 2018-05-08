<?php

function dynamicFooter(){
    if(Page::get()->getLang() != NULL){
        if(Page::get()->getWritingDir()){
            return '<?php echo staticFooter()?>';
        }
        else{
            throw new Exception('Writing direction of the page is not set.');
        }
    }
    else{
        throw new Exception('Language of the page is not set.');
    }
}

function staticFooter(){
    if(Page::get()->getWritingDir() != null && Page::get()->getLang() != NULL){
        $node = new HTMLNode('div');
        $node->setAttribute('class', 'pa-row');
        $fNode = new HTMLNode('footer');
        $fNode->setAttribute('id','footer');
        $fNode->setAttribute('dir', Page::get()->getWritingDir());
        $fNode->setAttribute('class','pa-'.Page::get()->getWritingDir().'-col-twelve show-border');
        $fNode->setAttribute('name', 'footer');
        $fNode->setAttribute('itemtype','http://schema.org/WPFooter');
        $fNav = new HTMLNode('nav');
        $fNavUl = new HTMLNode('ul');
        $fNav->addChild($fNavUl);
        $fNode->addChild($fNav);
        $node->addChild($fNode);
        $div = new HTMLNode('div');
        $div->setAttribute('class', 'pa-'.Page::get()->getWritingDir().'-col-twelve');
        if(Page::get()->getLang() == "EN"){
            $textNode = new HTMLNode('', FALSE, TRUE);
            $textNode->setText('Programming Academia, All Rights Reserved © 2018');
        }
        else if(Page::get()->getLang() == 'AR'){
            $textNode = new HTMLNode('', FALSE, TRUE);
            $textNode->setText('أكاديميا البرمجة, جميع الحقوق محفوظة © 2018 ');
        }
        $div->addChild($textNode);
        $fNode->addChild($div);
        return $node;
    }
}

function getFooterNode($dynamic=TRUE){
    if($dynamic){
        $node = new HTMLNode('', FALSE, TRUE);
        $node->setText(dynamicFooter());
        return $node;
    }
    else{
        return staticFooter();
    }
}