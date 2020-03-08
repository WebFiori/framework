<?php
namespace webfiori\theme\vutifyTheme;
use webfiori\entity\Theme;
use webfiori\entity\Page;
use phpStructs\html\HTMLNode;
use phpStructs\html\HeadNode;
use phpStructs\html\ListItem;
use phpStructs\html\JsCode;
/**
 * A basic theme which is based on Bootstrap CSS framework.
 * It loads all needed CSS and JS files which are needed to create a 
 * bootstrap theme.
 * @author Ibrahim
 */
class VuetifyTheme extends Theme{
    public function __construct() {
        parent::__construct();
        $this->setVersion('1.0');
        $this->setAuthor('Ibrahim');
        $this->setName('Vuetify Theme');
        $this->setDirectoryName('vuetify-based');
        $this->setJsDirName('js');
        $this->setAfterLoaded(function(){
            $topDiv = new HTMLNode('v-app');
            $topDiv->setID('app');
            $headerSec = Page::document()->getChildByID('page-header');
            Page::document()->removeChild($headerSec);
            $topDiv->addChild($headerSec);
            $bodySec = Page::document()->getChildByID('page-body');
            Page::document()->removeChild($bodySec);
            $topDiv->addChild($bodySec);
            $footerSec = Page::document()->getChildByID('page-footer');
            Page::document()->removeChild($footerSec);
            $topDiv->addChild($footerSec);
            Page::document()->getBody()->addChild($topDiv);
            Page::beforeRender(function(){
                $jsNode = new HTMLNode('script');
                $jsNode->setAttribute('src', Page::jsDir().'/init-vuetify.js');
                Page::document()->getBody()->addChild($jsNode);
            });
        });
    }
    /**
     * 
     * @param type $options
     * @return HTMLNode
     */
    public function createHTMLNode($options = array()){
        $type = isset($options['type']) ? $options['type'] : 'div';
        return new HTMLNode();
    }
    /**
     * 
     * @return HTMLNode
     */
    public function getAsideNode(){
        $node = new HTMLNode();
        $app = new HTMLNode();
        $node->addChild($app);
        return $node;
    }
    
    public function getFooterNode(){
        $node = new HTMLNode();
        $app = new HTMLNode('v-footer');
        $app->setAttribute('app');
        $app->setAttributes(['dark','padless']);
        $node->addChild($app);
        
        $vCard = new HTMLNode('v-card');
        $app->addChild($vCard);
        $vCard->setAttributes([
            'class'=>'flex',
            'flat','tile'
        ]);
        $vCardTitle = new HTMLNode('v-card-title');
        $vCard->addChild($vCardTitle);
        $vCardTitle->addTextNode('
          <strong class="subheading">Get connected with us on social networks!</strong>
          <v-spacer></v-spacer>
          <v-btn
            v-for="icon in icons"
            :key="icon"
            class="mx-4"
            dark
            icon
          >
            <v-icon size="24px">{{ icon }}</v-icon>
          </v-btn>', false);
        $vCardText = new HTMLNode('v-card-text');
        $vCardText->setClassName('py-2 white--text text-center');
        $vCardText->addTextNode('{{ new Date().getFullYear() }} — <strong>Vuetify</strong>', false);
        $vCard->addChild($vCardText);
        
        return $node;
    }

    public function getHeadNode(){
        $node = new HeadNode();
        $node->addCSS('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900',[], false);
        $node->addCSS('https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css', [], false);
        $node->addCSS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css', [], false);
        $node->addJs('https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js', [], false);
        $node->addJs('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js', [], false);
        
        //$node->addChild($appsJs);
        return $node;
    }

    public function getHeadrNode() {
        $node = new HTMLNode();
        $appBar = new HTMLNode('v-app-bar');
        $appBar->setAttribute('color', 'red');
        $node->addChild($appBar);
        return $node;
    }

}
return __NAMESPACE__;
