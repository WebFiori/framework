<?php
$theme = new Theme();
$theme->setAuthor('Ibrahim Ali');
$theme->setAuthorUrl('http://ibrahim-2017.blogspot.com');
$theme->setName('Greeny By Ibrahim Ali');
$theme->setVersion('1.0');
$theme->setDescription('First theme ever made. A nice green colored elements That '
        . 'makes you thing about the nature.');
$theme->setDirectoryName('greeny');
$theme->setImagesDirName('images');
$theme->setJsDirName('js');
$theme->setCssDirName('css');
$theme->addComponents(array(
    'head.php', 'header.php', 'footer.php',
    'aside.php'
));
$theme->setAfterLoaded(function(){
    $page = Page::get();
    $page->setLang(WebsiteFunctions::get()->getMainSession()->getLang(TRUE));
    $page->usingLanguage();
    $page->getDocument()->getBody()->setClassName('pa-container');
    $page->getDocument()->getChildByID('page-body')->setClassName('pa-row');
    if($page->hasAside()){
        $page->getDocument()->getChildByID('side-content-area')->setClassName('pa-'.$page->getWritingDir().'-col-two');
        $page->getDocument()->getChildByID('main-content-area')->setClassName('pa-'.$page->getWritingDir().'-col-ten');
    }
    else{
        $page->getDocument()->getChildByID('main-content-area')->setClassName('pa-'.$page->getWritingDir().'-col-twelve');
    }
});

