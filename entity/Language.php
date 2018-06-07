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
 * A class that is used to define language variables.
 *
 * @author Ibrahim
 * @version 1.0
 */
class Language {
    /**
     * A constant for left to right writing direction.
     * @var string 
     * @since 1.0
     */
    const DIR_LTR = 'ltr';
    /**
     * A constant for right to left writing direction.
     * @var string 
     * @since 1.0
     */
    const DIR_RTL = 'rtl';
    /**
     * An array that contains language definition.
     * @var type 
     */
    private $languageVars;
    /**
     * Creates new instance of the class.
     * @param string $dir 'ltr' or 'rtl'.
     * @param array $initials An initial array of directories.
     * @since 1.0
     */
    public function __construct($dir='ltr',$initials=array()) {
        $this->languageVars = array();
        if($this->setWritingDir($dir)){
            $this->setWritingDir('ltr');
        }
        foreach ($initials as $val){
            $this->createDirectory($val);
        }
    }
    /**
     * Creates a sub array to define language variables.
     * @param string $param A string that looks like a 
     * directory. For example, if the given string is 'general', 
     * an array with key name 'general' will be created. Another example is 
     * if the given string is 'pages/login', two arrays will be created. The 
     * top one will have the key value 'pages' and another one inside 
     * the pages array with key value 'login'.
     * @since 1.0
     */
    public function createDirectory($param) {
        if(gettype($param) == 'string' && strlen($param) != 0){
            $trim = trim($param, '/');
            $subSplit = explode('/', $trim);
            if(count($subSplit) != 0){
                if(isset($this->languageVars[$subSplit[0]])){
                    $this->_create($subSplit, $this->languageVars[$subSplit[0]],1);
                }
                else{
                    $this->languageVars[$subSplit[0]] = array();
                    $this->_create($subSplit, $this->languageVars[$subSplit[0]],1);
                }
            }
        }
    }
    private function _create($subs,&$top,$index){
        $count = count($subs);
        if($index < $count){
            if(isset($top[$subs[$index]])){
                return $this->_create($subs, $top[$subs[$index]],++$index);
            }
            else{
                $top[$subs[$index]] = array();
                return $this->_create($subs, $top[$subs[$index]],++$index);
            }
        }
    }
    /**
     * 
     * @param string $dir
     * @param array $arr
     * @since 1.0
     */
    public function setMultiple($dir,$arr=array()) {
        foreach ($arr as $k => $v){
            $this->set($dir, $k, $v);
        }
    }
    /**
     * 
     * @param string $dir
     * @param string $varName
     * @param string $varValue
     * @return boolean
     * @since 1.0
     */
    public function set($dir,$varName,$varValue) {
        if(gettype($dir) == 'string' && strlen($dir) != 0){
            if(gettype($varName) == 'string' && strlen($varName) != 0){
                $trim = trim($dir, '/');
                $subSplit = explode('/', $trim);
                if(count($subSplit) == 1){
                    if(isset($this->languageVars[$subSplit[0]])){
                        $this->languageVars[$subSplit[0]][$varName] = $varValue;
                        return TRUE;
                    }
                }
                else{
                    if(isset($this->languageVars[$subSplit[0]])){
                        return $this->_set($subSplit, $this->languageVars[$subSplit[0]],$varName,$varValue, 1);
                    }
                }
            }
        }
    }
    
    private function _set($subs,&$top,$var,$val,$index) {
        $count = count($subs);
        if($index + 1 == $count){
            if(isset($top[$subs[$index]])){
                $top[$subs[$index]][$var] = $val;
                return TRUE;
            }
        }
        else{
            if(isset($top[$subs[$index]])){
                return $this->_set($subs,$top[$subs[$index]],$var,$val, ++$index);
            }
        }
        return FALSE;
    }
    /**
     * 
     * @param string $name
     * @return string
     */
    public function get($name) {
        if(gettype($name) == 'string'){
            $trim = trim($name, '/');
            $subSplit = explode('/', $trim);
            if(count($subSplit) == 1){
                if(isset($this->languageVars[$subSplit[0]])){
                    return $this->languageVars[$subSplit[0]];
                }
            }
            else{
                if(isset($this->languageVars[$subSplit[0]])){
                    return $this->_get($subSplit, $this->languageVars[$subSplit[0]], 1);
                }
            }
        }
        return NULL;
    }
    private function _get(&$subs,&$top,$index){
        $count = count($subs);
        if($index + 1 == $count){
            if(isset($top[$subs[$index]])){
                return $top[$subs[$index]];
            }
        }
        else{
            if(isset($top[$subs[$index]])){
                return $this->_get($subs, $top[$subs[$index]], ++$index);
            }
        }
        return NULL;
    }
    /**
     * 
     * @param type $dir
     * @return boolean
     * @since 1.0
     */
    public function setWritingDir($dir) {
        $lDir = strtolower($dir);
        if($lDir == self::DIR_LTR || $lDir == self::DIR_RTL){
            $this->languageVars['dir'] = $lDir;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * 
     * @return string
     * @since 1.0
     */
    public function getWritingDir() {
        return $this->languageVars['dir'];
    }
    /**
     * 
     * @return array
     * @since 1.0
     */
    public function getLanguageVars() {
        return $this->languageVars;
    }
}