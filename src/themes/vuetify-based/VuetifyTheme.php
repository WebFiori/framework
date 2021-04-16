<?php
namespace webfiori\theme\vutifyTheme;

use webfiori\json\Json;
use webfiori\ui\Anchor;
use webfiori\ui\HeadNode;
use webfiori\ui\HTMLNode;
use webfiori\ui\JsCode;
use webfiori\framework\Page;
use webfiori\framework\Theme;
use webfiori\framework\session\SessionsManager;

use webfiori\framework\ConfigController;
use webfiori\framework\WebFioriApp;
/**
 * A basic theme which is based on Vuetify framework.
 * @author Ibrahim
 */
class VuetifyTheme extends Theme {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct();
        $this->setVersion('1.0');
        $this->setAuthor('Ibrahim');
        $this->setLicenseName('MIT License');
        $this->setLicenseUrl('https://opensource.org/licenses/MIT');
        $this->setName('Vuetify Theme');
        $this->setJsDirName('js');
        $this->setImagesDirName('img');
        $this->setAfterLoaded(function(Theme $theme)
        {
            $page = $theme->getPage();
            $page->includeI18nLables(true);
            LangExt::extendLang($page->getTranslation());
            $topDiv = new HTMLNode('v-app');
            $topDiv->setID('app');
            $headerSec = $page->getChildByID('page-header');
            $page->getDocument()->removeChild($headerSec);
            $bodySec = $page->getChildByID('page-body');
            $page->getDocument()->removeChild($bodySec);
            $footerSec = $page->getChildByID('page-footer');
            $page->getDocument()->removeChild($footerSec);
            $topDiv->addChild($footerSec)->addChild($headerSec)->addChild($bodySec);
            $page->getDocument()->getBody()->addChild($topDiv);
            $page->getDocument()->getChildByID('main-content-area')->setNodeName('v-main');
            $page->getDocument()->getChildByID('main-content-area')->setAttribute('app');
            
        });
    }
    /**
     * Creates a generic html node.
     * The returned node will depends on the way the developer has implemented 
     * the method.
     * @param array $options An associative array that contains options.
     * @return HTMLNode
     */
    public function createHTMLNode($options = []) {
        $type = isset($options['type']) ? $options['type'] : 'div';

        if ($type == 'v-list-item') {
            $node = new HTMLNode('v-list-item');
            $icon = isset($options['icon']) ? $options['icon'] : null;

            if ($icon !== null) {
                $iconNode = new HTMLNode('v-list-item-icon');
                $iconNode->addTextNode('<v-icon>'.$icon.'</v-icon>', false);
                $node->addChild($iconNode);
            }
            $title = isset($options['title']) ? $options['title'] : null;

            if ($title !== null) {
                $titleNode = new HTMLNode('v-list-item-title');
                $titleNode->addTextNode($title, false);
                $node->addChild($titleNode);
            }

            return $node;
        } else if ($type == 'icon-button') {
            $btn = new HTMLNode('v-btn');
            $btn->setAttribute('icon');
            $vIcon = new HTMLNode('v-icon');
            $btn->addChild($vIcon);
            $icon = isset($options['icon']) ? $options['icon'] : 'mdi-information';
            $vIcon->addTextNode($icon);

            return $btn;
        }

        return new HTMLNode();
    }
    /**
     * Creates the drawer which appears when menu button is clicked.
     * @return HTMLNode
     */
    public function getAsideNode() {
        $node = new HTMLNode('v-navigation-drawer',[
            'v-model' => 'drawer',
            'absolute',':right' => '$vuetify.rtl',
            'temporary','fixed'
        ]);
        $node->addChild('v-img',[
            'src' => Page::imagesDir().'/side-nav.PNG',
            ':aspect-ratio' => "16/9"
        ], true)->addChild('v-row', [
            'class' => 'lightbox white--text pa-2 fill-height',
            'align' => 'end'
        ])->addChild('v-col')
        ->addChild('div',['class'=>'subheading'])->text('Programming Academia')
        ->getParent()->getParent()->getParent()->addChild('v-list', [
            'dense','nav'
        ])->addChild('v-list-item-group', [
            'active-class'=>'deep-purple--text text--accent-4'
            ])->addChild($this->createHTMLNode([
        'type' => 'v-list-item',
        'title' => Page::translation()->get('side-menu/home'),
        'icon' => 'mdi-home'
        ]))->addChild($this->createHTMLNode([
            'type' => 'v-list-item',
            'title' => Page::translation()->get('side-menu/search'),
            'icon' => 'mdi-magnify'
        ]))->addChild($this->createHTMLNode([
            'type' => 'v-list-item',
            'title' => Page::translation()->get('side-menu/account'),
            'icon' => 'mdi-heart'
        ]))->addChild($this->createHTMLNode([
            'type' => 'v-list-item',
            'title' => Page::translation()->get('side-menu/something-else'),
            'icon' => 'mdi-information'
        ]));

        return $node;
    }
    /**
     * Creates the footer of the page.
     * @return HTMLNode
     */
    public function getFooterNode() {
        $node = new HTMLNode();
        $app = new HTMLNode('v-footer');
        $app->setAttribute('app');
        $app->setAttributes(['dark','padless']);
        $node->addChild($app);

        $vCard = new HTMLNode('v-card');
        $app->addChild($vCard);
        $vCard->setAttributes([
            'class' => 'flex',
            'flat','tile'
        ]);
        $vCardTitle = new HTMLNode('v-card-title');
        $vCardTitle->setClassName('teal social-media');
        $vCard->addChild($vCardTitle);
        $vCardTitle->addTextNode('
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

        $copywriteNoticeNode = new HTMLNode('strong');
        $copywriteNoticeNode->addTextNode('All Rights Reserved');
        

        $poweredByNode = new HTMLNode('p');
        $poweredByNode->setClassName('footer-notice');
        $poweredByNode->addTextNode('Powered By: ');
        $frameworkLink = new Anchor('https://webfiori.com', 'WebFiori Framework', '_blank');
        $poweredByNode->addChild($frameworkLink);
        

        $vuetifyNode = new HTMLNode('p');
        $vuetifyNode->setClassName('footer-notice');
        $vuetifyNode->addTextNode('Theme Designed Using ');
        $vuetifyLink = new Anchor('https://vuetifyjs.com', 'Vuetify', '_blank');
        $vuetifyNode->addChild($vuetifyLink);
        
        $vCardText->addChild($copywriteNoticeNode, true)
                ->addChild($poweredByNode, true)
                ->addChild($vuetifyNode);

        $vCard->addChild($vCardText);

        return $node;
    }
    /**
     * Creates and returns the head node of the web page.
     * It simply loads all needed JavaScript, CSS and any other resources.
     * @return HeadNode
     */
    public function getHeadNode() {
        $node = new HeadNode();
        $lang = Page::translation();
        $json = new Json();
        $langVars = $lang->getLanguageVars();

        foreach ($langVars as $key => $val) {
            $json->add($key, $val,['array-as-object' => true]);
        }
        $node->addCSS('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900',[], false);
        $node->addCSS('https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css', [], false);
        $node->addCSS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css', [], false);
        $node->addJs('https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js', [], false);
        $node->addJs('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js', [], false);

        return $node;
    }
    /**
     * Creates the header section of the page and return it.
     * @return HTMLNode
     */
    public function getHeadrNode() { 
        $appBar = new HTMLNode('v-app-bar');
        $appBar->setAttributes([
            'color' => 'red',
            'src' => 'https://picsum.photos/1920/1080?random',
            'hide-on-scroll',
            'elevate-on-scroll',
            'fixed','app'
        ]);

        //Adds a small logo in the bar
        $logo = new HTMLNode('v-img');
        $logo->setAttributes([
            'src' => 'favicon.png',
            'max-height' => 45,
            'max-width' => 45
        ]);
        

        //Adds a gradiant
        

        //An icon to show and hide aside menu.
        $drawerIcon = new HTMLNode('v-app-bar-nav-icon');
        
        $drawerIcon->setAttribute('@click', 'drawer = true');

        //Adds a text to the bar that represents website name
        $titleNode = new HTMLNode('v-toolbar-title');
        $titleNode->addTextNode(Page::siteName());
        
        $appBar->addChild('template', [
            'v-slot:img' => "{ props }"
        ])->addChild('v-img',[
            'v-bind' => 'props',
            'gradient' => "to top right, rgba(19,84,122,.5), rgba(128,208,199,.8)"
        ])->getParent()->getParent()
        ->addChild($logo, true)
        ->addChild($drawerIcon, true)
        ->addChild($titleNode, true)
        ->addChild('v-spacer', true)

        //Add extra actions to the bar such as search
                ->addChild($this->createHTMLNode([
                    'type' => 'icon-button',
                    'icon' => 'mdi-magnify'
                ]), true)
                ->addChild($this->createHTMLNode([
                    'type' => 'icon-button'
                ]), true)
                ->addChild($this->createHTMLNode([
                    'type' => 'icon-button',
                    'icon' => 'mdi-heart'
                ]));
        
        $node = new HTMLNode();
        $node->addChild($appBar);

        return $node;
    }
}

return __NAMESPACE__;
