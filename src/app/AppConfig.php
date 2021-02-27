<?php
namespace app;
use webfiori\http\Uri;
/**
 * Description of AppConfig
 *
 * @author Ibrahim
 */
class AppConfig {
    private $appVestion;
    private $appReleaseDate;
    private $appVersionType;
    private $defaultPageTitles;

    public function getVersion() {
        return $this->appVestion;
    }
    public function getReleaseDate() {
        return $this->appReleaseDate;
    }
    public function getVersionType() {
        return $this->appVersionType;
    }
    /**
     * The name of admin control pages Theme.
     * 
     * @var string
     * 
     * @since 1.3
     */
    private $adminThemeName;
    /**
     * The name of base website UI Theme.
     * 
     * @var string
     * 
     * @since 1.3
     */
    private $baseThemeName;
    /**
     * The base URL that is used by all web site pages to fetch resource files.
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $baseUrl;
    /**
     * Configuration file version number.
     * 
     * @var string
     * 
     * @since 1.2
     */
    private $configVision;
    /**
     * An array which contains different descriptions in different languages.
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $descriptions;
    /**
     * The URL of the home page.
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $homePage;
    /**
     * The primary language of the website.
     */
    private $primaryLang;
    /**
     *
     * @var string
     * 
     * @since 1.0
     */
    private $titleSep;
    /**
     * An array which contains all website names in different languages.
     * 
     * @var string
     * 
     * @since 1.0
     */
    private $webSiteNames;
    public function __construct() {
        $this->configVision = '1.2.1';
        $this->webSiteNames = [
            'EN'=>'WebFiori',
            'AR'=>'ويب فيوري',
        ];
        $this->baseUrl = Uri::getBaseURL();
        $this->titleSep = '|';
        $this->primaryLang = 'EN';
        $this->baseThemeName = 'WebFiori V108';
        $this->adminThemeName = 'WebFiori V108';
        $this->homePage = Uri::getBaseURL();
        $this->descriptions = [
            'EN'=>'',
            'AR'=>'',
        ];
        $this->defaultPageTitles = [
            'EN' => 'Hello World',
            'AR' => 'أهلا بالعالم'
        ];
    }
    /**
     * 
     * @param type $langCode
     * @return type
     */
    public function getDescription($langCode) {
        $langs = $this->getDescriptions();
        $langCodeF = strtoupper(trim($langCode));
        
        if (isset($langs[$langCodeF])) {
            return $langs[$langCode];
        }
    }
    public function getWebsiteName($langCode) {
        $langs = $this->getWebsiteNames();
        $langCodeF = strtoupper(trim($langCode));
        
        if (isset($langs[$langCodeF])) {
            return $langs[$langCode];
        }
    }
    public function getDefaultTitles() {
        return $this->defaultPageTitles;
    }
    public function getDefaultTitle($langCode) {
        $langs = $this->getDefaultTitles();
        $langCodeF = strtoupper(trim($langCode));
        
        if (isset($langs[$langCodeF])) {
            return $langs[$langCode];
        }
    }
    /**
     * Returns the name of the theme that is used in admin control pages.
     * 
     * @return string The name of the theme that is used in admin control pages.
     * 
     * @since 1.3
     */
    public function getAdminThemeName() {
        return $this->adminThemeName;
    }
    /**
     * Returns the name of base theme that is used in website pages.
     * 
     * Usually, this theme is used for the normally visitors of the web site.
     * 
     * @return string The name of base theme that is used in website pages.
     * 
     * @since 1.3
     */
    public function getBaseThemeName() {
        return $this->baseThemeName;
    }
    /**
     * Returns the base URL that is used to fetch resources.
     * 
     * The return value of this method is usually used by the tag 'base'
     * of web site pages.
     * 
     * @return string the base URL.
     * 
     * @since 1.0
     */
    public function getBaseURL() {
        return $this->baseUrl;
    }
    /**
     * Returns version number of the configuration file.
     * 
     * This value can be used to check for the compatability of configuration
     * file
     * 
     * @return string The version number of the configuration file.
     * 
     * @since 1.0
     */
    public function getConfigVersion() {
        return $this->configVision;
    }
    /**
     * Returns an associative array which contains different website descriptions
     * in different languages.
     * 
     * Each index will contain a language code and the value will be the description
     * of the website in the given language.
     * 
     * @return array An associative array which contains different website descriptions
     * in different languages.
     * 
     * @since 1.0
     */
    public function getDescriptions() {
        return $this->descriptions;
    }
    /**
     * Returns the home page URL of the website.
     * 
     * @return string The home page URL of the website.
     * 
     * @since 1.0
     */
    public function getHomePage() {
        return $this->homePage;
    }
    /**
     * Returns the primary language of the website.
     * 
     * This function will return a language code such as 'EN'.
     * 
     * @return string Language code of the primary language.
     * 
     * @since 1.3
     */
    public function getPrimaryLanguage() {
        return $this->primaryLang;
    }
    /**
     * Returns the character (or string) that is used to separate page title from website name.
     * 
     * @return string A string such as ' - ' or ' | '. Note that the method
     * will add the two spaces by default.
     * 
     * @since 1.0
     */
    public function getTitleSep() {
        return $this->titleSep;
    }
    /**
     * Returns an array which contains diffrent website names in different languages.
     * 
     * Each index will contain a language code and the value will be the name
     * of the website in the given language.
     * 
     * @return array An array which contains diffrent website names in different languages.
     * 
     * @since 1.0
     */
    public function getWebsiteNames() {
        return $this->webSiteNames;
    }
}
