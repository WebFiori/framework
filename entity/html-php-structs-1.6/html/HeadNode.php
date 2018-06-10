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
     * A linked list of all script tags that link to JS files.
     * @var LinledList
     * @since 1.0 
     */
    /**
     * The canonical URL of the page.
     * @var HTMLNode
     * @since 1.0 
     */
    private $canonical;
    /**
     * Creates new HTML node with name = 'head'.
     * @param string $title [Optional] The value to set for the node 'title'. Default 
     * is 'Default'. 
     * @param string $canonical [Optional] The value to set for the link node 
     * with attribute = 'canonical'. Default is empty string.
     * @param string $base [Optional] The value to set for the node 'base'. Default 
     * is empty string.
     * @since 1.0
     */
    public function __construct($title='Default',$canonical='',$base='') {
        parent::__construct('head', TRUE, FALSE);
        $this->setBase($base);
        $this->setTitle($title);
        $this->setCanonical($canonical);
        $this->addMeta('viewport', 'width=device-width, initial-scale=1.0');
    }
    
    /**
     * Sets the value of the attribute 'href' for the 'base' tag.
     * @param string $url The value to set.
     * @since 1.0
     */
    public function setBase($url){
        if(gettype($url) == 'string' && strlen($url) != 0){
            if($this->baseNode == NULL){
                $this->baseNode = new HTMLNode('base', FALSE, FALSE);
            }
            if(!$this->hasChild($this->baseNode)){
                $this->addChild($this->baseNode);
            }
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
     * Sets the text value of the node 'title'.
     * @param string $title The title to set.
     * @since 1.0
     */
    public function setTitle($title){
        if(gettype($title) == 'string'){
            if($this->titleNode == NULL){
                $this->titleNode = new HTMLNode('title');
                $this->titleNode->addChild(new HTMLNode('', '', TRUE));
            }
            if(!$this->hasChild($this->titleNode)){
                $this->addChild($this->titleNode);
            }
            $this->titleNode->children()->get(0)->setText($title);
        }
    }
    /**
     * Returns a linked list of all link tags that link to a CSS file.
     * @return LinkedList A linked list of all link tags that link to a CSS file. If 
     * the node has no CSS link tags, the function will return an empty list.
     * @since 1.0
     */
    public function getCSSNodes(){
        $children = $this->children();
        $chCount = $children->size();
        $list = new LinkedList();
        for($x = 0 ; $x < $chCount ; $x++){
            $child = $children->get($x);
            $childName = $child->getName();
            if($childName == 'link'){
                if($child->hasAttribut('rel') && $child->getAttributeValue('rel') == 'stylesheet'){
                    $list->add($child);
                }
            }
        }
        return $list;
    }
    /**
     * Returns a linked list of all script tags that link to a JS file.
     * @return LinkedList A linked list of all script tags with type = "text/javascript". 
     * If the node has no such nodes, the list will be empty.
     * @since 1.0
     */
    public function getJSNodes(){
        $children = $this->children();
        $chCount = $children->size();
        $list = new LinkedList();
        for($x = 0 ; $x < $chCount ; $x++){
            $child = $children->get($x);
            $childName = $child->getName();
            if($childName == 'script'){
                if($child->hasAttribut('type') && $child->getAttributeValue('type') == 'text/javascript'){
                    $list->add($child);
                }
            }
        }
        return $list;
    }
    /**
     * Returns a linked list of all meta tags.
     * @return LinkedList A linked list of all meta tags. If the node 
     * has no meta nodes, the list will be empty.
     * @since 1.0
     */
    public function getMetaNodes(){
        $children = $this->children();
        $chCount = $children->size();
        $list = new LinkedList();
        for($x = 0 ; $x < $chCount ; $x++){
            $child = $children->get($x);
            $childName = $child->getName();
            if($childName == 'meta'){
                $list->add($child);
            }
        }
        return $list;
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
                $this->addChild($meta);
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
            $this->addChild($tag);
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
            $this->addChild($tag);
        }
    }
    /**
     * Sets the canonical URL.
     * @param string $link The URL to set.
     * @since 1.0
     */
    public function setCanonical($link){
        if(gettype($link) == 'string' && strlen($link) != 0){
            if($this->canonical == NULL){
                $this->canonical = new HTMLNode('link',FALSE);
                $this->canonical->setAttribute('rel', 'canonical');
            }
            if(!$this->hasChild($this->canonical)){
                $this->addChild($this->canonical);
            }
            $this->canonical->setAttribute('href', $link);
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
                $this->addChild($node);
            }
        }
    }
    /**
     * Adds new 'link' node.
     * @param string $rel The value of the attribute 'rel'.
     * @param string $href The value of the attribute 'href'.
     * @param array $otherAttrs An associative array of keys and values. 
     * The keys will be used as an attribute and the key value will be used 
     * as attribute value.
     * @since 1.1
     */
    public function addLink($rel,$href,$otherAttrs=array()){
        if(strlen($rel) != 0 && strlen($href) != 0){
            $node = new HTMLNode('link', FALSE, FALSE);
            $node->setAttribute('rel',$rel);
            $node->setAttribute('href', $href);
            foreach ($otherAttrs as $key => $value) {
                $node->setAttribute($key, $value);
            }
            $this->addChild($node);
        }
    }
    /**
     * Returns a linked list of all alternate nodes that was added to the header.
     * @return LinkedList
     * @since 1.0
     */
    public function getAlternates() {
        $children = $this->children();
        $chCount = $children->size();
        $list = new LinkedList();
        for($x = 0 ; $x < $chCount ; $x++){
            $child = $children->get($x);
            $childName = $child->getName();
            if($childName == 'link'){
                if($child->hasAttribut('rel') && $child->getAttributeValue('rel') == 'alternate'){
                    $list->add($child);
                }
            }
        }
        return $list;
    }
}
