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
 * A class that represents &lt;a&gt; tag with text only.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class LinkNode extends HTMLNode{
    /**
     * Constructs a new instance of the class
     * @param string $href The link.
     * @param string $label The label to display.
     * @param string $target [optional] The value to set for the attribute 'target'.
     */
    public function __construct($href,$label,$target='') {
        parent::__construct('a', TRUE, FALSE);
        $this->setAttribute('href',$href);
        $this->setAttribute('target',$target);
        $textNode = new HTMLNode('', FALSE, TRUE);
        $textNode->setText($label);
        $this->addChild($textNode);
    }
    /**
     * Sets the value of the property 'href' of the link tag.
     * @param string $link The value to set.
     * @since 1.0
     */
    public function setHref($link) {
        $this->setAttribute('href', $link);
    }
    
    private function addChild($node) {
        parent::addChild($node);
    }
    /**
     * Sets the value of the property 'target' of the link tag.
     * @param string $name The value to set.
     * @since 1.0
     */
    public function setTarget($name){
        $this->setAttribute('target', $name);
    }
    /**
     * Sets the text that will be seen by the user.
     * @param string $text The text to set.
     * @since 1.0
     */
    public function setText($text){
        $this->childNodes()->get(0)->setText($text);
    }
}
