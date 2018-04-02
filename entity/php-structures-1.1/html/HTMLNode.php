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
 * @version 1.0
 */
class HTMLNode {
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
            $this->attributes[$name] = $val;
        }
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
    
    public function __toString() {
        if($this->isTextNode()){
            return $this->getName();
        }
        else{
            return $this->getName().'[children-count:'.$this->childNodes()->size().', attributes-count:'.count($this->getAttributes()).']';
        }
    }
}
