<?php
/**
 * This function is used to create the footer section of the page. The footer 
 * usually contains links to pages like 'about', 'privacy' or 'terms'. Also 
 * it can have a copyright notice.
 * @return HTMLNode The footer area of the page as 'HTMLNode' object.
 */
function getFooterNode(){
    $node = new HTMLNode('div');
    $fNode = new HTMLNode('footer');
    $fNode->addTextNode('Footer Section');
    $node->addChild($fNode);
    return $node;
}