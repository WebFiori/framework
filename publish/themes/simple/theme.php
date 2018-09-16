<?php
$theme = new Theme();
$theme->setAuthor('Ibrahim Ali');
$theme->setName('Simple By Ibrahim Ali');
$theme->setVersion('1.0');
$theme->setDescription('Just another simple theme.');
$theme->setDirectoryName('simple');
$theme->setImagesDirName('images');
$theme->setJsDirName('js');
$theme->setCssDirName('css');
$theme->addComponents(array(
    'head.php', 'header.php', 'footer.php',
    'aside.php','GUI.php','LangExt.php'
));
$theme->setAfterLoaded(function(){
    Page::lang(WebsiteFunctions::get()->getMainSession()->getLang(TRUE));
    Page::document()->getChildByID('main-content-area')->setClassName('pa-'.Page::dir().'-col-10');
    Page::document()->getChildByID('side-content-area')->setClassName('pa-'.Page::dir().'-col-2');
    Page::document()->getChildByID('page-body')->setClassName('pa-row');
    Page::document()->getChildByID('page-header')->setClassName('pa-row');
    Page::document()->getChildByID('page-footer')->setClassName('pa-row');
    SimpleGUI::createTitleNode();
    
    LangExt::extLang();
    $translation = &Page::translation();
    //adding menu items 
    $mainMenu = &Page::document()->getChildByID('main-menu');
    
    $item1 = new ListItem();
    $link1 = new LinkNode(SiteConfig::get()->getBaseURL(), $translation->get('menus/main-menu/menu-item-1'));
    $item1->addChild($link1);
    $mainMenu->addChild($item1);
    
    $item2 = new ListItem();
    $link2 = new LinkNode(SiteConfig::get()->getBaseURL(), $translation->get('menus/main-menu/menu-item-2'));
    $item2->addChild($link2);
    $mainMenu->addChild($item2);
    
    $item3 = new ListItem();
    $link3 = new LinkNode(SiteConfig::get()->getBaseURL(), $translation->get('menus/main-menu/menu-item-3'));
    $item3->addChild($link3);
    $mainMenu->addChild($item3);
    
});

