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
use webfiori\conf\SiteConfig;
use webfiori\framework\exceptions\UIException;
use webfiori\framework\i18n\Language;
use webfiori\framework\session\SessionsManager;
use webfiori\http\Request;
use webfiori\http\Response;
use webfiori\json\Json;
use webfiori\ui\HeadNode;
use webfiori\ui\HTMLDoc;
use webfiori\ui\HTMLNode;
use webfiori\framework\WebFioriApp;
use webfiori\framework\ThemeLoader;
use webfiori\framework\Theme;
use webfiori\framework\Util;
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
     *
     * @var Language|null 
     */
    private $tr;
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
     * The name of the web site that will be appended with the title of 
     * the page.
     * 
     * @var string 
     * 
     * @since 1.0
     */
    private $websiteName;
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
    public function __construct() {
        $this->reset();
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
        if ($val != null) {
            $this->title = $val;
            $this->document->getHeadNode()->setTitle($this->getTitle().$this->getTitleSep().$this->getWebsiteName());
        }
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
     * @since 1.8
     */
    public function getTitleSep() {
        return $this->titleSep;
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
    /**
     * Returns the description of the page.
     * 
     * @return string|null The description of the page. If the description is not set, 
     * the method will return null.
     * 
     * @since 1.0
     */
    private function getDescription() {
        return $this->description;
    }
    /**
     * Returns the canonical URL of the page.
     * 
     * @return null|string The method will return the  canonical URL of the page 
     * if set. If not, the method will return null.
     * 
     * @since 1.2
     */
    private function getCanonical() {
        return $this->canonical;
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

            if (!$headNode instanceof HeadNode) {
                throw new UIException('The method "'.get_class($loadedTheme).'::getHeadNode()" did not return '
                        .'an instance of the class "webfiori\\ui\\HeadNode".');
            }
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
    private function _loadByThemeName($themeNameOrClass) {
        if ($themeNameOrClass === null && $this->theme === null) {
            $themeNameOrClass = SiteConfig::getBaseThemeName();
        } else {
            $themeNameOrClass = trim($themeNameOrClass);

            if (strlen($themeNameOrClass) == 0) {
                return;
            }
        }

        if ($this->theme !== null) {
            if ($themeNameOrClass != $this->theme->getName()) {
                $tmpTheme = ThemeLoader::usingTheme($this, $themeNameOrClass);
            } else {
                return;
            }
        } else {
            $tmpTheme = ThemeLoader::usingTheme($this, $themeNameOrClass);
        }

        return $tmpTheme;
    }
    /**
     * Returns the name of the web site.
     * 
     * @return string The name of the web site. If the name was not set 
     * using the method WebPage::siteName(), the returned value will 
     * be 'My X Website'.
     * 
     * @since 1.8
     */
    private function getWebsiteName() {
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
    public function reset() {
        $this->document = new HTMLDoc();
        $this->_checkLang();
        $this->usingLanguage();
        
        $websiteName = WebFioriApp::getAppConfig()->getWebsiteName($this->getLangCode());
        $websiteName !== null ? $this->setWebsiteName($websiteName) : $this->setWebsiteName('New Website');
        
        $websiteDesc = WebFioriApp::getAppConfig()->getDescription($this->getLangCode());
        $websiteDesc !== null ? $this->setWebsiteName($websiteDesc) : '';
        
        $pageTitle = WebFioriApp::getAppConfig()->getWebsiteName($this->getLangCode());
        $pageTitle !== null ? $this->setTitle($websiteName) : $this->setTitle('Hello World');


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
        $this->includeLables = true;
        
        $this->_resetBeforeLoaded();
    }
    /**
     * 
     * @return Language
     */
    public function getTranslation() {
        return $this->tr;
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
    /**
     * Sets the display language of the page.
     * 
     * The length of the given string must be 2 characters in order to set the 
     * language code.
     * 
     * @param string $lang a two digit language code such as AR or EN.
     * 
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
     * Load the translation file based on the language code. 
     * 
     * The method uses 
     * two checks to load the translation. If the page language is set using 
     * the method WebPage::getLanguageCode(), then the language that will be loaded 
     * will be based on the value returned by the method Page::getLanguageCode(). If 
     * the language of the page is not set, The method will throw an exception.
     * 
     * @since 1.0
     */
    private function usingLanguage() {
        if ($this->getLangCode() !== null) {
            $this->tr = Language::loadTranslation($this->getLangCode());
            $pageLang = $this->getTranslation();
            $this->setWritingDir($pageLang->getWritingDir());
        }
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
     * @since 1.9.6
     */
    public function includeI18nLables($bool = null) {
        if ($bool !== null) {
            $this->includeLables = $bool === true;
        }

        return $this->includeLables;
    }
    /**
     * Returns the document that is associated with the page.
     * 
     * @return HTMLDoc An object of type 'HTMLDoc'.
     * 
     * @since 1.1
     */
    public function getDocument() {
        return $this->document;
    }
    /**
     * Loads a theme given its name.
     * 
     * @param string $themeNameOrClass The name of the theme as specified by the 
     * variable 'name' in theme definition. If the given name is 'null', the 
     * method will load the default theme as specified by the method 
     * 'SiteConfig::getBaseThemeName()'. Note that once the theme is updated, 
     * the document content of the page will reset if it was set before calling this 
     * method. This also can be the value which can be taken from 'ClassName::class'. 
     * 
     * 
     * @throws Exception The method will throw 
     * an exception if no theme was found which has the given name. Another case is 
     * when the file 'theme.php' of the theme is missing. 
     * Finally, an exception will be thrown if theme component is not found.
     * @since 1.4
     * @see Theme::usingTheme()
     */
    public function setTheme($themeNameOrClass = null) {
        $xthemeName = '\\'.$themeNameOrClass;

        if (class_exists($xthemeName)) {
            die('gggggggg');
            $tmpTheme = new $xthemeName();
            
            if (!($tmpTheme instanceof Theme)) {
                $tmpTheme = $this->_loadByThemeName($themeNameOrClass);
            }
            
            $tmpTheme->setPage($this);
            $tmpTheme->invokeBeforeLoaded();
        } else {
            die('jhghjg');
            $tmpTheme = $this->_loadByThemeName($themeNameOrClass);
        }

        if ($tmpTheme !== null) {
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
     * 
     * @return Theme
     */
    public function getTheme() {
        return $this->theme;
    }
    /**
     * Checks if a theme is loaded or not.
     * 
     * @return boolean true if loaded. false if not loaded.
     * 
     * @since 1.0
     */
    private function isThemeLoaded() {
        return $this->theme instanceof Theme;
    }
    /**
     * Returns the directory at which CSS files of the theme exists.
     * 
     * @return string The directory at which CSS files of the theme exists 
     * (e.g. 'assets/my-theme/css' ). 
     * If the theme is not loaded, the method will return empty string.
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
     * Returns the directory at which JavaScript files of the theme exists.
     * 
     * @return string The directory at which JavaScript files of the theme exists 
     * (e.g. 'assets/my-theme/js' ). 
     * If the theme is not loaded, the method will return empty string.
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
    public function get($label) {
        $langObj = $this->getTranslation();
        if ($langObj !== null) {
            return $langObj->get($label);
        }
        return $label;
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
                $sysV = WebFioriApp::getAppConfig()->getVersion();
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
                                    'revision' => $sysV
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
                                    'revision' => $sysV
                                ]);
                            }
                        }
                    }
                }
            }
        }];
    }
    /**
     * Adds a function which will be executed before the page is fully rendered.
     * 
     * The function will be executed when the method 'WebPage::render()' is called. 
     * One possible use is to do some modifications to the DOM before the 
     * page is displayed. It is possible to have multiple callbacks.
     * 
     * @param callback $callable A PHP function that will be get executed. before 
     * the page is rendered.
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
    public static function addBeforeRender($callable = '', $params = []) {
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
     * Sets the description of the page.
     * 
     * @param string $val The description of the page. 
     * If <b>null</b> is given, 
     * the description meta tag will be removed from the &lt;head&gt; node. If 
     * empty string is given, nothing will change.
     * 
     * @since 1.0
     */
    public function setDescription($val) {
        if ($val === null) {
            $descNode = $this->document->getHeadNode()->getMeta('description');
            $this->document->getHeadNode()->removeChild($descNode);
            $this->description = null;

            if (strlen($desc) !== 0) {
                $this->description = $desc;
                $this->document->getHeadNode()->addMeta('description', $desc, true);
            } else {
                $descNode = $this->document->getHeadNode()->getMeta('description');
                $this->document->getHeadNode()->removeChild($descNode);
                $this->description = null;
            }
        } else {
            
        }
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
}