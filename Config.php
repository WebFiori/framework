<?php
/**
 * Global configuration class. Used by the server part and the presentation part.
 * Do not modify this file manually unless you know what you are doing.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class Config{
    /**
     *
     * @var boolean 
     * @since 1.0
     */
    private $isConfigured;
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $dbHost;
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $dbUser;
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $dbPass;
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $dbName;
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $systemVersion;
    /**
     *
     * @var string 
     * @since 1.0
     */
    private $versionType;
    /**
     * Initialize configuration.
     */
    private function __construct() {
        $this->isConfigured = TRUE;
        $this->dbHost = 'localhost';
        $this->dbUser = 'root';
        $this->dbPass = '1329704803';
        $this->dbName = 'y_project';
        $this->systemVersion = '0.1';
        $this->versionType = 'Beta';
    }
    
    private static $cfg;
    /**
     * Returns an instance of the configuration file.
     * @return Config
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
     * @return string Version type (Such as 'alpha', 'beta').
     * @since 1.0
     */
    public function getVerType(){
        return $this->versionType;
    }
    
    public function __toString() {
        $retVal = '<b>System Configuration.</b><br/>';
        $retVal .= 'System Version: '.$this->getSysVersion().'<br/>';
        $retVal .= 'Version Type: '. $this->getVerType().'<br/>';
        $retVal .= 'Database Host: '. $this->getDBHost().'<br/>';
        $retVal .= 'Database Name: '.$this->getDBName().'<br/>';
        $retVal .= 'Database Username: '.$this->getDBUser().'<br/>';
        return $retVal;
    }
    
}
