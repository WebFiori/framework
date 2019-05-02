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
 
namespace webfiori\conf;
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die('<!DOCTYPE html><html><head><title>Forbidden</title></head><body>'
    . '<h1>403 - Forbidden</h1><hr><p>Direct access not allowed.</p></body></html>');
}
use webfiori\entity\DBConnectionInfo;
/**
 * Global configuration class. 
 * Used by the server part and the presentation part. It contains framework version 
 * information and database connection settings.
 * @author Ibrahim
 * @version 1.3.3
 */
class Config{
    /**
     * The type framework version that is used to build the system.
     * @var string The framework version that is used to build the system.
     * @since 1.0 
     */
    private $versionType;
    /**
     * The version of the framework that is used to build the system.
     * @var string The version of the framework that is used to build the system.
     * @since 1.0 
     */
    private $version;
    /**
     * The release date of the framework that is used to build the system.
     * @var string Release date of of the framework that is used to build the system.
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
     * An associative array that will contain database connections.
     * @var type 
     */
    private $dbConnections;
    
    /**
     * Initialize configuration.
     */
    private function __construct() {
        $this->isConfigured = false;
        $this->releaseDate = '2019-03-06';
        $this->version = '1.0.0';
        $this->versionType = 'Stable';
        $this->configVision = '1.3.3';
        $this->dbConnections = array(
        );
    }
    /**
     * An instance of Config.
     * @var Config 
     * @since 1.0
     */
    private static $cfg;
    /**
     * Returns an object that can be used to access configuration information.
     * @return Config An object of type Config.
     * @since 1.0
     */
    public static function &get(){
        if(self::$cfg != null){
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
     * The value is used to check for configuration compatibility since the 
     * framework is updated and more features are added.
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
     * This method is helpful in case the developer would like to create some 
     * kind of a setup wizard for the web application.
     * @return boolean true if the system is configured.
     * @since 1.0
     */
    public static function isConfig(){
        return self::get()->_isConfig();
    }
    private function _getDBName(){
        return $this->dbName;
    }
    
    private function _getVersion(){
        return $this->version;
    }
    /**
     * Returns WebFiori Framework version number.
     * @return string WebFiori Framework version number. The version number will 
     * have the following format: x.x.x
     * @since 1.2
     */
    public static function getVersion(){
        return self::get()->_getVersion();
    }
    private function _getVersionType(){
        return $this->versionType;
    }
    /**
     * Returns WebFiori Framework version type.
     * @return string WebFiori Framework version type (e.g. 'Beta', 'Alpha', 'Preview').
     * @since 1.2
     */
    public static function getVersionType(){
        return self::get()->_getVersionType();
    }
    private function _getReleaseDate(){
        return $this->releaseDate;
    }
    /**
     * Returns the date at which the current version of the framework is released.
     * The format of the date will be YYYY-MM-DD.
     * @return string The date at which the current version of the framework is released.
     * @since 1.0
     */
    public static function getReleaseDate(){
        return self::get()->_getReleaseDate();
    }
    /**
     * Returns an associative array that contain the information of database connections.
     * The keys of the array will be the name of database connection and the value of 
     * each key will be an object of type DBConnectionInfo.
     * @return array An associative array.
     * @since 1.3.3
     */
    public static function getDBConnections(){
        return self::get()->dbConnections;
    }
    /**
     * Returns database connection information given connection name.
     * @param string $conName The name of the connection.
     * @return DBConnectionInfo|null The method will return an object of type 
     * DBConnectionInfo if a connection info was found for the given connection name. 
     * Other than that, the method will return null.
     * @since 1.3.3
     */
    public static function getDBConnection($conName){
        $conns = self::getDBConnections();
        if(isset($conns[$conName])){
            return $conns[$conName];
        }
        return null;
    } 
}
