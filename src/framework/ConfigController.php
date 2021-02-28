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
namespace webfiori\framework;

use webfiori\conf\Config;
use webfiori\conf\MailConfig;
use webfiori\database\ConnectionInfo;
use webfiori\framework\exceptions\SMTPException;
use webfiori\framework\mail\SMTPAccount;
use webfiori\framework\mail\SocketMailer;
use webfiori\theme\WebFioriV108;
use app\AppConfig;
/**
 * A class that can be used to modify basic configuration settings of 
 * the web application. 
 *
 * @author Ibrahim
 * 
 * @version 1.4.4
 */
class ConfigController {
    /**
     * A constant that indicates the selected database schema has tables.
     * 
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
     * </ul>
     * @since 1.0
     */
    const INITIAL_CONFIG_VARS = [
        'release-date' => '2020-01-14',
        'version' => '2.0.0',
        'version-type' => 'Stable',
        'config-file-version' => '1.3.5',
    ];
    const DEFAULT_APP_CONFIG = [
        'config-file-version' => '1.0',
        'version' => [
            'v' => '1.0',
            'v-type' => 'Stable',
            'release-date' => '2021-01-10'
        ],
        'site' => [
            'base-url' => '',
            'primary-language' => 'EN',
            'title-separator' => ' | ',
            'home-page' => 'index',
            'admin-theme-name' => WebFioriV108::class,
            'theme-name' => WebFioriV108::class,
            'site-descriptions' => [
                'EN' => '',
                'AR' => ''
            ],
            'website-names' => [
                'EN' => 'WebFiori',
                'AR' => 'ويب فيوري'
            ],
            'titles' => [
                'EN' => 'Hello World',
                'AR' => 'اهلا و سهلا'
            ],
        ]
    ];
    /**
     * A constant that indicates the given username or password  
     * is invalid.
     * 
     * @since 1.1
     */
    const INV_CREDENTIALS = 'inv_username_or_pass';
    /**
     * A constant that indicates a mail server address or its port 
     * is invalid.
     * 
     * @since 1.1
     */
    const INV_HOST_OR_PORT = 'inv_mail_host_or_port';

    /**
     * A constant that indicates the file MailConfig.php was not found.
     * 
     * @since 1.2
     */
    const MAIL_CONFIG_MISSING = 'mail_config_file_missing';
    const NL = "\n";
    /**
     * A constant that indicates the file SiteConfig.php was not found.
     * 
     * @since 1.2
     */
    const SITE_CONFIG_MISSING = 'site_config_file_missing';
    /**
     * A constant that indicates the file Config.php was not found.
     * 
     * @since 1.2
     */
    const SYS_CONFIG_MISSING = 'config_file_missing';
    /**
     * An instance of the class.
     * 
     * @var ConfigController
     * 
     * @since 1.0 
     */
    private static $singleton;
    /**
     * Adds new database connections information or update existing connections.
     * 
     * The information of the connections will be stored in the file 'Config.php'.
     * 
     * @param array $dbConnectionsInfo An array that contains objects of type ConnectionInfo. 
     * 
     * @since 1.4.3
     */
    public function addOrUpdateDBConnections($dbConnectionsInfo) {
        if (gettype($dbConnectionsInfo) == 'array') {
            $confVars = $this->getConfigVars();

            foreach ($dbConnectionsInfo as $con) {
                if ($con instanceof ConnectionInfo && strlen($con->getHost()) > 0 && 
                    strlen($con->getPort()) > 0 &&
                    strlen($con->getUsername()) > 0 && 
                    strlen($con->getPassword()) > 0 && 
                    strlen($con->getDBName()) > 0) {
                    $confVars['databases'][$con->getName()] = $con;
                }
            }
            $this->writeConfig($confVars);
        }
    }
    public function createAppConfigFile() {
        if (!class_exists('app\AppConfig')) {
            $this->writeAppConfig([]);
        }
    }
    /**
     * Creates the file 'Config.php' if it does not exist.
     * 
     * @since 1.0
     */
    public function createConfigFile() {
        if (!class_exists('webfiori\conf\Config')) {
            $cfg = $this->getConfigVars();
            $this->writeConfig($cfg);
        }
    }
    /**
     * Creates the file 'MailConfig.php' if it does not exist.
     * 
     * @since 1.0
     */
    public function createEmailConfigFile() {
        if (!class_exists('webfiori\conf\MailConfig')) {
            $this->writeMailConfig([]);
        }
    }
    /**
     * Creates the file 'SiteConfig.php' if it does not exist.
     * 
     * @since 1.0
     */
    public function createSiteConfigFile() {
        if (!class_exists('webfiori\conf\SiteConfig')) {
            $initCfg = $this->getSiteConfigVars();
            $this->writeSiteConfig($initCfg);
        }
    }
    /**
     * Returns a single instance of the class.
     * 
     * @return ConfigController
     * 
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
     * 
     * The array that will be returned will have the following information: 
     * <ul>
     * <li>release-date: The release date of WebFiori Framework.</li>
     * <li>version: Version number of WebFiori Framework.</li>
     * <li>version-type: Type of WebFiori Framework version.</li>
     * <li>config-file-version: Configuration file version number.</li>
     * <li>databases: A sub associative array that contains multiple 
     * database connections information. The key will be the name of the database 
     * and the value is an object of type ConnectionInfo.</li>
     * </ul>
     * 
     * @return array An associative array that contains system configuration 
     * info.
     * 
     * @since 1.0
     */
    public function getConfigVars() {
        $cfgArr = ConfigController::INITIAL_CONFIG_VARS;

        if (class_exists('webfiori\conf\Config')) {
            $cfgArr['databases'] = Config::getDBConnections();
        }

        return $cfgArr;
    }
    /**
     * Returns an associative array that contains web site configuration 
     * info.
     * 
     * The returned array will have the following indices: 
     * <ul>
     * <li><b>website-names</b>: A sub associative array. The index of the 
     * array will be language code (such as 'EN') and the value 
     * will be the name of the web site in the given language.</li>
     * <li><b>base-url</b>: The URL at which system pages will be served from. 
     * usually, this URL is used in the tag 'base' of the web page.</li>
     * <li><b>title-separator</b>: A character or a string that is used 
     * to separate web site name from web page title.</li>
     * <li><b>home-page</b>: The URL of the home page of the web site.</li>
     * <li><b>theme-name</b>: The name of the theme that will be used to style 
     * web site UI.</li>
     * <li><b>primary-language</b>: Primary language of the website.
     * <li><b>admin-theme-name</b>: The name of the theme that is used to style 
     * admin web pages.</li>
     * <li><b>site-descriptions</b>: A sub associative array. The index of the 
     * array will be language code (such as 'EN') and the value 
     * will be the general web site description in the given language.</li></li>
     * </ul> 
     * @return array An associative array that contains web site configuration 
     * info.
     * 
     * @since 1.0
     */
    public function getSiteConfigVars() {
        $cfgArr = self::INITIAL_WEBSITE_CONFIG_VARS;

        if (class_exists('webfiori\conf\SiteConfig')) {
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
     * Returns a new instance of the class SocketMailer.
     * 
     * The method will try to establish a connection to SMTP server using 
     * the given SMTP account.
     * 
     * @param SMTPAccount $emailAcc An account that is used to initiate 
     * socket mailer.
     * 
     * @return SocketMailer|string The method will return an instance of SocketMailer
     * on successful connection. If no connection is established, the method will 
     * return MailFunctions::INV_HOST_OR_PORT. If user authentication fails, 
     * the method will return 'MailFunctions::INV_CREDENTIALS'.
     * 
     * @since 1.0
     */
    public function getSocketMailer($emailAcc) {
        if ($emailAcc instanceof SMTPAccount) {
            $retVal = self::INV_HOST_OR_PORT;
            $m = new SocketMailer();

            $m->setHost($emailAcc->getServerAddress());
            $m->setPort($emailAcc->getPort());

            if ($m->connect()) {
                try {
                    $m->setSender($emailAcc->getSenderName(), $emailAcc->getAddress());

                    if ($m->login($emailAcc->getUsername(), $emailAcc->getPassword())) {
                        $retVal = $m;
                    } else {
                        $retVal = self::INV_CREDENTIALS;
                    }
                } catch (\Exception $ex) {
                    throw new SMTPException($ex->getMessage());
                }
            }

            return $retVal;
        }

        return false;
    }
    /**
     * Removes SMTP email account if it is exist.
     * 
     * @param string $accountName The name of the email account (such as 'no-reply').
     * 
     * @return boolean If the account is not exist or the class 'MailConfig' 
     * does not exist, the method will return false. If the account was removed, 
     * The method will return true.
     * 
     * @since 1.3
     */
    public function removeAccount($accountName) {
        $retVal = false;

        if (class_exists('webfiori\conf\MailConfig')) {
            $account = MailConfig::getAccount($accountName);

            if ($account instanceof SMTPAccount) {
                $accountsArr = MailConfig::getAccounts();
                unset($accountsArr[$accountName]);
                $toSave = [];

                foreach ($accountsArr as $account) {
                    $toSave[] = $account;
                }
                $this->writeMailConfig($toSave);
                $retVal = true;
            }
        }

        return $retVal;
    }
    /**
     * Removes a set of database connections.
     * 
     * This method will search for a connection which has the given database 
     * name. Once it found, it will remove the connection and save the updated 
     * information to the file 'Config.php'.
     * 
     * @param array $connectionsNames An array that contains the names of database connections.
     * 
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
     * Adds new SMTP account or Updates an existing one.
     * 
     * Note that the connection will be added or updated only if it 
     * has correct information.
     * 
     * @param SMTPAccount $emailAccount An instance of 'SMTPAccount'.
     * 
     * @return boolean|string The method will return true if the email 
     * account was updated or added. If the email account contains wrong server
     *  information, the method will return MailFunctions::INV_HOST_OR_PORT. 
     * If the given email account contains wrong login info, the method will 
     * return MailFunctions::INV_CREDENTIALS. Other than that, the method 
     * will return false.
     * 
     * @since 1.1
     */
    public function updateOrAddEmailAccount($emailAccount) {
        $retVal = false;

        if ($emailAccount instanceof SMTPAccount) {
            $sm = $this->getSocketMailer($emailAccount);

            if ($sm instanceof SocketMailer) {
                if (class_exists('webfiori\conf\MailConfig')) {
                    $accountsArr = MailConfig::getAccounts();
                    $accountsArr[$emailAccount->getSenderName()] = $emailAccount;
                    $toSave = [];

                    foreach ($accountsArr as $account) {
                        $toSave[] = $account;
                    }
                    $this->writeMailConfig($toSave);
                } else {
                    $arr = [$emailAccount];
                    $this->writeMailConfig($arr);
                }
                $retVal = true;
            }
            $retVal = $sm;
        }

        return $retVal;
    }
    /**
     * Updates web site configuration based on some attributes.
     * 
     * @param array $websiteInfoArr an associative array. The array can 
     * have the following indices: 
     * <ul>
     * <li><b>primary-language</b>: The main display language of the website.
     * <li><b>website-names</b>: A sub associative array. The index of the 
     * array should be language code (such as 'EN') and the value 
     * should be the name of the web site in the given language.</li>
     * <li><b>title-separator</b>: A character or a string that is used 
     * to separate web site name from web page title. Two common 
     * values are '-' and '|'.</li>
     * <li><b>home-page</b>: The URL of the home page of the web site. For example, 
     * If root URL of the web site is 'https://www.example.com', This page is served 
     * when the user visits this URL.</li>
     * <li><b>theme-name</b>: The name of the theme that will be used to style 
     * web site UI.</li>
     * <li><b>admin-theme-name</b>: If the web site has two UIs (One for normal 
     * users and another for admins), this one 
     * can be used to serve the UI for web site admins.</li>
     * <li><b>site-descriptions</b>: A sub associative array. The index of the 
     * array should be language code (such as 'EN') and the value 
     * should be the general web site description in the given language.</li></li>
     * </ul> 
     * 
     * @since 1.0
     */
    public function updateSiteInfo($websiteInfoArr) {
        $confArr = $this->getSiteConfigVars();

        foreach ($confArr as $k => $v) {
            if (isset($websiteInfoArr[$k])) {
                $confArr[$k] = $websiteInfoArr[$k];
            }
        }
        $this->writeSiteConfig($confArr);
    }
    /**
     * A method to save changes to configuration file.
     * 
     * @param array $configArr An array that contains system configuration 
     * variables.
     * 
     * @since 1.0
     */
    private function writeConfig($configArr) {
        $fileAsStr = "<?php".self::NL
                ."namespace webfiori\conf;".self::NL
                ."".self::NL
                ."use webfiori\\database\ConnectionInfo;".self::NL
                ."/**".self::NL
                ." * Global configuration class.".self::NL
                ." * ".self::NL
                ." * Used by the server part and the presentation part. It contains framework version".self::NL
                ." * information and database connection settings.".self::NL
                ." * ".self::NL
                ." * @author Ibrahim".self::NL
                ." * ".self::NL
                ." * @version 1.3.5".self::NL
                ." */".self::NL
                ."class Config {".self::NL
                ."    /**".self::NL
                ."     * An instance of Config.".self::NL
                ."     * ".self::NL
                ."     * @var Config".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    private static \$cfg;".self::NL
                ."    /**".self::NL
                ."     * An associative array that will contain database connections.".self::NL
                ."     * ".self::NL
                ."     * @var type".self::NL
                ."     */".self::NL
                ."    private \$dbConnections;".self::NL
                ."    /**".self::NL
                ."     * The release date of the framework that is used to build the system.".self::NL
                ."     * ".self::NL
                ."     * @var string Release date of of the framework that is used to build the system.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    private \$releaseDate;".self::NL
                ."    /**".self::NL
                ."     * The version of the framework that is used to build the system.".self::NL
                ."     * ".self::NL
                ."     * @var string The version of the framework that is used to build the system.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    private \$version;".self::NL
                ."    /**".self::NL
                ."     * The type framework version that is used to build the system.".self::NL
                ."     * ".self::NL
                ."     * @var string The framework version that is used to build the system.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    private \$versionType;".self::NL
                ."    ".self::NL
                ."    /**".self::NL
                ."     * Initialize configuration.".self::NL
                ."     */".self::NL
                ."    private function __construct() {".self::NL
                ."        \$this->releaseDate = '".$configArr['release-date']."';".self::NL
                ."        \$this->version = '".$configArr['version']."';".self::NL
                ."        \$this->versionType = '".$configArr['version-type']."';".self::NL
                ."        \$this->configVision = '".$configArr['config-file-version']."';".self::NL
                ."        \$this->dbConnections = [".self::NL
                ."";
        $count = count($configArr['databases']);
        $i = 0;

        foreach ($configArr['databases'] as $dbConn) {
            if ($i + 1 == $count) {
                $fileAsStr .= "            '".$dbConn->getName()."' => new ConnectionInfo("
                        ."'".$dbConn->getDatabaseType()."', "
                        ."'".$dbConn->getUsername()."', "
                        ."'".$dbConn->getPassword()."', "
                        ."'".$dbConn->getDBName()."', "
                        ."'".$dbConn->getHost()."', "
                        ."".$dbConn->getPort().")";
            } else {
                $fileAsStr .= "            '".$dbConn->getName()."' => new ConnectionInfo("
                        ."'".$dbConn->getDatabaseType()."', "
                        ."'".$dbConn->getUsername()."', "
                        ."'".$dbConn->getPassword()."', "
                        ."'".$dbConn->getDBName()."', "
                        ."'".$dbConn->getHost()."', "
                        ."".$dbConn->getPort()."),".self::NL;
            }
            $i++;
        }
        $fileAsStr .= "".self::NL
                   ."        ];".self::NL;

        foreach ($configArr['databases'] as $dbConn) {
            $fileAsStr .= '        $this->dbConnections[\''.$dbConn->getName().'\']->setName(\''.$dbConn->getName().'\');'."".self::NL;
        }
        $fileAsStr .= ""
                ."    }".self::NL
                ."    /**".self::NL
                ."     * Adds new database connection or updates an existing one.".self::NL
                ."     * ".self::NL
                ."     * @param ConnectionInfo \$connectionInfo an object of type 'ConnectionInfo'".self::NL
                ."     * that will contain connection information.".self::NL
                ."     * ".self::NL
                ."     * @since 1.3.4".self::NL
                ."     */".self::NL
                ."    public static function addDbConnection(\$connectionInfo) {".self::NL
                ."        if (\$connectionInfo instanceof ConnectionInfo) {".self::NL
                ."            self::get()->dbConnections[\$connectionInfo->getName()] = \$connectionInfo;".self::NL
                ."        }".self::NL
                ."    }".self::NL
                ."    /**".self::NL
                ."     * Returns an object that can be used to access configuration information.".self::NL
                ."     * ".self::NL
                ."     * @return Config An object of type Config.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    public static function get() {".self::NL
                ."        if (self::\$cfg != null) {".self::NL
                ."            return self::\$cfg;".self::NL
                ."        }".self::NL
                ."        self::\$cfg = new Config();".self::NL
                ."        ".self::NL
                ."        return self::\$cfg;".self::NL
                ."    }".self::NL
                ."    /**".self::NL
                ."     * Returns the version number of configuration file.".self::NL
                ."     * ".self::NL
                ."     * The value is used to check for configuration compatibility since the".self::NL
                ."     * framework is updated and more features are added.".self::NL
                ."     * ".self::NL
                ."     * @return string The version number of configuration file.".self::NL
                ."     * ".self::NL
                ."     * @since 1.2".self::NL
                ."     */".self::NL
                ."    public static function getConfigVersion() {".self::NL
                ."        return self::get()->_getConfigVersion();".self::NL
                ."    }".self::NL
                ."    /**".self::NL
                ."     * Returns database connection information given connection name.".self::NL
                ."     * ".self::NL
                ."     * @param string \$conName The name of the connection.".self::NL
                ."     * ".self::NL
                ."     * @return ConnectionInfo|null The method will return an object of type".self::NL
                ."     * ConnectionInfo if a connection info was found for the given connection name.".self::NL
                ."     * Other than that, the method will return null.".self::NL
                ."     * ".self::NL
                ."     * @since 1.3.3".self::NL
                ."     */".self::NL
                ."    public static function getDBConnection(\$conName) {".self::NL
                ."        \$conns = self::getDBConnections();".self::NL
                ."        \$trimmed = trim(\$conName);".self::NL
                ."        ".self::NL
                ."        if (isset(\$conns[\$trimmed])) {".self::NL
                ."            return \$conns[\$trimmed];".self::NL
                ."        }".self::NL
                ."        ".self::NL
                ."        return null;".self::NL
                ."    }".self::NL
                ."    /**".self::NL
                ."     * Returns an associative array that contain the information of database connections.".self::NL
                ."     * ".self::NL
                ."     * The keys of the array will be the name of database connection and the value of".self::NL
                ."     * each key will be an object of type ConnectionInfo.".self::NL
                ."     * ".self::NL
                ."     * @return array An associative array.".self::NL
                ."     * ".self::NL
                ."     * @since 1.3.3".self::NL
                ."     */".self::NL
                ."    public static function getDBConnections() {".self::NL
                ."        return self::get()->dbConnections;".self::NL
                ."    }".self::NL
                ."    /**".self::NL
                ."     * Returns the date at which the current version of the framework is released.".self::NL
                ."     * ".self::NL
                ."     * The format of the date will be YYYY-MM-DD.".self::NL
                ."     * ".self::NL
                ."     * @return string The date at which the current version of the framework is released.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    public static function getReleaseDate() {".self::NL
                ."        return self::get()->_getReleaseDate();".self::NL
                ."    }".self::NL
                ."    /**".self::NL
                ."     * Returns WebFiori Framework version number.".self::NL
                ."     * ".self::NL
                ."     * @return string WebFiori Framework version number. The version number will".self::NL
                ."     * have the following format: x.x.x".self::NL
                ."     * ".self::NL
                ."     * @since 1.2".self::NL
                ."     */".self::NL
                ."    public static function getVersion() {".self::NL
                ."        return self::get()->_getVersion();".self::NL
                ."    }".self::NL
                ."    /**".self::NL
                ."     * Returns WebFiori Framework version type.".self::NL
                ."     * ".self::NL
                ."     * @return string WebFiori Framework version type (e.g. 'Beta', 'Alpha', 'Preview').".self::NL
                ."     * ".self::NL
                ."     * @since 1.2".self::NL
                ."     */".self::NL
                ."    public static function getVersionType() {".self::NL
                ."        return self::get()->_getVersionType();".self::NL
                ."    }".self::NL
                ."    /**".self::NL
                ."     * Checks if the system is configured or not.".self::NL
                ."     * ".self::NL
                ."     * This method is helpful in case the developer would like to create some".self::NL
                ."     * kind of a setup wizard for the web application.".self::NL
                ."     * ".self::NL
                ."     * @return boolean true if the system is configured.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    private function _getConfigVersion() {".self::NL
                ."        return \$this->configVision;".self::NL
                ."    }".self::NL
                ."    private function _getReleaseDate() {".self::NL
                ."        return \$this->releaseDate;".self::NL
                ."    }".self::NL
                ."    ".self::NL
                ."    private function _getVersion() {".self::NL
                ."        return \$this->version;".self::NL
                ."    }".self::NL
                ."    private function _getVersionType() {".self::NL
                ."        return \$this->versionType;".self::NL
                ."    }".self::NL
                ."".self::NL
                ."}".self::NL
                ."";

        $mailConfigFile = new File('Config.php', ROOT_DIR.DS.'conf');
        $mailConfigFile->remove();
        $mailConfigFile->setRawData($fileAsStr);
        $mailConfigFile->write(false, true);
    }
    public function writeAppConfig($appConfigArr) {
        $cFile = new File('AppConfig.php', ROOT_DIR.DS.'app');
        $cFile->remove();
        $this->a($cFile, "<?php");
        $this->a($cFile, "");
        $this->a($cFile, "use webfiori\database\ConnectionInfo;");
        $this->a($cFile, "use webfiori\framework\mail\SMTPAccount;");
        $this->a($cFile, "use webfiori\http\Uri;");
        $this->a($cFile, "/**");
        $this->a($cFile, " * Configuration class of the application");
        $this->a($cFile, " *");
        $this->a($cFile, " * @author Ibrahim");
        $this->a($cFile, " *");
        $this->a($cFile, " * @version 1.0");
        $this->a($cFile, " *");
        $this->a($cFile, " * @since 2.1.0");
        $this->a($cFile, " */");
        $this->a($cFile, "class AppConfig {");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * The name of admin control pages Theme.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var string");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$adminThemeName;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * The date at which the application was released.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var string");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$appReleaseDate;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * A string that represents the type of the release.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var string");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$appVersionType;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Version of the web application.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var string");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$appVestion;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * The name of base website UI Theme.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var string");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$baseThemeName;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * The base URL that is used by all web site pages to fetch resource files.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var string");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$baseUrl;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Configuration file version number.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var string");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$configVision;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * An associative array that will contain database connections.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var array");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$dbConnections;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * An array that is used to hold default page titles for different languages.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var array");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$defaultPageTitles;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * An array that holds SMTP connections information.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var string");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$emailAccounts;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * The URL of the home page.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var string");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$homePage;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * The primary language of the website.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var string");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$primaryLang;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * The character which is used to saperate site name from page title.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var string");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$titleSep;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * An array which contains all website names in different languages.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @var string");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private \$webSiteNames;");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Creates new instance of the class.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function __construct() {");
        $this->a($cFile, "        \$this->configVision = '1.0.0';");
        $this->a($cFile, "        \$this->initVersionInfo();");
        $this->a($cFile, "        \$this->initSiteInfo();");
        $this->a($cFile, "        \$this->initDbConnections();");
        $this->a($cFile, "        \$this->initSmtpConnections();");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Adds an email account.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * The developer can use this method to add new account during runtime.");
        $this->a($cFile, "     * The account will be removed once the program finishes.");
        $this->a($cFile, "     * "); 
        $this->a($cFile, "     * @param SMTPAccount \$acc an object of type SMTPAccount."); 
        $this->a($cFile, "     * "); 
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "     public function addAccount(SMTPAccount \$acc) {");
        $this->a($cFile, "        \$this->emailAccounts[\$acc->getAccountName()] = \$acc;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Adds new database connection or updates an existing one.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @param ConnectionInfo \$connectionInfo an object of type 'ConnectionInfo'");
        $this->a($cFile, "     * that will contain connection information.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function addDbConnection(ConnectionInfo \$connectionInfo) {");
        $this->a($cFile, "        \$this->dbConnections[\$connectionInfo->getName()] = \$connectionInfo;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns SMTP account given its name.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * The method will search for an account with the given name in the set");
        $this->a($cFile, "     * of added accounts. If no account was found, null is returned.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @param string \$name The name of the account.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return SMTPAccount|null If the account is found, The method");
        $this->a($cFile, "     * will return an object of type SMTPAccount. Else, the");
        $this->a($cFile, "     * method will return null.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getAccount(\$name) {");
        $this->a($cFile, "        if (isset(\$this->emailAccounts[\$name])) {");
        $this->a($cFile, "            return \$this->emailAccounts[\$name];");
        $this->a($cFile, "        }");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns an associative array that contains all email accounts.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * The indices of the array will act as the names of the accounts.");
        $this->a($cFile, "     * The value of the index will be an object of type SMTPAccount.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return array An associative array that contains all email accounts.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getAccounts() {");
        $this->a($cFile, "        return \$this->emailAccounts;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns the name of the theme that is used in admin control pages.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string The name of the theme that is used in admin control pages.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getAdminThemeName() {");
        $this->a($cFile, "        return \$this->adminThemeName;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns the name of base theme that is used in website pages.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * Usually, this theme is used for the normally visitors of the web site.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string The name of base theme that is used in website pages.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getBaseThemeName() {");
        $this->a($cFile, "        return \$this->baseThemeName;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns the base URL that is used to fetch resources.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * The return value of this method is usually used by the tag 'base'");
        $this->a($cFile, "     * of web site pages.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string the base URL.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getBaseURL() {");
        $this->a($cFile, "        return \$this->baseUrl;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns version number of the configuration file.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * This value can be used to check for the compatability of configuration file");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string The version number of the configuration file.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getConfigVersion() {");
        $this->a($cFile, "        return \$this->configVision;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns database connection information given connection name.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @param string \$conName The name of the connection.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return ConnectionInfo|null The method will return an object of type");
        $this->a($cFile, "     * ConnectionInfo if a connection info was found for the given connection name.");
        $this->a($cFile, "     * Other than that, the method will return null.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getDBConnection(\$conName) {");
        $this->a($cFile, "        \$conns = \$this->getDBConnections();");
        $this->a($cFile, "        \$trimmed = trim(\$conName);");
        $this->a($cFile, "        ");
        $this->a($cFile, "        if (isset(\$conns[\$trimmed])) {");
        $this->a($cFile, "            return \$conns[\$trimmed];");
        $this->a($cFile, "        }");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns an associative array that contain the information of database connections.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * The keys of the array will be the name of database connection and the");
        $this->a($cFile, "     * value of each key will be an object of type ConnectionInfo.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return array An associative array.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getDBConnections() {");
        $this->a($cFile, "        return \$this->dbConnections;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns the global title of the web site that will be");
        $this->a($cFile, "     * used as default page title.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @param string \$langCode Language code such as 'AR' or 'EN'.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string|null If the title of the page");
        $this->a($cFile, "     * does exist in the given language, the method will return it.");
        $this->a($cFile, "     * If no such title, the method will return null.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getDefaultTitle(\$langCode) {");
        $this->a($cFile, "        \$langs = \$this->getDefaultTitles();");
        $this->a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        $this->a($cFile, "        ");
        $this->a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        $this->a($cFile, "            return \$langs[\$langCode];");
        $this->a($cFile, "        }");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns an array that holds the default pages titles for different languages.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return array The indices of the array will be languages codes such as");
        $this->a($cFile, "     * 'AR' and the value at each index will be page title in that language.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getDefaultTitles() {");
        $this->a($cFile, "        return \$this->defaultPageTitles;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns the global description of the web site that will be");
        $this->a($cFile, "     * used as default page description.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @param string \$langCode Language code such as 'AR' or 'EN'.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string|null If the description for the given language");
        $this->a($cFile, "     * does exist, the method will return it. If no such description, the");
        $this->a($cFile, "     * method will return null.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getDescription(\$langCode) {");
        $this->a($cFile, "        \$langs = \$this->getDescriptions();");
        $this->a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        $this->a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        $this->a($cFile, "            return \$langs[\$langCode];");
        $this->a($cFile, "        }");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns an associative array which contains different website descriptions");
        $this->a($cFile, "     * in different languages.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * Each index will contain a language code and the value will be the description");
        $this->a($cFile, "     * of the website in the given language.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return array An associative array which contains different website descriptions");
        $this->a($cFile, "     * in different languages.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getDescriptions() {");
        $this->a($cFile, "        return \$this->descriptions;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns the home page URL of the website.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string The home page URL of the website.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getHomePage() {");
        $this->a($cFile, "        return \$this->homePage;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns the primary language of the website.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string Language code of the primary language such as 'EN'.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getPrimaryLanguage() {");
        $this->a($cFile, "        return \$this->primaryLang;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns the date at which the application was released at.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string The method will return a string in the format");
        $this->a($cFile, "     * 'YYYY-MM-DD' that represents application release date.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getReleaseDate() {");
        $this->a($cFile, "        return \$this->appReleaseDate;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns the character (or string) that is used to separate page title from website name.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string A string such as ' - ' or ' | '. Note that the method");
        $this->a($cFile, "     * will add the two spaces by default.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getTitleSep() {");
        $this->a($cFile, "        return \$this->titleSep;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns version number of the application.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string The method should return a string in the");
        $this->a($cFile, "     * form 'x.x.x.x'.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getVersion() {");
        $this->a($cFile, "        return \$this->appVestion;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns a string that represents application release type.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string The method will return a string such as");
        $this->a($cFile, "     * 'Stable', 'Alpha', 'Beta' and so on.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getVersionType() {");
        $this->a($cFile, "        return \$this->appVersionType;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns the global website name.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @param string \$langCode Language code such as 'AR' or 'EN'.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return string|null If the name of the website for the given language");
        $this->a($cFile, "     * does exist, the method will return it. If no such name, the");
        $this->a($cFile, "     * method will return null.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getWebsiteName(\$langCode) {");
        $this->a($cFile, "        \$langs = \$this->getWebsiteNames();");
        $this->a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        $this->a($cFile, "        ");
        $this->a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        $this->a($cFile, "            return \$langs[\$langCode];");
        $this->a($cFile, "        }");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * Returns an array which contains different website names in different languages.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * Each index will contain a language code and the value will be the name");
        $this->a($cFile, "     * of the website in the given language.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @return array An array which contains different website names in different languages.");
        $this->a($cFile, "     * ");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    public function getWebsiteNames() {");
        $this->a($cFile, "        return \$this->webSiteNames;");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private function initDbConnections() {");
        $this->a($cFile, "        \$this->dbConnections = [");
        
        if (isset($appConfigArr['db-connections']) && gettype($appConfigArr['db-connections']) == 'array') {
            $dbCons = $appConfigArr['db-connections'];
        } else {
            $dbCons = $this->getDatabaseConnections();
        }
        foreach ($dbCons as $connObj) {
            if ($connObj instanceof ConnectionInfo) {
                $cName = $connObj->getName();
                $this->a($cFile, "        '$cName' => new ConnectionInfo('".$connObj->getDatabaseType()."',"
                        . "'".$connObj->getUsername()."',"
                        . "'".$connObj->getPassword()."',"
                        . "'".$connObj->getDBName()."',"
                        . "'".$connObj->getHost()."',"
                        . "".$connObj->getPort().",");
                $this->a($cFile, "        ['connection-name' => ".$cName."])");
            }
        }
        $this->a($cFile, "        ];");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private function initSiteInfo() {");
        
        if (isset($appConfigArr['website-names']) && gettype($appConfigArr['website-names']) == 'array') {
            $wNamesArr = $appConfigArr['website-names'];
        } else {
            $wNamesArr = $this->getWebsiteNames();
        }
        $this->a($cFile, "        \$this->webSiteNames = [");
        foreach ($wNamesArr as $langCode => $name) {
            $desc = str_replace("'", "\'", $name);
            $this->a($cFile, "            '$langCode' => '$name',");
        }
        $this->a($cFile, "        ];");
        $this->a($cFile, "    ");
        
        if (isset($appConfigArr['titles']) && gettype($appConfigArr['titles']) == 'array') {
            $titlesArr = $appConfigArr['titles'];
        } else {
            $titlesArr = $this->getTitles();
        }
        $this->a($cFile, "        \$this->defaultPageTitles = [");
        foreach ($wNamesArr as $langCode => $title) {
            $desc = str_replace("'", "\'", $title);
            $this->a($cFile, "            '$langCode' => '$title',");
        }
        $this->a($cFile, "        ];");
        
        if (isset($appConfigArr['descriptions']) && gettype($appConfigArr['descriptions']) == 'array') {
            $descArr = $appConfigArr['descriptions'];
        } else {
            $descArr = $this->getDescriptions();
        }
        $this->a($cFile, "        \$this->descriptions = [");
        foreach ($wNamesArr as $langCode => $desc) {
            $desc = str_replace("'", "\'", $desc);
            $this->a($cFile, "            '$langCode' => '$desc',");
        }
        $this->a($cFile, "        ];");
        
        $this->a($cFile, "        \$this->baseUrl = Uri::getBaseURL();");
        
        if (isset($appConfigArr['title-sep'])) {
            $sep = $appConfigArr['title-sep'];
        } else {
            $sep = $this->getTitleSep();
        }
        $this->a($cFile, "        \$this->titleSep = '$sep';");
        
        if (isset($appConfigArr['primary-lang'])) {
            $lang = $appConfigArr['primary-lang'];
        } else {
            $lang = $this->getPrimaryLang();
        }
        $this->a($cFile, "        \$this->primaryLang = '$lang';");
        
        if (isset($appConfigArr['base-theme'])) {
            $baseTheme = $appConfigArr['base-theme'];
        } else {
            $baseTheme = $this->getBaseTheme();
        }
        if (class_exists($baseTheme)) {
            $this->a($cFile, "        \$this->baseThemeName = $baseTheme;");
        } else {
            $this->a($cFile, "        \$this->baseThemeName = '$baseTheme';");
        }
        
        if (isset($appConfigArr['admin-theme'])) {
            $adminTheme = $appConfigArr['admin-theme'];
        } else {
            $adminTheme = $this->getBaseTheme();
        }
        if (class_exists($adminTheme)) {
            $this->a($cFile, "        \$this->adminThemeName = $adminTheme;");
        } else {
            $this->a($cFile, "        \$this->adminThemeName = '$adminTheme';");
        }
        if (isset($appConfigArr['home-page'])) {
            $this->a($cFile, "        \$this->homePage = '".$appConfigArr['home-page']."';");
        } else {
            $home = $this->getHomePage();
            if ($home === null) {
                $this->a($cFile, "        \$this->homePage = Uri::getBaseURL();");
            } else {
                $this->a($cFile, "        \$this->homePage = '$home';");
            }
        }
        
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private function initSmtpConnections() {");
        $this->a($cFile, "        \$this->emailAccounts = [");
        if (isset($appConfigArr['smtp']) && gettype($appConfigArr['smtp']) == 'array') {
            $smtpAccArr = $appConfigArr['smtp'];
        } else {
            $smtpAccArr = $this->getSMTPAccounts();
        }
        foreach ($smtpAccArr as $smtpAcc) {
            if ($smtpAcc instanceof SMTPAccount) {
                $this->a($cFile, "            '".$smtpAcc->getAccountName()."' => new SMTPAccount([");
                $this->a($cFile, "                'port' => ".$smtpAcc->getPort().",");
                $this->a($cFile, "                'user' => ".$smtpAcc->getUsername().",");
                $this->a($cFile, "                'pass' => ".$smtpAcc->getPassword().",");
                $this->a($cFile, "                'sender-name' => ".$smtpAcc->getSenderName().",");
                $this->a($cFile, "                'sender-address' => ".$smtpAcc->getAddress().",");
                $this->a($cFile, "                'account-name' => ".$smtpAcc->getAccountName()."");
                $this->a($cFile, "            ]),");
            }
        }
        $this->a($cFile, "        ];");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "    /**");
        $this->a($cFile, "     * @since 1.0");
        $this->a($cFile, "     */");
        $this->a($cFile, "    private function initVersionInfo() {");
        $this->a($cFile, "        \$this->appVestion = '1.0';");
        $this->a($cFile, "        \$this->appVersionType = 'Stable';");
        $this->a($cFile, "        \$this->appReleaseDate = '2021-01-10';");
        $this->a($cFile, "    }");
        
        $this->a($cFile, "}");
    }
    public function getHomePage() {
        if (class_exists('app\\AppConfig')) {
            $c = new AppConfig();
            return $c->getHomePage();
        }
        return null;
    }
    public function getAdminTheme() {
        if (class_exists('app\\AppConfig')) {
            $c = new AppConfig();
            return $c->getAdminThemeName();
        }
        return self::DEFAULT_APP_CONFIG['site']['admin-theme-name'];
    }
    public function getBaseTheme() {
        if (class_exists('app\\AppConfig')) {
            $c = new AppConfig();
            return $c->getBaseThemeName();
        }
        return self::DEFAULT_APP_CONFIG['site']['theme-name'];
    }
    public function getPrimaryLang() {
        if (class_exists('app\\AppConfig')) {
            $c = new AppConfig();
            return $c->getPrimaryLanguage();
        }
        return self::DEFAULT_APP_CONFIG['site']['primary-language'];
    }
    public function getTitleSep() {
        if (class_exists('app\\AppConfig')) {
            $c = new AppConfig();
            return $c->getTitleSep();
        }
        return self::DEFAULT_APP_CONFIG['site']['title-separator'];
    }
    public function getDescriptions() {
        if (class_exists('app\\AppConfig')) {
            $c = new AppConfig();
            return $c->getDescriptions();
        }
        return self::DEFAULT_APP_CONFIG['site']['site-descriptions'];
    }
    public function getTitles() {
        if (class_exists('app\\AppConfig')) {
            $c = new AppConfig();
            return $c->getWebsiteNames();
        }
        return self::DEFAULT_APP_CONFIG['site']['titles'];
    }
    public function getWebsiteNames() {
        if (class_exists('app\\AppConfig')) {
            $c = new AppConfig();
            return $c->getWebsiteNames();
        }
        return self::DEFAULT_APP_CONFIG['site']['website-names'];
    }
    public function getDatabaseConnections() {
        if (class_exists('app\\AppConfig')) {
            $c = new AppConfig();
            return $c->getDBConnections();
        }
        return [];
    }
    public function getSMTPAccounts() {
        if (class_exists('app\\AppConfig')) {
            $c = new AppConfig();
            return $c->getAccounts();
        }
        return [];
    }
    private function a(File $file, $str) {
        $file->append($str.self::NL);
    }
    /**
     * A method to save changes to mail configuration file.
     * 
     * @param array $emailAccountsArr An associative array that contains an objects of 
     * type 'SMTPAccount'. The indices of the array are the names of the accounts.
     * 
     * @since 1.1
     */
    private function writeMailConfig($emailAccountsArr) {
        $fileData = ""
                ."<?php".self::NL
                ."namespace webfiori\\conf;".self::NL
                ."".self::NL
                ."use webfiori\\framework\\mail\\SMTPAccount;".self::NL
                ."/**".self::NL
                ." * SMTP configuration class.".self::NL
                ." * ".self::NL
                ." * The developer can create multiple SMTP accounts and add".self::NL
                ." * Connection information inside the body of this class.".self::NL
                ." * ".self::NL
                ." * @author Ibrahim".self::NL
                ." * ".self::NL
                ." * @version 1.0.1".self::NL
                ." */".self::NL
                ."class MailConfig {".self::NL
                ."    private \$emailAccounts;".self::NL
                ."    /**".self::NL
                ."     *".self::NL
                ."     * @var MailConfig".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    private static \$inst;".self::NL
                ."    private function __construct() {".self::NL
                ."        \$this->emailAccounts = [];".self::NL;
        $index = 0;

        foreach ($emailAccountsArr as $emailAcc) {
            $fileData .= ""
                    ."        \$acc$index = new SMTPAccount([".self::NL
                    ."            'server-address' => '".$emailAcc->getServerAddress()."',".self::NL
                    ."            'port' => ".$emailAcc->getPort().",".self::NL
                    ."            'user' => '".$emailAcc->getUsername()."',".self::NL
                    ."            'pass' => '".$emailAcc->getPassword()."',".self::NL
                    ."            'sender-name' => '".$emailAcc->getSenderName()."',".self::NL
                    ."            'sender-address' => '".$emailAcc->getAddress()."',".self::NL
                    ."            'account-name' => '".$emailAcc->getAccountName()."'".self::NL
                    ."        ]);".self::NL
                    ."        \$this->addAccount(\$acc$index, '".$emailAcc->getAccountName()."');".self::NL
                    ."        ".self::NL;
            $index++;
        }
        $fileData .= "    }".self::NL
                ."    /**".self::NL
                ."     * Adds new SMTP connection information or updates an existing one.".self::NL
                ."     * ".self::NL
                ."     * @param string \$accName The name of the account that will be added or updated.".self::NL
                ."     * ".self::NL
                ."     * @param SMTPAccount \$smtpConnInfo An object of type 'SMTPAccount' that".self::NL
                ."     * will contain SMTP account information.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0.1".self::NL
                ."     */".self::NL
                ."    public static function addSMTPAccount(\$accName, \$smtpConnInfo) {".self::NL
                ."        if (\$smtpConnInfo instanceof SMTPAccount) {".self::NL
                ."            \$trimmedName = trim(\$accName);".self::NL
                ."            ".self::NL
                ."            if (strlen(\$trimmedName) != 0) {".self::NL
                ."                self::get()->addAccount(\$smtpConnInfo, \$trimmedName);".self::NL
                ."            }".self::NL
                ."        }".self::NL
                ."    }".self::NL
                .""
                ."    /**".self::NL
                ."     * Return a single instance of the class.".self::NL
                ."     * ".self::NL
                ."     * Calling this method multiple times will result in returning".self::NL
                ."     * the same instance every time.".self::NL
                ."     * ".self::NL
                ."     * @return MailConfig".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    public static function get() {".self::NL
                ."        if (self::\$inst === null) {".self::NL
                ."            self::\$inst = new MailConfig();".self::NL
                ."        }".self::NL
                ."        ".self::NL
                ."        return self::\$inst;".self::NL
                ."    }".self::NL
                .""
                ."    /**".self::NL
                ."     * Returns an email account given its name.".self::NL
                ."     * ".self::NL
                ."     * The method will search for an account with the given name in the set".self::NL
                ."     * of added accounts. If no account was found, null is returned.v"
                ."     * ".self::NL
                ."     * @param string \$name The name of the account.".self::NL
                ."     * ".self::NL
                ."     * @return SMTPAccount|null If the account is found, The method".self::NL
                ."     * will return an object of type SMTPAccount. Else, the".self::NL
                ."     * method will return null.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    public static function getAccount(\$name) {".self::NL
                ."        return self::get()->_getAccount(\$name);".self::NL
                ."    }".self::NL
                .""
                ."    /**".self::NL
                ."     * Returns an associative array that contains all email accounts.".self::NL
                ."     * ".self::NL
                ."     * The indices of the array will act as the names of the accounts.".self::NL
                ."     * The value of the index will be an object of type EmailAccount.".self::NL
                ."     * ".self::NL
                ."     * @return array An associative array that contains all email accounts.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    public static function getAccounts() {".self::NL
                ."        return self::get()->_getAccounts();".self::NL
                ."    }".self::NL
                ."    private function _getAccount(\$name) {".self::NL
                ."        if (isset(\$this->emailAccounts[\$name])) {".self::NL
                ."            return \$this->emailAccounts[\$name];".self::NL
                ."        }".self::NL
                ."        ".self::NL
                ."        return null;".self::NL
                ."    }".self::NL
                ."    private function _getAccounts() {".self::NL
                ."        return \$this->emailAccounts;".self::NL
                ."    }".self::NL
                .""
                ."    /**".self::NL
                ."     * Adds an email account.".self::NL
                ."     * ".self::NL
                ."     * The developer can use this method to add new account during runtime.".self::NL
                ."     * The account will be removed once the program finishes.".self::NL
                ."     * ".self::NL
                ."     * @param SMTPAccount \$acc an object of type SMTPAccount.".self::NL
                ."     * ".self::NL
                ."     * @param string \$name A name to associate with the email account.".self::NL
                ."     * ".self::NL
                ."     * @since 1.0".self::NL
                ."     */".self::NL
                ."    private function addAccount(\$acc,\$name) {".self::NL
                ."        \$this->emailAccounts[\$name] = \$acc;".self::NL
                ."    }".self::NL;
        //End of class
        $fileData .= "}".self::NL;
        $mailConfigFile = new File('MailConfig.php', ROOT_DIR.DS.'conf');
        $mailConfigFile->remove();
        $mailConfigFile->setRawData($fileData);
        $mailConfigFile->write(false, true);
    }
    /**
     * A method to save changes to web site configuration file.
     * 
     * @param array $configArr An array that contains system configuration 
     * variables.
     * 
     * @since 1.0
     */
    private function writeSiteConfig($configArr) {
        $names = "[".self::NL;

        foreach ($configArr['website-names'] as $k => $v) {
            $names .= '            \''.$k.'\'=>\''.$v.'\','."".self::NL;
        }
        $names .= '        ]';
        $descriptions = "[".self::NL;

        foreach ($configArr['site-descriptions'] as $k => $v) {
            $descriptions .= '            \''.$k.'\'=>\''.$v.'\','."".self::NL;
        }
        $descriptions .= '        ]';

        $fileAsStr = "<?php".self::NL
            ."namespace webfiori\conf;".self::NL
            ."".self::NL
            ."use webfiori\\framework\Util;".self::NL
            ."/**".self::NL
            ."  * Website configuration class.".self::NL
            ."  * ".self::NL
            ."  * This class is used to control the following settings:".self::NL
            ."  * <ul>".self::NL
            ."  * <li>The base URL of the website.</li>".self::NL
            ."  * <li>The primary language of the website.</li>".self::NL
            ."  * <li>The name of the website in different languages.</li>".self::NL
            ."  * <li>The general description of the website in different languages.</li>".self::NL
            ."  * <li>The character that is used to separate the name of the website from page title.</li>".self::NL
            ."  * <li>The theme of the website.</li>".self::NL
            ."  * <li>Admin theme of the website (if uses one).</li>".self::NL
            ."  * <li>The home page of the website.</li>".self::NL
            ."  * </ul>".self::NL
            ."  */".self::NL
            ."class SiteConfig {".self::NL
            ."    /**".self::NL
            ."     * The name of admin control pages Theme.".self::NL
            ."     * ".self::NL
            ."     * @var string".self::NL
            ."     * ".self::NL
            ."     * @since 1.3".self::NL
            ."     */".self::NL
            ."    private \$adminThemeName;".self::NL
            ."    /**".self::NL
            ."     * The name of base website UI Theme.".self::NL
            ."     * ".self::NL
            ."     * @var string".self::NL
            ."     * ".self::NL
            ."     * @since 1.3".self::NL
            ."     */".self::NL
            ."    private \$baseThemeName;".self::NL
            ."    /**".self::NL
            ."     * The base URL that is used by all web site pages to fetch resource files.".self::NL
            ."     * ".self::NL
            ."     * @var string".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    private \$baseUrl;".self::NL
            ."    /**".self::NL
            ."     * Configuration file version number.".self::NL
            ."     * ".self::NL
            ."     * @var string".self::NL
            ."     * ".self::NL
            ."     * @since 1.2".self::NL
            ."     */".self::NL
            ."    private \$configVision;".self::NL
            ."    /**".self::NL
            ."     * An array which contains different descriptions in different languages.".self::NL
            ."     * ".self::NL
            ."     * @var string".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    private \$descriptions;".self::NL
            ."    /**".self::NL
            ."     * The URL of the home page.".self::NL
            ."     * ".self::NL
            ."     * @var string".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    private \$homePage;".self::NL
            ."    /**".self::NL
            ."     * The primary language of the website.".self::NL
            ."     */".self::NL
            ."    private \$primaryLang;".self::NL
            ."    /**".self::NL
            ."     * A singleton instance of the class.".self::NL
            ."     * ".self::NL
            ."     * @var SiteConfig".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    private static \$siteCfg;".self::NL
            ."    /**".self::NL
            ."     *".self::NL
            ."     * @var string".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    private \$titleSep;".self::NL
            ."    /**".self::NL
            ."     * An array which contains all website names in different languages.".self::NL
            ."     * ".self::NL
            ."     * @var string".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    private \$webSiteNames;".self::NL
            ."    private function __construct() {".self::NL
            ."        \$this->configVision = '".$configArr['config-file-version']."';".self::NL
            ."        \$this->webSiteNames = ".$names.";".self::NL
            ."        \$this->baseUrl = Util::getBaseURL();".self::NL
            ."        \$this->titleSep = '".trim($configArr['title-separator'])."';".self::NL
            ."        \$this->primaryLang = '".trim($configArr['primary-language'])."';".self::NL
            ."        \$this->baseThemeName = '".$configArr['theme-name']."';".self::NL
            ."        \$this->adminThemeName = '".$configArr['admin-theme-name']."';".self::NL
            ."        \$this->homePage = Util::getBaseURL();".self::NL
            ."        \$this->descriptions = ".$descriptions.";".self::NL
            ."    }".self::NL
            ."    /**".self::NL
            ."     * Returns an instance of the configuration file.".self::NL
            ."     * ".self::NL
            ."     * @return SiteConfig".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    public static function get() {".self::NL
            ."        if (self::\$siteCfg != null) {".self::NL
            ."            return self::\$siteCfg;".self::NL
            ."        }".self::NL
            ."        self::\$siteCfg = new SiteConfig();".self::NL
            ."        ".self::NL
            ."        return self::\$siteCfg;".self::NL
            ."    }".self::NL
            ."    /**".self::NL
            ."     * Returns the name of the theme that is used in admin control pages.".self::NL
            ."     * ".self::NL
            ."     * @return string The name of the theme that is used in admin control pages.".self::NL
            ."     * ".self::NL
            ."     * @since 1.3".self::NL
            ."     */".self::NL
            ."    public static function getAdminThemeName() {".self::NL
            ."        return self::get()->_getAdminThemeName();".self::NL
            ."    }".self::NL
            ."    /**".self::NL
            ."     * Returns the name of base theme that is used in website pages.".self::NL
            ."     * ".self::NL
            ."     * Usually, this theme is used for the normall visitors of the web site.".self::NL
            ."     * ".self::NL
            ."     * @return string The name of base theme that is used in website pages.".self::NL
            ."     * ".self::NL
            ."     * @since 1.3".self::NL
            ."     */".self::NL
            ."    public static function getBaseThemeName() {".self::NL
            ."        return self::get()->_getBaseThemeName();".self::NL
            ."    }".self::NL
            ."    /**".self::NL
            ."     * Returns the base URL that is used to fetch resources.".self::NL
            ."     * ".self::NL
            ."     * The return value of this method is usually used by the tag 'base'".self::NL
            ."     * of web site pages.".self::NL
            ."     * ".self::NL
            ."     * @return string the base URL.".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    public static function getBaseURL() {".self::NL
            ."        return self::get()->_getBaseURL();".self::NL
            ."    }".self::NL
            ."    /**".self::NL
            ."     * Returns version number of the configuration file.".self::NL
            ."     * ".self::NL
            ."     * This value can be used to check for the compatability of configuration".self::NL
            ."     * file".self::NL
            ."     * ".self::NL
            ."     * @return string The version number of the configuration file.".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    public static function getConfigVersion() {".self::NL
            ."        return self::get()->_getConfigVersion();".self::NL
            ."    }".self::NL
            ."    /**".self::NL
            ."     * Returns an associative array which contains different website descriptions".self::NL
            ."     * in different languages.".self::NL
            ."     * ".self::NL
            ."     * Each index will contain a language code and the value will be the description".self::NL
            ."     * of the website in the given language.".self::NL
            ."     * ".self::NL
            ."     * @return string An associative array which contains different website descriptions".self::NL
            ."     * in different languages.".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    public static function getDescriptions() {".self::NL
            ."        return self::get()->_getDescriptions();".self::NL
            ."    }".self::NL
            ."    /**".self::NL
            ."     * Returns the home page URL of the website.".self::NL
            ."     * ".self::NL
            ."     * @return string The home page URL of the website.".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    public static function getHomePage() {".self::NL
            ."        return self::get()->_getHomePage();".self::NL
            ."    }".self::NL
            ."    /**".self::NL
            ."     * Returns the primary language of the website.".self::NL
            ."     * ".self::NL
            ."     * This function will return a language code such as 'EN'.".self::NL
            ."     * ".self::NL
            ."     * @return string Language code of the primary language.".self::NL
            ."     * ".self::NL
            ."     * @since 1.3".self::NL
            ."     */".self::NL
            ."    public static function getPrimaryLanguage() {".self::NL
            ."        return self::get()->_getPrimaryLanguage();".self::NL
            ."    }".self::NL
            ."    /**".self::NL
            ."     * Returns the character (or string) that is used to separate page title from website name.".self::NL
            ."     * ".self::NL
            ."     * @return string A string such as ' - ' or ' | '. Note that the method".self::NL
            ."     * will add the two spaces by default.".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    public static function getTitleSep() {".self::NL
            ."        return self::get()->_getTitleSep();".self::NL
            ."    }".self::NL
            ."    /**".self::NL
            ."     * Returns an array which contains diffrent website names in different languages.".self::NL
            ."     * ".self::NL
            ."     * Each index will contain a language code and the value will be the name".self::NL
            ."     * of the website in the given language.".self::NL
            ."     * ".self::NL
            ."     * @return array An array which contains diffrent website names in different languages.".self::NL
            ."     * ".self::NL
            ."     * @since 1.0".self::NL
            ."     */".self::NL
            ."    public static function getWebsiteNames() {".self::NL
            ."        return self::get()->_getWebsiteNames();".self::NL
            ."    }".self::NL
            ."    private function _getAdminThemeName() {".self::NL
            ."        return \$this->adminThemeName;".self::NL
            ."    }".self::NL
            ."    private function _getBaseThemeName() {".self::NL
            ."        return \$this->baseThemeName;".self::NL
            ."    }".self::NL
            ."    private function _getBaseURL() {".self::NL
            ."        return \$this->baseUrl;".self::NL
            ."    }".self::NL
            ."    private function _getConfigVersion() {".self::NL
            ."        return \$this->configVision;".self::NL
            ."    }".self::NL
            ."    private function _getDescriptions() {".self::NL
            ."        return \$this->descriptions;".self::NL
            ."    }".self::NL
            ."    private function _getHomePage() {".self::NL
            ."        return \$this->homePage;".self::NL
            ."    }".self::NL
            ."    ".self::NL
            ."    private function _getPrimaryLanguage() {".self::NL
            ."        return \$this->primaryLang;".self::NL
            ."    }".self::NL
            ."    private function _getTitleSep() {".self::NL
            ."        return \$this->titleSep;".self::NL
            ."    }".self::NL
            ."    private function _getWebsiteNames() {".self::NL
            ."        return \$this->webSiteNames;".self::NL
            ."    }".self::NL
            ."}".self::NL;
        $mailConfigFile = new File('SiteConfig.php', ROOT_DIR.DS.'conf');
        $mailConfigFile->remove();
        $mailConfigFile->setRawData($fileAsStr);
        $mailConfigFile->write(false, true);
    }
}
