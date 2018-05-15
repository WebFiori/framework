<?php
/**
 * Global configuration class. Used by the server part and the presentation part.
 * Do not modify this file manually unless you know what you are doing.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1
 */
class Config{
    /**
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
     * The database user's password.
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
    private $configVision;
    /**
     * Initialize configuration.
     */
    private function __construct() {
        $this->isConfigured = FALSE;
        $this->templateDate = '10-03-2018 (DD-MM-YYYY)';
        $this->templateVersion = '0.1.2';
        $this->templateVersionType = 'Beta';
        $this->configVision = '1.2';
        $this->dbHost = 'localhost';
        $this->dbUser = '';
        $this->dbPass = '';
        $this->dbName = '';
        $this->systemVersion = '1.0';
        $this->versionType = 'Stable';
    }
    /**
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
    public function getConfigVersion(){
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
        $retVal = '<b>Project Template Info.</b><br/>';
        $retVal .= '<b>Template Version:<b> '.$this->getTemplateVersion().'<br/>';
        $retVal .= '<b>Template Version Type:<b> '.$this->getTemplateVersionType().'<br/>';
        $retVal .= '<b>Template Release Date:<b> '.$this->getTemplateDate().'<br/><br/>';
        $retVal .= 'Config Version: '.$this->getConfigVersion().'<br/>';
        $retVal .= '<b>System Configuration Info.</b><br/>';
        $retVal .= '<b>System Version:<b> '.$this->getSysVersion().'<br/>';
        $retVal .= '<b>Version Type:<b> '. $this->getVerType().'<br/>';
        $retVal .= '<b>Database Host:<b> '. $this->getDBHost().'<br/>';
        $retVal .= '<b>Database Name:<b> '.$this->getDBName().'<br/>';
        $retVal .= '<b>Database Username:<b> '.$this->getDBUser().'<br/><br/>';
        return $retVal;
    }
}
