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
    header('HTTP/1.1 403 Forbidden');
    exit;
}
/**
 * Description of Theme
 *
 * @author Ibrahim
 */
class Theme {
    const THEMES_DIR = 'publish/themes';
    private static $loadedThemes = array();
    private $themeMeta;
    private $themeComponents;
    private $cssDir;
    private $jsDir;
    private $imagesDir;
    private $afterLoaded;
    public function __construct() {
        $this->themeMeta = array(
            'name'=>'',
            'url'=>'',
            'author'=>'',
            'author-url'=>'',
            'version'=>'1.0',
            'license'=>'n/a',
            'license-url'=>'',
            'description'=>'',
            'directory'=>''
        );
        $this->cssDir = 'css';
        $this->jsDir = 'js';
        $this->imagesDir = 'images';
        $this->themeComponents = array();
        $this->afterLoaded = function(){};
    }
    public function addComponents($arr) {
        foreach ($arr as $component){
            $this->addComponent($component);
        }
    }
    public function getComponents() {
        return $this->themeComponents;
    }
    public function addComponent($componentName) {
        array_push($this->themeComponents, $componentName);
    }
    /**
     * Loads a theme given its name.
     * @param string $themeName [Optional] The name of the theme as specified by the 
     * variable 'name' in theme definition. If the given name is <b>NULL</b>, the 
     * function will load the default theme as specified by the function 
     * <b>SiteConfig::getBaseThemeName()</b>.
     * @return Theme The function will return an object of type <b>Theme</b> once the 
     * theme is loaded. The object will contain all theme information.
     * @throws Exception The function will throw 
     * an exception if no theme was found which has the given name. Another case is 
     * when the file 'theme.php' of the theme is missing. 
     * Finally, an exception will be thrown if theme component is not found.
     * @since 1.0
     */
    public static function usingTheme($themeName=null) {
        if($themeName == NULL){
            $themeName = SiteConfig::get()->getBaseThemeName();
        }
        $themeToLoad = NULL;
        if(self::isThemeLoaded($themeName)){
            $themeToLoad = self::$loadedThemes[$themeName];
        }
        else{
            $themes = self::getAvailableThemes();
            if(isset($themes[$themeName])){
                $themeToLoad = $themes[$themeName];
                array_push(self::$loadedThemes, $themeToLoad);
            }
            else{
                throw new Exception('No such theme: \''.$themeName.'\'.');
            }
        }
        if(isset($themeToLoad)){
            $themeDir = ROOT_DIR.'/'.self::THEMES_DIR.'/'.$themeToLoad->getDirectoryName();
            if(file_exists($themeDir.'/theme.php')){
                require_once $themeDir.'/theme.php';
                foreach ($themeToLoad->getComponents() as $component){
                    if(file_exists($themeDir.'/'.$component)){
                        require_once $themeDir.'/'.$component;
                    }
                    else{
                        throw new Exception('Component \''.$component.'\' of the theme not found. Eather define it or remove it from the array of theme components.');
                    }
                }
                return $themeToLoad;
            }
            else{
                throw new Exception('The file \'theme.php\' is missing from the theme with name = \''.$themeName.'\'');
            }
        }
        throw new Exception('No such theme: \''.$themeName.'\'');
    }
    public function setAfterLoaded($function) {
        if(is_callable($function)){
            $this->afterLoaded = $function;
        }
    }
    public function invokeAfterLoaded(){
        call_user_func($this->afterLoaded);
    }

    /**
     * Checks if a theme is loaded or not given its name.
     * @param string $themeName The name of the theme.
     * @return boolean The function will return <b>TRUE</b> if 
     * the theme was found in the array of loaded themes. <b>FALSE</b> 
     * if not.
     * @since 1.0
     */
    public static function isThemeLoaded($themeName) {
        return isset(self::$loadedThemes[$themeName]) === TRUE;
    }
    /**
     * Returns an array that contains the meta data of all available themes. 
     * @return array An associative array that contains all themes information. The key 
     * is the theme name and the value is theme info.
     * @throws Exception If the constant 'ROOT_DIR' is not defined.
     * @since 1.1 
     */
    public static function getAvailableThemes(){
        $GLOBALS['ADD_TO_LOADED'] = FALSE;
        $themes = array();
        $themesDirs = array_diff(scandir(ROOT_DIR.'/'. self::THEMES_DIR), array('..', '.'));
        foreach ($themesDirs as $dir){
            include ROOT_DIR.'/'.self::THEMES_DIR.'/'.$dir.'/theme.php';
            if(isset($theme)){
                $themes[$theme->getName()] = $theme;
                unset($theme);
            }
        }
        return $themes;
    }
    public function setName($name) {
        $this->themeMeta['name'] = $name.'';
    }
    public function getName() {
        return $this->themeMeta['name'];
    }
    public function setUrl($url) {
        $this->themeMeta['url'] = $url.'';
    }
    public function setAuthor($author) {
        $this->themeMeta['author'] = $author.'';
    }
    public function setAuthorUrl($authorUrl) {
        $this->themeMeta['author-url'] = $authorUrl.'';
    }
    public function setVersion($vNum) {
        $this->themeMeta['version'] = $vNum.'';
    }
    public function setLicenseName($text) {
        $this->themeMeta['license'] = $text.'';
    }
    public function setLicenseUrl($url) {
        $this->themeMeta['license-url'] = $url.'';
    }
    public function setDescription($desc) {
        $this->themeMeta['description'] = $desc.'';
    }
    public function setDirectoryName($name) {
        $this->themeMeta['directory'] = $name.'';
    }
    public function getDirectoryName() {
        return $this->themeMeta['directory'];
    }
    public function setImagesDirName($name) {
        $this->imagesDir = $name;
    }
    public function getImagesDirName() {
        return $this->imagesDir;
    }
    public function setJsDirName($name) {
        $this->jsDir = $name;
    }
    public function getJsDirName() {
        return $this->jsDir;
    }
    public function setCssDirName($name) {
        $this->cssDir = $name;
    }
    public function getCssDirName() {
        return $this->cssDir;
    }
    public static function getLoadedThemes(){
        return self::$loadedThemes;
    }
}
