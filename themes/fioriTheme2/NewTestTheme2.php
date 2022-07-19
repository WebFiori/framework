<?php
namespace themes\fioriTheme2;

use webfiori\framework\Theme;
use webfiori\ui\HeadNode;
use webfiori\ui\HTMLNode;
use themes\fioriTheme2\AsideSection;
use themes\fioriTheme2\FooterSection;
use themes\fioriTheme2\HeadSection;
use themes\fioriTheme2\HeaderSection;
class NewTestTheme2 extends Theme {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('New Theme 2');
        //TODO: Set the properties of your theme.
        $this->setVersion('1.0');
        $this->setAuthor('Ibrahim Ali');
        $this->setDescription('Colofull Theme.');
        $this->setAuthorUrl('https://ibrahim-binalshikh.me');
        $this->setLicenseName('MIT');
        $this->setLicenseUrl('https://opensource.org/licenses/MIT');
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
