<?php
namespace webfiori;
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
    public static function &get(){
        if(self::$siteCfg != NULL){
            return self::$siteCfg;
        }
        self::$siteCfg = new SiteConfig();
        return self::$siteCfg;
    }
    private function __construct() {
        $this->configVision = '1.2.1';
        $this->webSiteNames = array('AR'=>'أكاديميا البرمجة','EN'=>'Programming Academia',);
        $this->baseUrl = 'http://localhost/liskscode/';
        $this->titleSep = ' | ';
        $this->primaryLang = 'EN';
        $this->baseThemeName = 'Greeny By Ibrahim Ali';
        $this->adminThemeName = 'Greeny By Ibrahim Ali';
        $this->homePage = 'index';
        $this->descriptions = array('AR'=>'','EN'=>'',);
    }
    
    private function _getPrimaryLanguage(){
        return $this->primaryLang;
    }
    /**
     * Returns the primary language of the website.
     * This function will return a language code such as 'EN'.
     * @return string Language code of the primary language.
     * @since 1.3
     */
    public static function getPrimaryLanguage(){
        return self::get()->_getPrimaryLanguage();
    }
    private function _getBaseThemeName(){
        return $this->baseThemeName;
    }
    /**
     * Returns the name of base theme that is used in website pages.
     * Usually, this theme is used for the normall visitors of the web site.
     * @return string The name of base theme that is used in website pages.
     * @since 1.3
     */
    public static function getBaseThemeName(){
        return self::get()->_getBaseThemeName();
    }
    private function _getAdminThemeName(){
        return $this->adminThemeName;
    }
    /**
     * Returns the name of the theme that is used in admin control pages.
     * @return string The name of the theme that is used in admin control pages.
     * @since 1.3
     */
    public static function getAdminThemeName(){
        return self::get()->_getAdminThemeName();
    }
    private function _getConfigVersion(){
        return $this->configVision;
    }
    /**
     * Returns version number of the configuration file.
     * This value can be used to check for the compatability of configuration 
     * file
     * @return string The version number of the configuration file.
     * @since 1.0
     */
    public static function getConfigVersion(){
        return self::get()->_getConfigVersion();
    }
    private function _getBaseURL(){
        return $this->baseUrl;
    }
    /**
     * Returns the base URL that is used to fetch resources.
     * The return value of this function is usually used by the tag 'base' 
     * of web site pages.
     * @return string the base URL.
     * @since 1.0
     */
    public static function getBaseURL(){
        return self::get()->_getBaseURL();
    }
    private function _getDescriptions(){
        return $this->descriptions;
    }
    /**
     * Returns an associative array which contains different website descriptions 
     * in different languages.
     * Each index will contain a language code and the value will be the description 
     * of the website in the given language.
     * @return string An associative array which contains different website descriptions 
     * in different languages.
     * @since 1.0
     */
    public static function getDescriptions(){
        return self::get()->_getDescriptions();
    }
    private function _getTitleSep(){
        return $this->titleSep;
    }
    /**
     * Returns the character (or string) that is used to separate page title from website name.
     * @return string A string such as ' - ' or ' | '. Note that the function 
     * will add the two spaces by default.
     * @since 1.0
     */
    public static function getTitleSep(){
        return self::get()->_getTitleSep();
    }
    private function _getHomePage(){
        return $this->homePage;
    }
    /**
     * Returns the home page URL of the website.
     * @return string The home page URL of the website.
     * @since 1.0
     */
    public static function getHomePage(){
        return self::get()->_getHomePage();
    }
    private function _getWebsiteNames(){
        return $this->webSiteNames;
    }
    /**
     * Returns an array which contains diffrent website names in different languages.
     * Each index will contain a language code and the value will be the name 
     * of the website in the given language.
     * @return array An array which contains diffrent website names in different languages.
     * @since 1.0
     */
    public static function getWebsiteNames(){
        return self::get()->_getWebsiteNames();
    }
    
}
