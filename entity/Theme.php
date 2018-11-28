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
namespace webfiori\entity;
if(!defined('ROOT_DIR')){
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
}
/**
 * A base class that is used to construct website UI.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.2.1
 */
class Theme implements JsonI{
    /**
     * The directory where themes is located in.
     * @since 1.0
     */
    const THEMES_DIR = 'publish/themes';
    /**
     * An array that contains all loaded themes.
     * @var array
     * @since 1.0 
     */
    private static $loadedThemes = array();
    /**
     * An associative array that contains theme meta info.
     * @var array
     * @since 1.0 
     */
    private $themeMeta;
    /**
     * An array that contains the names of theme component files.
     * @var array
     * @since 1.0 
     */
    private $themeComponents;
    /**
     * The name of theme CSS files directory. 
     * @var string 
     * @since 1.0
     */
    private $cssDir;
    /**
     * The name of theme JavaScript files directory. 
     * @var string 
     * @since 1.0
     */
    private $jsDir;
    /**
     * The directory where theme images are stored.
     * @var string
     * @since 1.0 
     */
    private $imagesDir;
    /**
     * A callback function to call after the theme is loaded.
     * @var Function
     * @since 1.0 
     */
    private $afterLoaded;
    /**
     * An array of callback parameters.
     * @var array
     * @since 1.3 
     */
    private $afterLoadedParams;
    /**
     * A callback function to call after the theme is loaded.
     * @var Function
     * @since 1.2.1
     */
    private $beforeLoaded;
    /**
     * An array of callback parameters.
     * @var array
     * @since 1.2.1
     */
    private $beforeLoadedParams;
    /**
     * Creates new instance of the class using default values.
     */
    public function __construct() {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Initializing theme...');
        $this->themeMeta = array(
            'name'=>'',
            'url'=>'',
            'author'=>'',
            'author-url'=>'',
            'version'=>'1.0.0',
            'license'=>'',
            'license-url'=>'',
            'description'=>'',
            'directory'=>''
        );
        $this->setCssDirName('css');
        $this->setJsDirName('js');
        $this->setImagesDirName('images');
        $this->themeComponents = array();
        $this->afterLoaded = function(){};
        $this->afterLoadedParams = array();
        $this->beforeLoaded = function(){};
        $this->beforeLoadedParams = array();
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Adds a set of theme components to the theme.
     * @param array $arr An array that contains the names of components files 
     * (such as 'head.php').
     * @since 1.0
     */
    public function addComponents($arr) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Adding set of theme components...');
        foreach ($arr as $component){
            $this->addComponent($component);
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Returns an array which contains the names of theme components files.
     * @return array An array which contains the names of theme components files. 
     * The components of a theme are usually .php files.
     * @since 1.0
     */
    public function getComponents() {
        return $this->themeComponents;
    }
    /**
     * Adds a single component to the set of theme components.
     * @param string $componentName The name of the component file (such as 'head.php')
     * @since 1.0
     */
    public function addComponent($componentName) {
        Logger::logFuncCall(__METHOD__);
        $trimmed = trim($componentName);
        Logger::log('Checking if component is already added or not...');
        Logger::log('Component file name = \''.$trimmed.'\'.', 'debug');
        if(strlen($trimmed) != 0 && !in_array($trimmed, $this->themeComponents)){
            Logger::log('Adding the component...');
            $this->themeComponents[] = $trimmed;
            Logger::log('Component Added.');
        }
        else{
            Logger::log('Already added.');
        }
        Logger::logFuncReturn(__METHOD__);
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
        Logger::logFuncCall(__METHOD__);
        Logger::log('Theme name = \''.$themeName.'\' ('. gettype($themeName).').', $themeName);
        Logger::log('Validating theme name...');
        if($themeName === NULL){
            $themeName = SiteConfig::get()->getBaseThemeName();
            Logger::log('Given name is NULL. Using the theme \''.$themeName.'\'.', 'warning');
        }
        $themeToLoad = NULL;
        Logger::log('Checking if theme is already loaded...');
        if(self::isThemeLoaded($themeName)){
            Logger::log('Theme is already loaded.');
            $themeToLoad = self::$loadedThemes[$themeName];
        }
        else{
            Logger::log('No theme is loaded. Checking available themes...');
            $themes = self::getAvailableThemes();
            if(isset($themes[$themeName])){
                $themeToLoad = $themes[$themeName];
                Logger::log('Theme found. Added to the set of loaded themes.');
                array_push(self::$loadedThemes, $themeToLoad);
            }
            else{
                Logger::log('No theme was found which has the given name. An exception is thrown.', 'error');
                Logger::requestCompleted();
                throw new Exception('No such theme: \''.$themeName.'\'.');
            }
        }
        if(isset($themeToLoad)){
            $themeToLoad->invokeBeforeLoaded();
            Logger::log('Loading theme meta and components...');
            $themeDir = ROOT_DIR.'/'.self::THEMES_DIR.'/'.$themeToLoad->getDirectoryName();
            Logger::log('Theme directory: \''.$themeDir.'\'.', $themeName);
            Logger::log('Checking if the file \'theme.php\' exist in theme directory...');
            if(file_exists($themeDir.'/theme.php')){
                Logger::log('Loading the file using require_once...');
                require_once $themeDir.'/theme.php';
                Logger::log('Loading theme components...');
                foreach ($themeToLoad->getComponents() as $component){
                    Logger::log('Checking if file \''.$component.'\' exist...');
                    if(file_exists($themeDir.'/'.$component)){
                        Logger::log('Loading file using require_once...');
                        require_once $themeDir.'/'.$component;
                        Logger::log('Component loaded.');
                    }
                    else{
                        Logger::log('No file was found which represents the component. An exception is thrown.', 'error');
                        Logger::requestCompleted();
                        throw new Exception('Component \''.$component.'\' of the theme not found. Eather define it or remove it from the array of theme components.');
                    }
                }
                Logger::log('Theme loaded.');
                Logger::logFuncReturn(__METHOD__);
                return $themeToLoad;
            }
            else{
                Logger::log('The file \'theme.php\' was not found. An exception is thrown.', 'error');
                Logger::requestCompleted();
                throw new Exception('The file \'theme.php\' is missing from the theme with name = \''.$themeName.'\'');
            }
        }
        Logger::log('No theme was found which has the given name. An exception is thrown.','error');
        Logger::requestCompleted();
        throw new Exception('No such theme: \''.$themeName.'\'');
    }
    /**
     * Sets the value of the callback which will be called after theme is loaded.
     * @param Function $function The callback.
     * @param array $params An array of parameters which can be passed to the 
     * callback.
     * @since 1.0
     */
    public function setAfterLoaded($function,$params=array()) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if first parameter is callable...');
        if(is_callable($function)){
            Logger::log('It is callable. Callable updated.');
            $this->afterLoaded = $function;
            if(gettype($params) == 'array'){
                $this->afterLoadedParams = $params;
            }
        }
        else{
            Logger::log('It is not callable.');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets the value of the callback which will be called before theme is loaded.
     * @param Function $function The callback.
     * @param array $params An array of parameters which can be passed to the 
     * callback.
     * @since 1.2.1
     */
    public function setBeforeLoaded($function,$params=array()){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if first parameter is callable...');
        if(is_callable($function)){
            Logger::log('It is callable. Callable updated.');
            $this->beforeLoaded = $function;
            if(gettype($params) == 'array'){
                $this->beforeLoadedParams = $params;
            }
        }
        else{
            Logger::log('It is not callable.');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Fire the callback function.
     * @since 1.2.1
     */
    public function invokeBeforeLoaded(){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Firing before loaded event...');
        call_user_func($this->beforeLoaded, $this->beforeLoadedParams);
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Fire the callback function.
     * @since 1.0
     */
    public function invokeAfterLoaded(){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Firing after loaded event...');
        call_user_func($this->afterLoaded, $this->afterLoadedParams);
        Logger::logFuncReturn(__METHOD__);
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
     * @since 1.1 
     */
    public static function getAvailableThemes(){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Building an array of available themes...');
        $themes = array();
        $themesDirs = array_diff(scandir(ROOT_DIR.'/'. self::THEMES_DIR), array('..', '.'));
        foreach ($themesDirs as $dir){
            Logger::log('Checking if theme main file is exist...');
            $fullPath = ROOT_DIR.'/'.self::THEMES_DIR.'/'.$dir.'/theme.php';
            Logger::log('Path to theme main file: \''.$fullPath.'\'.', 'debug');
            if(file_exists($fullPath)){
                Logger::log('File found.');
                require $fullPath;
                if(isset($theme)){
                    $themes[$theme->getName()] = $theme;
                    unset($theme);
                }
            }
            else{
                Logger::log('File not found.');
            }
        }
        Logger::logFuncReturn(__METHOD__);
        return $themes;
    }
    /**
     * Sets the name of the theme.
     * @param string $name The name of the theme. Note that the name of the theme 
     * acts as the unique identifier for the theme.
     * @since 1.0
     */
    public function setName($name) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$name.'\'.', 'debug');
        if(strlen($name) != 0){
            $this->themeMeta['name'] = $name.'';
            Logger::log('Theme name updated.');
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Returns the name of the theme.
     * @return string The name of the theme. If it is not set, 
     * the function will return empty string.
     * @since 1.0
     */
    public function getName() {
        return $this->themeMeta['name'];
    }
    /**
     * Sets the URL of theme designer website. It can be the same as author URL.
     * @param string $url The URL to theme designer website.
     * @since 1.0
     */
    public function setUrl($url) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$url.'\'.', 'debug');
        if(strlen($url) > 0){
            Logger::log('Theme URL updated.');
            $this->themeMeta['url'] = $url.'';
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets the name of theme author.
     * @param string $author The name of theme author (such as 'Ibrahim BinAlshikh')
     * @since 1.0
     */
    public function setAuthor($author) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$author.'\'.', 'debug');
        if(strlen($author) > 0){
            Logger::log('Author name updated.');
            $this->themeMeta['author'] = $author.'';
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets the URL to the theme author. It can be the same as Theme URL.
     * @param string $authorUrl The URL to the author's website.
     * @since 1.0
     */
    public function setAuthorUrl($authorUrl) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$authorUrl.'\'.', 'debug');
        if(strlen($authorUrl) > 0){
            Logger::log('Author URL updated.');
            $this->themeMeta['author-url'] = $authorUrl.'';
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets the version number of the theme.
     * @param string $vNum Version number. The format of version number is 
     * usually like 'X.X.X' where the 'X' can be any number.
     * @since 1.0
     */
    public function setVersion($vNum) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$vNum.'\'.', 'debug');
        if(strlen($vNum) != 0){
            $this->themeMeta['version'] = $vNum.'';
            Logger::log('Version updated.');
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets the name of theme license.
     * @param string $text The name of theme license.
     * @since 1.0
     */
    public function setLicenseName($text) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$text.'\'.', 'debug');
        if(strlen($text) != 0){
            $this->themeMeta['license'] = $text.'';
            Logger::log('License name updated.');
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets a URL to the license where people can find more details about it.
     * @param string $url A URL to the license.
     * @since 1.0
     */
    public function setLicenseUrl($url) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$url.'\'.', 'debug');
        if(strlen($url) > 0){
            $this->themeMeta['license-url'] = $url.'';
            Logger::log('License URL updated.');
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets the description of the theme.
     * @param string $desc Theme description. Usually a short paragraph of two 
     * or 3 sentences.
     * @since 1.0
     */
    public function setDescription($desc) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$desc.'\'.', 'debug');
        if(strlen($desc) > 0){
            $this->themeMeta['description'] = $desc.'';
            Logger::log('Theme description updated.');
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets the name of the directory where all theme files are kept.
     * @param string $name The name of the directory. Usually, each theme 
     * must have a unique directory to prevent collision.
     * @since 1.0
     */
    public function setDirectoryName($name) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$name.'\'.', 'debug');
        if(strlen($name) != 0){
            $this->themeMeta['directory'] = $name.'';
            Logger::log('Theme directory updated.');
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Returns the name of the directory where all theme files are kept.
     * @return string The name of the directory where all theme files are kept. 
     * If it is not set, the function will return empty string.
     * @since 1.0
     */
    public function getDirectoryName() {
        return $this->themeMeta['directory'];
    }
    /**
     * Sets the name of the directory where theme images are kept.
     * @param string $name The name of the directory where theme images are kept. 
     * Note that it will be set only if the given name is not an empty string.
     * @since 1.0
     */
    public function setImagesDirName($name) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$name.'\'.', 'debug');
        if(strlen($name) != 0){
            $this->imagesDir = $name;
            Logger::log('Images directory updated.');
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Returns the name of the directory where theme images are kept.
     * @return string The name of the directory where theme images are kept. If 
     * the name of the directory was not set by the function <b>Theme::setImagesDirName()</b>, 
     * then the returned value will be 'images'.
     * @since 1.0
     */
    public function getImagesDirName() {
        return $this->imagesDir;
    }
    /**
     * Sets the name of the directory where theme JavaScript files are kept.
     * @param string $name The name of the directory where theme JavaScript files are kept. 
     * Note that it will be set only if the given name is not an empty string.
     * @since 1.0
     */
    public function setJsDirName($name) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$name.'\'.', 'debug');
        if(strlen($name) != 0){
            $this->jsDir = $name;
            Logger::log('JavaScript directory updated.');
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Returns the name of the directory where JavaScript files are kept.
     * @return string The name of the directory where theme JavaScript files kept. If 
     * the name of the directory was not set by the function <b>Theme::setJsDirName()</b>, 
     * then the returned value will be 'js'.
     * @since 1.0
     */
    public function getJsDirName() {
        return $this->jsDir;
    }
    /**
     * Sets the name of the directory where theme CSS files are kept.
     * @param string $name The name of the directory where theme CSS files are kept. 
     * Note that it will be set only if the given name is not an empty string.
     * @since 1.0
     */
    public function setCssDirName($name) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$name.'\'.', 'debug');
        if(strlen($name) != 0){
            $this->cssDir = $name;
            Logger::log('CSS directory updated.');
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Returns the name of the directory where CSS files are kept.
     * @return string The name of the directory where theme CSS files kept. If 
     * the name of the directory was not set by the function <b>Theme::setCssDirName()</b>, 
     * then the returned value will be 'css'.
     * @since 1.0
     */
    public function getCssDirName() {
        return $this->cssDir;
    }
    /**
     * Returns an array which contains all loaded themes.
     * @return array An array which contains all loaded themes.
     * @since 1.0
     */
    public static function getLoadedThemes(){
        return self::$loadedThemes;
    }
    /**
     * Returns theme version number.
     * @return string theme version number. The format if the version number 
     * is 'x.x.x' where 'x' can be any number. If it is not set, the 
     * function will return '1.0.0'
     * @since 1.1
     */
    public function getVersion() {
        return $this->themeMeta['version'];
    }
    /**
     * Returns the name of theme author.
     * @return string The name of theme author. If author name is not set, the 
     * function will return empty string.
     * @since 1.1
     */
    public function getAuthor() {
        return $this->themeMeta['author'];
    }
    /**
     * Returns the name of theme license.
     * @return string The name of theme license. If it is not set, 
     * the function will return empty string.
     */
    public function getLicenseName() {
        return $this->themeMeta['license'];
    }
    /**
     * Returns a URL which should contain a full version of theme license.
     * @return string A URL which contain a full version of theme license. 
     * If it is not set, the function will return empty string.
     * @since 1.1
     */
    public function getLicenseUrl(){
        return $this->themeMeta['license-url'];
    }
    /**
     * Returns the URL which takes the users to author's website.
     * @return string The URL which takes users to author's website. 
     * If author URL is not set, the function will return empty string.
     * @since 1.1
     */
    public function getAuthorUrl() {
        return $this->themeMeta['author-url'];
    }
    /**
     * Returns A URL which should point to theme website.
     * @return string A URL which should point to theme website. Usually, 
     * this one is the same as author URL.
     * If it is not set, the function will return empty string.
     * @since 1.1
     */
    public function getUrl() {
        return $this->themeMeta['url'];
    }
    /**
     * Returns the description of the theme.
     * @return string The description of the theme. If the description is not 
     * set, the function will return empty string.
     * @since 1.1
     */
    public function getDescription() {
        return $this->themeMeta['description'];
    }
    /**
     * 
     * @return JsonX
     */
    public function toJSON() {
        $j = new JsonX();
        $j->add('name', $this->getName());
        $j->add('version', $this->getVersion());
        $j->add('author', $this->getAuthor());
        $j->add('images-dir-name', $this->getImagesDirName());
        $j->add('css-dir-name', $this->getCssDirName());
        $j->add('js-dir-name', $this->getJsDirName());
        $j->add('components', $this->getComponents());
        return $j;
    }

}
