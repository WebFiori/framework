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
namespace webfiori\functions;
use webfiori\entity\Logger;
use webfiori\WebFiori;
use webfiori\entity\FileHandler;
use webfiori\Config;
use Exception;
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
 * A class that can be used to modify basic configuration settings of 
 * the website and save them to the file 'Config.php'
 *
 * @author Ibrahim
 * @version 1.4.2
 */
class SystemFunctions extends Functions{
    /**
     * A constant that indicates the selected database schema has tables.
     * @since 1.1
     */
    const DB_NOT_EMPTY = 'db_has_tables';
    /**
     * A constant that indicates the file Config.php was not found.
     * @since 1.2
     */
    const SYS_CONFIG_MISSING = 'config_file_missing';
    /**
     * A constant that indicates the file SiteConfig.php was not found.
     * @since 1.2
     */
    const SITE_CONFIG_MISSING = 'site_config_file_missing';
    /**
     * A constant that indicates the file MailConfig.php was not found.
     * @since 1.2
     */
    const MAIL_CONFIG_MISSING = 'mail_config_file_missing';
    /**
     * An array that contains initial system configuration variables.
     * This array has the following indices and values:
     * <ul>
     * <li>is-config = 'FALSE'</li>
     * <li>release-date = '01-01-2019 (DD-MM-YYYY)'</li>
     * <li>version = '1.0.1'</li>
     * <li>version-type = 'Stable'</li>
     * <li>config-file-version => '1.3.2'</li>
     * <li>database-host = 'localhost'</li>
     * <li>database-username = ''</li>
     * <li>database-password = ''</li>
     * <li>database-name = ''</li>
     * <li>database-port = '3306'</li>
     * </ul>
     * @since 1.0
     */
    const INITIAL_CONFIG_VARS = array(
        'is-config'=>'FALSE',
        'release-date'=>'01-01-2019 (DD-MM-YYYY)',
        'version'=>'1.0.1',
        'version-type'=>'Stable',
        'config-file-version'=>'1.3.2',
        'database-host'=>'localhost',
        'database-username'=>'',
        'database-password'=>'',
        'database-name'=>'',
        'database-port'=>'3306'
    );
    /**
     * An instance of SystemFunctions
     * @var SystemFunctions
     * @since 1.0 
     */
    private static $singleton;
    /**
     * Returns a single instance of the class.
     * @return SystemFunctions
     * @since 1.0
     */
    public static function &get(){
        Logger::logFuncCall(__METHOD__);
        if(self::$singleton === NULL){
            Logger::log('Initializing \'SystemFunctions\' instance...');
            self::$singleton = new SystemFunctions();
            Logger::log('Initializing of \'SystemFunctions\' completed.');
        }
        Logger::log('Returning \'SystemFunctions\' instance.');
        Logger::logFuncReturn(__METHOD__);
        return self::$singleton;
    }
    /**
     * Creates the file 'Config.php' if it does not exist.
     * @since 1.0
     */
    public function createConfigFile() {
        Logger::logFuncCall(__METHOD__);
        if(!class_exists('webfiori\Config')){
            Logger::log('Creating Configuration File \'Config.php\'');
            $cfg = $this->getConfigVars();
            $this->writeConfig($cfg);
            Logger::log('Created.');
        }
        else{
            Logger::log('Configuration File \'Config.php\' Already Exist.');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Creates new instance of the class.
     * It is not recommended to use this function. Instead, 
     * use SystemFunctions::get().
     */
    public function __construct() {
        parent::__construct(WebFiori::MAIN_SESSION_NAME);
    }
    /**
     * Update database attributes. 
     * @param string $dbHost The name of database host. It can be an IP address 
     * or a URL.
     * @param string $dbUser The username of database user.
     * @param string $dbPass The password of the database user.
     * @param string $dbName The name of database schema.
     * @param int $dbPort Port number of database server.
     * @return boolean|string The function will return TRUE in case 
     * of valid database attributes. Also the function will return 
     * DBConnectionFactory::DB_CONNECTION_ERR in case the connection was not 
     * established.
     * @since 1.0
     */
    public function updateDBAttributes($dbHost,$dbUser,$dbPass,$dbName,$dbPort){
        Logger::logFuncCall(__METHOD__);
        $r = DBConnectionFactory::mysqlLink(array(
            'user'=>$dbUser,
            'host'=>$dbHost,
            'pass'=>$dbPass,
            'db-name'=>$dbName,
            'db-port'=>$dbPort
        ));
        if($r === TRUE){
            $configVars = $this->getConfigVars();
            $configVars['database-host'] = $dbHost;
            $configVars['database-username'] = $dbUser;
            $configVars['database-password'] = $dbPass;
            $configVars['database-name'] = $dbName;
            $configVars['database-port'] = $dbPort;
            $this->writeConfig($configVars);
        }
        else{
            Logger::log('The database connect function did not return TRUE.', 'warning');
        }
        Logger::logReturnValue($r);
        Logger::logFuncReturn(__METHOD__);
        return $r;
    }
    /**
     * Updates system configuration status.
     * This function is useful when the developer would like to create some 
     * kind of a setup wizard for his web application.
     * @param boolean $isConfig TRUE to set system as configured. 
     * FALSE to make it not configured.
     * @since 1.3
     */
    public function configured($isConfig=true){
        Logger::logFuncCall(__METHOD__);
        $confVars = $this->getConfigVars();
        $confVars['is-config'] = $isConfig === TRUE ? 'TRUE' : 'FALSE';
        Logger::log('Is Configured = '.$confVars['is-config'], 'debug');
        $this->writeConfig($confVars);
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Returns an associative array that contains system configuration 
     * info.
     * The array that will be returned will have the following information: 
     * <ul>
     * <li>is-config: A string. 'TRUE' or 'FALSE'</li>
     * <li>release-date: The release date of WebFiori Framework.</li>
     * <li>version: Version number of WebFiori Framework.</li>
     * <li>version-type: Type of WebFiori Framework version.</li>
     * <li>config-file-version: Configuration file version number.</li>
     * <li>database-host: The address of database server host.</li>
     * <li>database-username: The user that will be used to access the database.</li>
     * <li>database-password: The password of database user.</li>
     * <li>database-name: The name of the database.</li>
     * <li>database-port: Database host server number.</li>
     * </ul>
     * @return array An associative array that contains system configuration 
     * info.
     * @since 1.0
     */
    public function getConfigVars(){
        $cfgArr = SystemFunctions::INITIAL_CONFIG_VARS;
        if(class_exists('webfiori\Config')){
            $cfgArr['is-config'] = Config::isConfig() === TRUE ? 'TRUE' : 'FALSE';
            $cfgArr['database-host'] = Config::getDBHost();
            $cfgArr['database-username'] = Config::getDBUser();
            $cfgArr['database-password'] = Config::getDBPassword();
            $cfgArr['database-name'] = Config::getDBName();
            $cfgArr['database-port'] = Config::getDBPort();
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
        Logger::logFuncCall(__METHOD__);
        $configFileLoc = ROOT_DIR.'/entity/Config.php';
        Logger::log('Saving configuration variables to the file \''.$configFileLoc.'\'.');
        foreach ($configArr as $k => $v){
            Logger::log($k.' => '.$v, 'debug');
        }
        $fh = new FileHandler($configFileLoc);
        $fh->write('<?php', TRUE, TRUE);
        $fh->write('namespace webfiori;', FALSE, TRUE);
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
        $fh->write('/**
 * Global configuration class. Used by the server part and the presentation part.
 * Do not modify this file manually unless you know what you are doing.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.5
 */', TRUE, TRUE);
        $fh->write('class Config{', TRUE, TRUE);
        $fh->addTab();
        //stat here
        $fh->write('/**
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
    private $configVision;',TRUE,TRUE);
        $fh->write('/**
     * Initialize configuration.
     */
    private function __construct() {
        $this->isConfigured = '.$configArr['is-config'].';
        $this->releaseDate = \''.$configArr['release-date'].'\';
        $this->version = \''.$configArr['version'].'\';
        $this->versionType = \''.$configArr['version-type'].'\';
        $this->configVision = \''.$configArr['config-file-version'].'\';
        $this->dbHost = \''.$configArr['database-host'].'\';
        $this->dbUser = \''.$configArr['database-username'].'\';
        $this->dbPass = \''.$configArr['database-password'].'\';
        $this->dbName = \''.$configArr['database-name'].'\';
        $this->dbPort = \''.$configArr['database-port'].'\';
    }', TRUE, TRUE);
        $fh->write('/**
     * An instance of Config.
     * @var Config 
     * @since 1.0
     */
    private static $cfg;
    /**
     * Returns a single instance of the configuration file.
     * @return Config An object of type Config.
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
     * @return boolean TRUE if the system is configured.
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
     * Returns server port number that is used to connect to the database.
     * @return string Server port number.
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
     * The host can be an IP address, a URL or simply \'localhost\' if the database 
     * is in the same server that will host the web application.
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
     * Returns the password of database user.
     * @return string Database user\'s password.
     * @since 1.0
     */
    public static function getDBPassword(){
        return self::get()->_getDBPassword();
    }
    private function _getVersion(){
        return $this->version;
    }
    /**
     * Returns WebFiori Framework version number.
     * @return string WebFiori Framework version number.
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
     * @return string WebFiori Framework version type.
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
     * @return string The date at which the current version of the framework is released.
     * @since 1.0
     */
    public static function getReleaseDate(){
        return self::get()->_getReleaseDate();
    }', TRUE, TRUE);
        $fh->reduceTab();
        $fh->write('}', TRUE, TRUE);
        $fh->close();
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Checks if the application setup is completed or not.
     * Note that the function will throw an exception in case one of the 3 main 
     * configuration files is missing.
     * @return boolean If the system is configured, the function will return 
     * TRUE. If it is not configured, It will return FALSE.
     * @throws Exception If one of configuration files is missing.
     * @since 1.0
     */
    public function isSetupFinished(){
        Logger::logFuncCall(__METHOD__);
        if(class_exists('webfiori\Config')){
            if(class_exists('webfiori\MailConfig')){
                if(class_exists('webfiori\SiteConfig')){
                    $retVal = Config::isConfig();
                    Logger::logReturnValue($retVal);
                    Logger::logFuncReturn(__METHOD__);
                    return $retVal;
                }
                Logger::log('The file \'SiteConfig.php\' is missing. An exception is thrown.', 'error');
                Logger::requestCompleted();
                throw new Exception('SiteConfig.php is missing.');
            }
            Logger::log('The file \'MailConfig.php\' is missing. An exception is thrown.', 'error');
            Logger::requestCompleted();
            throw new Exception('MailConfig.php is missing.');
        }
        Logger::log('The file \'Config.php\' is missing. An exception is thrown.', 'error');
        Logger::requestCompleted();
        throw new Exception('Config.php is missing.');
    }
}