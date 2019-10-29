<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\logic;
use webfiori\entity\FileHandler;
use webfiori\conf\SiteConfig;
/**
 * A class that can be used to modify basic settings of the web site and 
 * save them to the file 'SiteConfig.php'
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class WebsiteController extends Controller{
    /**
     * An associative array that contains initial system configuration variables.
     * The array has the following values:
     * <ul>
     * <li>site-descriptions = array(<ul>
     * <li>EN = 'WebFiori'</li>
     * <li>AR = 'ويب فيوري'</li>
     * </ul>)</li>
     * <li>base-url = ''</li>
     * <li>primary-language = 'EN'</li>
     * <li>title-separator = ' | '</li>
     * <li>home-page = 'index'</li>
     * <li>admin-theme-name = 'WebFiori Theme'</li>
     * <li>theme-name = 'WebFiori Theme'</li>
     * <li>site-descriptions = array(<ul>
     * <li>EN = ''</li>
     * <li>AR = ''</li>
     * </ul>)</li>
     * <li>config-file-version => 1.2.1</li>
     * </ul>
     * @since 1.0
     */
    const INITIAL_WEBSITE_CONFIG_VARS = [
        'website-names'=>[
            'EN'=>'WebFiori',
            'AR'=>'ويب فيوري'
        ],
        'base-url'=>'',
        'primary-language'=>'EN',
        'title-separator'=>' | ',
        'home-page'=>'index',
        'admin-theme-name'=>'WebFiori Theme',
        'theme-name'=>'WebFiori Theme',
        'site-descriptions'=>array(
            'EN'=>'',
            'AR'=>''
        ),
        'config-file-version'=>'1.2.1',
    ];
    /**
     *
     * @var WebsiteController 
     */
    private static $singleton;
    /**
     * Returns a singleton instance of the class.
     * @return WebsiteController
     * @since 1.0
     */
    public static function get(){
        if(self::$singleton === null){
            self::$singleton = new WebsiteController();
        }
        return self::$singleton;
    }
    /**
     * Creates new instance of the class.
     * It is not recommended to use this method. Instead, 
     * use WebsiteFunctions::get().
     */
    public function __construct() {
        parent::__construct();
    }
    /**
     * Initialize new session or use an existing one.
     * Note that the name of the session must be 'wf-session' in 
     * order to initialize it.
     * @param array $options An array of session options. See 
     * Controller::useSettion() for more information about available options.
     * @return boolean If session is created or resumed, the method will 
     * return true. False otherwise.
     * @since 1.0.1
     */
    public function useSession($options=[]) {
        if(gettype($options) == 'array' && isset($options['name'])){
            if($options['name'] == 'wf-session'){
                return parent::useSession($options);
            }
        }
        return false;
    }
    /**
     * Creates the file 'SiteConfig.php' if it does not exist.
     * @since 1.0
     */
    public function createSiteConfigFile() {
        if(!class_exists('webfiori\conf\SiteConfig')){
            $initCfg = $this->getSiteConfigVars();
            $this->writeSiteConfig($initCfg);
        }
    }
    /**
     * Updates web site configuration based on some attributes.
     * @param array $websiteInfoArr an associative array. The array can 
     * have the following indices: 
     * <ul>
     * <li><b>primary-language</b>: The main display language of the website.
     * <li><b>website-names</b>: A sub associative array. The index of the 
     * array should be language code (such as 'EN') and the value 
     * should be the name of the web site in the given language.</li>
     * <li><b>title-separator</b>: A character or a string that is used 
     * to separate web site name from web page title. Two common 
     * values are '-' and '|'.</li>
     * <li><b>home-page</b>: The URL of the home page of the web site. For example, 
     * If root URL of the web site is 'https://www.example.com', This page is served 
     * when the user visits this URL.</li>
     * <li><b>theme-name</b>: The name of the theme that will be used to style 
     * web site UI.</li>
     * <li><b>admin-theme-name</b>: If the web site has two UIs (One for normal 
     * users and another for admins), this one 
     * can be used to serve the UI for web site admins.</li>
     * <li><b>site-descriptions</b>: A sub associative array. The index of the 
     * array should be language code (such as 'EN') and the value 
     * should be the general web site description in the given language.</li></li>
     * </ul> 
     * @since 1.0
     */
    public function updateSiteInfo($websiteInfoArr){
        $confArr = $this->getSiteConfigVars();
        foreach ($confArr as $k=>$v){
            if(isset($websiteInfoArr[$k])){
                $confArr[$k] = $websiteInfoArr[$k];
            }
        }
        $this->writeSiteConfig($confArr);
    }
    /**
     * Returns an associative array that contains web site configuration 
     * info.
     * The returned array will have the following indices: 
     * <ul>
     * <li><b>website-names</b>: A sub associative array. The index of the 
     * array will be language code (such as 'EN') and the value 
     * will be the name of the web site in the given language.</li>
     * <li><b>base-url</b>: The URL at which system pages will be served from. 
     * usually, this URL is used in the tag 'base' of the web page.</li>
     * <li><b>title-separator</b>: A character or a string that is used 
     * to separate web site name from web page title.</li>
     * <li><b>home-page</b>: The URL of the home page of the web site.</li>
     * <li><b>theme-name</b>: The name of the theme that will be used to style 
     * web site UI.</li>
     * <li><b>primary-language</b>: Primary language of the website.
     * <li><b>admin-theme-name</b>: The name of the theme that is used to style 
     * admin web pages.</li>
     * <li><b>site-descriptions</b>: A sub associative array. The index of the 
     * array will be language code (such as 'EN') and the value 
     * will be the general web site description in the given language.</li></li>
     * </ul> 
     * @return array An associative array that contains web site configuration 
     * info.
     * @since 1.0
     */
    public function getSiteConfigVars(){
        $cfgArr = WebsiteController::INITIAL_WEBSITE_CONFIG_VARS;
        if(class_exists('webfiori\conf\SiteConfig')){
            $SC = SiteConfig::get();
            $cfgArr['website-names'] = $SC->getWebsiteNames();
            $cfgArr['base-url'] = $SC->getBaseURL();
            $cfgArr['title-separator'] = $SC->getTitleSep();
            $cfgArr['home-page'] = $SC->getHomePage();
            $cfgArr['primary-language'] = $SC->getPrimaryLanguage();
            $cfgArr['site-descriptions'] = $SC->getDescriptions();
            $cfgArr['theme-name'] = $SC->getBaseThemeName();
            $cfgArr['admin-theme-name'] = $SC->getAdminThemeName();
        }
        return $cfgArr;
    }
    /**
     * A method to save changes to web site configuration file.
     * @param array $configArr An array that contains system configuration 
     * variables.
     * @since 1.0
     */
    private function writeSiteConfig($configArr){
        $fh = new FileHandler(ROOT_DIR.'/conf/SiteConfig.php');
        $fh->write('<?php', true, true);
        $fh->write('namespace webfiori\conf;',true,true);
        $fh->write('use webfiori\entity\Util;', true, true);
        $fh->write('/** 
 * Website configuration class.
 * This class is used to control the following settings:
 * <ul>
 * <li>The base URL of the website.</li>
 * <li>The primary language of the website.</li>
 * <li>The name of the website in different languages.</li>
 * <li>The general description of the website in different languages.</li>
 * <li>The character that is used to separate the name of the website from page title.</li>
 * <li>The theme of the website.</li>
 * <li>Admin theme of the website (if uses one).</li>
 * <li>The home page of the website.</li>
 * </ul>
 */', true, true);
        $fh->write('class SiteConfig{', true, true);
        $fh->addTab();
        $fh->write('/**
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
        if(self::$siteCfg != null){
            return self::$siteCfg;
        }
        self::$siteCfg = new SiteConfig();
        return self::$siteCfg;
    }', true, true);
        $names = 'array(';
        foreach ($configArr['website-names'] as $k => $v){
            $names .= '\''.$k.'\'=>\''.$v.'\',';
        }
        $names .= ')';
        $descriptions = 'array(';
        foreach ($configArr['site-descriptions'] as $k => $v){
            $descriptions .= '\''.$k.'\'=>\''.$v.'\',';
        }
        $descriptions .= ')';
        $fh->write('private function __construct() {
        $this->configVision = \''.$configArr['config-file-version'].'\';
        $this->webSiteNames = '.$names.';
        $this->baseUrl = Util::getBaseURL();
        $this->titleSep = \' '. trim($configArr['title-separator']).' \';
        $this->primaryLang = \''. trim($configArr['primary-language']).'\';
        $this->baseThemeName = \''.$configArr['theme-name'].'\';
        $this->adminThemeName = \''.$configArr['admin-theme-name'].'\';
        $this->homePage = Util::getBaseURL();
        $this->descriptions = '.$descriptions.';
    }', true, true);
        $fh->write('
    private function _getPrimaryLanguage(){
        return $this->primaryLang;
    }
    /**
     * Returns the primary language of the website.
     * This function will return a language code such as \'EN\'.
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
     * The return value of this method is usually used by the tag \'base\' 
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
     * @return string A string such as \' - \' or \' | \'. Note that the method 
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
    ', true, true);
        $fh->reduceTab();
        $fh->write('}', true, true);
        $fh->close();
    }
}
