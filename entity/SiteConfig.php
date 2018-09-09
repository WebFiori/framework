<?php
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
class SiteConfig{
    /**
     * An array which contains all website names in different languages.
     * @var string 
     * @since 1.0
     */
    private $webSiteNames;
    /**
     * The primary language of the website.
     */
    private $primaryLang;
    /**
     * An array which contains different descriptions in different languages.
     * @var string 
     * @since 1.0
     */
    private $descriptions;
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
     * The base URL that is used by all web site pages to fetch resource files.
     * @var string 
     * @since 1.0
     */
    private $baseUrl;
    /**
     * The name of base website UI Theme.
     * @var string 
     * @since 1.3
     */
    private $baseThemeName;
    /**
     * The name of admin control pages Theme.
     * @var string 
     * @since 1.3
     */
    private $adminThemeName;
    /**
     * Configuration file version number.
     * @var string 
     * @since 1.2
     */
    private $configVision;
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
        $this->configVision = '1.1';
        $this->webSiteNames = array('AR'=>'أكاديميا البرمجة','EN'=>'Programming Academia',);
        $this->baseUrl = 'http://localhost/liskscode/';
        $this->titleSep = ' | ';
        $this->primaryLang = 'AR';
        $this->baseThemeName = 'Greeny By Ibrahim Ali';
        $this->adminThemeName = 'Greeny By Ibrahim Ali';
        $this->homePage = 'index';
        $this->descriptions = array('AR'=>'','EN'=>'',);
    }
    
    /**
     * Returns the primary language of the website.
     * @return string Language code of the primary language.
     * @since 1.3
     */
    public function getPrimaryLanguage(){
        return $this->primaryLang;
    }
    /**
     * Returns the name of base theme that is used in website pages.
     * @return string The name of base theme that is used in website pages.
     * @since 1.3
     */
    public function getBaseThemeName(){
        return $this->baseThemeName;
    }
    /**
     * Returns the name of the theme that is used in admin control pages.
     * @return string The name of the theme that is used in admin control pages.
     * @since 1.3
     */
    public function getAdminThemeName(){
        return $this->adminThemeName;
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
     * Returns an associative array which contains different website descriptions 
     * in different languages.
     * @return string An associative array which contains different website descriptions 
     * in different languages.
     * @since 1.0
     */
    public function getDescriptions(){
        return $this->descriptions;
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
     * Returns an array which contains diffrent website names in different languages.
     * @return array An array which contains diffrent website names in different languages.
     * @since 1.0
     */
    public function getWebsiteNames(){
        return $this->webSiteNames;
    }
    
}
