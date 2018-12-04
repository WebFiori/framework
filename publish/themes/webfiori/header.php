<?php
use phpStructs\html\HTMLNode;
use webfiori\entity\Page;
use phpStructs\html\LinkNode;
use functions\WebsiteFunctions;
use phpStructs\html\PNode;
use phpStructs\html\UnorderedList;
/**
 * Modify the content of this function to customize the top section of the 
 * page. The Top section usually contains main menu, logo, search and other 
 * components.
 * @return string The header as HTML string.
 */
function getHeaderNode(){
    $headerSec = new HTMLNode();
    $logoContainer = new HTMLNode();
    $logoContainer->setID('inner-header');
    $logoContainer->setClassName('pa-'.Page::dir().'-col-11-nm-np');
    $img = new HTMLNode('img', FALSE);
    $img->setAttribute('src',Page::imagesDir().'/WebsiteIcon_1024x1024.png');
    $img->setClassName('pa-'.Page::dir().'-col-1-np-nm');
    $img->setID('logo');
    $img->setWritingDir(Page::dir());
    $link = new LinkNode(SiteConfig::get()->getHomePage(), '');
    $link->addChild($img);
    $headerSec->addChild($link);
    $langCode = WebsiteFunctions::get()->getSession()->getLang(TRUE);
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
    $logoContainer->addChild($menu);
    return $headerSec;
}

