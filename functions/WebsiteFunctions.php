<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
namespace functions;
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
use webfiori\entity\Logger;
use webfiori\WebFiori;

/**
 * Description of WebsiteFunctions
 *
 * @author Ibrahim
 */
class WebsiteFunctions extends Functions{
    /**
     * An array that contains initial system configuration variables.
     * @since 1.0
     */
    const INITIAL_WEBSITE_CONFIG_VARS = array(
        'website-names'=>array(
            'EN'=>'Programming Academia',
            'AR'=>'أكاديميا البرمجة'
        ),
        'base-url'=>'',
        'primary-language'=>'EN',
        'title-separator'=>' | ',
        'home-page'=>'index',
        'admin-theme-name'=>'Greeny By Ibrahim Ali',
        'theme-name'=>'Greeny By Ibrahim Ali',
        'site-descriptions'=>array(
            'EN'=>'',
            'AR'=>''
        ),
        'config-file-version'=>'1.2',
    );
    /**
     *
     * @var WebsiteFunctions 
     */
    private static $singleton;
    /**
     * 
     * @return WebsiteFunctions
     * @since 1.0
     */
    public static function &get(){
        Logger::logFuncCall(__METHOD__);
        if(self::$singleton === NULL){
            Logger::log('Initializing \'WebsiteFunctions\' instance...');
            self::$singleton = new WebsiteFunctions();
            Logger::log('Initializing of \'WebsiteFunctions\' completed.');
        }
        Logger::log('Returning \'WebsiteFunctions\' instance.');
        Logger::logFuncReturn(__METHOD__);
        return self::$singleton;
    }
    public function __construct() {
        parent::__construct(WebFiori::MAIN_SESSION_NAME);
    }
    /**
     * Creates the file 'SiteConfig.php' if it does not exist.
     * @since 1.0
     */
    public function createSiteConfigFile() {
        Logger::logFuncCall(__METHOD__);
        if(!class_exists('SiteConfig')){
            Logger::log('Creating Configuration File \'SiteConfig.php\'');
            $initCfg = $this->getSiteConfigVars();
            $this->writeSiteConfig($initCfg);
            Logger::log('Creatied.');
        }
        else{
            Logger::log('Configuration File \'SiteConfig.php\' Already Exist.');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Updates web site configuration based on some attributes.
     * @param array $websiteInfoArr an associative array. The array can 
     * have the following indices: 
     * <ul>
     * <li><b>website-names</b>:</li>
     * <li><b>base-url</b>:</li>
     * <li><b>title-separator</b>:</li>
     * <li><b>home-page</b>:</li>
     * <li><b>theme-name</b>:</li>
     * <li><b>admin-theme-name</b>:</li>
     * <li><b>site-descriptions</b>:</li>
     * </ul> 
     * @since 1.0
     */
    public function updateSiteInfo($websiteInfoArr){
        Logger::logFuncCall(__METHOD__);
        $confArr = $this->getSiteConfigVars();
        foreach ($confArr as $k=>$v){
            if(isset($websiteInfoArr[$k])){
                $confArr[$k] = $websiteInfoArr[$k];
            }
        }
        $this->writeSiteConfig($confArr);
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Returns an associative array that contains web site configuration 
     * info.
     * @return array An associative array that contains web site configuration 
     * info.
     * @since 1.0
     */
    public function getSiteConfigVars(){
        $cfgArr = WebsiteFunctions::INITIAL_WEBSITE_CONFIG_VARS;
        if(class_exists('SiteConfig')){
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
     * A function to save changes to web site configuration file.
     * @param array $configArr An array that contains system configuration 
     * variables.
     * @since 1.0
     */
    private function writeSiteConfig($configArr){
        Logger::logFuncCall(__METHOD__);
        foreach ($configArr as $k => $v){
            if(gettype($v) == 'array'){
                foreach ($v as $lCode => $text){
                    Logger::log($k.' => ['.$lCode.' => '.$text.']', 'debug');
                }
            }
            else{
                Logger::log($k.' => '.$v, 'debug');
            }
        }
        $fh = new FileHandler(ROOT_DIR.'/entity/SiteConfig.php');
        $fh->write('<?php', TRUE, TRUE);
        $fh->write('if(!defined(\'ROOT_DIR\')){
    header("HTTP/1.1 403 Forbidden");
    die(\'\'
        . \'<!DOCTYPE html>\'
        . \'<html>\'
        . \'<head>\'
        . \'<title>Forbidden</title>\'
        . \'</head>\'
        . \'<body>\'
        . \'<h1>403 - Forbidden</h1>\'
        . \'<hr>\'
        . \'<p>\'
        . \'Direct access not allowed.\'
        . \'</p>\'
        . \'</body>\'
        . \'</html>\');
}', TRUE, TRUE);
        $fh->write('class SiteConfig{', TRUE, TRUE);
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
        if(self::$siteCfg != NULL){
            return self::$siteCfg;
        }
        self::$siteCfg = new SiteConfig();
        return self::$siteCfg;
    }', TRUE, TRUE);
        $names = 'array(';
        $configArr['website-names'];
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
        $this->baseUrl = \''.$configArr['base-url'].'\';
        $this->titleSep = \' '. trim($configArr['title-separator']).' \';
        $this->primaryLang = \''. trim($configArr['primary-language']).'\';
        $this->baseThemeName = \''.$configArr['theme-name'].'\';
        $this->adminThemeName = \''.$configArr['admin-theme-name'].'\';
        $this->homePage = \''.$configArr['home-page'].'\';
        $this->descriptions = '.$descriptions.';
    }', TRUE, TRUE);
        $fh->write('
    private function _getPrimaryLanguage(){
        return $this->primaryLang;
    }
    /**
     * Returns the primary language of the website.
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
     * @return string
     * @since 1.0
     */
    public static function getTitleSep(){
        return self::get()->_getTitleSep();
    }
    private function _getHomePage(){
        return $this->homePage;
    }
    /**
     * Returns the home page name of the website.
     * @return string The home page name of the website.
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
     * @return array An array which contains diffrent website names in different languages.
     * @since 1.0
     */
    public static function getWebsiteNames(){
        return self::get()->_getWebsiteNames();
    }
    ', TRUE, TRUE);
        $fh->reduceTab();
        $fh->write('}', TRUE, TRUE);
        $fh->close();
        Logger::logFuncReturn(__METHOD__);
    }
}
