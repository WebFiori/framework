<?php
use webfiori\entity\Theme;
use phpStructs\html\HTMLNode;
use webfiori\entity\Page;
use phpStructs\html\HeadNode;
use jsonx\JsonX;
use phpStructs\html\JsCode;
use webfiori\entity\Util;
use webfiori\WebFiori;

/**
 * A generic template that can be used to create Vuetify based themes.
 *
 * @author Eng.Ibrahim
 */
class VuetifyTemplate extends Theme {
    
    public function __construct() {
        parent::__construct();
        $this->setName('Vuetify Template');
        $this->setAuthor('Ibrahim BinAlshikh');
        $this->setLicenseName('MIT');
        $this->setJsDirName('js');
        $this->setImagesDirName('img');
        $this->setBeforeLoaded(function()
        {
            Page::lang(WebFiori::getWebsiteController()->getSessionLang());
            Page::siteName(WebFiori::getSiteConfig()->getWebsiteNames()[Page::lang()]);
        });
        $this->setAfterLoaded(function()
        {
            $topDiv = new HTMLNode('v-app');
            $topDiv->setID('app');
            $headerSec = Page::document()->getChildByID('page-header');
            Page::document()->removeChild($headerSec);
            $bodySec = Page::document()->getChildByID('page-body');
            Page::document()->removeChild($bodySec);
            $footerSec = Page::document()->getChildByID('page-footer');
            Page::document()->removeChild($footerSec);
            $topDiv->addChild($footerSec)->addChild($headerSec)->addChild($bodySec);
            Page::document()->getBody()->addChild($topDiv);
            Page::document()->getChildByID('main-content-area')->setNodeName('v-main');
            Page::document()->getChildByID('main-content-area')->setAttribute('app');

            //initialize vue before the page is rendered.
            //the initialization process is performed by the file 
            //'themes/vuetify-based/init-vuetify.js'
            Page::beforeRender(function()
            {
                $jsNode = new HTMLNode('script');
                $jsNode->setAttribute('src', Page::jsDir().'/init-vuetify.js');
                Page::document()->getBody()->addChild($jsNode);
            });
        });
    }
    
    public function createHTMLNode($options = array()) {
        return new HTMLNode();
    }

    public function getAsideNode() {
        $aside = new HTMLNode('v-container');
        return $aside;
    }

    public function getFooterNode() {
        $footer = new HTMLNode('v-footer');
        return $footer;
    }

    public function getHeadNode() {
        $node = new HeadNode();
        $lang = Page::translation();
        $json = new JsonX();
        $langVars = $lang->getLanguageVars();

        foreach ($langVars as $key => $val) {
            $json->add($key, $val,['array-as-object' => true]);
        }
        $js = new JsCode();
        $js->setID('data-model');
        $js->addCode('window.locale = '.$json.';');
                
        $node->addChild($js)
        ->addCSS(Page::cssDir().'/theme.css')
        ->addCSS('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900',[], false)
        ->addCSS('https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css', [], false)
        ->addCSS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css', [], false)
        ->addJs('https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js', [], false)
        ->addJs('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js', [], false);
        
        return $node;
    }

    public function getHeadrNode() {
        $aside = new HTMLNode('v-toolbar');
        return $aside;
    }

}
return __NAMESPACE__;