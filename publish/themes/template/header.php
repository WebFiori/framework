<?php

/**
 * Modify the content of this function to customize the top section of the 
 * page. The Top section usually contains main menu, logo, search and other 
 * components.
 * @return HTMLNode The header as 'HTMLNode' object.
 */
function getHeaderNode(){
    $headerSec = new HTMLNode();
    $headerBody = new HTMLNode();
    $headerBody->addTextNode('Header Sec');
    $headerSec->addChild($headerBody);
    return $headerSec;
}

