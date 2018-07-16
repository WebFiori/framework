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
header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
/**
 * A class that is used to define language variables.
 *
 * @author Ibrahim
 * @version 1.2
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
     * An attribute that will be set to <b>TRUE</b> if the language 
     * is added to the set of loaded languages.
     * @var boolean
     * @since 1.2 
     */
    private $loadLang;
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
     * will throw an exception when the translation file is loaded but no object 
     * of type <b>Language</b> was stored in the set of loaded translations.
     * @return Language an object of type <b>Language</b> is returned if 
     * the language was loaded.
     * @since 1.1
     */
    public static function loadTranslation($langCode='EN'){
        $uLangCode = strtoupper($langCode);
        $langFile = ROOT_DIR.'/entity/langs/Language_'.$uLangCode.'.php';
        if(file_exists($langFile)){
            require $langFile;
            if(isset(self::$loadedLangs[$uLangCode])){
                return self::$loadedLangs[$uLangCode];
            }
            else{
                throw new Exception('The translation file was found. But no object of type \'Language\' is stored.');
            }
        }
        else{
            throw new Exception('Unable to load translation file. The file \''.$langFile.'\' does not exists.');
        }
    }
    /**
     * Unload translation based on its language code.
     * @param string $langCode  two digits language code (such as 'ar').
     * @since 1.2 
     */
    public static function unloadTranslation($langCode){
        $uLangCode = strtoupper($langCode);
        if(isset(self::$loadedLangs[$uLangCode])){
            unset(self::$loadedLangs[$langCode]);
        }
    }
    /**
     * Creates new instance of the class.
     * @param string $dir [Optional] 'ltr' or 'rtl'. Default is 'ltr'.
     * @param string $code [Optional] Language code (such as 'AR'). Default is 'XX'
     * @param array $initials [Optional] An initial array of directories.
     * @param boolean $addtoLoadedAfterCreate [Optional] If set to <b>TRUE</b>, the language object that 
     * will be created will be added to the set of loaded languages. Default is <b>TRUE</b>.
     * @since 1.0
     */
    public function __construct($dir='ltr',$code='XX',$initials=array(),$addtoLoadedAfterCreate=true) {
        $this->languageVars = array();
        $this->loadLang = $addtoLoadedAfterCreate === TRUE ? TRUE : FALSE;
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
     * Checks if the language is added to the set of loaded languages.
     * @return boolean The function will return <b>TRUE</b> if the language is added to 
     * the set of loaded languages.
     * @since 1.2
     */
    public function isLoaded() {
        return $this->loadLang;
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
            if($this->isLoaded()){
                if(isset(self::$loadedLangs[$this->getCode()])){
                    unset(self::$loadedLangs[$this->getCode()]);
                }
            }
            $this->languageVars['code'] = strtoupper($code);
            if($this->isLoaded()){
                self::$loadedLangs[$this->getCode()] = $this;
            }
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