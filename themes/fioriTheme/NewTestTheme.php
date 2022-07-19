<?php
namespace themes\fioriTheme;

use webfiori\framework\Theme;
use webfiori\ui\HeadNode;
use webfiori\ui\HTMLNode;
use themes\fioriTheme\AsideSection;
use themes\fioriTheme\FooterSection;
use themes\fioriTheme\HeadSection;
use themes\fioriTheme\HeaderSection;
class NewTestTheme extends Theme {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('New Theme');
        //TODO: Set the properties of your theme.
        //$this->setName('Super Theme');
        //$this->setVersion('1.0');
        //$this->setAuthor('Me');
        //$this->setDescription('My Super Cool Theme.');
        //$this->setAuthorUrl('https://me.com');
        //$this->setLicenseName('MIT');
        //$this->setLicenseUrl('https://opensource.org/licenses/MIT');
        //$this->setCssDirName('css');
        //$this->setJsDirName('js');
        //$this->setImagesDirName('images');
    }
    /**
     * Returns an object of type 'HTMLNode' that represents aside section of the page. 
     *
     * @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no aside
     * section, the method might return null.
     */
    public function getAsideNode() : HTMLNode {
        return new AsideSection();
    }
    /**
     * Returns an object of type 'HTMLNode' that represents footer section of the page.
     *
     * @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no footer
     * section, the method might return null.
     */
    public function getFooterNode() : HTMLNode {
        return new FooterSection();
    }
    /**
     * Returns an object of type HeadNode that represents HTML &lt;head&gt; node.
     *
     * @return HeadNode
     */
    public function getHeadNode() : HeadNode {
        return new HeadSection();
    }
    /**
     * Returns an object of type HTMLNode that represents header section of the page.
     *
     * @return HTMLNode|null @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no header
     * section, the method might return null.
     */
    public function getHeaderNode() : HTMLNode {
        return new HeaderSection();
    }
}
return __NAMESPACE__;
