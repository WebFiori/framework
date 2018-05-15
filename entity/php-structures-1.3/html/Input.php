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
 * A class that represents &lt;input&gt; tag;
 *
 * @author Ibrahim
 * @version 1.0
 */
class Input extends HTMLNode{
    /**
     * An array of supported input types.
     */
    const INPUT_TYPES = array('text','date','password','submit','checkbox','email','url','tel',
        'color','file','range','month','number','date-local','hidden','time','week','search');
    /**
     * An array of supported input modes.
     */
    const INPUT_MODES = array('none','text','decimal','numeric','tel','search','email','url');
    public function __construct($type='text') {
        parent::__construct('input', FALSE);
        $this->setType($type);
    }
    /**
     * Returns the value of the attribute 'type'.
     * @return string he value of the attribute 'type'.
     * @since 1.0
     */
    public function getType() {
        return $this->getAttributeValue('type');
    }
    /**
     * Sets the value of the attribute 'type'.
     * @param string $type The type of the input element.
     * It can be only a value from the array <b>Input::INPUT_TYPES</b>.
     * @since 1.0
     */
    public function setType($type) {
        $l = strtolower($type);
        if(in_array($l, Input::INPUT_TYPES)){
            $this->setAttribute('type', $l);
        }
    }
    /**
     * Sets the value of the attribute 'placeholder'.
     * @param string $text The value to set. The attribute can be 
     * set only if the type of the input is text or password or number.
     */
    public function setPlaceholder($text) {
        $iType = $this->getType();
        if($iType == 'password' || $iType == 'text' || $iType == 'number'){
            $this->setAttribute('placeholder', $text);
        }
    }
    /**
     * Sets the value of the attribute 'value'
     * @param string $text The value to set.
     * @since 1.0
     */
    public function setValue($text){
        $this->setAttribute('value', $text);
    }
    /**
     * Sets the value of the attribute 'list'
     * @param string $listId The ID of the element that will be acting 
     * as pre-defined list of elements. It cannot be set for hidden, file, 
     * checkbox and radio input types.
     * @since 1.0
     */
    public function setList($listId){
        $iType = $this->getType();
        if($iType != 'hidden' && 
                $iType != 'file' && 
                $iType != 'checkbox' && 
                $iType != 'radio'){
            $this->setAttribute('list', $listId);
        }
    }
    /**
     * Sets the value of the attribute 'min'.
     * @param string $min The value to set.
     * @since 1.0
     */
    public function setMin($min) {
        $this->setAttribute('min', $min);
    }
    /**
     * Sets the value of the attribute 'max'.
     * @param string $max The value to set.
     * @since 1.0
     */
    public function setMax($max) {
        $this->setAttribute('max', $max);
    }
    /**
     * Sets the value of the attribute 'minlength'.
     * @param string $length The value to set. The attribute value can be set only 
     * for text, email, search, tel and url input types.
     * @since 1.0
     */
    public function setMinLength($length){
        $iType = $this->getType();
        if($iType == 'text' || $iType == 'email' || $iType == 'search' || $iType == 'tel' || $iType == 'url'){
            $this->setAttribute('minlength', $length);
        }
    }
    /**
     * Sets the value of the attribute 'maxlength'.
     * @param string $length The value to set. The attribute value can be set only 
     * for text, email, search, tel and url input types.
     * @since 1.0
     */
    public function setMaxLength($length){
        $iType = $this->getType();
        if($iType == 'text' || $iType == 'email' || $iType == 'search' || $iType == 'tel' || $iType == 'url'){
            $this->setAttribute('maxlength', $length);
        }
    }
    /**
     * Sets the value of the attribute 'inputmode'.
     * @param string $mode The value to set. It must be a value from the array 
     * <b>Input::INPUT_MODES</b>.
     * @since 1.0
     */
    public function setInputMode($mode) {
        $lMode = strtolower($mode);
        if(in_array($lMode, Input::INPUT_MODES)){
            $this->setAttribute('inputmode', $lMode);
        }
    }
}
