<?php
class SiteConfig{
    /**
     * The name of the web site (Such as 'Programming Academia')
     * @var string 
     * @since 1.0
     */
    private $webSiteName;
    /**
     * A general description for the web site.
     * @var string 
     * @since 1.0
     */
    private $description;
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $titleSep;
    /**
     * The URL of the home page.
     * @var string 
     * @since 1.0
     */
    private $homePage;
    /**
     * The directory of the theme that is used by web site administration pages. 
     * @var string
     * @since 1.0 
     */
    private $adminPanelThemeDir;
    /**
     * The base URL that is used by all web site pages to fetch resource files.
     * @var string 
     * @since 1.0
     */
    private $baseUrl;
    /**
     * Configuration file version number.
     * @var string 
     * @since 1.2
     */
    private $configVision;
    /**
     * The directory of web site pages theme.
     * @var string
     * @since 1.0 
     */
    private $selectedThemeDir;
    /**
     * A singleton instance of the class.
     * @var SiteConfig 
     * @since 1.0
     */
    private static $siteCfg;
    /**
     * Returns an instance of the configuration file.
     * @return SiteConfig
     * @since 1.0
     */
    public static function get(){
        if(self::$siteCfg != NULL){
            return self::$siteCfg;
        }
        self::$siteCfg = new SiteConfig();
        return self::$siteCfg;
    }
    private function __construct() {
        $this->configVision = '1.0';
        $this->webSiteName = 'Programming Academia';
        $this->baseUrl = Util::getBaseURL();
        $this->titleSep = ' | ';
        $this->homePage = 'index';
        $this->description = '';
        $this->selectedThemeDir = 'publish/themes/greeny';
        $this->adminPanelThemeDir = 'publish/themes/greeny';
    }
    /**
     * Returns the directory at which the web site theme exist.
     * @return string The directory at which the web site theme exist.
     * @since 1.0
     */
    public function getThemeDir() {
        return $this->selectedThemeDir;
    }
    /**
     * Returns the directory at which the administrator pages theme exists.
     * @return string The directory at which the administrator pages theme exists.
     * @since 1.0
     */
    public function getAdminThemeDir(){
        return $this->adminPanelThemeDir;
    }
    /**
     * Returns version number of the configuration file.
     * @return string The version number of the configuration file.
     * @since 1.0
     */
    public function getConfigVersion(){
        return $this->configVision;
    }
    /**
     * Returns the base URL that is used to fetch resources.
     * @return string the base URL.
     * @since 1.0
     */
    public function getBaseURL(){
        return $this->baseUrl;
    }
    
    /**
     * Returns the description of the web site.
     * @return string The description of the web site.
     * @since 1.0
     */
    public function getDesc(){
        return $this->description;
    }
    /**
     * Returns the character (or string) that is used to separate page title from website name.
     * @return string
     * @since 1.0
     */
    public function getTitleSep(){
        return $this->titleSep;
    }
    /**
     * Returns the home page name of the website.
     * @return string The home page name of the website.
     * @since 1.0
     */
    public function getHomePage(){
        return $this->homePage;
    }
    /**
     * Returns the name of the website.
     * @return string The name of the website.
     * @since 1.0
     */
    public function getWebsiteName(){
        return $this->webSiteName;
    }
    public function __toString() {
        $retVal = '<b>Website Configuration</b><br/>';
        $retVal .= 'Website Name: '.$this->getWebsiteName().'<br/>';
        $retVal .= 'Home Page: '.$this->getHomePage().'<br/>';
        $retVal .= 'Config Version: '.$this->getConfigVersion().'<br/>';
        $retVal .= 'Description: '.$this->getDesc().'<br/>';
        $retVal .= 'Title Separator: '.$this->getTitleSep().'<br/>';
        return $retVal;
    }
}
