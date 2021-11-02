<?php

use webfiori\framework\Theme;
use webfiori\ui\HeadNode;
use webfiori\ui\HTMLNode;

class TemplateTheme extends Theme {
    public function __construct() {
        parent::__construct();
        //the only code that you need in your main theme class.
        $this->setAuthor('Ibrahim Ali');
        $this->setAuthorUrl('http://ibrahim-2017.blogspot.com');
        $this->setName('Template Theme');
        $this->setVersion('1.0');
        $this->setDescription('Generic Theme Template.');
        $this->setImagesDirName('images');
        $this->setJsDirName('js');
        $this->setCssDirName('css');
        $this->setBeforeLoaded(function()
        {
            //the code in here will be executed before the theme is loaded.
            //You cannot change page layout here.
            //But you can use it to initialize any variables your theme is using
        });
        $this->setAfterLoaded(function()
        {
            //the code in here will be executed after the theme is loaded.
            //You can change page layout here.
        });
    }
    /**
     * Create your custom HTML nodes here.
     * @param type $options
     * @return HTMLNode
     */
    public function createHTMLNode($options = []) {
        $node = new HTMLNode();

        return $node;
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
        //Add head tag tags here as needed.
        //Note that you don't have to add CSS and JS files of the theme as 
        //They will be added automatically for you.

        return $headTag;
    }

    public function getHeaderNode() {
        $headerSec = new HTMLNode();
        $headerBody = new HTMLNode();
        $headerBody->addTextNode('Header Sec');
        $headerSec->addChild($headerBody);

        return $headerSec;
    }
}
