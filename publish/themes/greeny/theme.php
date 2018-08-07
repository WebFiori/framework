<?php
$theme = new Theme();
$theme->setAuthor('Ibrahim Ali');
$theme->setAuthorUrl('http://ibrahim-2017.blogspot.com');
$theme->setName('Greeny By Ibrahim Ali');
$theme->setVersion('1.0');
$theme->setLicenseName('MIT License');
$theme->setLicenseUrl('https://opensource.org/licenses/MIT');
$theme->setDescription('First theme ever made. A nice green colored elements That '
        . 'makes you thing about the nature. Use it as a template and a guide for creating '
        . 'new themes.');
$theme->setDirectoryName('greeny');
$theme->setImagesDirName('images');
$theme->setJsDirName('js');
$theme->setCssDirName('css');
$theme->addComponents(array(
    'head.php', 'header.php', 'footer.php',
    'aside.php', 'UIFunctions.php'
));
$theme->setAfterLoaded(function(){
    Page::lang(WebsiteFunctions::get()->getMainSession()->getLang(TRUE));
    Page::translation();
    Page::document()->getBody()->setClassName('pa-container');
    Page::document()->getChildByID('page-body')->setClassName('pa-row');
    if(Page::aside()){
        Page::document()->getChildByID('side-content-area')->setClassName('pa-'.Page::dir().'-col-2 show-border');
        Page::document()->getChildByID('main-content-area')->setClassName('pa-'.Page::dir().'-col-10 show-border');
    }
    else{
        Page::document()->getChildByID('main-content-area')->setClassName('pa-'.Page::dir().'-col-12 show-border');
    }
    Page::document()->getChildByID('main-content-area')->addTextNode('Main Content Area.');
});

