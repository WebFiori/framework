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

use Exception;
use phpStructs\html\HeadNode;
use phpStructs\html\HTMLDoc;
use phpStructs\html\HTMLNode;
use webfiori\conf\SiteConfig;
use webfiori\entity\langs\Language;
use webfiori\entity\ThemeLoader;
/**
 * A class used to initialize view components.
 * This class is one of the core components for creating web pages. It is simply 
 * represents a web page. By default class has a HTML document that contains 
 * the following basic elements:
 * <ul>
 * <li>Head tag that contains CSS, JS and other meta and link tags.</li>
 * <li>A div element which has the ID 'page-body' that represents the body 
 * of the page.</li>
 * <li>A div element which has the ID 'page-header' that represents the header 
 * section of the page.</li>
 * <li>A div element which has the ID 'page-footer' that represents the footer 
 * section of the page.</li>
 * <li>A div element which has the ID 'main-content-area' that represents the area 
 * at which user content will be added to.</li>
 * <li>A div element which has the ID 'side-content-area' that represents the side 
 * section of the page.</li>
 * </ul>
 * In addition to that, this class can be used to set some of the basic attributes 
 * of the page including page language, title, description, writing direction 
 * and canonical URL. Also, this class can be used to load a specific theme 
 * and use it to change the look and feel of the web site.
 * @author Ibrahim
 * @version 1.9.3
 */
class Page {
    /**
     * An array that contains the IDs of the 3 main page elements.
     * The array has the following values:
     * <ul>
     * <li>page-body</li>
     * <li>page-header</li>
     * <li>main-content-area</li>
     * <li>side-content-area</li>
     * <li>page-footer</li>
     * </ul>
     * @since 1.9.2
     */
    const MAIN_ELEMENTS = [
        'page-body',
        'page-header',
        'main-content-area',
        'side-content-area',
        'page-footer'
    ];
    /**
     * A closure which will be called before the page is fully sent back.
     * @var callable|null 
     */
    private $beforeRender;
    /**
     *
     * @var string
     * @since 1.9.3 
     */
    private static $BoolType = 'boolean';
    /**
     * The canonical page URL.
     * @var type 
     * @since 1.2
     */
    private $canonical;
    /**
     * The writing direction of the page.
     * @var string
     * @since 1.0 
     */
    private $contentDir;
    /**
     * The language of the page.
     * @var string
     * @since 1.0 
     */
    private $contentLang;
    /**
     * The description of the page.
     * @var string
     * @since 1.0 
     */
    private $description;
    /**
     * The document that represents the page.
     * @var HTMLDoc 
     * @since 1.4
     */
    private $document;
    /**
     * A variable that is set to <b>true</b> if page has aside area.
     * @var boolean A variable that is set to <b>true</b> if page has footer.
     * @since 1.2 
     */
    private $incAside;
    /**
     * A variable that is set to <b>true</b> if page has footer.
     * @var boolean A variable that is set to <b>true</b> if page has footer.
     * @since 1.2 
     */
    private $incFooter;
    /**
     * A variable that is set to <b>true</b> if page has header.
     * @var boolean A variable that is set to <b>true</b> if page has header.
     * @since 1.2 
     */
    private $incHeader;
    /**
     * A single instance of the class.
     * @var Page 
     * @since 1.0
     */
    private static $instance;
    /**
     * An object of type <b>Theme</b> that contains loaded theme info.
     * @var Theme 
     * @since 1.6
     */
    private $theme;
    /**
     * The title of the page.
     * @var string
     * @since 1.0 
     */
    private $title;
    /**
     * The character or string that is used to separate web page title 
     * and web site name.
     * @var string
     * @since 1.8 
     */
    private $titleSep;
    /**
     * The name of the web site that will be appended with the title of 
     * the page.
     * @var string 
     * @since 1.8
     */
    private $websiteName;
    private function __construct() {
        $this->_reset();
    }
    /**
     * Sets or checks if the page will have aside area or not.
     * @param boolean|null $bool If set to true, the generated page 
     * will have a 'div' element with ID = 'side-content-area'. If set to 
     * false, the generated page will have no such element.
     * @return boolean The method will return true if the page will have 
     * aside area.
     */
    public static function aside($bool = null) {
        $p = Page::get();

        if ($bool !== null) {
            $p->setHasAside($bool);
        }

        return $p->hasAside();
    }
    /**
     * Sets or gets a function which will be executed before the page is rendered.
     * The function will be executed when the method 'Page::render()' is called. 
     * One possible use is to do some modifications to the DOM before the 
     * page is displayed.
     * @param callback $callable A PHP function that will be get executed. If 
     * null is passed, the callable will be unset. If anything else is passed, 
     * it will be ignored.
     * Returns the function that will be get executed before the view is completely 
     * rendered.
     * @return callable|null If the callable is set, the method will return 
     * it. Other than that, the method will return null.
     * @since 1.9.1
     */
    public static function beforeRender($callable = '') {
        if (is_callable($callable)) {
            self::get()->beforeRender = $callable;
        } else {
            if ($callable === null) {
                self::get()->beforeRender = null;
            }
        }

        return self::get()->beforeRender;
    }
    /**
     * Sets or gets the canonical URL of the page.
     * Note that it will be set automatically but the developer can change 
     * it if he would like to.
     * @param string $new The new canonical URL. If null is given, the 
     * method will not update the canonical URL.
     * @return string The canonical URL of the page. 
     * @since 1.9
     */
    public static function canonical($new = null) {
        $p = Page::get();

        if ($new != null && strlen($new) != 0) {
            $p->setCanonical($new);
        }

        return $p->getCanonical();
    }
    /**
     * Returns the directory at which CSS files of loaded theme exists.
     * @return string The directory at which CSS files of the theme exists 
     * (e.g. 'themes/my-theme/css' ). 
     * If the theme is not loaded, the method will return empty string.
     * @since 1.9
     */
    public static function cssDir() {
        return Page::get()->getThemeCSSDir();
    }
    /**
     * Sets or gets the description of the page.
     * @param string $new The description of the page. If <b>empty string</b> is given, 
     * the description meta tag will be removed from the &lt;head&gt; element. If 
     * null is given, nothing will change. Default value is null.
     * @since 1.9
     * @return string The description of the page. If it is not set, the method 
     * will return null.
     */
    public static function description($new = null) {
        $p = Page::get();
        $p->setDescription($new);

        return $p->getDescription();
    }
    /**
     * Sets or gets page writing direction.
     * Note that the writing direction of the page might change depending 
     * on loaded translation file.
     * @param string $new 'ltr' or 'rtl'. If something else is given, nothing 
     * will change.
     * @return string ltr' or 'rtl'. Default return value is null
     * @since 1.9
     */
    public static function dir($new = null) {
        $p = Page::get();
        $p->setWritingDir($new);

        return $p->getWritingDir();
    }
    /**
     * Returns the document that is linked with the page.
     * @return HTMLDoc The document that is linked with the page.
     * @since 1.9
     */
    public static function document() {
        return Page::get()->getDocument();
    }
    /**
     * Sets or checks if the page will have footer area or not.
     * @param boolean|null $bool If set to true, the generated page 
     * will have a 'div' element with ID = 'page-footer'. If set to 
     * false, the generated page will have no such element.
     * @return boolean The method will return true if the page will have 
     * footer area.
     */
    public static function footer($bool = null) {
        $p = Page::get();

        if ($bool !== null) {
            $p->setHasFooter($bool);
        }

        return $p->hasFooter();
    }
    /**
     * Sets or checks if the page will have header area or not.
     * @param boolean|null $bool If set to true, the generated page 
     * will have a 'div' element with ID = 'page-header'. If set to 
     * false, the generated page will have no such element.
     * @return boolean The method will return true if the page will have 
     * header area.
     */
    public static function header($bool = null) {
        $p = Page::get();

        if ($bool !== null) {
            $p->setHasHeader($bool);
        }

        return $p->hasHeader();
    }
    /**
     * Returns the directory at which image files of loaded theme exists.
     * @return string The directory at which image files of the theme exists 
     * (e.g. 'themes/my-theme/images' ). 
     * If the theme is not loaded, the method will return empty string.
     * @since 1.9
     */
    public static function imagesDir() {
        return Page::get()->getThemeImagesDir();
    }
    /**
     * Adds a child node inside the body of a node given its ID.
     * @param HTMLNode $node The node that will be inserted.
     * @param string $parentNodeId The ID of the node that the given node 
     * will be inserted to.
     * @return boolean The method will return true if the given node 
     * was inserted. If it is not, the method will return false.
     * @since 1.9
     */
    public static function insert($node,$parentNodeId = self::MAIN_ELEMENTS[2]) {
        return Page::get()->insertNode($node, $parentNodeId);
    }
    /**
     * Returns the directory at which JavaScript files of the theme exists.
     * @return string The directory at which JavaScript files of the theme exists 
     * (e.g. 'themes/my-theme/js' ). 
     * If the theme is not loaded, the method will return empty string.
     * @since 1.9
     */
    public static function jsDir() {
        return Page::get()->getThemeJSDir();
    }
    /**
     * Sets or gets language code of the page.
     * @param string $new A two digit language code such as AR or EN. 
     * @return string|null Two digit language code. In case language is not set, the 
     * method will return null
     * @since 1.9
     */
    public static function lang($new = null) {
        $p = Page::get();

        if ($new !== null && strlen($new) == 2) {
            $p->setLang($new);
        }

        return $p->getLang();
    }
    /**
     * Display the page in the web browser or gets the rendered document as string.
     * @param boolean $returnResult If this parameter is set to true, the method 
     * will return the rendered HTML document as string. Default value is 
     * false.
     * @param boolean $formatted If this parameter is set to true, the rendered 
     * HTML document will be well formatted and readable. Note that by adding 
     * formatting to the page, the size of rendered HTML document 
     * will increase. Default is false.
     * @return null|string If the parameter <b>$returnResult</b> is set to true, 
     * the method will return a string that represents the rendered page. Other 
     * than that, it will return null.
     * @since 1.9
     */
    public static function render($returnResult = false,$formatted = false) {
        if (Page::get()->beforeRender !== null) {
            call_user_func(Page::get()->beforeRender);
        }

        if ($returnResult) {
            return Page::get()->getDocument()->toHTML($formatted);
        } else {
            echo Page::get()->getDocument()->toHTML($formatted);
        }
    }
    /**
     * Reset the page to its defaults.
     */
    public static function reset() {
        Page::get()->_reset();
    }
    /**
     * Sets or returns the string that is used to separate web site name from 
     * the title of the page.
     * @param string $new The new title separator.
     * @return string The string that is used to separate web site name from 
     * the title of the page. Note that a space will be appended to the start 
     * and to the end of the returned value. For example, if separator is set 
     * to the character '|', then the method will return the string ' | '.
     * @since 1.9
     */
    public static function separator($new = null) {
        $p = Page::get();

        if ($new != null && strlen($new) != 0) {
            $p->setTitleSep($new);
        }

        return $p->getTitleSep();
    }
    /**
     * Sets or returns the name of page web site.
     * @param string $new The new name to set. It must be non-empty 
     * string in order to update.
     * @return string The name of page web site. Default is 'Hello Website'.
     * @since 1.9
     */
    public static function siteName($new = null) {
        $p = Page::get();

        if ($new != null && strlen($new) != 0) {
            $p->setWebsiteName($new);
        }

        return $p->getWebsiteName();
    }
    /**
     * Loads or returns page theme.
     * @param string $name The name of the theme which will be 
     * loaded. If null is given, the method will get theme name from the 
     * class 'SiteConfig' and try to load it. If empty string is given, 
     * nothing will be loaded.
     * @return Theme If a theme is loaded, the method will 
     * return it contained in an object of type 'Theme'. If no 
     * theme is loaded, the method will return null.
     * @see Page::usingTheme()
     * @since 1.9
     */
    public static function theme($name = null) {
        $p = Page::get();
        $p->usingTheme($name);

        return $p->getTheme();
    }
    /**
     * Sets or gets the title of the page.
     * The format of the title is <b>PAGE_NAME TITLE_SEP WEBSITE_NAME</b>. 
     * for example, if the page name is 'Home' and title separator is 
     * '|' and the name of the website is 'Programming Academia'. The title 
     * of the page will be 'Home | Programming Academia'. In this case, the value 
     * that must be supplied to this method is 'Home'.
     * @param string $new The title of the page. 
     * @since 1.9
     * @return string The title of the page. Default return value is 'Hello World'.
     */
    public static function title($new = null) {
        $p = Page::get();

        if ($new != null && strlen($new) != 0) {
            $p->setTitle($new);
        }

        return $p->getTitle();
    }
    /**
     * Loads and returns translation based on page language code.
     * Note that page language must be set before calling this method in 
     * order to load a translation file. Translations can be found in 
     * the folder '/entity/lang'. Also, the method will throw an exception 
     * in case language file is not found or not initialized correctly.
     * @return Language|null An object of type Language is returned 
     * if the language is loaded. Other than that, the method will return 
     * null.
     * @throws Exception
     * @since 1.9
     */
    public static function translation() {
        $p = Page::get();

        if ($p->getLang() != null) {
            $p->usingLanguage();

            return $p->getLanguage();
        }

        return null;
    }

    private function _getAside() {
        $loadedTheme = $this->getTheme();
        $node = null;

        if ($loadedTheme !== null) {
            $node = $loadedTheme->getAsideNode();
        }

        if ($node instanceof HTMLNode) {
            $node->setID(self::MAIN_ELEMENTS[3]);
        } else {
            $node = new HTMLNode();
            $node->setID(self::MAIN_ELEMENTS[3]);
        }

        return $node;
    }

    private function _getFooter() {
        $loadedTheme = $this->getTheme();
        $node = null;

        if ($loadedTheme !== null) {
            $node = $loadedTheme->getFooterNode();
        }

        if ($node instanceof HTMLNode) {
            $node->setID(self::MAIN_ELEMENTS[4]);
        } else {
            $node = new HTMLNode();
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
                SiteConfig::getBaseURL()
            );
        } else {
            $headNode = $loadedTheme->getHeadNode();
        }
        $headNode->addMeta('charset','UTF-8',true);
        $headNode->setTitle($this->getTitle().$this->getTitleSep().$this->getWebsiteName());
        $headNode->setBase(SiteConfig::getBaseURL());
        $headNode->setCanonical($this->getCanonical());

        if ($this->getDescription() != null) {
            $headNode->addMeta('description', $this->getDescription(), true);
        }

        return $headNode;
    }

    private function _getHeader() {
        $loadedTheme = $this->getTheme();
        $node = null;

        if ($loadedTheme !== null) {
            $node = $loadedTheme->getHeadrNode();
        }

        if ($node instanceof HTMLNode) {
            $node->setID(self::MAIN_ELEMENTS[1]);
        } else {
            $node = new HTMLNode();
            $node->setID(self::MAIN_ELEMENTS[1]);
        }

        return $node;
    }
    private function _reset() {
        $this->document = new HTMLDoc();
        $this->beforeRender = null;
        $this->setTitle('Hello World');
        $this->setWebsiteName('Hello Website');
        $this->setTitleSep('|');
        $this->contentDir = 'ltr';
        $this->description = null;
        $this->contentLang = null;
        $this->incFooter = true;
        $this->incHeader = true;
        $this->theme = null;
        $this->incAside = true;
        $this->setWritingDir();
        $this->setCanonical(Util::getRequestedURL());
        $this->document->setLanguage($this->getLang());
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
    }
    /**
     * Returns a single instance of 'Page'
     * @return Page an instance of 'Page'.
     * @since 1.0
     */
    private static function get() {
        if (self::$instance === null) {
            self::$instance = new Page();
        }

        return self::$instance;
    }
    /**
     * Returns the canonical URL of the page.
     * @return null|string The method will return the  canonical URL of the page 
     * if set. If not, the method will return null.
     * @since 1.2
     */
    private function getCanonical() {
        return $this->canonical;
    }
    /**
     * Returns the description of the page.
     * @return string|null The description of the page. If the description is not set, 
     * the method will return null.
     * @since 1.0
     */
    private function getDescription() {
        return $this->description;
    }
    /**
     * Returns the document that is associated with the page.
     * @return HTMLDoc An object of type 'HTMLDoc'.
     * @since 1.1
     */
    private function getDocument() {
        return $this->document;
    }
    /**
     * Returns the language code of the page.
     * @return string|null Two digit language code. In case language is not set, the 
     * method will return null
     * @since 1.0
     */
    private function getLang() {
        return $this->contentLang;
    }
    /**
     * Returns the language variables based on loaded translation.
     * @return Language|null an object of type 'Language' if language 
     * is loaded. If no language found, 'null' is returned. This 
     * method should be called after calling the method 'Page::loadTranslation()' in 
     * order for the method to return non-null value.
     * @since 1.6
     */
    private function getLanguage() {
        $loadedLangs = Language::getLoadedLangs();

        if (isset($loadedLangs[$this->getLang()])) {
            return $loadedLangs[$this->getLang()];
        }
    }
    /**
     * Returns an object of type 'Theme' that contains loaded theme information.
     * @return Theme|null An object of type Theme that contains theme information. If the theme 
     * is not loaded, the method will return null.
     * @since 1.6
     */
    private function getTheme() {
        return $this->theme;
    }
    /**
     * Returns the directory at which CSS files of the theme exists.
     * @return string The directory at which CSS files of the theme exists 
     * (e.g. 'publish/my-theme/css' ). 
     * If the theme is not loaded, the method will return empty string.
     * @since 1.6
     */
    private function getThemeCSSDir() {
        if ($this->isThemeLoaded()) {
            $loadedTheme = $this->getTheme();

            return Theme::THEMES_DIR.'/'.$loadedTheme->getDirectoryName().'/'.$loadedTheme->getCssDirName();
        }

        return '';
    }
    /**
     * Returns the directory at which image files of the theme exists.
     * @return string The directory at which image files of the theme exists 
     * (e.g. 'publish/my-theme/images' ). 
     * If the theme is not loaded, the method will return empty string.
     * @since 1.6
     */
    private function getThemeImagesDir() {
        if ($this->isThemeLoaded()) {
            $loadedTheme = $this->getTheme();

            return Theme::THEMES_DIR.'/'.$loadedTheme->getDirectoryName().'/'.$loadedTheme->getImagesDirName();
        }

        return '';
    }
    /**
     * Returns the directory at which JavaScript files of the theme exists.
     * @return string The directory at which JavaScript files of the theme exists 
     * (e.g. 'publish/my-theme/js' ). 
     * If the theme is not loaded, the method will return empty string.
     * @since 1.6
     */
    private function getThemeJSDir() {
        if ($this->isThemeLoaded()) {
            $loadedTheme = $this->getTheme();

            return Theme::THEMES_DIR.'/'.$loadedTheme->getDirectoryName().'/'.$loadedTheme->getJsDirName();
        }

        return '';
    }
    /**
     * Returns the title of the page.
     * @return string|null The title of the page. Default return value is 
     * 'Default X'.
     * @since 1.0
     */
    private function getTitle() {
        return $this->title;
    }
    /**
     * Returns the character or string that is used to separate web page title 
     * and web site name.
     * @return string The character or string that is used to separate web page title 
     * and web site name. If the separator was not set 
     * using the method <b>Page::setTitleSep()</b>, the returned value will 
     * be ' | '.
     * @since 1.8
     */
    private function getTitleSep() {
        return $this->titleSep;
    }
    /**
     * Returns the name of the web site.
     * @return string The name of the web site. If the name was not set 
     * using the method Page::siteName(), the returned value will 
     * be 'My X Website'.
     * @since 1.8
     */
    private function getWebsiteName() {
        return $this->websiteName;
    }
    /**
     * Returns the writing direction of the page.
     * @return string|null 'ltr' or 'rtl'. If the writing direction is not set, 
     * the method will return null.
     * @since 1.0
     */
    private function getWritingDir() {
        return $this->contentDir;
    }
    /**
     * Checks if the page will have an aside section or not.
     * @return boolean true if the page has an aside section.
     * @since 1.6
     */
    private function hasAside() {
        return $this->incAside;
    }
    /**
     * Checks if the page will have a footer section or not.
     * @return boolean true if the page has a footer section.
     * @since 1.2
     */
    private function hasFooter() {
        return $this->incFooter;
    }
    /**
     * Checks if the page will have a header section or not.
     * @return boolean true if the page has a header section.
     * @since 1.2
     */
    private function hasHeader() {
        return $this->incHeader;
    }
    /**
     * Adds a child node inside the body of a node given its ID.
     * @param HTMLNode $node The node that will be inserted.
     * @param string $parentNodeId The ID of the node that the given node 
     * will be inserted to.
     * @return boolean The method will return true if the given node 
     * was inserted. If it is not, the method will return false.
     * @since 1.6
     */
    private function insertNode($node,$parentNodeId = '') {
        $retVal = false;

        if (strlen($parentNodeId) != 0 && $node instanceof HTMLNode) {
            $parentNode = $this->document->getChildByID($parentNodeId);

            if ($parentNode instanceof HTMLNode) {
                $parentNode->addChild($node);
                $retVal = true;
            }
        }

        return $retVal;
    }
    /**
     * Checks if a theme is loaded or not.
     * @return boolean true if loaded. false if not loaded.
     * @since 1.1
     */
    private function isThemeLoaded() {
        return $this->theme instanceof Theme;
    }
    /**
     * Sets the canonical URL of the page.
     * @since 1.2
     * @param string $url The canonical URL of the page.
     */
    private function setCanonical($url) {
        if (strlen($url) != 0) {
            $this->canonical = $url;

            if ($this->document !== null) {
                $this->document->getHeadNode()->setCanonical($url);
            }
        }
    }
    /**
     * Sets the description of the page.
     * @param string $val The description of the page. If <b>null</b> is given, 
     * the description meta tag will be removed from the &lt;head&gt; node. If 
     * empty string is given, nothing will change.
     * @since 1.0
     */
    private function setDescription($val) {
        if ($val !== null) {
            $desc = trim($val);

            if (strlen($desc) !== 0) {
                $this->description = $desc;
                $this->document->getHeadNode()->addMeta('description', $desc, true);
            } else {
                $descNode = $this->document->getHeadNode()->getMeta('description');
                $this->document->getHeadNode()->removeChild($descNode);
                $this->description = null;
            }
        }
    }
    /**
     * Sets the property that is used to check if page has an aside section or not.
     * @param boolean $bool true to include aside section. false if 
     * not.
     * @since 1.2
     */
    private function setHasAside($bool) {
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
            } else {
                if ($this->incAside && !$bool) {
                    //remove aside
                    $aside = $this->document->getChildByID(self::MAIN_ELEMENTS[3]);

                    if ($aside instanceof HTMLNode) {
                        $this->document->removeChild($aside);
                        $this->incAside = false;
                    }
                }
            }
        }
    }

    /**
     * Sets the property that is used to check if page has a footer section or not.
     * @param boolean $bool true to include the footer section. false if 
     * not.
     * @since 1.2
     */
    private function setHasFooter($bool) {
        if (gettype($bool) == self::$BoolType) {
            if (!$this->incFooter && $bool) {
                $this->document->addChild($this->_getFooter());
            } else {
                if ($this->incFooter && !$bool) {
                    $footer = $this->document->getChildByID(self::MAIN_ELEMENTS[4]);

                    if ($footer instanceof HTMLNode) {
                        $this->document->removeChild($footer);
                    }
                }
            }
            $this->incFooter = $bool;
        }
    }
    /**
     * Sets the property that is used to check if page has a header section or not.
     * @param boolean $bool true to include the header section. false if 
     * not.
     * @since 1.2
     */
    private function setHasHeader($bool) {
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
            } else {
                if ($this->incHeader && !$bool) {
                    //remove header
                    $header = $this->document->getChildByID(self::MAIN_ELEMENTS[1]);

                    if ($header instanceof HTMLNode) {
                        $this->document->removeChild($header);
                    }
                }
            }
            $this->incHeader = $bool;
        }
    }
    /**
     * Sets the display language of the page.
     * The length of the given string must be 2 characters in order to set the 
     * language code.
     * @param string $lang a two digit language code such as AR or EN.
     * @since 1.0
     */
    private function setLang($lang = 'EN') {
        $langU = strtoupper(trim($lang));

        if (strlen($lang) == 2) {
            $this->contentLang = $langU;

            if ($this->document != null) {
                $this->document->setLanguage($langU);
            }
        }
    }
    /**
     * Sets the title of the page.
     * @param string $val The title of the page. If <b>null</b> is given, 
     * the title will not updated. Also note that if page document was created, 
     * calling this method will set the value of the &lt;titlt&gt; node. 
     * The format of the title is <b>PAGE_NAME TITLE_SEP WEBSITE_NAME</b>. 
     * for example, if the page name is 'Home' and title separator is 
     * '|' and the name of the website is 'Programming Academia'. The title 
     * of the page will be 'Home | Programming Academia'.
     * @since 1.0
     */
    private function setTitle($val) {
        if ($val != null) {
            $this->title = $val;
            $this->document->getHeadNode()->setTitle($this->getTitle().$this->getTitleSep().$this->getWebsiteName());
        }
    }
    /**
     * Sets the character or string that is used to separate web page title.
     * The given character or string is used in setting the title of the page. 
     * The format of the title is 'PAGE_NAME TITLE_SEP WEBSITE_NAME'. 
     * for example, if the page name is 'Home' and title separator is 
     * '|' and the name of the web site is 'Programming Academia'. The title 
     * of the page will be 'Home | Programming Academia'.
     * The character be updated only if the given string is not empty. 
     * Also note that if page document was created, 
     * calling this method will set the value of the &lt;titlt&gt; node.
     * @param string $str The new character or string that will be used to 
     * separate page title and web site name.
     * @since 1.8
     */
    private function setTitleSep($str) {
        $trimmed = trim($str);

        if (strlen($trimmed) != 0) {
            $this->titleSep = ' '.$trimmed.' ';
            $this->setTitle($this->getTitle());
        }
    }
    /**
     * Sets the name of the web site.
     * The name of the web site is used in setting the title of the page. 
     * The format of the title is 'PAGE_NAME TITLE_SEP WEBSITE_NAME'. 
     * for example, if the page name is 'Home' and title separator is 
     * '|' and the name of the web site is 'Programming Academia'. The title 
     * of the page will be 'Home | Programming Academia'.
     * The name will be updated only if the given string is not empty. 
     * Also note that if page document was created, 
     * calling this method will set the value of the &lt;titlt&gt; node.
     * @param string $name The name of the web site that will be appended with the title of 
     * the page. 
     * @since 1.8
     */
    private function setWebsiteName($name) {
        if (strlen($name) != 0) {
            $this->websiteName = $name;
            $this->setTitle($this->getTitle());
        }
    }
    /**
     * Sets the writing direction of the page.
     * @param string $dir Page::DIR_LTR or Page::DIR_RTL.
     * @return boolean True if the direction was not set and its the first time to set. 
     * if it was set before, the method will return false.
     * @throws Exception If the writing direction is not Page::DIR_LTR or Page::DIR_RTL.
     * @since 1.0
     */
    private function setWritingDir($dir = 'ltr') {
        $dirL = strtolower($dir);

        if ($dirL == Language::DIR_LTR || $dirL == Language::DIR_RTL) {
            $this->contentDir = $dirL;
        }
    }
    /**
     * Load the translation file based on the language code. The method uses 
     * two checks to load the translation. If the page language is set using 
     * the method Page::lang(), then the language that will be loaded 
     * will be based on the value returned by the method Page::lang(). If 
     * the language of the page is not set, The method will throw an exception.
     * @since 1.0
     */
    private function usingLanguage() {
        if ($this->getLang() != null) {
            Language::loadTranslation($this->getLang());
            $pageLang = $this->getLanguage();
            $this->setWritingDir($pageLang->getWritingDir());
        }
    }
    /**
     * Loads a theme given its name.
     * @param string $themeName The name of the theme as specified by the 
     * variable 'name' in theme definition. If the given name is 'null', the 
     * method will load the default theme as specified by the method 
     * 'SiteConfig::getBaseThemeName()'. Note that once the theme is updated, 
     * the document content of the page will reset if it was set before calling this 
     * method.
     * @throws Exception The method will throw 
     * an exception if no theme was found which has the given name. Another case is 
     * when the file 'theme.php' of the theme is missing. 
     * Finally, an exception will be thrown if theme component is not found.
     * @since 1.4
     * @see Theme::usingTheme()
     */
    private function usingTheme($themeName = null) {
        if ($themeName === null && $this->theme === null) {
            $themeName = SiteConfig::getBaseThemeName();
        } else {
            $themeName = trim($themeName);

            if (strlen($themeName) == 0) {
                return;
            }
        }

        if ($this->theme !== null) {
            if ($themeName != $this->theme->getName()) {
                $tmpTheme = ThemeLoader::usingTheme($themeName);
            } else {
                return;
            }
        } else {
            $tmpTheme = ThemeLoader::usingTheme($themeName);
        }
        $this->theme = $tmpTheme;
        $this->document = new HTMLDoc();
        $headNode = $this->_getHead();
        $footerNode = $this->_getFooter();
        $asideNode = $this->_getAside();
        $headerNode = $this->_getHeader();
        $this->document->setLanguage($this->getLang());
        $this->document->setHeadNode($headNode);
        $this->document->addChild($headerNode);
        $body = new HTMLNode();
        $body->setID(self::MAIN_ELEMENTS[0]);
        $body->addChild($asideNode);
        $mainContentArea = new HTMLNode();
        $mainContentArea->setID(self::MAIN_ELEMENTS[2]);
        $body->addChild($mainContentArea);
        $this->document->addChild($body);
        $this->document->addChild($footerNode);
        $this->theme->invokeAfterLoaded();
    }
}
