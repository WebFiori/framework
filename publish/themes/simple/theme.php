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
    'aside.php','GUI.php'
));
$theme->setAfterLoaded(function(){
    Page::lang(WebsiteFunctions::get()->getMainSession()->getLang(TRUE));
    Page::document()->getChildByID('main-content-area')->setClassName('pa-'.Page::dir().'-col-10');
    Page::document()->getChildByID('side-content-area')->setClassName('pa-'.Page::dir().'-col-2');
    Page::document()->getChildByID('page-body')->setClassName('pa-row');
    Page::document()->getChildByID('page-header')->setClassName('pa-row');
    Page::document()->getChildByID('page-footer')->setClassName('pa-row');
    SimpleGUI::createTitleNode();
});

