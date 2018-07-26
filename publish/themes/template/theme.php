<?php
$theme = new Theme();
$theme->setAuthor('Ibrahim Ali');
$theme->setAuthorUrl('http://ibrahim-2017.blogspot.com');
$theme->setName('Template');
$theme->setVersion('1.0');
$theme->setDescription('Generic Theme Template.');
$theme->setDirectoryName('template');
$theme->setImagesDirName('images');
$theme->setJsDirName('js');
$theme->setCssDirName('css');
$theme->addComponents(array(
    'head.php', 'header.php', 'footer.php',
    'aside.php'
));
$theme->setAfterLoaded(function(){
    //the code in here will be executed after the theme is loaded.
    //You can change page layout here.
});

