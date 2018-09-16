<?php

/**
 * Modify the content of this function to customize the top section of the 
 * page. The Top section usually contains main menu, logo, search and other 
 * components.
 * @return string The header as HTML string.
 */
function getHeaderNode(){
    $headerSec = new HTMLNode();
    $logoContainer = new HTMLNode();
    $logoContainer->setClassName('pa-'.Page::dir().'-col-4');
    $langCode = WebsiteFunctions::get()->getMainSession()->getLang(TRUE);
    $p = new PNode();
    $siteNames = SiteConfig::get()->getWebsiteNames();
    if(isset($siteNames[$langCode])){
        $p->addText($siteNames[$langCode], array('bold'=>TRUE));
    }
    else{
        if(isset($_GET['language']) && isset($siteNames[$_GET['language']])){
            $p->addText($siteNames[$_GET['language']], array('bold'=>TRUE));
        }
        else{
            $p->addText('<SITE NAME>', array('bold'=>TRUE));
        }
    }
    $logoContainer->addChild($p);
    $headerSec->addChild($logoContainer);
    //end of logo UI
    //starting of main menu items
    $menu = new HTMLNode('nav');
    $menu->setID('main-nav');
    $menu->setClassName('pa-'.Page::dir().'-col-9');
    $ul = new UnorderedList();
    $ul->setID('main-menu');
    $ul->setClassName('pa-row');
    $ul->setAttribute('dir', Page::dir());
    $menu->addChild($ul);
    $headerSec->addChild($menu);
    
    return $headerSec;
}

