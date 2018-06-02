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
 * A class that represents List Item node.
 *
 * @author Ibrahim
 * @version 1.1
 */
class ListItem extends HTMLNode{
    /**
     * A boolean value that is set to <b>TRUE</b> in case the list item only 
     * accepts text.
     * @var boolean
     * @since 1.1 
     */
    private $textOnly;
    /**
     * Constructs new list item
     * @param boolean $textOnly [Optional] Set to <b>TRUE</b> to make the list item 
     * accepts text only. Default is <b>FALSE</b>.
     * @param string $text [Optional] The text that will be displayed by the list 
     * item. Ignored if the parameter <b>$textOnly</b> is set to <b>FALSE</b>.
     * @since 1.0
     */
    public function __construct($textOnly=false,$text='') {
        parent::__construct('li', TRUE);
        $this->textOnly = $textOnly === TRUE ? TRUE : FALSE;
        $this->setText($text);
    }
    /**
     * Sets the text to display on the list item.
     * @param string $text The text to display. Only set if the node 
     * accepts text.
     * @since 1.1
     */
    public function setText($text) {
        if($this->isTextOnly()){
            if($this->childNodes()->get(0) != NULL){
                $this->childNodes()->get(0)->setText($text);
            }
            else{
                $textNode = new HTMLNode('', '', TRUE);
                $textNode->setText($text);
                parent::addChild($textNode);
            }
        }
    }
    /**
     * Checks if the node only accepts text or not.
     * @return boolean <b>TRUE</b> is returned if the node accepts text 
     * only.
     */
    public function isTextOnly() {
        return $this->textOnly;
    }
    /**
     * Adds new child node to the list item.
     * @param HTMLNode $node The node that will be added. Node that the 
     * function will work only if the list item allows things other 
     * than the text in its body.
     * @since 1.1
     */
    public function addChild($node) {
        if(!$this->isTextOnly()){
            parent::addChild($node);
        }
    }
}
