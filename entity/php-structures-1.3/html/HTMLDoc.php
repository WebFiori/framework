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
 * A class that represents HTML document.
 *
 * @author Ibrahim
 * @version 1.2
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
     * The indentation space that is used to make the tags well formated.
     * @var string 
     */
    private $tapSpace;
    /**
     * A number that represents the number of open tags (0 no tag. 1 one tag. 2 
     * is 2 tags and so on). It is also used as tab indicator.
     * @var int 
     * @since 1.0
     */
    private $tabCount = 0;
    /**
     * The whole document as HTML string.
     * @var string
     * @since 1.0 
     */
    private $document;
    /**
     * A stack that contains an objects of type <b>HTMLNode</b>. Used to build 
     * the document.
     * @var Stack 
     */
    private $nodesStack;
    /**
     * New line character.
     * @var string 
     * @since 1.0
     */
    private $nl = "\n";
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
        $hNodes = $this->headNode->getChildrenByTag($val);
        for($x = 0 ; $x < $hNodes->size() ; $x++){
            $list->add($hNodes->get($x));
        }
        return $list;
    }
    /**
     * Returns a child node given its ID.
     * @param string $id The ID of the child.
     * @return NULL|HTMLNode The function returns an object of type <b>HTMLNode</b> 
     * if found. If no node has the given ID, the function will return <b>NULL</b>.
     * @since 1.2
     */
    public function getChildByID($id) {
        return $this->htmlNode->getChildByID($id);
    }
    /**
     * Constructs a new HTML document.
     */
    public function __construct() {
        $this->body = new HTMLNode('body', TRUE, FALSE);
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
     * Sets the value of the head node.
     * @param HeadNode $node The node to set.
     * @since 1.0
     */
    public function setHeadNode($node){
        if($node instanceof HeadNode){
            $this->htmlNode->replaceNode($this->headNode, $node);
            $this->headNode = $node;
        }
    }
    public function __toString() {
        return $this->toHTML();
    }
    /**
     * Returns a string that represents the document.
     * @param boolean $formatted If set to true, The generated document will be 
     * well formatted.
     * @return string A string that represents the document.
     * @since 1.0
     */
    public function toHTML($formatted=true){
        if(!$formatted){
            $this->nl = '';
            $this->tapSpace = '';
        }
        else{
            $this->nl = "\n";
            $tabCount=0;
            $tabSpacesCount=4;
            if($tabCount > -1){
                $this->tabCount = $tabCount;
            }
            else{
                $this->tabCount = 0;
            }
            $this->tapSpace = '';
            if($tabSpacesCount > 0 && $tabSpacesCount < 9){
                for($x = 0 ; $x < $tabSpacesCount ; $x++){
                    $this->tapSpace .= ' ';
                }
            }
            else{
                for($x = 0 ; $x < 4 ; $x++){
                    $this->tapSpace .= ' ';
                }
            }
        }
        $this->nodesStack = new Stack();
        $this->document = '<!DOCTYPE html>'.$this->nl;
        $this->pushNode($this->htmlNode,$formatted);
        return $this->document;
    }
    /**
     * Returns the node that represents the 'head' node.
     * @return HeadNode The node that represents the 'head' node.
     * @since 1.2
     */
    public function getHeadNode() {
        return $this->headNode;
    }
    /**
     * Returns the node that represents the body of the document.
     * @return HTMLNode The node that represents the body.
     * @since 1.2
     */
    public function getBody() {
        return $this->body;
    }
    /**
     * Appends new node to the body of the document.
     * @param HTMLNode $node The node that will be added.
     * @since 1.0
     */
    public function addNode($node){
        if($node instanceof HTMLNode){
            $this->body->addChild($node);
        }
    }
    /**
     * 
     * @param HTMLNode $node
     */
    private function pushNode($node) {
        if($node->isTextNode()){
            $this->document .= $this->getTab().$node->getText().$this->nl;
        }
        else{
            if($node->mustClose()){
                $nodeChilds = $node->childNodes();
                $chCount = $nodeChilds->size();
                $this->nodesStack->push($node);
                $this->document .= $this->getTab().$node->asHTML().$this->nl;
                $this->addTab();
                for($x = 0 ; $x < $chCount ; $x++){
                    $nodeAtx = $nodeChilds->get($x);
                    $this->pushNode($nodeAtx);
                }
                $this->reduceTab();
                $this->popNode();
            }
            else{
                $this->document .= $this->getTab().$node->asHTML().$this->nl;
            }
        }
    }
    private function popNode(){
        $node = $this->nodesStack->pop();
        if($node != NULL){
            $this->document .= $this->getTab().'</'.$node->getName().'>'.$this->nl;
        }
    }
    /**
     * Increase tab size by 1.
     * @since 1.0
     */
    private function addTab(){
        $this->tabCount += 1;
    }
    
    /**
     * Reduce tab size by 1.
     * If the tab size is 0, it will not reduce it more.
     * @since 1.0
     */
    private function reduceTab(){
        if($this->tabCount > 0){
            $this->tabCount -= 1;
        }
    }
    /**
     * Returns the currently used tag space. 
     * @return string
     * @since 1.0
     */
    private function getTab(){
        if($this->tabCount == 0){
            return '';
        }
        else{
            $tab = '';
            for($i = 0 ; $i < $this->tabCount ; $i++){
                $tab .= $this->tapSpace;
            }
            return $tab;
        }
    }
}
