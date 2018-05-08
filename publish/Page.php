<?php
/**
 * The directory where themes will be stored.
 */
define('THEMES_DIR','publish/themes');
/**
 * A class used to initialize main page components.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1
 */
class Page{
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
     * A boolean value that is set to true once the theme is loaded.
     * @var boolean 
     * @since 1.1
     */
    private $isThemeLoaded;
    private function __construct() {
        $this->title = NULL;
        $this->contentDir = NULL;
        $this->description = NULL;
        $this->contentLang = NULL;
        $this->isThemeLoaded = FALSE;
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
     * @param type $dynamic
     * @return HTMLDoc An object of type <b>HTMLDoc</b>.
     * @throws Exception If page theme is not loaded.
     * @since 1.1
     */
    public function getDocument($dynamic=true){
        $document = new HTMLDoc();
        if($this->isThemeLoaded()){
            $document->setHeadNode(getHeadNode($dynamic));
            $document->setFooterNode(getFooterNode($dynamic));
            $document->setLanguage($this->getLang());
            return $document;
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
}

