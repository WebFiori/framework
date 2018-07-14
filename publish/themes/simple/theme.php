<?php
$theme = new Theme();
$theme->setAuthor('Ibrahim Ali');
$theme->setName('Alyaseen Simple By Ibrahim Ali');
$theme->setVersion('1.0');
$theme->setDescription('Just another simple theme.');
$theme->setDirectoryName('simple');
$theme->setImagesDirName('images');
$theme->setJsDirName('js');
$theme->setCssDirName('css');
$theme->addComponents(array(
    'head.php', 'header.php', 'footer.php',
    'aside.php','body.php'
));
$theme->setAfterLoaded(function(){
    $page = Page::get();
    $page->setLang(WebsiteFunctions::get()->getMainSession()->getLang(TRUE));
    $page->usingLanguage();
});

