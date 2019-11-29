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
use jsonx\JsonI;
use webfiori\conf\SiteConfig;
use Exception;
/**
 * A base class that is used to construct web site UI.
 * A theme is a way to change the look and feel of all pages in 
 * a website. It can be used to unify all UI components and 
 * change them later using themes. The developer can extend this 
 * class and implement its abstract methods to create header section, 
 * a footer section and aside section. In addition, 
 * the developer can include custom head tags (like CSS files or 
 * JS files) by implementing one of the abstract methods. Themes must exist in 
 * the folder '/themes' of the framework.
 * @author Ibrahim
 * @version 1.2.3
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
    private static $loadedThemes = [];
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
        $this->themeComponents = [];
        $this->afterLoaded = function(){};
        $this->afterLoadedParams = [];
        $this->beforeLoaded = function(){};
        $this->beforeLoadedParams = [];
    }
    /**
     * Returns the base URL that will be used by the theme.
     * The URL is used by the HTML tag 'base' to fetch page resources. 
     * If the URL is not set by the developer, the method will return the 
     * URL that is returned by the method SiteConfig::getBaseURL().
     * @return string The base URL that will be used by the theme.
     */
    public function getBaseURL(){
        if($this->baseUrl !== null){
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
        $trimmed = trim($url);
        if(strlen($trimmed) > 0){
            $this->baseUrl = $trimmed;
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
        foreach ($arr as $component){
            $this->addComponent($component);
        }
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
        $trimmed = trim($componentName);
        if(strlen($trimmed) != 0 && !in_array($trimmed, $this->themeComponents)){
            $this->themeComponents[] = $trimmed;
        }
    }
    /**
     * Loads a theme given its name.
     * If the given name is null, the method will load the default theme as 
     * specified by the method SiteConfig::getBaseThemeName().
     * @param string $themeName The name of the theme. 
     * @return Theme The method will return an object of type Theme once the 
     * theme is loaded. The object will contain all theme information.
     * @throws Exception The method will throw 
     * an exception if no theme was found which has the given name.
     * @since 1.0
     */
    public static function usingTheme($themeName=null) {
        if($themeName === null){
            $themeName = SiteConfig::getBaseThemeName();
        }
        $themeToLoad = null;
        if(self::isThemeLoaded($themeName)){
            $themeToLoad = self::$loadedThemes[$themeName];
        }
        else{
            $themes = self::getAvailableThemes();
            if(isset($themes[$themeName])){
                $themeToLoad = $themes[$themeName];
                self::$loadedThemes[$themeName] = $themeToLoad;
            }
            else{
                throw new Exception('No such theme: \''.$themeName.'\'.');
            }
        }
        if(isset($themeToLoad)){
            $themeToLoad->invokeBeforeLoaded();
            $themeDir = ROOT_DIR.'/'.self::THEMES_DIR.'/'.$themeToLoad->getDirectoryName();
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
        throw new Exception('No such theme: \''.$themeName.'\'.');
    }
    /**
     * Sets the value of the callback which will be called after theme is loaded.
     * @param callable $function The callback.
     * @param array $params An array of parameters which can be passed to the 
     * callback.
     * @since 1.0
     */
    public function setAfterLoaded($function,$params=[]) {
        if(is_callable($function)){
            $this->afterLoaded = $function;
            if(gettype($params) == 'array'){
                $this->afterLoadedParams = $params;
            }
        }
    }
    /**
     * Sets the value of the callback which will be called before theme is loaded.
     * @param callback $function The callback.
     * @param array $params An array of parameters which can be passed to the 
     * callback.
     * @since 1.2.1
     */
    public function setBeforeLoaded($function,$params=[]){
        if(is_callable($function)){
            $this->beforeLoaded = $function;
            if(gettype($params) == 'array'){
                $this->beforeLoadedParams = $params;
            }
        }
    }
    /**
     * Fire the callback function which should be called before loading the theme.
     * This method must not be used by the developers. It is called automatically 
     * when the theme is being loaded.
     * @since 1.2.1
     */
    public function invokeBeforeLoaded(){
        call_user_func($this->beforeLoaded, $this->beforeLoadedParams);
    }
    /**
     * Fire the callback function which should be called after loading the theme.
     * This method must not be used by the developers. It is called automatically 
     * when the theme is loaded.
     * @since 1.0
     */
    public function invokeAfterLoaded(){
        call_user_func($this->afterLoaded, $this->afterLoadedParams);
    }

    /**
     * Checks if a theme is loaded or not given its name.
     * @param string $themeName The name of the theme.
     * @return boolean The method will return true if 
     * the theme was found in the array of loaded themes. false
     * if not.
     * @since 1.0
     */
    public static function isThemeLoaded($themeName) {
        return isset(self::$loadedThemes[$themeName]) === true;
    }
    /**
     * Returns an array that contains the meta data of all available themes. 
     * This method will return an associative array. The key is the theme 
     * name and the value is an object of type Theme that contains theme info.
     * @return array An associative array that contains all themes information. The name 
     * of the theme will be the key and the value is an object of type 'Theme'.
     * @since 1.1 
     */
    public static function getAvailableThemes(){
        $themes = array();
        $DS = DIRECTORY_SEPARATOR;
        $themesDirs = array_diff(scandir(ROOT_DIR.$DS.self::THEMES_DIR), ['..', '.']);
        foreach ($themesDirs as $dir){
            $pathToScan = ROOT_DIR.$DS.self::THEMES_DIR.$DS.$dir;
            $filesInDir = array_diff(scandir($pathToScan), ['..', '.']);
            foreach ($filesInDir as $fileName){
                $fileExt = substr($fileName, -4);
                if($fileExt == '.php'){
                    $cName = str_replace('.php', '', $fileName);
                    $ns = require_once $pathToScan.$DS.$fileName;
                    $aNs = $ns != 1 ? $ns.'\\' : '';
                    $aCName = $aNs.$cName;
                    if(class_exists($aCName)){
                        $instance = new $aCName();
                        if($instance instanceof Theme){
                            $themes[$instance->getName()] = $instance;
                        }
                    }
                }
            }
        }
        return $themes;
    }
    /**
     * Sets the name of the theme.
     * @param string $name The name of the theme. It must be non-empty string 
     * in order to set. Note that the name of the theme 
     * acts as the unique identifier for the theme. It can be used to load the 
     * theme later.
     * @since 1.0
     */
    public function setName($name) {
        $trimmed = trim($name);
        if(strlen($trimmed) != 0){
            $this->themeMeta['name'] = $trimmed;
        }
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
        $trimmed = trim($url);
        if(strlen($trimmed) > 0){
            $this->themeMeta['url'] = $trimmed;
        }
    }
    /**
     * Sets the name of theme author.
     * @param string $author The name of theme author (such as 'Ibrahim BinAlshikh')
     * @since 1.0
     */
    public function setAuthor($author) {
        $trimmed = trim($author);
        if(strlen($trimmed) > 0){
            $this->themeMeta['author'] = $trimmed;
        }
    }
    /**
     * Sets the URL to the theme author. It can be the same as Theme URL.
     * @param string $authorUrl The URL to the author's web site.
     * @since 1.0
     */
    public function setAuthorUrl($authorUrl) {
        $trimmed = trim($authorUrl);
        if(strlen($trimmed) > 0){
            $this->themeMeta['author-url'] = $trimmed;
        }
    }
    /**
     * Sets the version number of the theme.
     * @param string $vNum Version number. The format of version number is 
     * usually like 'X.X.X' where the 'X' can be any number.
     * @since 1.0
     */
    public function setVersion($vNum) {
        $trimmed = trim($vNum);
        if(strlen($trimmed) != 0){
            $this->themeMeta['version'] = $trimmed;
        }
    }
    /**
     * Sets the name of theme license.
     * @param string $text The name of theme license. It must be non-empty 
     * string in order to set.
     * @since 1.0
     */
    public function setLicenseName($text) {
        $trimmed = trim($text);
        if(strlen($trimmed) != 0){
            $this->themeMeta['license'] = $trimmed;
        }
    }
    /**
     * Sets a URL to the license where people can find more details about it.
     * @param string $url A URL to the license.
     * @since 1.0
     */
    public function setLicenseUrl($url) {
        $trimmed = trim($url);
        if(strlen($trimmed) > 0){
            $this->themeMeta['license-url'] = $trimmed;
        }
    }
    /**
     * Sets the description of the theme.
     * @param string $desc Theme description. Usually a short paragraph of two 
     * or 3 sentences. It must be non-empty string in order to set.
     * @since 1.0
     */
    public function setDescription($desc) {
        $trimmed = trim($desc);
        if(strlen($trimmed) > 0){
            $this->themeMeta['description'] = $trimmed;
        }
    }
    /**
     * Sets the name of the directory where all theme files are kept.
     * Each theme must have a unique directory to prevent collision. The 
     * directory of the theme must be a folder which exist inside the directory 
     * '/themes'.
     * @param string $name The name of theme directory.
     * @since 1.0
     */
    public function setDirectoryName($name) {
        $trimmed = trim($name);
        if(strlen($trimmed) != 0){
            $this->themeMeta['directory'] = $trimmed;
        }
    }
    /**
     * Returns the name of the directory where all theme files are kept.
     * The directory of a theme is a folder which exist inside the directory 
     * '/themes'.
     * @return string The name of the directory where all theme files are kept. 
     * If it is not set, the method will return empty string.
     * @since 1.0
     */
    public function getDirectoryName() {
        return $this->themeMeta['directory'];
    }
    /**
     * Sets the name of the directory where theme images are kept.
     * Note that it will be set only if the given name is not an empty string. In 
     * addition, directory name must not include theme directory name. For example, 
     * if your theme images exist in the directory '/themes/super-theme/images', 
     * the value that must be supplied to this method is 'images'.
     * @param string $name The name of the directory where theme images are kept. 
     * @since 1.0
     */
    public function setImagesDirName($name) {
        $trimmed = trim($name);
        if(strlen($trimmed) != 0){
            $this->imagesDir = $trimmed;
        }
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
     * Note that it will be set only if the given name is not an empty string. In 
     * addition, directory name must not include theme directory name. For example, 
     * if your theme JavaScript files exist in the directory '/themes/super-theme/js', 
     * the value that must be supplied to this method is 'js'.
     * @param string $name The name of the directory where theme JavaScript files are kept. 
     * @since 1.0
     */
    public function setJsDirName($name) {
        $trimmed = trim($name);
        if(strlen($trimmed) != 0){
            $this->jsDir = $trimmed;
        }
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
     * Note that it will be set only if the given name is not an empty string. In 
     * addition, directory name must not include theme directory name. For example, 
     * if your theme CSS files exist in the directory '/themes/super-theme/css', 
     * the value that must be supplied to this method is 'css'.
     * @param string $name The name of the directory where theme CSS files are kept.
     * @since 1.0
     */
    public function setCssDirName($name) {
        $trimmed = trim($name);
        if(strlen($trimmed) != 0){
            $this->cssDir = $trimmed;
        }
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
     * @return array An associative array which contains all loaded themes. 
     * The index will be theme name and the value is an object of type 'Theme' 
     * which contains theme info.
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
     * any JavaScript or CSS files that website pages needs. Also, it can be used to 
     * add custom meta tags to &lt;head&gt; node or any tag that can be added 
     * to the &lt;head&gt; HTML element.
     * @return HeadNode An object of type HeadNode.
     * @since 1.2.2
     */
    public abstract function getHeadNode();
    /**
     * Returns an object of type HTMLNode that represents header section of the page. 
     * The developer must implement this method such that it returns an 
     * object of type 'HTMLNode'. Header section of the page usually include a 
     * main navigation menu, web site name and web site logo. More complex 
     * layout can include other things such as a search bar, notifications 
     * area and user profile picture. If the page does not have a header 
     * section, the developer can make this method return null.
     * @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no header 
     * section, the method might return null.
     * @since 1.2.2
     */
    public abstract function getHeadrNode();
    /**
     * Returns an object of type 'HTMLNode' that represents footer section of the page. 
     * The developer must implement this method such that it returns an 
     * object of type 'HTMLNode'. Footer section of the page usually include links 
     * to social media profiles, about us page and site map. In addition, 
     * it might contain copyright notice and contact information. More complex 
     * layouts can have more items in the footer.
     * @return HTMLNode An object of type 'HTMLNode'. If the theme has no footer 
     * section, the method might return null.
     * @since 1.2.2
     */
    public abstract function getFooterNode();
    /**
     * Returns an object of type 'HTMLNode' that represents aside section of the page. 
     * The developer must implement this method such that it returns an 
     * object of type 'HTMLNode'. Aside section of the page will 
     * contain advertisements most of the time. Sometimes, it can contain aside menu for 
     * the web site or widgets.
     * @return HTMLNode An object of type 'HTMLNode'. If the theme has no aside 
     * section, the method might return null.
     * @since 1.2.2
     */
    public abstract function getAsideNode();
    /**
     * Creates an instance of 'HTMLNode' given an array of options.
     * This method is used to allow the creation of multiple HTML elements 
     * depending on the way the developer will implement it. The method might 
     * only return a single instance of the class 'HTMLNode' for every call or 
     * the developer can make it customizable by supporting options. The options 
     * can be passed as an array. A use case for this method would be as 
     * follows, the developer would like to create different type of input 
     * elements. One possible option in the passed array would be 'input-type'. 
     * By checking this option in the body of the method, the developer can return 
     * different types of input elements.
     * @param array $options An array of options that developer can specify.
     * @return HTMLNode The developer must implement this method in away that 
     * makes it return an instance of the class 'HTMLNode'. 
     * @since 1.2.3
     */
    public abstract function createHTMLNode($options=array());
}
