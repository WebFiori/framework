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
 * A node that represents in line JavaScript code that can be inserted on a 
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
        parent::__construct('script');
        parent::setAttribute('type', 'text/javascript');
    }
    /**
     * Adds new line of JS code into the body.
     * @param string $jsCode JavaScript code.
     * @since 1.0
     */
    public function addCode($jsCode) {
        parent::addChild(self::createTextNode($jsCode));
    }
    /**
     * A method that does nothing.
     * @param type $node
     * @since 1.0
     */
    public function addChild($node) {
        
    }
    /**
     * Sets a value for an attribute.
     * @param string $name The name of the attribute. If the attribute does not 
     * exist, it will be created. If already exists, its value will be updated. 
     * If the attribute name is 'type', nothing will happen, 
     * the attribute will never be created.
     * @param string $val The value of the attribute. Default is empty string.
     * @since 1.0
     */
    public function setAttribute($name,$val='') {
        if($name != 'type'){
            parent::setAttribute($name, $val);
        }
    }
    /**
     * A method that does nothing.
     * @since 1.0
     * @param type $val
     */
    public function setClassName($val) {
        
    }
    /**
     * * A method that does nothing.
     * @since 1.0
     * @param type $val
     */
    public function setName($val) {
        
    }
    /**
     * A method that does nothing.
     * @since 1.0
     * @param type $val
     */
    public function setTabIndex($val) {
        
    }
    /**
     * A method that does nothing.
     * @since 1.0
     * @param type $text
     */
    public function setText($text) {
        
    }
    /**
     * A method that does nothing.
     * @since 1.0
     * @param type $val
     */
    public function setTitle($val) {
        
    }
    /**
     * A method that does nothing.
     * @since 1.0
     * @param type $val
     */
    public function setWritingDir($val) {
        
    }
    
}