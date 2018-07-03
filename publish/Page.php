<?php
/**
 * A class used to initialize main page components.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.7
 */
class Page{
    /**
     *
     * @var type 
     * @since 1.6
     */
    private $isDynamic;
    /**
     * The document that represents the page.
     * @var HTMLDoc 
     * @since 1.4
     */
    private $document;
    /**
     * The writing direction of the page.
     * @var string
     * @since 1.0 
     */
    private $contentDir;
    /**
     * A variable that is set to <b>TRUE</b> if page has footer.
     * @var boolean A variable that is set to <b>TRUE</b> if page has footer.
     * @since 1.2 
     */
    private $incFooter;
    /**
     * A variable that is set to <b>TRUE</b> if page has aside area.
     * @var boolean A variable that is set to <b>TRUE</b> if page has footer.
     * @since 1.2 
     */
    private $incAside;
    /**
     * A variable that is set to <b>TRUE</b> if page has header.
     * @var boolean A variable that is set to <b>TRUE</b> if page has header.
     * @since 1.2 
     */
    private $incHeader;
    /**
     * The language of the page.
     * @var string
     * @since 1.0 
     */
    private $contentLang;
    /**
     * The title of the page.
     * @var string
     * @since 1.0 
     */
    private $title;
    /**
     * The description of the page.
     * @var string
     * @since 1.0 
     */
    private $description;
    /**
     * The canonical page URL.
     * @var type 
     * @since 1.2
     */
    private $canonical;
    /**
     * An object of type <b>Theme</b> that contains loaded theme info.
     * @var Theme 
     * @since 1.6
     */
    private $theme;
    /**
     * The name of request page.
     * @var string
     * @since 1.6 
     */
    private $name;
    /**
     * Sets the canonical URL of the page.
     * @since 1.2
     * @param string $url The canonical URL of the page.
     */
    public function setCanonical($url){
        if(strlen($url) != 0){
            $this->canonical = $url;
        }
    }
    /**
     * Returns the canonical URL of the page.
     * @return NULL|string The function will return the  canonical URL of the page 
     * if set. If not, the function will return <b>NULL</b>.
     * @since 1.2
     */
    public function getCanonical() {
        return $this->canonical;
    }
    private function __construct() {
        $this->title = NULL;
        $this->contentDir = NULL;
        $this->description = NULL;
        $this->contentLang = NULL;
        $this->incFooter = TRUE;
        $this->incHeader = TRUE;
        $this->isDynamic = TRUE;
        $this->incAside = TRUE;
        $this->setWritingDir();
        WebsiteFunctions::get()->getMainSession()->initSession(FALSE, TRUE);
        
        $arr = explode('/', filter_var($_SERVER['REQUEST_URI']));
        $file = $arr[count($arr) - 1];
        $fix = explode('.', $file)[0];
        $this->name = explode('?', $fix)[0];
    }
    /**
     * Returns the name of requested page.
     * @return string The name of the requested page.
     * @since 1.6
     */
    public function getPageName() {
        return $this->name;
    }
    /**
     * Checks if the type of page will be dynamic or static.
     * @return boolean The function will return <b>TRUE</b> if document 
     * type is dynamic. Otherwise, the function will return <b>FALSE</b>.
     * @since 1.6
     */
    public function isDynamicDoc() {
        return $this->isDynamic;
    }
    /**
     * Adds a child node inside the body of a node given its ID.
     * @param HTMLNode $node The node that will be inserted.
     * @param string $parentNodeId The ID of the node that the given node 
     * will be inserted to.
     * @return boolean The function will return <b>TRUE</b> if the given node 
     * was inserted. If it is not, the function will return <b>FALSE</b>.
     * @since 1.6
     */
    public function insertNode($node,$parentNodeId='') {
        if(strlen($parentNodeId) != 0){
            if($node instanceof HTMLNode){
                if($this->document == NULL){
                    $this->getDocument();
                }
                $parentNode = $this->document->getChildByID($parentNodeId);
                if($parentNode instanceof HTMLNode){
                    $parentNode->addChild($node);
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    /**
     * A single instance of the class.
     * @var Page 
     * @since 1.0
     */
    private static $instance;
    /**
     * Returns a single instance of <b>Page</b>
     * @return Page an instance of <b>Page</b>.
     * @since 1.0
     */
    public static function get(){
        if(self::$instance != NULL){
            return self::$instance;
        }
        self::$instance = new Page();
        return self::$instance;
    }
    /**
     * Sets the title of the page.
     * @param string $val The title of the page. If <b>NULL</b> is given, 
     * the title will not updated.
     * @since 1.0
     */
    public function setTitle($val){
        if($val != NULL){
            $this->title = $val;
            if($this->document != NULL){
                $this->document->getHeadNode()->setTitle($this->getTitle().SiteConfig::get()->getTitleSep().SiteConfig::get()->getWebsiteName());
            }
        }
    }
    /**
     * Saves the page to a file.
     * @param string $path The location where the file will be written to 
     * (such as 'pages/system/view-users'. 'view-users' is the page name).
     *  Note that the last part of the 
     * path will be the name of the file. It must be without extention.
     * @param boolean $isDynamic Set to <b>TRUE</b> to save the file as 
     * dynamic PHP web page. If the user wants a plain HTML, set this attribute 
     * to <b>FALSE</b>.
     * @return boolean The function will return <b>TRUE</b> if the file is saved. 
     * If not, the function will return <b>FALSE</b>
     * @since 1.7
     */
    public function saveToFile($path,$isDynamic=false){
        if($isDynamic === TRUE){
            $path = str_replace('\\', '/', $path);
            $fArr = explode('/', $path);
            $name = $fArr[count($fArr) - 1];
            $f = fopen($path.'.php', 'w+');
            if($f != FALSE){
                $root = str_replace('\\', '/', ROOT_DIR);
                fwrite($f, "<?php\n");
                fwrite($f, 'require \''.$root.'/root.php\';'."\n");
                fwrite($f, '$page = Page::get();'."\n");
                fwrite($f, '$page->usingTheme(SiteConfig::get()->getBaseThemeName());'."\n");
                fwrite($f, '$page->setLang(WebsiteFunctions::get()->getMainSession()->getLang());'."\n");
                if($this->getLanguage() != NULL){
                    fwrite($f, '$page->usingLanguage();'."\n");
                    if($this->getLanguage()->get('pages/'.$name) === NULL){
                        fwrite($f, '// Notice: \'pages/'.$name.'\' is not set on loaded language.'."\n");
                        fwrite($f, '// Page title and description will not set because of that.'."\n");
                    }
                }
                else{
                    fwrite($f, '// Notice: Static page title and description will be used.'."\n");
                    fwrite($f, '$page->setTitle(\''.$this->getTitle().'\');'."\n");
                    fwrite($f, '$page->setDescription(\''.$this->getDescription().'\');'."\n");
                }
                fwrite($f, 'echo $page->getDocument();'."\n");
                fclose($f);
                return TRUE;
            }
            return FALSE;
        }
        else{
            return $this->getDocument()->saveToFile($path, TRUE, 'html');
        }
    }
    /**
     * Returns the document that is associated with the page.
     * @return HTMLDoc An object of type <b>HTMLDoc</b>.
     * @throws Exception If page theme is not loaded.
     * @since 1.1
     */
    public function getDocument(){
        if($this->isThemeLoaded()){
            if($this->document == NULL){
                $headNode = $this->_getHead();
                $footerNode = $this->_getFooter();
                $asideNode = $this->_getAside();
                $headerNode = $this->_getHeader();
                $this->document = new HTMLDoc();
                $this->document->setLanguage($this->getLang());
                $this->document->setHeadNode($headNode);
                $this->document->addChild($headerNode);
                $body = new HTMLNode();
                $body->setID('page-body');
                $body->addChild($asideNode);
                $contentArea = new HTMLNode();
                $contentArea->setID('main-content-area');
                $body->addChild($contentArea);
                $this->document->addChild($body);
                $this->document->addChild($footerNode);
            }
            return $this->document;
        }
        throw new Exception('Theme is not loaded. A theme must be loaded before using the document.');
    }
    /**
     * Returns the title of the page.
     * @return string|NULL The title of the page. If the title is not set, 
     * the function will return <b>NULL</b>
     * @since 1.0
     */
    public function getTitle(){
        return $this->title;
    }
    /**
     * Checks if the selected theme is loaded or not.
     * @return boolean <b>TRUE</b> if loaded. <b>FALSE</b> if not loaded.
     * @since 1.1
     */
    public function isThemeLoaded(){
        return $this->theme instanceof Theme;
    }
    /**
     * Sets the description of the page.
     * @param string $val The description of the page. If <b>NULL</b> is given, 
     * description will not change.
     * @since 1.0
     */
    public function setDescription($val){
        if($val != NULL){
            $this->description = $val;
            if($this->document != NULL ){
                $headCh = $this->document->getHeadNode()->children();
                $headChCount = $headCh->size();
                for($x = 0 ; $x < $headChCount ; $x++){
                    $node = $headCh->get($x);
                    if($node->getAttributeValue('name') == 'description'){
                        $node->setAttribute('content',$val);
                        return;
                    }
                }
            }
        }
    }
    /**
     * Returns the description of the page.
     * @return string|NULL The title of the page. If the description is not set, 
     * the function will return <b>NULL</b>
     * @since 1.0
     */
    public function getDescription(){
        return $this->description;
    }
    /**
     * Returns the language.
     * @return string|NULL Two digit language code. In case language is not set, the 
     * function will return <b>NULL</b>
     * @since 1.0
     */
    public function getLang(){
        return $this->contentLang;
    }
   /**
    * Load the translation file based on the language code. The function uses 
    * two checks to load the translation. If the page language is set using 
    * the function <b>Page::setLang()</b>, then the language that will be loaded 
    * will be based on the value returned by the function <b>Page::getLang()</b>.
    * @throws Exception in case the language is not set, or <b>ROOT_DIR</b> is not defined.
    * @since 1.0
    */
    public function usingLanguage(){
        if($this->getLang() != NULL){
            Language::loadTranslation($this->getLang());
            $pageLang = $this->getLanguage();
            $this->setWritingDir($pageLang->getWritingDir());
            $this->setTitle($pageLang->get('pages/'.$this->getPageName().'/title'));
            $this->setDescription($pageLang->get('pages/'.$this->getPageName().'/description'));
        }
        else{
            throw new Exception('Unable to load transulation. Page language is not set.');
        }
    }
    /**
     * Sets the display language of the page.
     * @param string $lang a two digit language code such as AR or EN.
     * @return boolean True if the language was not set and its the first time to set. 
     * if it was set before, the method will return false.
     * @throws Exception If the language is not supported.
     * @since 1.0
    */
    public function setLang($lang='EN'){
        $langU = strtoupper($lang);
        if(in_array($langU, SessionManager::SUPPORTED_LANGS)){
            $this->contentLang = $langU;
            if($this->document != NULL){
                $this->document->setLanguage($langU);
            }
        }
        else{
            throw new Exception('Unknown language code: '.$lang);
        }
    }
    /**
     * Returns the language variables based on loaded translation.
     * @return Language|NULL an object of type <b>Language</b> if language 
     * is loaded. If no language found, <b>NULL</b> is returned. This 
     * function should be called after calling the function <b>Page::loadTranslation()</b> in 
     * order for the function to return non-null value.
     * @since 1.6
     */
    public function getLanguage() {
        $loadedLangs = Language::getLoadedLangs();
        if(isset($loadedLangs[$this->getLang()])){
            return $loadedLangs[$this->getLang()];
        }
        return NULL;
    }
    /**
     * Loads a theme given its name.
     * @param string $themeName [Optional] The name of the theme as specified by the 
     * variable 'name' in theme definition. If the given name is <b>NULL</b>, the 
     * function will load the default theme as specified by the function 
     * <b>SiteConfig::getBaseThemeName()</b>.
     * @throws Exception The function will throw 
     * an exception if no theme was found which has the given name. Another case is 
     * when the file 'theme.php' of the theme is missing. 
     * Finally, an exception will be thrown if theme component is not found.
     * @since 1.4
     * @see Theme::usingTheme()
     */
    public function usingTheme($themeName=null) {
        $themeNamel = $themeName === NULL ? $themeName : SiteConfig::get()->getBaseThemeName();
        $tmpTheme = Theme::usingTheme($themeNamel);
        $this->theme = $tmpTheme;
        $this->theme->invokeAfterLoaded();
    }
    /**
     * Returns the directory at which CSS files of the theme exists.
     * @return string The directory at which CSS files of the theme exists 
     * (e.g. 'publish/my-theme/css' ). 
     * If the theme is not loaded, the function will return empty string.
     * @since 1.6
     */
    public function getThemeCSSDir() {
        if($this->isThemeLoaded()){
            $theme = $this->getTheme();
            return Theme::THEMES_DIR.'/'.$theme->getDirectoryName().'/'.$theme->getCssDirName();
        }
        return '';
    }
    /**
     * Returns the directory at which image files of the theme exists.
     * @return string The directory at which image files of the theme exists 
     * (e.g. 'publish/my-theme/images' ). 
     * If the theme is not loaded, the function will return empty string.
     * @since 1.6
     */
    public function getThemeImagesDir() {
        if($this->isThemeLoaded()){
            $theme = $this->getTheme();
            return Theme::THEMES_DIR.'/'.$theme->getDirectoryName().'/'.$theme->getImagesDirName();
        }
        return '';
    }
    /**
     * Returns the directory at which JavaScript files of the theme exists.
     * @return string The directory at which JavaScript files of the theme exists 
     * (e.g. 'publish/my-theme/js' ). 
     * If the theme is not loaded, the function will return empty string.
     * @since 1.6
     */
    public function getThemeJSDir() {
        if($this->isThemeLoaded()){
            $theme = $this->getTheme();
            return Theme::THEMES_DIR.'/'.$theme->getDirectoryName().'/'.$theme->getJsDirName();
        }
        return '';
    }
    /**
     * Returns an object of type <b>Theme</b> that contains loaded theme information.
     * @return Theme|NULL An object of type <b>Theme</b> that contains theme information. If the theme 
     * is not loaded, the function will return <b>NULL</b>.
     * @since 1.6
     */
    public function getTheme() {
        return $this->theme;
    }
    /**
     * Returns the writing direction of the page.
     * @return string | NULL 'ltr' or 'rtl'. If the writing direction is not set, 
     * the function will return <b>NULL</b>
     * @since 1.0
     */
    public function getWritingDir(){
        return $this->contentDir;
    }
    /**
     * Sets the writing direction of the page.
     * @param string $dir <b>Page::DIR_LTR</b> or <b>Page::DIR_RTL</b>.
     * @return boolean True if the direction was not set and its the first time to set. 
     * if it was set before, the method will return false.
     * @throws Exception If the writing direction is not <b>Page::DIR_LTR</b> or <b>Page::DIR_RTL</b>.
     * @since 1.0
     */
    function setWritingDir($dir='ltr'){
        $dirL = strtolower($dir);
        if($dirL == Language::DIR_LTR || $dirL == Language::DIR_RTL){
            $this->contentDir = $dirL;
            return TRUE;
        }
        else{
            throw new Exception('Unknown writing direction: '.$dir);
        }
    }
    /**
     * Sets the property that is used to check if page has a header section or not.
     * @param boolean $bool <b>TRUE</b> to include the header section. <b>FALSE</b> if 
     * not. <b>HAS BUG</b>
     * @since 1.2
     */
    public function setHasHeader($bool){
        if(gettype($bool) == 'boolean'){
            if($this->document != NULL){
                if($this->incHeader == FALSE && $bool == TRUE){
                    $children = $this->document->getBody()->children();
                    $this->document->getBody()->removeAllChildNodes();
                    $this->document->addChild($this->_getHeader());
                    for($x = 0 ; $x < $children->size() ; $x++){
                        $this->document->addChild($children->get($x));
                    }
                }
                else if($this->incHeader == TRUE && $bool == FALSE){
                    $header = $this->document->getChildByID('page-header');
                    $this->document->removeChild($header);
                }
            }
            $this->incHeader = $bool;
        }
    }
    
    public function setHasAside($bool){
        if(gettype($bool) == 'boolean'){
            if($this->document != NULL){
                if($this->incAside == FALSE && $bool == TRUE){
                    
                }
                else if($this->incFooter == TRUE && $bool == FALSE){
                    
                }
            }
            $this->incAside = $bool;
        }
    }

    /**
     * Sets the property that is used to check if page has a footer section or not.
     * @param boolean $bool <b>TRUE</b> to include the footer section. <b>FALSE</b> if 
     * not. <b>HAS BUG</b>
     * @since 1.2
     */
    public function setHasFooter($bool){
        if(gettype($bool) == 'boolean'){
            if($this->document != NULL){
                if($this->incFooter == FALSE && $bool == TRUE){
                    $this->document->addChild($this->_getFooter());
                }
                else if($this->incFooter == TRUE && $bool == FALSE){
                    $footer = $this->document->getChildByID('page-footer');
                    $this->document->removeChild($footer);
                }
            }
            $this->incFooter = $bool;
        }
    }
    /**
     * Checks if the page will have a footer section or not.
     * @return boolean <b>TRUE</b> if the page has a footer section.
     * @since 1.2
     */
    public function hasFooter(){
        return $this->incFooter;
    }
    /**
     * Checks if the page will have a header section or not.
     * @return boolean <b>TRUE</b> if the page has a header section.
     * @since 1.2
     */
    public function hasHeader(){
        return $this->incHeader;
    }
    /**
     * Checks if the page will have an aside section or not.
     * @return boolean <b>TRUE</b> if the page has an aside section.
     * @since 1.6
     */
    public function hasAside() {
        return $this->incAside;
    }
    
    private function _getAside() {
        if($this->hasAside()){
            if($this->document == NULL){
                $h = getAsideNode();
                $h->setID('aside-container');
                return $h;
            }
            return $this->document->getChildByID('aside-container');
        }
    }
    
    private function _getHeader(){
        if($this->hasHeader()){
            if($this->document == NULL){
                $h = getHeaderNode();
                $h->setID('page-header');
                return $h;
            }
            return $this->document->getChildByID('page-header');
        }
    }
    
    private function _getFooter(){
        if($this->hasFooter()){
            if($this->document == NULL){
                $f = getFooterNode();
                $f->setID('page-footer');
                return $f;
            }
            return $this->document->getChildByID('page-footer');
        }
    }
    
    private function _getHead(){
        if($this->document === NULL){
            $headNode = new HeadNode(
                $this->getTitle().SiteConfig::get()->getTitleSep().SiteConfig::get()->getWebsiteName(),
                $this->getCanonical(),
                SiteConfig::get()->getBaseURL()
            );
            $metaCharset = new HTMLNode('meta', FALSE);
            $metaCharset->setAttribute('charset', 'UTF-8');
            $headNode->addChild($metaCharset);
            $descNode = new HTMLNode('meta', FALSE);
            $descNode->setAttribute('name', 'description');
            $descNode->setAttribute('content', $this->getDescription());
            $headNode->addChild($descNode);
            $tmpHead = getHeadNode();
            $children = $tmpHead->children();
            $count = $children->size();
            for($x = 0 ; $x < $count ; $x++){
                $node = $children->get($x);
                $nodeName = $node->getName();
                if($nodeName != 'base' && $nodeName != 'title'){
                    if($node->getAttributeValue('name') != 'description'){
                        $headNode->addChild($node);
                    }
                }
            }
            return $headNode;
        }
        return $this->document->getHeadNode();
    }
}

