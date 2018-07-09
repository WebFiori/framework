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
if(!defined('ROOT_DIR')){
    http_response_code(403);
    die('{"message":"Forbidden"}');
}
/**
 * A class that is used to define language variables.
 *
 * @author Ibrahim
 * @version 1.1
 */
class Language {
    /**
     * An associative array that contains loaded languages.
     * @var array The key of the array represents two 
     * characters language code. The index will contain an object of type <b>Language</b>.
     * <b>Language</b>.
     * @since 1.1 
     */
    private static $loadedLangs = array();
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
     * Returns an associative array that contains an objects of type <b>Language</b>.
     * @return array The key of the array represents two 
     * characters language code. The index will contain an object of type <b>Language</b>.
     * @since 1.1
     */
    public static function getLoadedLangs(){
        return self::$loadedLangs;
    }
    /**
     * Loads a language file based on language code.
     * @param string $langCode A two digits language code (such as 'ar').
     * @throws Exception An exception will be thrown if no language file 
     * was found that matches the given language code. Language files must 
     * have the name 'Language_XX' where 'XX' is language code. Also the function 
     * will throw an exception when the variable 'ROOT_DIR' is not defined.
     * @return Language an object of type <b>Language</b> is returned if 
     * the language was loaded.
     * @since 1.1
     */
    public static function loadTranslation($langCode='EN'){
        if(defined('ROOT_DIR')){
            $uLangCode = strtoupper($langCode);
            $langFile = ROOT_DIR.'/entity/langs/Language_'.$uLangCode.'.php';
            if(file_exists($langFile)){
                require $langFile;
                return self::$loadedLangs[$uLangCode];
            }
            else{
                throw new Exception('Unable to load translation file. The file \''.$langFile.'\' does not exists.');
            }
        }
        else{
            throw new Exception('Unable to load translation file. ROOT_DIR is undefined.');
        }
    }
    
    /**
     * Creates new instance of the class.
     * @param string $dir 'ltr' or 'rtl'.
     * @param string $code Language code (such as 'AR').
     * @param array $initials An initial array of directories.
     * @since 1.0
     */
    public function __construct($dir='ltr',$code='XX',$initials=array()) {
        $this->languageVars = array();
        if(!$this->setCode($code)){
            $this->setCode('XX');
        }
        if(!$this->setWritingDir($dir)){
            $this->setWritingDir('ltr');
        }
        foreach ($initials as $val){
            $this->createDirectory($val);
        }
    }
    /**
     * Sets the language code.
     * @param string $code Language code (such as 'AR').
     * @return boolean The function will return <b>TRUE</b> if the language 
     * code is set. If not set, the function will return <b>FALSE</b>.
     * @since 1.1
     */
    public function setCode($code) {
        if(strlen($code) == 2){
            if(isset(self::$loadedLangs[$this->getCode()])){
                unset(self::$loadedLangs[$this->getCode()]);
            }
            $this->languageVars['code'] = strtoupper($code);
            self::$loadedLangs[$this->getCode()] = $this;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns the language code.
     * @return string Language code in upper case (such as 'AR'). If language 
     * code is not set, default is returned which is 'XX'.
     * @since 1.1
     */
    public function getCode() {
        if(isset($this->languageVars['code'])){
            return $this->languageVars['code'];
        }
        return 'XX';
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
     * Sets multiple language variables.
     * @param string $dir A string that looks like a 
     * directory. 
     * @param array $arr An associative array. The key will act as the variable 
     * and the value of the key will act as the variable value.
     * @since 1.0
     */
    public function setMultiple($dir,$arr=array()) {
        foreach ($arr as $k => $v){
            $this->set($dir, $k, $v);
        }
    }
    /**
     * Sets or updates a language variable.
     * @param string $dir A string that looks like a 
     * directory. 
     * @param string $varName The name of the variable.
     * @param string $varValue The value of the variable.
     * @return boolean The function will return <b>TRUE</b> if the variable is set. 
     * Other than that, the function will return <b>FALSE</b>.
     * @since 1.0
     */
    public function set($dir,$varName,$varValue) {
        if(gettype($dir) == 'string' && strlen($dir) != 0){
            $varName = ''.$varName;
            if(strlen($varName) != 0){
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
        return FALSE;
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
     * Returns the value of a language variable.
     * @param string $name A directory to the language variable (such as 'pages/login/login-label').
     * @return string|array|NULL 
     * @since 1.0
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
     * Sets language writing direction.
     * @param string $dir 'ltr' or 'rtl'.
     * @return boolean The function will return <b>TRUE</b> if the language 
     * writing direction is updated. The only case that the function 
     * will return <b>FALSE</b> is when the writing direction is invalid (
     * Any value other than 'ltr' and 'rtl').
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
     * Returns language writing direction.
     * @return string 'ltr' or 'rtl'.
     * @since 1.0
     */
    public function getWritingDir() {
        return $this->languageVars['dir'];
    }
    /**
     * Returns an associative array that contains language definition.
     * @return array An associative array that contains language definition.
     * @since 1.0
     */
    public function getLanguageVars() {
        return $this->languageVars;
    }
}