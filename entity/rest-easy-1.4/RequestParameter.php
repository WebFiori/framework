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
 * A class that represents request parameter.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.2
 */
class RequestParameter implements JsonI{
    /**
     * The type of the data the parameter will represents.
     * @var string
     * @since 1.0 
     */
    private $type;
    /**
     * The name of the parameter.
     * @var string
     * @since 1.0 
     */
    private $name;
    /**
     * Indicates wither the attribute is optional or not.
     * @var boolean <b>TRUE</b> if the parameter is optional.
     * @since 1.0
     */
    private $isOptional;
    /**
     * The default value that will be used in case of parameter filter 
     * failure.
     * @var type 
     * @since 1.1
     */
    private $default;
    /**
     * The minimum value. Used if the parameter type is numeric.
     * @var type 
     * @since 1.1
     */
    private $minVal;
    /**
     * The maximum value. Used if the parameter type is numeric.
     * @var type 
     * @since 1.1
     */
    private $maxVal;
    /**
     * The description of the parameter.
     * @var string
     * @since 1.0 
     */
    private $desc;
    /**
     * A callback that is used to make a custom filtered value.
     * @var Fulnction
     * @since 1.2 
     */
    private $customFilterFunc;
    /**
     * A boolean value that is set to true in case the 
     * basic filter will be applied before custom one.
     * @var boolean
     * @since 1.2 
     */
    private $applyBasicFilter;
    /**
     * Sets the description of the parameter.
     * @param sting $desc Parameter description. Used to help front-end to identify 
     * the use of the API.
     * @since 1.1
     */
    public function setDescription($desc) {
        $this->desc = $desc;
    }
    /**
     * Returns the description of the parameter.
     * @return string|NULL The description of the parameter. If the description is 
     * not set, the function will return <b>NULL</b>.
     * @since 1.1
     */
    public function getDescription() {
        return $this->desc;
    }
    /**
     * Creates new instance of <b>RequestParameter</b>
     * @param string $name The name of the parameter as it appears in the request body.
     * @param string $type The type of the data that will be in the parameter (integer, 
     * string, email etc...). It must be a value from the array <b>APIFilter::TYPES</b>. 
     * If the given type is invalid, 'string' is used.
     * @param boolean $isOptional Set to <b>TRUE</b> if the parameter is optional.
     */
    public function __construct($name,$type='string',$isOptional=false) {
        if(!$this->setName($name)){
            $this->setName('a-parameter');
        }
        $this->isOptional = $isOptional;
        if(!$this->setType($type)){
            $this->type = 'string';
        }
        $this->applyBasicFilter = FALSE;
    }
    /**
     * Returns the minimum numeric value the parameter can accept.
     * @return int|NULL The minimum numeric value the parameter can accept. 
     * If the request parameter type is not numeric, the function will return 
     * <b>NULL</b>.
     * @since 1.1
     */
    public function getMinVal() {
        return $this->minVal;
    }
    /**
     * Returns the maximum numeric value the parameter can accept.
     * @return int|NULL The maximum numeric value the parameter can accept. 
     * If the request parameter type is not numeric, the function will return 
     * <b>NULL</b>.
     * @since 1.1
     */
    public function getMaxVal() {
        return $this->maxVal;
    }
    /**
     * Sets the minimum value.
     * @param int $val The minimum value to set. The value will be updated 
     * only if:
     * <ul>
     * <li>The request parameter type is numeric ('integer').</li>
     * <li>The given value is less than <b>RequestParameter::getMaxVal()</b></li>
     * </ul>
     * @return boolean The function will return <b>TRUE</b> once the minimum value 
     * is updated. <b>FALSE</b> if not.
     * @since 1.1
     */
    public function setMinVal($val) {
        $type = $this->getType();
        if($type == 'integer'){
            $max = $this->getMaxVal();
            if($max != NULL && $val < $max){
                $this->minVal = $val;
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Sets the maximum value.
     * @param int $val The maximum value to set. The value will be updated 
     * only if:
     * <ul>
     * <li>The request parameter type is numeric ('integer').</li>
     * <li>The given value is greater than <b>RequestParameter::getMinVal()</b></li>
     * </ul>
     * @return boolean The function will return <b>TRUE</b> once the maximum value 
     * is updated. <b>FALSE</b> if not.
     * @since 1.1
     */
    public function setMaxVal($val) {
        $type = $this->getType();
        if($type == 'integer'){
            $min = $this->getMinVal();
            if($min != NULL && $val > $min){
                $this->maxVal = $val;
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Sets a default value for the parameter to use if the parameter is 
     * not provided.
     * @param type $val The value to set.
     * @since 1.1
     */
    public function setDefault($val) {
        $this->default = $val;
    }
    /**
     * Returns the default value to use in case the parameter is 
     * not provided.
     * @return type The default value to use in case the parameter is 
     * not provided.
     * @since 1.1
     */
    public function getDefault() {
        return $this->default;
    }
    /**
     * Sets the type of the parameter.
     * @param string $type The type of the parameter. It must be a value 
     * form the array <b>APIFilter::TYPES</b>.
     * @return boolean <b>TRUE</b> is returned if the type is updated. <b>FALSE</b> 
     * if not.
     * @since 1.1
     */
    public function setType($type) {
        $sType = strtolower($type);
        if(in_array($sType, APIFilter::TYPES)){
            $this->type = $type;
            if($sType == 'integer'){
                $this->minVal = defined('PHP_INT_MIN') ? PHP_INT_MIN : ~PHP_INT_MAX;
                $this->maxVal = PHP_INT_MAX;
            }
            else{
                $this->maxVal = NULL;
                $this->minVal = NULL;
            }
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Sets the name of the parameter.
     * @param string $name The name of the parameter. A valid parameter name must 
     * follow the following rules:
     * <ul>
     * <li>It can contain the letters [A-Z] and [a-z].</li>
     * <li>It can contain the numbers [0-9].</li>
     * <li>It can have the character '-' and the character '_'.</li>
     * </ul>
     * @return boolean If the given name is valid, the function will return 
     * <b>TRUE</b> once the name is set. <b>FALSE</b> is returned if the given 
     * name is invalid.
     * @since 1.0
     */
    public function setName($name){
        $name .= '';
        $len = strlen($name);
        if($len != 0){
            if(strpos($name, ' ') === FALSE){
                    for ($x = 0 ; $x < $len ; $x++){
                        $ch = $name[$x];
                        if($ch == '_' || $ch == '-' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){
                            
                        }
                        else{
                            return FALSE;
                        }
                    }
                    $this->name = $name;
                    return TRUE;
                }
        }
        return FALSE;
    }
    /**
     * Returns the name of the parameter.
     * @return string The name of the parameter.
     * @since 1.0
     */
    public function getName(){
        return $this->name;
    }
    /**
     * Returns a boolean value that indicates if the parameter is optional or not.
     * @return boolean <b>TRUE</b> if the parameter is optional and <b>FALSE</b> 
     * if not.
     * @since 1.0
     */
    public function isOptional(){
        return $this->isOptional;
    }
    /**
     * Returns the type of the parameter.
     * @return string The type of the parameter (Such as 'string', 'email', 'integer').
     * @since 1.0
     */
    public function getType(){
        return $this->type;
    }
    /**
     * Returns a JsonX object that represents the request parameter.
     * @return JsonX An object of type <b>JsonX</b>.
     * @since 1.0
     */
    public function toJSON() {
        $json = new JsonX();
        $json->add('name', $this->name);
        $json->add('type', $this->getType());
        $json->add('description', $this->getDescription());
        $json->add('is-optional', $this->isOptional());
        if($this->getDefault() != NULL){
            $json->add('default', $this->getDefault());
        }
        if($this->getMinVal() != NULL){
            $json->add('min-val', $this->getMinVal());
        }
        if($this->getMaxVal() != NULL){
            $json->add('max-val', $this->getMaxVal());
        }
        return $json;
    }
    /**
     * Sets a callback function to work as a filter for the inputs.
     * @param Function $function A callback function. The callback function 
     * will have two parameters passed to it. The first one is an associative 
     * array that contains the not-filtered value and the value filtered 
     * using basic filter. The values are contained in two 
     * indices: <b>'original-value'</b> and <b>'basic-filter-result'</b>. 
     * The second parameter is an object of type <b>RequestParameter</b> 
     * which contains original information for the filter. The function 
     * must be implemented in a way that makes it return <b>FALSE</b> if the 
     * parameter has invalid value. If the parameter is filtered and 
     * was validated, the function must return the valid and filtered 
     * value.
     * @param boolean $applyBasicFilter [Optional] If set to <b>TRUE</b>, 
     * the basic filter will be applied to the parameter. Default 
     * is <b>TRUE</b>.
     * @since 1.2
     */
    public function setCustomFilterFunction($function,$applyBasicFilter=true) {
        if(is_callable($function)){
            $this->customFilterFunc = $function;
        }
        $this->applyBasicFilter = $applyBasicFilter === TRUE ? TRUE : FALSE;
    }
    /**
     * Checks if we need to apply basic filter or not 
     * before applying custom filter callback.
     * @return boolean The function will return <b>TRUE</b> 
     * if the basic filter will be applied before applying custom filter.
     * @since 1.2
     */
    public function applyBasicFilter() {
        return $this->applyBasicFilter;
    }
    /**
     * Returns the function that is used as a custom filter 
     * for the parameter.
     * @return Function | NULL The function that is used as a custom filter 
     * for the parameter. If not set, the function will return <b>NULL</b>.
     * @since 1.2
     */
    public function getCustomFilterFunction() {
        return $this->customFilterFunc;
    }
}
