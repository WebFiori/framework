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

use Exception;
use webfiori\conf\Config;
use webfiori\framework\DBConnectionInfo;
use webfiori\framework\exceptions\InitializationException;
use webfiori\framework\File;
/**
 * A class that can be used to modify basic configuration settings of 
 * the web application. 
 * This class is mainly used to add, update or remove database connections and 
 * save them to the file 'Config.php'.
 *
 * @author Ibrahim
 * @version 1.4.4
 */
class ConfigController extends Controller {
    /**
     * A constant that indicates the selected database schema has tables.
     * @since 1.1
     */
    const DB_NOT_EMPTY = 'db_has_tables';
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
        'is-config' => 'false',
        'release-date' => '2020-07-05',
        'version' => '1.1.0',
        'version-type' => 'Beta 3',
        'config-file-version' => '1.3.4',
        'databases' => []
    ];
    /**
     * A constant that indicates the file MailConfig.php was not found.
     * @since 1.2
     */
    const MAIL_CONFIG_MISSING = 'mail_config_file_missing';
    /**
     * A constant that indicates the file SiteConfig.php was not found.
     * @since 1.2
     */
    const SITE_CONFIG_MISSING = 'site_config_file_missing';
    /**
     * A constant that indicates the file Config.php was not found.
     * @since 1.2
     */
    const SYS_CONFIG_MISSING = 'config_file_missing';
    /**
     * An instance of SystemFunctions
     * @var ConfigController
     * @since 1.0 
     */
    private static $singleton;
    /**
     * Adds new database connections information or update existing connections.
     * The information of the connections will be stored in the file 'Config.php'.
     * @param array $dbConnectionsInfo An array that contains objects of type DBConnectionInfo. 
     * @since 1.4.3
     */
    public function addOrUpdateDBConnections($dbConnectionsInfo) {
        if (gettype($dbConnectionsInfo) == 'array') {
            $confVars = $this->getConfigVars();

            foreach ($dbConnectionsInfo as $con) {
                if ($con instanceof DBConnectionInfo && strlen($con->getHost()) > 0 && 
                    strlen($con->getPort()) > 0 &&
                    strlen($con->getUsername()) > 0 && 
                    strlen($con->getPassword()) > 0 && 
                    strlen($con->getDBName()) > 0) {
                    $confVars['databases'][$con->getConnectionName()] = $con;
                }
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
    public function configured($isConfig = true) {
        $confVars = $this->getConfigVars();
        $confVars['is-config'] = $isConfig === true ? 'true' : 'false';
        $this->writeConfig($confVars);
    }
    /**
     * Creates the file 'Config.php' if it does not exist.
     * @since 1.0
     */
    public function createConfigFile() {
        if (!class_exists('webfiori\conf\Config')) {
            $cfg = $this->getConfigVars();
            $this->writeConfig($cfg);
        }
    }
    /**
     * Returns a single instance of the class.
     * @return ConfigController
     * @since 1.0
     */
    public static function get() {
        if (self::$singleton === null) {
            self::$singleton = new ConfigController();
        }

        return self::$singleton;
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
    public function getConfigVars() {
        $cfgArr = ConfigController::INITIAL_CONFIG_VARS;

        if (class_exists('webfiori\conf\Config')) {
            $cfgArr['is-config'] = Config::isConfig() === true ? 'true' : 'false';
            $cfgArr['databases'] = Config::getDBConnections();
        }

        return $cfgArr;
    }
    /**
     * Checks if the application setup is completed or not.
     * Note that the method will throw an exception in case one of the 3 main 
     * configuration files is missing.
     * @return boolean If the system is configured, the method will return 
     * true. If it is not configured, It will return false.
     * @throws InitializationException If one of configuration files is missing. The format 
     * of exception message will be 'XX.php is missing.' where XX is the name 
     * of the configuration file.
     * @since 1.0
     */
    public function isSetupFinished() {
        if (class_exists('webfiori\conf\Config')) {
            if (class_exists('webfiori\conf\MailConfig')) {
                if (class_exists('webfiori\conf\SiteConfig')) {
                    return Config::isConfig();
                }
                throw new InitializationException('SiteConfig.php is missing.');
            }
            throw new InitializationException('MailConfig.php is missing.');
        }
        throw new InitializationException('Config.php is missing.');
    }
    /**
     * Removes a set of database connections.
     * This method will search for a connection which has the given database 
     * name. Once it found, it will remove the connection and save the updated 
     * information to the file 'Config.php'.
     * @param array $connectionsNames An array that contains the names of database connections.
     * @since 1.4.3
     */
    public function removeDBConnections($connectionsNames) {
        if (gettype($connectionsNames) == 'array') {
            $confVars = $this->getConfigVars();

            foreach ($connectionsNames as $dbName) {
                unset($confVars['databases'][$dbName]);
            }
            $this->writeConfig($confVars);
        }
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
    public function useSession($options = []) {
        if (gettype($options) == 'array' && isset($options['name']) && $options['name'] == 'wf-session') {
            return parent::useSession($options);
        }

        return false;
    }
    /**
     * A method to save changes to configuration file.
     * @param type $configArr An array that contains system configuration 
     * variables.
     * @since 1.0
     */
    private function writeConfig($configArr) {
        
        $fileAsStr = "<?php\n"
                . "namespace webfiori\conf;\n"
                . "\n"
                . "use webfiori\\entity\DBConnectionInfo;\n"
                . "/**\n"
                . " * Global configuration class.\n"
                . " * Used by the server part and the presentation part. It contains framework version\n"
                . " * information and database connection settings.\n"
                . " * @author Ibrahim\n"
                . " * @version 1.3.4\n"
                . " */\n"
                . "class Config {\n"
                . "    /**\n"
                . "     * An instance of Config.\n"
                . "     * @var Config\n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    private static \$cfg;\n"
                . "    /**\n"
                . "     * An associative array that will contain database connections.\n"
                . "     * @var type\n"
                . "     */\n"
                . "    private \$dbConnections;\n"
                . "    /**\n"
                . "     * A boolean value. Set to true once system configuration is completed.\n"
                . "     * @var boolean\n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    private \$isConfigured;\n"
                . "    /**\n"
                . "     * The release date of the framework that is used to build the system.\n"
                . "     * @var string Release date of of the framework that is used to build the system.\n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    private \$releaseDate;\n"
                . "    /**\n"
                . "     * The version of the framework that is used to build the system.\n"
                . "     * @var string The version of the framework that is used to build the system.\n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    private \$version;\n"
                . "    /**\n"
                . "     * The type framework version that is used to build the system.\n"
                . "     * @var string The framework version that is used to build the system.\n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    private \$versionType;\n"
                . "    \n"
                . "    /**\n"
                . "     * Initialize configuration.\n"
                . "     */\n"
                . "    private function __construct() {\n"
                . "        \$this->isConfigured = ".$configArr['is-config'].";\n"
                . "        \$this->releaseDate = '".$configArr['release-date']."';\n"
                . "        \$this->version = '".$configArr['version']."';\n"
                . "        \$this->versionType = '".$configArr['version-type']."';\n"
                . "        \$this->configVision = '".$configArr['config-file-version']."';\n"
                . "        \$this->dbConnections = [\n"
                . "";
        $count = count($configArr['databases']);
        $i = 0;

        foreach ($configArr['databases'] as $dbConn) {
            if ($i + 1 == $count) {
                $fileAsStr .= "            '".$dbConn->getConnectionName()."' => new DBConnectionInfo("
                        . "'".$dbConn->getUsername()."', "
                        . "'".$dbConn->getPassword()."', "
                        . "'".$dbConn->getDBName()."', "
                        . "'".$dbConn->getHost()."', "
                        . "".$dbConn->getPort().")";
            } else {
                $fileAsStr .= "            '".$dbConn->getConnectionName()."' => new DBConnectionInfo("
                        . "'".$dbConn->getUsername()."', "
                        . "'".$dbConn->getPassword()."', "
                        . "'".$dbConn->getDBName()."', "
                        . "'".$dbConn->getHost()."', "
                        . "".$dbConn->getPort()."),\n";
            }
            $i++;
        }
        $fileAsStr .= "\n"
                   . "        ];\n";
        foreach ($configArr['databases'] as $dbConn) {
            $fileAsStr .= '        $this->dbConnections[\''.$dbConn->getConnectionName().'\']->setConnectionName(\''.$dbConn->getConnectionName().'\');'."\n";
        }
                $fileAsStr .= ""
                . "    }\n"
                . "    /**\n"
                . "     * Adds new database connection or updates an existing one.\n"
                . "     * @param DBConnectionInfo \$connectionInfo an object of type 'DBConnectionInfo'\n"
                . "     * that will contain connection information.\n"
                . "     * @since 1.3.4\n"
                . "     */\n"
                . "    public static function addDbConnection(\$connectionInfo) {\n"
                . "        if (\$connectionInfo instanceof DBConnectionInfo) {\n"
                . "            self::get()->dbConnections[\$connectionInfo->getConnectionName()] = \$connectionInfo;\n"
                . "        }\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns an object that can be used to access configuration information.\n"
                . "     * @return Config An object of type Config.\n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    public static function get() {\n"
                . "        if (self::\$cfg != null) {\n"
                . "            return self::\$cfg;\n"
                . "        }\n"
                . "        self::\$cfg = new Config();\n"
                . "        \n"
                . "        return self::\$cfg;\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns the version number of configuration file.\n"
                . "     * The value is used to check for configuration compatibility since the\n"
                . "     * framework is updated and more features are added.\n"
                . "     * @return string The version number of configuration file.\n"
                . "     * @since 1.2\n"
                . "     */\n"
                . "    public static function getConfigVersion() {\n"
                . "        return self::get()->_getConfigVersion();\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns database connection information given connection name.\n"
                . "     * @param string \$conName The name of the connection.\n"
                . "     * @return DBConnectionInfo|null The method will return an object of type\n"
                . "     * DBConnectionInfo if a connection info was found for the given connection name.\n"
                . "     * Other than that, the method will return null.\n"
                . "     * @since 1.3.3\n"
                . "     */\n"
                . "    public static function getDBConnection(\$conName) {\n"
                . "        \$conns = self::getDBConnections();\n"
                . "        \$trimmed = trim(\$conName);\n"
                . "        \n"
                . "        if (isset(\$conns[\$trimmed])) {\n"
                . "            return \$conns[\$trimmed];\n"
                . "        }\n"
                . "        \n"
                . "        return null;\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns an associative array that contain the information of database connections.\n"
                . "     * The keys of the array will be the name of database connection and the value of\n"
                . "     * each key will be an object of type DBConnectionInfo.\n"
                . "     * @return array An associative array.\n"
                . "     * @since 1.3.3\n"
                . "     */\n"
                . "    public static function getDBConnections() {\n"
                . "        return self::get()->dbConnections;\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns the date at which the current version of the framework is released.\n"
                . "     * The format of the date will be YYYY-MM-DD.\n"
                . "     * @return string The date at which the current version of the framework is released.\n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    public static function getReleaseDate() {\n"
                . "        return self::get()->_getReleaseDate();\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns WebFiori Framework version number.\n"
                . "     * @return string WebFiori Framework version number. The version number will\n"
                . "     * have the following format: x.x.x\n"
                . "     * @since 1.2\n"
                . "     */\n"
                . "    public static function getVersion() {\n"
                . "        return self::get()->_getVersion();\n"
                . "    }\n"
                . "    /**\n"
                . "     * Returns WebFiori Framework version type.\n"
                . "     * @return string WebFiori Framework version type (e.g. 'Beta', 'Alpha', 'Preview').\n"
                . "     * @since 1.2\n"
                . "     */\n"
                . "    public static function getVersionType() {\n"
                . "        return self::get()->_getVersionType();\n"
                . "    }\n"
                . "    /**\n"
                . "     * Checks if the system is configured or not.\n"
                . "     * This method is helpful in case the developer would like to create some\n"
                . "     * kind of a setup wizard for the web application.\n"
                . "     * @return boolean true if the system is configured.\n"
                . "     * @since 1.0\n"
                . "     */\n"
                . "    public static function isConfig() {\n"
                . "        return self::get()->_isConfig();\n"
                . "    }\n"
                . "    private function _getConfigVersion() {\n"
                . "        return \$this->configVision;\n"
                . "    }\n"
                . "    private function _getReleaseDate() {\n"
                . "        return \$this->releaseDate;\n"
                . "    }\n"
                . "    \n"
                . "    private function _getVersion() {\n"
                . "        return \$this->version;\n"
                . "    }\n"
                . "    private function _getVersionType() {\n"
                . "        return \$this->versionType;\n"
                . "    }\n"
                . "    private function _isConfig() {\n"
                . "        return \$this->isConfigured;\n"
                . "    }\n"
                . "}\n"
                . "";


        
        $mailConfigFile = new File('Config.php', ROOT_DIR.DS.'conf');
        $mailConfigFile->remove();
        $mailConfigFile->setRawData($fileAsStr);
        $mailConfigFile->write(false, true);
    }
}
