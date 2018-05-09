<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * A class that represents the head tag of a HTML document.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1
 */
class HeadNode extends HTMLNode{
    /**
     * A node that represents the tag 'base'.
     * @var HTMLNode
     * @since 1.0 
     */
    private $baseNode;
    /**
     * The text node that will hold the title of the page.
     * @var HTMLNode
     * @since 1.0 
     */
    private $titleNode;
    /**
     * A linked list of all link tags that link to CSS files.
     * @var LinledList
     * @since 1.0 
     */
    private $cssNodes;
    /**
     * A linked list of all script tags that link to JS files.
     * @var LinledList
     * @since 1.0 
     */
    private $jsNodes;
    /**
     * A linked list of all meta tags.
     * @var LinledList
     * @since 1.0 
     */
    private $metaNodes;
    /**
     * The canonical URL of the page.
     * @var string
     * @since 1.0 
     */
    private $canonical;
    /**
     * A linked list that contains alternate URLs.
     * @var LinkedList
     * @since 1.0 
     */
    private $hrefLang;
    public function __construct() {
        parent::__construct('head', TRUE, FALSE);
        $this->cssNodes = new LinkedList();
        $this->jsNodes = new LinkedList();
        $this->metaNodes = new LinkedList();
        $this->hrefLang = new LinkedList();
        $this->setTitle('Default');
        $this->addMeta('viewport', 'width=device-width, initial-scale=1.0');
    }
    
    /**
     * Sets the value of the attribute 'href' for the 'base' tag.
     * @param string $url The value to set.
     * @since 1.0
     */
    public function setBase($url){
        if(gettype($url) == 'string' && strlen($url) != 0){
            $this->baseNode = new HTMLNode('base', FALSE, FALSE);
            $this->baseNode->setAttribute('href',$url);
        }
    }
    /**
     * Returns a node that represents the tag 'base'.
     * @return HTMLNode|NULL A node that represents the tag 'base'. If the 
     * base URL is not set, The function will return <b>NULL</b>.
     * @since 1.0
     */
    public function getBase(){
        return $this->baseNode;
    }

    /**
     * Sets the title of the document.
     * @param string $title The title to set.
     * @since 1.0
     */
    public function setTitle($title){
        if(gettype($title) == 'string'){
            $this->titleNode = new HTMLNode('', FALSE, TRUE);
            $this->titleNode->setText($title);
        }
    }
    /**
     * Returns a linked list of all link tags that link to a CSS file.
     * @return LinkedList A linked list of all link tags that link to a CSS file.
     * @since 1.0
     */
    public function getCSSNodes(){
        return $this->cssNodes;
    }
    /**
     * Returns a linked list of all script tags that link to a JS file.
     * @return LinkedList A linked list of all script tags that link to a JS file.
     * @since 1.0
     */
    public function getJSNodes(){
        return $this->jsNodes;
    }
    /**
     * Returns a linked list of all meta tags.
     * @return LinkedList A linked list of all meta tags.
     * @since 1.0
     */
    public function getMetaNodes(){
        return $this->metaNodes;
    }
    /**
     * Adds new meta tag.
     * @param string $name The value of the property 'name'.
     * @param string $content The value of the property 'content'.
     * @since 1.0
     */
    public function addMeta($name,$content){
        if(gettype($name) == 'string' && gettype($content) == 'string'){
            if(strlen($name) != 0 && strlen($content) != 0){
                $meta = new HTMLNode('meta', FALSE, FALSE);
                $meta->setAttribute('name', $name);
                $meta->setAttribute('content', $content);
                $this->metaNodes->add($meta);
            }
        }
    }
    /**
     * Adds new CSS source file.
     * @param string $href The link to the file.
     * @since 1.0
     */
    public function addCSS($href){
        if(gettype($href) == 'string' && strlen($href) != 0){
            $tag = new HTMLNode('link', FALSE, FALSE);
            $tag->setAttribute('rel','stylesheet');
            $tag->setAttribute('href', $href);
            $this->cssNodes->add($tag);
        }
    }
    /**
     * Adds new JavsScript source file.
     * @param string $loc The location of the file.
     * @since 1.0
     */
    public function addJs($loc){
        if(gettype($loc) == 'string' && strlen($loc) != 0){
            $tag = new HTMLNode('script', TRUE, FALSE);
            $tag->setAttribute('type','text/javascript');
            $tag->setAttribute('src', $loc);
            $this->jsNodes->add($tag);
        }
    }
    /**
     * Sets the canonical URL.
     * @param string $link The URL to set.
     * @since 1.0
     */
    public function setCanonical($link){
        if(gettype($link) == 'string' && strlen($link) != 0){
            $this->canonical = $link;
        }
    }
    /**
     * Returns the canonical URL if set.
     * @return string|NULL The canonical URL if set. If the URL is not set, 
     * the function will return <b>NULL</b>.
     * @since 1.0
     */
    public function getCanonical(){
        return $this->canonical;
    }
    /**
     * Adds new alternate tag to the header.
     * @param string $url The link to the alternate page.
     * @param string $lang The language of the page.
     * @since 1.0
     */
    public function addAlternate($url,$lang){
        if(gettype($url) == 'string' && gettype($lang) == 'string'){
            if(strlen($url) != 0 && strlen($lang) != 0){
                $node = new HTMLNode('link', FALSE, FALSE);
                $node->setAttribute('rel','alternate');
                $node->setAttribute('hreflang', $lang);
                $node->setAttribute('href', $url);
                $this->hrefLang->add($node);
            }
        }
    }
    /**
     * Adds new 'link' node.
     * @param string $rel The value of the attribute 'rel'.
     * @param string $href The value of the attribute 'href'.
     * @since 1.1
     */
    public function addLink($rel,$href){
        if(strlen($rel) != 0 && strlen($href) != 0){
            $node = new HTMLNode('link', FALSE, FALSE);
            $node->setAttribute('rel',$rel);
            $node->setAttribute('href', $href);
            $this->addChild($node);
        }
    }

    /**
     * Returns a linked list of all alternate nodes that was added to the header.
     * @return LinkedList
     * @since 1.0
     */
    public function getAlternates() {
        return $this->hrefLang;
    }
    
    /**
     * Returns a linked list of all child nodes.
     * @return LinkedList
     * @since 1.0
     */
    public function childNodes() {
        $chls = new LinkedList();
        if($this->getBase() != NULL){
            $chls->add($this->getBase());
        }
        if($this->getCanonical() != NULL){
            $can = new HTMLNode('link', FALSE, FALSE);
            $can->setAttribute('rel','canonical');
            $can->setAttribute('href', $this->getCanonical());
            $chls->add($can);
        }
        $metaCharset = new HTMLNode('meta', FALSE, FALSE);
        $metaCharset->setAttribute('charset','UTF-8');
        $chls->add($metaCharset);
        $tNode = new HTMLNode('title', TRUE, FALSE);
        $tNode->addChild($this->titleNode);
        $chls->add($tNode);
        
        $metaNodes = $this->getMetaNodes();
        for($x = 0 ; $x < $metaNodes->size(); $x++){
            $chls->add($metaNodes->get($x));
        }
        
        $cssNodes = $this->getCSSNodes();
        for($x = 0 ; $x < $cssNodes->size(); $x++){
            $chls->add($cssNodes->get($x));
        }
        
        $hrefLangNodes = $this->getAlternates();
        for($x = 0 ; $x < $hrefLangNodes->size(); $x++){
            $chls->add($hrefLangNodes->get($x));
        }
        
        $jsNodes = $this->getJSNodes();
        for($x = 0 ; $x < $jsNodes->size(); $x++){
            $chls->add($jsNodes->get($x));
        }
        $parentCh = parent::childNodes();
        for($x = 0 ; $x < $parentCh->size() ; $x++){
            $chls->add($parentCh->get($x));
        }
        return $chls;
    }
}
