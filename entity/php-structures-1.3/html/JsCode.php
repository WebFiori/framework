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
 * A node that represents in line javascript code that can be inserted on a 
 * head node.
 *
 * @author Ibrahim
 * @version 1.0
 */
class JsCode extends HTMLNode{
    /**
     * Creates a new instance of the class.
     */
    public function __construct() {
        parent::__construct('script', TRUE, FALSE);
        parent::setAttribute('type', 'text/javascript');
    }
    /**
     * Adds new line of JS code into the body.
     * @param string $jsCode JavaScript code.
     * @since 1.0
     */
    public function addCode($jsCode) {
        $textNode = new HTMLNode('', FALSE, TRUE);
        $textNode->setText($jsCode);
        parent::addChild($textNode);
    }
    /**
     * A function that does nothing.
     * @param type $node
     * @since 1.0
     */
    public function addChild($node) {
        
    }
    /**
     * A function that does nothing.
     * @param type $param
     * @param type $param2
     * @since 1.0
     */
    public function setAttribute($param,$param2='') {
        
    }
    /**
     * A function that does nothing.
     * @since 1.0
     * @param type $val
     */
    public function setClassName($val) {
        
    }
    /**
     * * A function that does nothing.
     * @since 1.0
     * @param type $val
     */
    public function setName($val) {
        
    }
    /**
     * A function that does nothing.
     * @since 1.0
     * @param type $val
     */
    public function setTabIndex($val) {
        
    }
    /**
     * A function that does nothing.
     * @since 1.0
     * @param type $text
     */
    public function setText($text) {
        
    }
    /**
     * A function that does nothing.
     * @since 1.0
     * @param type $val
     */
    public function setTitle($val) {
        
    }
    /**
     * A function that does nothing.
     * @since 1.0
     * @param type $val
     */
    public function setWritingDir($val) {
        
    }
    
}
