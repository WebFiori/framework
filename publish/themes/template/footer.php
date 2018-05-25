<?php
function getFooterNode(){
    $fNode = new HTMLNode('footer');
    $fNode->setID('page-footer');
    return $fNode;
}