<?php
namespace webfiori\theme\vutifyTheme;
use webfiori\WebFiori;
use webfiori\entity\Theme;
use webfiori\entity\Page;
use webfiori\entity\langs\Language;
use phpStructs\html\HTMLNode;
use phpStructs\html\HeadNode;
use phpStructs\html\ListItem;
use phpStructs\html\JsCode;
use webfiori\entity\router\Router;
use jsonx\JsonX;
use webfiori\theme\vutifyTheme\LangExt;
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
        $this->setBeforeLoaded(function(){
            Page::lang(WebFiori::getWebsiteController()->getSessionLang());
            LangExt::extendLang(Page::translation());
            Page::siteName(WebFiori::getSiteConfig()->getWebsiteNames()[Page::lang()]);
        });
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
            Page::document()->getChildByID('main-content-area')->setNodeName('v-content');
            Page::document()->getChildByID('main-content-area')->setAttribute('app');
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
        $node = new HTMLNode('v-navigation-drawer');
        $node->setAttributes([
            'v-model'=>'drawer',
            'absolute',':right'=>'$vuetify.rtl',
            'temporary']);
        $node->addTextNode('<v-list
          nav
          dense
        >
          <v-list-item-group
            v-model="group"
            active-class="deep-purple--text text--accent-4"
          >
            <v-list-item>
              <v-list-item-icon>
                <v-icon>mdi-home</v-icon>
              </v-list-item-icon>
              <v-list-item-title>Home</v-list-item-title>
            </v-list-item>
  
            <v-list-item>
              <v-list-item-icon>
                <v-icon>mdi-account</v-icon>
              </v-list-item-icon>
              <v-list-item-title>Account</v-list-item-title>
            </v-list-item>
  
          </v-list-item-group>
        </v-list>', false);
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
          <strong class="subheading">'.Page::translation()->get('example/footer/get-connected').'</strong>
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
        $vCardText->addTextNode('<strong>'.Page::translation()->get('example/footer/copyright-notice').'</strong>', false);
        $vCard->addChild($vCardText);
        
        return $node;
    }

    public function getHeadNode(){
        $node = new HeadNode();
        $lang = Page::translation();
        $json = new JsonX();
        $langVars = $lang->getLanguageVars();
        foreach ($langVars as $key => $val){
            $json->add($key, $val,['array-as-object'=>true]);
        }
        $js = new JsCode();
        $js->addCode('window.locale = '.$json.';');
        $node->addChild($js);
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
        //$appBar->setAttribute('app');
        $appBar->addTextNode('<template v-slot:img="{ props }">
          <v-img
            v-bind="props"
            gradient="to top right, rgba(19,84,122,.5), rgba(128,208,199,.8)"
          ></v-img>
        </template>', false);
        $drawerIcon = new HTMLNode('v-app-bar-nav-icon');
        $appBar->addChild($drawerIcon);
        $drawerIcon->setAttribute('@click', 'drawer = true');
        $appBar->setAttributes([
            'color'=>'red',
            'src'=>'https://picsum.photos/1920/1080?random',
            //'absolute'
            'hide-on-scroll'
            ]);
        $titleNode = new HTMLNode('v-toolbar-title');
        $titleNode->addTextNode(Page::siteName());
        $appBar->addChild($titleNode);
        $node->addChild($appBar);
        return $node;
    }

}
return __NAMESPACE__;
