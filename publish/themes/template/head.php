<?php

/**
 * Modify the content of this function to add custom head tags.
 * @return HeadNode Head tag as <b>HeadNode</b> object.
 */
function getHeadNode(){
    $page = Page::get();
    $headTag = new HeadNode();
    $headTag->setTitle($page->getTitle().SiteConfig::get()->getTitleSep().SiteConfig::get()->getWebsiteName());
    $headTag->addMeta('description',$page->getDescription());
    $headTag->setBase(SiteConfig::get()->getBaseURL());
    return $headTag;
}

