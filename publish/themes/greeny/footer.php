<?php
function getFooterNode(){
    $node = new HTMLNode('div');
    $node->setAttribute('class', 'pa-row');
    $fNode = new HTMLNode('footer');
    $fNode->setAttribute('dir', Page::get()->getWritingDir());
    $fNode->setAttribute('class','pa-'.Page::get()->getWritingDir().'-col-12 show-border');
    $fNode->setAttribute('itemtype','http://schema.org/WPFooter');
    $fNav = new HTMLNode('nav');
    $fNavUl = new HTMLNode('ul');
    $fNav->addChild($fNavUl);
    $fNode->addChild($fNav);
    $node->addChild($fNode);
    $div = new HTMLNode('div');
    $div->setAttribute('class', 'pa-ltr-col-twelve');
    $div->addTextNode('<b style="color:gray;font-size:8pt;">Powered By: <a href="https://github.com/usernane/webfiori" '
            . 'target="_blank">WebFiori Framework</a> v'.Config::get()->getVersion().' ('.Config::get()->getVersionType().')');
    $fNode->addChild($div);
    return $node;
}