<?php
use webfiori\entity\Theme;
use webfiori\entity\Page;
use phpStructs\html\HTMLNode;
use phpStructs\html\HeadNode;
use functions\WebsiteFunctions;
class GreenyTheme extends Theme{
    public function __construct() {
        parent::__construct();
        $this->setAuthor('Ibrahim Ali');
        $this->setAuthorUrl('http://ibrahim-2017.blogspot.com');
        $this->setName('Greeny By Ibrahim Ali');
        $this->setVersion('1.0');
        $this->setLicenseName('MIT License');
        $this->setLicenseUrl('https://opensource.org/licenses/MIT');
        $this->setDescription('First theme ever made. A nice green colored elements That '
                . 'makes you thing about the nature. Use it as a template and a guide for creating '
                . 'new themes.');
        $this->setDirectoryName('greeny');
        $this->setImagesDirName('images');
        $this->setJsDirName('js');
        $this->setCssDirName('css');
        $this->addComponents(array(
            'UIFunctions.php'
        ));
        $this->setAfterLoaded(function(){
            Page::lang(WebsiteFunctions::get()->getSession()->getLang(TRUE));
            Page::translation();
            Page::document()->getBody()->setClassName('pa-container');
            Page::document()->getChildByID('page-body')->setClassName('pa-row');
            if(Page::aside()){
                Page::document()->getChildByID('side-content-area')->setClassName('pa-'.Page::dir().'-col-2 show-border');
                Page::document()->getChildByID('main-content-area')->setClassName('pa-'.Page::dir().'-col-10 show-border');
            }
            else{
                Page::document()->getChildByID('main-content-area')->setClassName('pa-'.Page::dir().'-col-12 show-border');
            }
            Page::document()->getChildByID('main-content-area')->addTextNode('Main Content Area.');
        });
    }
    public function getAsideNode() {
        $menu = new HTMLNode('div');
        $menu->addTextNode('Aside');
        return $menu;
    }

    public function getFooterNode() {
        $node = new HTMLNode('div');
        $node->setAttribute('class', 'pa-row');
        $fNode = new HTMLNode('footer');
        $fNode->setAttribute('dir', Page::get()->getWritingDir());
        $fNode->setAttribute('class','pa-'.Page::get()->getWritingDir().'-col-12 show-border');
        $fNode->setAttribute('itemtype','http://schema.org/WPFooter');
        $fNav = new HTMLNode('nav');
        $fNavUl = new HTMLNode('ul');
        $fNav->addChild($fNavUl);
        $fNode->addChild($fNav);
        $node->addChild($fNode);
        $div = new HTMLNode('div');
        $div->setAttribute('class', 'pa-ltr-col-twelve');
        $div->addTextNode('<b style="color:gray;font-size:8pt;">Powered By: <a href="https://github.com/usernane/webfiori" '
                . 'target="_blank">WebFiori Framework</a> v'.Config::get()->getVersion().' ('.Config::get()->getVersionType().')');
        $fNode->addChild($div);
        return $node;
    }

    public function getHeadNode() {
        $page = Page::get();
        $lang = WebsiteFunctions::get()->getSession()->getLang(TRUE);
        $page->setLang($lang);
        $headTag = new HeadNode();
        $headTag->setBase(SiteConfig::get()->getBaseURL());
        $headTag->addLink('icon', $page->getThemeImagesDir().'/favicon.png');
        $headTag->setCanonical(SiteConfig::get()->getBaseURL().$page->getCanonical());
        $page->setWebsiteName(SiteConfig::get()->getWebsiteNames()[$lang]);
        $headTag->addCSS($page->getThemeCSSDir().'/Grid.css');
        $headTag->addCSS($page->getThemeCSSDir().'/colors.css');
        $headTag->addCSS($page->getThemeCSSDir().'/theme-specific.css');
        $headTag->addJs('res/js/js-ajax-helper-1.0.0/AJAX.js');
        $headTag->addJs('res/js/APIs.js');
        $headTag->addMeta('robots', 'index, follow');
        return $headTag;
    }

    public function getHeadrNode() {
        $page = Page::get();
        $headerSec = new HTMLNode();
        $headerSec->setClassName('pa-row');
        $headerBody = new HTMLNode();
        $headerBody->setClassName('pa-'.$page->getWritingDir().'-col-12 show-border');
        $headerBody->setWritingDir($page->getWritingDir());
        $headerBody->addTextNode('Header Sec');
        $headerSec->addChild($headerBody);
        return $headerSec;
    }

}

