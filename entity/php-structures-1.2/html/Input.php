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
 * Description of Input
 *
 * @author Ibrahim
 */
class Input extends HTMLNode{
    const INPUT_TYPES = array('text','date','password','submit','checkbox','email','url','tel',
        'color','file','range','month','number','date-local','hidden','time','week','search');
    const INPUT_MODES = array('none','text','decimal','numeric','tel','search','email','url');
    public function __construct($type='text') {
        parent::__construct('input', FALSE);
        $this->setType($type);
    }
    
    public function getType() {
        return $this->getAttributeValue('type');
    }
    
    public function setType($type) {
        $l = strtolower($type);
        if(in_array($l, Input::INPUT_TYPES)){
            $this->setAttribute('type', $l);
        }
    }
    
    public function setPlaceholder($text) {
        $iType = $this->getType();
        if($iType == 'password' || $iType == 'text'){
            $this->setAttribute('placeholder', $text);
        }
    }
    
    public function setValue($text){
        $this->setAttribute('value', $text);
    }
    
    public function setList($listId){
        $iType = $this->getType();
        if($iType != 'hidden' && 
                $iType != 'file' && 
                $iType != 'checkbox' && 
                $iType != 'radio'){
            $this->setAttribute('list', $listId);
        }
    }
    
    public function setMin($min) {
        $this->setAttribute('min', $min);
    }
    
    public function setMax($max) {
        $this->setAttribute('max', $max);
    }
    
    public function setMinLength($length){
        $iType = $this->getType();
        if($iType == 'text' || $iType == 'email' || $iType == 'search' || $iType == 'tel' || $iType == 'url'){
            $this->setAttribute('minlength', $length);
        }
    }
    
    public function setMaxLength($length){
        $iType = $this->getType();
        if($iType == 'text' || $iType == 'email' || $iType == 'search' || $iType == 'tel' || $iType == 'url'){
            $this->setAttribute('maxlength', $length);
        }
    }
    
    public function setInputMode($mode) {
        $lMode = strtolower($mode);
        if(in_array($lMode, Input::INPUT_MODES)){
            $this->setAttribute('inputmode', $lMode);
        }
    }
}
