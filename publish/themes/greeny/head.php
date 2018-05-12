<?php

/**
 * Modify the content of this function to add custom head tags.
 * @return HeadNode Head tag as <b>HeadNode</b> object.
 */
function getHeadNode($canonical='',$title='',$description=''){
    $headTag = new HeadNode();
    $headTag->setTitle(Page::get()->getTitle().SiteConfig::get()->getTitleSep().SiteConfig::get()->getWebsiteName());
    $headTag->addMeta('description',Page::get()->getDescription());
    $headTag->setBase(SiteConfig::get()->getBaseURL());
    $headTag->addLink('icon', $GLOBALS['THEME_META']['images-directory'].'/favicon.png');
    if($canonical != '' || $canonical !== FALSE){
        $headTag->setCanonical(SiteConfig::get()->getBaseURL().$canonical);
    }
    $headTag->addCSS($GLOBALS['THEME_META']['css-directory'].'\programming-academia.css');
    $headTag->addJs('res/js/js-ajax-helper-1.0.0/AJAX.js');
    $headTag->addJs('res/js/APIs.js');
    $headTag->addMeta('robots', 'index, follow');
    return $headTag;
}

