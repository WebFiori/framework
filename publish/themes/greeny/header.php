<?php

/**
 * Modify the content of this function to customize the top section of the 
 * page. The Top section usually contains main menu, logo, search and other 
 * components.
 * @return string The header as HTML string.
 */
function getHeaderNode(){
    $headerSec = new HTMLNode();
    $headerSec->setClassName('pa-row');
    $text = new HTMLNode('', '', TRUE);
    $text->setText('Header Section');
    $headerSec->addChild($text);
    return $headerSec;
}

