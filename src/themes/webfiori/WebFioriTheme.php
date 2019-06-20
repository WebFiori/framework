<?php
use webfiori\entity\Theme;
use webfiori\WebFiori;
use webfiori\entity\Page;
use webfiori\functions\WebsiteFunctions;
use phpStructs\html\ListItem;
use phpStructs\html\LinkNode;
use phpStructs\html\HeadNode;
use phpStructs\html\HTMLNode;
use phpStructs\html\PNode;
use phpStructs\html\UnorderedList;
use webfiori\conf\SiteConfig;
use webfiori\conf\Config;

class WebFioriTheme extends Theme{
    public function __construct() {
        parent::__construct();
        $this->setAuthor('Ibrahim Ali');
        $this->setName('WebFiori Theme');
        $this->setVersion('1.0');
        $this->setDescription('The main theme for WebFiori Framework.');
        $this->setDirectoryName('webfiori');
        $this->setImagesDirName('images');
        $this->setJsDirName('js');
        $this->setCssDirName('css');
        $this->addComponents(array(
            'LangExt.php'
        ));
        $this->setBeforeLoaded(function(){
            $session = WebsiteFunctions::get()->getSession();
            $lang = $session->getLang(true);
            Page::lang($lang);
            if($lang == 'AR'){
                Page::dir('rtl');
            }
            else{
                Page::dir('ltr');
            }
        });
        $this->setAfterLoaded(function(){
            $session = WebsiteFunctions::get()->getSession();
            Page::lang($session->getLang(true));
            Page::document()->getChildByID('main-content-area')->setClassName('pa-'.Page::dir().'-col-10');
            Page::document()->getChildByID('side-content-area')->setClassName('pa-'.Page::dir().'-col-2');
            Page::document()->getChildByID('page-body')->setClassName('pa-row');
            Page::document()->getChildByID('page-header')->setClassName('pa-row-np');
            Page::document()->getChildByID('page-footer')->setClassName('pa-row');
            //WebFioriGUI::createTitleNode();

            LangExt::extLang();
            $translation = &Page::translation();
            //adding menu items 
            $mainMenu = &Page::document()->getChildByID('menu-items-container');

            $item1 = new ListItem();
            $link1 = new LinkNode(SiteConfig::getBaseURL(), $translation->get('menus/main-menu/menu-item-1'));
            $item1->addChild($link1);
            $mainMenu->addChild($item1);

            $item2 = new ListItem();
            $link2 = new LinkNode(SiteConfig::getBaseURL(), $translation->get('menus/main-menu/menu-item-2'));
            $item2->addChild($link2);
            $mainMenu->addChild($item2);

            $item3 = new ListItem();
            $link3 = new LinkNode(SiteConfig::getBaseURL(), $translation->get('menus/main-menu/menu-item-3'));
            $item3->addChild($link3);
            $mainMenu->addChild($item3);

        });

    }
    public function getAsideNode() {
        $menu = new HTMLNode('div');
        return $menu;
    }

    public function getFooterNode() {
        $page = Page::get();
        $node = new HTMLNode('div');
        $socialMedia = new HTMLNode();
        $socialMedia->setClassName('pa-row');
        $socialMedia->setID('social-media-container');
        $socialMedia->setWritingDir($page->getWritingDir());

        $facebookIcon = new HTMLNode('img', false);
        $facebookIcon->setAttribute('src', $page->getThemeImagesDir().'/facebook.png');
        $facebookIcon->setClassName('social-media-icon');
        $facebookLink = new HTMLNode('a');
        $facebookLink->setAttribute('href', '');
        $facebookLink->setAttribute('target', '_blank');
        $facebookLink->addChild($facebookIcon);
        $socialMedia->addChild($facebookLink);

        $twtrIcon = new HTMLNode('img', false);
        $twtrIcon->setAttribute('src', $page->getThemeImagesDir().'/tweeter.png');
        $twtrIcon->setClassName('social-media-icon');
        $twtrLink = new HTMLNode('a');
        $twtrLink->setAttribute('href', '');
        $twtrLink->setAttribute('target', '_blank');
        $twtrLink->addChild($twtrIcon);
        $socialMedia->addChild($twtrLink);

        $linkedinIcon = new HTMLNode('img', false);
        $linkedinIcon->setAttribute('src', $page->getThemeImagesDir().'/linkedin.png');
        $linkedinIcon->setClassName('social-media-icon');
        $linkedinLink = new HTMLNode('a');
        $linkedinLink->setAttribute('href', '');
        $linkedinLink->setAttribute('target', '_blank');
        $linkedinLink->addChild($linkedinIcon);
        $socialMedia->addChild($linkedinLink);

        $snapIcon = new HTMLNode('img', false);
        $snapIcon->setAttribute('src', $page->getThemeImagesDir().'/snapchat.png');
        $snapIcon->setClassName('social-media-icon');
        $snapLink = new HTMLNode('a');
        $snapLink->setAttribute('href', '');
        $snapLink->setAttribute('target', '_blank');
        $snapLink->addChild($snapIcon);
        $socialMedia->addChild($snapLink);

        $node->addChild($socialMedia);
        $contactInfo = new HTMLNode();
        $contactInfo->setClassName('pa-'.Page::dir().'-col-12');
        $p = new PNode();
        $p->addText('013 xxx xxxx', array('new-line'=>true));
        $p->addText('youremail@example.com',array('new-line'=>true));
        $contactInfo->addChild($p);
        $node->addChild($contactInfo);
        $p->addText('Your Copyright Notice © 2018');
        $div = new HTMLNode('div');
        $div->setAttribute('class', 'pa-ltr-col-twelve');
        $div->addTextNode('<b style="color:gray;font-size:8pt;">Powered By: <a href="https://github.com/usernane/webfiori" '
                . 'target="_blank">WebFiori Framework</a> v'.Config::getVersion().' ('.Config::getVersionType().')</b>',false);
        $node->addChild($div);
        return $node;
    }

    public function getHeadNode() {
        $headTag = new HeadNode();
        $headTag->setBase(SiteConfig::getBaseURL());
        $headTag->addLink('icon', Page::imagesDir().'/favicon.png');
        $headTag->addCSS(Page::cssDir().'/Grid.css');
        $headTag->addCSS(Page::cssDir().'/colors.css');
        $headTag->addCSS(Page::cssDir().'/theme.css');
        $headTag->addMeta('robots', 'index, follow');
        return $headTag;
    }

    public function getHeadrNode() {
        $headerSec = new HTMLNode();
        $logoContainer = new HTMLNode();
        $logoContainer->setID('inner-header');
        $logoContainer->setClassName('pa-'.Page::dir().'-col-11-nm-np');
        $img = new HTMLNode('img', false);
        $img->setAttribute('src',Page::imagesDir().'/WebsiteIcon_1024x1024.png');
        $img->setClassName('pa-'.Page::dir().'-col-1-np-nm');
        $img->setID('logo');
        $img->setWritingDir(Page::dir());
        $link = new LinkNode(SiteConfig::getHomePage(), '');
        $link->addChild($img);
        $headerSec->addChild($link);
        $langCode = WebsiteFunctions::get()->getSession()->getLang(true);
        $p = new PNode();
        $siteNames = SiteConfig::getWebsiteNames();
        if(isset($siteNames[$langCode])){
            $p->addText($siteNames[$langCode], array('bold'=>true));
        }
        else{
            if(isset($_GET['language']) && isset($siteNames[$_GET['language']])){
                $p->addText($siteNames[$_GET['language']], array('bold'=>true));
            }
            else{
                $p->addText('<SITE NAME>', array('bold'=>true));
            }
        }
        $logoContainer->addChild($p);
        $headerSec->addChild($logoContainer);
        //end of logo UI
        //starting of main menu items
        $menu = new HTMLNode('nav');
        $menu->setID('main-navigation-menu');
        $menu->setClassName('pa-'.Page::dir().'-col-9-np');
        $ul = new UnorderedList();
        $ul->setID('menu-items-container');
        $ul->setClassName('pa-row-nm-np');
        $ul->setAttribute('dir', Page::dir());
        $menu->addChild($ul);
        $logoContainer->addChild($menu);
        return $headerSec;
    }
    /**
     * 
     * @param array $options An associative array of options. Available 
     * options are:
     * <ul>
     * <li>type: The type of the node that will be created. Supported 
     * types are: 
     * <ul>
     * <li>div (default)</li>
     * <li>wf-row</li>
     * <li>wf-col</li>
     * </ul>
     * </li>
     * </ul>
     * @return HTMLNode
     */
    public function createHTMLNode($options = array()) {
        $nodeType = isset($options['type']) ? $options['type'] : 'div';
        $withPadding = isset($options['with-padding']) ? $options['with-padding'] === true : true;
        $withMargin = isset($options['with-margin']) ? $options['with-margin'] === true : true;
        if($nodeType == 'div'){
            $node = new HTMLNode();
            return $node;
        }
        else if($nodeType == 'wf-row'){
            $wp = $withPadding === true ? '' : '-np';
            $wm = $withMargin === true ? '' : '-nm';
            $node = new HTMLNode();
            $node->setClassName('pa-row'.$wm.$wp);
            return $node;
        }
        else if($nodeType == 'wf-col'){
            $colSize = isset($options['size']) ? $options['size'] : 12;
            if($colSize > 12 || $colSize < 1){
                $colSize = 12;
            }
            $wp = $withPadding === true ? '' : '-np';
            $wm = $withMargin === true ? '' : '-nm';
            $node = new HTMLNode();
            $node->setClassName('pa-'.Page::get()->getWritingDir().'-col-'.$colSize.$wm.$wp);
            return $node;
        }
        else if($nodeType == 'page-title'){
            $titleRow = $this->createHTMLNode([
                'type'=>'wf-row'
            ]);
            $title = isset($options['title']) ? $options['title'] : Page::title();
            $h1 = new HTMLNode('h2');
            $h1->addTextNode($title);
            $h1->setClassName('pa-'.Page::dir().'-col-10-nm-np');
            $titleRow->addChild($h1);
        }
    }
}

