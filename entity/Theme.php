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
use jsonx\JsonI;
use webfiori\conf\SiteConfig;
use Exception;
/**
 * A base class that is used to construct web site UI.
 * 
 * @author Ibrahim
 * @version 1.2.2
 */
abstract class Theme implements JsonI{
    /**
     * An optional base URL.
     * This URL is used by the tag 'base' to fetch page resources.
     * @var string
     * @since 1.2.2 
     */
    private $baseUrl;
    /**
     * The directory where themes are located in.
     * @since 1.0
     */
    const THEMES_DIR = 'themes';
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
     * The default values will be set as follows:
     * <ul>
     * <li>Theme name will be an empty string.</li>
     * <li>Theme URL will be an empty string.</li>
     * <li>Author name will be an empty string.</li>
     * <li>Author URL will be an empty string.</li>
     * <li>Theme version will be set to '1.0.0'</li>
     * <li>Theme license will be an empty string.</li>
     * <li>License URL will be an empty string.</li>
     * <li>Theme description will be an empty string.</li>
     * <li>Theme directory name will be an empty string.</li>
     * <li>Theme CSS directory name will be set to 'css'</li>
     * <li>Theme JS directory name will be set to 'js'</li>
     * <li>Theme images directory name will be set to 'images'</li>
     * </ul>
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
     * Returns the base URL that will be used by the theme.
     * The URL is used by the HTML tag 'base' to fetch page resources. 
     * If the URL is not set by the developer, the method will return the 
     * URL that is returned by the method SiteConfig::getBaseURL().
     * @return string The base URL that will be used by the theme.
     */
    public function getBaseURL(){
        if($this->baseUrl !== NULL){
            return $this->baseUrl;
        }
        else{
            return SiteConfig::getBaseURL();
        }
    }
    /**
     * Sets The base URL that will be used by the theme.
     * This URL is used by the HTML tag 'base' to fetch page resources. The 
     * given string must be non-empty string in order to set.
     * @param string $url The base URL that will be used by the theme.
     */
    public function setBaseURL($url) {
        if(strlen(trim($url)) > 0){
            $this->baseUrl = $url;
        }
    }
    /**
     * Adds a set of theme components to the theme.
     * Theme components are a set of PHP files that must exist inside theme 
     * directory. The developer can create any number of components and add 
     * them to the theme.
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
     * Theme components are a set of PHP files that must exist inside theme 
     * directory.
     * @return array An array which contains the names of theme components files.
     * @since 1.0
     */
    public function getComponents() {
        return $this->themeComponents;
    }
    /**
     * Adds a single component to the set of theme components.
     * Theme components are a set of PHP files that must exist inside theme 
     * directory.
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
     * If the given name is NULL, the method will load the default theme as 
     * specified by the method SiteConfig::getBaseThemeName().
     * @param string $themeName The name of the theme. 
     * @return Theme The method will return an object of type Theme once the 
     * theme is loaded. The object will contain all theme information.
     * @throws Exception The method will throw 
     * an exception if no theme was found which has the given name.
     * @since 1.0
     */
    public static function usingTheme($themeName=null) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Theme name = \''.$themeName.'\' ('. gettype($themeName).').', $themeName);
        Logger::log('Validating theme name...');
        if($themeName === NULL){
            $themeName = SiteConfig::getBaseThemeName();
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
                self::$loadedThemes[$themeName] = $themeToLoad;
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
        Logger::log('No theme was found which has the given name. An exception is thrown.','error');
        Logger::requestCompleted();
        throw new Exception('No such theme: \''.$themeName.'\'.');
    }
    /**
     * Sets the value of the callback which will be called after theme is loaded.
     * @param callable $function The callback.
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
     * @param callback $function The callback.
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
     * Fire the callback function which should be called before loading the theme.
     * This method must not be used by the developers. It is called automatically 
     * when the theme is being loaded.
     * @since 1.2.1
     */
    public function invokeBeforeLoaded(){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Firing before loaded event...');
        call_user_func($this->beforeLoaded, $this->beforeLoadedParams);
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Fire the callback function which should be called after loading the theme.
     * This method must not be used by the developers. It is called automatically 
     * when the theme is loaded.
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
     * @return boolean The method will return TRUE if 
     * the theme was found in the array of loaded themes. FALSE
     * if not.
     * @since 1.0
     */
    public static function isThemeLoaded($themeName) {
        return isset(self::$loadedThemes[$themeName]) === TRUE;
    }
    /**
     * Returns an array that contains the meta data of all available themes. 
     * This method will return an associative array. The key is the theme 
     * name and the value is an object of type Theme that contains theme info.
     * @return array An associative array that contains all themes information.
     * @since 1.1 
     */
    public static function getAvailableThemes(){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Building an array of available themes...');
        $themes = array();
        $themesDirs = array_diff(scandir(ROOT_DIR.'/'. self::THEMES_DIR), array('..', '.'));
        foreach ($themesDirs as $dir){
            $pathToScan = ROOT_DIR.'/'.self::THEMES_DIR.'/'.$dir;
            Logger::log('Path to scan: \''.$pathToScan.'\'.', 'debug');
            $filesInDir = array_diff(scandir($pathToScan), array('..', '.'));
            foreach ($filesInDir as $fileName){
                $fileExt = substr($fileName, -4);
                if($fileExt == '.php'){
                    $cName = str_replace('.php', '', $fileName);
                    if(class_exists($cName)){
                        $instance = new $cName();
                        if($instance instanceof Theme){
                            $themes[$instance->getName()] = $instance;
                        }
                    }
                }
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
        $trimmed = trim($name);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$trimmed.'\'.', 'debug');
        if(strlen($trimmed) != 0){
            $this->themeMeta['name'] = $trimmed.'';
            Logger::log('Theme name updated.');
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Returns the name of the theme.
     * If the name is not set, the method will return empty string.
     * @return string The name of the theme.
     * @since 1.0
     */
    public function getName() {
        return $this->themeMeta['name'];
    }
    /**
     * Sets the URL of theme designer web site. 
     * Theme URL can be the same as author URL.
     * @param string $url The URL to theme designer web site.
     * @since 1.0
     */
    public function setUrl($url) {
        Logger::logFuncCall(__METHOD__);
        $trimmed = trim($url);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$trimmed.'\'.', 'debug');
        if(strlen($trimmed) > 0){
            Logger::log('Theme URL updated.');
            $this->themeMeta['url'] = $trimmed.'';
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
        $trimmed = trim($author);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$trimmed.'\'.', 'debug');
        if(strlen($trimmed) > 0){
            Logger::log('Author name updated.');
            $this->themeMeta['author'] = $trimmed.'';
        }
        else{
            Logger::log('Given string is empty.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets the URL to the theme author. It can be the same as Theme URL.
     * @param string $authorUrl The URL to the author's web site.
     * @since 1.0
     */
    public function setAuthorUrl($authorUrl) {
        Logger::logFuncCall(__METHOD__);
        $trimmed = trim($authorUrl);
        Logger::log('Checking if given value is not empty string...');
        Logger::log('Given Value = \''.$trimmed.'\'.', 'debug');
        if(strlen($trimmed) > 0){
            Logger::log('Author URL updated.');
            $this->themeMeta['author-url'] = $trimmed.'';
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
     * Each theme must have a unique directory to prevent collision.
     * @param string $name The name of the directory.
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
     * If it is not set, the method will return empty string.
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
     * the name of the directory was not set by the method Theme::setImagesDirName(), 
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
     * the name of the directory was not set by the method Theme::setJsDirName(), 
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
     * the name of the directory was not set by the method Theme::setCssDirName(), 
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
     * method will return '1.0.0'
     * @since 1.1
     */
    public function getVersion() {
        return $this->themeMeta['version'];
    }
    /**
     * Returns the name of theme author.
     * @return string The name of theme author. If author name is not set, the 
     * method will return empty string.
     * @since 1.1
     */
    public function getAuthor() {
        return $this->themeMeta['author'];
    }
    /**
     * Returns the name of theme license.
     * @return string The name of theme license. If it is not set, 
     * the method will return empty string.
     */
    public function getLicenseName() {
        return $this->themeMeta['license'];
    }
    /**
     * Returns a URL which should contain a full version of theme license.
     * @return string A URL which contain a full version of theme license. 
     * If it is not set, the method will return empty string.
     * @since 1.1
     */
    public function getLicenseUrl(){
        return $this->themeMeta['license-url'];
    }
    /**
     * Returns the URL which takes the users to author's web site.
     * @return string The URL which takes users to author's web site. 
     * If author URL is not set, the method will return empty string.
     * @since 1.1
     */
    public function getAuthorUrl() {
        return $this->themeMeta['author-url'];
    }
    /**
     * Returns A URL which should point to theme web site.
     * @return string A URL which should point to theme web site. Usually, 
     * this one is the same as author URL.
     * If it is not set, the method will return empty string.
     * @since 1.1
     */
    public function getUrl() {
        return $this->themeMeta['url'];
    }
    /**
     * Returns the description of the theme.
     * @return string The description of the theme. If the description is not 
     * set, the method will return empty string.
     * @since 1.1
     */
    public function getDescription() {
        return $this->themeMeta['description'];
    }
    /**
     * Returns an object of type JsonX that represents the theme.
     * JSON string that will be generated by the JsonX instance will have 
     * the following information:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"name":""<br/>
     * &nbsp;&nbsp;"version":""<br/>
     * &nbsp;&nbsp;"author":""<br/>
     * &nbsp;&nbsp;"images-dir-name":""<br/>
     * &nbsp;&nbsp;"theme-dir-name":""<br/>
     * &nbsp;&nbsp;"css-dir-name":""<br/>
     * &nbsp;&nbsp;"js-dir-name":""<br/>
     * &nbsp;&nbsp;"components":[]<br/>
     * }
     * </p>
     * @return JsonX An object of type JsonX.
     */
    public function toJSON() {
        $j = new JsonX();
        $j->add('name', $this->getName());
        $j->add('version', $this->getVersion());
        $j->add('author', $this->getAuthor());
        $j->add('images-dir-name', $this->getImagesDirName());
        $j->add('theme-dir-name', $this->getDirectoryName());
        $j->add('css-dir-name', $this->getCssDirName());
        $j->add('js-dir-name', $this->getJsDirName());
        $j->add('components', $this->getComponents());
        return $j;
    }
    /**
     * Returns an object of type HeadNode that represents HTML &lt;head&gt; node. 
     * The developer must implement this method such that it returns an 
     * object of type HeadNode. The developer can use this method to include 
     * any JavaScript or CSS files that the page needs. Also, it can be used to 
     * add custom meta tags to &lt;head&gt; node or any tag that can be added 
     * to the node.
     * @return HeadNode An object of type HeadNode.
     * @since 1.2.2
     */
    public abstract function getHeadNode();
    /**
     * Returns an object of type HTMLNode that represents header section of the page. 
     * The developer must implement this method such that it returns an 
     * object of type HTMLNode. Header section of the page usually include a 
     * main navigation icon, web site name and web site logo. More complex 
     * layout can include other things such as a search bar, notifications 
     * area and user profile picture. If the page does not have a header 
     * section, the developer can make this method return NULL.
     * @return HTMLNode|NULL An object of type HTMLNode. If the theme has no header 
     * section, the method might return NULL.
     * @since 1.2.2
     */
    public abstract function getHeadrNode();
    /**
     * Returns an object of type HTMLNode that represents footer section of the page. 
     * The developer must implement this method such that it returns an 
     * object of type HTMLNode. Footer section of the page usually include links 
     * to social media profiles, about us page and site map. In addition, 
     * it might contain copyright notice and contact information. More complex 
     * layouts can have more items in the footer.
     * @return HTMLNode An object of type HTMLNode. If the theme has no footer 
     * section, the method might return NULL.
     * @since 1.2.2
     */
    public abstract function getFooterNode();
    /**
     * Returns an object of type HTMLNode that represents aside section of the page. 
     * The developer must implement this method such that it returns an 
     * object of type HTMLNode. Aside section of the page most of the time 
     * contains advertisements. Sometimes, it can contain aside menu for 
     * the web site or widgets.
     * @return HTMLNode An object of type HTMLNode. If the theme has no aside 
     * section, the method might return NULL.
     * @since 1.2.2
     */
    public abstract function getAsideNode();
}
