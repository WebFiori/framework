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
    $ul->setClassName('pa-row');
    $ul->setAttribute('dir', Page::dir());
    $menu->addChild($ul);
    $headerSec->addChild($menu);
    
    $item1 = new ListItem();
    $link1 = new LinkNode(SiteConfig::get()->getBaseURL(), 'Menu Item 1');
    $item1->addChild($link1);
    $ul->addChild($item1);
    
    $item2 = new ListItem();
    $link2 = new LinkNode(SiteConfig::get()->getBaseURL(), 'Menu Item 2');
    $item2->addChild($link2);
    $ul->addChild($item2);
    
    $item3 = new ListItem();
    $link3 = new LinkNode(SiteConfig::get()->getBaseURL(), 'Menu Item 3');
    $item3->addChild($link3);
    $ul->addChild($item3);
    
    $item4 = new ListItem();
    $link4 = new LinkNode(SiteConfig::get()->getBaseURL(), 'Menu Item 4');
    $item4->addChild($link4);
    $ul->addChild($item4);
    
    $link5 = new LinkNode(SiteConfig::get()->getBaseURL(), 'Menu Item 5');
    $item5 = new ListItem();
    $item5->addChild($link5);
    $ul->addChild($item5);
    return $headerSec;
}

