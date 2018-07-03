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
if(!defined('ROOT_DIR')){
    header('HTTP/1.1 403 Forbidden');
    exit;
}
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
        'website-name'=>'Programming Academia',
        'base-url'=>'',
        'title-separator'=>' | ',
        'home-page'=>'index',
        'theme-directory'=>'publish/themes/greeny',
        'admin-theme-directory'=>'publish/themes/greeny',
        'admin-theme-name'=>'Greeny By Ibrahim Ali',
        'theme-name'=>'Greeny By Ibrahim Ali',
        'site-description'=>'',
        'config-file-version'=>'1.1',
    );
    /**
     *
     * @var WebsiteFunctions 
     */
    private static $singleton;
    /**
     * 
     * @return SystemFunctions
     * @since 1.0
     */
    public static function get(){
        if(self::$singleton !== NULL){
            return self::$singleton;
        }
        self::$singleton = new WebsiteFunctions();
        return self::$singleton;
    }
    public function __construct() {
        parent::__construct();
        
    }
    
    /**
     * Creates the file 'SiteConfig.php' if it does not exist.
     * @since 1.0
     */
    public function createSiteConfigFile() {
        if(!class_exists('SiteConfig')){
            $initCfg = $this->getSiteConfigVars();
            $this->writeSiteConfig($initCfg);
        }
    }
    /**
     * Updates web site configuration based on some attributes.
     * @param array $websiteInfoArr an associative array. The array can 
     * have the following indices: 
     * <ul>
     * <li><b>website-name</b>:</li>
     * <li><b>base-url</b>:</li>
     * <li><b>title-separator</b>:</li>
     * <li><b>home-page</b>:</li>
     * <li><b>theme-directory</b>:</li>
     * <li><b>admin-theme-directory</b>:</li>
     * <li><b>site-description</b>:</li>
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
     * @return array An associative array that contains web site configuration 
     * info.
     * @since 1.0
     */
    public function getSiteConfigVars(){
        $cfgArr = WebsiteFunctions::INITIAL_WEBSITE_CONFIG_VARS;
        $cfgArr['base-url'] = Util::getBaseURL();
        if(class_exists('SiteConfig')){
            $cfgArr['website-name'] = SiteConfig::get()->getWebsiteName();
            $cfgArr['base-url'] = SiteConfig::get()->getBaseURL();
            $cfgArr['title-separator'] = SiteConfig::get()->getTitleSep();
            $cfgArr['home-page'] = SiteConfig::get()->getHomePage();
            $cfgArr['theme-directory'] = SiteConfig::get()->getThemeDir();
            $cfgArr['admin-theme-directory'] = SiteConfig::get()->getAdminThemeDir();
            $cfgArr['site-description'] = SiteConfig::get()->getDesc();
            $cfgArr['theme-name'] = SiteConfig::get()->getBaseThemeName();
            $cfgArr['admin-theme-name'] = SiteConfig::get()->getAdminThemeName();
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
        $fh = new FileHandler(ROOT_DIR.'/entity/SiteConfig.php');
        $fh->write('<?php', TRUE, TRUE);
        $fh->write('if(!defined(\'ROOT_DIR\')){
    header(\'HTTP/1.1 403 Forbidden\');
    exit;
}', TRUE, TRUE);
        $fh->write('class SiteConfig{', TRUE, TRUE);
        $fh->addTab();
        $fh->write('/**
     * The name of the web site (Such as \'Programming Academia\')
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
     * @deprecated since version 1.3
     */
    private $adminPanelThemeDir;
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
     * The directory of web site pages theme.
     * @var string
     * @since 1.0 
     * @deprecated since version 1.3
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
    }', TRUE, TRUE);
        $fh->write('private function __construct() {
        $this->configVision = \''.$configArr['config-file-version'].'\';
        $this->webSiteName = \''.$configArr['website-name'].'\';
        $this->baseUrl = \''.$configArr['base-url'].'\';
        $this->titleSep = \' '. trim($configArr['title-separator']).' \';
        $this->baseThemeName = \''.$configArr['theme-name'].'\';
        $this->adminThemeName = \''.$configArr['admin-theme-name'].'\';
        $this->homePage = \''.$configArr['home-page'].'\';
        $this->description = \''.$configArr['site-description'].'\';
        $this->selectedThemeDir = \''.$configArr['theme-directory'].'\';
        $this->adminPanelThemeDir = \''.$configArr['admin-theme-directory'].'\';
    }', TRUE, TRUE);
        $fh->write('/**
     * Returns the directory at which the web site theme exist.
     * @return string The directory at which the web site theme exist.
     * @since 1.0
     * @deprecated since version 1.3
     */
    public function getThemeDir() {
        return $this->selectedThemeDir;
    }
    /**
     * Returns the directory at which the administrator pages theme exists.
     * @return string The directory at which the administrator pages theme exists.
     * @since 1.0
     * @deprecated since version 1.3
     */
    public function getAdminThemeDir(){
        return $this->adminPanelThemeDir;
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
        $retVal = \'<b>Website Configuration</b><br/>\';
        $retVal .= \'Website Name: \'.$this->getWebsiteName().\'<br/>\';
        $retVal .= \'Home Page: \'.$this->getHomePage().\'<br/>\';
        $retVal .= \'Config Version: \'.$this->getConfigVersion().\'<br/>\';
        $retVal .= \'Description: \'.$this->getDesc().\'<br/>\';
        $retVal .= \'Title Separator: \'.$this->getTitleSep().\'<br/>\';
        return $retVal;
    }', TRUE, TRUE);
        $fh->reduceTab();
        $fh->write('}', TRUE, TRUE);
        $fh->close();
    }
}
