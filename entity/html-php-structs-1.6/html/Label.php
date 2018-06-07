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
 * A class that represents &lt;label&gt; tag.
 *
 * @author Ibrahim
 * @version 1.0
 */
class Label extends HTMLNode{
    /**
     * Creates a new label node with specific text on it.
     * @param string $text The text that will be displayed by the label. 
     * Default is empty string.
     * @since 1.0
     */
    public function __construct($text='') {
        parent::__construct('label');
        $textNode = new HTMLNode('', FALSE, TRUE);
        $textNode->setText($text);
        parent::addChild($textNode);
    }
    /**
     * Sets the text that will be displayed by the label.
     * @param string $text The text that will be displayed by the label.
     * @since 1.0
     */
    public function setText($text) {
        $this->children()->get(0)->setText($text);
    }
    /**
     * A function that does nothing.
     * @param type $node
     * @since 1.0
     */
    public function addChild($node) {
        
    }
}
