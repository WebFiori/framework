<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Ui;

use Error;
use Exception;
use TypeError;
use WebFiori\Collections\LinkedList;
use WebFiori\Framework\App;
use WebFiori\Framework\Exceptions\InitializationException;
use WebFiori\Framework\Exceptions\MissingLangException;
use WebFiori\Framework\Exceptions\SessionException;
use WebFiori\Framework\Exceptions\UIException;
use WebFiori\Framework\Lang;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Session\Session;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\Theme;
use WebFiori\Framework\ThemeManager;
use WebFiori\Framework\Util;
use WebFiori\Http\Request;
use WebFiori\Http\Response;
use WebFiori\Json\Json;
use WebFiori\Ui\HeadNode;
use WebFiori\Ui\HTMLDoc;
use WebFiori\Ui\HTMLNode;
require_once 'ui-functions.php';
/**
 * A base class that can be used to implement web pages.
 *
 * @author Ibrahim
 *
 * @version 1.0.1
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
     * A linked list that contains closures
     * which will be called before the page is fully rendered.
     *
     * @var LinkedList
     *
     * @since 1.0
     */
    private $beforeRenderCallbacks;
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
     * A lock to disable language loading status during class initialization
     * stage.
     *
     * @var boolean
     *
     * @since 1.0.1
     */
    private $skipLangCheck;
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
     * @var Lang|null
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
        $this->title = '';
        $this->titleSep = '|';
        global $page;
        $page = $this;
        $this->reset();
    }
    /**
     * Adds a function which will be executed before the page is fully rendered.
     *
     * The function will be executed when the method 'WebPage::render()' is called.
     * One possible use is to do some modifications to the DOM before the
     * page is displayed. It is possible to have multiple callbacks.
     *
     * @param callable $callable A PHP function that will be get executed. before
     * the page is rendered. Note that the first argument of the function will
     * always be an object of type "WebPage".
     *
     * @param int $priority A positive number that represents the priority of
     * the callback. Large number means that
     * the callback has higher priority. This means a callback with priority
     * 100 will have higher priority than a callback with priority 80. If
     * a negative number is provided, 0 will be set as its priority.
     *
     * @param array $params An array of parameters which will be passed to the
     * callback. The parameters can be accessed in the callback in the
     * same order at which they appear in the array.
     *
     *
     * @return BeforeRenderCallback The method will return the added callback as
     * an object of type 'BeforeRenderCallback'.
     *
     * @since 1.0
     */
    public function &addBeforeRender(callable $callable, $priority = 0, array $params = []) : BeforeRenderCallback {
        $beforeRender = new BeforeRenderCallback($callable, $priority, $params);
        $this->beforeRenderCallbacks->add($beforeRender);

        return $beforeRender;
    }
    /**
     * Adds new CSS source file.
     *
     * @param string $href The link to the file. Must be non empty string. It is
     * possible to append query string to the end of the link.
     *
     * @param array $attrs An associative array of additional attributes
     * to set for the node. One special attribute has the name 'revision'. If
     * set to true, a query string parameter in the form '?cv=x.x' is appended
     * to the 'href' attribute value. The 'x.x' represent application version
     * taken from the class 'AppConfig' Default value of the attribute is true.
     *
     * @since 1.0
     */
    public function addCSS(string $href, array $attrs = []) {
        if (!isset($attrs['revision'])) {
            $attrs['revision'] = $this->getConfigVar('getAppVersion', '1.0');
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
     * to set for the JavaScript node. One special attribute has the name 'revision'. If
     * set to true, a query string parameter in the form '?jv=x.x' is appended
     * to the 'href' attribute value. The 'x.x' represent application version
     * taken from the class 'AppConfig' Default value of the attribute is true.
     *
     * @since 1.0
     */
    public function addJS(string $src, array $attrs = []) {
        if (!isset($attrs['revision'])) {
            $attrs['revision'] = $this->getConfigVar('getAppVersion', '1.0');
        }
        $this->getDocument()->getHeadNode()->addJs($src, $attrs);
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
    public function addMeta(string $name, string $content, bool $override = false) {
        $this->getDocument()->getHeadNode()->addMeta($name, $content, $override);
    }
    /**
     * Create HTML node.
     *
     * If a theme is applied to the web page, the created node will be based on
     * the implementation of the method Theme::createHTMLNode(). If no theme is
     * applied, the method will create an object based on two indices on the
     * given options array. The first one is 'name' and the second one is 'attributes'.
     * The first index represents node name such as 'div' and the second represents
     * a set of attributes for the node.
     *
     * @param array $nodeInfo An array that holds node options. If not provided,
     * the method will create a 'div' element.
     *
     * @return HTMLNode The created HTML node as an object.
     *
     * @since 1.0
     */
    public function createHTMLNode(array $nodeInfo = []) : HTMLNode {
        $pageTheme = $this->getTheme();

        if ($pageTheme === null) {
            $name = isset($nodeInfo['name']) ? trim((string)$nodeInfo['name']) : 'div';

            if (strlen($name) == 0) {
                $name = 'div';
            }
            $attrs = isset($nodeInfo['attributes']) && gettype($nodeInfo['attributes']) == 'array' ? $nodeInfo['attributes'] : [];

            return new HTMLNode($name, $attrs);
        }

        return $pageTheme->createHTMLNode($nodeInfo);
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
    public function get(string $label) {
        $langObj = $this->getTranslation();

        if ($langObj !== null) {
            return $langObj->get($label);
        }

        return $label;
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
     * Returns a child node given its ID.
     *
     * @param string $id The ID of the child.
     *
     * @return null|HTMLNode The method returns an object of type HTMLNode.
     * if found. If no node has the given ID, the method will return null.
     *
     * @since 1.0
     */
    public function getChildByID(string $id) {
        return $this->getDocument()->getChildByID($id);
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
     * Returns the document that is associated with the page.
     *
     * @return HTMLDoc An object of type 'HTMLDoc'.
     *
     * @since 1.0
     */
    public function getDocument() : HTMLDoc {
        return $this->document;
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
     * Returns the content attribute of a meta given its name.
     *
     * @param string $name The value of the attribute 'name' of the meta
     * tag.
     *
     * @return string If a meta tag which has the given name was found,
     * the method will return the value of the attribute 'content'. Other than
     * that, the method will return empty string.
     */
    public function getMetaVal(string $name) : string {
        $node = $this->getDocument()->getHeadNode()->getMeta($name);

        if ($node !== null) {
            return $node->getAttribute('content');
        }

        return '';
    }
    /**
     * Returns the value of a parameter which exist in the path part of page URI.
     *
     * When creating routes, some parts of the route might not be set
     * for dynamic routes. The parts are usually defined using the syntax '{var-name}'.
     *
     * @param string $paramName The name of the parameter. Note that it must
     * not include braces.
     *
     * @return string|null The method will return the value of the
     * parameter if it was set. Other than that, the method will return null.
     *
     */
    public function getParameterValue(string $paramName) {
        return Router::getParameterValue($paramName);
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
     * Returns the name of the directory at which CSS files of the applied theme exists.
     *
     * @return string The directory at which CSS files of the theme exists
     * (e.g. 'assets/my-theme/css' ). The folder will always exist inside the folder
     * 'public/assets'.
     * If no theme is applied, the method will return empty string.
     *
     * @since 1.0
     */
    public function getThemeCSSDir() : string {
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
    public function getThemeImagesDir() : string {
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
    public function getThemeJSDir() : string {
        if ($this->isThemeLoaded()) {
            $loadedTheme = $this->getTheme();

            return 'assets/'.$loadedTheme->getDirectoryName().'/'.$loadedTheme->getJsDirName();
        }

        return '';
    }

    /**
     * Returns the title of the page.
     *
     * @return string The title of the page.
     *
     * @since 1.0
     */
    public function getTitle() : string {
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
    public function getTitleSep() : string {
        return $this->titleSep;
    }
    /**
     * Returns an object which holds i18n labels.
     *
     * @return Lang The returned object labels will be based on the
     * language of the page.
     *
     */
    public function getTranslation() : Lang {
        if (!$this->skipLangCheck && $this->tr === null) {
            $this->usingLanguage();
        }

        return $this->tr;
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
    public function getWebsiteName() : string {
        return $this->websiteName;
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
    public function hasAside() : bool {
        return $this->incAside;
    }
    /**
     * Checks if the page will have a footer section or not.
     *
     * @return boolean true if the page has a footer section.
     *
     * @since 1.0
     */
    public function hasFooter() : bool {
        return $this->incFooter;
    }
    /**
     * Checks if the page will have a header section or not.
     *
     * @return boolean true if the page has a header section.
     *
     * @since 1.0
     */
    public function hasHeader() : bool {
        return $this->incHeader;
    }
    /**
     * Checks if system user has specific privilege or not.
     *
     * @param string $prId The ID of the privilege.
     *
     * @return bool If the user has a privilege which has the given ID, the method
     * will return true. Other than that, the method will return false.
     *
     */
    public function hasPrivilege(string $prId) : bool {
        $session = $this->getActiveSession();

        if ($session == null) {
            return false;
        }

        $user = $session->getUser();

        if ($user === null) {
            return false;
        }

        return $user->hasPrivilege($prId);
    }
    /**
     * Render HTML or PHP template file and return its content as an object.
     *
     * @param string $path The path to the template file.
     *
     * @param array $args An optional array that contain slots values or
     * PHP variables that will be passed to the template.
     *
     * @return HTMLNode The method will return rendered HTML as an instance of the
     * class HTMLNode.
     */
    public function include(string $path, array $args = []) : HTMLNode {
        return HTMLNode::fromFile($path, array_merge([
            'page' => $this
        ], $args));
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
    public function includeI18nLables(?bool $bool = null) : bool {
        if ($bool !== null) {
            $this->includeLables = $bool === true;
        }

        return $this->includeLables;
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
    public function insert($node, string $parentNodeId = self::MAIN_ELEMENTS[2]) {
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
     * Checks if a theme is loaded or not.
     *
     * @return boolean true if loaded. false if not loaded.
     *
     * @since 1.0
     */
    public function isThemeLoaded() : bool {
        return $this->theme instanceof Theme;
    }
    /**
     * Removes a callback given its ID.
     * 
     * @param string $id The unique identifier of the callback.
     * 
     * @return BeforeRenderCallback|null If removed, an object will be returned
     * that holds its information. Other than that, null is returned.
     */
    public function removeBeforeRender(string $id) : ?BeforeRenderCallback {
        $index = -1;
        $tempIndex = 0;
        
        foreach ($this->beforeRenderCallbacks as $callback) {
            
            if ($callback->getID() == $id) {
                $index = $tempIndex;
                break;
            }
            $tempIndex++;
        }
        
        return $this->beforeRenderCallbacks->remove($index);
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
    public function render(bool $formatted = false, bool $returnResult = false) {
        $this->beforeRender();

        if (!$returnResult) {
            $formatted = $formatted === true || (defined('WF_VERBOSE') && WF_VERBOSE);
            Response::write($this->getDocument()->toHTML($formatted));

            return null;
        }

        return $this->getDocument();
    }
    /**
     * Resets page attributes to default values.
     *
     * @since 1.0
     */
    public function reset() {
        $this->skipLangCheck = true;
        $this->document = new HTMLDoc();
        $this->checkLang();
        $this->usingLanguage();


        $this->setWebsiteName($this->getConfigVar('getAppName', 'WebFiori App', [$this->getLangCode()]));
        $this->setDescription($this->getConfigVar('getDescription', '', [$this->getLangCode()]));
        $this->setTitle($this->getConfigVar('getTitle', 'Hello World', [$this->getLangCode()]));
        $this->setTitleSep($this->getConfigVar('getTitleSeparator', '|'));

        try {
            $langObj = $this->getTranslation();
            $this->contentDir = $langObj->getWritingDir();
        } catch (TypeError $ex) {
            $this->contentDir = 'ltr';
        }



        $this->incFooter = true;
        $this->incHeader = true;
        $this->theme = null;
        $this->incAside = true;
        $this->setWritingDir();
        $this->setCanonical(Request::getRequestedURI());
        $this->document->setLanguage($this->getLangCode());
        $headNode = $this->getHead();
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

        $this->resetBeforeLoaded();
        $this->skipLangCheck = false;
    }
    /**
     * Sets the canonical URL of the page.
     *
     * Note that if empty string is given, it
     * won't be set. To unset the canonical, use 'null' as value.
     *
     * @param string $url The canonical URL of the page.
     *
     * @since 1.0
     *
     */
    public function setCanonical(string $url) {
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
     * If empty string is given,
     * the description meta tag will be removed from the &lt;head&gt; node.
     *
     * @since 1.0
     */
    public function setDescription(string $val) {
        $trimmed = trim($val);

        if (strlen($trimmed) == 0) {
            $descNode = $this->document->getHeadNode()->getMeta('description');
            $this->document->getHeadNode()->removeChild($descNode);
            $this->description = null;

            return;
        }
        $this->description = $trimmed;
        $this->document->getHeadNode()->addMeta('description', $trimmed, true);
    }
    /**
     * Sets the property that is used to check if page has an aside section or not.
     *
     * @param boolean $bool true to include aside section. false if
     * not.
     *
     * @since 1.0
     */
    public function setHasAside(bool $bool) {
        if (gettype($bool) == self::$BoolType) {
            if (!$this->incAside && $bool) {
                //add aside
                $mainContentArea = $this->document->getChildByID(self::MAIN_ELEMENTS[0]);

                if ($mainContentArea instanceof HTMLNode) {
                    $children = $mainContentArea->children();
                    $currentChCount = $children->size();
                    $mainContentArea->removeAllChildNodes();
                    $mainContentArea->addChild($this->_getComponent('getAsideNode', self::MAIN_ELEMENTS[3]));
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
    public function setHasFooter(bool $bool) {
        if (gettype($bool) == self::$BoolType) {
            if (!$this->incFooter && $bool) {
                $this->document->addChild($this->_getComponent('getFooterNode', self::MAIN_ELEMENTS[4]));
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
    public function setHasHeader(bool $bool) {
        if (gettype($bool) == self::$BoolType) {
            if (!$this->incHeader && $bool) {
                //add the header
                $children = $this->document->getBody()->children();
                $currentChCount = $children->size();
                $this->document->getBody()->removeAllChildNodes();
                $this->document->addChild($this->_getComponent('getHeaderNode', self::MAIN_ELEMENTS[1]));

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
    public function setLang(string $lang = 'EN') {
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
     * Loads a theme given its name.
     *
     * @param string $themeNameOrClass The name of the theme as specified by the
     * variable 'name' in theme definition. If the given name is 'null', the
     * method will load the default theme as specified by application configuration.
     * Note that once the theme is updated,
     * the document content of the page will reset if it was set before calling this
     * method. This also can be the value which can be taken from 'ClassName::class'.
     *
     * @throws Exception The method will throw
     * an exception if no theme was found which has the given name. Another case is
     * when the file 'theme.php' of the theme is missing.
     * Finally, an exception will be thrown if theme component is not found.
     *
     * @since 1.0
     */
    public function setTheme(?string $themeNameOrClass = null) {
        if ($themeNameOrClass !== null && strlen(trim($themeNameOrClass)) == 0) {
            return;
        }
        $xthemeName = $themeNameOrClass;

        if (strlen(trim($themeNameOrClass.'')) == 0) {
            $xthemeName = $this->getConfigVar('getTheme', $themeNameOrClass);
        }

        if (strlen($xthemeName) == 0) {
            $xthemeName = $themeNameOrClass;
        }
        $tmpTheme = ThemeManager::usingTheme($xthemeName);

        if ($tmpTheme !== null) {
            if ($this->theme !== null && $tmpTheme->getName() == $this->theme->getName()) {
                return;
            }
            $this->theme = $tmpTheme;
            $this->theme->setPage($this);
            $this->theme->invokeBeforeLoaded();
            $mainContentArea = $this->getDocument()->getChildByID(self::MAIN_ELEMENTS[2]);

            if ($mainContentArea === null) {
                $mainContentArea = new HTMLNode();
                $mainContentArea->setID(self::MAIN_ELEMENTS[2]);
            }

            $this->document = new HTMLDoc();
            $this->document->setHeadNode($this->getHead());

            $body = new HTMLNode();
            $body->setID(self::MAIN_ELEMENTS[0]);

            $this->document->setLanguage($this->getLangCode());

            //Header first
            $this->document->addChild($this->_getComponent('getHeaderNode', self::MAIN_ELEMENTS[1]));

            //Then body and inside it side and main
            $body->addChild($this->_getComponent('getAsideNode', self::MAIN_ELEMENTS[3]));
            $body->addChild($mainContentArea);
            $this->document->addChild($body);

            //Finally, footer
            $this->document->addChild($this->_getComponent('getFooterNode', self::MAIN_ELEMENTS[4]));

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
    public function setTitle(string $val) {
        if ($val !== null) {
            $this->title = $val;
            $this->document->getHeadNode()->setPageTitle($this->getTitle().$this->getTitleSep().$this->getWebsiteName());
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
    public function setTitleSep(string $str) {
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
    public function setWebsiteName(string $name) {
        if (strlen($name) != 0) {
            $this->websiteName = $name;
            $this->setTitle($this->getTitle());
        }
    }
    /**
     * Sets the writing direction of the page.
     *
     * @param string $dir Lang::DIR_LTR or Lang::DIR_RTL.
     *
     * @return boolean True if the direction was not set and its the first time to set.
     * if it was set before, the method will return false.
     * @throws Exception If the writing direction is not Lang::DIR_LTR or Lang::DIR_RTL.
     *
     * @since 1.0
     */
    public function setWritingDir(string $dir = 'ltr') {
        $dirL = strtolower($dir);

        if ($dirL == Lang::DIR_LTR || $dirL == Lang::DIR_RTL) {
            $this->contentDir = $dirL;
        }
    }
    private function _getComponent($methToCall, $nodeId) {
        $loadedTheme = $this->getTheme();
        $node = new HTMLNode();

        if ($loadedTheme !== null) {
            $node = $loadedTheme->$methToCall();
        }

        if ($loadedTheme === null || ($node instanceof HTMLNode)) {
            $node->setID($nodeId);

            return $node;
        }

        throw new UIException('The the method "'.get_class($loadedTheme).'::'.$methToCall.'()" did not return '
                    .'an instance of the class "WebFiori\\ui\\HTMLNode".');
    }
    /**
     * Sets the language of the page based on session language or
     * request.
     */
    private function checkLang() {
        try {
            $session = SessionsManager::getActiveSession();
        } catch (Exception $ex) {
            if (!$this->skipLangCheck) {
                throw new SessionException($ex->getMessage(), $ex->getCode(), $ex);
            }
            $session = null;
        } catch (Error $ex) {
            if (!$this->skipLangCheck) {
                throw new SessionException($ex->getMessage(), $ex->getCode(), $ex);
            }
            $session = null;
        }
        $langCodeFromSession = $session !== null ? $session->getLangCode(true) : null;

        if ($langCodeFromSession === null) {
            $langCodeFromRequest = Request::getParam('lang');

            if ($langCodeFromRequest === null) {
                $this->setLang($this->getConfigVar('getPrimaryLanguage', 'EN'));

                return;
            }
            $this->setLang($langCodeFromRequest);

            return;
        }
        $this->setLang($langCodeFromSession);
    }
    private function getConfigVar(string $meth, ?string $default = null, array $params = []) {
        try {
            return call_user_func_array([App::getConfig(), $meth], $params);
        } catch (InitializationException $ex) {
            return $default;
        }
    }


    private function getHead() {
        $loadedTheme = $this->getTheme();
        $defaultBase = Util::getBaseURL();
        $base = $this->getConfigVar('getBaseURL', $defaultBase);

        $headNode = new HeadNode(
            $this->getTitle().$this->getTitleSep().$this->getWebsiteName(),
            $this->getCanonical(),
            $base
        );

        if ($loadedTheme !== null) {
            $headNode = $loadedTheme->getHeadNode();

            if (!($headNode instanceof HeadNode)) {
                throw new UIException('The method "'.get_class($loadedTheme).'::getHeadNode()" did not return '
                        .'an instance of the class "WebFiori\\ui\\HeadNode".');
            }
        }
        $headNode->addMeta('charset','UTF-8',true);
        $headNode->setPageTitle($this->getTitle().$this->getTitleSep().$this->getWebsiteName());
        $headNode->setBase($base);
        $headNode->setCanonical($this->getCanonical());

        if ($this->getDescription() != null) {
            $headNode->addMeta('description', $this->getDescription(), true);
        }

        return $headNode;
    }
    /**
     * Prepares for rendering by sorting and invoking before-render callbacks.
     */
    public function beforeRender() {
        $this->beforeRenderCallbacks->insertionSort(false);
        $this->invokeBeforeRender();
    }
    /**
     * Recursively executes before-render callbacks with dynamic callback handling.
     * 
     * @param int $current The current callback index being processed.
     */
    private function invokeBeforeRender(int $current = 0) {
        $currentCount = count($this->beforeRenderCallbacks);

        if ($currentCount == 0 || $currentCount == $current) {
            return;
        }
        $this->beforeRenderCallbacks->get($current)->call($this);
        $newCount = count($this->beforeRenderCallbacks);

        if ($newCount != $currentCount) {
            //This part is used to handel callbacks
            //which are added during the process of executing
            //callbacks
            $this->beforeRenderCallbacks->insertionSort(false);
            $this->invokeBeforeRender();
        } else {
            $this->invokeBeforeRender($current + 1);
        }
    }
    private function resetBeforeLoaded() {
        $this->beforeRenderCallbacks = new LinkedList();
        $this->addBeforeRender(function (WebPage $page)
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

                $jsDir = ROOT_PATH.DS.'public'.DS.$themeAssetsDir.DS.$pageTheme->getJsDirName();

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

                $cssDir = ROOT_PATH.DS.'public'.DS.$themeAssetsDir.DS.$pageTheme->getCssDirName();

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
        }, PHP_INT_MAX);
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
     * have the name 'LangXX.php' where 'XX' is language code. Also the function
     * will throw an exception when the translation file is loaded but no object
     * of type 'Lang' was stored in the set of loaded translations.
     */
    private function usingLanguage() {
        if ($this->getLangCode() !== null) {
            try {
                $this->tr = Lang::loadTranslation($this->getLangCode());
            } catch (MissingLangException $ex) {
                if (!$this->skipLangCheck) {
                    throw new MissingLangException($ex->getMessage());
                }

                return;
            }
            $pageLang = $this->getTranslation();

            if ($pageLang !== null) {
                $this->setWritingDir($pageLang->getWritingDir());
            }
        }
    }
}
