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
//    $logo = new HTMLNode('img', FALSE);
//    if($page->getLang() == 'AR'){
//        $logo->setAttribute('src', $page->getThemeImagesDir().'/company-logo-2-ar.jpg');
//    }
//    else{
//        $logo->setAttribute('src', $page->getThemeImagesDir().'/company-logo-2-en.jpg');
//    }
//    $logo->setAttribute('style', 'height:100px');
    $p = new PNode();
    $p->addText('شركة الياسين الزراعية', array('bold'=>TRUE));
    $p->setAttribute('style', 'font-size: 2.25rem;margin:0;font-family: \'Noto Kufi Arabic\', sans-serif; ');
    $logoContainer->addChild($p);
    $headerSec->addChild($logoContainer);
    //end of logo UI
    //
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
    $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'our-products', $lang->get('alyaseen-agri/main-nav/our-branches'));
    $item1->addChild($link1);
    $ul->addChild($item1);
    $item1 = new ListItem();
    $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'our-products', $lang->get('alyaseen-agri/main-nav/about-management'));
    $item1->addChild($link1);
    $ul->addChild($item1);
    $item1 = new ListItem();
    $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'branches', $lang->get('alyaseen-agri/main-nav/branches'));
    $item1->addChild($link1);
    $ul->addChild($item1);
    $item1 = new ListItem();
    $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'contact-us', $lang->get('alyaseen-agri/main-nav/contact-us'));
    $item1->addChild($link1);
    $ul->addChild($item1);
    $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'suppliers', $lang->get('alyaseen-agri/main-nav/suppliers'));
    $item1 = new ListItem();
    $item1->addChild($link1);
    $ul->addChild($item1);
    return $headerSec;
}

