<?php
use webfiori\entity\Theme;
class TemplateTheme extends Theme{
    public function __construct() {
        parent::__construct();
        //the only code that you need in your main theme class.
        $this->setAuthor('Ibrahim Ali');
        $this->setAuthorUrl('http://ibrahim-2017.blogspot.com');
        $this->setName('Template');
        $this->setVersion('1.0');
        $this->setDescription('Generic Theme Template.');
        $this->setDirectoryName('template');
        $this->setImagesDirName('images');
        $this->setJsDirName('js');
        $this->setCssDirName('css');
        $this->addComponents(array(
            'head.php', 'header.php', 'footer.php',
            'aside.php'
        ));
        $this->setBeforeLoaded(function(){
            //the code in here will be executed before the theme is loaded.
            //You cannot change page layout here.
            //But you can use it to initialize any variables your theme is using
        });
        $this->setAfterLoaded(function(){
            //the code in here will be executed after the theme is loaded.
            //You can change page layout here.
        });
    }
}

