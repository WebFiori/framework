<?php
/**
 * The directory where themes will be stored.
 */
define('THEMES_DIR','publish/themes');
/**
 * A class used to initialize main page components.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.6
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
     * An array that contains loaded theme info.
     * @var array 
     * @since 1.6
     */
    private $theme;
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
        if($this->document != NULL){
            $this->document->getHeadNode()->setTitle($this->getTitle().SiteConfig::get()->getTitleSep().SiteConfig::get()->getWebsiteName());
        }
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
                $this->document->getHeadNode()->setBase(SiteConfig::get()->getBaseURL());
                $this->document->getHeadNode()->addMeta('description', $this->getDescription());
                $this->document->getHeadNode()->setTitle($this->getTitle().SiteConfig::get()->getTitleSep().SiteConfig::get()->getWebsiteName());
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
        if($this->document != NULL){
            $this->document->getHeadNode()->addMeta('description', $val);
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
     * @return array An associative array that contains all themes information. The key 
     * is the theme name and the value is theme info.
     * @throws Exception If the constant 'ROOT_DIR' is not defined.
     * @since 1.1 
     */
    public function getAvailableThemes(){
        if(defined('ROOT_DIR')){
            $themeNames = array();
            $themesDirs = array_diff(scandir(ROOT_DIR.'/'.THEMES_DIR), array('..', '.'));
            foreach ($themesDirs as $dir){
                include ROOT_DIR.'/'.THEMES_DIR.'/'.$dir.'/theme.php';
                if(isset($GLOBALS['THEME'])){
                    $themeNames[$GLOBALS['THEME']['META']['name']] = $GLOBALS['THEME'];
                    unset($GLOBALS['THEME']);
                }
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
     * @deprecated since version 1.4
     */
    public function loadAdminTheme(){
        return $this->usingTheme(SiteConfig::get()->getAdminThemeName());
    }
    /**
     * Loads a theme given its name.
     * @param string $themeName [Optional] The name of the theme as specified by the 
     * variable 'name' in theme definition. If the given name is <b>NULL</b>, the 
     * function will load the default theme as specified by the function 
     * <b>SiteConfig::getBaseThemeName()</b>.
     * @return boolean The function will return <b>TRUE</b> once the 
     * theme is loaded.
     * @throws Exception The first case that the function will throw an exception 
     * is when the constant 'ROOT_DIR' is not defined. Also the function will throw 
     * an exception if no theme was found which has the given name. Another case is 
     * when the file 'theme.php' of the theme is missing. Also if the variabale 
     * 'THEME_COMPONENTS' is not defined. Finally, an exception will be thrown 
     * if theme component is not found.
     * @since 1.4
     */
    public function usingTheme($themeName=null) {
        if(defined('ROOT_DIR')){
            if($themeName == NULL){
                $themeName = SiteConfig::get()->getBaseThemeName();
            }
            $themes = $this->getAvailableThemes();
            if(isset($themes[$themeName])){
                $this->theme = $themes[$themeName];
                $theme = $this->getTheme();
                $themeDir = ROOT_DIR.'/'.THEMES_DIR.'/'.$theme['META']['directory'];
                require_once $themeDir.'/theme.php';
                if(isset($theme['COMPONENTS'])){
                    foreach ($theme['COMPONENTS'] as $component){
                        if(file_exists($themeDir.'/'.$component)){
                            require_once $themeDir.'/'.$component;
                        }
                        else{
                            throw new Exception('Component \''.$component.'\' of the theme not found. Eather define it or remove it from the array \'COMPONENTS\'.');
                        }
                    }
                    $this->isThemeLoaded = TRUE;
                    return TRUE;
                }
                throw new Exception('The variable \'COMPONENTS\' is missing from the theme definition.');
            }
            throw new Exception('No such theme: \''.$themeName.'\'.');
        }
        throw new Exception('Unable to load theme because root directory is not defined.');
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
            return THEMES_DIR.'/'.$theme['css-dir'];
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
            return THEMES_DIR.'/'.$theme['images-dir'];
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
            return THEMES_DIR.'/'.$theme['js-dir'];
        }
        return '';
    }
    /**
     * Returns an array that contains theme information.
     * @return array|NULL An array that contains theme information. If the theme 
     * is not loaded, the function will return <b>NULL</b>.
     * @since 1.6
     */
    public function getTheme() {
        return $this->theme;
    }
    /**
     * Loads the selected web site theme.
     * @return boolean <b>TRUE</b> if selected theme is loaded.
     * @since 1.0
     * @throws Exception If the constant 'ROOT_DIR' is not defined.
     * @deprecated since version 1.4
     */
    public function loadTheme(){
        return $this->usingTheme(SiteConfig::get()->getBaseThemeName());
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
                $node->setText('<?php echo getHeaderNode() ?>');
                return $node;
            }
            else{
                return getHeaderNode();
            }
        }
    }
    
    private function getFooter($dynamic=true){
        if($this->hasFooter()){
            if($dynamic){
                $node = new HTMLNode('', FALSE, TRUE);
                $node->setText('<?php echo getFooterNode() ?>');
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
            $textNode->setText('<?php echo getHeadNode() ?>');
            return $textNode;
        }
        return getHeadNode();
    }
}

