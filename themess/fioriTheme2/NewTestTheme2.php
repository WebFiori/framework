<?php
namespace themes\fioriTheme2;

use webfiori\framework\Theme;
use WebFiori\UI\HeadNode;
use WebFiori\UI\HTMLNode;
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
        $this->setUrl('https://my-theme-side.com');
        $this->setBeforeLoaded(function (Theme $theme)
        {
            $theme->setDescription('This theme is in before loaded.');
        });
        $this->setAfterLoaded(function (Theme $theme)
        {
            $paragraph = $theme->getPage()->insert('p');
            $paragraph->text('Theme is loaded.');
            $paragraph->setID('theme-after-loaded-el');
        });
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
     * Returns an object of type HTMLNode that represents header section of the page.
     *
     * @return HTMLNode|null @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no header
     * section, the method might return null.
     */
    public function getHeaderNode() : HTMLNode {
        return new HeaderSection();
    }
    /**
     * Returns an object of type HeadNode that represents HTML &lt;head&gt; node.
     *
     * @return HeadNode
     */
    public function getHeadNode() : HeadNode {
        return new HeadSection();
    }
}

