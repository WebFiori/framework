<?php

/**
 * Modify the content of this function to customize the top section of the 
 * page. The Top section usually contains main menu, logo, search and other 
 * components.
 * @return string The header as HTML string.
 */
function getHeaderNode(){
    $page = Page::get();
    $headerSec = new HTMLNode();
    $headerSec->setClassName('pa-row');
    $headerBody = new HTMLNode();
    $headerBody->setClassName('pa-'.$page->getWritingDir().'-col-twelve show-border');
    $headerBody->setWritingDir($page->getWritingDir());
    $text = new HTMLNode('', '', TRUE);
    $text->setText('Header Section');
    $headerBody->addChild($text);
    $headerSec->addChild($headerBody);
    return $headerSec;
}

