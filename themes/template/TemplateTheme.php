<?php
use webfiori\entity\Theme;
use phpStructs\html\HTMLNode;
use phpStructs\html\HeadNode;
use webfiori\entity\Page;
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

    public function getAsideNode() {
        $menu = new HTMLNode('div');
        $menu->addTextNode('Aside');
        return $menu;
    }

    public function getFooterNode() {
        $node = new HTMLNode('div');
        $fNode = new HTMLNode('footer');
        $fNode->addTextNode('Footer Section');
        $node->addChild($fNode);
        return $node;
    }

    public function getHeadNode() {
        $headTag = new HeadNode();
        //always set base URL to correctly fetch resources
        $headTag->setBase(SiteConfig::get()->getBaseURL());
        $headTag->addCSS(Page::cssDir().'/cssFile.css');
        $headTag->addJs(Page::jsDir().'/jsFile.js');
        return $headTag;
    }

    public function getHeadrNode() {
        $headerSec = new HTMLNode();
        $headerBody = new HTMLNode();
        $headerBody->addTextNode('Header Sec');
        $headerSec->addChild($headerBody);
        return $headerSec;
    }

}

