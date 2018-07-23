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
    $headerBody->setClassName('pa-'.$page->getWritingDir().'-col-12 show-border');
    $headerBody->setWritingDir($page->getWritingDir());
    $headerBody->addTextNode('Header Sec');
    $headerSec->addChild($headerBody);
    return $headerSec;
}

