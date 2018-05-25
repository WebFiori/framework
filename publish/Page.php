<?php
/**
 * The directory where themes will be stored.
 */
define('THEMES_DIR','publish/themes');
/**
 * A class used to initialize main page components.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.4
 */
class Page{
    /**
     * The document that represents the page.
     * @var HTMLDoc 
     * @since 1.4
     */
    private $document;
    /**
     * A constant for left to right writing direction.
     * @var string 
     * @since 1.0
     */
    const DIR_LTR = 'ltr';
    /**
     * A constant for right to left writing direction.
     * @var string 
     * @since 1.0
     */
    const DIR_RTL = 'rtl';
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
     * A boolean value that is set to true once the theme is loaded.
     * @var boolean 
     * @since 1.1
     */
    private $isThemeLoaded;
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
        $this->isThemeLoaded = FALSE;
        $this->incFooter = FALSE;
        $this->incHeader = FALSE;
        WebsiteFunctions::get()->getMainSession()->initSession(FALSE, TRUE);
    }
    
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
     * @param string $val The title of the page.
     * @since 1.0
     */
    public function setTitle($val){
        $this->title = $val;
    }
    /**
     * Returns the document that is associated with the page.
     * @param boolean $dynamic 
     * @return HTMLDoc An object of type <b>HTMLDoc</b>.
     * @throws Exception If page theme is not loaded.
     * @since 1.1
     */
    public function getDocument($dynamic=false){
        if($this->isThemeLoaded()){
            if($this->document == NULL){
                $this->document = new HTMLDoc();
                $this->document->setLanguage($this->getLang());
                $this->document->setHeadNode($this->getHead($dynamic));
                $this->document->addNode($this->getHeader($dynamic));
                $this->document->addNode($this->getFooter($dynamic));
            }
            return $this->document;
        }
        throw new Exception('Theme is not loaded. Call the function Page::loadTheme() first.');
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
        return $this->isThemeLoaded;
    }
    /**
     * Sets the description of the page.
     * @param string $val The description of the page.
     * @since 1.0
     */
    public function setDescription($val){
        $this->description = $val;
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
            $langSet = FALSE;
            if($this->contentLang == NULL){
                $this->contentLang = $langU;
                $this->loadTranslation();
                //need to find solution for other languages in setting writing direction
                if($langU == 'EN'){
                    $this->setWritingDir(self::DIR_LTR);
                }
                else if($langU == 'AR'){
                    $this->setWritingDir(self::DIR_RTL);
                }
                $langSet = TRUE;
            }
            return $langSet;
        }
        else{
            throw new Exception('Unknown language code: '.$lang);
        }
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
    * Load the translation file based on the language 
    * @throws Exception in case the language is not supported, or the session 
    * is not running, or <b>ROOT_DIR</b> is not defined.
    * @since 1.0
    * @param boolean $uses_session_lang If <b>TRUE<b> is given and language is not 
    * set, the language of the session will be used. 
    */
    public function loadTranslation($uses_session_lang=true){
        if(defined('ROOT_DIR')){
            if($this->getLang() != NULL){
                include_once ROOT_DIR.'/entity/langs/Language_'.$this->getLang().'.php';
            }
            else if($uses_session_lang === TRUE){
                $sLang = WebsiteFunctions::get()->getMainSession()->getLang(TRUE);
                if($sLang != NULL){
                    if($this->setLang($sLang)){
                        include_once ROOT_DIR.'/entity/langs/Language_'.$sLang.'.php';
                    }
                    else{
                        throw new Exception('Unable to set language to \''.$sLang.'\'.');
                    }
                }
                else{
                    throw new Exception('Unable to load transulation. Session Language is not set.');
                }
            }
            else{
                throw new Exception('Unable to load transulation. Language is not set.');
            }
        }
        else{
            throw new Exception('Unable to load transulation. Root directory is not set.');
        }
    }
    /**
     * Returns an array that contains the meta data of all available themes. 
     * @return array An array that contains the meta data of all available themes.
     * @throws Exception If the constant 'ROOT_DIR' is not defined.
     * @since 1.1 
     */
    public function getAvailableThemes(){
        if(defined('ROOT_DIR')){
            $themeNames = array();
            $themesDirs = array_diff(scandir(ROOT_DIR.'/'.THEMES_DIR), array('..', '.'));
            foreach ($themesDirs as $dir){
                include ROOT_DIR.THEMES_DIR.'/'.$dir.'/theme.php';
                array_push($themeNames, $GLOBALS['THEME_META']);
            }
            return $themeNames;
        }
        throw new Exception('Unable to load theme because root directory is not defined.');
    }
    /**
     * Loads the selected admin panel theme.
     * @return boolean <b>TRUE</b> if selected theme is loaded.
     * @since 1.0
     * @throws Exception If the constant 'ROOT_DIR' is not defined.
     */
    public function loadAdminTheme(){
        if(defined('ROOT_DIR')){
            $themeDir = ROOT_DIR.'/'.SiteConfig::get()->getAdminThemeDir();
            require_once $themeDir.'/theme.php';
            foreach ($GLOBALS['THEME_COMPONENTS'] as $component){
                require_once $themeDir.'/'.$component;
            }
            $this->isThemeLoaded = TRUE;
            return TRUE;
        }
        throw new Exception('Unable to load theme because root directory is not defined.');
    }
    /**
     * Loads the selected web site theme.
     * @return boolean <b>TRUE</b> if selected theme is loaded.
     * @since 1.0
     * @throws Exception If the constant 'ROOT_DIR' is not defined.
     */
    public function loadTheme(){
        if(defined('ROOT_DIR')){
            $themeDir = ROOT_DIR.'/'.SiteConfig::get()->getThemeDir();
            require_once $themeDir.'/theme.php';
            foreach ($GLOBALS['THEME_COMPONENTS'] as $component){
                require_once $themeDir.'/'.$component;
            }
            $this->isThemeLoaded = TRUE;
            return TRUE;
        }
        throw new Exception('Unable to load theme because root directory is not defined.');
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
    function setWritingDir($dir=self::DIR_LTR){
        $dirL = strtolower($dir);
        if($dirL == self::DIR_LTR || $dirL == self::DIR_RTL){
            if($this->getWritingDir()  == NULL){
                $this->contentDir = $dirL;
                return TRUE;
            }
            return FALSE;
        }
        else{
            throw new Exception('Unknown writing direction: '.$dir);
        }
    }
    /**
     * Sets the property that is used to check if page has a header section or not.
     * @param boolean $bool <b>TRUE</b> to include the header section. <b>FALSE</b> if 
     * not.
     * @since 1.2
     */
    public function setHasHeader($bool){
        if(gettype($bool) == 'boolean'){
            $this->incHeader = $bool;
        }
    }
    /**
     * Sets the property that is used to check if page has a footer section or not.
     * @param boolean $bool <b>TRUE</b> to include the footer section. <b>FALSE</b> if 
     * not.
     * @since 1.2
     */
    public function setHasFooter($bool){
        if(gettype($bool) == 'boolean'){
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
    
    private function getHeader($dynamic=true){
        if($this->hasHeader()){
            if($dynamic){
                $node = new HTMLNode('', FALSE, TRUE);
                $node->setText('<?php getHeaderNode() ?>');
                return $node;
            }
            else{
                return dynamicPageHeader();
            }
        }
    }
    private function getFooter($dynamic=true){
        if($this->hasFooter()){
            if($dynamic){
                $node = new HTMLNode('', FALSE, TRUE);
                $node->setText('<?php getFooterNode() ?>');
                return $node;
            }
            else{
                return getFooterNode();
            }
        }
    }
    
    private function getHead($dynamic=true){
        if($dynamic){
            $textNode = new HTMLNode('', FALSE, TRUE);
            $textNode->setText('<?php getHeadNode(\''.$this->getCanonical().'\',\''.$this->getTitle().'\',\''.$this->getDescription().'\') ?>');
            return $textNode;
        }
        return getHeadNode($this->getCanonical(), $this->getTitle(), $this->getDescription());
    }
}

