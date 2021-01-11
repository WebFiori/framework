<?php
namespace ibrahim\themes;

use webfiori\framework\Theme;
use webfiori\ui\HeadNode;
use webfiori\ui\HTMLNode;
use webfiori\framework\Page;
use webfiori\framework\WebFiori;
use webfiori\framework\session\SessionsManager;
use webfiori\ui\Anchor;
use webfiori\ui\JsCode;
/**
 * A theme which is created to be used by the website https://ibrahim-binalshikh.me
 * 
 */
class IbrahimTheme extends Theme {

    public function __construct() {
        parent::__construct('Ibrahim Personal');
        $this->setDescription('A theme which was built for the website https://ibrahim-binalshikh.me '
                . 'using Vue, Vuetify and WebFiori framework.');
        $this->setLicenseName('MIT Licesnse');
        $this->setVersion('1.0');
        Page::siteName(WebFiori::getSiteConfig()->getWebsiteNames()[SessionsManager::getActiveSession()->getLangCode(true)]);
        Page::beforeRender(function ($theme) {
            
            $gta = $theme->getGta();
            if ($gta !== null) {
                Page::document()->getBody()->insert($gta, 0);
            }
            
            $appDiv = new HTMLNode('div', [
                'id' => 'app'
            ]);
            $vApp = new HTMLNode('v-app');
            $appDiv->addChild($vApp);
            $appDiv->addChild($appDiv);
            $body = Page::document()->getChildByID('page-body');
            $body->setNodeName('v-main');
            
            $header = Page::document()->getChildByID('page-header');
            $footer = Page::document()->getChildByID('page-footer');
            $vApp->addChild($header);
            $vApp->addChild($body);
            $sideMenu = $body->getChildByID('side-content-area');
            $body->removeChild($sideMenu);
            $vApp->addChild($sideMenu);
            $vApp->addChild($footer);
            Page::document()->removeChild($header);
            Page::document()->removeChild($body);
            Page::document()->removeChild($footer);
            Page::document()->addChild($appDiv);
            Page::document()->getChildByID('main-content-area')->setClassName('container');
        }, [$this]);
        Page::beforeRender(function() {
            Page::document()->getBody()->addChild('script', [
                'type' => 'text/javascript',
                'src' => 'assets/ibrahim/default.js',
                'id' => 'default-vue-init'
            ]);
        });
    }

    public function getGta() {
        if (defined('GTA')) {
            $node = new HTMLNode('noscript');
            $node->addChild('iframe', [
                'src' => "https://www.googletagmanager.com/ns.html?id=".GTA,
                'height' => "0",
                'width' => "0",
                'style' => "display:none;visibility:hidden"
            ]);
            return $node;
        }
    }


    public function getAsideNode() {
        if (Page::lang() == 'AR') {
            $right = 'right';
        } else {
            $right = '';
        }
        $sideDrawer = new HTMLNode('v-navigation-drawer', [
            'v-model' => "drawer",
            'app', $right,
            'width' => '250px',
            'app', 'temporary',
        ]);
        $sideDrawer->addChild($this->createAvatar());
        $sideDrawer->addChild('v-divider');
        $itemsPanel = new HTMLNode('template');
        $sideDrawer->addChild($itemsPanel);
        $itemsPanel->addChild('v-expansion-panels', [], false)
        ->addChild($this->createDrawerMenuItem($this->createButton(['text', 'block', 'href' => $this->getBaseURL().'/about-me'], Page::translation()->get('main-menu/about-me'), 'mdi-information-variant')))
                ->addChild($this->createDrawerMenuItem($this->createButton(['text', 'block', 'href' => $this->getBaseURL().'/contact-me'], Page::translation()->get('main-menu/contact-me'), 'mdi-comment-plus-outline')));
        return $sideDrawer;
    }
    public function createAvatar() {
        $vList = new HTMLNode('v-list');
        $vList->addChild('v-list-item', [], false)
                ->addChild('v-list-item-avatar', [], false)
                ->addChild('img', [
                    'src' => 'res/images/WFLogo512.png',
                    'alt' => 'A'
                ]);
        return $vList;
    }
    public function getFooterNode() {
        $footer = new HTMLNode('v-footer', [
            'padless'
        ]);
        $footer->addChild('v-snackbar', [
            'v-model' => 'snackbar.visible',
            ':color' => 'snackbar.color'
        ], false)->text('{{snackbar.text}}');
        $card = new HTMLNode('v-card', ['flat', 'tile', 'class' => 'flex', 'dark']);
        $footer->addChild($card);
        $card->addChild('v-card-text', [], false)
                ->addChild($this->createButton([
                    'text', 
                    'fab', 
                    'x-small',
                    'target' => '_blank',
                    'href' => 'https://www.linkedin.com/in/ibrahim-binalshikh/'], null, 'mdi-linkedin'))
                ->addChild($this->createButton([
                    'text', 
                    'fab', 
                    'x-small',
                    'target' => '_blank',
                    'href' => 'https://t.me/WarriorVx'], null, 'mdi-telegram'))
                ->addChild($this->createButton([
                    'text', 
                    'fab', 
                    'x-small',
                    'target' => '_blank',
                    'href' => 'https://github.com/usernane'], null, 'mdi-github'));
        
        //
        $card->addChild('v-card-text', [], false)
        ->addChild('small', [], false)
        ->text(Page::translation()->get('footer/built-with'))
         ->addChild(new Anchor('https://webfiori.com', Page::translation()->get('general/framework-name')));
        
        $card->addChild('v-divider')
        ->addChild('v-card-text', ['flat'], false)
        ->addChild('small', [], false)->text(Page::translation()->get('footer/all-rights').' '.date('Y'));
        return $footer;
    }

    public function getHeadNode() {
        $head = new HeadNode();
        $head->addJs('https://unpkg.com/vue@2.x.x');
        $head->addCSS('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900');
        $head->addCSS('https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css');
        $head->addCSS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css');
        $head->addJs('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js');
        $head->addJs('https://cdn.jsdelivr.net/gh/usernane/AJAXRequestJs@1.x.x/AJAXRequest.js',[
            'revision' => true
        ]);
        if (defined('RECAPTCHA_SITE_KEY')) {
            $head->addJs('https://www.google.com/recaptcha/api.js?render='.RECAPTCHA_SITE_KEY);
        }
        if (defined('GTA')) {
            $head->addJs('https://www.googletagmanager.com/gtag/js?id='.GTA, [
                'async'
            ]);
            $head->addChild('script', [
                'type' => 'text/javascript'
            ], false)->text("window.dataLayer = window.dataLayer || [];"
                    . "function gtag(){"
                    . "dataLayer.push(arguments);"
                    . "}"
                    . "gtag('js', new Date());"
                    . "gtag('config', '".GTA."');");
        }
        return $head;
    }

    public function getHeadrNode() {
        $vAppBar = new HTMLNode('v-app-bar', [
            'app',
            'color' => 'green',
            //'src' => $this->getBaseURL().'/assets/images/WFLogo512.png',
            'hide-on-scroll',
            'elevate-on-scroll',
            'fixed'
        ]);
        
        $vAppBar->addChild('v-app-bar-nav-icon', [
                    'class' => 'd-sm-flex d-md-none',
                    '@click' => "drawer = !drawer",
                ])->addChild('v-toolbar-title', [
                    'style' => [
                        'min-width' => '250px'
                    ]
                ], false)
                ->addChild(new Anchor($this->getBaseURL(), Page::siteName()), [
                    'style' => [
                        'color' => 'white',
                        'text-decoration' => 'none',
                        'font-weight' => 'bold'
                    ],
                    'class' => 'site-name'
                ])
                ->getParent()
                ->addChild(HTMLNode::createTextNode('<template v-slot:img="{ props }">
                <v-img
                  v-bind="props"
                  gradient="to top right, rgba(19,84,122,.5), rgba(128,208,199,.8)"
                ></v-img>
              </template>', false));
        $vAppBar->addChild('v-spacer');
        $navLinksContainer = new HTMLNode('v-container', [
            'class' => 'd-none d-md-flex'
        ]);
        $vAppBar->addChild($navLinksContainer);
        $navLinksContainer->addChild($this->createButton(['text', 'href' => $this->getBaseURL().'/about-me'], Page::translation()->get('main-menu/about-me'), 'mdi-information-variant'))
                ->addChild($this->createButton(['text', 'href' => $this->getBaseURL().'/contact-me'], Page::translation()->get('main-menu/contact-me'), 'mdi-comment-plus-outline'))
                ->getParent()->addChild('v-spacer');
        $this->createDarkSwitch($vAppBar);
        $this->createLangSwitch($vAppBar);
        return $vAppBar;
    }
    private function createLangSwitch($vAppBar) {
        $switchLangMenu = new HTMLNode('v-menu', [
            'bottom',
            'origin' => "center center",
            'transition' => "slide-y-transition",
            'open-on-hover'
        ]);
        $vAppBar->addChild($switchLangMenu);
        $switchLangMenu->addChild('template', [
            'v-slot:activator' => "{ on, attrs }"
        ], false)->addChild($this->createButton([
            'fab',
            'text',
            'v-bind' => "attrs",
            'v-on' => "on"
        ], null, 'mdi-earth'));
        $langsList = new HTMLNode('v-list');
        $switchLangMenu->addChild($langsList);
        $canonical = explode('?', Page::canonical())[0];
        foreach (Page::translation()->get('main-menu/lang-switch') as $langCode => $label) {
            $langsList->addChild('v-list-item', [], false)
                    ->addChild('v-list-item-title', [], false)
                    ->addChild(new Anchor($canonical.'?lang='.$langCode, $label));
        }
    }
    private function createDarkSwitch($vAppBar) {
        $vAppBar->addChild('v-icon', [
            'v-if' => '$vuetify.theme.dark',
            '@click' => '$vuetify.theme.dark = !$vuetify.theme.dark'
        ], false)->text('mdi-lightbulb-on');
        $vAppBar->addChild('v-icon', [
            'v-if' => '!$vuetify.theme.dark',
            '@click' => '$vuetify.theme.dark = !$vuetify.theme.dark'
        ], false)->text('mdi-lightbulb-outline');
    }
    private function createButton($props = [], $text = null, $icon = null, $iconProps = []) {
        $btn = new HTMLNode('v-btn', $props);
        
        if ($text !== null) {
            $btn->text($text);
        }
        if ($icon !== null) {
            $btn->addChild('v-icon', $iconProps, false)->text($icon);
        }
        return $btn;
    }
    private function createShareButton() {
        $dir = Page::dir() == 'ltr' ? 'right' : 'left';
        $btn = new HTMLNode('v-speed-dial', [
            'v-model' => "fab",
            'transition' => 'slide-y-reverse-transition',
            'buttom', $dir, 'bottom', 'fixed'
        ]);
        $btn->addChild('template ', [
            'v-slot:activator'
        ], false)->addChild('v-btn', [
            'fab', 'v-model' => "fab"
        ], false)->addChild('v-icon', [
            'v-if' => '!fab'
        ], false)->text('mdi-share-variant-outline')
        ->getParent()->addChild('v-icon', [
            'v-else'
        ], false)->text('mdi-close');
        $btn->addChild($this->createButton([
            'fab',
            'dark',
            'small',
            'color' => "green"
        ], null, 'mdi-pencil'));
        $btn->addChild($this->createButton([
            'fab',
            'dark',
            'small',
            'color' => "red"
        ], null, 'mdi-delete'));
        return $btn;
    }
    private function createDrawerMenuItem($listTitle) {
        $item = new HTMLNode('v-list-item');
        $last = $item->addChild('v-list-item-content', [], false)
             ->addChild('v-list-item-title', [], false);
        if ($listTitle instanceof HTMLNode) {
            $last->addChild($listTitle);
        } else {
            $last->text($listTitle);
        }
        return $item;
    }

    public function createHTMLNode($options = []) {
        $type = isset($options['name']) ? $options['name'] : 'div';
        if ($type == 'heading') {
            return $this->createHeading($options);
        } else if ($type == 'menu') {
            $text = isset($options['title']) ? $options['title'] : 'Menu';
            $attrs = [
                'bottom',
                'origin' => "center center",
                'transition' => "scale-transition",
                'color' => "transparent"
            ];
            return $this->createMenu($text, $attrs);
        } else if ($type == 'expansion-panel') {
            $panel = new HTMLNode('v-expansion-panel');
            $text = isset($options['heading']) ? $options['heading'] : 'Item';
            $panel->addChild('v-expansion-panel-header', [], false)->text($text);
            $panel->addChild('v-expansion-panel-content');
            return $panel;
        } else if ($type == 'title') {
            $title = isset($options['title']) ? $options['title'] : Page::title();
            return $this->createPageTitle($title);
        } else if ($type == 'select') {
            $select = new HTMLNode('v-autocomplete');
            $items = isset($options['items']) ? $options['items'] : null;
            if ($items !== null) {
                $select->setAttribute(':items', $items);
            }
            $label = isset($options['label']) ? $options['label'] : 'Select an option.';
            $select->setAttribute('label', $label);
            return $select;
        } else if ($type == 'share-button') {
            return $this->createShareButton();
        } else {
            return parent::createHTMLNode($options);
        }
    }
    private function createMenu($title, $attrs) {
        $menu = new HTMLNode('v-menu', $attrs);
        $menu->addChild('template', [
            'v-slot:activator' => "{ on, attrs }"
        ], false)->addChild('v-btn', [], false)
                ->text($title);
        return $menu;
    }
    private function createPageTitle($title) {
        $row = new HTMLNode('v-row', []);
        $row->addChild('v-col', [
            'cols' => 12
        ], false)->addChild('v-card', [], false)
                ->addChild('v-card-title', [], false)->text($title);
        return $row;
    }
    private function createHeading($options) {
        $headingLvl = $this->getHeadingLevel($options);
        $headingContainer = new HTMLNode('v-row');
        $heading = new HTMLNode('h'.$headingLvl);
        if (isset($options['id'])) {
            $heading->setID($options['id']);
        }
        if (isset($options['title'])) {
            if ($options['title'] instanceof HTMLNode) {
                $heading->addChild($options['title']);
            } else {
                $heading->text($options['title']);
            }
        } 
        $headingCol = new HTMLNode('v-col', [
            'cols' => 10
        ]);
        $headingContainer->addChild($headingCol);
        $headingCol->addChild($heading);
        return $headingContainer;
    }
    private function getHeadingLevel($options) {
        if (isset($options['level'])) {
            $asInt = intval($options['level']);
            if ($asInt > 0 && $asInt < 7) {
                return $asInt;
            }
        }
        return 1;
    }
}
return __NAMESPACE__;