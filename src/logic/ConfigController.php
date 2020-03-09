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
use webfiori\entity\DBConnectionInfo;
use webfiori\conf\Config;
use Exception;
/**
 * A class that can be used to modify basic configuration settings of 
 * the web application. 
 * This class is mainly used to add, update or remove database connections and 
 * save them to the file 'Config.php'.
 *
 * @author Ibrahim
 * @version 1.4.4
 */
class ConfigController extends Controller{
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
     * <li>is-config = 'false'</li>
     * <li>release-date</li>
     * <li>version</li>
     * <li>version-type</li>
     * <li>config-file-version</li>
     * <li>databases</li>
     * </ul>
     * @since 1.0
     */
    const INITIAL_CONFIG_VARS = [
        'is-config'=>'false',
        'release-date'=>'2020-03-10',
        'version'=>'1.0.9',
        'version-type'=>'Stable',
        'config-file-version'=>'1.3.4',
        'databases'=>[]
    ];
    /**
     * An instance of SystemFunctions
     * @var ConfigController
     * @since 1.0 
     */
    private static $singleton;
    /**
     * Returns a single instance of the class.
     * @return ConfigController
     * @since 1.0
     */
    public static function get(){
        if(self::$singleton === null){
            self::$singleton = new ConfigController();
        }
        return self::$singleton;
    }
    /**
     * Creates the file 'Config.php' if it does not exist.
     * @since 1.0
     */
    public function createConfigFile() {
        if(!class_exists('webfiori\conf\Config')){
            $cfg = $this->getConfigVars();
            $this->writeConfig($cfg);
        }
    }
    /**
     * Creates new instance of the class.
     * It is not recommended to use this method. Instead, 
     * use SystemFunctions::get().
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
     * @since 1.4.4
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
     * Adds new database connections information or update existing connections.
     * The information of the connections will be stored in the file 'Config.php'.
     * @param array $dbConnectionsInfo An array that contains objects of type DBConnectionInfo. 
     * @since 1.4.3
     */
    public function addOrUpdateDBConnections($dbConnectionsInfo){
        if(gettype($dbConnectionsInfo) == 'array'){
            $confVars = $this->getConfigVars();
            foreach ($dbConnectionsInfo as $con){
                if($con instanceof DBConnectionInfo){
                    if(strlen($con->getHost()) > 0 && 
                       strlen($con->getPort()) > 0 &&
                       strlen($con->getUsername()) > 0 && 
                       strlen($con->getPassword()) > 0 && 
                       strlen($con->getDBName()) > 0){  
                        $confVars['databases'][$con->getConnectionName()] = $con;
                    }
                }
            }
            $this->writeConfig($confVars);
        }
    }
    /**
     * Removes a set of database connections.
     * This method will search for a connection which has the given database 
     * name. Once it found, it will remove the connection and save the updated 
     * information to the file 'Config.php'.
     * @param array $connectionsNames An array that contains the names of database connections.
     * @since 1.4.3
     */
    public function removeDBConnections($connectionsNames){
        if(gettype($connectionsNames) == 'array'){
            $confVars = $this->getConfigVars();
            foreach ($connectionsNames as $dbName){  
                unset($confVars['databases'][$dbName]);
            }
            $this->writeConfig($confVars);
        }
    }
    /**
     * Updates system configuration status.
     * This method is useful when the developer would like to create some 
     * kind of a setup wizard for his web application. This method is used 
     * to update the value which is returned by the method  Config::isConfig().
     * @param boolean $isConfig true to set system as configured. 
     * false to make it not configured.
     * @since 1.3
     */
    public function configured($isConfig=true){
        $confVars = $this->getConfigVars();
        $confVars['is-config'] = $isConfig === true ? 'true' : 'false';
        $this->writeConfig($confVars);
    }
    /**
     * Returns an associative array that contains system configuration 
     * info.
     * The array that will be returned will have the following information: 
     * <ul>
     * <li>is-config: A string. 'true' or 'false'</li>
     * <li>release-date: The release date of WebFiori Framework.</li>
     * <li>version: Version number of WebFiori Framework.</li>
     * <li>version-type: Type of WebFiori Framework version.</li>
     * <li>config-file-version: Configuration file version number.</li>
     * <li>databases: A sub associative array that contains multiple 
     * database connections information. The key will be the name of the database 
     * and the value is an object of type DBConnectionInfo.</li>
     * </ul>
     * @return array An associative array that contains system configuration 
     * info.
     * @since 1.0
     */
    public function getConfigVars(){
        $cfgArr = ConfigController::INITIAL_CONFIG_VARS;
        if(class_exists('webfiori\conf\Config')){
            $cfgArr['is-config'] = Config::isConfig() === true ? 'true' : 'false';
            $cfgArr['databases'] = Config::getDBConnections();
        }
        return $cfgArr;
    }
    /**
     * A method to save changes to configuration file.
     * @param type $configArr An array that contains system configuration 
     * variables.
     * @since 1.0
     */
    private function writeConfig($configArr){
        $configFileLoc = ROOT_DIR.'/conf/Config.php';
        $fh = new FileHandler($configFileLoc);
        $fh->write('<?php', true, true);
        $fh->write('namespace webfiori\conf;', false, true);
        $fh->write('use webfiori\entity\DBConnectionInfo;', true, true);
        $fh->write('/**
 * Global configuration class. 
 * Used by the server part and the presentation part. It contains framework version 
 * information and database connection settings.
 * @author Ibrahim
 * @version 1.3.4
 */', true, true);
        $fh->write('class Config{', true, true);
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
     * An associative array that will contain database connections.
     * @var type 
     */
    private $dbConnections;
    ',true,true);
        $fh->write('/**
     * Initialize configuration.
     */
    private function __construct() {
        $this->isConfigured = '.$configArr['is-config'].';
        $this->releaseDate = \''.$configArr['release-date'].'\';
        $this->version = \''.$configArr['version'].'\';
        $this->versionType = \''.$configArr['version-type'].'\';
        $this->configVision = \''.$configArr['config-file-version'].'\';
        $this->dbConnections = [', true, true);
        $count = count($configArr['databases']);
        $i = 0;
        foreach ($configArr['databases'] as $dbConn){
            if($i + 1 == $count){
                $fh->write('        \''.$dbConn->getConnectionName().'\'=> new DBConnectionInfo(\''
                    .$dbConn->getUsername()
                    .'\',\''
                    .$dbConn->getPassword()
                    .'\',\''
                    .$dbConn->getDBName()
                    .'\',\''
                    .$dbConn->getHost().'\','
                    . ''
                    .$dbConn->getPort().')', true, true);
            }
            else{
                $fh->write('        \''.$dbConn->getConnectionName().'\'=> new DBConnectionInfo(\''
                    .$dbConn->getUsername()
                    .'\',\''
                    .$dbConn->getPassword()
                    .'\',\''
                    .$dbConn->getDBName()
                    .'\',\''
                    .$dbConn->getHost().'\','
                    . ''
                    .$dbConn->getPort().'),', true, true);
            }
            $i++;
        }
        $fh->write('    ];', true, true);
        foreach ($configArr['databases'] as $dbConn){
            $fh->write('$this->dbConnections[\''.$dbConn->getConnectionName().'\']->setConnectionName(\''.$dbConn->getConnectionName().'\');', true, true);
        }
        $fh->write('}', true, true);
        $fh->write('/**
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
    public static function get(){
        if(self::$cfg != null){
            return self::$cfg;
        }
        self::$cfg = new Config();
        return self::$cfg;
    }
    /**
     * Adds new database connection or updates an existing one.
     * @param DBConnectionInfo $connectionInfo an object of type \'DBConnectionInfo\' 
     * that will contain connection information.
     * @since 1.3.4
     */
    public static function addDbConnection($connectionInfo){
        if($connectionInfo instanceof DBConnectionInfo){
            self::get()->dbConnections[$connectionInfo->getConnectionName()] = $connectionInfo;
        }
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
     * @return string WebFiori Framework version type (e.g. \'Beta\', \'Alpha\', \'Preview\').
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
        $trimmed = trim($conName);
        if(isset($conns[$trimmed])){
            return $conns[$trimmed];
        }
        return null;
    } ', true, true);
        $fh->reduceTab();
        $fh->write('}', true, true);
        $fh->close();
    }
    /**
     * Checks if the application setup is completed or not.
     * Note that the method will throw an exception in case one of the 3 main 
     * configuration files is missing.
     * @return boolean If the system is configured, the method will return 
     * true. If it is not configured, It will return false.
     * @throws Exception If one of configuration files is missing. The format 
     * of exception message will be 'XX.php is missing.' where XX is the name 
     * of the configuration file.
     * @since 1.0
     */
    public function isSetupFinished(){
        if(class_exists('webfiori\conf\Config')){
            if(class_exists('webfiori\conf\MailConfig')){
                if(class_exists('webfiori\conf\SiteConfig')){
                    $retVal = Config::isConfig();
                    return $retVal;
                }
                throw new Exception('SiteConfig.php is missing.');
            }
            throw new Exception('MailConfig.php is missing.');
        }
        throw new Exception('Config.php is missing.');
    }
}