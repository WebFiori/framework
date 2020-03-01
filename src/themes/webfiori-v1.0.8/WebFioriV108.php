<?php
namespace webfiori\theme;
use webfiori\WebFiori;
use webfiori\entity\Theme;
use phpStructs\html\HTMLNode;
use phpStructs\html\HeadNode;
use phpStructs\html\Anchor;
use phpStructs\html\ListItem;
use phpStructs\html\UnorderedList;
use webfiori\entity\Page;
/**
 * WebFiori Theme Which is bundled with v1.0.8 of the framework.
 *
 * @author Ibrahim
 */
class WebFioriV108 extends Theme{
    public function __construct() {
        parent::__construct();
        $this->setVersion('1.0');
        $this->setAuthor('Ibrahim');
        $this->setName('WebFiori V108');
        $this->setLicenseName('MIT License');
        $this->setLicenseUrl('https://opensource.org/licenses/MIT');
        $this->setDirectoryName('webfiori-v1.0.8');
        $this->setAfterLoaded(function(){
            Page::document()->getChildByID('page-body')->setClassName('row  ml-0 mr-0');
            Page::document()->getChildByID('page-body')->setStyle([
                'margin-top'=>'50px'
            ]);
            Page::document()->getBody()->setStyle([
                'max-height'=>'10px',
                'height'=>'10px'
            ]);
            Page::document()->getChildByID('main-content-area')->setClassName('col-10 p-5');
        });
    }
    
    public function createHTMLNode($options = array()){
        $nodeType = isset($options['type']) ? $options['type'] : '';
        $elementId = isset($options['element-id']) ? $options['element-id'] : null;
        if($nodeType == 'section'){
            $sec = new HTMLNode('section');
            $hLvl = isset($options['h-level']) ? $options['h-level'] : 3;
            $hLevelX = $hLvl > 0 && $hLvl < 7 ? $hLvl : 1;
            $h = new HTMLNode('h'.$hLevelX);
            $title = isset($options['title']) ? $options['title'] : 'Sec_Title';
            $h->addTextNode($title);
            $sec->addChild($h);
            if($elementId !== null){
                $h->setID($elementId);
            }
            return $sec;
        }
        else if($nodeType == 'col'){
            $node = new HTMLNode();
            $colSize = isset($options['size']) ? $options['size'] : null;
            if($colSize >= 1 && $colSize <= 12){
                $node->setClassName('col-'.$colSize);
            }
            else{
                $node->setClassName('col');
            }
            return $node;
        }
        else if($nodeType == 'vertical-nav-bar'){
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
            foreach ($listItems as $listItemArr){
                $linkLabel = isset($listItemArr['label']) ? $listItemArr['label'] : 'Item_Lbl';
                $itemLink = isset($listItemArr['link']) ? $listItemArr['link'] : '#';
                $isActive = isset($listItemArr['is-active']) && $listItemArr['is-active'] === true ? true : false;
                $mainLinksUl->addListItem('<a href="'.$itemLink.'" class="nav-link p-0">'.$linkLabel.'</a>', false);
                if($isActive === true){
                    $mainLinksUl->getChild($index)->setClassName('nav-item active');
                }
                else{
                    $mainLinksUl->getChild($index)->setClassName('nav-item');
                }
                $index++;
            }
            $subLists = isset($options['sub-lists']) ? $options['sub-lists'] : [];
            foreach ($subLists as $subList){
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
                    'background'=>'transparent',
                    'border'=>'0px'
                ]);
                $liDiv->addChild($textButton);
                if($link !== null){
                    $textButton->addTextNode('<a href="'.$link.'">'.$listTxt.'</a>', false);
                }
                else{
                    $textButton->addTextNode($listTxt);
                }
                $expandButton = new HTMLNode('button');
                $expandButton->setClassName('btn btn-secondary dropdown-toggle dropdown-toggle-split');
                $expandButton->setStyle([
                    'background'=>'transparent',
                    'border'=>'0px'
                ]);
                $expandButton->setAttributes([
                    'type'=>'button',
                    'data-toggle'=>"dropdown",
                    'aria-haspopup'=>"true",
                    'aria-expanded'=>"false"
                ]);
                $liDiv->addChild($expandButton);
                $subItemsContainer = new HTMLNode();
                $liDiv->addChild($subItemsContainer);
                $subItemsContainer->setAttributes([
                    'class'=>'dropdown-menu',
                    'x-placement'=>'right-start',
                    'style'=>'position: absolute; transform: translate3d(159px, 0px, 0px); top: 0px; left: 0px; will-change: transform;',
                ]);
                $index = 0; 
                foreach ($subListItems as $listItem){
                    $linkLabel = isset($listItem['label']) ? $listItem['label'] : 'Item_Lbl';
                    $itemLink = isset($listItem['link']) ? $listItem['link'] : '#';
                    $isActive = isset($listItem['is-active']) && $listItem['is-active'] === true ? true : false;
                    $linkNode = new Anchor($itemLink, $linkLabel);
                    $linkNode->setClassName('dropdown-item');
                    $subItemsContainer->addChild($linkNode);
                    if($isActive === true){
                        $subItemsContainer->getChild($index)->setClassName('active');
                    }
                    $index++;
                }
                $mainLinksUl->addChild($li);
            }
            $navItemsContainer->addChild($mainLinksUl);
            return $mainNav;
        }
        else if($nodeType == 'container'){
            $node = new HTMLNode();
            $node->setClassName('container');
            return $node;
        }
        else if($nodeType == 'row'){
            $node = new HTMLNode();
            $node->setClassName('row');
            return $node;
        }
        else if($nodeType == 'page-title'){
            $titleRow = $this->createHTMLNode(['type'=>'row']);
            $h1 = new HTMLNode('h2');
            $title = isset($options['title']) ? $options['title'] : Page::title();
            $h1->addTextNode($title,false);
            $h1->setClassName('page-title pb-2 mt-4 mb-2 border-bottom');
            $titleRow->addChild($h1);
            return $titleRow;
        }
        else if($nodeType == 'row'){
            $node = new HTMLNode();
            $node->setClassName('row');
            return $node;
        }
        $node = new HTMLNode();
        return $node;
    }

    public function getAsideNode(){
        $aside = new HTMLNode();
        $aside->setClassName('col-2');
        return $aside;
    }

    public function getFooterNode(){
        $footer = new HTMLNode('footer');
        $footer->setClassName('bd-footer text-muted');
        $footer->setClassName('container-fluid p-md-4');
        $footerLinksUl = new UnorderedList();
        $footerLinksUl->setClassName('nav justify-content-center');
        $footerLinksUl->addListItems([
            '<a href="https://github.com/usernane/webfiori">GitHub</a>',
            '<a href="https://twitter.com/webfiori_" >Twitter</a>',
            '<a href="https://t.me/webfiori" >Telegram</a>'
        ], false);
        $footerLinksUl->getChild(0)->setClassName('nav-item');
        $footerLinksUl->getChild(1)->setClassName('nav-item ml-3');
        $footerLinksUl->getChild(2)->setClassName('nav-item ml-3');
        $footer->addChild($footerLinksUl);
        $powerdByNode = new HTMLNode('p');
        $powerdByNode->addTextNode('Powered by: <a href="https://programmingacademia.com/webfiori">WebFiori Framework</a> v'.WebFiori::getConfig()->getVersion().'. '
                . 'Code licensed under the <a href="https://opensource.org/licenses/MIT">MIT License</a>.', false);
        $footer->addChild($powerdByNode);
        $img = new HTMLNode('img');
        $img->setAttribute('src', Page::imagesDir().'/favicon.png');
        $img->setAttribute('alt', 'logo');
        $img->setStyle([
            'height'=>'25px'
        ]);
        $footer->addChild($img);
        $copywriteNotice = new HTMLNode('p');
        $copywriteNotice->addTextNode('All Rights Reserved © '.date('Y'));
        $footer->addChild($copywriteNotice);
        return $footer;
    }
    public function getHeadNode(){
        $head = new HeadNode();
        $head->addCSS(Page::cssDir().'/theme.css');
        $head->addCSS('https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css',false);
        $head->addJs('https://code.jquery.com/jquery-3.4.1.slim.min.js',false);
        $head->addJs('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js', false);
        $head->addJs('https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js',false);
        return $head;
    }

    public function getHeadrNode() {
        $header = new HTMLNode('header');
        $header->setClassName('container-fluid');
        $mainNav = new HTMLNode('nav');
        $header->addChild($mainNav);
        $mainNav->setClassName('navbar navbar-expand-lg navbar-light fixed-top');
        $mainNav->setStyle([
            'background-color'=>'#c1ec9b',
            'padding'=>'0'
        ]);
        $logo = new HTMLNode('img');
        $logo->setID('main-logo');
        $logo->setAttribute('src', Page::imagesDir().'/favicon.png');
        $logo->setAttribute('alt', 'logo');
        $logoLink = new Anchor(WebFiori::getSiteConfig()->getHomePage(), $logo->toHTML());
        $logoLink->setClassName('navbar-brand ml-3');
        $mainNav->addChild($logoLink);
        
        $button = new HTMLNode('button');
        $button->setClassName('navbar-toggler');
        $button->addTextNode('<span class="navbar-toggler-icon"></span>', false);
        $button->setAttribute('data-toggle', 'collapse');
        $button->setAttribute('data-target', '#navItemsContainer');
        $button->setAttribute('type', 'button');
        $button->setAttribute('aria-controls', 'navItemsContainer');
        $button->setAttribute('aria-expanded', 'false');
        $mainNav->addChild($button);
        
        $navItemsContainer = new HTMLNode();
        $navItemsContainer->setID('navItemsContainer');
        $navItemsContainer->setClassName('collapse navbar-collapse');
        $mainNav->addChild($navItemsContainer);
        
        $mainLinksUl = new UnorderedList();
        $mainLinksUl->setClassName('navbar-nav justify-content-center');
        $mainLinksUl->addListItems([
            '<a href="download" class="nav-link">Download</a>',
            '<a href="docs" class="nav-link">API Docs</a>',
            '<a href="learn" class="nav-link">Learn</a>',
            '<a href="contribute" class="nav-link">Contribute</a>'
        ], false);
        $mainLinksUl->getChild(0)->setClassName('nav-item');
        $mainLinksUl->getChild(1)->setClassName('nav-item');
        $mainLinksUl->getChild(2)->setClassName('nav-item');
        $mainLinksUl->getChild(3)->setClassName('nav-item');
        $navItemsContainer->addChild($mainLinksUl);
        
        return $header;
    }
}
return __NAMESPACE__;
