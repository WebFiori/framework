<?php

/**
 * Modify the content of this function to customize the top section of the 
 * page. The Top section usually contains main menu, logo, search and other 
 * components.
 * @return string The header as HTML string.
 */
function getHeaderNode(){
    $page = Page::get();
    $headerSec = new HTMLNode();
    $logoContainer = new HTMLNode();
    $logoContainer->setClassName('pa-'.$page->getWritingDir().'-col-four');
    $lang = WebsiteFunctions::get()->getMainSession()->getLang(TRUE);
    $p = new PNode();
    $p->addText('My Website Name', array('bold'=>TRUE));
    $logoContainer->addChild($p);
    $headerSec->addChild($logoContainer);
    //end of logo UI
    //starting of main menu items
    $menu = new HTMLNode('nav');
    $menu->setID('main-nav');
    $menu->setClassName('pa-'.$page->getWritingDir().'-col-nine');
    $ul = new UnorderedList();
    $ul->setClassName('pa-row');
    $ul->setAttribute('dir', $page->getWritingDir());
    $menu->addChild($ul);
    $headerSec->addChild($menu);
    $lang = $page->getLanguage();
    $item1 = new ListItem();
    $link1 = new LinkNode(SiteConfig::get()->getBaseURL(), 'Menu Item 1');
    $item1->addChild($link1);
    $ul->addChild($item1);
    $item1 = new ListItem();
    $link1 = new LinkNode(SiteConfig::get()->getBaseURL(), 'Menu Item 2');
    $item1->addChild($link1);
    $ul->addChild($item1);
    $item1 = new ListItem();
    $link1 = new LinkNode(SiteConfig::get()->getBaseURL(), 'Menu Item 3');
    $item1->addChild($link1);
    $ul->addChild($item1);
    $item1 = new ListItem();
    $link1 = new LinkNode(SiteConfig::get()->getBaseURL(), 'Menu Item 4');
    $item1->addChild($link1);
    $ul->addChild($item1);
    $link1 = new LinkNode(SiteConfig::get()->getBaseURL(), 'Menu Item 5');
    $item1 = new ListItem();
    $item1->addChild($link1);
    $ul->addChild($item1);
    return $headerSec;
}

