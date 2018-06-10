<?php

/**
 * Modify the content of this function to add custom head tags.
 * @return HeadNode Head tag as <b>HeadNode</b> object.
 */
function getHeadNode(){
    $page = Page::get();
    $headTag = new HeadNode();
    $headTag->setBase(SiteConfig::get()->getBaseURL());
    $headTag->addLink('icon', $page->getThemeImagesDir().'/favicon.png');
    $headTag->setCanonical(SiteConfig::get()->getBaseURL().$page->getCanonical());
    $headTag->addCSS($page->getThemeCSSDir().'/Grid.css');
    $headTag->addCSS($page->getThemeCSSDir().'/colors.css');
    $headTag->addCSS($page->getThemeCSSDir().'/theme-specific.css');
    $headTag->addJs('res/js/js-ajax-helper-1.0.0/AJAX.js');
    $headTag->addJs('res/js/APIs.js');
    $headTag->addMeta('robots', 'index, follow');
    return $headTag;
}

