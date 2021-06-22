<?php
namespace themes\newFiori;

use webfiori\framework\Theme;
use webfiori\framework\ui\WebPage;
use webfiori\ui\HTMLNode;
use webfiori\ui\HeadNode;
use webfiori\ui\Anchor;
use webfiori\ui\JsCode;

/**
 * The new WebFiori framework website theme.
 *
 * @author Ibrahim
 */
class NewFiori extends Theme {
    public function __construct() {
        parent::__construct('New Fiori');
        $this->setVersion('1.0');
        $this->setLicenseName('MIT');
        $this->setDescription('The new WebFiori framework website theme.');
        
        $this->setAfterLoaded(function (Theme $theme) {
            $page = $theme->getPage();
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
            $page->removeChild($header);
            $page->removeChild($body);
            $page->removeChild($footer);
            $page->getDocument()->addChild($appDiv);
            $page->getChildByID('main-content-area')->setClassName('container');
            $page->addBeforeRender(function (WebPage $page) {
                $page->getDocument()->getBody()->addChild('script', [
                    'type' => 'text/javascript',
                    'src' => 'assets/new-wf/default.js',
                    'id' => 'default-vue-init'
                ]);
                $page->getDocument()->getBody()->addChild('script', [
                    'src' => 'assets/js/prism.js',
                    'type' => 'text/javascript'
                ], false);
                $page->getDocument()->getBody()->addChild('script', [
                    'src' => 'assets/js/algolia.js',
                    'type' => 'text/javascript'
                ], false);
            });
        });
    }
    public function getAsideNode() {
        $page = $this->getPage();
        $right = $page->getWritingDir() == 'rtl' ? 'right' : '';
        $sideDrawer = new HTMLNode('v-navigation-drawer', [
            'v-model' => "drawer",
            'app', $right,
            'width' => '250px',
            'app', 'temporary',
        ]);
        $sideDrawer->addChild('v-divider');
        $itemsPanel = new HTMLNode('template');
        $sideDrawer->addChild($itemsPanel);
        $itemsPanel->addChild('v-expansion-panels', [], false)
        ->addChild($this->createDrawerMenuItem(
                $this->createButton([
                    'text', 'block', 
                    'href' => $this->getBaseURL().'/learn'
                    ], 'Learn', 'mdi-information-variant')), true)
        ->addChild($this->createDrawerMenuItem(
                $this->createButton([
                    'text', 'block', 
                    'href' => $this->getBaseURL().'/docs/webfiori'
                    ], 'API Reference', 'mdi-information-variant')), true)
        ->addChild($this->createDrawerMenuItem(
                $this->createButton([
                    'text', 'block', 
                    'href' => $this->getBaseURL().'/download'
                    ], 'Download', 'mdi-information-variant')), true)
        ->addChild($this->createDrawerMenuItem(
                $this->createButton([
                    'text', 'block', 
                    'href' => $this->getBaseURL().'/contribute'
                    ], 'Contribute', 'mdi-information-variant')), true)
        ;
        return $sideDrawer;
    }

    public function getFooterNode() {
        $page = $this->getPage();
        $footer = new HTMLNode('v-footer', [
            'padless',
        ]);
        $card = new HTMLNode('v-card', [
            'flat', 'tile', 'class' => 'flex text-center', 'dark']);
        $footer->addChild($card);
//        $card->addChild('v-card-text')
//                ->addChild($this->createButton([
//                    'text', 
//                    'fab', 
//                    'x-small',
//                    'target' => '_blank',
//                    'href' => 'https://www.linkedin.com/in/ibrahim-binalshikh/'], null, 'mdi-linkedin'), true)
//                ->addChild($this->createButton([
//                    'text', 
//                    'fab', 
//                    'x-small',
//                    'target' => '_blank',
//                    'href' => 'https://t.me/WarriorVx'], null, 'mdi-telegram'), true)
//                ->addChild($this->createButton([
//                    'text', 
//                    'fab', 
//                    'x-small',
//                    'target' => '_blank',
//                    'href' => 'https://github.com/usernane'], null, 'mdi-github'), true);
//        
        //
        $card->addChild('v-card-text')
        ->addChild('small')
        ->text($page->get('footer/built-with'))
        ->addChild(new Anchor('https://webfiori.com', $page->get('general/framework-name')), true)
        ->text(', ')
        ->addChild(new Anchor('https://vuejs.org', 'Vue'), true)
        ->text(' and ')
        ->addChild(new Anchor('https://vuetifyjs.com', 'Vuetify'), true);
        
        $card->addChild('v-divider', true)
        ->addChild('v-card-text', ['flat'])
        ->addChild('small')->text('All Rights Reserved'.'  © 2018 - '.date('Y'));
        return $footer;
    }

    public function getHeadNode() {
        $head = new HeadNode();
        $head->addCSS('assets/css/prism.css');
        $head->addCSS('assets/css/code-theme.css');
        
        //$head->addCSS('https://cdn.jsdelivr.net/npm/instantsearch.css@7/themes/algolia-min.css');
        $head->addJs("https://cdn.jsdelivr.net/npm/algoliasearch@4.5.1/dist/algoliasearch-lite.umd.js");
        //$head->addJs('https://cdn.jsdelivr.net/npm/instantsearch.js@4');
        
        $head->addJs('https://unpkg.com/vue@2.x.x');
        $head->addCSS('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900');
        $head->addCSS('https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css');
        $head->addCSS('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css');
        $head->addJs('https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js');
        $head->addJs('https://cdn.jsdelivr.net/gh/usernane/AJAXRequestJs@1.x.x/AJAXRequest.js',[
            'revision' => true
        ]);
        
        
        $head->addJs("https://www.googletagmanager.com/gtag/js?id=UA-91825602-2", ['async'=>''], false);
        $jsCode = new JsCode();
        $jsCode->addCode("window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-91825602-2');");
        $head->addChild($jsCode);
        return $head;
    }

    public function getHeadrNode() {
        $page = $this->getPage();
        
        $vAppBar = new HTMLNode('v-app-bar', [
            'app',
            'color' => '#d2ed9a',
            //'src' => $this->getBaseURL().'/assets/images/WFLogo512.png',
            //'hide-on-scroll',
            //'elevate-on-scroll',
            'fixed',
            'height' => '50px',
            'flat',
            'dense'
        ]);
        
        $vAppBar->addChild('v-app-bar-nav-icon', [
                    'class' => 'd-sm-flex d-md-none',
                    '@click' => "drawer = !drawer",
                ], true);
        //Add Title with logo
        $vAppBar->addChild('v-toolbar-title', [
                    'class' => 'd-none d-md-flex',
                    'style' => [
                        'min-width' => '250px'
                    ]
                ])
                ->addChild('v-row',[
                    'class' => 'd-none d-md-flex'
                ])
                ->addChild('v-col', [
                    'cols' => 12,
                    'md' => 4
                ])->addChild('img', [
                    'src' => 'assets/images/WFLogo512.png',
                    'style' => [
                        'width' => '45px'
                    ]
                ], true)->getParent()
                ->addChild('v-col', [
                    'cols' => 12,
                    'md' => 8,
                    'class' => 'align-center d-flex'
                ])
                ->addChild(new Anchor($this->getBaseURL(), 
                        $page->getWebsiteName()
                        ), [
                    'style' => [
                        'color' => 'black',
                        'text-decoration' => 'none',
                        'font-weight' => 'bold'
                    ],
                    'class' => 'site-name align-center'
                ]);
        $vAppBar->addChild('v-spacer');
        $navLinksContainer = new HTMLNode('v-container', [
            'class' => 'd-none d-md-flex'
        ]);
        $vAppBar->addChild($navLinksContainer);
        $navLinksContainer->addChild(
                self::createButton(['text', 
                    'href' => $this->getBaseURL().'/docs/webfiori'], 'API Reference'), true)
                ->addChild(self::createButton(['text', 'href' => $this->getBaseURL().'/learn'], 'Learn'), true)
                ->addChild(self::createButton(['text', 'href' => $this->getBaseURL().'/download'], 'Download'), true)
                ->addChild(self::createButton(['text', 'href' => $this->getBaseURL().'/contribute'], 'Contribute'), true)
                ->getParent()->addChild('v-spacer');
        
        $vAppBar->addChild($this->createTopSearchBar());
        $vAppBar->addChild('v-spacer');
        $homeImgContainer = $vAppBar->addChild('div', [
            'class' => 'd-sm-flex d-md-none align-center',
        ]);
        $homeImgContainer->addChild('a', [
            'href' => \webfiori\framework\WebFioriApp::getAppConfig()->getHomePage()
        ])->img([
            'src' => 'assets/images/WFLogo512.png',
            'style' => [
                'width' => '45px'
            ]
        ]);
        return $vAppBar;
    }
    private function createTopSearchBar() {
        $searchContainer = new HTMLNode('v-container', [
            'class' => 'd-flex align-center d-none d-md-flex'
        ]);
        
        $row = $searchContainer->addChild('v-row', [
            'no-gutters'
        ]);

        $vList = $row->addChild('v-col', [
            'cols' => 12,
            'no-gutters',
            
            ])->addChild('v-menu', [
            'relative','overflow',
            'v-model'=>"show_search_menu",
                'offset-y',
                'bottom'
        ])->addChild('template', [
            'v-slot:activator'=>"{ on, attrs }",
            
        ])
        ->addChild('v-text-field', [
            'outlined', 'prepend-inner-icon' => 'mdi-magnify',
            'dense', 'rounded', 'hide-details',
            'id' => 'search-box',
            'v-model' => 'search_val','@input' => 'search',
            'v-bind'=>"attrs",
            'v-on'=>"on"
        ], true)->getParent()
        ->addChild('v-list');
        
        $vList->addChild('v-subheader', [
            'style' => 'font-weight: bold;'
        ])
        ->text('Learn')
        ->getParent()
        ->addChild('v-list-item', [
            'v-for' => 'result in docs_search_results'
        ])->addChild('v-list-item-title', [], false)
        ->addChild('a', [
            ':href' => 'result.link',
            'style' => [
                'font-size' => '9pt',
                'font-weight' => 'bold'
            ]
        ])->addChild('span', [
            'v-if' => 'result.parent_page !== null'
        ])->text('{{result.parent_page}} > {{result.title}}')
        ->getParent()
        ->addChild('span', [
            'v-else' => 'result.parent_page'
        ])->text('{{result.title}}');
        
        $vList->addChild('v-subheader', [
            'style' => 'font-weight: bold;'
        ])
        ->text('Classes')
        ->getParent()
        ->addChild('v-list-item', [
            'v-for' => 'result in search_results'
        ])->addChild('v-list-item-title', [], false)
        ->addChild('a', [
            ':href' => 'result.link',
            'style' => [
                'font-size' => '9pt',
                'font-weight' => 'bold'
            ]
        ])->text('{{result.class_name}}')
        ->getParent()
                ->addChild('v-list-item-subtitle',[
                    'style' => [
                        'font-size' => '9pt'
                    ]
                ])
                ->text('{{result.summary}}');
        $vList->addChild('v-subheader', [
            'style' => 'font-weight: bold;'
        ])
        ->text('Methods')
        ->getParent()
        ->addChild('v-list-item', [
            'v-for' => 'result in methods_search_results'
        ])->addChild('v-list-item-title')
        ->addChild('a', [
            ':href' => 'result.link',
            'style' => [
                'font-size' => '9pt',
                'font-weight' => 'bold'
            ]
        ])->text('{{result.name}}')
        ->getParent()
                ->addChild('v-list-item-subtitle',[
                    'style' => [
                        'font-size' => '9pt'
                    ]
                ])
                ->text('{{result.summary}}')
        ->getParent()->getParent()->getParent()
        ->addChild('a', [
            'href' => 'https://www.algolia.com/',
            'target' => '_blank',
            'class' => 'd-flex',
            'style' => 'border-top: 1px solid;'
        ])
        ->img([
            'src' => 'assets/images/search-by-algolia-light-background.webp',
            'style'=> ['width' => '130px']
        ]);
        return $searchContainer;
    }
    public static function createButton($props = [], $text = null, $icon = null, $iconProps = []) {
        $btn = new HTMLNode('v-btn', $props);
        
        if ($text !== null) {
            $btn->text($text);
        }
        if ($icon !== null) {
            $btn->addChild('v-icon', $iconProps, false)->text($icon);
        }
        return $btn;
    }
    public function createDrawerMenuItem($listTitle) {
        $item = new HTMLNode('v-list-item');
        $last = $item->addChild('v-list-item-content')
             ->addChild('v-list-item-title');
        if ($listTitle instanceof HTMLNode) {
            $last->addChild($listTitle);
        } else {
            $last->text($listTitle);
        }
        return $item;
    }
}
return __NAMESPACE__;
