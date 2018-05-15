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
 * A class that represents a paragraph element.
 *
 * @author Ibrahim
 * @version 1.0
 */
class PNode extends HTMLNode{
    const ALLOWED_CHILDS = array('b','br','abbr','dfn','i','em','span','img',
        'big','small','kbd','samp','code','script');
    public function __construct() {
        parent::__construct('p', TRUE);
    }
    /**
     * Appends new text to the body of the paragraph.
     * @param string $text The text that will be added.
     * @since 1.0
     */
    public function addText($text) {
        if(strlen($text) != 0){
            $textNode = new HTMLNode('', FALSE, TRUE);
            $textNode->setText($text);
            $this->addChild($textNode);
        }
    }
    /**
     * Adds new child node.
     * @param HTMLNode $node The node that will be added. The paragraph element 
     * can only accept the addition of inline HTML elements.
     */
    public function addChild($node) {
        if($node instanceof HTMLNode){
            if(in_array($node->getName(), PNode::ALLOWED_CHILDS) || $node->isTextNode()){
                parent::addChild($node);
            }
        }
    }
    /**
     * Adds a 'br' element to the body of the paragraph.
     * @since 1.0
     */
    public function addLineBreak() {
        $this->addChild(new Br());
    }
}
