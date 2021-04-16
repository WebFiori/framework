<?php
/*
 * The MIT License
 *
 * Copyright 2021 Ibrahim, WebFiori Framework.
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
namespace webfiori\framework\ui;

use Exception;
use webfiori\framework\exceptions\UIException;
use webfiori\framework\i18n\Language;
use webfiori\framework\session\SessionsManager;
use webfiori\framework\Theme;
use webfiori\framework\ThemeLoader;
use webfiori\framework\Util;
use webfiori\framework\WebFioriApp;
use webfiori\http\Request;
use webfiori\http\Response;
use webfiori\json\Json;
use webfiori\ui\HeadNode;
use webfiori\ui\HTMLDoc;
use webfiori\ui\HTMLNode;
use webfiori\framework\session\Session;
use webfiori\framework\exceptions\MissingLangException;
/**
 * A base class that can be used to implement web pages.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 * 
 * @since 2.1.0
 */
class WebPage {
    /**
     * An array that contains the IDs of the 3 main page elements.
     * 
     * The array has the following values:
     * <ul>
     * <li>page-body</li>
     * <li>page-header</li>
     * <li>main-content-area</li>
     * <li>side-content-area</li>
     * <li>page-footer</li>
     * </ul>
     * 
     * @since 1.0
     */
    const MAIN_ELEMENTS = [
        'page-body',
        'page-header',
        'main-content-area',
        'side-content-area',
        'page-footer'
    ];
    /**
     * An array that contains closures 
     * which will be called before the page is fully rendered.
     * 
     * @var array
     * 
     * @since 1.0
     */
    private $beforeRenderCallbacks;
    /**
     * An array that contains the parameters which will be based to the 
     * callbacks which will be get executed before rendering the page.
     * 
     * @var array
     * 
     * @since 1.0
     */
    private $beforeRenderParams;
    /**
     *
     * @var string
     * 
     * @since 1.0
     */
    private static $BoolType = 'boolean';
    /**
     * The canonical page URL.
     * 
     * @var type 
     * 
     * @since 1.0
     */
    private $canonical;
    /**
     * The writing direction of the page.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $contentDir;
    /**
     * The language of the page.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $contentLang;
    /**
     * The description of the page.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $description;
    /**
     * The document that represents the page.
     * 
     * @var HTMLDoc 
     * 
     * @since 1.0
     */
    private $document;
    /**
     * A variable that is set to <b>true</b> if page has aside area.
     * 
     * @var boolean A variable that is set to <b>true</b> if page has footer.
     * 
     * @since 1.0 
     */
    private $incAside;
    /**
     * A variable that is set to <b>true</b> if page has footer.
     * 
     * @var boolean A variable that is set to <b>true</b> if page has footer.
     * 
     * @since 1.0 
     */
    private $incFooter;
    /**
     * A variable that is set to <b>true</b> if page has header.
     * 
     * @var boolean A variable that is set to <b>true</b> if page has header.
     * 
     * @since 1.0 
     */
    private $incHeader;
    /**
     *
     * @var boolean 
     * 
     * @since 1.0
     */
    private $includeLables;
    /**
     * An object of type <b>Theme</b> that contains loaded theme info.
     * 
     * @var Theme 
     * 
     * @since 1.0
     */
    private $theme;
    /**
     * The title of the page.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $title;
    /**
     * The character or string that is used to separate web page title 
     * and web site name.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $titleSep;
    /**
     *
     * @var Language|null 
     */
    private $tr;
    /**
     * The name of the web site that will be appended with the title of 
     * the page.
     * 
     * @var string 
     * 
     * @since 1.0
     */
    private $websiteName;
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        $this->reset();
    }
    /**
     * Adds a function which will be executed before the page is fully rendered.
     * 
     * The function will be executed when the method 'WebPage::render()' is called. 
     * One possible use is to do some modifications to the DOM before the 
     * page is displayed. It is possible to have multiple callbacks.
     * 
     * @param callback $callable A PHP function that will be get executed. before 
     * the page is rendered. Note that the first argument of the function will 
     * always be an object of type "WebPage".
     * 
     * @param array $params An array of parameters which will be passed to the 
     * callback. The parameters can be accessed in the callback in the 
     * same order at which they appear in the array.
     * 
     * @return int|null If the callable is added, the method will return a 
     * number that represents its ID. If not added, the method will return 
     * null.
     * 
     * @since 1.0
     */
    public function addBeforeRender($callable = '', $params = []) {
        if (is_callable($callable) || $callable instanceof \Closure) {
            $this->beforeRenderCallbacks[] = $callable;
            $xParamsArr = [$this];

            if (gettype($params) == 'array') {
                foreach ($params as $p) {
                    $xParamsArr[] = $p;
                }
            }
            $this->beforeRenderParams[] = $xParamsArr;
            $callbacksCount = count($this->beforeRenderCallbacks);

            return $callbacksCount - 1;
        }
    }
    /**
     * Adds new meta tag.
     * 
     * @param string $name The value of the property 'name'. Must be non empty 
     * string.
     * 
     * @param string $content The value of the property 'content'.
     * 
     * @param boolean $override A boolean parameter. If a meta node was found 
     * which has the given name and this attribute is set to true, 
     * the content of the meta will be overridden by the passed value. 
     * 
     * @return HeadNote The method will return the instance at which the method 
     * is called on.
     * 
     * @since 1.0
     */
    public function addMeta($name, $content, $override = false) {
        $this->getDocument()->getHeadNode()->addMeta($name, $content, $override);
    }
    /**
     * Adds new CSS source file.
     * 
     * @param string $href The link to the file. Must be non empty string. It is 
     * possible to append query string to the end of the link.
     * 
     * @param array $attrs An associative array of additional attributes 
     * to set for the node.
     * 
     * @since 1.0
     */
    public function addCSS($href, array $attrs = []) {
        if (!isset($attrs['revision'])) {
            $attrs['revision'] = WebFioriApp::getAppConfig()->getVersion();
        }
        $this->getDocument()->getHeadNode()->addCSS($href, $attrs);
    }
    /**
     * Adds new JavsScript source file.
     * 
     * @param string $src The location of the file. Must be non-empty string. It 
     * can have query string at the end.
     * 
     * @param array $attrs An associative array of additional attributes 
     * to set for the JavaScript node.
     * 
     * @since 1.0
     */
    public function addJS($src, array $attrs = []) {
        if (!isset($attrs['revision'])) {
            $attrs['revision'] = WebFioriApp::getAppConfig()->getVersion();
        }
        $this->getDocument()->getHeadNode()->addJs($src, $attrs);
    }
    /**
     * Returns the value of a language label.
     * 
     * @param string $label A directory to the language variable 
     * (such as 'pages/login/login-label').
     * 
     * @return string|array If the given directory represents a label, the 
     * method will return its value. If it represents an array, the array will 
     * be returned. If nothing was found, the returned value will be the passed 
     * value to the method.
     * 
     * @since 1.0 
     */
    public function get($label) {
        $langObj = $this->getTranslation();

        if ($langObj !== null) {
            return $langObj->get($label);
        }

        return $label;
    }
    /**
     * Returns a child node given its ID.
     * 
     * @param string $id The ID of the child.
     * 
     * @return null|HTMLNode The method returns an object of type HTMLNode. 
     * if found. If no node has the given ID, the method will return null.
     * 
     * @since 1.0
     */
    public function getChildByID($id) {
        return $this->getDocument()->getChildByID($id);
    }
    /**
     * Returns the document that is associated with the page.
     * 
     * @return HTMLDoc An object of type 'HTMLDoc'.
     * 
     * @since 1.0
     */
    public function getDocument() {
        return $this->document;
    }
    /**
     * Removes a child node from the document of the page.
     * 
     * @param HTMLNode|string $node The node that will be removed.  This also 
     * can be the value of the attribute ID of the node that will be removed.
     * 
     * @return HTMLNode|null The method will return the node if removed. 
     * If not removed, the method will return null.
     * 
     * @since 1.0
     */
    public function removeChild($node) {
        return $this->getDocument()->removeChild($node);
    }
    /**
     * Returns the language code of the page.
     * 
     * @return string|null Two digit language code. In case language is not set, the 
     * method will return null
     * 
     * @since 1.0
     */
    public function getLangCode() {
        return $this->contentLang;
    }
    /**
     * Returns an object which holds applied theme information.
     * 
     * @return Theme|null If no theme is applied, the method will return null. 
     * Other than than, the method will return an object that holds applied 
     * theme info.
     * 
     * @since 1.0
     */
    public function getTheme() {
        return $this->theme;
    }
    /**
     * Create HTML node based on the method which exist on the applied theme.
     * 
     * This method can be only used if a theme is applied and the method 
     * Theme::createHTMLNode() is implemented.
     * 
     * @param array $nodeInfo An array that holds node information.
     * 
     * @return HTMLNode|null The returned HTML node will depend on how the 
     * developer has implemented the method Theme::createHTMLNode(). If 
     * no theme is applied, the method will return null.
     * 
     * @since 1.0
     */
    public function createHTMLNode($nodeInfo) {
        $theme = $this->getTheme();
        if ($theme !== null) {
            return $theme->createHTMLNode($nodeInfo);
        }
    }
    /**
     * Returns the name of the directory at which CSS files of the applied theme exists.
     * 
     * @return string The directory at which CSS files of the theme exists 
     * (e.g. 'assets/my-theme/css' ). The folder will always exist inside the folder 
     * 'public/assets'.
     * If no theme is applied, the method will return empty string.
     * 
     * @since 1.0
     */
    public function getThemeCSSDir() {
        if ($this->isThemeLoaded()) {
            $loadedTheme = $this->getTheme();

            return 'assets/'.$loadedTheme->getDirectoryName().'/'.$loadedTheme->getCssDirName();
        }

        return '';
    }
    /**
     * Returns the name of the directory at which image files of the applied theme exists.
     * 
     * @return string The directory at which image files of the theme exists 
     * (e.g. 'assets/my-theme/images' ).
     * If no theme is applied, the method will return empty string.
     * 
     * @since 1.0
     */
    public function getThemeImagesDir() {
        if ($this->isThemeLoaded()) {
            $loadedTheme = $this->getTheme();

            return 'assets/'.$loadedTheme->getDirectoryName().'/'.$loadedTheme->getImagesDirName();
        }

        return '';
    }
    /**
     * Returns the name of the directory at which JavaScript files of the applied theme exists.
     * 
     * @return string The directory at which JavaScript files of the theme exists 
     * (e.g. 'assets/my-theme/js' ). 
     * If no theme is applied, the method will return empty string.
     * 
     * @since 1.0
     */
    public function getThemeJSDir() {
        if ($this->isThemeLoaded()) {
            $loadedTheme = $this->getTheme();

            return 'assets/'.$loadedTheme->getDirectoryName().'/'.$loadedTheme->getJsDirName();
        }

        return '';
    }
    
    /**
     * Returns the title of the page.
     * 
     * @return string|null The title of the page. Default return value is 
     * 'Default X'.
     * 
     * @since 1.0
     */
    public function getTitle() {
        return $this->title;
    }
    /**
     * Returns the character or string that is used to separate web page title 
     * and web site name.
     * 
     * @return string The character or string that is used to separate web page title 
     * and web site name. If the separator was not set 
     * using the method <b>WebPage::setTitleSep()</b>, the returned value will 
     * be ' | '.
     * 
     * @since 1.0
     */
    public function getTitleSep() {
        return $this->titleSep;
    }
    /**
     * Returns an object which holds i18n labels.
     * 
     * @return Language The returned object labels will be based on the 
     * language of the page.
     * 
     * @since 1.0
     */
    public function getTranslation() {
        return $this->tr;
    }
    /**
     * Returns the writing direction of the page.
     * 
     * @return string|null 'ltr' or 'rtl'. If the writing direction is not set, 
     * the method will return null.
     * 
     * @since 1.0
     */
    public function getWritingDir() {
        return $this->contentDir;
    }
    /**
     * Checks if the page will have an aside section or not.
     * 
     * @return boolean true if the page has an aside section.
     * 
     * @since 1.0
     */
    public function hasAside() {
        return $this->incAside;
    }
    /**
     * Checks if the page will have a footer section or not.
     * 
     * @return boolean true if the page has a footer section.
     * 
     * @since 1.0
     */
    public function hasFooter() {
        return $this->incFooter;
    }
    /**
     * Checks if the page will have a header section or not.
     * 
     * @return boolean true if the page has a header section.
     * 
     * @since 1.0
     */
    public function hasHeader() {
        return $this->incHeader;
    }
    /**
     * Sets the value of the property which is used to determine if the 
     * JavaScript variable 'window.i18n' will be included or not.
     * 
     * @param boolean|null $bool true to include it. False to not. Passing null 
     * will cause no change.
     * 
     * @return boolean The method will return true if the variable will be included. 
     * False if not. Default return value is true.
     * 
     * @since 1.0
     */
    public function includeI18nLables($bool = null) {
        if ($bool !== null) {
            $this->includeLables = $bool === true;
        }

        return $this->includeLables;
    }
    /**
     * Display the page in the web browser or gets the rendered document as string.
     * 
     * @param boolean $formatted If this parameter is set to true, the rendered 
     * HTML document will be well formatted and readable. Note that by adding 
     * formatting to the page, the size of rendered HTML document 
     * will increase. The document will be compressed if this 
     * parameter is set to false. Default is false.
     * 
     * @param boolean $returnResult If this parameter is set to true, the method 
     * will return the rendered HTML document as string. Default value is 
     * false.
     * 
     * @return null|HTMLDoc If the parameter <b>$returnResult</b> is set to true, 
     * the method will return an object of type 'HTMLDoc' that represents the rendered page. Other 
     * than that, it will return null.
     * 
     * @since 1.0
     */
    public function render($formatted = false, $returnResult = false) {
        $index = 0;

        foreach ($this->beforeRenderCallbacks as $function) {
            call_user_func_array($function, $this->beforeRenderParams[$index]);
            $index++;
        }

        if ($returnResult) {
            return $this->getDocument();
        } else {
            $formatted = $formatted === true || (defined('WF_VERBOSE') && WF_VERBOSE);
            Response::write($this->getDocument()->toHTML($formatted));
        }
    }
    /**
     * Resets page attributes to default values.
     * 
     * @since 1.0
     */
    public function reset() {
        $this->document = new HTMLDoc();
        $this->_checkLang();
        $this->usingLanguage();

        $websiteName = WebFioriApp::getAppConfig()->getWebsiteName($this->getLangCode());
        $websiteName !== null ? $this->setWebsiteName($websiteName) : $this->setWebsiteName('New Website');

        $websiteDesc = WebFioriApp::getAppConfig()->getDescription($this->getLangCode());
        $websiteDesc !== null ? $this->setWebsiteName($websiteDesc) : '';

        $pageTitle = WebFioriApp::getAppConfig()->getDefaultTitle($this->getLangCode());
        $pageTitle !== null ? $this->setTitle($pageTitle) : $this->setTitle('Hello World');


        $this->setTitleSep(WebFioriApp::getAppConfig()->getTitleSep());

        $langObj = $this->getTranslation();

        if ($langObj !== null) {
            $this->contentDir = $langObj->getWritingDir();
        } else {
            $this->contentDir = 'ltr';
        }

        $this->incFooter = true;
        $this->incHeader = true;
        $this->theme = null;
        $this->incAside = true;
        $this->setWritingDir();
        $this->setCanonical(Request::getRequestedURL());
        $this->document->setLanguage($this->getLangCode());
        $headNode = $this->_getHead();
        $this->document->setHeadNode($headNode);
        $headerNode = new HTMLNode();
        $headerNode->setID(self::MAIN_ELEMENTS[1]);
        $this->document->addChild($headerNode);
        $body = new HTMLNode();
        $body->setID(self::MAIN_ELEMENTS[0]);
        $asideNode = new HTMLNode();
        $asideNode->setID(self::MAIN_ELEMENTS[3]);
        $body->addChild($asideNode);
        $contentArea = new HTMLNode();
        $contentArea->setID(self::MAIN_ELEMENTS[2]);
        $body->addChild($contentArea);
        $this->document->addChild($body);
        $footerNode = new HTMLNode();
        $footerNode->setID(self::MAIN_ELEMENTS[4]);
        $this->document->addChild($footerNode);
        $this->includeLables = false;

        $this->_resetBeforeLoaded();
    }
    /**
     * Adds a child node inside the body of a node given its ID.
     * 
     * @param HTMLNode|string $node The node that will be inserted. Also, 
     * this can be the tag name of the node such as 'div'.
     * 
     * @param string $parentNodeId The ID of the node that the given node 
     * will be inserted to. Default value is 'main-content-area'.
     * 
     * @return HTMLNode|null The method will return the inserted 
     * node if it was inserted. If it is not, the method will return null.
     * 
     * @since 1.0
     */
    public function insert($node, $parentNodeId = self::MAIN_ELEMENTS[2]) {
        if (gettype($node) == 'string') {
            $node = new HTMLNode($node);
        }
        $parent = $this->getChildByID($parentNodeId);
        if ($parent !== null) {
            $parent->addChild($node);
            return $node;
        }
    }
    /**
     * Sets the canonical URL of the page.
     * 
     * Note that if empty string is given, it 
     * won't be set. To unset the canonical, use 'null' as value.
     * 
     * @since 1.0
     * 
     * @param string $url The canonical URL of the page.
     */
    public function setCanonical($url) {
        if (strlen($url) != 0) {
            $this->canonical = $url;

            if ($this->document !== null) {
                $this->document->getHeadNode()->setCanonical($url);
            }
        }
    }
    /**
     * Sets the description of the page.
     * 
     * @param string $val The description of the page. 
     * If null is given, 
     * the description meta tag will be removed from the &lt;head&gt; node. If 
     * empty string is given, nothing will change.
     * 
     * @since 1.0
     */
    public function setDescription($val) {
        if ($val !== null) {
            $trim = trim($val);

            if (strlen($trim) !== 0) {
                $this->description = $trim;
                $this->document->getHeadNode()->addMeta('description', $trim, true);
            }
        } else {
            $descNode = $this->document->getHeadNode()->getMeta('description');
            $this->document->getHeadNode()->removeChild($descNode);
            $this->description = null;
        }
    }
    /**
     * Sets the property that is used to check if page has an aside section or not.
     * 
     * @param boolean $bool true to include aside section. false if 
     * not.
     * 
     * @since 1.0
     */
    public function setHasAside($bool) {
        if (gettype($bool) == self::$BoolType) {
            if (!$this->incAside && $bool) {
                //add aside
                $mainContentArea = $this->document->getChildByID(self::MAIN_ELEMENTS[0]);

                if ($mainContentArea instanceof HTMLNode) {
                    $children = $mainContentArea->children();
                    $currentChCount = $children->size();
                    $mainContentArea->removeAllChildNodes();
                    $mainContentArea->addChild($this->_getAside());
                    $this->incAside = true;

                    for ($x = 0 ; $x < $currentChCount ; $x++) {
                        $mainContentArea->addChild($children->get($x));
                    }
                }
            } else if ($this->incAside && !$bool) {
                //remove aside
                $aside = $this->document->getChildByID(self::MAIN_ELEMENTS[3]);

                if ($aside instanceof HTMLNode) {
                    $this->document->removeChild($aside);
                    $this->incAside = false;
                }
            }
        }
    }

    /**
     * Sets the property that is used to check if page has a footer section or not.
     * 
     * @param boolean $bool true to include the footer section. false if 
     * not.
     * 
     * @since 1.0
     */
    public function setHasFooter($bool) {
        if (gettype($bool) == self::$BoolType) {
            if (!$this->incFooter && $bool) {
                $this->document->addChild($this->_getFooter());
            } else if ($this->incFooter && !$bool) {
                $footer = $this->document->getChildByID(self::MAIN_ELEMENTS[4]);

                if ($footer instanceof HTMLNode) {
                    $this->document->removeChild($footer);
                }
            }
            $this->incFooter = $bool;
        }
    }
    /**
     * Sets the property that is used to check if page has a header section or not.
     * 
     * @param boolean $bool true to include the header section. false if 
     * not.
     * 
     * @since 1.0
     */
    public function setHasHeader($bool) {
        if (gettype($bool) == self::$BoolType) {
            if (!$this->incHeader && $bool) {
                //add the header
                $children = $this->document->getBody()->children();
                $currentChCount = $children->size();
                $this->document->getBody()->removeAllChildNodes();
                $this->document->addChild($this->_getHeader());

                for ($x = 0 ; $x < $currentChCount ; $x++) {
                    $this->document->addChild($children->get($x));
                }
            } else if ($this->incHeader && !$bool) {
                //remove header
                $header = $this->document->getChildByID(self::MAIN_ELEMENTS[1]);

                if ($header instanceof HTMLNode) {
                    $this->document->removeChild($header);
                }
            }
            $this->incHeader = $bool;
        }
    }
    /**
     * Loads a theme given its name.
     * 
     * @param string $themeNameOrClass The name of the theme as specified by the 
     * variable 'name' in theme definition. If the given name is 'null', the 
     * method will load the default theme as specified by the method 
     * 'AppConfig::getBaseThemeName()'. Note that once the theme is updated, 
     * the document content of the page will reset if it was set before calling this 
     * method. This also can be the value which can be taken from 'ClassName::class'. 
     * 
     * @throws Exception The method will throw 
     * an exception if no theme was found which has the given name. Another case is 
     * when the file 'theme.php' of the theme is missing. 
     * Finally, an exception will be thrown if theme component is not found.
     * 
     * @since 1.0
     * 
     * @see Theme::usingTheme()
     */
    public function setTheme($themeNameOrClass = null) {
        if ($themeNameOrClass !== null && strlen(trim($themeNameOrClass)) == 0) {
            return;
        }
        $xthemeName = $themeNameOrClass === null ? WebFioriApp::getAppConfig()->getBaseThemeName() : $themeNameOrClass;
        $tmpTheme = ThemeLoader::usingTheme($xthemeName);

        if ($tmpTheme !== null) {
            if ($this->theme !== null && $tmpTheme->getName() == $this->theme->getName()) {
                return;
            }
            $this->theme = $tmpTheme;
            $this->theme->setPage($this);
            $mainContentArea = $this->getDocument()->getChildByID(self::MAIN_ELEMENTS[2]);

            if ($mainContentArea === null) {
                $mainContentArea = new HTMLNode();
                $mainContentArea->setID(self::MAIN_ELEMENTS[2]);
            }

            $this->document = new HTMLDoc();
            $headNode = $this->_getHead();
            $footerNode = $this->_getFooter();
            $asideNode = $this->_getAside();
            $headerNode = $this->_getHeader();
            $this->document->setLanguage($this->getLangCode());
            $this->document->setHeadNode($headNode);
            $this->document->addChild($headerNode);
            $body = new HTMLNode();
            $body->setID(self::MAIN_ELEMENTS[0]);
            $body->addChild($asideNode);

            $body->addChild($mainContentArea);
            $this->document->addChild($body);
            $this->document->addChild($footerNode);

            $this->theme->invokeAfterLoaded();
        }
    }
    /**
     * Sets the title of the page.
     * 
     * @param string $val The title of the page. If <b>null</b> is given, 
     * the title will not updated. Also note that if page document was created, 
     * calling this method will set the value of the &lt;titlt&gt; node. 
     * The format of the title is <b>PAGE_NAME TITLE_SEP WEBSITE_NAME</b>. 
     * for example, if the page name is 'Home' and title separator is 
     * '|' and the name of the website is 'Programming Academia'. The title 
     * of the page will be 'Home | Programming Academia'.
     * 
     * @since 1.0
     */
    public function setTitle($val) {
        if ($val !== null) {
            $this->title = $val;
            $this->document->getHeadNode()->setTitle($this->getTitle().$this->getTitleSep().$this->getWebsiteName());
        }
    }
    /**
     * Sets the character or string that is used to separate web page title.
     * 
     * The given character or string is used in setting the title of the page. 
     * The format of the title is 'PAGE_NAME TITLE_SEP WEBSITE_NAME'. 
     * for example, if the page name is 'Home' and title separator is 
     * '|' and the name of the web site is 'Programming Academia'. The title 
     * of the page will be 'Home | Programming Academia'.
     * The character be updated only if the given string is not empty. 
     * Also note that if page document was created, 
     * calling this method will set the value of the &lt;titlt&gt; node.
     * 
     * @param string $str The new character or string that will be used to 
     * separate page title and web site name.
     * 
     * @since 1.0
     */
    public function setTitleSep($str) {
        $trimmed = trim($str);

        if (strlen($trimmed) != 0) {
            $this->titleSep = ' '.$trimmed.' ';
            $this->setTitle($this->getTitle());
        }
    }
    /**
     * Sets the name of the web site.
     * 
     * The name of the web site is used in setting the title of the page. 
     * The format of the title is 'PAGE_NAME TITLE_SEP WEBSITE_NAME'. 
     * for example, if the page name is 'Home' and title separator is 
     * '|' and the name of the web site is 'Programming Academia'. The title 
     * of the page will be 'Home | Programming Academia'.
     * The name will be updated only if the given string is not empty. 
     * Also note that if page document was created, 
     * calling this method will set the value of the &lt;titlt&gt; node.
     * 
     * @param string $name The name of the web site that will be appended with the title of 
     * the page. 
     * 
     * @since 1.0
     */
    public function setWebsiteName($name) {
        if (strlen($name) != 0) {
            $this->websiteName = $name;
            $this->setTitle($this->getTitle());
        }
    }
    /**
     * Sets the writing direction of the page.
     * 
     * @param string $dir Language::DIR_LTR or Language::DIR_RTL.
     * 
     * @return boolean True if the direction was not set and its the first time to set. 
     * if it was set before, the method will return false.
     * @throws Exception If the writing direction is not Language::DIR_LTR or Language::DIR_RTL.
     * 
     * @since 1.0
     */
    public function setWritingDir($dir = 'ltr') {
        $dirL = strtolower($dir);

        if ($dirL == Language::DIR_LTR || $dirL == Language::DIR_RTL) {
            $this->contentDir = $dirL;
        }
    }
    /**
     * Sets the language of the page based on session language or 
     * request.
     */
    private function _checkLang() {
        $session = SessionsManager::getActiveSession();
        $langCodeFromSession = $session !== null ? $session->getLangCode(true) : null;

        if ($langCodeFromSession !== null) {
            $this->setLang($langCodeFromSession);
        } else {
            $langCodeFromRequest = Request::getParam('lang');

            if ($langCodeFromRequest !== null) {
                $this->setLang($langCodeFromRequest);
            } else {
                $this->setLang(WebFioriApp::getAppConfig()->getPrimaryLanguage());
            }
        }
    }
    private function _getAside() {
        $loadedTheme = $this->getTheme();
        $node = new HTMLNode();

        if ($loadedTheme !== null) {
            $node = $loadedTheme->getAsideNode();
        }

        if ($loadedTheme !== null && !$node instanceof HTMLNode) {
            throw new UIException('The the method "'.get_class($loadedTheme).'::getAsideNode()" did not return '
                    .'an instance of the class "webfiori\\ui\\HTMLNode".');
        } else {
            $node->setID(self::MAIN_ELEMENTS[3]);
        }

        return $node;
    }

    private function _getFooter() {
        $loadedTheme = $this->getTheme();
        $node = new HTMLNode();

        if ($loadedTheme !== null) {
            $node = $loadedTheme->getFooterNode();
        }

        if ($loadedTheme !== null && !$node instanceof HTMLNode) {
            throw new UIException('The the method "'.get_class($loadedTheme).'::getFooterNode()" did not return '
                    .'an instance of the class "webfiori\\ui\\HTMLNode".');
        } else {
            $node->setID(self::MAIN_ELEMENTS[4]);
        }

        return $node;
    }
    private function _getHead() {
        $loadedTheme = $this->getTheme();

        if ($loadedTheme === null) {
            $headNode = new HeadNode(
                $this->getTitle().$this->getTitleSep().$this->getWebsiteName(),
                $this->getCanonical(),
                WebFioriApp::getAppConfig()->getBaseURL()
            );
        } else {
            $headNode = $loadedTheme->getHeadNode();

            if (!$headNode instanceof HeadNode) {
                throw new UIException('The method "'.get_class($loadedTheme).'::getHeadNode()" did not return '
                        .'an instance of the class "webfiori\\ui\\HeadNode".');
            }
        }
        $headNode->addMeta('charset','UTF-8',true);
        $headNode->setTitle($this->getTitle().$this->getTitleSep().$this->getWebsiteName());
        $headNode->setBase(WebFioriApp::getAppConfig()->getBaseURL());
        $headNode->setCanonical($this->getCanonical());

        if ($this->getDescription() != null) {
            $headNode->addMeta('description', $this->getDescription(), true);
        }

        return $headNode;
    }
    /**
     * Returns the value of the attribute 'href' of the node 'base' of page document.
     * 
     * @return string|null If the base URL is set, the method will return its value. 
     * If the value of the base URL is not set, the method will return null.
     * 
     * @since 1.0
     */
    public function getBase() {
        $headNode = $this->getDocument()->getHeadNode();
        return $headNode->getBaseURL();
    }
    private function _getHeader() {
        $loadedTheme = $this->getTheme();
        $node = new HTMLNode();

        if ($loadedTheme !== null) {
            $node = $loadedTheme->getHeadrNode();
        }

        if ($loadedTheme !== null && !$node instanceof HTMLNode) {
            throw new UIException('The the method "'.get_class($loadedTheme).'::getHeadrNode()" did not return '
                    .'an instance of the class "webfiori\\ui\\HTMLNode".');
        } else {
            $node->setID(self::MAIN_ELEMENTS[1]);
        }

        return $node;
    }
    private function _resetBeforeLoaded() {
        $this->beforeRenderParams = [
            0 => [$this]
        ];
        $this->beforeRenderCallbacks = [function (WebPage $page)
        {
            if ($page->includeI18nLables()) {
                $translation = $page->getTranslation();
                $json = new Json();
                $json->addArray('vars', $translation->getLanguageVars(), true);
                $i18nJs = new HTMLNode('script', [
                    'type' => 'text/javascript',
                    'id' => 'i18n'
                ]);
                $i18nJs->text('window.i18n = '.$json.';', false);
                $page->getDocument()->getHeadNode()->addChild($i18nJs);
            }

            //Load Js and CSS automatically
            $pageTheme = $page->getTheme();

            if ($pageTheme !== null) {
                $themeAssetsDir = 'assets'.DS.$pageTheme->getDirectoryName();

                $jsDir = $themeAssetsDir.DS.$pageTheme->getJsDirName();

                if (Util::isDirectory($jsDir)) {
                    $filesInDir = array_diff(scandir($jsDir), ['.','..']);
                    $fileBase = $page->getThemeJSDir().'/';

                    foreach ($filesInDir as $fileName) {
                        $expl = explode('.', $fileName);

                        if (count($expl) > 0) {
                            $ext = $expl[count($expl) - 1];

                            if ($ext == 'js') {
                                $page->getDocument()->getHeadNode()->addJs($fileBase.$fileName, [
                                    'revision' => $pageTheme->getVersion()
                                ]);
                            }
                        }
                    }
                }

                $cssDir = $themeAssetsDir.DS.$pageTheme->getCssDirName();

                if (Util::isDirectory($cssDir)) {
                    $filesInDir = array_diff(scandir($cssDir), ['.','..']);
                    $fileBase = $page->getThemeCSSDir().'/';

                    foreach ($filesInDir as $fileName) {
                        $expl = explode('.', $fileName);

                        if (count($expl) > 0) {
                            $ext = $expl[count($expl) - 1];

                            if ($ext == 'css') {
                                $page->getDocument()->getHeadNode()->addCSS($fileBase.$fileName, [
                                    'revision' => $pageTheme->getVersion()
                                ]);
                            }
                        }
                    }
                }
            }
        }];
    }
    /**
     * Returns the canonical URL of the page.
     * 
     * @return null|string The method will return the  canonical URL of the page 
     * if set. If not, the method will return null.
     * 
     * @since 1.0
     */
    public function getCanonical() {
        return $this->canonical;
    }
    /**
     * Returns the description of the page.
     * 
     * @return string|null The description of the page. If the description is not set, 
     * the method will return null.
     * 
     * @since 1.0
     */
    public function getDescription() {
        return $this->description;
    }
    /**
     * Returns the name of the web site.
     * 
     * @return string The name of the web site. If the name was not set 
     * using the method WebPage::siteName(), the returned value will 
     * be 'My X Website'.
     * 
     * @since 1.0
     */
    public function getWebsiteName() {
        return $this->websiteName;
    }
    /**
     * Checks if a theme is loaded or not.
     * 
     * @return boolean true if loaded. false if not loaded.
     * 
     * @since 1.0
     */
    public function isThemeLoaded() {
        return $this->theme instanceof Theme;
    }
    /**
     * Sets the display language of the page.
     * 
     * The length of the given string must be 2 characters in order to set the 
     * language code.
     * 
     * @param string $lang a two digit language code such as AR or EN. Default 
     * value is 'EN'.
     * 
     * @since 1.0
     */
    public function setLang($lang = 'EN') {
        $langU = strtoupper(trim($lang));

        if (strlen($lang) == 2) {
            $this->contentLang = $langU;

            if ($this->document !== null) {
                $this->document->setLanguage($langU);
            }
            $this->usingLanguage();
        }
    }
    /**
     * Returns the session which is currently active.
     * 
     * @return Session|null If a session is active, the method will return its 
     * data stored in an object. If no session is active, the method will return 
     * null.
     * 
     * @since 1.0
     */
    public function getActiveSession() {
        return SessionsManager::getActiveSession();
    }
    /**
     * Load the translation file based on the language code. 
     * 
     * The method uses 
     * two checks to load the translation. If the page language is set using 
     * the method WebPage::getLanguageCode(), then the language that will be loaded 
     * will be based on the value returned by the method Page::getLanguageCode(). If 
     * the language of the page is not set, The method will throw an exception.
     * 
     * @throws MissingLangException An exception will be thrown if no language file 
     * was found that matches the given language code. Language files must 
     * have the name 'LanguageXX.php' where 'XX' is language code. Also the function 
     * will throw an exception when the translation file is loaded but no object 
     * of type 'Language' was stored in the set of loaded translations.
     * 
     * @since 1.0
     */
    private function usingLanguage() {
        if ($this->getLangCode() !== null) {
            try {
                $this->tr = Language::loadTranslation($this->getLangCode());
            } catch (MissingLangException $ex) {
                throw new MissingLangException($ex->getMessage());
            }
            $pageLang = $this->getTranslation();
            $this->setWritingDir($pageLang->getWritingDir());
        }
    }
}
