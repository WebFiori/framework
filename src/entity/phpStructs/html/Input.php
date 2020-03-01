<?php
/*
 * The MIT License
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh, phpStructs.
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
use phpStructs\html\HTMLNode;
/**
 * A class that represents any input element.
 *
 * @author Ibrahim
 * @version 1.0.2
 */
class Input extends HTMLNode{
    /**
     * An array of supported input types.
     * This array has the following values:
     * <ul>
     * <li>text</li>
     * <li>date</li>
     * <li>password</li>
     * <li>submit</li>
     * <li>checkbox</li>
     * <li>email</li>
     * <li>url</li>
     * <li>tel</li>
     * <li>color</li>
     * <li>file</li>
     * <li>range</li>
     * <li>month</li>
     * <li>number</li>
     * <li>date-local</li>
     * <li>hidden</li>
     * <li>time</li>
     * <li>week</li>
     * <li>search</li>
     * <li>select</li>
     * <li>textarea</li>
     * <li>radio</li>
     * </ul>
     * @since 1.0
     */
    const INPUT_TYPES = ['text','date','password','submit','checkbox','email','url','tel',
        'color','file','range','month','number','date-local','hidden','time','week','search', 
        'select','textarea','radio'];
    /**
     * An array of supported input modes.
     * The array contains the following values:
     * <ul>
     * <li>none</li>
     * <li>text</li>
     * <li>decimal</li>
     * <li>numeric</li>
     * <li>tel</li>
     * <li>search</li>
     * <li>email</li>
     * <li>url</li>
     * </ul>
     * @since 1.0
     */
    const INPUT_MODES = ['none','text','decimal','numeric','tel','search','email','url'];
    /**
     * Creates new instance of the class.
     * @param string $type The type of the input element. If the 
     * given type is not in the array Input::INPUT_TYPES, 'text' 
     * will be used by default.
     * @since 1.0
     */
    public function __construct($type='text') {
        parent::__construct();
        $lType = strtolower(trim($type));
        if($lType == 'select' || $lType == 'textarea'){
            parent::setNodeName($lType);
        }
        else{
            parent::setNodeName('input');
            if(!$this->setType($lType)){
                $this->setType('text');
            }
        }
    }
    /**
     * A method that does nothing.
     * @param string $name
     * @return boolean The method will always return false.
     * @since 1.0.2
     */
    public function setNodeName($name) {
        return false;
    }
    /**
     * Returns the value of the attribute 'type'.
     * @return string|null The value of the attribute 'type'. For 'textarea' and 
     * select, this method will return null.
     * @since 1.0
     */
    public function getType() {
        return $this->getAttributeValue('type');
    }
    /**
     * Sets the value of the attribute 'type'.
     * @param string $type The type of the input element. If the 
     * given type is not in the array Input::INPUT_TYPES, The 
     * method will not update the type.
     * It can be only a value from the array Input::INPUT_TYPES. Also, if 
     * the input type is 'textarea' or 'select', this attribute will never 
     * be set using this method.
     * @return boolean If input type is updated, the method will return true. 
     * If input type is not updated, the method will return false.
     * @since 1.0
     */
    public function setType($type) {
        $nodeName = $this->getNodeName();
        if($nodeName == 'input'){
            $l = strtolower(trim($type));
            if(in_array($l, Input::INPUT_TYPES) && $l != 'textarea' && $l != 'select'){
                return $this->setAttribute('type', $l);
            }
        }
        return false;
    }
    /**
     * Sets a placeholder text for the input element if it supports it.
     * A placeholder can be set for the following input types:
     * <ul>
     * <li>text</li>
     * <li>textarea</li>
     * <li>password</li>
     * <li>number</li>
     * <li>search</li>
     * <li>email</li>
     * <li>url</li>
     * </ul>
     * @param string|null $text The value to set. The attribute can be 
     * set only if the type of the input is text or password or number. If null 
     * is given, the attribute will be unset If it was set.
     * @return boolean If placeholder is set, the method will return true. If 
     * it is not set, the method will return false.
     */
    public function setPlaceholder($text) {
        if($text !== null){
            $iType = $this->getType();
            if($iType == 'password' || 
               $iType == 'text' || 
               $iType == 'number' || 
               $iType == 'search' || 
               $iType == 'email' || 
               $iType == 'url' || 
               $this->getNodeName() == 'textarea'){
                return $this->setAttribute('placeholder', $text);
            }
        }
        else{
            if($this->hasAttribute('placeholder')){
                $this->removeAttribute('placeholder');
                return true;
            }
        }
        return false;
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
     * checkbox, textarea, select and radio input types.
     * @return boolean If datalist is set, the method will return true. If 
     * it is not set, the method will return false.
     * @since 1.0
     */
    public function setList($listId){
        $iType = $this->getType();
        if($iType != 'hidden' && 
                $iType != 'file' && 
                $iType != 'checkbox' && 
                $iType != 'radio' && ($this->getNodeName() != 'textarea' || $this->getNodeName() == 'select')){
            $this->setAttribute('list', $listId);
            return true;
        }
        return false;
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
     * Input::INPUT_MODES.
     * @return boolean If the attribute value is set or updated, the method will 
     * return true. False if not.
     * @since 1.0
     */
    public function setInputMode($mode) {
        $lMode = strtolower(trim($mode));
        if(in_array($lMode, Input::INPUT_MODES)){
            return $this->setAttribute('inputmode', $lMode);
        }
        return false;
    }
    /**
     * Adds new child node.
     * The node will be added only if the type of the node is 
     * &lt;select&gt; and the given node is of type &lt;option&gt; or 
     * &lt;optgroup&gt;. Also, if the input type is &lt;textarea&gt; and 
     * the given node is a text node, it will be added.
     * @param HTMLNode $node The node that will be added.
     * @since 1.0.1
     */
    public function addChild($node) {
        if($node instanceof HTMLNode){
            if($this->getNodeName() == 'select' && ($node->getNodeName() == 'option' || 
                    $node->getNodeName() == 'optgroup')){
                parent::addChild($node);
            }
            else if($this->getNodeName() == 'textarea' && $node->getNodeName() == '#TEXT'){
                parent::addChild($node);
            }
        }
    }
    /**
     * Adds an option to the input element which has the type 'select'.
     * @param array $options An associative array that contains select options. 
     * The array must have at least the following indices:
     * <ul>
     * <li>label: A label that will be displayed to the user.</li>
     * <li>value: The value that will be set for the attribute 'value'.</li>
     * <li>attributes: An associative array of attributes which can be set 
     * for the option.</li>
     * </ul>
     * In addition to the two indices, the array can have additional index. 
     * The index name is 'attributes'. This index can have an associative array 
     * of attributes which will be set for the option. The key will act as the 
     * attribute name and the value of the key will act as the value of the 
     * attribute.
     * @since 1.0.1
     */
    public function addOption($options=[]) {
        if($this->getNodeName() == 'select'){
            if(gettype($options) == 'array' && isset($options['value']) && isset($options['label'])){
                $option = new HTMLNode('option');
                $option->setAttribute('value', $options['value']);
                $option->addTextNode($options['label'],false);
                if(isset($options['attributes'])){
                    foreach ($options['attributes'] as $attr => $value) {
                        $option->setAttribute($attr, $value);
                    }
                }
                $this->addChild($option);
            }
        }
    }
    /**
     * Adds multiple options at once to an input element of type 'select'.
     * @param array $arrayOfOpt An associative array of options. 
     * The key will act as the 'value' attribute and 
     * the value of the key will act as the label for the option. Also, 
     * it is possible that the value of the key is a sub-associative array that 
     * contains only two indices: 
     * <ul>
     * <li>label: A label for the option.</li>
     * <li>attributes: An optional associative array of attributes for the option. 
     * The key will act as the 
     * attribute name and the value of the key will act as the value of the 
     * attribute.</li>
     * </ul>
     * @since 1.0.1
     */
    public function addOptions($arrayOfOpt) {
        if(gettype($arrayOfOpt) == 'array'){
            foreach ($arrayOfOpt as $value => $lblOrOptions){
                if(gettype($lblOrOptions) == 'array'){
                    $attrs = isset($lblOrOptions['attributes']) ? $lblOrOptions['attributes'] : array();
                    $this->addOption([
                        'value'=>$value,
                        'label'=>$lblOrOptions['label'],
                        'attributes'=>$attrs
                    ]);
                }
                else{
                    $this->addOption([
                        'value'=>$value,
                        'label'=>$lblOrOptions
                    ]);
                }
            }
        }
    }
    /**
     * Adds an 'optgroup' child element.
     * @param array $optionsGroupArr An associative array that contains 
     * group info. The array must have the following indices:
     * <ul>
     * <li>label: The label of the group.</li>
     * <li>attributes: An optional associative array that contains group attributes.</li>
     * <li>options: A sub associative array that contains group 
     * options. The key will act as the 'value' attribute and 
     * the value of the key will act as the label for the option. Also, 
     * it is possible that the value of the key is a sub-associative array that 
     * contains only two indices: 
     * <ul>
     * <li>label: A label for the option.</li>
     * <li>attributes: An optional associative array of attributes. 
     * The key will act as the 
     * attribute name and the value of the key will act as the value of the 
     * attribute.</li></li>
     * </ul>
     * @since 1.0.1
     */
    public function addOptionsGroup($optionsGroupArr) {
        if($this->getNodeName() == 'select'){
            if(gettype($optionsGroupArr) == 'array'){
                if(isset($optionsGroupArr['label']) && isset($optionsGroupArr['options'])){
                    $optGroup = new HTMLNode('optgroup');
                    $optGroup->setAttribute('label', $optionsGroupArr['label']);
                    if(isset($optionsGroupArr['attributes']) && gettype($optionsGroupArr['attributes']) == 'array'){
                        foreach ($optionsGroupArr['attributes'] as $k => $v){
                            $optGroup->setAttribute($k, $v);
                        }
                    }
                    foreach ($optionsGroupArr['options'] as $value => $labelOrOptions){
                        if(gettype($labelOrOptions) == 'array'){
                            if(isset($labelOrOptions['label'])){
                                $o = new HTMLNode('option');
                                $o->setAttribute('value', $value);
                                $o->addTextNode($labelOrOptions['label'],false);
                                if(isset($labelOrOptions['attributes'])){
                                    foreach ($labelOrOptions['attributes'] as $attr => $v){
                                        $o->setAttribute($attr, $v);
                                    }
                                }
                                $optGroup->addChild($o);
                            }
                        }
                        else{
                            $o = new HTMLNode('option');
                            $o->setAttribute('value', $value);
                            $o->addTextNode($labelOrOptions,false);
                            $optGroup->addChild($o);
                        }
                    }
                    $this->addChild($optGroup);
                }
            }
        }
    }
}
