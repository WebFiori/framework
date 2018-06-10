<?php
function getFooterNode(){
    $node = new HTMLNode('div');
    $node->setAttribute('class', 'pa-row');
    $fNode = new HTMLNode('footer');
    $fNode->setAttribute('dir', Page::get()->getWritingDir());
    $fNode->setAttribute('class','pa-'.Page::get()->getWritingDir().'-col-twelve show-border');
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
    else{
        $textNode = new HTMLNode('', FALSE, TRUE);
        $textNode->setText('Programming Academia, All Rights Reserved © 2018');
    }
    $div->addChild($textNode);
    $fNode->addChild($div);
    return $node;
}