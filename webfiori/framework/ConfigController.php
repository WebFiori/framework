<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2020 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework;

use Exception;
use webfiori\database\ConnectionInfo;
use webfiori\email\SMTPAccount;
use webfiori\file\exceptions\FileException;
use webfiori\file\File;
use webfiori\framework\exceptions\InitializationException;
use webfiori\framework\writers\LangClassWriter;
/**
 * A class that can be used to modify basic configuration settings of 
 * the web application. 
 *
 * @author Ibrahim
 * 
 * @version 1.5.3
 */
class ConfigController {
    const NL = "\n";
    private $blockEnd;
    private $configVars;
    private $docEmptyLine;
    private $docEnd;
    private $docStart;
    private $since10;
    private $since101;
    /**
     * An instance of the class.
     * 
     * @var ConfigController
     * 
     * @since 1.0 
     */
    private static $singleton;
    private function __construct() {
        $this->since10 = " * @since 1.0";
        $this->since101 = " * @since 1.0.1";
        $this->docEnd = " */";
        $this->blockEnd = "}";
        $this->docStart = "/**";
        $this->docEmptyLine = " * ";
        $this->configVars = [
            'config-file-version' => '1.0',
            'smtp-connections' => [],
            'database-connections' => [],
            'scheduler-password' => 'NO_PASSWORD',
            'version-info' => [
                'version' => '1.0',
                'version-type' => 'Stable',
                'release-date' => '2021-01-10'
            ],
            'site' => [
                'base-url' => '',
                'primary-lang' => 'EN',
                'title-sep' => '|',
                'home-page' => null,
                'admin-theme' => '',
                'base-theme' => '',
                'descriptions' => [
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
    }

    /**
     * Adds new database connections information or update existing connections.
     *
     * The information of the connections will be stored in the file 'AppConfig.php'.
     *
     * @param ConnectionInfo $dbConnectionsInfo An object that contains objects of type ConnectionInfo.
     *
     * @throws FileException
     * @since 1.4.3
     */
    public function addOrUpdateDBConnection(ConnectionInfo $dbConnectionsInfo) {
        $this->configVars['database-connections'][$dbConnectionsInfo->getName()] = $dbConnectionsInfo;
        $this->writeAppConfig();
    }

    /**
     * Creates application configuration class in the root directory of application
     * folder if not exist.
     * @throws FileException
     */
    public function createAppConfigFile() {
        if (!class_exists(APP_DIR.'\\config\\AppConfig')) {
            $this->writeAppConfig();
        }
    }
    /**
     * Creates the class 'Env'.
     * 
     * By default, the class will be created inside the folder 'APP_DIR/config'.
     * 
     * @throws Exception The method will throw an exception if the method
     * was unable to create the class.
     */
    public function createConstClass() {
        $this->createAppDirs();
        $DS = DIRECTORY_SEPARATOR;
        //The class GlobalConstants must exist before autoloader.
        //For this reason, use the 'resource' instead of the class 'File'. 
        $path = ROOT_PATH.$DS.APP_DIR.$DS.'config'.$DS."Env.php";
        $resource = fopen($path, 'w');

        if (!is_resource($resource)) {
            require_once ROOT_PATH.$DS.'vendor'.$DS.'webfiori'.$DS.'framework'.$DS.'webfiori'.$DS.'framework'.$DS.'exceptions'.$DS.'InitializationException.php';
            throw new InitializationException('Unable to create the file "'.$path.'"');
        }
        $this->a($resource, [
            "<?php",
            '',
            "namespace ".APP_DIR."\\config;",
            '',
            $this->docStart,
            " * A class which is used to initialize environment variables as global constants.",
            $this->docEmptyLine,
            " * This class has one static method which is used to define environment variables.",
            " * The class can be used to initialize any constant that the application depends",
            " * on. The constants that this class will initialize are the constants which",
            " * uses the function <code>define()</code>.",
            " * Also, the developer can modify existing ones as needed to change some of the",
            " * default settings of application environment.",
            $this->docEmptyLine,
            " * @since 1.1.0",
            $this->docEnd,
            "class Env {"
        ]);

        $this->a($resource, [
            $this->docStart,
            " * Initialize environment variables.",
            $this->docEmptyLine,
            " * Include your own in the body of this method or modify existing ones",
            " * to suite your configuration. It is recommended to check if the global",
            " * constant is defined or not before defining it using the function",
            " * <code>defined</code>.",
            $this->docEmptyLine,
            $this->since10,
            $this->docEnd,
            "public static function defineEnvVars() {"
        ], 1);
        $constantsArr = [
            'SCRIPT_MEMORY_LIMIT' => [
                'summary' => 'Memory limit per script.',
                'description' => "This constant represents the maximum amount of memory each script will
             * consume before showing a fatal error. 
             * Default value is 2GB. The* developer can change this value as needed.",
                'since' => '1.0',
                'type' => 'string',
                'value' => "'2048M'"
            ],
            'WF_SESSION_STORAGE' => [
                'summary' => 'A constant which holds the class name of sessions storage 
             * engine alongside its namespace.',
               'description' => "The value of this constant is used to configure session storage 
             * engine. For example, if the name of the class that represents 
             * storage engine is 'MySessionStorage' and the class exist in the 
             * namespace 'extras\\util', then the value of the constant should be 
             * '\\extras\\util\\MySessionStorage'. To use database session storage 
             * set this constant to the value '\\webfiori\\framework\\session\\DatabaseSessionStorage'.",
               'since' => '2.1.0',
               'type' => 'string',
               'value' => "'\\webfiori\\framework\\session\\DefaultSessionStorage'"
            ],
            'DATE_TIMEZONE' => [
                'summary' => 'Define the timezone at which the system will operate in.',
                'description' => "The value of this constant is passed to the function 'date_default_timezone_set()'. 
             * This one is used to fix some date and time related issues when the 
             * application is deployed in multiple servers.
             * See https://php.net/manual/en/timezones.php for supported time zones.
             * Change this as needed.",
                'since' => '1.0',
                'type' => 'string',
                'value' => "'Asia/Riyadh'"
            ],
            'PHP_INT_MIN' => [
                'summary' => 'Fallback for older php versions that does not support the constant 
             * PHP_INT_MIN.',
               'since' => '1.0',
               'type' => 'int',
               'value' => '~PHP_INT_MAX'
            ],
            'LOAD_COMPOSER_PACKAGES' => [
                'summary' => 'This constant is used to tell the core if the application uses composer 
             * packages or not.',
               'description' => "If set to true, then composer packages will be loaded.",
               'since' => '1.0',
               'type' => 'boolean',
               'value' => "true"
            ],
            'SCHEDULER_THROUGH_HTTP' => [
                'summary' => 'A constant which is used to enable or disable HTTP access to background tasks control panel.',
                'description' => "If the constant value is set to true, the framework will add routes to the 
             * components which is used to allow access to tasks control panel. The control 
             * panel is used to execute tasks and check execution status. Default value is false.",
                'since' => '1.0',
                'type' => 'boolean',
                'value' => "false"
            ],
            'WF_VERBOSE' => [
                'summary' => 'This constant is used to tell the framework if more information should 
             * be displayed if an exception is thrown or an error happens.',
               'description' => "The main aim 
             * of this constant is to hide some sensitive information from users if the 
             * system is in production environment. Note that the constant will have effect 
             * only if the framework is accessed through HTTP protocol. If used in CLI 
             * environment, everything will appear. Default value of the constant is 
             * false.",
               'since' => '1.0',
               'type' => 'boolean',
               'value' => "false"
            ],
            'NO_WWW' => [
                'summary' => 'This constant is used to redirect a URI with www to non-www.',
                'description' => "If this constant is defined and is set to true and a user tried to 
             * access a resource using a URI that contains www in the host part,
             * the router will send a 301 - permanent redirect HTTP response code and 
             * send the user to non-www host. For example, if a request is sent to 
             * 'https://www.example.com/my-page', it will be redirected to 
             * 'https://example.com/my-page'. Default value of the constant is false which 
             * means no redirection will be performed.",
                'since' => '1.0',
                'type' => 'boolean',
                'value' => "false"
            ],

            'CLI_HTTP_HOST' => [
                'summary' => 'Host name to use in case the system is executed through CLI.',
                'description' => "When the application is running through CLI, there is no actual 
             * host name. For this reason, the host is set to 127.0.0.1 by default. 
             * If this constant is defined, the host will be changed to the value of 
             * the constant. Default value of the constant is 'example.com'.",
                'since' => '1.0',
                'type' => 'string',
                'value' => "'example.com'"
            ],
            'DS' => [
                'summary' => 'Directory separator.',
                'description' => "This one is is used as a shorthand instead of using PHP 
             * constant 'DIRECTORY_SEPARATOR'. The two will have the same value.",
                'since' => '1.0',
                'type' => 'string',
                'value' => "DIRECTORY_SEPARATOR"
            ],
            'USE_HTTP' => [
                'summary' => 'Sets the framework to use \'http://\' or \'https://\' for base URIs.',
                'description' => "The default behaviour of the framework is to use 'https://'. But 
             * in some cases, there is a need for using 'http://'.
             * If this constant is set to true, the framework will use 'http://' for 
             * base URI of the system. Default value is false.",
                'since' => '1.0',
                'type' => 'boolean',
                'value' => "false"
            ]
        ];

        foreach ($constantsArr as $constName => $props) {
            $props['name'] = $constName;
            $this->addConst($resource, $props);
        }
        $this->a($resource, "        if (!defined('THEMES_PATH')){");
        $this->a($resource, "            \$themesDirName = 'themes';");
        $this->a($resource, "            \$themesPath = substr(__DIR__, 0, strlen(__DIR__) - strlen(APP_DIR.'/config')).\$themesDirName;");
        $this->a($resource, '            /**');
        $this->a($resource, '             * This constant represents the directory at which themes exist.');
        $this->a($resource, '             * ');
        $this->a($resource, '             * @var string');
        $this->a($resource, '             * ');
        $this->a($resource, '             * @since 1.0');
        $this->a($resource, '             * ');
        $this->a($resource, '             */');
        $this->a($resource, "            define('THEMES_PATH', \$themesPath);");
        $this->a($resource, '        }');
        $this->a($resource, $this->blockEnd, 1);
        $this->a($resource, $this->blockEnd);
        fclose($resource);
        require_once $path;
    }

    /**
     * Creates initialization class.
     *
     * Note that if routes class already exist, this method will override
     * existing file.
     *
     * @param string $className The name of the class.
     *
     * @param string $comment A PHPDoc comment for class method.
     *
     * @throws FileException
     * @since 1.5.1
     */
    public function createIniClass(string $className, string $comment) {
        $cFile = new File("$className.php", APP_PATH.'ini');
        $cFile->remove();
        $cFile->create();
        $this->a($cFile, [
            "<?php",
            '',
            "namespace ".APP_DIR."\\ini;",
            '',
            "class $className {",

        ]);
        $this->a($cFile, [
            $this->docStart,
            " * $comment",
            $this->docEmptyLine,
            $this->since10,
            $this->docEnd,
            'public static function init() {'
        ], 1);
        $this->a($cFile, "", 3);
        $this->a($cFile, "}", 1);
        $this->a($cFile, "}");
        $cFile->create(true);
        $cFile->write();
        require_once APP_PATH.'ini'.DS."$className.php";
    }

    /**
     * Creates a file that holds class information which is used to create
     * routes.
     *
     * Note that if routes class already exist, this method will override
     * existing file.
     *
     * @param string $className The name of the class.
     *
     * @throws FileException
     * @since 1.5.1
     */
    public function createRoutesClass(string $className) {
        $cFile = new File("$className.php", APP_PATH.'ini'.DS.'routes');
        $cFile->remove();
        $this->a($cFile, "<?php");
        $this->a($cFile, "");
        $this->a($cFile, "namespace ".APP_DIR."\\ini\\routes;");
        $this->a($cFile, "");
        $this->a($cFile, "use webfiori\\framework\\router\\Router;");
        $this->a($cFile, "");
        $this->a($cFile, "class $className {");
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Initialize system routes.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public static function create() {");
        $this->a($cFile, "        //TODO: Add your own routes here.");
        $this->a($cFile, $this->blockEnd, 1);
        $this->a($cFile, "}");
        $cFile->create(true);
        $cFile->write();
        require_once $cFile->getAbsolutePath();
    }
    /**
     * Returns a single instance of the class.
     * 
     * @return ConfigController
     * 
     * @since 1.0
     */
    public static function get(): ConfigController {
        if (self::$singleton === null) {
            self::$singleton = new ConfigController();
        }

        return self::$singleton;
    }
    /**
     * Returns a string that represents the name of admin theme of the web 
     * application.
     * 
     * Note that if theme is not set the method will return empty string.
     * 
     * @return string
     * 
     * @since 1.0
     */
    public function getAdminTheme(): string {
        return $this->configVars['site']['admin-theme'];
    }
    /**
     * Returns an associative array that holds application version info.
     * 
     * @return array The array will have the following indices: 'version', 
     * 'version-type' and 'release-date'.
     * 
     * @since 1.5.2
     */
    public function getAppVersionInfo(): array {
        return $this->configVars['version-info'];
    }
    /**
     * Returns the base URL which is use as a value for the tag &gt;base&lt;.
     * 
     * @return string
     * 
     * @since 1.0
     */
    public function getBase(): string {
        return $this->configVars['site']['base-url'];
    }
    /**
     * Returns a string that represents the name of the base theme of the web 
     * application.
     * 
     * Note that if theme is not set the method will return empty string.
     * 
     * @return string 
     * 
     * @since 1.0
     */
    public function getBaseTheme() : string {
        return $this->configVars['site']['base-theme'];
    }
    /**
     * Returns password hash of the password which is used to protect background 
     * tasks from unauthorized execution.
     * 
     * @return string Password hash or the string 'NO_PASSWORD' if there is no 
     * password.
     * 
     * @since 1.5.2
     */
    public function getSchedulerPassword(): string {
        return $this->configVars['scheduler-password'];
    }
    /**
     * Returns an array that holds database connections.
     * 
     * @return array The indices of the array are names of the connections and 
     * the value of each index is an object of type 'ConnectionInfo'.
     * 
     * @since 1.0
     */
    public function getDatabaseConnections(): array {
        return $this->configVars['database-connections'];
    }
    /**
     * Returns an array that holds different descriptions for the web application 
     * on different languages.
     * 
     * @return array The indices of the array are language codes such as 'AR' and 
     * the value of the index is the description.
     * 
     * @since 1.0
     */
    public function getDescriptions(): array {
        return $this->configVars['site']['descriptions'];
    }
    /**
     * Returns a link that represents the home page of the web application.
     * 
     * @return string|null
     * 
     * @since 1.0
     */
    public function getHomePage() {
        return $this->configVars['site']['home-page'];
    }
    /**
     * Returns a string that represents primary language of the web application.
     * 
     * @return string A two characters string such as 'EN'.
     * 
     * @since 1.0
     */
    public function getPrimaryLang(): string {
        return $this->configVars['site']['primary-lang'];
    }
    /**
     * Returns an associative array that contains website configuration 
     * info.
     * 
     * The returned array will have the following indices: 
     * <ul>
     * <li><b>website-names</b>: A sub associative array. The index of the 
     * array will be language code (such as 'EN') and the value 
     * will be the name of the website in the given language.</li>
     * <li><b>base-url</b>: The URL at which system pages will be served from. 
     * usually, this URL is used in the tag 'base' of the web page.</li>
     * <li><b>title-sep</b>: A character or a string that is used 
     * to separate website name from web page title.</li>
     * <li><b>home-page</b>: The URL of the home page of the website.</li>
     * <li><b>base-theme</b>: The name of the theme that will be used to style 
     * website UI.</li>
     * <li><b>primary-lang</b>: Primary language of the website.
     * <li><b>admin-theme</b>: The name of the theme that is used to style 
     * admin web pages.</li>
     * <li><b>descriptions</b>: A sub associative array. The index of the 
     * array will be language code (such as 'EN') and the value 
     * will be the general website description in the given language.</li></li>
     * </ul> 
     * @return array An associative array that contains website configuration 
     * info.
     * 
     * @since 1.0
     */
    public function getSiteConfigVars(): array {
        return $this->configVars['site'];
    }
    /**
     * Returns an array that holds SMTP connections.
     * 
     * @return array The indices of the array are names of the connections and 
     * the value of each index is an object of type 'SMTPAccount'.
     * 
     * @since 1.0
     */
    public function getSMTPAccounts(): array {
        return $this->configVars['smtp-connections'];
    }
    /**
     * Returns an array that holds different page titles for the web application 
     * on different languages.
     * 
     * @return array The indices of the array are language codes such as 'AR' and 
     * the value of the index is the title.
     * 
     * @since 1.0
     */
    public function getTitles(): array {
        return $this->configVars['site']['titles'];
    }
    /**
     * Returns a string that represents the string that will be used to separate 
     * website name from page title.
     * 
     * @return string
     * 
     * @since 1.0
     */
    public function getTitleSep(): string {
        return $this->configVars['site']['title-sep'];
    }
    /**
     * Returns an array that holds different names for the web application 
     * on different languages.
     * 
     * @return array The indices of the array are language codes such as 'AR' and 
     * the value of the index is the name.
     * 
     * @since 1.0
     */
    public function getWebsiteNames(): array {
        return $this->configVars['site']['website-names'];
    }

    /**
     * Removes SMTP email account if it exists.
     *
     * @param string $accountName The name of the email account (such as 'no-reply').
     *
     * @throws FileException
     * @since 1.3
     */
    public function removeAccount(string $accountName) {
        if (isset($this->configVars['smtp-connections'][$accountName])) {
            unset($this->configVars['smtp-connections'][$accountName]);
        }
        $this->writeAppConfig();
    }

    /**
     * Removes all stored database connections from the class 'AppConfig'.
     * @throws FileException
     */
    public function removeAllDBConnections() {
        $this->configVars['database-connections'] = [];
        $this->writeAppConfig();
    }
    /**
     * Removes the class 'Env.php' and the file 'AppConfig.php'.
     */
    public function removeConfigFiles() {
        $cFile = new File('AppConfig.php', APP_PATH.'config');
        $cFile->remove();
        $eFile = new File('Env.php', APP_PATH.'config');
        $eFile->remove();
    }

    /**
     * Removes database connection given its name.
     *
     * This method will search for a connection which has the given database
     * name. Once it found, it will remove the connection and save the updated
     * information to the file 'AppConfig.php'.
     *
     * @param string $connectionName The name of the connection.
     *
     * @throws FileException
     * @since 1.4.3
     */
    public function removeDBConnection(string $connectionName) {
        $connections = $this->getDatabaseConnections();
        $updated = [];

        foreach ($connections as $name => $conObj) {
            if ($name != $connectionName) {
                $updated[] = $conObj;
            }
        }
        $this->configVars['database-connections'] = $updated;
        $this->writeAppConfig();
    }

    /**
     * @throws FileException
     */
    public function resetConfig() {
        self::get()->setConfig(App::getConfig());
        $this->writeAppConfig();
    }
    /**
     * Sets the configuration that will be used by the class.
     * 
     * @param Config $cfg
     * 
     * @since 1.5.3
     */
    public function setConfig(Config $cfg) {
        $this->configVars['smtp-connections'] = $cfg->getAccounts();
        $this->configVars['database-connections'] = $cfg->getDBConnections();
        $this->configVars['version-info'] = [
            'version' => $cfg->getVersion(),
            'version-type' => $cfg->getVersionType(),
            'release-date' => $cfg->getReleaseDate()
        ];
        $this->configVars['site'] = [
            'base-url' => $cfg->getBaseURL(),
            'primary-lang' => $cfg->getPrimaryLanguage(),
            'title-sep' => $cfg->getTitleSep(),
            'home-page' => $cfg->getHomePage(),
            'admin-theme' => $cfg->getAdminThemeName(),
            'base-theme' => $cfg->getBaseThemeName(),
            'descriptions' => $cfg->getDescriptions(),
            'website-names' => $cfg->getWebsiteNames(),
            'titles' => $cfg->getTitles(),
        ];
    }

    /**
     * Update application version information.
     *
     * @param string $vNum Version number such as 1.0.0.
     *
     * @param string $vType Version type such as 'Beta', 'Alpha' or 'RC'.
     *
     * @param string $releaseDate The date at which the version was released on.
     *
     * @throws FileException
     * @since 1.5.2
     */
    public function updateAppVersionInfo(string $vNum, string $vType, string $releaseDate) {
        $this->configVars['version-info'] = [
            'version' => $vNum,
            'version-type' => $vType,
            'release-date' => $releaseDate
        ];
        $this->writeAppConfig();
    }

    /**
     * Updates the password which is used to protect tasks from unauthorized
     * execution.
     *
     * @param string $newPass The new password. If empty string is given, the password
     * will be set to the string 'NO_PASSWORD'. Note that provided value will
     * be hashed using SHA256 algorithm.
     *
     * @throws FileException
     * @since 1.5.2
     */
    public function updateSchedulerPassword(string $newPass) {
        $this->configVars['scheduler-password'] = hash('sha256', $newPass);
        $this->writeAppConfig();
    }

    /**
     * Adds new SMTP account or Updates an existing one.
     *
     * Note that the connection will be added or updated only if it
     * has correct information.
     *
     * @param SMTPAccount $emailAccount An instance of 'SMTPAccount'.
     *
     *
     * @throws FileException
     * @since 1.1
     */
    public function updateOrAddEmailAccount(SMTPAccount $emailAccount) {
        $this->configVars[$emailAccount->getAccountName()] = $emailAccount;
        $this->writeAppConfig();
    }

    /**
     * Updates website configuration based on some attributes.
     *
     * @param array $websiteInfoArr an associative array. The array can
     * have the following indices:
     * <ul>
     * <li><b>primary-lang</b>: The main display language of the website.
     * <li><b>website-names</b>: A sub associative array. The index of the
     * array should be language code (such as 'EN') and the value
     * should be the name of the website in the given language.</li>
     * <li><b>title-sep</b>: A character or a string that is used
     * to separate website name from web page title. Two common
     * values are '-' and '|'.</li>
     * <li><b>home-page</b>: The URL of the home page of the website. For example,
     * If root URL of the website is 'https://www.example.com', This page is served
     * when the user visits this URL.</li>
     * <li><b>base-theme</b>: The name of the theme that will be used to style
     * website UI.</li>
     * <li><b>admin-theme</b>: If the website has two UIs (One for normal
     * users and another for admins), this one
     * can be used to serve the UI for website admins.</li>
     * <li><b>descriptions</b>: A sub associative array. The index of the
     * array should be language code (such as 'EN') and the value
     * should be the general website description in the given language.</li></li>
     * </ul>
     *
     * @throws FileException
     * @since 1.0
     */
    public function updateSiteInfo(array $websiteInfoArr) {
        $keys = array_keys($this->configVars['site']);
        $updated = [];

        foreach ($keys as $key) {
            if (isset($websiteInfoArr[$key])) {
                $updated[$key] = $websiteInfoArr[$key];
            } else {
                $updated[$key] = $this->configVars['site'][$key];
            }
        }
        $this->configVars['site'] = $updated;
        $this->writeAppConfig();
    }

    /**
     * Stores configuration variables into the application configuration class.
     *
     * @throws FileException
     * @since 1.5
     */
    public function writeAppConfig() {
        $cFile = new File('AppConfig.php', APP_PATH.'config');
        $cFile->remove();

        $this->writeAppConfigAttrs($cFile);

        $this->writeAppConfigConstructor($cFile);

        $this->writeAppConfigAddMethods($cFile);

        $this->writeFuncHeader($cFile, 
            'public function getAccount(string $name)', 
            'Returns SMTP account given its name.', 
            [
                'The method will search for an account with the given name in the set',
                'of added accounts. If no account was found, null is returned.'
            ], 
            [
                '$name' => [
                    'type' => 'string',
                    'description' => 'The name of the account.'
                ]
            ], 
            [
                'type' => 'SMTPAccount|null',
                'description' => [
                    'If the account is found, The method',
                    'will return an object of type SMTPAccount. Else, the',
                    'method will return null.'
                ]
            ]);
        $this->a($cFile, "        if (isset(\$this->emailAccounts[\$name])) {");
        $this->a($cFile, "            return \$this->emailAccounts[\$name];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getAccounts() : array', 
            'Returns an associative array that contains all email accounts.', 
            [
                'The indices of the array will act as the names of the accounts.',
                'The value of the index will be an object of type SMTPAccount.'
            ], 
            [], 
            [
                'type' => 'array',
                'description' => 'An associative array that contains all email accounts.'
            ]);
        $this->a($cFile, "        return \$this->emailAccounts;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getAdminThemeName() : string', 
            'Returns the name of the theme that is used in admin control pages.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => 'The name of the theme that is used in admin control pages.'
            ]);
        $this->a($cFile, "        return \$this->adminThemeName;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getBaseThemeName() : string', 
            'Returns the name of base theme that is used in website pages.', 
            'Usually, this theme is used for the normally visitors of the website.', 
            [], 
            [
                'type' => 'string',
                'description' => 'The name of base theme that is used in website pages.'
            ]);
        $this->a($cFile, "        return \$this->baseThemeName;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getBaseURL() : string', 
            'Returns the base URL that is used to fetch resources.', 
            [
                "The return value of this method is usually used by the tag 'base'",
                'of website pages.'
            ], 
            [], 
            [
                'type' => 'string',
                'description' => 'The base URL.'
            ]);
        $this->a($cFile, "        return \$this->baseUrl;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getConfigVersion() : string', 
            'Returns version number of the configuration file.', 
            'This value can be used to check for the compatability of configuration file', 
            [], 
            [
                'type' => 'string',
                'description' => 'The version number of the configuration file.'
            ]);
        $this->a($cFile, "        return \$this->configVision;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getSchedulerPassword() : string', 
            'Returns sha256 hash of the password which is used to prevent unauthorized access to run the tasks or access scheduler web interface.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => "Password hash or the string 'NO_PASSWORD' if there is no password."
            ]);
        $this->a($cFile, "        return \$this->schedulerPass;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getDBConnection(string $conName)', 
            'Returns database connection information given connection name.', 
            '', 
            [
                '$conName' => [
                    'type' => 'string',
                    'description' => 'The name of the connection.'
                ]
            ], 
            [
                'type' => 'ConnectionInfo|null',
                'description' => [
                    'The method will return an object of type',
                    'ConnectionInfo if a connection info was found for the given connection name.',
                    'Other than that, the method will return null.'
                ]
            ]);
        $this->a($cFile, "        \$conns = \$this->getDBConnections();");
        $this->a($cFile, "        \$trimmed = trim(\$conName);");
        $this->a($cFile, "        ");
        $this->a($cFile, "        if (isset(\$conns[\$trimmed])) {");
        $this->a($cFile, "            return \$conns[\$trimmed];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getDBConnections() : array', 
            'Returns an associative array that contain the information of database connections.', 
            [
                'The keys of the array will be the name of database connection and the',
                'value of each key will be an object of type ConnectionInfo.'
            ], 
            [], 
            [
                'type' => 'array',
                'description' => 'An associative array.'
            ]);
        $this->a($cFile, "        return \$this->dbConnections;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getDefaultTitle(string $langCode)', 
            'Returns the global title of the website that will be used as default page title.', 
            '', 
            [
                '$langCode' => [
                    'type' => 'string',
                    'description' => "Language code such as 'AR' or 'EN'."
                ]
            ], 
            [
                'type' => 'string|null',
                'description' => [
                    'If the title of the page',
                    'does exist in the given language, the method will return it.',
                    'If no such title, the method will return null.'
                ]
            ]);
        $this->a($cFile, "        \$langs = \$this->getTitles();");
        $this->a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        $this->a($cFile, "        ");
        $this->a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        $this->a($cFile, "            return \$langs[\$langCode];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);


        $this->writeFuncHeader($cFile, 
            'public function getDescription(string $langCode)', 
            'Returns the global description of the website that will be used as default page description.', 
            '', 
            [
                '$langCode' => [
                    'type' => 'string',
                    'description' => "Language code such as 'AR' or 'EN'."
                ]
            ], 
            [
                'type' => 'string|null',
                'description' => [
                    'If the description for the given language',
                    'does exist, the method will return it. If no such description, the',
                    'method will return null.'
                ]
            ]);
        $this->a($cFile, "        \$langs = \$this->getDescriptions();");
        $this->a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        $this->a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        $this->a($cFile, "            return \$langs[\$langCode];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getDescriptions() : array', 
            'Returns an associative array which contains different website descriptions in different languages.', 
            [
                'Each index will contain a language code and the value will be the description',
                'of the website in the given language.'
            ], 
            [], 
            [
                'type' => 'array',
                'description' => [
                    'An associative array which contains different website descriptions',
                    'in different languages.'
                ]
            ]);
        $this->a($cFile, "        return \$this->descriptions;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getHomePage() : string', 
            'Returns the home page URL of the website.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => 'The home page URL of the website.'
            ]);
        $this->a($cFile, "        return \$this->homePage;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getPrimaryLanguage() : string', 
            'Returns the primary language of the website.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => "Language code of the primary language such as 'EN'."
            ]);
        $this->a($cFile, "        return \$this->primaryLang;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getReleaseDate() : string', 
            'Returns the date at which the application was released at.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => [
                    'The method will return a string in the format',
                    "YYYY-MM-DD' that represents application release date."
                ]
            ]);
        $this->a($cFile, "        return \$this->appReleaseDate;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getTitles() : array', 
            'Returns an array that holds the default page title for different display languages.', 
            '', 
            [], 
            [
                'type' => 'array',
                'description' => [
                    'An associative array. The indices of the array are language codes',
                    'and the values are pages titles.'
                ]
            ]);
        $this->a($cFile, "        return \$this->defaultPageTitles;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getTitleSep() : string', 
            'Returns the character (or string) that is used to separate page title from website name.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => [
                    "A string such as ' - ' or ' | '. Note that the method",
                    'will add the two spaces by default.'
                ]
            ]);
        $this->a($cFile, "        return \$this->titleSep;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getVersion() : string', 
            'Returns version number of the application.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => [
                    'The method should return a string in the',
                    "form 'x.x.x.x'."
                ]
            ]);
        $this->a($cFile, "        return \$this->appVersion;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getVersionType() : string', 
            'Returns a string that represents application release type.', 
            '', 
            [], 
            [
                'type' => 'string',
                'description' => [
                    'The method will return a string such as',
                    "'Stable', 'Alpha', 'Beta' and so on."
                ]
            ]);
        $this->a($cFile, "        return \$this->appVersionType;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getWebsiteName(string $langCode)', 
            'Returns the global website name.', 
            '', 
            [
                '$langCode' => [
                    'type' => 'string',
                    'description' => "Language code such as 'AR' or 'EN'."
                ]
            ], 
            [
                'type' => 'string|null',
                'description' => [
                    'If the name of the website for the given language',
                    'does exist, the method will return it. If no such name, the',
                    'method will return null.'
                ]
            ]);
        $this->a($cFile, "        \$langs = \$this->getWebsiteNames();");
        $this->a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        $this->a($cFile, "        ");
        $this->a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        $this->a($cFile, "            return \$langs[\$langCode];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function getWebsiteNames() : array', 
            'Returns an array which contains different website names in different languages.', 
            [
                'Each index will contain a language code and the value will be the name',
                'of the website in the given language.'
            ], 
            [], 
            [
                'type' => 'array',
                'description' => [
                    'An array which contains different website names in different languages.'
                ]
            ]);
        $this->a($cFile, "        return \$this->webSiteNames;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function removeDBConnections()', 
            'Removes all stored database connections.');
        $this->a($cFile, "        \$this->dbConnections = [];");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeDbCon($cFile);
        $this->writeSiteInfo($cFile);
        $this->writeSmtpConn($cFile);
        $this->writeAppVersionInfo($cFile);

        $this->a($cFile, "}");
        $cFile->create(true);
        $cFile->write();
        require_once APP_PATH.'config'.DS.'AppConfig.php';
    }
    private function writeAppConfigAttrs($cFile) {
        $this->a($cFile, "<?php");
        $this->a($cFile, "");
        $this->a($cFile, "namespace ".APP_DIR."\\config;");
        $this->a($cFile, "");
        $this->a($cFile, "use webfiori\\database\\ConnectionInfo;");
        $this->a($cFile, "use webfiori\\email\\SMTPAccount;");
        $this->a($cFile, "use webfiori\\framework\\Config;");
        $this->a($cFile, "use webfiori\\http\\Uri;");
        $this->a($cFile, "/**");
        $this->a($cFile, " * Configuration class of the application");
        $this->a($cFile, " *");
        $this->a($cFile, " * @author Ibrahim");
        $this->a($cFile, " *");
        $this->a($cFile, " * @version 1.0.1");
        $this->a($cFile, " *");
        $this->a($cFile, " * @since 2.1.0");
        $this->a($cFile, " */");
        $this->a($cFile, "class AppConfig implements Config {");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The name of admin control pages Theme.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$adminThemeName;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The date at which the application was released.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$appReleaseDate;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * A string that represents the type of the release.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$appVersionType;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Version of the web application.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$appVersion;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The name of base website UI Theme.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$baseThemeName;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The base URL that is used by all website pages to fetch resource files.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$baseUrl;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Configuration file version number.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$configVision;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Password hash of scheduler sub-system.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$schedulerPass;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An associative array that will contain database connections.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var array");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$dbConnections;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An array that is used to hold default page titles for different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var array");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$defaultPageTitles;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An array that is used to hold default page descriptions for different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var array");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$descriptions;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An array that holds SMTP connections information.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$emailAccounts;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The URL of the home page.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$homePage;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The primary language of the website.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$primaryLang;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The character which is used to separate site name from page title.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$titleSep;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * An array which contains all website names in different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$webSiteNames;");
    }
    private function writeAppVersionInfo($cFile) {
        $this->a($cFile, [
            $this->docStart,
            $this->since10,
            $this->docEnd
        ], 1);

        $this->a($cFile, "private function initVersionInfo() {", 1);

        $versionInfo = $this->getAppVersionInfo();

        $this->a($cFile, [
            "\$this->appVersion = '".$versionInfo['version']."';",
            "\$this->appVersionType = '".$versionInfo['version-type']."';",
            "\$this->appReleaseDate = '".$versionInfo['release-date']."';"
        ], 2);

        $this->a($cFile, $this->blockEnd, 1);
    }
    private function writeSchedulerPass($cFile) {
        $password = $this->getSchedulerPassword();
        $this->a($cFile, "        \$this->schedulerPass = '".$password."';");
    }
    private function writeDbCon($cFile) {
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private function initDbConnections() {");
        $this->a($cFile, "        \$this->dbConnections = [");


        $dbCons = $this->getDatabaseConnections();

        foreach ($dbCons as $connObj) {
            if ($connObj instanceof ConnectionInfo) {
                $cName = $connObj->getName();
                $this->a($cFile, "            '$cName' => new ConnectionInfo('".$connObj->getDatabaseType()."',"
                        ."'".$connObj->getUsername()."', "
                        ."'".$connObj->getPassword()."', "
                        ."'".$connObj->getDBName()."', "
                        ."'".$connObj->getHost()."', ".$connObj->getPort().", [");
                $this->a($cFile, "                'connection-name' => '".str_replace("'", "\'", $cName)."'");
                $this->a($cFile, "            ]),");
            }
        }
        $this->a($cFile, "        ];");
        $this->a($cFile, $this->blockEnd, 1);
    }
    private function writeSiteTitles($cFile) {
        $titlesArr = $this->getTitles();
        $this->a($cFile, "        \$this->defaultPageTitles = [");

        foreach ($titlesArr as $langCode => $title) {
            $title = str_replace("'", "\'", $title);
            $this->a($cFile, "            '$langCode' => '$title',");

            if (!class_exists(APP_DIR.'\\langs\\Language'.$langCode)) {
                //This requires a fix in the future
                $dir = $langCode == 'AR' ? 'rtl' : 'ltr';

                $writer = new LangClassWriter($langCode, $dir);
                $writer->writeClass();
                require_once $writer->getAbsolutePath();
            }
        }
        $this->a($cFile, "        ];");
    }
    private function a($file, $str, $tabSize = 0) {
        $isResource = is_resource($file);
        $tabStr = $tabSize > 0 ? '    ' : '';

        if (gettype($str) == 'array') {
            foreach ($str as $subStr) {
                if ($isResource) {
                    fwrite($file, str_repeat($tabStr, $tabSize).$subStr.self::NL);
                } else {
                    $file->append(str_repeat($tabStr, $tabSize).$subStr.self::NL);
                }
            }
        } else {
            if ($isResource) {
                fwrite($file, str_repeat($tabStr, $tabSize).$str.self::NL);
            } else {
                $file->append(str_repeat($tabStr, $tabSize).$str.self::NL);
            }
        }
    }

    /**
     *
     * @param File|resource $file
     * @param array $options
     */
    private function addConst($file, array $options) {
        $this->a($file, "        if (!defined('".$options['name']."')){");
        $this->a($file, '            /**');

        if (isset($options['summary'])) {
            $this->a($file, '             * '.$options['summary']);
            $this->a($file, '             * ');
        }

        if (isset($options['description'])) {
            $this->a($file, '             * '.$options['description']);
            $this->a($file, '             * ');
        }

        if (isset($options['type'])) {
            $this->a($file, '             * @var '.$options['type']);
            $this->a($file, '             * ');
        }

        if (isset($options['since'])) {
            $this->a($file, '             * @since '.$options['since']);
            $this->a($file, '             * ');
        }
        $this->a($file, '             */');
        $val = $options['value'];
        $this->a($file, "            define('".$options['name']."', $val);");
        $this->a($file, '        }');
    }
    /**
     * Creates all directories at which the application needs to run.
     */
    private function createAppDirs() {
        $DS = DIRECTORY_SEPARATOR;
        $this->mkdir(ROOT_PATH.$DS.APP_DIR);
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'ini');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'ini'.$DS.'routes');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'pages');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'commands');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'tasks');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'middleware');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'langs');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'apis');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'config');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'sto');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'sto'.$DS.'uploads');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'sto'.$DS.'logs');
        $this->mkdir(ROOT_PATH.$DS.APP_DIR.$DS.'sto'.$DS.'sessions');
        $this->mkdir(ROOT_PATH.$DS.'public');
    }
    private function mkdir($dir) {
        if (!is_dir($dir)) {
            set_error_handler(function (int $errno, string $errstr)
            {
                http_response_code(500);
                die('Unable to create one or more of application directories due to an error: "Code: '.$errno.', Message: '.$errstr.'"');
            });
            mkdir($dir);
            restore_error_handler();
        }
    }
    private function writeAppConfigAddMethods(File $cFile) {
        $this->writeFuncHeader($cFile, 
            'public function addAccount(SMTPAccount $acc)', 
            'Adds SMTP account.', 
            [
                'The developer can use this method to add new account during runtime.',
                'The account will be removed once the program finishes.'
            ], [
                '$acc' => [
                    'type' => 'SMTPAccount',
                    'description' => [
                        'An object of type SMTPAccount.'
                    ]
                ]
            ]);
        $this->a($cFile, "        \$this->emailAccounts[\$acc->getAccountName()] = \$acc;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->writeFuncHeader($cFile, 
            'public function addDbConnection(ConnectionInfo $connectionInfo)', 
            'Adds new database connection or updates an existing one.', 
            '', 
            [
                '$connectionInfo' => [
                    'type' => 'ConnectionInfo',
                    'description' => [
                        "An object of type 'ConnectionInfo' that will contain connection information."
                    ]
                ]
            ]);
        $this->a($cFile, "        \$this->dbConnections[\$connectionInfo->getName()] = \$connectionInfo;");
        $this->a($cFile, $this->blockEnd, 1);
    }
    private function writeAppConfigConstructor(File $cFile) {
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Creates new instance of the class.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function __construct() {");
        $this->a($cFile, "        \$this->configVision = '1.0.1';");
        $this->a($cFile, "        \$this->initVersionInfo();");
        $this->a($cFile, "        \$this->initSiteInfo();");
        $this->a($cFile, "        \$this->initDbConnections();");
        $this->a($cFile, "        \$this->initSmtpConnections();");


        $this->writeSchedulerPass($cFile);

        $this->a($cFile, $this->blockEnd, 1);
    }
    private function writeFuncHeader($cFile, $methSig, $methodSummary = '', $description = [], $params = [], $returns = null) {
        $phpDocArr = [
            $this->docStart,
            ' * '.$methodSummary,
            $this->docEmptyLine,
        ];

        if (gettype($description) == 'array') {
            foreach ($description as $line) {
                $phpDocArr[] = ' * '.$line;
            }
            $phpDocArr[] = $this->docEmptyLine;
        } else if (strlen($description) != 0) {
            $phpDocArr[] = ' * '.$description;
            $phpDocArr[] = $this->docEmptyLine;
        }


        foreach ($params as $paramName => $paramArr) {
            $currentDescLine = ' * @param '.$paramArr['type'].' '.$paramName.' ';

            if (gettype($paramArr['description']) == 'array') {
                $currentDescLine .= $paramArr['description'][0];
                $phpDocArr[] = $currentDescLine;

                for ($x = 1 ; $x < count($paramArr['description']) ; $x++) {
                    $phpDocArr[] = ' * '.$paramArr['description'][$x];
                }
            } else {
                $phpDocArr[] = $currentDescLine.$paramArr['description'];
            }
            $phpDocArr[] = $this->docEmptyLine;
        }

        if ($returns !== null && gettype($returns) == 'array') {
            $phpDocArr[] = ' * @return '.$returns['type'].' ';

            if (gettype($returns['description']) == 'array') {
                $phpDocArr[count($phpDocArr) - 1] .= $returns['description'][0];

                for ($x = 1 ; $x < count($returns['description']) ; $x++) {
                    $phpDocArr[] = ' * '.$returns['description'][$x];
                }
            } else {
                $phpDocArr[count($phpDocArr) - 1] .= $returns['description'];
            }
        }
        $phpDocArr[] = $this->docEnd;
        $phpDocArr[] = $methSig.' {';
        $this->a($cFile, $phpDocArr, 1);
    }
    private function writeSiteDescriptions($cFile) {
        $descArr = $this->getDescriptions();
        $this->a($cFile, "        \$this->descriptions = [");

        foreach ($descArr as $langCode => $desc) {
            $desc = str_replace("'", "\'", $desc);
            $this->a($cFile, "            '$langCode' => '$desc',");
        }
        $this->a($cFile, "        ];");
    }
    private function writeSiteInfo($cFile) {
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private function initSiteInfo() {");

        $this->writeSiteNames($cFile);
        $this->writeSiteTitles($cFile);
        $this->writeSiteDescriptions($cFile);

        $this->a($cFile, "        \$this->baseUrl = Uri::getBaseURL();");

        $sep = $this->getTitleSep();
        $this->a($cFile, "        \$this->titleSep = '$sep';");

        $lang = $this->getPrimaryLang();
        $this->a($cFile, "        \$this->primaryLang = '$lang';");


        $baseTheme = $this->getBaseTheme();

        if (class_exists($baseTheme)) {
            $this->a($cFile, "        \$this->baseThemeName = \\".trim($baseTheme, '\\')."::class;");
        } else {
            $this->a($cFile, "        \$this->baseThemeName = '$baseTheme';");
        }

        $adminTheme = $this->getBaseTheme();


        if (class_exists($adminTheme)) {
            $this->a($cFile, "        \$this->adminThemeName = \\".trim($adminTheme, '\\')."::class;");
        } else {
            $this->a($cFile, "        \$this->adminThemeName = '$adminTheme';");
        }


        $home = $this->getHomePage();

        if ($home === null) {
            $this->a($cFile, "        \$this->homePage = Uri::getBaseURL();");
        } else {
            $this->a($cFile, "        \$this->homePage = '$home';");
        }


        $this->a($cFile, $this->blockEnd, 1);
    }
    private function writeSiteNames($cFile) {
        $wNamesArr = $this->getWebsiteNames();
        $this->a($cFile, "        \$this->webSiteNames = [");

        foreach ($wNamesArr as $langCode => $name) {
            $name = str_replace("'", "\'", $name);
            $this->a($cFile, "            '$langCode' => '$name',");
        }
        $this->a($cFile, "        ];");
        $this->a($cFile, "    ");
    }
    private function writeSmtpConn($cFile) {
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private function initSmtpConnections() {");
        $this->a($cFile, "        \$this->emailAccounts = [");

        $smtpAccArr = $this->getSMTPAccounts();

        foreach ($smtpAccArr as $smtpAcc) {
            if ($smtpAcc instanceof SMTPAccount) {
                $this->a($cFile, "            '".$smtpAcc->getAccountName()."' => new SMTPAccount([");
                $this->a($cFile, "                'port' => ".$smtpAcc->getPort().",");
                $this->a($cFile, "                'server-address' => '".$smtpAcc->getServerAddress()."',");
                $this->a($cFile, "                'user' => '".$smtpAcc->getUsername()."',");
                $this->a($cFile, "                'pass' => '".$smtpAcc->getPassword()."',");
                $this->a($cFile, "                'sender-name' => '".str_replace("'", "\'", $smtpAcc->getSenderName())."',");
                $this->a($cFile, "                'sender-address' => '".$smtpAcc->getAddress()."',");
                $this->a($cFile, "                'account-name' => '".str_replace("'", "\'", $smtpAcc->getAccountName())."'");
                $this->a($cFile, "            ]),");
            }
        }
        $this->a($cFile, "        ];");
        $this->a($cFile, $this->blockEnd, 1);
    }
}
