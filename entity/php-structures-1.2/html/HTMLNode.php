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
 * A class that represents HTML or XML tag.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.2
 */
class HTMLNode {
    /**
     * The parent node of the instance.
     * @var HTMLNode
     * @since 1.2 
     */
    private $parentNode;
    /**
     * The name of the tag (such as 'div')
     * @var string
     * @since 1.0 
     */
    private $name;
    /**
     * An array of key-value elements. The key acts as the attribute name 
     * and the value acts as the value of the attribute.
     * @var array
     * @since 1.0 
     */
    private $attributes;
    /**
     * A list of child nodes.
     * @var LinkedList
     * @since 1.0 
     */
    private $childNodes;
    /**
     * A boolean value. If set to true, The node must be closed while building 
     * the document.
     * @var boolean
     * @since 1.0 
     */
    private $requireClose;
    /**
     * An attribute that is set to true if the node only contains text. 
     * @var boolean
     * @since 1.0 
     */
    private $isText;
    /**
     * The text that is located in the node body (applies only if the node is a 
     * text node). 
     * @var string
     * @since 1.0 
     */
    private $text;
    /**
     * Constructs a new instance of the class.
     * @param string $name The name of the node (such as 'div'). Default value 
     * is 'div'.
     * @param boolean $reqClose If set to <b>TRUE</b>, this means that the node 
     * must end with closing tag. 
     * @param boolean $isTextNode If set to <b>TRUE</b>, this means the node is 
     * a text node.
     * 
     */
    public function __construct($name='div',$reqClose=true,$isTextNode=false) {
        $this->name = $name;
        $this->requireClose = $reqClose;
        $this->isText = $isTextNode;
        $this->childNodes = new LinkedList();
        $this->attributes = array();
    }
    /**
     * Returns the parent node.
     * @return HTMLNode | NULL An object of type <b>HTMLNode</b> if the node 
     * has a parent. If the node has no parent, the function will return <b>NULL</b>.
     * @since 1.2
     */
    public function getParentNode() {
        return $this->parentNode;
    }
    /**
     * 
     * @param HTMLNode $node
     * @since 1.2
     */
    private function setParentNode($node){
        $this->parentNode = $node;
    }
    /**
     * Returns a linked list of all child nodes.
     * @return LinkedList
     * @since 1.0
     */
    public function childNodes(){
        return $this->childNodes;
    }
    /**
     * Checks if the node is a text node or not.
     * @return boolean <b>TRUE</b> if the node is a text node.
     * @since 1.0
     */
    public function isTextNode() {
        return $this->isText;
    }
    /**
     * Checks if a given node is a child of the instance
     * @param HTMLNode $node The node that will be checked.
     * @return boolean <b>TRUE</b> is returned if the node is a child 
     * of the instance. <b>FALSE</b> if not.
     * @since 1.2
     */
    public function hasNode($node) {
        if($node instanceof HTMLNode){
            return $this->childNodes()->indexOf($node) != -1;
        }
        return FALSE;
    }
    /**
     * Replace a node with a new one.
     * @param HTMLNode $oldNode The old node. It must be a child of the instance.
     * @param HTMLNode $replacement The replacement node.
     * @return boolean <b>TRUE</b> is returned if the node replaced. <b>FALSE</b> if not.
     * @since 1.2
     */
    public function replaceNode($oldNode,$replacement) {
        if($oldNode instanceof HTMLNode){
            if($this->hasNode($oldNode)){
                if($replacement instanceof HTMLNode){
                    $this->childNodes()->replace($this->childNodes()->indexOf($oldNode), $replacement);
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    /**
     * 
     * @param string $val
     * @param LinkedList $chList
     * @param LinkedList $list
     * @return LinkedList
     */
    private function _getChildrenByTag($val,$chList,$list){
        $chCount = $chList->size();
        for($x = 0 ; $x < $chCount ; $x++){
            $child = $chList->get($x);
            $tmpList = $child->_getChildrenByTag($val,$child->childNodes(),new LinkedList());
            for($y = 0 ; $y < $tmpList->size() ; $y++){
                $list->add($tmpList->get($y));
            }
        }
        for($x = 0 ; $x < $chCount ; $x++){
            $child = $chList->get($x);
            if($child->getName() == $val){
                $list->add($child);
            }
        }
        return $list;
    }
    /**
     * Returns a linked list that contains all child nodes which has the given 
     * tag name.
     * @param string $val The name of the tag (such as 'div' or 'a').
     * @return LinkedList A linked list that contains all child nodes which has the given 
     * tag name.
     * @since 1.2
     */
    public function getChildrenByTag($val){
        $val = $val.'';
        $list = new LinkedList();
        if(strlen($val) != 0){
            return $this->_getChildrenByTag($val, $this->childNodes(), $list);
        }
        return $list;
    }
    /**
     * 
     * @param type $val
     * @param LinkedList $chNodes
     * @return NULL|HTMLNode Description
     */
    private function _getChildByID($val,$chNodes){
        $chCount = $chNodes->size();
        for($x = 0 ; $x < $chCount ; $x++){
            $child = $chNodes->get($x);
            $tmpCh = $child->_getChildByID($val,$child->childNodes());
            if($tmpCh instanceof HTMLNode){
                return $tmpCh;
            }
        }
        for($x = 0 ; $x < $chCount ; $x++){
            $child = $chNodes->get($x);
            if($child->hasAttribute('id')){
                $attrVal = $child->getAttributeValue('id');
                if($attrVal == $val){
                    return $child;
                }
            }
        }
        return NULL;
    }
    /**
     * Returns a child node given its ID.
     * @param string $val The ID of the child.
     * @return NULL|HTMLNode The function returns an object of type <b>HTMLNode</b> 
     * if found. If no node has the given ID, the function will return <b>NULL</b>.
     * @since 1.2
     */
    public function getChildByID($val){
        $val = $val.'';
        if(strlen($val) != 0){
            return $this->_getChildByID($val, $this->childNodes());
        }
        return NULL;
    }
    /**
     * Checks if the node require ending tag or not.
     * @return boolean <b>TRUE</b> if the node does require ending tag.
     * @since 1.0
     */
    public function mustClose() {
        return $this->requireClose;
    }
    /**
     * Returns the name of the node.
     * @return string The name of the node. If the node is a text node, the 
     * function will return the value '#text'.
     * @since 1.0
     */
    public function getName(){
        if($this->isTextNode()){
            return '#text['.$this->getText().']';
        }
        return $this->name;
    }
    /**
     * Returns an array of all node attributes with the values
     * @return array an associative array. The keys will act as the attribute 
     * name and the value will act as the value of the attribute. If the node 
     * is a text node, the array will be empty.
     * @since 1.0 
     */
    public function getAttributes() {
        return $this->attributes;
    }
    /**
     * Sets a value for an attribute.
     * @param string $name The name of the attribute. If the attribute does not 
     * exist, it will be created. Note that if the node type is text node, 
     * the attribute will never be created.
     * @param string $val The value of the attribute.
     * @since 1.0
     */
    public function setAttribute($name,$val=''){
        if(!$this->isTextNode() && gettype($name) == 'string' && strlen($name) != 0){
            $lower = strtolower($name);
            if($name == 'dir'){
                $lowerVal = strtolower($val);
                if($val == 'ltr' || $val == 'rtl'){
                    $this->attributes[$lower] = $lowerVal;
                }
            }
            else{
                $this->attributes[$lower] = $val;
            }
        }
    }
    /**
     * Sets the value of the attribute 'id' of the node.
     * @param string $idVal The value to set.
     * @since 1.2
     */
    public function setID($idVal){
        $this->setAttribute('id',$idVal);
    }
    /**
     * Sets the value of the attribute 'tabindex' of the node.
     * @param int $val The value to set. From MDN: An integer attribute indicating if 
     * the element can take input focus. It can takes several values: 
     * <ul>
     * <li>A negative value means that the element should be focusable, but 
     * should not be reachable via sequential keyboard navigation.</li>
     * <li>0 means that the element should be focusable and reachable via sequential 
     * keyboard navigation, but its relative order is defined by the platform convention</li>
     * <li>A positive value means that the element should be focusable 
     * and reachable via sequential keyboard navigation; the order in 
     * which the elements are focused is the increasing value of the 
     * tabindex. If several elements share the same tabindex, their relative 
     * order follows their relative positions in the document.</li>
     * </ul>
     * @since 1.2
     */
    public function setTabIndex($val){
        $this->setAttribute('tabindex', $val);
    }
    /**
     * Sets the value of the attribute 'title' of the node.
     * @param string $val The value to set. From MDN: Contains a 
     * text representing advisory information related to the element 
     * it belongs to. Such information can typically, but not necessarily, 
     * be presented to the user as a tooltip.
     * @since 1.2
     */
    public function setTitle($val){
        $this->setAttribute('title', $val);
    }
    /**
     * Sets the value of the attribute 'dir' of the node.
     * @param string $val The value to set. It can be 'ltr' or 'rtl'.
     * @since 1.2
     */
    public function setWritingDir($val){
        $this->setAttribute('dir', $val);
    }
    /**
     * Sets the value of the attribute 'class' of the node.
     * @param string $val The value to set.
     * @since 1.2
     */
    public function setClassName($val){
        $this->setAttribute('class',$val);
    }
    /**
     * Sets the value of the attribute 'name' of the node.
     * @param string $val The value to set.
     * @since 1.2
     */
    public function setName($val){
        $this->setAttribute('name',$val);
    }
    /**
     * Removes an attribute from the node given its name.
     * @param string $name The name of the attribute.
     * @since 1.0
     */
    public function removeAttribute($name){
        if(isset($this->attributes[$name])){
            unset($this->attributes[$name]);
        }
    }
    /**
     * Removes all child nodes.
     * @since 1.0
     */
    public function removeAllChildNodes() {
        $this->childNodes->clear();
    }
    /**
     * Removes a child node.
     * @param HTMLNode $node The node that will be removed.
     * @return HTMLNode|NULL The function will return the node if removed. 
     * If not removed, the function will return <b>NULL</b>.
     * @since 1.2
     */
    public function removeNode($node) {
        if($node instanceof HTMLNode){
            $count = $this->childNodes()->size();
            for($x = 0 ; $x < $count ; $x++){
                $child = $this->childNodes()->get($x);
                if($child === $node){
                    $this->childNodes()->remove($x);
                    $child->setParentNode(NULL);
                    return $child;
                }
            }
        }
        return NULL;
    }
    /**
     * Adds new child node.
     * @param HTMLNode $node The node that will be added. The node can have 
     * child notes only if two conditions are met. If the node is not a text node 
     * and the node must have ending tag.
     * @since 1.0
     */
    public function addChild($node) {
        if(!$this->isTextNode() && $this->mustClose()){
            if($node instanceof HTMLNode){
                $this->childNodes->add($node);
                $node->setParentNode($this);
            }
        }
    }
    /**
     * Sets the value of the property <b>$text</b>.
     * @param string $text The text to set. If the node is not a text node, 
     * the value will never be set.
     * @since 1.0
     */
    public function setText($text) {
        if($this->isTextNode()){
            $this->text = $text;
        }
    }
    /**
     * Returns the value of the property <b>$text</b>.
     * @return string The value of the property <b>$text</b>. If the node is 
     * not a text node, the function will return empty string.
     * @since 1.0
     */
    public function getText() {
        return $this->text;
    }
    /**
     * Returns a string that represents the opening part of the tag.
     * @return string A string that represents the opening part of the tag. 
     * if the node is a text node, the returned value will be an empty string.
     * @since 1.0
     */
    public function asHTML() {
        $retVal = '';
        if(!$this->isTextNode()){
            $retVal .= '<'.$this->getName().'';
            foreach ($this->getAttributes() as $attr => $val){
                $retVal .= ' '.$attr.'="'.$val.'"';
            }
            $retVal .= '>';
        }
        return $retVal;
    }
    /**
     * Returns a node based on its attribute value (Direct childs).
     * @param string $attrName The name of the attribute.
     * @param string $attrVal The value of the attribute.
     * @return HTMLNode|NULL The function will return an object of type <b>HTMLNode</b> 
     * if a node is found. Other than that, the function will return <b>NULL</b>.
     */
    public function getChildByAttributeValue($attrName,$attrVal) {
        for($x = 0 ; $x < $this->childNodes()->size() ; $x++){
            $ch = $this->childNodes()->get($x);
            if($ch->hasAttribute($attrName)){
                if($ch->getAttributeValue($attrName) == $attrVal){
                    return $ch;
                }
            }
        }
        return NULL;
    }
    /**
     * Returns the value of an attribute.
     * @param string $attrName The name of the attribute.
     * @return string|NULL The function will return the value of the attribute 
     * if found. If no such attribute, the function will return NULL.
     * @since 1.1
     */
    public function getAttributeValue($attrName) {
        if($this->hasAttribute($attrName)){
            return $this->attributes[$attrName];
        }
        return NULL;
    }
    /**
     * Checks if the node has a given attribute or not.
     * @param type $attrName The name of the attribute.
     * @return boolean <b>TRUE</b> if the attribute is set.
     * @since 1.1
     */
    public function hasAttribute($attrName){
        return isset($this->attributes[$attrName]);
    }
    public function __toString() {
        return $this->toHTML();
    }
    private $document;
    private $nodesStack;
    public function toHTML(){
        $this->document = '';
        $this->nodesStack = new Stack();
        $this->pushNode($this);
        return $this->document;
    }
    /**
     * 
     * @param HTMLNode $node
     */
    private function pushNode($node) {
        if($node->isTextNode()){
            $this->document .= $node->getText();
        }
        else{
            if($node->mustClose()){
                $chCount = $node->childNodes()->size();
                $this->nodesStack->push($node);
                $this->document .= $node->asHTML();
                for($x = 0 ; $x < $chCount ; $x++){
                    $nodeAtx = $node->childNodes()->get($x);
                    $this->pushNode($nodeAtx);
                }
                $this->popNode();
            }
            else{
                $this->document .= $node->asHTML();
            }
        }
    }
    private function popNode(){
        $node = $this->nodesStack->pop();
        if($node != NULL){
            $this->document .= '</'.$node->getName().'>';
        }
    }
}
