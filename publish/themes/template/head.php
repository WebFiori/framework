<?php

/**
 * Modify the content of this function to add custom head tags. In addition to that, 
 * load your CSS and JS theme files here. Also you can include external CSS or 
 * JS files.
 * @return HeadNode Head node as 'HeadNode' object.
 */
function getHeadNode(){
    $headTag = new HeadNode();
    //always set base URL to correctly fetch resources
    $headTag->setBase(SiteConfig::get()->getBaseURL());
    $headTag->addCSS(Page::cssDir().'/cssFile.css');
    $headTag->addJs(Page::jsDir().'/jsFile.js');
    return $headTag;
}

