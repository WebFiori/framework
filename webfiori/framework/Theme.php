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
namespace webfiori\framework;

use ReflectionClass;
use webfiori\framework\ui\WebPage;
use webfiori\json\Json;
use webfiori\json\JsonI;
use webfiori\ui\HTMLNode;
/**
 * A base class that is used to construct web site UI.
 * 
 * A theme is a way to change the look and feel of all pages in 
 * a website. It can be used to unify all UI components and 
 * change them later using themes. The developer can extend this 
 * class and implement its abstract methods to create header section, 
 * a footer section and aside section. In addition, 
 * the developer can include custom head tags (like CSS files or 
 * JS files) by implementing one of the abstract methods. Themes must exist in 
 * the folder '/themes' of the framework.
 * 
 * @author Ibrahim
 * 
 * @version 1.2.8
 */
abstract class Theme implements JsonI {
    /**
     * 
     * @var array
     * 
     * @since 1.2.8
     */
    private $afterLoadedParamsPool;
    /**
     * 
     * @var array
     * 
     * @since 1.2.8
     */
    private $afterLoadedPool;
    /**
     * An optional base URL.
     * 
     * This URL is used by the tag 'base' to fetch page resources.
     * 
     * @var string
     * 
     * @since 1.2.2 
     */
    private $baseUrl;
    /**
     * 
     * @var array
     * 
     * @since 1.2.8
     */
    private $beforeLoadedParamsPool;
    /**
     * 
     * @var array
     * 
     * @since 1.2.8
     */
    private $beforeLoadedPool;
    /**
     * The name of theme CSS files directory. 
     * 
     * @var string 
     * 
     * @since 1.0
     */
    private $cssDir;
    /**
     * The directory where theme images are stored.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $imagesDir;
    /**
     * The name of theme JavaScript files directory. 
     * 
     * @var string 
     * 
     * @since 1.0
     */
    private $jsDir;
    /**
     * The web page at which the theme will be applied to.
     * 
     * @var WebPage|null
     * 
     * @since 1.2.7 
     */
    private $page;
    /**
     * An associative array that contains theme meta info.
     * 
     * @var array
     * 
     * @since 1.0 
     */
    private $themeMeta;
    /**
     * Creates new instance of the class using default values.
     * 
     * The default values will be set as follows:
     * <ul>
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
     * 
     * @param $themeName The name of the theme. The name is used to load the 
     * theme. For that reason, it must be unique.
     * 
     * @since 1.0
     */
    public function __construct($themeName = '') {
        $this->themeMeta = [
            'name' => '',
            'url' => '',
            'author' => '',
            'author-url' => '',
            'version' => '1.0.0',
            'license' => '',
            'license-url' => '',
            'description' => '',
            'directory' => ''
        ];

        $reflection = new ReflectionClass($this);
        $dirExpl = explode(DS, dirname($reflection->getFileName()));       
        $this->themeMeta['directory'] = $dirExpl[count($dirExpl) - 1];

        $this->setCssDirName('css');
        $this->setJsDirName('js');
        $this->setImagesDirName('images');
        $this->setName($themeName);

        $this->beforeLoadedParamsPool = [];
        $this->beforeLoadedPool = [];
        $this->afterLoadedParamsPool = [];
        $this->afterLoadedPool = [];
    }

    /**
     * Creates an instance of 'HTMLNode' given an array of options.
     * 
     * The developer can override this method to make it create custom HTML 
     * elements which can be re-used across web pages. By default, the method 
     * will create a &lt;div&gt; element.
     * A use case for this method would be as 
     * follows, the developer would like to create different type of input 
     * elements. One possible option in the passed array would be 'input-type'. 
     * By checking this option in the body of the method, the developer can return 
     * different types of input elements.
     * 
     * @param array $options An array of options that developer can specify. Default 
     * implementation of the method accepts two options, the option 'name' 
     * which represents the name of HTML tag (such as 'div') and the option 
     * 'attributes' which is a sub array that contains tag attributes.
     * 
     * @return HTMLNode The developer must implement this method in away that 
     * makes it return an instance of the class 'HTMLNode'. 
     * 
     * @since 1.2.3
     */
    public function createHTMLNode($options = []) {
        if (gettype($options) != 'array') {
            $options = [
                'name' => 'div'
            ];
        }
        $nodeName = isset($options['name']) ? $options['name'] : 'div';
        $attributes = isset($options['attributes']) ? $options['attributes'] : [];

        return new HTMLNode($nodeName, $attributes);
    }
    /**
     * Returns an object of type 'HTMLNode' that represents aside section of the page. 
     * 
     * The developer must implement this method such that it returns an 
     * object of type 'HTMLNode'. Aside section of the page will 
     * contain advertisements most of the time. Sometimes, it can contain aside menu for 
     * the web site or widgets.
     * 
     * @return HTMLNode An object of type 'HTMLNode'. If the theme has no aside 
     * section, the method might return null.
     * 
     * @since 1.2.2
     */
    public abstract function getAsideNode();
    /**
     * Returns the name of theme author.
     * 
     * @return string The name of theme author. If author name is not set, the 
     * method will return empty string.
     * 
     * @since 1.1
     */
    public function getAuthor() {
        return $this->themeMeta['author'];
    }
    /**
     * Returns the URL which takes the users to author's web site.
     * 
     * @return string The URL which takes users to author's web site. 
     * If author URL is not set, the method will return empty string.
     * 
     * @since 1.1
     */
    public function getAuthorUrl() {
        return $this->themeMeta['author-url'];
    }
    /**
     * Returns the base URL that will be used by the theme.
     * 
     * The URL is used by the HTML tag 'base' to fetch page resources. 
     * If the URL is not set by the developer, the method will return the 
     * URL that is returned by the method SiteConfig::getBaseURL().
     * 
     * @return string The base URL that will be used by the theme.
     */
    public function getBaseURL() {
        if ($this->baseUrl !== null) {
            return $this->baseUrl;
        } else {
            return WebFioriApp::getAppConfig()->getBaseURL();
        }
    }

    /**
     * Returns the name of the directory where CSS files are kept.
     * 
     * @return string The name of the directory where theme CSS files kept. If 
     * the name of the directory was not set by the method Theme::setCssDirName(), 
     * then the returned value will be 'css'.
     * 
     * @since 1.0
     */
    public function getCssDirName() {
        return $this->cssDir;
    }
    /**
     * Returns the description of the theme.
     * 
     * @return string The description of the theme. If the description is not 
     * set, the method will return empty string.
     * 
     * @since 1.1
     */
    public function getDescription() {
        return $this->themeMeta['description'];
    }
    /**
     * Returns a string that represents the directory at which the theme exist 
     * in the system.
     * 
     * This method is useful if the developer would like to load HTML file which 
     * is part of the theme using the method HTMLNode::loadComponent().
     * 
     * @return string The string will be something like 'C:\Server\apache\htdocs\my-site\themes\my-theme\'.
     * 
     * @since 1.2.6
     */
    public function getDirecotry() {
        return THEMES_PATH.DS.$this->getDirectoryName().DS;
    }
    /**
     * Returns the name of the directory where all theme files are kept.
     * 
     * The directory of a theme is a folder which exist inside the directory 
     * '/themes'.
     * 
     * @return string The name of the directory where all theme files are kept. 
     * If it is not set, the method will return empty string.
     * 
     * @since 1.0
     */
    public function getDirectoryName() {
        return $this->themeMeta['directory'];
    }
    /**
     * Returns an object of type 'HTMLNode' that represents footer section of the page. 
     * 
     * The developer must implement this method such that it returns an 
     * object of type 'HTMLNode'. Footer section of the page usually include links 
     * to social media profiles, about us page and site map. In addition, 
     * it might contain copyright notice and contact information. More complex 
     * layouts can have more items in the footer.
     * 
     * @return HTMLNode An object of type 'HTMLNode'. If the theme has no footer 
     * section, the method might return null.
     * 
     * @since 1.2.2
     */
    public abstract function getFooterNode();
    /**
     * Returns an object of type HTMLNode that represents header section of the page. 
     * 
     * The developer must implement this method such that it returns an 
     * object of type 'HTMLNode'. Header section of the page usually include a 
     * main navigation menu, web site name and web site logo. More complex 
     * layout can include other things such as a search bar, notifications 
     * area and user profile picture. If the page does not have a header 
     * section, the developer can make this method return null.
     * 
     * @return HTMLNode|null An object of type 'HTMLNode'. If the theme has no header 
     * section, the method might return null.
     * 
     * @since 1.2.2
     */
    public abstract function getHeaderNode();
    /**
     * Returns an object of type HeadNode that represents HTML &lt;head&gt; node. 
     * 
     * The developer must implement this method such that it returns an 
     * object of type HeadNode. The developer can use this method to include 
     * any JavaScript or CSS files that website pages needs. Also, it can be used to 
     * add custom meta tags to &lt;head&gt; node or any tag that can be added 
     * to the &lt;head&gt; HTML element.
     * 
     * @return HeadNode An object of type HeadNode.
     * 
     * @since 1.2.2
     */
    public abstract function getHeadNode();
    /**
     * Returns the name of the directory where theme images are kept.
     * 
     * @return string The name of the directory where theme images are kept. If 
     * the name of the directory was not set by the method Theme::setImagesDirName(), 
     * then the returned value will be 'images'.
     * 
     * @since 1.0
     */
    public function getImagesDirName() {
        return $this->imagesDir;
    }
    /**
     * Returns the name of the directory where JavaScript files are kept.
     * 
     * @return string The name of the directory where theme JavaScript files kept. If 
     * the name of the directory was not set by the method Theme::setJsDirName(), 
     * then the returned value will be 'js'.
     * 
     * @since 1.0
     */
    public function getJsDirName() {
        return $this->jsDir;
    }
    /**
     * Returns the name of theme license.
     * 
     * @return string The name of theme license. If it is not set, 
     * the method will return empty string.
     */
    public function getLicenseName() {
        return $this->themeMeta['license'];
    }
    /**
     * Returns a URL which should contain a full version of theme license.
     * 
     * @return string A URL which contain a full version of theme license. 
     * If it is not set, the method will return empty string.
     * 
     * @since 1.1
     */
    public function getLicenseUrl() {
        return $this->themeMeta['license-url'];
    }
    /**
     * Returns the name of the theme.
     * 
     * If the name is not set, the method will return empty string.
     * 
     * @return string The name of the theme.
     * 
     * @since 1.0
     */
    public function getName() {
        return $this->themeMeta['name'];
    }
    /**
     * Returns the page at which the theme will be applied to.
     * 
     * @return WebPage|null If the theme is applied to a page, the 
     * method will return it as an object. If theme is not applied to 
     * any page, the method will return null.
     * 
     * @since 1.2.7
     */
    public function getPage() {
        return $this->page;
    }
    /**
     * Returns A URL which should point to theme web site.
     * 
     * @return string A URL which should point to theme web site. Usually, 
     * this one is the same as author URL.If it is not set, the method will 
     * return empty string.
     * 
     * @since 1.1
     */
    public function getUrl() {
        return $this->themeMeta['url'];
    }
    /**
     * Returns theme version number.
     * 
     * @return string theme version number. The format if the version number 
     * is 'x.x.x' where 'x' can be any number. If it is not set, the 
     * method will return '1.0.0'.
     * 
     * @since 1.1
     */
    public function getVersion() {
        return $this->themeMeta['version'];
    }
    /**
     * Fire the callback function which should be called after loading the theme.
     * 
     * This method must not be used by the developers. It is called automatically 
     * when the theme is loaded.
     * 
     * @since 1.0
     */
    public function invokeAfterLoaded() {
        $callbackCount = count($this->afterLoadedPool);

        for ($x = 0 ; $x < $callbackCount ; $x++) {
            call_user_func_array($this->afterLoadedPool[$x], $this->afterLoadedParamsPool[$x]);
        }
    }
    /**
     * Fire the callback function which should be called before loading the theme.
     * 
     * This method must not be used by the developers. It is called automatically 
     * when the theme is being loaded.
     * 
     * @since 1.2.1
     */
    public function invokeBeforeLoaded() {
        $callbackCount = count($this->beforeLoadedPool);

        for ($x = 0 ; $x < $callbackCount ; $x++) {
            call_user_func_array($this->beforeLoadedPool[$x], $this->beforeLoadedParamsPool[$x]);
        }
    }
    /**
     * Sets the value of the callback which will be called after theme is loaded.
     * 
     * @param callable $function The callback. The first parameter of the 
     * callback will be always 'this' theme. (e.g. function ($theme){}). The function 
     * can have other parameters if they are provided.
     * 
     * @param array $params An array of parameters which can be passed to the 
     * callback.
     * 
     * @since 1.0
     */
    public function setAfterLoaded($function,$params = []) {
        if (is_callable($function)) {
            $afterLoadedParams = [$this];

            if (gettype($params) == 'array') {
                foreach ($params as $param) {
                    $afterLoadedParams[] = $param;
                }
            }
            $this->afterLoadedPool[] = $function;
            $this->afterLoadedParamsPool[] = $afterLoadedParams;
        }
    }
    /**
     * Sets the name of theme author.
     * 
     * @param string $author The name of theme author (such as 'Ibrahim BinAlshikh').
     * 
     * @since 1.0
     */
    public function setAuthor($author) {
        $trimmed = trim($author);

        if (strlen($trimmed) > 0) {
            $this->themeMeta['author'] = $trimmed;
        }
    }
    /**
     * Sets the URL to the theme author. It can be the same as Theme URL.
     * 
     * @param string $authorUrl The URL to the author's web site.
     * 
     * @since 1.0
     */
    public function setAuthorUrl($authorUrl) {
        $trimmed = trim($authorUrl);

        if (strlen($trimmed) > 0) {
            $this->themeMeta['author-url'] = $trimmed;
        }
    }
    /**
     * Sets The base URL that will be used by the theme.
     * 
     * This URL is used by the HTML tag 'base' to fetch page resources. The 
     * given string must be non-empty string in order to set.
     * 
     * @param string $url The base URL that will be used by the theme.
     * @since 1.0
     */
    public function setBaseURL($url) {
        $trimmed = trim($url);

        if (strlen($trimmed) > 0) {
            $this->baseUrl = $trimmed;
        }
    }
    /**
     * Sets the value of the callback which will be called before theme is loaded.
     * 
     * @param callback $function The callback. The first parameter of the 
     * callback will be always 'this' theme. (e.g. function ($theme){}). The function 
     * can have other parameters if they are provided.
     * 
     * @param array $params An array of parameters which can be passed to the 
     * callback.
     * 
     * @since 1.2.1
     */
    public function setBeforeLoaded($function,$params = []) {
        if (is_callable($function)) {
            $beforeLoadedParams = [$this];

            if (gettype($params) == 'array') {
                foreach ($params as $param) {
                    $beforeLoadedParams[] = $param;
                }
            }
            $this->beforeLoadedPool[] = $function;
            $this->beforeLoadedParamsPool[] = $beforeLoadedParams;
        }
    }
    /**
     * Sets the name of the directory where theme CSS files are kept.
     * 
     * Note that it will be set only if the given name is not an empty string. In 
     * addition, directory name must not include theme directory name. For example, 
     * if your theme CSS files exist in the directory '/themes/super-theme/css', 
     * the value that must be supplied to this method is 'css'.
     * 
     * @param string $name The name of the directory where theme CSS files are kept.
     * 
     * @since 1.0
     */
    public function setCssDirName($name) {
        $trimmed = trim($name);

        if (strlen($trimmed) != 0) {
            $this->cssDir = $trimmed;
        }
    }
    /**
     * Sets the description of the theme.
     * 
     * @param string $desc Theme description. Usually a short paragraph of two 
     * or 3 sentences. It must be non-empty string in order to set.
     * 
     * @since 1.0
     */
    public function setDescription($desc) {
        $trimmed = trim($desc);

        if (strlen($trimmed) > 0) {
            $this->themeMeta['description'] = $trimmed;
        }
    }
    /**
     * Sets the name of the directory where theme images are kept.
     * 
     * Note that it will be set only if the given name is not an empty string. In 
     * addition, directory name must not include theme directory name. For example, 
     * if your theme images exist in the directory '/themes/super-theme/images', 
     * the value that must be supplied to this method is 'images'.
     * 
     * @param string $name The name of the directory where theme images are kept. 
     * 
     * @since 1.0
     */
    public function setImagesDirName($name) {
        $trimmed = trim($name);

        if (strlen($trimmed) != 0) {
            $this->imagesDir = $trimmed;
        }
    }
    /**
     * Sets the name of the directory where theme JavaScript files are kept.
     * 
     * Note that it will be set only if the given name is not an empty string. In 
     * addition, directory name must not include theme directory name. For example, 
     * if your theme JavaScript files exist in the directory '/themes/super-theme/js', 
     * the value that must be supplied to this method is 'js'.
     * 
     * @param string $name The name of the directory where theme JavaScript files are kept. 
     * 
     * @since 1.0
     */
    public function setJsDirName($name) {
        $trimmed = trim($name);

        if (strlen($trimmed) != 0) {
            $this->jsDir = $trimmed;
        }
    }
    /**
     * Sets the name of theme license.
     * 
     * @param string $text The name of theme license. It must be non-empty 
     * string in order to set.
     * 
     * @since 1.0
     */
    public function setLicenseName($text) {
        $trimmed = trim($text);

        if (strlen($trimmed) != 0) {
            $this->themeMeta['license'] = $trimmed;
        }
    }
    /**
     * Sets a URL to the license where people can find more details about it.
     * 
     * @param string $url A URL to the license.
     * 
     * @since 1.0
     */
    public function setLicenseUrl($url) {
        $trimmed = trim($url);

        if (strlen($trimmed) > 0) {
            $this->themeMeta['license-url'] = $trimmed;
        }
    }
    /**
     * Sets the name of the theme.
     * 
     * @param string $name The name of the theme. It must be non-empty string 
     * in order to set. Note that the name of the theme 
     * acts as the unique identifier for the theme. It can be used to load the 
     * theme later.
     * 
     * @since 1.0
     */
    public function setName($name) {
        $trimmed = trim($name);

        if (strlen($trimmed) != 0) {
            $this->themeMeta['name'] = $trimmed;
        }
    }
    /**
     * Sets the page at which the theme will be applied to.
     * 
     * @param WebPage $page The page that the theme is applied to.
     * 
     * @since 1.2.7
     */
    public function setPage(WebPage $page) {
        $this->page = $page;
    }
    /**
     * Sets the URL of theme designer web site. 
     * 
     * Theme URL can be the same as author URL.
     * 
     * @param string $url The URL to theme designer web site.
     * 
     * @since 1.0
     */
    public function setUrl($url) {
        $trimmed = trim($url);

        if (strlen($trimmed) > 0) {
            $this->themeMeta['url'] = $trimmed;
        }
    }
    /**
     * Sets the version number of the theme.
     * 
     * @param string $vNum Version number. The format of version number is 
     * usually like 'X.X.X' where the 'X' can be any number.
     * 
     * @since 1.0
     */
    public function setVersion($vNum) {
        $trimmed = trim($vNum);

        if (strlen($trimmed) != 0) {
            $this->themeMeta['version'] = $trimmed;
        }
    }
    /**
     * Returns an object of type Json that represents the theme.
     * 
     * JSON string that will be generated by the Json instance will have 
     * the following information:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"themesPath":""<br/>
     * &nbsp;&nbsp;"name":""<br/>
     * &nbsp;&nbsp;"version":""<br/>
     * &nbsp;&nbsp;"author":""<br/>
     * &nbsp;&nbsp;"imagesDirName":""<br/>
     * &nbsp;&nbsp;"themeDirName":""<br/>
     * &nbsp;&nbsp;"cssDirName":""<br/>
     * &nbsp;&nbsp;"jsDirName":""<br/>
     * }
     * </p>
     * 
     * @return Json An object of type Json.
     */
    public function toJSON() {
        return new Json([
            'themesPath' => THEMES_PATH,
            'name' => $this->getName(),
            'url' => $this->getUrl(),
            'license' => $this->getLicenseName(),
            'licenseUrl' => $this->getLicenseUrl(),
            'version' => $this->getVersion(),
            'author' => $this->getAuthor(),
            'authorUrl' => $this->getAuthorUrl(),
            'imagesDirName' => $this->getImagesDirName(),
            'themeDirName' => $this->getDirectoryName(),
            'cssDirName' => $this->getCssDirName(),
            'jsDirName' => $this->getJsDirName(),
        ]);
    }
}
