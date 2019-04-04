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
            'WebFioriGUI.php','LangExt.php'
        ));
        $this->setBeforeLoaded(function(){
            WebsiteFunctions::get()->useSession(array(
                'name'=>'lang-session',
                'create-new'=>true,
                'duration'=>60*24*7,
                'refresh'=>TRUE
            ));
            $session = WebsiteFunctions::get()->getSession();
            $lang = $session->getLang(TRUE);
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
            Page::lang($session->getLang(TRUE));
            Page::document()->getChildByID('main-content-area')->setClassName('pa-'.Page::dir().'-col-10');
            Page::document()->getChildByID('side-content-area')->setClassName('pa-'.Page::dir().'-col-2');
            Page::document()->getChildByID('page-body')->setClassName('pa-row');
            Page::document()->getChildByID('page-header')->setClassName('pa-row-np');
            Page::document()->getChildByID('page-footer')->setClassName('pa-row');
            //WebFioriGUI::createTitleNode();

            LangExt::extLang();
            $translation = &Page::translation();
            //adding menu items 
            $mainMenu = &Page::document()->getChildByID('main-menu');

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

        $facebookIcon = new HTMLNode('img', FALSE);
        $facebookIcon->setAttribute('src', $page->getThemeImagesDir().'/facebook.png');
        $facebookIcon->setClassName('social-media-icon');
        $facebookLink = new HTMLNode('a');
        $facebookLink->setAttribute('href', '');
        $facebookLink->setAttribute('target', '_blank');
        $facebookLink->addChild($facebookIcon);
        $socialMedia->addChild($facebookLink);

        $twtrIcon = new HTMLNode('img', FALSE);
        $twtrIcon->setAttribute('src', $page->getThemeImagesDir().'/tweeter.png');
        $twtrIcon->setClassName('social-media-icon');
        $twtrLink = new HTMLNode('a');
        $twtrLink->setAttribute('href', '');
        $twtrLink->setAttribute('target', '_blank');
        $twtrLink->addChild($twtrIcon);
        $socialMedia->addChild($twtrLink);

        $linkedinIcon = new HTMLNode('img', FALSE);
        $linkedinIcon->setAttribute('src', $page->getThemeImagesDir().'/linkedin.png');
        $linkedinIcon->setClassName('social-media-icon');
        $linkedinLink = new HTMLNode('a');
        $linkedinLink->setAttribute('href', '');
        $linkedinLink->setAttribute('target', '_blank');
        $linkedinLink->addChild($linkedinIcon);
        $socialMedia->addChild($linkedinLink);

        $snapIcon = new HTMLNode('img', FALSE);
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
        $p->addText('013 xxx xxxx', array('new-line'=>TRUE));
        $p->addText('youremail@example.com',array('new-line'=>TRUE));
        $contactInfo->addChild($p);
        $node->addChild($contactInfo);
        $p->addText('Your Copyright Notice © 2018');
        $div = new HTMLNode('div');
        $div->setAttribute('class', 'pa-ltr-col-twelve');
        $div->addTextNode('<b style="color:gray;font-size:8pt;">Powered By: <a href="https://github.com/usernane/webfiori" '
                . 'target="_blank">WebFiori Framework</a> v'.Config::getVersion().' ('.Config::getVersionType().')</b>');
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
        $img = new HTMLNode('img', FALSE);
        $img->setAttribute('src',Page::imagesDir().'/WebsiteIcon_1024x1024.png');
        $img->setClassName('pa-'.Page::dir().'-col-1-np-nm');
        $img->setID('logo');
        $img->setWritingDir(Page::dir());
        $link = new LinkNode(SiteConfig::getHomePage(), '');
        $link->addChild($img);
        $headerSec->addChild($link);
        $langCode = WebsiteFunctions::get()->getSession()->getLang(TRUE);
        $p = new PNode();
        $siteNames = SiteConfig::getWebsiteNames();
        if(isset($siteNames[$langCode])){
            $p->addText($siteNames[$langCode], array('bold'=>TRUE));
        }
        else{
            if(isset($_GET['language']) && isset($siteNames[$_GET['language']])){
                $p->addText($siteNames[$_GET['language']], array('bold'=>TRUE));
            }
            else{
                $p->addText('<SITE NAME>', array('bold'=>TRUE));
            }
        }
        $logoContainer->addChild($p);
        $headerSec->addChild($logoContainer);
        //end of logo UI
        //starting of main menu items
        $menu = new HTMLNode('nav');
        $menu->setID('main-nav');
        $menu->setClassName('pa-'.Page::dir().'-col-9');
        $ul = new UnorderedList();
        $ul->setID('main-menu');
        $ul->setClassName('pa-row');
        $ul->setAttribute('dir', Page::dir());
        $menu->addChild($ul);
        $logoContainer->addChild($menu);
        return $headerSec;
    }

    public function createHTMLNode($options = array()) {
        $node = new HTMLNode();
        return $node;
    }

}

