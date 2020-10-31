<?php
namespace webfiori\theme;

use webfiori\ui\HeadNode;
use webfiori\ui\HTMLNode;
use webfiori\ui\ListItem;
use webfiori\framework\Page;
use webfiori\entity\Theme;
/**
 * A basic theme which is based on Bootstrap CSS framework.
 * It loads all needed CSS and JS files which are needed to create a 
 * bootstrap theme.
 * @author Ibrahim
 */
class BootstrapTheme extends Theme {
    public function __construct() {
        parent::__construct();
        $this->setVersion('1.0');
        $this->setAuthor('Ibrahim');
        $this->setName('Bootstrap Theme');
        $this->setAfterLoaded(function()
        {
            Page::document()->getChildByID('page-body')->setClassName('row');
            Page::document()->getBody()->setClassName('container-fluid');
            Page::document()->getChildByID('main-content-area')->setClassName('col');
            $breadCrump = new HTMLNode('nav');
            $breadCrump->setClassName('col');
            Page::document()->getChildByID('main-content-area')->addChild($breadCrump);
            $breadCrump->setAttribute('aria-label', 'breadcrumb');
            $olBr = new HTMLNode('ol');
            $olBr->setClassName('breadcrumb');
            $breadCrump->addChild($olBr);
            $li = new ListItem();
            $li->setAttribute('aria-current', 'page');
            $li->addTextNode(Page::title());
            $li->setClassName('breadcrumb-item active');
            $olBr->addChild($li);
        });
    }
    /**
     * 
     * @param type $options
     * @return HTMLNode
     */
    public function createHTMLNode($options = []) {
        $type = isset($options['type']) ? $options['type'] : 'div';

        if ($type == 'row') {
            $node = new HTMLNode();
            $node->setClassName('row');

            return $node;
        } else if ($type == 'container') {
            $node = new HTMLNode();
            $node->setClassName('container');

            return $node;
        } else if ($type == 'container-f') {
            $node = new HTMLNode();
            $node->setClassName('container-fluid');

            return $node;
        } else {
            $node = new HTMLNode();

            return $node;
        }
    }
    /**
     * 
     * @return HTMLNode
     */
    public function getAsideNode() {
        $node = new HTMLNode();
        $node->setClassName('col-2');

        return $node;
    }

    public function getFooterNode() {
        $node = $this->createHTMLNode(['type' => 'row']);

        return $node;
    }

    public function getHeadNode() {
        $node = new HeadNode();
        $node->addCSS('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css', [
            'integrity' => 'sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T',
            'crossorigin' => 'anonymous'
        ], false);
        $node->addJs('https://code.jquery.com/jquery-3.3.1.slim.min.js', [
            'integrity' => 'sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo',
            'crossorigin' => 'anonymous'
        ], false);
        $node->addJs('https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js', [
            'integrity' => 'sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1',
            'crossorigin' => 'anonymous'
        ], false);
        $node->addJs('https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', [
            'integrity' => 'sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM',
            'crossorigin' => 'anonymous'
        ], false);

        return $node;
    }

    public function getHeadrNode() {
        $node = $this->createHTMLNode(['type' => 'row']);
        $topNav = HTMLNode::loadComponent($this->getDirecotry().'header.html', [
            // TODO: Supply values for placeholders.
        ]);
        $node->addChild($topNav);

        return $node;
    }
}

return __NAMESPACE__;
