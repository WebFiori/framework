<?php
namespace themes\fioriTheme;

use webfiori\framework\Theme;
use WebFiori\UI\HeadNode;
use WebFiori\UI\HTMLNode;
class NewFTestTheme extends Theme {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('New Super Theme');
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
        $this->setBeforeLoaded(function (Theme $theme, string $txt)
        {
            $theme->setAfterLoaded(function (Theme $theme, string $txt2)
            {
                $div = $theme->getPage()->insert('div');
                $div->text($txt2);
                $div->setID('my-div');
            }, [$txt]);
        }, ['My Name Is Super Hero']);
    }
    public function createHTMLNode(array $options = []) : HTMLNode {
        $nodeType = isset($options['type']) ? $options['type'] : '';
        $elementId = isset($options['element-id']) ? $options['element-id'] : null;

        if ($nodeType == 'section') {
            $sec = new HTMLNode('section');
            $hLvl = isset($options['h-level']) ? $options['h-level'] : 3;
            $hLevelX = $hLvl > 0 && $hLvl < 7 ? $hLvl : 1;
            $h = new HTMLNode('h'.$hLevelX);
            $title = isset($options['title']) ? $options['title'] : 'Sec_Title';
            $h->addTextNode($title);
            $sec->addChild($h);

            if ($elementId !== null) {
                $h->setID($elementId);
            }

            return $sec;
        } else {
            if ($nodeType == 'col') {
                $node = new HTMLNode();
                $colSize = isset($options['size']) ? $options['size'] : null;

                if ($colSize >= 1 && $colSize <= 12) {
                    $node->setClassName('col-'.$colSize);
                } else {
                    $node->setClassName('col');
                }

                return $node;
            } else {
                if ($nodeType == 'vertical-nav-bar') {
                    $mainNav = new HTMLNode('nav');
                    $mainNav->setClassName('navbar navbar-expand-lg navbar-light p-0');
                    $navbarId = isset($options['id']) ? $options['id'] : 'nav'.substr(hash('sha256',date('Y-m-d H:i:s')), 0,10);
                    $button = new HTMLNode('button');
                    $button->setClassName('navbar-toggler');
                    $button->addTextNode('<span class="navbar-toggler-icon"></span>', false);
                    $button->setAttribute('data-toggle', 'collapse');
                    $button->setAttribute('data-target', '#'.$navbarId);
                    $button->setAttribute('type', 'button');
                    $button->setAttribute('aria-controls', ''.$navbarId);
                    $button->setAttribute('aria-expanded', 'false');
                    $mainNav->addChild($button);

                    $navItemsContainer = new HTMLNode();
                    $navItemsContainer->setID($navbarId);
                    $navItemsContainer->setClassName('collapse navbar-collapse');
                    $mainNav->addChild($navItemsContainer);

                    $mainLinksUl = new UnorderedList();
                    $mainLinksUl->setClassName('navbar-nav flex-column');
                    $listItems = isset($options['nav-links']) ? $options['nav-links'] : [];
                    $index = 0;

                    foreach ($listItems as $listItemArr) {
                        $linkLabel = isset($listItemArr['label']) ? $listItemArr['label'] : 'Item_Lbl';
                        $itemLink = isset($listItemArr['link']) ? $listItemArr['link'] : '#';
                        $isActive = isset($listItemArr['is-active']) && $listItemArr['is-active'] === true ? true : false;
                        $mainLinksUl->addListItem('<a href="'.$itemLink.'" class="nav-link p-0">'.$linkLabel.'</a>', false);

                        if ($isActive === true) {
                            $mainLinksUl->getChild($index)->setClassName('nav-item active');
                        } else {
                            $mainLinksUl->getChild($index)->setClassName('nav-item');
                        }
                        $index++;
                    }
                    $subLists = isset($options['sub-lists']) ? $options['sub-lists'] : [];

                    foreach ($subLists as $subList) {
                        $listTxt = isset($subList['label']) ? $subList['label'] : 'Sub_list';
                        $link = isset($subList['link']) ? $subList['link'] : null;
                        $isActive = isset($subList['is-active']) && $subList['is-active'] === true ? true : false;
                        $subListItems = isset($subList['list-items']) ? $subList['list-items']:[];
                        $li = new ListItem();
                        $li->setClassName('nav-item');
                        $liDiv = new HTMLNode();
                        $li->addChild($liDiv);
                        $liDiv->setClassName('btn-group dropright');
                        $textButton = new HTMLNode('button');
                        $textButton->setClassName('btn btn-secondary p-0');
                        $textButton->setAttribute('type', 'button');
                        $textButton->setStyle([
                    'background' => 'transparent',
                    'border' => '0px'
                ]);
                        $liDiv->addChild($textButton);

                        if ($link !== null) {
                            $textButton->addTextNode('<a href="'.$link.'">'.$listTxt.'</a>', false);
                        } else {
                            $textButton->addTextNode($listTxt);
                        }
                        $expandButton = new HTMLNode('button');
                        $expandButton->setClassName('btn btn-secondary dropdown-toggle dropdown-toggle-split');
                        $expandButton->setStyle([
                    'background' => 'transparent',
                    'border' => '0px'
                ]);
                        $expandButton->setAttributes([
                    'type' => 'button',
                    'data-toggle' => "dropdown",
                    'aria-haspopup' => "true",
                    'aria-expanded' => "false"
                ]);
                        $liDiv->addChild($expandButton);
                        $subItemsContainer = new HTMLNode();
                        $liDiv->addChild($subItemsContainer);
                        $subItemsContainer->setAttributes([
                    'class' => 'dropdown-menu',
                    'x-placement' => 'right-start',
                    'style' => 'position: absolute; transform: translate3d(159px, 0px, 0px); top: 0px; left: 0px; will-change: transform;',
                ]);
                        $index = 0;

                        foreach ($subListItems as $listItem) {
                            $linkLabel = isset($listItem['label']) ? $listItem['label'] : 'Item_Lbl';
                            $itemLink = isset($listItem['link']) ? $listItem['link'] : '#';
                            $isActive = isset($listItem['is-active']) && $listItem['is-active'] === true ? true : false;
                            $linkNode = new Anchor($itemLink, $linkLabel);
                            $linkNode->setClassName('dropdown-item');
                            $subItemsContainer->addChild($linkNode);

                            if ($isActive === true) {
                                $subItemsContainer->getChild($index)->setClassName('active');
                            }
                            $index++;
                        }
                        $mainLinksUl->addChild($li);
                    }
                    $navItemsContainer->addChild($mainLinksUl);

                    return $mainNav;
                } else {
                    if ($nodeType == 'container') {
                        $node = new HTMLNode();
                        $node->setClassName('container');

                        return $node;
                    } else {
                        if ($nodeType == 'row') {
                            $node = new HTMLNode();
                            $node->setClassName('row');

                            return $node;
                        } else {
                            if ($nodeType == 'page-title') {
                                $titleRow = $this->createHTMLNode(['type' => 'row']);
                                $h1 = new HTMLNode('h2');
                                $title = isset($options['title']) ? $options['title'] : Page::title();
                                $h1->addTextNode($title,false);
                                $h1->setClassName('page-title pb-2 mt-4 mb-2 border-bottom');
                                $titleRow->addChild($h1);

                                return $titleRow;
                            } else {
                                if ($nodeType == 'row') {
                                    $node = new HTMLNode();
                                    $node->setClassName('row');

                                    return $node;
                                }
                            }
                        }
                    }
                }
            }
        }

        return parent::createHTMLNode($options);
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

