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
namespace phpStructs\html;
/**
 * A class that represents HTML document. The created document is HTML 5 compatible (
 * DOCTYPE html). Also, the document will have the following features by default: 
 * <ul>
 * <li>A Head node with meta charset = 'utf-8' and view port = 'width=device-width, initial-scale=1.0'.</li>
 * <li>A body node.</li>
 * </ul>
 *
 * @author Ibrahim
 * @version 1.4
 */
class HTMLDoc {
    /**
     * The parent HTML Node.
     * @var HTMLNode
     * @since 1.2 
     */
    private $htmlNode;
    /**
     * The head tag of the document.
     * @var HTMLNode 
     * @since 1.0
     */
    private $headNode;
    /**
     * The body tag of the document
     * @var HTMLNode 
     * @since 1.0
     */
    private $body;

    /**
     * The whole document as HTML string.
     * @var string
     * @since 1.0 
     */
    private $document;
    /**
     * New line character.
     * @var string 
     * @since 1.0
     */
    private $nl = "\n";
    /**
     * A constant that represents new line character
     * @since 1.3
     */
    const NL = "\n";
    /**
     * Saves the document into a file.
     * @param string $path The location where the content will be written to.
     * @param boolean $wellFormatted If set to true, The generated file will be 
     * well formatted (readable by humans).
     * @param string $fileType The type of the file that the document will be 
     * stored to (such as 'txt' or 'html').
     * @return boolean <b>TRUE</b> if the file is successfully created.
     * @since 1.0
     */
    public function saveToFile($path,$wellFormatted=true,$fileType='html'){
        $f = fopen($path.'.'.$fileType, 'w+');
        if($f != FALSE){
            fwrite($f, $this->toHTML($wellFormatted));
            fclose($f);
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns a linked list that contains all children which has the given tag 
     * value.
     * @param string $val The value of the tag (such as 'div' or 'input').
     * @return LinkedList A linked list that contains all children which has the given tag 
     * value. 
     * @since 1.2
     */
    public function getChildrenByTag($val) {
        $list = new LinkedList();
        $this->_getChildrenByTag($val, $list, $this->htmlNode);
        return $list;
    }
    /**
     * 
     * @param sring $val
     * @param LinkedList $list
     * @param HTMLNode $child
     */
    private function _getChildrenByTag($val,$list,&$child){
        if($child->getName() == $val){
            $list->add($child);
        }
        if(!$child->isTextNode() && !$child->isComment()){
            $children = &$child->children();
            $chCount = $children->size();
            for($x = 0 ; $x < $chCount ; $x++){
                $ch = &$children->get($x);
                $this->_getChildrenByTag($val, $list, $ch);
            }
        }
    }
    /**
     * Returns a child node given its ID.
     * @param string $id The ID of the child.
     * @return NULL|HTMLNode The function returns an object of type <b>HTMLNode</b> 
     * if found. If no node has the given ID, the function will return <b>NULL</b>.
     * @since 1.2
     */
    public function &getChildByID($id) {
        return $this->htmlNode->getChildByID($id);
    }
    /**
     * Constructs a new HTML document.
     */
    public function __construct() {
        $this->body = new HTMLNode('body', TRUE, FALSE);
        $this->body->setAttribute('itemscope', '');
        $this->body->setAttribute('itemtype', 'http://schema.org/WebPage');
        $this->headNode = new HeadNode();
        $this->htmlNode = new HTMLNode('html');
        $this->htmlNode->addChild($this->headNode);
        $this->htmlNode->addChild($this->body);
    }
    /**
     * Sets the language of the document.
     * @param string $lang A two characters language code.
     * @since 1.0
     */
    public function setLanguage($lang){
        if(strlen($lang) == 2){
            $this->htmlNode->setAttribute('lang', $lang);
        }
    }
    /**
     * Returns the language of the document.
     * @return string A two characters language code. If the language is 
     * not set, the function will return empty string.
     * @since 1.0
     */
    public function getLanguage(){
        if($this->htmlNode->hasAttribute('lang')){
            return $this->htmlNode->getAttributeValue('lang');
        }
        return '';
    }
    /**
     * Updates the head node that is used by the document.
     * @param HeadNode $node The node to set.
     * @since 1.0
     */
    public function setHeadNode(&$node){
        if($node instanceof HeadNode){
            $this->htmlNode->replaceChild($this->headNode, $node);
            $this->headNode = $node;
        }
    }
    /**
     * Returns a string of HTML code that represents the document.
     * @return string A string of HTML code that represents the document.
     */
    public function __toString() {
        return $this->toHTML(FALSE);
    }
    /**
     * Returns HTML string that represents the document.
     * @param boolean $formatted [Optional] If set to <b>TRUE</b>, The generated HTML code will be 
     * well formatted. Default is <b>TRUE</b>.
     * @return string HTML string that represents the document.
     * @since 1.0
     */
    public function toHTML($formatted=true){
        if(!$formatted){
            $this->nl = '';
        }
        else{
            $this->nl = self::NL;
        }
        $this->document = '<!DOCTYPE html>'.$this->nl;
        $this->document .= $this->htmlNode->toHTML($formatted);
        return $this->document;
    }
    /**
     * Returns the document as readable HTML code wrapped inside 'pre' element.
     * @param array $formattingOptions [Optional] An associative array which contains 
     * an options for formatting the code. The available options are:
     * <ul>
     * <li><b>tab-spaces</b>: The number of spaces in a tab. Usually 4.</li>
     * <li><b>with-colors</b>: A boolean value. If set to TRUE, the code will 
     * be highlighted with colors.</li>
     * <li><b>initial-tab</b>: Number of initial tabs</li>
     * <li><b>colors</b>: An associative array of highlight colors.</li>
     * </ul>
     * The array 'colors' has the following options:
     * <ul>
     * <li><b>bg-color</b>: The 'pre' block background color.</li>
     * <li><b>attribute-color</b>: HTML attribute name color.</li>
     * <li><b>attribute-value-color</b>: HTML attribute value color.</li>
     * <li><b>text-color</b>: Normal text color.</li>
     * <li><b>comment-color</b>: Comment color.</li>
     * <li><b>operator-color</b>: Assignment operator color.</li>
     * <li><b>lt-gt-color</b>: Less than and greater than color.</li>
     * <li><b>node-name-color</b>: Node name color.</li>
     * </ul>
     * @return string The document as readable HTML code wrapped inside 'pre' element.
     * @since 1.4
     */
    public function asCode($formattingOptions=HTMLNode::DEFAULT_CODE_FORMAT) {
        return $this->htmlNode->asCode($formattingOptions);
    }
    /**
     * Removes a child node from the document.
     * @param HTMLNode $node The node that will be removed. If the given 
     * node name is 'body' or 'head', The node will never be removed.
     * @return HTMLNode|NULL The function will return the node if removed. 
     * If not removed, the function will return <b>NULL</b>.
     * @since 1.4
     */
    public function removeChild(&$node) {
        if($node instanceof HTMLNode){
            return $this->_removeChild($this->htmlNode, $node);
        }
        $null = NULL;
        return $null;
    }
    /**
     * 
     * @param HTMLNode $ch
     */
    private function _removeChild(&$ch,&$nodeToRemove){
        for($x = 0 ; $x < $ch->childrenCount() ; $x++){
            $removed = $this->_removeChild($ch->children()->get($x),$nodeToRemove);
            if($removed instanceof HTMLNode){
                return $removed;
            }
        }
        $removed = &$ch->removeChild($nodeToRemove);
        return $removed;
    }
    /**
     * Returns the node that represents the 'head' node.
     * @return HeadNode The node that represents the 'head' node.
     * @since 1.2
     */
    public function &getHeadNode() {
        return $this->headNode;
    }
    /**
     * Returns the node that represents the body of the document.
     * @return HTMLNode The node that represents the body.
     * @since 1.2
     */
    public function &getBody() {
        return $this->body;
    }
    /**
     * Appends new node to the body of the document.
     * @param HTMLNode $node The node that will be added. It will be added 
     * only if the name of the node is not 'html', 'head' or body.
     * @since 1.0
     */
    public function addChild(&$node){
        if($node instanceof HTMLNode){
            $name = $node->getName();
            if($name != 'body' && $name != 'head' && $name != 'html'){
                $this->body->addChild($node);
            }
        }
    }
}
