<?php

/**
 * Modify the content of this function to add custom head tags. In addition to that, 
 * load your CSS and JS theme files here. Also you can include external CSS or 
 * JS files.
 * @return HeadNode Head node as 'HeadNode' object.
 */
function getHeadNode(){
    $headTag = new HeadNode();
    $headTag->setBase(SiteConfig::get()->getBaseURL());
    return $headTag;
}

