<?php

use webfiori\framework\Page;
use webfiori\framework\Theme;
use webfiori\framework\ui\WebPage;
use webfiori\ui\HeadNode;
use webfiori\ui\HTMLNode;

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

        $this->setAfterLoaded(function(Theme $theme)
        {
            $page = $theme->getPage();
            $page->includeI18nLables(true);
            $page->addBeforeRender(function (WebPage $page)
            {
                $appDiv = new HTMLNode('div', [
                    'id' => 'app'
                ]);
                $vApp = new HTMLNode('v-app');
                $appDiv->addChild($vApp);
                $appDiv->addChild($appDiv);
                $body = $page->getChildByID('page-body');
                $body->setNodeName('v-main');

                $header = $page->getChildByID('page-header');
                $footer = $page->getChildByID('page-footer');
                $vApp->addChild($header);
                $vApp->addChild($body);
                $sideMenu = $body->getChildByID('side-content-area');
                $body->removeChild($sideMenu);
                $vApp->addChild($sideMenu);
                $vApp->addChild($footer);
                $page->getDocument()->removeChild($header);
                $page->getDocument()->removeChild($body);
                $page->getDocument()->removeChild($footer);
                $page->getDocument()->addChild($appDiv);
                $page->getDocument()->getChildByID('main-content-area')->setClassName('container');
            }, [$theme]);
            $page->addBeforeRender(function (WebPage $page)
            {
                $page->getDocument()->getBody()->addChild('script', [
                    'type' => 'text/javascript',
                    'src' => 'assets/vuetify-template/default.js',
                    'id' => 'default-vue-init'
                ]);
            });
        });
    }

    public function createHTMLNode($options = []) {
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

        $node->addCSS(Page::cssDir().'/theme.css')
        ->addCSS('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900',[], false)
        ->addCSS('https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css', [], false)
        ->addCSS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css', [], false)
        ->addJs('https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js', [], false)
        ->addJs('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js', [], false);

        return $node;
    }

    public function getHeaderNode() {
        $aside = new HTMLNode('v-toolbar');

        return $aside;
    }
}

return __NAMESPACE__;
