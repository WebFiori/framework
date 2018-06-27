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
 * A class used to filter request parameters.
 * @author Ibrahim Ali <ibinshikh@hotmail.com>
 * @version 1.2
 */
class APIFilter{
    /**
     * Supported input types.
     * @var array
     * @since 1.0
     */
    const TYPES = array(
        'string','integer','email','float','url','boolean'
    );
    /**
     * An array that will contains filtered data.
     * @var array
     * @since 1.0 
     */
    private $inputs;
    /**
     * An array that contains non-filtered data.
     * @var array
     * @since 1.2 
     */
    private $nonFilteredInputs;
    /**
     * Array that contains filter definitions.
     * @var array
     * @since 1.0 
     */
    private $defenitions = array();
    private $paramDefs = array();
    /**
     * Adds new value to filter from request body.
     * @param string $name The name of the variable.
     * @param string $type The type of the variable. It must be a value from the 
     * array <b>APIFilter::TYPES</b>.
     * @return boolean <b>TRUE</b> in case the filter is applied. <b>FALSE<b> 
     * otherwise.
     * @since 1.0
     * @deprecated since version 1.0 Use <b>APIFilter::addRequestPaameter()</b> instead.
     */
    public function addParameter($name,$type){
        $sType = strtolower($type);
        if(strlen($name) != 0){
            if(!in_array($name, $this->defenitions)){
                if(in_array($type, self::TYPES)){
                    if($sType == 'integer'){
                        $this->defenitions[$name] = FILTER_SANITIZE_NUMBER_INT;
                        return TRUE;
                    }
                    else if($sType == 'string'){
                        $this->defenitions[$name] = FILTER_SANITIZE_STRING;
                        return TRUE;
                    }
                    else if($sType == 'email'){
                        $this->defenitions[$name] = FILTER_SANITIZE_EMAIL;
                        return TRUE;
                    }
                    else if($sType == 'float'){
                        $this->defenitions[$name] = FILTER_SANITIZE_NUMBER_FLOAT;
                        return TRUE;
                    }
                    else{
                        $this->defenitions[$name] = FILTER_DEFAULT;
                    }
                }
            }
        }
        return FALSE;
    }
    /**
     * Adds a new request parameter to the filter.
     * @param RequestParameter $reqParam The request parameter that will be added.
     * @since 1.1
     */
    public function addRequestPaameter($reqParam) {
        if($reqParam instanceof RequestParameter){
            $attribute = array(
                'parameter'=>$reqParam,
                'filters'=>array(),
                'options'=>array('options'=>array())
            );
            if($reqParam->getDefault() !== NULL){
                $attribute['options']['options']['default'] = $reqParam->getDefault();
            }
            if($reqParam->getCustomFilterFunction() != NULL){
                $attribute['options']['filter-func'] = $reqParam->getCustomFilterFunction();
            }
            $paramType = $reqParam->getType();
            if($paramType == 'integer'){
                if($reqParam->getMaxVal() !== NULL){
                    $attribute['options']['options']['max_range'] = $reqParam->getMaxVal();
                }
                if($reqParam->getMinVal() !== NULL){
                    $attribute['options']['options']['min_range'] = $reqParam->getMinVal();
                }
                array_push($attribute['filters'], FILTER_SANITIZE_NUMBER_INT);
                array_push($attribute['filters'], FILTER_VALIDATE_INT);
            }
            else if($paramType == 'float'){
                array_push($attribute['filters'], FILTER_SANITIZE_NUMBER_FLOAT);
            }
            else if($paramType == 'email'){
                array_push($attribute['filters'], FILTER_SANITIZE_EMAIL);
                array_push($attribute['filters'], FILTER_VALIDATE_EMAIL);
            }
            else if($paramType == 'url'){
                array_push($attribute['filters'], FILTER_SANITIZE_URL);
                array_push($attribute['filters'], FILTER_VALIDATE_URL);
            }
            else{
                array_push($attribute['filters'], FILTER_DEFAULT);
            }
            array_push($this->paramDefs, $attribute);
        }
    }
    /**
     * 
     * @param type $boolean
     * @return boolean|string
     * @since 1.1
     */
    private function filterBoolean($boolean) {
        $booleanLwr = strtolower($boolean);
        
        $boolTypes = array(
            't'=>TRUE,
            'f'=>FALSE,
            'yes'=>TRUE,
            'no'=>FALSE,
            '-1'=>FALSE,
            '1'=>TRUE,
            '0'=>FALSE,
            'true'=>TRUE,
            'false'=>FALSE,
            'on'=>TRUE,
            'off'=>FALSE,
            'y'=>TRUE,
            'n'=>FALSE,
            'ok'=>TRUE);
        if(isset($boolTypes[$booleanLwr])){
            return $boolTypes[$booleanLwr];
        }
        return 'INV';
    }
    /**
     * Returns the array that contains request inputs.
     * @return array|NULL The array that contains request inputs. If no data was 
     * filtered, the function will return <b>NULL</b>.
     * @since 1.0
     */
    public function getInputs(){
        return $this->inputs;
    }
    /**
     * Returns the array that contains request inputs without filters applied.
     * @return array The array that contains request inputs.
     * @since 1.2
     */
    public function getNonFiltered(){
        return $this->nonFilteredInputs;
    }

    /**
     * Filter GET parameters.
     * @since 1.0
     */
    public function filterGET(){
        foreach ($this->paramDefs as $def){
            $name = $def['parameter']->getName();
            if(isset($_GET[$name])){
                $this->nonFilteredInputs[$name] = $_GET[$name];
                if(isset($def['options']['filter-func'])){
                    $filteredValue = '';
                    $arr = array(
                        'original-value'=>$_GET[$name],
                    );
                    if($def['parameter']->applyBasicFilter() === TRUE){
                        if($def['parameter']->getType() == 'boolean'){
                            $filteredValue = $this->filterBoolean(filter_input(INPUT_GET, $name));
                        }
                        else{
                            $filteredValue = filter_input(INPUT_GET, $name);
                            foreach ($def['filters'] as $val) {
                                $filteredValue = filter_var($filteredValue, $val, $def['options']);
                            }
                            if($filteredValue == FALSE){
                                $filteredValue = 'INV';
                            }
                        }
                        $arr['basic-filter-result'] = $filteredValue;
                    }
                    else{
                        $filteredValue = 'INV';
                        $arr['basic-filter-result'] = 'NOT APLICABLE';
                    }
                    $r = call_user_func($def['options']['filter-func'],$arr,$def['parameter']);
                    if($r === NULL){
                        $this->inputs[$name] = FALSE;
                    }
                    else{
                        $this->inputs[$name] = $r;
                    }
                    if($this->inputs[$name] == FALSE && $def['parameter']->getType() != 'boolean'){
                        $this->inputs[$name] = 'INV';
                    }
                }
                else{
                    if($def['parameter']->getType() == 'boolean'){
                        $this->inputs[$name] = $this->filterBoolean(filter_input(INPUT_GET, $name));
                    }
                    else{
                        $this->inputs[$name] = filter_input(INPUT_GET, $name);
                        foreach ($def['filters'] as $val) {
                            $this->inputs[$name] = filter_var($this->inputs[$name], $val, $def['options']);
                        }
                        if($this->inputs[$name] == FALSE){
                            $this->inputs[$name] = 'INV';
                        }
                    }
                }
            }
            else{
                if($def['parameter']->isOptional()){
                    $this->inputs[$name] = $def['parameter']->getDefault();
                }
            }
        }
    }
    /**
     * Filter POST parameters.
     * @since 1.0
     */
    public function filterPOST(){
        foreach ($this->paramDefs as $def){
            $name = $def['parameter']->getName();
            if(isset($_POST[$name])){
                $this->nonFilteredInputs[$name] = $_POST[$name];
                if(isset($def['options']['filter-func'])){
                    $filteredValue = '';
                    $arr = array(
                        'original-value'=>$_POST[$name],
                    );
                    if($def['parameter']->applyBasicFilter() === TRUE){
                        if($def['parameter']->getType() == 'boolean'){
                            $filteredValue = $this->filterBoolean(filter_input(INPUT_POST, $name));
                        }
                        else{
                            $filteredValue = filter_input(INPUT_POST, $name);
                            foreach ($def['filters'] as $val) {
                                $filteredValue = filter_var($filteredValue, $val, $def['options']);
                            }
                            if($filteredValue == FALSE){
                                $filteredValue = 'INV';
                            }
                        }
                        $arr['basic-filter-result'] = $filteredValue;
                    }
                    else{
                        $filteredValue = 'INV';
                        $arr['basic-filter-result'] = 'NOT APLICABLE';
                    }
                    $r = call_user_func($def['options']['filter-func'],$arr,$def['parameter']);
                    if($r === NULL){
                        $this->inputs[$name] = FALSE;
                    }
                    else{
                        $this->inputs[$name] = $r;
                    }
                    if($this->inputs[$name] == FALSE && $def['parameter']->getType() != 'boolean'){
                        $this->inputs[$name] = 'INV';
                    }
                }
                else{
                    if($def['parameter']->getType() == 'boolean'){
                        $this->inputs[$name] = $this->filterBoolean(filter_input(INPUT_POST, $name));
                    }
                    else{
                        $this->inputs[$name] = filter_input(INPUT_POST, $name);
                        foreach ($def['filters'] as $val) {
                            $this->inputs[$name] = filter_var($this->inputs[$name], $val, $def['options']);
                        }
                        if($this->inputs[$name] === FALSE){
                            $this->inputs[$name] = 'INV';
                        }
                    }
                }
            }
            else{
                if($def['parameter']->isOptional()){
                    $this->inputs[$name] = $def['parameter']->getDefault();
                }
            }
        }
    }
    /**
     * Clears filter variables.
     * @since 1.1
     */
    public function clear() {
        $this->defenitions = array();
        $this->paramDefs = array();
        $this->inputs = NULL;
        $this->nonFilteredInputs = NULL;
    }
}

