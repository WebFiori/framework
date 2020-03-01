<?php
/*
 * The MIT License
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh, phpStructs.
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
namespace phpStructs\html;
use phpStructs\html\HTMLNode;
use phpStructs\LinkedList;
/**
 * A class that represents the tag &lt;head&lt; of a HTML document.
 *
 * @author Ibrahim
 * @version 1.1.5
 */
class HeadNode extends HTMLNode{
    /**
     * An array that contains the names of allowed child tags.
     * The array has the following values:
     * <ul>
     * <li>base</li>
     * <li>title</li>
     * <li>meta</li>
     * <li>link</li>
     * <li>script</li>
     * <li>noscript</li>
     * <li>#COMMENT</li>
     * <li>style</li>
     * </ul>
     * @since 1.1.4
     */
    const ALLOWED_CHILDREN = [
        'base','title','meta','link','script','noscript','#COMMENT', 
        'style'
    ];
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
     * A meta note that contains the attribute 'charset' of the document.
     * @var HTMLNode
     * @since 1.1.4 
     */
    private $metaCharset;
    /**
     * The canonical URL of the page.
     * @var HTMLNode
     * @since 1.0 
     */
    private $canonical;
    /**
     * Creates new HTML node that represents head tag of HTML document.
     * Note that by default, the node will have the following nodes in 
     * its body:
     * <ul>
     * <li>A meta tag with "name"="viewport" and "content"="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"</li>
     * <li>A title tag.</li>
     * </ul>
     * @param string $title The value to set for the node 'title'. Default 
     * is 'Default'. 
     * @param string $canonical The value to set for the link node 
     * with attribute = 'canonical'. Default is empty string.
     * @param string $base The value to set for the node 'base'. Default 
     * is empty string.
     * @since 1.0
     */
    public function __construct($title='Default',$canonical='',$base='') {
        parent::__construct('head');
        if(!$this->setBase($base)){
            $this->baseNode = new HTMLNode('base');
        }
        if(!$this->setTitle($title)){
            $this->titleNode = new HTMLNode('title');
            $this->titleNode->addTextNode('');
        }
        if(!$this->setCanonical($canonical)){
            $this->canonical = new HTMLNode('link');
            $this->canonical->setAttribute('rel', 'canonical');
        }
        $this->metaCharset = new HTMLNode('meta');
        $this->addMeta('viewport', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');
    }
    
    /**
     * Sets the value of the attribute 'href' for the 'base' tag.
     * @param string|null $url The value to set. The base URL will be updated 
     * only if the given parameter is a string and it is not empty. If null is 
     * given, the node will be removed from the body of the head tag.
     * @return boolean The method will return true if the base URL has been updated. 
     * False if not.
     * @since 1.0
     */
    public function setBase($url){
        if($url === null && $this->hasChild($this->baseNode)){
            $this->removeChild($this->baseNode);
            $this->baseNode->removeAttribute('href');
            return true;
        }
        $trimmedUrl = trim($url.'');
        if(strlen($trimmedUrl) != 0){
            if($this->baseNode == null){
                $this->baseNode = new HTMLNode('base');
            }
            if(!$this->hasChild($this->baseNode)){
                parent::insert($this->baseNode,0);
            }
            $this->baseNode->setAttribute('href',$trimmedUrl);
            return true;
        }
        return false;
    }
    /**
     * Returns the value of the attribute 'charset' of the meta tag that is used 
     * to specify character set of the document.
     * @return string|null A string such as 'UTF-8'. If character set is not 
     * set, the method will return null.
     * @since 1.1.4
     */
    public function getCharSet() {
        return $this->metaCharset->getAttributeValue('charset');
    }
    /**
     * Returns an object of type HTMLNode that represents the meta tag which 
     * has the attribute 'charset'.
     * Note that the node that represents charset of the will always have a 
     * position between 0 and 2 in the body of the head node.
     * @return HTMLNode An object of type HTMLNode.
     * @since 1.1.4
     */
    public function getCharsetNode() {
        return $this->metaCharset;
    }
    /**
     * Set the value of the meta tag which has the attribute 'charset'.
     * @param string|null $charset The character set that will be used to 
     * render the document (such as 'UTF-8' or 'ISO-8859-8'. If null is 
     * given, the node will be removed from the head body. 
     * @return boolean The method will return true if the charset is updated 
     * or the node is removed. Other than that, the method will return false. 
     * @since 1.1.4
     */
    public function setCharSet($charset) {
        if($charset === null && $this->hasChild($this->metaCharset)){
            $this->removeChild($this->metaCharset);
            $this->metaCharset->removeAttribute('charset');
            return true;
        }
        $trimmedCharset = trim($charset);
        if(strlen($charset) > 0){
            if(!$this->hasChild($this->metaCharset)){
                $position = 2;
                if(!$this->hasChild($this->baseNode)){
                    $position--;
                }
                if(!$this->hasChild($this->titleNode)){
                    $position--;
                }
                parent::insert($this->metaCharset,$position);
            }
            $this->metaCharset->setAttribute('charset', $trimmedCharset);
            return true;
        }
        return false;
    }
    /**
     * Returns a node that represents the tag 'base'.
     * Note that the base note has a fixed position in the head node which is 0.
     * @return HTMLNode A node that represents the tag 'base'.
     * @since 1.0
     */
    public function getBaseNode(){
        return $this->baseNode;
    }
    /**
     * Returns the value of the attribute 'href' of the node 'base'.
     * @return string|null The value of the attribute 'href' of the node 'base'. 
     * if the value of the base URL is not set, the method will return null.
     * @since 1.1.3
     */
    public function getBaseURL() {
        return $this->baseNode->getAttributeValue('href');
    }
    /**
     * Sets the text value of the node 'title'.
     * @param string|null $title The title to set. It must be non-empty string in 
     * order to set. If null is given, 'title' node will be omitted from the 
     * body of the 'head' tag.
     * @return boolean If the title is set or title node is removed, the method 
     * will return true. False otherwise.
     * @since 1.0
     */
    public function setTitle($title){
        if($title === null && $this->hasChild($this->titleNode)){
            $this->removeChild($this->titleNode);
            $this->titleNode->children()->get(0)->setText('');
            return true;
        }
        $trimmedTitle = trim($title);
        if(strlen($trimmedTitle) != 0){
            if($this->titleNode == null){
                $this->titleNode = new HTMLNode('title');
                $this->titleNode->addChild(self::createTextNode($trimmedTitle));
            }
            if(!$this->hasChild($this->titleNode)){
                $position = 1;
                if(!$this->hasChild($this->baseNode)){
                    $position--;
                }
                parent::insert($this->titleNode,$position);
            }
            $this->titleNode->children()->get(0)->setText($trimmedTitle);
            return true;
        }
        return false;
    }
    /**
     * Returns an object of type HTMLNode that represents the title node.
     * Note that the title node will be always in position 0 or 1 in the 
     * body of the head node.
     * @return HTMLNode The method will return 
     * an object of type HTMLNode that represents title node.
     * @since 1.1.3
     */
    public function getTitleNode() {
        return $this->titleNode;
    }
    /**
     * Returns the text that was set for the note 'title'.
     * @return string The text that was set for the note 'title'. If it was not 
     * set, the method will return empty string.
     * @since 1.1.3
     */
    public function getTitle() {
        return $this->titleNode->children()->get(0)->getText();
    }
    /**
     * Returns a linked list of all link tags that link to a CSS file.
     * @return LinkedList A linked list of all link tags that link to a CSS file. If 
     * the node has no CSS link tags, the method will return an empty list.
     * @since 1.0
     */
    public function getCSSNodes(){
        $children = $this->children();
        $chCount = $children->size();
        $list = new LinkedList();
        for($x = 0 ; $x < $chCount ; $x++){
            $child = $children->get($x);
            $childName = $child->getNodeName();
            if($childName == 'link'){
                if($child->hasAttribute('rel') && $child->getAttributeValue('rel') == 'stylesheet'){
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
            $childName = $child->getNodeName();
            if($childName == 'script'){
                if($child->hasAttribute('type') && $child->getAttributeValue('type') == 'text/javascript'){
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
            $childName = $child->getNodeName();
            if($childName == 'meta'){
                $list->add($child);
            }
        }
        return $list;
    }
    /**
     * Adds new meta tag.
     * @param string $name The value of the property 'name'. Must be non empty 
     * string.
     * @param string $content The value of the property 'content'.
     * @param boolean $override A boolean attribute. If a meta node was found 
     * which has the given name and this attribute is set to true, 
     * the content of the meta will be overridden by the passed value. 
     * @return boolean If the meta tag is added or updated, the method will return 
     * true. Other than that, the method will return false.
     * @since 1.0
     */
    public function addMeta($name,$content,$override=false){
        $trimmedName = trim(strtolower($name.''));
        if(strlen($trimmedName) != 0){
            $meta = $this->getMeta($trimmedName);
            if($meta !== null && $override === true){
                $meta->setAttribute('content', $content);
                return true;
            }
            else if($meta === null){
                $meta = new HTMLNode('meta');
                $meta->setAttribute('name', $trimmedName);
                $meta->setAttribute('content', $content);
                $insertPosition = -1;
                for($x = 0 ; $x < $this->childrenCount() ; $x++){
                    $chNode = $this->getChild($x);
                    if($chNode->getNodeName() == 'meta'){
                        $insertPosition = $x;
                    }
                }
                if($insertPosition != -1){
                    $this->insert($meta,$insertPosition+1);
                }
                else{
                    $this->addChild($meta);
                }
                return true;
            }
        }
        return false;
    }
    /**
     * Adds new child node.
     * @param HTMLNode $node The node that will be added. The node will be added 
     * only if the following conditions are met:
     * <ul>
     * <li>It must be not a 'title' or 'base' node.</li>
     * <li>It is a 'link' node but 'rel' attribute is not 'canonical'.</li>
     * <li>It is a 'script' or 'noscript' node.</li>
     * <li>It is a 'meta' node which is not added before.</li>
     * <li>It is a '#COMMENT' node.</li>
     * </ul>
     * Other than that, the node will be not added.
     * @return boolean If the node is added, the method will return true. If 
     * not added, the method will return false.
     * @since 1.0
     */
    public function addChild($node) {
        $retVal = false;
        if($node instanceof HTMLNode){
            $nodeName = $node->getNodeName();
            if(in_array($nodeName, self::ALLOWED_CHILDREN)){
                if($nodeName == 'meta'){
                    $nodeAttrs = $node->getAttributes();
                    foreach ($nodeAttrs as $attr => $val){
                        if(strtolower($attr) == 'charset'){
                            return false;
                        }
                    }
                    if($this->hasMeta($node->getAttributeValue('name'))){
                        $retVal = false;
                    }
                    else{
                        parent::addChild($node);
                        $retVal = true;
                    }
                }
                else if($nodeName == 'base' || $nodeName == 'title'){
                    $retVal = false;
                }
                else if($nodeName == 'link'){
                    $relVal = $node->getAttributeValue('rel');
                    if($relVal == 'canonical'){
                        $retVal = false;
                    }
                    else{
                        parent::addChild($node);
                        $retVal = true;
                    }
                }
                else{
                    parent::addChild($node);
                    $retVal = true;
                }
            }
        }
        return $retVal;
    }
    /**
     * Returns HTML node that represents a meta tag.
     * @param string $name The value of the attribute 'name' of the meta 
     * tag. Note that if the meta node that you would like to get is 
     * the tag which has the attribute 'charset', then the passed attribute 
     * must have the value 'charset'.
     * @return HTMLNode|null If a meta tag which has the given name was found, 
     * It will be returned. If no meta node was found, null is returned.
     * @since 1.1.2
     */
    public function getMeta($name) {
        $lName = strtolower(trim($name));
        if($lName == 'charset'){
            return $this->getCharsetNode();
        }
        else{
            for($x = 0 ; $x < $this->childrenCount() ; $x++){
                $node = $this->children()->get($x);
                if($node->getNodeName() == 'meta'){
                    if($node->getAttributeValue('name') == $name){
                        return $node;
                    }
                }
            }
        }
        $null = null;
        return $null;
    }
    /**
     * Checks if a CSS node with specific 'href' does exist or not.
     * Note that the method will not check for query string in the passed 
     * value. It will simply ignore it.
     * @param string $loc The value of the attribute 'href' of 
     * the CSS node.
     * @return boolean If a link node with the given 'href' value does 
     * exist, the method will return true. Other than that, the method 
     * will return false.
     * 1.1.5
     */
    public function hasCss($loc) {
        $trimmedLoc = trim($loc);
        $splitted = explode('?', $trimmedLoc);
        if(count($splitted) == 2){
            $trimmedLoc = trim($splitted[0]);
            $queryString = trim($splitted[1]);
        }
        else{
            $queryString = '';
        }
        $cssNodes = $this->getCSSNodes();
        foreach ($cssNodes as $node){
            if($node->hasAttribute('href')){
                $hrefExpl = explode('?', $node->getAttribute('href'));
                $href = $hrefExpl[0];
                if($href == $trimmedLoc){
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Checks if a JavaScript node with specific 'src' value does exist or not.
     * Note that the method will not check for query string in the passed 
     * value. It will simply ignore it.
     * @param string $src The value of the attribute 'src' of 
     * the script node.
     * @return boolean If a JavaScript node with the given 'src' value does 
     * exist, the method will return true. Other than that, the method 
     * will return false.
     * 1.1.5
     */
    public function hasJs($src) {
        $trimmedLoc = trim($src);
        $splitted = explode('?', $trimmedLoc);
        if(count($splitted) == 2){
            $trimmedLoc = trim($splitted[0]);
            $queryString = trim($splitted[1]);
        }
        else{
            $queryString = '';
        }
        $jsNodes = $this->getJSNodes();
        foreach ($jsNodes as $node){
            if($node->hasAttribute('src')){
                $srcV = explode('?', $node->getAttribute('src'))[0];
                if($srcV == $trimmedLoc){
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Checks if a meta tag which has the given name exist or not.
     * @param string $name The value of the attribute 'name' of the meta 
     * tag. If the developer would like to check for the existence of the 
     * node which has the attribute 'charset', he can pass the value 'charset'.
     * @return boolean If a meta tag which has the given name was found, 
     * true is returned. false otherwise.
     * @since 1.1.2
     */
    public function hasMeta($name) {
        $lName = strtolower($name);
        if($lName == 'charset'){
            return $this->hasChild($this->metaCharset);
        }
        else{
            for($x = 0 ; $x < $this->childrenCount() ; $x++){
                $node = $this->children()->get($x);
                if($node->getNodeName() == 'meta'){
                    if($node->getAttributeValue('name') == $name){
                        return true;
                    }
                }
            }
        }
        return false;
    }
    /**
     * Adds new CSS source file.
     * @param string $href The link to the file. Must be non empty string. It is 
     * possible to append query string to the end of the link.
     * @param $otherAttrs An associative array of additional attributes 
     * to set for the node. The indices are the names of attributes and the value 
     * of each index is the value of the attribute. Also, the array can only 
     * have attribute name if its empty attribute. Default is empty array.
     * @param boolean $preventCaching If set to true, a string in the form '?cv=xxxxxxxxxx' will 
     * be appended to the 'href' attribute value. It is used to prevent caching. 
     * Default is true. 'cv' = CSS Version.
     * @return boolean If a link tag which has the given CSS file is created, the 
     * method will return true. If no node is added, the method will return 
     * false.
     * @since 1.0
     */
    public function addCSS($href, $otherAttrs=[], $preventCaching=true){
        $trimmedHref = trim($href);
        $splitted = explode('?', $trimmedHref);
        if(count($splitted) == 2){
            $trimmedHref = trim($splitted[0]);
            $queryString = trim($splitted[1]);
        }
        else if(count($splitted) > 2){
            return false;
        }
        else{
            $queryString = '';
        }
        if(strlen($trimmedHref) != 0){
            $tag = new HTMLNode('link');
            $tag->setAttribute('rel','stylesheet');
            if($preventCaching === true){
                //used to prevent caching 
                $version = substr(hash('sha256', time()+rand(0, 10000)), rand(0,10),10);
                if(strlen($queryString) != 0){
                    $tag->setAttribute('href', $trimmedHref.'?'.$queryString.'&cv='.$version);
                }
                else{
                    $tag->setAttribute('href', $trimmedHref.'?cv='.$version);
                }
            }
            else{
                if(strlen($queryString) != 0){
                    $tag->setAttribute('href', $trimmedHref.'?'.$queryString);
                }
                else{
                    $tag->setAttribute('href', $trimmedHref);
                }
            }
            if(gettype($otherAttrs) == 'array'){
                foreach ($otherAttrs as $attr=>$val){
                    if(gettype($attr) == 'integer'){
                        $trimmedAttr = trim(strtolower($val));
                        if($trimmedAttr != 'rel' && $trimmedAttr != 'href'){
                            $tag->setAttribute($val);
                        }
                    }
                    else{
                        $trimmedAttr = trim(strtolower($attr));
                        if($trimmedAttr != 'rel' && $trimmedAttr != 'href'){
                            $tag->setAttribute($trimmedAttr, $val);
                        }
                    }
                }
            }
            $insertPosition = -1;
            for($x = 0 ; $x < $this->childrenCount() ; $x++){
                $chNode = $this->getChild($x);
                if($chNode->getNodeName() == 'link' && $chNode->getAttribute('rel') == 'stylesheet'){
                    $insertPosition = $x;
                }
            }
            if($insertPosition != -1){
                $this->insert($tag,$insertPosition+1);
            }
            else{
                $this->addChild($tag);
            }
            return true;
        }
        return false;
    }
    /**
     * Adds new JavsScript source file.
     * @param string $loc The location of the file. Must be non-empty string. It 
     * can have query string at the end.
     * @param $otherAttrs An associative array of additional attributes 
     * to set for the node. The indices are the names of attributes and the value 
     * of each index is the value of the attribute. Also, the array can only 
     * have attribute name if its empty attribute. Default is empty array.
     * @param boolean $preventCaching If set to true, a string in the form '?jv=xxxxxxxxxx' will 
     * be appended to the 'href' attribute value. It is used to prevent caching. 
     * 'jv' = JavaScript Version.
     * Default is true.
     * @return boolean If a script node which has the given JS file is added, the 
     * method will return true. If no node is added, the method will return 
     * false.
     * @since 1.0
     */
    public function addJs($loc, $otherAttrs=[],$preventCaching=true){
        $trimmedLoc = trim($loc);
        $splitted = explode('?', $trimmedLoc);
        if(count($splitted) == 2){
            $trimmedLoc = trim($splitted[0]);
            $queryString = trim($splitted[1]);
        }
        else if(count($splitted) > 2){
            return false;
        }
        else{
            $queryString = '';
        }
        if(strlen($trimmedLoc) != 0){
            $tag = new HTMLNode('script');
            $tag->setAttribute('type','text/javascript');
            if($preventCaching === true){
                //used to prevent caching 
                $version = substr(hash('sha256', time()+rand(0, 10000)), rand(0,10),10);
                if(strlen($queryString) == 0){
                    $tag->setAttribute('src', $trimmedLoc.'?jv='.$version);
                }
                else{
                    $tag->setAttribute('src', $trimmedLoc.'?'.$queryString.'&jv='.$version);
                }
            }
            else{
                if(strlen($queryString) == 0){
                    $tag->setAttribute('src', $trimmedLoc);
                }
                else{
                    $tag->setAttribute('src', $trimmedLoc.'?'.$queryString);
                }
            }
            if(gettype($otherAttrs) == 'array'){
                foreach ($otherAttrs as $indexOrAttrName=>$attrOrVal){
                    if(gettype($indexOrAttrName) == 'integer'){
                        $trimmedAttr = trim(strtolower($attrOrVal));
                        if($trimmedAttr != 'type' && $trimmedAttr != 'src'){
                            $tag->setAttribute($trimmedAttr);
                        }
                    }
                    else{
                        $trimmedAttr = trim(strtolower($indexOrAttrName));
                        if($trimmedAttr != 'type' && $trimmedAttr != 'src'){
                            $tag->setAttribute($trimmedAttr, $attrOrVal);
                        }
                    }
                }
            }
            $insertPosition = -1;
            for($x = 0 ; $x < $this->childrenCount() ; $x++){
                $chNode = $this->getChild($x);
                if($chNode->getNodeName() == 'script' && $chNode->getAttribute('type') == 'text/javascript'){
                    $insertPosition = $x;
                }
            }
            if($insertPosition != -1){
                $this->insert($tag,$insertPosition+1);
            }
            else{
                $this->addChild($tag);
            }
            return true;
        }
        return false;
    }
    /**
     * Sets the canonical URL.
     * Note that the canonical URL will be set only if the given string is not 
     * empty. Also, the node will always have a 
     * position between 0 and 3 in the body of the head node.
     * @param string|null $link The URL to set. If null is given, the link node 
     * which represents the canonical URL will be removed from the body of the 
     * head tag.
     * @return boolean If the canonical is set or removed, the method will return true. False 
     * if not set.
     * @since 1.0
     */
    public function setCanonical($link){
        if($link === null && $this->hasChild($this->canonical)){
            $this->removeChild($this->canonical);
            $this->canonical->removeAttribute('href');
            return true;
        }
        $trimmedLink = trim($link.'');
        if(strlen($trimmedLink) != 0){
            if($this->canonical == null){
                $this->canonical = new HTMLNode('link');
                $this->canonical->setAttribute('rel', 'canonical');
            }
            if(!$this->hasChild($this->canonical)){
                $position = 3;
                if(!$this->hasChild($this->baseNode)){
                    $position--;
                }
                if(!$this->hasChild($this->titleNode)){
                    $position--;
                }
                if(!$this->hasChild($this->metaCharset)){
                    $position--;
                }
                parent::insert($this->canonical,$position);
            }
            $this->canonical->setAttribute('href', $trimmedLink);
            return true;
        }
        return false;
    }
    /**
     * Returns an object of type HTMLNode that represents the canonical URL.
     * @return HTMLNode|null If the canonical URL is set, the method will return 
     * an object of type HTMLNode. If not set, the method will return null.
     * @since 1.1.3
     */
    public function getCanonicalNode() {
        return $this->canonical;
    }
    /**
     * Returns the canonical URL if set.
     * @return string|null The canonical URL if set. If the URL is not set, 
     * the method will return null.
     * @since 1.0
     */
    public function getCanonical(){
        return $this->canonical->getAttributeValue('href');
    }
    /**
     * Adds new alternate tag to the header.
     * @param string $url The link to the alternate page. Must be non-empty string.
     * @param string $lang The language of the page. Must be non-empty string.
     * @param array $otherAttrs An associative array of additional attributes 
     * to set for the node. The indices are the names of attributes and the value 
     * of each index is the value of the attribute. Also, the array can only 
     * have attribute name if its empty attribute. Default is empty array.
     * @return boolean If a link element is created and added, the method will 
     * return true. If not added, the method will return false.
     * @since 1.0
     */
    public function addAlternate($url,$lang,$otherAttrs=[]){
        $trimmedUrl = trim($url);
        $trimmedLang = trim($lang);
        if(strlen($trimmedUrl) != 0 && strlen($trimmedLang) != 0){
            $node = new HTMLNode('link');
            $node->setAttribute('rel','alternate');
            $node->setAttribute('hreflang', $trimmedLang);
            $node->setAttribute('href', $trimmedUrl);
            if(gettype($otherAttrs) == 'array'){
                foreach ($otherAttrs as $attr=>$val){
                    if(gettype($attr) == 'integer'){
                        $trimmedAttr = trim(strtolower($val));
                        if($trimmedAttr != 'rel' && $trimmedAttr != 'hreflang' && $trimmedAttr != 'href'){
                            $node->setAttribute($trimmedAttr);
                        }
                    }
                    else{
                        $trimmedAttr = trim(strtolower($attr));
                        if($trimmedAttr != 'rel' && $trimmedAttr != 'hreflang' && $trimmedAttr != 'href'){
                            $node->setAttribute($trimmedAttr, $val);
                        }
                    }
                }
            }
            $insertPosition = -1;
            for($x = 0 ; $x < $this->childrenCount() ; $x++){
                $chNode = $this->getChild($x);
                if($chNode->getNodeName() == 'link' && $chNode->getAttribute('rel') == 'alternate'){
                    $insertPosition = $x;
                }
            }
            if($insertPosition != -1){
                $this->insert($node,$insertPosition+1);
            }
            else{
                $this->addChild($node);
            }
            return true;
        }
        return false;
    }
    /**
     * Adds new 'link' node.
     * Note that if the 'rel' attribute value is 'canonical' or 'alternate', no node will be 
     * created.
     * @param string $rel The value of the attribute 'rel'.
     * @param string $href The value of the attribute 'href'.
     * @param array $otherAttrs An associative array of keys and values. 
     * The keys will be used as an attribute and the key value will be used 
     * as attribute value.
     * @return boolean The method will return true if the element is created. False 
     * if not.
     * @since 1.1
     */
    public function addLink($rel,$href,$otherAttrs=[]){
        $trimmedRel = trim(strtolower($rel));
        $trimmedHref = trim($href);
        if(strlen($trimmedRel) != 0 && strlen($trimmedHref) != 0){
            if($trimmedRel != 'canonical' && $trimmedRel != 'alternate'){
                if($rel == 'stylesheet'){
                    return $this->addCSS($href, $otherAttrs);
                }
                else{
                    $node = new HTMLNode('link');
                    $node->setAttribute('rel',$trimmedRel);
                    $node->setAttribute('href', $trimmedHref);
                    if(gettype($otherAttrs) == 'array'){
                        foreach ($otherAttrs as $attr=>$val){
                            if(gettype($attr) == 'integer'){
                                $trimmedAttr = trim(strtolower($val));
                                if($trimmedAttr != 'rel' && $trimmedAttr != 'href'){
                                    $node->setAttribute($val);
                                }
                            }
                            else{
                                $trimmedAttr = trim(strtolower($attr));
                                if($trimmedAttr != 'rel' && $trimmedAttr != 'href'){
                                    $node->setAttribute($trimmedAttr, $val);
                                }
                            }
                        }
                    }
                    $insertPosition = -1;
                    for($x = 0 ; $x < $this->childrenCount() ; $x++){
                        $chNode = $this->getChild($x);
                        if($chNode->getNodeName() == 'link' && $chNode->getAttribute('rel') == $trimmedRel){
                            $insertPosition = $x;
                        }
                    }
                    if($insertPosition != -1){
                        $this->insert($node,$insertPosition+1);
                    }
                    else{
                        $this->addChild($node);
                    }
                    return true;
                }
            }
        }
        return false;
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
            $childName = $child->getNodeName();
            if($childName == 'link'){
                if($child->hasAttribute('rel') && $child->getAttributeValue('rel') == 'alternate'){
                    $list->add($child);
                }
            }
        }
        return $list;
    }
}
