<?php
namespace webfiori\theme;

use webfiori\framework\Page;
use webfiori\framework\Theme;
use webfiori\framework\WebFioriApp;
use webfiori\ui\HeadNode;
use webfiori\ui\HTMLNode;
class GreenyTheme extends Theme {
    public function __construct() {
        parent::__construct();
        $this->setAuthor('Ibrahim Ali');
        $this->setAuthorUrl('http://ibrahim-2017.blogspot.com');
        $this->setName('Greeny By Ibrahim Ali');
        $this->setVersion('1.0');
        $this->setLicenseName('MIT License');
        $this->setLicenseUrl('https://opensource.org/licenses/MIT');
        $this->setDescription('First theme ever made. A nice green colored elements That '
                .'makes you thing about the nature. Use it as a template and a guide for creating '
                .'new themes.');
        $this->setImagesDirName('images');
        $this->setJsDirName('js');
        $this->setCssDirName('css');
        $this->setAfterLoaded(function(Theme $theme)
        {
            $page = $theme->getPage();
            $page->getDocument()->getBody()->setClassName('pa-container');
            $page->getDocument()->getChildByID('page-body')->setClassName('pa-row');

            if ($page->hasAside()) {
                $page->getDocument()->getChildByID('side-content-area')->setClassName('pa-'.Page::dir().'-col-2 show-border');
                $page->getDocument()->getChildByID('main-content-area')->setClassName('pa-'.Page::dir().'-col-10 show-border');
            } else {
                $page->getDocument()->getChildByID('main-content-area')->setClassName('pa-'.Page::dir().'-col-12 show-border');
            }
            $page->getDocument()->getChildByID('main-content-area')->addTextNode('Main Content Area.');
        });
        $this->setBeforeLoaded(function()
        {
        });
    }

    public function createHTMLNode($options = []) {
        $node = new HTMLNode();

        return $node;
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
        $fNode->setAttribute('dir', Page::dir());
        $fNode->setAttribute('class','pa-'.Page::dir().'-col-12 show-border');
        $fNode->setAttribute('itemtype','http://schema.org/WPFooter');
        $fNav = new HTMLNode('nav');
        $fNavUl = new HTMLNode('ul');
        $fNav->addChild($fNavUl);
        $fNode->addChild($fNav);
        $node->addChild($fNode);
        $div = new HTMLNode('div');
        $div->setAttribute('class', 'pa-ltr-col-twelve');
        $div->addTextNode('<b style="color:gray;font-size:8pt;">Powered By: <a href="https://github.com/usernane/webfiori" '
                .'target="_blank">WebFiori Framework</a> v'.WF_VERSION.' ('.WF_VERSION_TYPE.')</b>',false);
        $fNode->addChild($div);

        return $node;
    }

    public function getHeadNode() {
        $headTag = new HeadNode();
        $headTag->setBase(WebFioriApp::getAppConfig()->getBaseURL());
        $headTag->addLink('icon', Page::imagesDir().'/favicon.png');
        $headTag->setCanonical(WebFioriApp::getAppConfig()->getBaseURL().Page::canonical());

        $headTag->addMeta('robots', 'index, follow');

        return $headTag;
    }

    public function getHeadrNode() {
        $headerSec = new HTMLNode();
        $headerSec->setClassName('pa-row');
        $headerBody = new HTMLNode();
        $headerBody->setClassName('pa-'.Page::dir().'-col-12 show-border');
        $headerBody->setWritingDir(Page::dir());
        $headerBody->addTextNode('Header Sec');
        $headerSec->addChild($headerBody);

        return $headerSec;
    }
}

return __NAMESPACE__;
