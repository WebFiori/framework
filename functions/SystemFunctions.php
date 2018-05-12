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
require_once '../root.php';
/**
 * Description of SystemFunctions
 *
 * @author Ibrahim
 * @version 1.0
 */
class SystemFunctions extends Functions{
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
        'site-description'=>'',
        'config-file-virsion'=>'1.0',
    );
    /**
     * An array that contains initial system configuration variables.
     * @since 1.0
     */
    const INITIAL_CONFIG_VARS = array(
        'is-config'=>'FALSE',
        'template-date'=>'10-03-2018 (DD-MM-YYYY)',
        'template-version'=>'0.1.2',
        'template-version-type'=>'Beta',
        'config-file-virsion'=>'1.2',
        'database-host'=>'localhost',
        'database-username'=>'',
        'database-password'=>'',
        'database-name'=>'',
        'system-version'=>'1.0',
        'system-version-type'=>'Stable'
    );
    
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
        self::$singleton = new SystemFunctions();
        return self::$singleton;
    }
    public function __construct() {
        parent::__construct();
    }
    /**
     * Update database attributes. 
     * @param string $dbHost The name of database host. It can be an IP address 
     * or a URL.
     * @param string $dbUser The username of database user.
     * @param string $dbPass The password of the database user.
     * @param string $dbName The name of database schema.
     * @return boolean|string The function will return <b>TRUE</b> in case 
     * of valid database attributes. Other than that, the function will return 
     * <b>SessionManager::DB_CONNECTION_ERR</b>.
     * @since 1.0
     */
    public function updateDBAttributes($dbHost,$dbUser,$dbPass,$dbName){
        $r = $this->getMainSession()->useDb(array(
            'user'=>$dbUser,
            'host'=>$dbHost,
            'pass'=>$dbPass,
            'db-name'=>$dbName
        ));
        if($r === TRUE){
            $config = $this->getConfigVars();
            $config['database-host'] = $dbHost;
            $config['database-username'] = $dbUser;
            $config['database-password'] = $dbPass;
            $config['database-name'] = $dbName;
            $this->writeConfig($config);
            return TRUE;
        }
        return $r;
    }
    /**
     * Returns an associative array that contains web site configuration 
     * info.
     * @return array An associative array that contains web site configuration 
     * info.
     * @since 1.0
     */
    public function getSiteConfigVars(){
        $cfgArr = SystemFunctions::INITIAL_WEBSITE_CONFIG_VARS;
        if(class_exists('SiteConfig')){
            $cfgArr['website-name'] = SiteConfig::get()->getWebsiteName();
            $cfgArr['base-url'] = SiteConfig::get()->getBaseURL();
            $cfgArr['title-separator'] = SiteConfig::get()->getTitleSep();
            $cfgArr['home-page'] = SiteConfig::get()->getHomePage();
            $cfgArr['theme-directory'] = SiteConfig::get()->getThemeDir();
            $cfgArr['admin-theme-directory'] = SiteConfig::get()->getAdminThemeDir();
            $cfgArr['site-description'] = SiteConfig::get()->getDesc();
        }
        return $cfgArr;
    }
    /**
     * Returns an associative array that contains system configuration 
     * info.
     * @return array An associative array that contains system configuration 
     * info.
     * @since 1.0
     */
    public function getConfigVars(){
        $cfgArr = SystemFunctions::INITIAL_CONFIG_VARS;
        if(class_exists('Config')){
            $cfgs = Config::get();
            $cfgArr['is-config'] = $cfgs->isConfig() === TRUE ? 'TRUE' : 'FALSE';
            $cfgArr['database-host'] = $cfgs->getDBHost();
            $cfgArr['database-username'] = $cfgs->getDBUser();
            $cfgArr['database-password'] = $cfgs->getDBPassword();
            $cfgArr['database-name'] = $cfgs->getDBName();
        }
        return $cfgArr;
    }
    /**
     * A function to save changes to configuration file.
     * @param type $configArr An array that contains system configuration 
     * variables.
     * @since 1.0
     */
    private function writeConfig($configArr){
        $fh = new FileHandler(ROOT_DIR.'/CFG_Test.php');
        $fh->write('<?php', TRUE, TRUE);
        $fh->write('/**
 * Global configuration class. Used by the server part and the presentation part.
 * Do not modify this file manually unless you know what you are doing.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1
 */', TRUE, TRUE);
        $fh->write('class Config{', TRUE, TRUE);
        $fh->addTab();
        //stat here
        $fh->write('/**
     * The type version of the template that is used to build the project.
     * @var string The type version of the template that is used to build the project.
     * @since 1.0 
     */
    private $templateVersionType;
    /**
     * The version of the template that is used to build the project.
     * @var string The version of the template that is used to build the project.
     * @since 1.0 
     */
    private $templateVersion;
    /**
     * The release date of the template that is used to build the project.
     * @var string Release date of of the template that is used to build the project.
     * @since 1.0 
     */
    private $templateDate;
    /**
     * A boolean value. Set to true once system configuration is completed.
     * @var boolean 
     * @since 1.0
     */
    private $isConfigured;
    /**
     * The name of database host.
     * @var string 
     * @since 1.0
     */
    private $dbHost;
    /**
     * The name of database username. It must be a user with all privileges over the database.
     * @var string 
     * @since 1.0
     */
    private $dbUser;
    /**
     * The database user\'s password.
     * @var string 
     * @since 1.0
     */
    private $dbPass;
    /**
     * The name of database schema.
     * @var string 
     * @since 1.0
     */
    private $dbName;
    /**
     * System version number.
     * @var string 
     * @since 1.0
     */
    private $systemVersion;
    /**
     * Type of system version (beta, alpha, etc...)
     * @var string 
     * @since 1.0
     */
    private $versionType;
    /**
     * Configuration file version number.
     * @var string 
     * @since 1.2
     */
    private $configVision;',TRUE,TRUE);
        $fh->write('/**
     * Initialize configuration.
     */
    private function __construct() {
        $this->isConfigured = '.$configArr['is-config'].';
        $this->templateDate = \''.$configArr['template-date'].'\';
        $this->templateVersion = \''.$configArr['template-version'].'\';
        $this->templateVersionType = \''.$configArr['template-version-type'].'\';
        $this->configVision = \''.$configArr['config-file-virsion'].'\';
        $this->dbHost = \''.$configArr['database-host'].'\';
        $this->dbUser = \''.$configArr['database-username'].'\';
        $this->dbPass = \''.$configArr['database-password'].'\';
        $this->dbName = \''.$configArr['database-name'].'\';
        $this->systemVersion = \''.$configArr['system-version'].'\';
        $this->versionType = \''.$configArr['system-version-type'].'\';
    }', TRUE, TRUE);
        $fh->write('/**
     * An instance of <b>Config</b>.
     * @var Config 
     * @since 1.0
     */
    private static $cfg;
    /**
     * Returns an instance of the configuration file.
     * @return Config An object of type <b>Config</b>.
     * @since 1.0
     */
    public static function get(){
        if(self::$cfg != NULL){
            return self::$cfg;
        }
        self::$cfg = new Config();
        return self::$cfg;
    }
    /**
     * Returns the version number of configuration file.
     * @return string The version number of configuration file.
     * @since 1.2
     */
    public function getConfigFileVersion(){
        return $this->configVision;
    }
    /**
     * Checks if the system is configured or not.
     * @return boolean <b>TRUE</b> if the system is configured.
     * @since 1.0
     */
    public function isConfig(){
        return $this->isConfigured;
    }
    /**
     * Returns the name of the database.
     * @return string Database name.
     * @since 1.0
     */
    public function getDBName(){
        return $this->dbName;
    }
    /**
     * Returns the name of database host.
     * @return string Database host.
     * @since 1.0
     */
    public function getDBHost(){
        return $this->dbHost;
    }
    /**
     * Returns the name of the database user.
     * @return string Database username.
     * @since 1.0
     */
    public function getDBUser(){
        return $this->dbUser;
    }
    /**
     * Returns the password of the database user.
     * @return string Database password.
     * @since 1.0
     */
    public function getDBPassword(){
        return $this->dbPass;
    }
    /**
     * Returns the version of the system.
     * @return string System version (such as 1.0)
     * @since 1.0
     */
    public function getSysVersion(){
        return $this->systemVersion;
    }
    /**
     * Return the type of system version.
     * @return string Version type (Such as alpha or beta).
     * @since 1.0
     */
    public function getVerType(){
        return $this->versionType;
    }
    /**
     * Returns template version.
     * @return string Template version.
     * @since 1.2
     */
    public function getTemplateVersion(){
        return $this->templateVersion;
    }
    /**
     * Returns template version type.
     * @return string Template version type.
     * @since 1.2
     */
    public function getTemplateVersionType(){
        return $this->templateVersionType;
    }
    /**
     * Returns the date at which the template is released.
     * @return string The date at which the template is released.
     * @since 1.0
     */
    public function getTemplateDate(){
        return $this->templateDate;
    }

    public function __toString() {
        $retVal = \'<b>Project Template Info.</b><br/>\';
        $retVal .= \'<b>Template Version:<b> \'.$this->getTemplateVersion().\'<br/>\';
        $retVal .= \'<b>Template Version Type:<b> \'.$this->getTemplateVersionType().\'<br/>\';
        $retVal .= \'<b>Template Release Date:<b> \'.$this->getTemplateDate().\'<br/><br/>\';
        $retVal .= \'<b>System Configuration Info.</b><br/>\';
        $retVal .= \'<b>System Version:<b> \'.$this->getSysVersion().\'<br/>\';
        $retVal .= \'<b>Version Type:<b> \'. $this->getVerType().\'<br/>\';
        $retVal .= \'<b>Database Host:<b> \'. $this->getDBHost().\'<br/>\';
        $retVal .= \'<b>Database Name:<b> \'.$this->getDBName().\'<br/>\';
        $retVal .= \'<b>Database Username:<b> \'.$this->getDBUser().\'<br/><br/>\';
        return $retVal;
    }', TRUE, TRUE);
        $fh->reduceTab();
        $fh->write('}', TRUE, TRUE);
        $fh->close();
    }
    
    public function isSetupFinished(){
        if(class_exists('Config')){
            return Config::get()->isConfig();
        }
        throw new Exception('Config.php is missing.');
    }
}
//
//$sf = SystemFunctions::get();
//$sf->writeConfig($sf->getConfigVars());
//echo hash('sha256', '123456654321');