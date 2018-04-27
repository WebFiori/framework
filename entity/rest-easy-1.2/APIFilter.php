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
 * @version 1.0
 */
class APIFilter{
    /**
     * Supported input types.
     * @var array
     * @since 1.0
     */
    const TYPES = array(
        'string','integer','email','float',
    );
    /**
     * An array that will contains filtered data.
     * @var array
     * @since 1.0 
     */
    private $inputs;
    /**
     * Array that contains filter definitions.
     * @var array
     * @since 1.0 
     */
    private $defenitions = array();
    /**
     * Adds new value to filter from request body.
     * @param string $name The name of the variable.
     * @param string $type The type of the variable. It must be a value from the 
     * array <b>APIFilter::TYPES</b>.
     * @return boolean <b>TRUE</b> in case the filter is applied. <b>FALSE<b> 
     * otherwise.
     * @since 1.0
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
                }
            }
        }
        return FALSE;
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
     * Filter GET parameters.
     * @since 1.0
     */
    public function filterGET(){
        $this->inputs = filter_var_array($_GET, $this->defenitions, FALSE);
    }
    /**
     * Filter POST parameters.
     * @since 1.0
     */
    public function filterPOST(){
        $this->inputs = filter_var_array($_POST, $this->defenitions, FALSE);
    }
}

