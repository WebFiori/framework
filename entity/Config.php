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
/**
 * Global configuration class. Used by the server part and the presentation part.
 * Do not modify this file manually unless you know what you are doing.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.5
 */
class Config{
    /**
     * The type framework version that is used to build the project.
     * @var string The framework version that is used to build the project.
     * @since 1.0 
     */
    private $versionType;
    /**
     * The version of the framework that is used to build the project.
     * @var string The version of the framework that is used to build the project.
     * @since 1.0 
     */
    private $version;
    /**
     * The release date of the framework that is used to build the project.
     * @var string Release date of of the framework that is used to build the project.
     * @since 1.0 
     */
    private $releaseDate;
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
     * Port number of the database.
     * @var string 
     * @since 1.4
     */
    private $dbPort;
    /**
     * The name of database schema.
     * @var string 
     * @since 1.0
     */
    private $dbName;
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
        $this->isConfigured = TRUE;
        $this->releaseDate = '09-25-2018 (DD-MM-YYYY)';
        $this->version = '1.0.0';
        $this->versionType = 'Stable';
        $this->configVision = '1.3.1';
        $this->dbHost = 'localhost';
        $this->dbUser = '';
        $this->dbPass = '';
        $this->dbName = '';
        $this->dbPort = '3306';
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
    public static function &get(){
        if(self::$cfg != NULL){
            return self::$cfg;
        }
        self::$cfg = new Config();
        return self::$cfg;
    }
    private function _getConfigVersion(){
        return $this->configVision;
    }
    /**
     * Returns the version number of configuration file.
     * @return string The version number of configuration file.
     * @since 1.2
     */
    public static function getConfigVersion(){
        return self::get()->_getConfigVersion();
    }
    private function _isConfig(){
        return $this->isConfigured;
    }
    /**
     * Checks if the system is configured or not.
     * @return boolean <b>TRUE</b> if the system is configured.
     * @since 1.0
     */
    public static function isConfig(){
        return self::get()->_isConfig();
    }
    private function _getDBName(){
        return $this->dbName;
    }
    /**
     * Returns the name of the database.
     * @return string Database name.
     * @since 1.0
     */
    public static function getDBName(){
        return self::get()->_getDBName();
    }
    private function _getDBPort(){
        return $this->dbPort;
    }
    /**
     * Returns the number of the port that is used to connect to the database.
     * @return string Database name.
     * @since 1.0
     */
    public static function getDBPort(){
        return self::get()->_getDBPort();
    }
    private function _getDBHost(){
        return $this->dbHost;
    }
    /**
     * Returns the name of database host.
     * @return string Database host.
     * @since 1.0
     */
    public static function getDBHost(){
        return self::get()->_getDBHost();
    }
    private function _getDBUser(){
        return $this->dbUser;
    }
    /**
     * Returns the name of the database user.
     * @return string Database username.
     * @since 1.0
     */
    public static function getDBUser(){
        return self::get()->_getDBUser();
    }
    private function _getDBPassword(){
        return $this->dbPass;
    }
    /**
     * Returns the password of the database user.
     * @return string Database password.
     * @since 1.0
     */
    public static function getDBPassword(){
        return self::get()->_getDBPassword();
    }
    private function _getVersion(){
        return $this->version;
    }
    /**
     * Returns framework version number.
     * @return string Framework version number.
     * @since 1.2
     */
    public static function getVersion(){
        return self::get()->_getVersion();
    }
    private function _getVersionType(){
        return $this->versionType;
    }
    /**
     * Returns framework version type.
     * @return string framework version type.
     * @since 1.2
     */
    public static function getVersionType(){
        return self::get()->_getVersionType();
    }
    private function _getReleaseDate(){
        return $this->releaseDate;
    }
    /**
     * Returns the date at which the framework is released.
     * @return string The date at which the framework is released.
     * @since 1.0
     */
    public static function getReleaseDate(){
        return self::get()->_getReleaseDate();
    }
}
