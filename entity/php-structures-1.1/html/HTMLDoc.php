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
 * @version 1.0
 */
class HTMLDoc {
    /**
     * The head tag of the document.
     * @var HTMLNode 
     * @since 1.0
     */
    private $headTag;
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
     * The language of the document. A two characters string.
     * @var string
     * @since 1.0 
     */
    private $docLang;
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
     * Constructs a new HTML document.
     */
    public function __construct() {
        $this->body = new HTMLNode('body', TRUE, FALSE);
        $this->headTag = new HeadNode();
    }
    /**
     * Sets the language of the document.
     * @param string $lang A two characters language code.
     * @since 1.0
     */
    public function setLanguage($lang){
        if(strlen($lang) == 2){
            $this->docLang = $lang;
        }
    }
    /**
     * Returns the language of the document.
     * @return string A two characters language code.
     * @since 1.0
     */
    public function getLanguage(){
        return $this->docLang;
    }
    /**
     * Sets the value of the head node.
     * @param HeadNode $node The node to set.
     * @since 1.0
     */
    public function setHeadNode($node){
        if($node instanceof HeadNode){
            $this->headTag = $node;
        }
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
        $html = new HTMLNode('html', TRUE, FALSE);
        $this->nodesStack = new Stack();
        $this->document = '<!DOCTYPE html>'.$this->nl;
        $html->addChild($this->headTag);
        $html->addChild($this->body);
        if($this->getLanguage() != NULL){
            $html->setAttribute('lang', $this->getLanguage());
        }
        $this->pushNode($html,$formatted);
        return $this->document;
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
                $chCount = $node->childNodes()->size();
                $this->nodesStack->push($node);
                $this->document .= $this->getTab().$node->asHTML().$this->nl;
                $this->addTab();
                for($x = 0 ; $x < $chCount ; $x++){
                    $nodeAtx = $node->childNodes()->get($x);
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
