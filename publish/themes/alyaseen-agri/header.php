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
    $logoContainer->setClassName('pa-'.$page->getWritingDir().'-col-three');
    $logo = new HTMLNode('img', FALSE);
    if($page->getLang() == 'AR'){
        $logo->setAttribute('src', $page->getThemeImagesDir().'/company-logo-2-ar.jpg');
    }
    else{
        $logo->setAttribute('src', $page->getThemeImagesDir().'/company-logo-2-en.jpg');
    }
    $logo->setAttribute('style', 'height:100px');
    $logoContainer->addChild($logo);
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
    if($page->getLang() == 'AR'){
        $item1 = new ListItem();
        $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'our-products', 'منتجاتنا');
        $item1->addChild($link1);
        $ul->addChild($item1);
        $item1 = new ListItem();
        $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'news', 'أخبار الشركة');
        $item1->addChild($link1);
        $ul->addChild($item1);
        $item1 = new ListItem();
        $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'about', 'حول الشركة');
        $item1->addChild($link1);
        $ul->addChild($item1);
        $item1 = new ListItem();
        $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'contact-us', 'خدمة العملاء');
        $item1->addChild($link1);
        $ul->addChild($item1);
        $item1 = new ListItem();
        $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'careers', 'مركز التوظيف');
        $item1->addChild($link1);
        $ul->addChild($item1);
    }
    else{
        $item1 = new ListItem();
        $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'our-products', 'Our Products');
        $item1->addChild($link1);
        $ul->addChild($item1);
        $item1 = new ListItem();
        $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'news', 'Company News');
        $item1->addChild($link1);
        $ul->addChild($item1);
        $item1 = new ListItem();
        $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'about', 'About Us');
        $item1->addChild($link1);
        $ul->addChild($item1);
        $item1 = new ListItem();
        $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'contact-us', 'Customer Service');
        $item1->addChild($link1);
        $ul->addChild($item1);
        $item1 = new ListItem();
        $link1 = new LinkNode(SiteConfig::get()->getBaseURL().'careers', 'Careers');
        $item1->addChild($link1);
        $ul->addChild($item1);
    }
    return $headerSec;
}

