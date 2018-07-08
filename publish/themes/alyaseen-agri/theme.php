<?php
$theme = new Theme();
$theme->setAuthor('Ibrahim Ali');
$theme->setName('Alyaseen Agri By Ibrahim Ali');
$theme->setVersion('1.0');
$theme->setDescription('Theme for the website of alyaseen agri co.');
$theme->setDirectoryName('alyaseen-agri');
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
});

