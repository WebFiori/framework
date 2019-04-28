<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity\langs;
use Exception;
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die('<!DOCTYPE html><html><head><title>Forbidden</title></head><body>'
    . '<h1>403 - Forbidden</h1><hr><p>Direct access not allowed.</p></body></html>');
}
/**
 * A class that is can be used to make the application ready for 
 * Internationalization.
 * In order to create a language file, the developer must extend this class. 
 * The language class must be added to the namespace 'webfiori/entity/langs' and the name 
 * of language file must be 'LanguageXX.php' where 'XX' are two characters that 
 * represents language code.
 * @author Ibrahim
 * @version 1.2.1
 */
class Language {
    /**
     * An associative array that contains loaded languages.
     * @var array The key of the array represents two 
     * characters language code. The index will contain an object of type <b>Language</b>.
     * 'Language'.
     * @since 1.1 
     */
    private static $loadedLangs = array();
    /**
     * An attribute that will be set to 'true' if the language 
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
     * Returns a reference to an associative array that contains an objects of 
     * type 'Language'.
     * @return array The key of the array represents two 
     * characters language code. The index will contain an object of type 'Language'.
     * @since 1.1
     */
    public static function &getLoadedLangs(){
        return self::$loadedLangs;
    }
    /**
     * Loads a language file based on language code.
     * @param string $langCode A two digits language code (such as 'ar').
     * @throws Exception An exception will be thrown if no language file 
     * was found that matches the given language code. Language files must 
     * have the name 'LanguageXX.php' where 'XX' is language code. Also the function 
     * will throw an exception when the translation file is loaded but no object 
     * of type 'Language' was stored in the set of loaded translations.
     * @return Language an object of type 'Language' is returned if 
     * the language was loaded.
     * @since 1.1
     */
    public static function &loadTranslation($langCode){
        $uLangCode = strtoupper(trim($langCode));
        if(isset(self::$loadedLangs[$uLangCode])){
            return self::$loadedLangs[$uLangCode];
        }
        else{
            $langClassName = 'webfiori\entity\langs\Language'.$uLangCode;
            if(class_exists($langClassName)){
                $class = new $langClassName();
                if($class instanceof Language){
                    if(isset(self::$loadedLangs[$uLangCode])){
                        return self::$loadedLangs[$uLangCode];
                    }
                    else{
                        throw new Exception('The translation file was found. But no object of type \'Language\' is stored. Make sure that the parameter '
                                . '$addtoLoadedAfterCreate is set to true when creating the object.');
                    }
                }
                else{
                    throw new Exception('A language class for the language \''.$uLangCode.'\' was found. But it is not a sub class of \'Language\'.');
                }
            }
            else{
                throw new Exception('No language class was found for the language \''.$uLangCode.'\'.');
            }
        }
    }
    /**
     * Unload translation based on its language code.
     * @param string $langCode A two digits language code (such as 'ar').
     * @return boolean If the translation file was unloaded, the method will 
     * return true. If not, the method will return false.
     * @since 1.2 
     */
    public static function unloadTranslation($langCode){
        $uLangCode = strtoupper(trim($langCode));
        if(isset(self::$loadedLangs[$uLangCode])){
            unset(self::$loadedLangs[$uLangCode]);
            return true;
        }
        return false;
    }
    /**
     * Creates new instance of the class.
     * @param string $dir 'ltr' or 'rtl'. Default is 'ltr'.
     * @param string $code Language code (such as 'AR'). Default is 'XX'
     * @param array $initials An initial array of directories.
     * @param boolean $addtoLoadedAfterCreate If set to true, the language object that 
     * will be created will be added to the set of loaded languages. Default is true.
     * @since 1.0
     */
    public function __construct($dir='ltr',$code='XX',$addtoLoadedAfterCreate=true) {
        $this->languageVars = array();
        $this->loadLang = $addtoLoadedAfterCreate === true ? true : false;
        if(!$this->setCode($code)){
            $this->setCode('XX');
        }
        if(!$this->setWritingDir($dir)){
            $this->setWritingDir('ltr');
        }
    }
    /**
     * Checks if the language is added to the set of loaded languages or not.
     * @return boolean The function will return true if the language is added to 
     * the set of loaded languages.
     * @since 1.2
     */
    public function isLoaded() {
        return $this->loadLang;
    }
    /**
     * Sets the code of the language.
     * @param string $code Language code (such as 'AR').
     * @return boolean The function will return true if the language 
     * code is set. If not set, the function will return false.
     * @since 1.1
     */
    public function setCode($code) {
        $trimmedCode = strtoupper(trim($code));
        if(strlen($trimmedCode) == 2){
            if($trimmedCode[0] >= 'A' && $trimmedCode[0] <= 'Z' && $trimmedCode[1] >= 'A' && $trimmedCode[1] <= 'Z'){
                $oldCode = $this->getCode();
                if($this->isLoaded()){
                    if(isset(self::$loadedLangs[$oldCode])){
                        unset(self::$loadedLangs[$oldCode]);
                    }
                }
                $this->languageVars['code'] = $trimmedCode;
                if($this->isLoaded()){
                    self::$loadedLangs[$trimmedCode] = &$this;
                }
                return true;
            }
        }
        return false;
    }
    /**
     * Returns the language code that the object represents.
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
        $trim00 = trim($param);
        $trim01 = trim($trim00,'/');
        if(strlen($trim01) != 0){
            $subSplit = explode('/', $trim01);
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
    /**
     * Creates a sub-array for defining language variables given initial set 
     * of variables.
     * @param string $dir A string that looks like a 
     * directory.
     * @param array $labels An associative array. The key will act as the variable 
     * name and the value of the key will act as the variable value.
     * @since 1.2.1
     */
    public function createAndSet($dir,$labels) {
        $this->createDirectory($dir);
        $this->setMultiple($dir, $labels);
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
     * name and the value of the key will act as the variable value.
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
     * @return boolean The function will return <b>true</b> if the variable is set. 
     * Other than that, the function will return <b>false</b>.
     * @since 1.0
     */
    public function set($dir,$varName,$varValue) {
        $dirTrimmed = trim($dir);
        $varTrimmed = trim($varName);
        if(strlen($dirTrimmed) != 0){
            if(strlen($varTrimmed) != 0){
                $trim = trim($dirTrimmed, '/');
                $subSplit = explode('/', $trim);
                if(count($subSplit) == 1){
                    if(isset($this->languageVars[$subSplit[0]])){
                        $this->languageVars[$subSplit[0]][$varTrimmed] = $varValue;
                        return true;
                    }
                }
                else{
                    if(isset($this->languageVars[$subSplit[0]])){
                        return $this->_set($subSplit, $this->languageVars[$subSplit[0]],$varTrimmed,$varValue, 1);
                    }
                }
            }
        }
        return false;
    }
    
    private function _set($subs,&$top,$var,$val,$index) {
        $count = count($subs);
        if($index + 1 == $count){
            if(isset($top[$subs[$index]])){
                $top[$subs[$index]][$var] = $val;
                return true;
            }
        }
        else{
            if(isset($top[$subs[$index]])){
                return $this->_set($subs,$top[$subs[$index]],$var,$val, ++$index);
            }
        }
        return false;
    }
    /**
     * Returns the value of a language variable.
     * @param string $name A directory to the language variable (such as 'pages/login/login-label').
     * @return string|array If the given directory represents a label, the 
     * function will return its value. If it represents an array, the array will 
     * be returned. If nothing was found, the returned value will be the passed 
     * value to the function. 
     * @since 1.0
     */
    public function get($name) {
        $trimmed = trim($name);
        $toReturn = trim($trimmed, '/');
        $trim = $toReturn;
        $subSplit = explode('/', $trim);
        if(count($subSplit) == 1){
            if(isset($this->languageVars[$subSplit[0]])){
                $toReturn = $this->languageVars[$subSplit[0]];
            }
        }
        else{
            if(isset($this->languageVars[$subSplit[0]])){
                $val = $this->_get($subSplit, $this->languageVars[$subSplit[0]], 1);
                if($val !== null){
                    $toReturn = $val;
                }
            }
        }
        return $toReturn;
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
        return null;
    }
    /**
     * Sets language writing direction.
     * @param string $dir 'ltr' or 'rtl'. Letters case does not matter.
     * @return boolean The function will return <b>true</b> if the language 
     * writing direction is updated. The only case that the function 
     * will return <b>false</b> is when the writing direction is invalid (
     * Any value other than 'ltr' and 'rtl').
     * @since 1.0
     */
    public function setWritingDir($dir) {
        $lDir = strtolower(trim($dir));
        if($lDir == self::DIR_LTR || $lDir == self::DIR_RTL){
            $this->languageVars['dir'] = $lDir;
            return true;
        }
        return false;
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
     * Returns an associative array that contains language variables definition.
     * @return array An associative array that contains language variables definition. 
     * @since 1.0
     */
    public function getLanguageVars() {
        return $this->languageVars;
    }
}