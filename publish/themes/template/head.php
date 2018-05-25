<?php

/**
 * Modify the content of this function to add custom head tags.
 * @return HeadNode Head tag as <b>HeadNode</b> object.
 */
function getHeadNode(){
    $headTag = new HeadNode();
    $headTag->setTitle(Page::get()->getTitle().SiteConfig::get()->getTitleSep().SiteConfig::get()->getWebsiteName());
    $headTag->setBase(SiteConfig::get()->getBaseURL());
    $headTag->addMeta('description', Page::get()->getDescription());
    return $headTag;
}

