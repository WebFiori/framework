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

use webfiori\database\ConnectionInfo;
use webfiori\framework\cli\writers\LangClassWriter;
use webfiori\framework\exceptions\InitializationException;
use webfiori\framework\mail\SMTPAccount;

/**
 * A class that can be used to modify basic configuration settings of 
 * the web application. 
 *
 * @author Ibrahim
 * 
 * @version 1.5.2
 */
class ConfigController {
    /**
     * A constant that indicates the selected database schema has tables.
     * 
     * @since 1.1
     */
    const DB_NOT_EMPTY = 'db_has_tables';

    const DEFAULT_APP_CONFIG = [
        'config-file-version' => '1.0',
        'version-info' => [
            'version' => '1.0',
            'version-type' => 'Stable',
            'release-date' => '2021-01-10'
        ],
        'site' => [
            'base-url' => '',
            'primary-language' => 'EN',
            'title-separator' => '|',
            'home-page' => 'index',
            'admin-theme' => '\themes\newFiori\NewFiori',
            'base-theme' => '\themes\newFiori\NewFiori',
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

    const NL = "\n";
    private $blockEnd;
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
    }
    /**
     * Adds new database connections information or update existing connections.
     * 
     * The information of the connections will be stored in the file 'AppConfig.php'.
     * 
     * @param array $dbConnectionsInfo An array that contains objects of type ConnectionInfo. 
     * 
     * @since 1.4.3
     */
    public function addOrUpdateDBConnection(ConnectionInfo $dbConnectionsInfo) {
        $connections = $this->getDatabaseConnections();
        $connections[$dbConnectionsInfo->getName()] = $dbConnectionsInfo;
        $this->writeAppConfig([
            'db-connections' => $connections
        ]);
    }
    public function createAppConfigFile() {
        if (!class_exists(APP_DIR_NAME.'\AppConfig')) {
            $this->writeAppConfig([]);
        }
    }
    /**
     * Creates the class 'GlobalConstants'.
     * 
     * By default, the class will be created inside the folder 'app/ini'.
     * 
     * @throws \Exception The method will throw an exception if the method
     * was unable to create the class.
     */
    public function createConstClass() {
        $this->createAppDirs();
        $DS = DIRECTORY_SEPARATOR;
        //The class GlobalConstants must exist before autoloader.
        //For this reason, use the 'resource' instead of the class 'File'. 
        $path = ROOT_DIR.$DS.APP_DIR_NAME.$DS.'ini'.$DS."GlobalConstants.php";
        $resource = fopen($path, 'w');

        if (!is_resource($resource)) {
            require_once ROOT_DIR.$DS.'vendor'.$DS.'webfiori'.$DS.'framework'.$DS.'webfiori'.$DS.'framework'.$DS.'exceptions'.$DS.'InitializationException.php';
            throw new InitializationException('Unable to create the file "'.$path.'"');
        }
        $this->a($resource, [
            "<?php",
            '',
            "namespace ".APP_DIR_NAME."\\ini;",
            '',
            $this->docStart,
            " * A class which is used to initialize global constants.",
            $this->docEmptyLine,
            " * This class has one static method which is used to define the constants.",
            " * The class can be used to initialize any constant that the application depends",
            " * on. The constants that this class will initialize are the constants which",
            " * uses the function <code>define()</code>.",
            " * Also, the developer can modify existing ones as needed to change some of the",
            " * default settings of the framework.",
            $this->docEmptyLine,
            " * @since 1.1.0",
            $this->docEnd,
            "class GlobalConstants {"
        ]);

        $this->a($resource, [
            $this->docStart,
            " * Initialize the constants.",
            $this->docEmptyLine,
            " * Include your own in the body of this method or modify existing ones",
            " * to suite your configuration. It is recommended to check if the global",
            " * constant is defined or not before defining it using the function",
            " * <code>defined</code>.",
            $this->docEmptyLine,
            $this->since10,
            $this->docEnd,
            "public static function defineConstants() {"
        ], 1);

        $this->addConst($resource, [
            'name' => 'SCRIPT_MEMORY_LIMIT',
            'summary' => 'Memory limit per script.',
            'description' => "This constant represents the maximum amount of memory each script will 
             * consume before showing a fatal error. Default value is 2GB. The 
             * developer can change this value as needed.",
            'since' => '1.0',
            'type' => 'string',
            'value' => "'2048M'"
        ]);
        $this->addConst($resource, [
            'name' => 'WF_SESSION_STORAGE',
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
        ]);
        $this->addConst($resource, [
            'name' => 'DATE_TIMEZONE',
            'summary' => 'Define the timezone at which the system will operate in.',
            'description' => "The value of this constant is passed to the function 'date_default_timezone_set()'. 
             * This one is used to fix some date and time related issues when the 
             * application is deployed in multiple servers.
             * See http://php.net/manual/en/timezones.php for supported time zones.
             * Change this as needed.",
            'since' => '1.0',
            'type' => 'string',
            'value' => "'Asia/Riyadh'"
        ]);
        $this->addConst($resource, [
            'name' => 'PHP_INT_MIN',
            'summary' => 'Fallback for older php versions that does not support the constant 
             * PHP_INT_MIN.',
            'since' => '1.0',
            'type' => 'int',
            'value' => '~PHP_INT_MAX'
        ]);
        $this->addConst($resource, [
            'name' => 'LOAD_COMPOSER_PACKAGES',
            'summary' => 'This constant is used to tell the core if the application uses composer 
             * packages or not.',
            'description' => "If set to true, then composer packages will be loaded.",
            'since' => '1.0',
            'type' => 'boolean',
            'value' => "true"
        ]);
        $this->addConst($resource, [
            'name' => 'CRON_THROUGH_HTTP',
            'summary' => 'A constant which is used to enable or disable HTTP access to cron.',
            'description' => "If the constant value is set to true, the framework will add routes to the 
             * components which is used to allow access to cron control panel. The control 
             * panel is used to execute jobs and check execution status. Default value is false.",
            'since' => '1.0',
            'type' => 'boolean',
            'value' => "false"
        ]);
        $this->addConst($resource, [
            'name' => 'WF_VERBOSE',
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
        ]);
        $this->addConst($resource, [
            'name' => 'NO_WWW',
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
        ]);
        $this->addConst($resource, [
            'name' => 'MAX_BOX_MESSAGES',
            'summary' => 'The maximum number of message boxes to show in one page.',
            'description' => "A message box is a box which will be shown in a web page that 
             * contains some information. The 
             * box can be created manually by using the method 'Util::print_r()' or 
             * it can be as a result of an error during execution.
             * Default value is 15. The developer can change the value as needed. Note 
             * that if the constant is not defined, the number of boxes will 
             * be almost unlimited.",
            'since' => '1.0',
            'type' => 'int',
            'value' => "15"
        ]);
        $this->addConst($resource, [
            'name' => 'CLI_HTTP_HOST',
            'summary' => 'Host name to use in case the system is executed through CLI.',
            'description' => "When the application is running throgh CLI, there is no actual 
             * host name. For this reason, the host is set to 127.0.0.1 by default. 
             * If this constant is defined, the host will be changed to the value of 
             * the constant. Default value of the constant is 'example.com'.",
            'since' => '1.0',
            'type' => 'string',
            'value' => "'example.com'"
        ]);
        $this->addConst($resource, [
            'name' => 'DS',
            'summary' => 'Directory separator.',
            'description' => "This one is is used as a shorthand instead of using PHP 
             * constant 'DIRECTORY_SEPARATOR'. The two will have the same value.",
            'since' => '1.0',
            'type' => 'string',
            'value' => "DIRECTORY_SEPARATOR"
        ]);

        $this->a($resource, "        if (!defined('THEMES_PATH')){");
        $this->a($resource, "            \$themesDirName = 'themes';");
        $this->a($resource, "            \$themesPath = substr(__DIR__, 0, strlen(__DIR__) - strlen(APP_DIR_NAME.'/ini')).DIRECTORY_SEPARATOR.\$themesDirName;");
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

        $this->addConst($resource, [
            'name' => 'USE_HTTP',
            'summary' => 'Sets the framework to use \'http://\' or \'https://\' for base URIs.',
            'description' => "The default behaviour of the framework is to use 'https://'. But 
             * in some cases, there is a need for using 'http://'.
             * If this constant is set to true, the framework will use 'http://' for 
             * base URI of the system. Default value is false.",
            'since' => '1.0',
            'type' => 'boolean',
            'value' => "false"
        ]);
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
     * @since 1.5.1
     */
    public function createIniClass($className, $comment) {
        $cFile = new File("$className.php", ROOT_DIR.DS.APP_DIR_NAME.DS.'ini');
        $cFile->remove();
        $this->a($cFile, [
            "<?php",
            '',
            "namespace ".APP_DIR_NAME."\\ini;",
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
        $cFile->write(true, true);
        require_once ROOT_DIR.DS.APP_DIR_NAME.DS.'ini'.DS."$className.php";
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
     * @since 1.5.1
     */
    public function createRoutesClass($className) {
        $cFile = new File("$className.php", ROOT_DIR.DS.APP_DIR_NAME.DS.'ini'.DS.'routes');
        $cFile->remove();
        $this->a($cFile, "<?php");
        $this->a($cFile, "");
        $this->a($cFile, "namespace ".APP_DIR_NAME."\\ini\\routes;");
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
        $cFile->write(true, true);
        require_once $cFile->getAbsolutePath();
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
     * Returns a string that represents the name of admin theme of the web 
     * application.
     * 
     * @return string
     * 
     * @since 1.0
     */
    public function getAdminTheme() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return $c->getAdminThemeName();
        }

        return self::DEFAULT_APP_CONFIG['site']['admin-theme'];
    }
    /**
     * Returns an associative array that holds application version info.
     * 
     * @return array The array will have the following indices: 'version', 
     * 'version-type' and 'release-date'.
     * 
     * @since 1.5.2
     */
    public function getAppVersionInfo() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return [
                'version' => $c->getVersion(),
                'version-type' => $c->getVersionType(),
                'release-date' => $c->getReleaseDate()
            ];
        }

        return self::DEFAULT_APP_CONFIG['version-info'];
    }
    /**
     * Returns the base URL which is use as a value for the tag &gt;base&lt;.
     * 
     * @return string
     * 
     * @since 1.0
     */
    public function getBase() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return $c->getBaseURL();
        }

        return '';
    }
    /**
     * Returns a string that represents the name of the base theme of the web 
     * application.
     * 
     * @return string
     * 
     * @since 1.0
     */
    public function getBaseTheme() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return $c->getBaseThemeName();
        }

        return self::DEFAULT_APP_CONFIG['site']['base-theme'];
    }
    /**
     * Returns password hash of the password which is used to protect background 
     * jobs from unauthorized execution.
     * 
     * @return string Password hash or the string 'NO_PASSWORD' if there is no 
     * password.
     * 
     * @since 1.5.2
     */
    public function getCRONPassword() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return $c->getCRONPassword();
        }

        return 'NO_PASSWORD';
    }
    /**
     * Returns an array that holds database connections.
     * 
     * @return array The indices of the array are names of the connections and 
     * the value of each index is an object of type 'ConnectionInfo'.
     * 
     * @since 1.0
     */
    public function getDatabaseConnections() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return $c->getDBConnections();
        }

        return [];
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
    public function getDescriptions() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            if (WebFioriApp::getClassStatus() == WebFioriApp::STATUS_INITIALIZED) {
                return WebFioriApp::getAppConfig()->getDescriptions();
            }
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return $c->getDescriptions();
        }

        return self::DEFAULT_APP_CONFIG['site']['descriptions'];
    }
    /**
     * Returns a link that represents the home page of the web application.
     * 
     * @return string
     * 
     * @since 1.0
     */
    public function getHomePage() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return $c->getHomePage();
        }

        return null;
    }
    /**
     * Returns a string that represents primary language of the web application.
     * 
     * @return string A two characters string such as 'EN'.
     * 
     * @since 1.0
     */
    public function getPrimaryLang() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return $c->getPrimaryLanguage();
        }

        return self::DEFAULT_APP_CONFIG['site']['primary-language'];
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
     * <li><b>base-theme</b>: The name of the theme that will be used to style 
     * web site UI.</li>
     * <li><b>primary-language</b>: Primary language of the website.
     * <li><b>admin-theme</b>: The name of the theme that is used to style 
     * admin web pages.</li>
     * <li><b>descriptions</b>: A sub associative array. The index of the 
     * array will be language code (such as 'EN') and the value 
     * will be the general web site description in the given language.</li></li>
     * </ul> 
     * @return array An associative array that contains web site configuration 
     * info.
     * 
     * @since 1.0
     */
    public function getSiteConfigVars() {
        $cfgArr = self::DEFAULT_APP_CONFIG['site'];
        $cfgArr['website-names'] = $this->getWebsiteNames();
        $cfgArr['base-url'] = $this->getBase();
        $cfgArr['title-separator'] = $this->getTitleSep();
        $cfgArr['home-page'] = $this->getHomePage();
        $cfgArr['primary-language'] = $this->getHomePage();
        $cfgArr['descriptions'] = $this->getDescriptions();
        $cfgArr['titles'] = $this->getTitles();
        $cfgArr['base-theme'] = $this->getBaseTheme();
        $cfgArr['admin-theme'] = $this->getAdminTheme();

        return $cfgArr;
    }
    /**
     * Returns an array that holds SMTP connections.
     * 
     * @return array The indices of the array are names of the connections and 
     * the value of each index is an object of type 'SMTPAccount'.
     * 
     * @since 1.0
     */
    public function getSMTPAccounts() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return $c->getAccounts();
        }

        return [];
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
    public function getTitles() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            if (WebFioriApp::getClassStatus() == WebFioriApp::STATUS_INITIALIZED) {
                return WebFioriApp::getAppConfig()->getTitles();
            }
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return $c->getTitles();
        }

        return self::DEFAULT_APP_CONFIG['site']['titles'];
    }
    /**
     * Returns a string that represents the string that will be used to separate 
     * website name from page title.
     * 
     * @return string
     * 
     * @since 1.0
     */
    public function getTitleSep() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return $c->getTitleSep();
        }

        return self::DEFAULT_APP_CONFIG['site']['title-separator'];
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
    public function getWebsiteNames() {
        if (class_exists(APP_DIR_NAME.'\\AppConfig')) {
            if (WebFioriApp::getClassStatus() == WebFioriApp::STATUS_INITIALIZED) {
                return WebFioriApp::getAppConfig()->getWebsiteNames();
            }
            $constructor = '\\'.APP_DIR_NAME.'\\'.'AppConfig';
            $c = new $constructor();

            return $c->getWebsiteNames();
        }

        return self::DEFAULT_APP_CONFIG['site']['website-names'];
    }
    /**
     * Removes SMTP email account if it is exist.
     * 
     * @param string $accountName The name of the email account (such as 'no-reply').
     * 
     * @since 1.3
     */
    public function removeAccount($accountName) {
        $accounts = $this->getSMTPAccounts();

        if (isset($accounts[$accountName])) {
            unset($accounts[$accountName]);
        }
        $this->writeAppConfig([
            'smtp' => $accounts
        ]);
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
     * @since 1.4.3
     */
    public function removeDBConnection($connectionName) {
        $connections = $this->getDatabaseConnections();
        $updated = [];

        foreach ($connections as $name => $conObj) {
            if ($name != $connectionName) {
                $updated[] = $conObj;
            }
        }
        $this->writeAppConfig([
            'db-connections' => $updated
        ]);
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
     * @since 1.5.2
     */
    public function updateAppVersionInfo($vNum, $vType, $releaseDate) {
        $this->writeAppConfig([
            'version-info' => [
                'version' => $vNum,
                'version-type' => $vType,
                'release-date' => $releaseDate
            ]
        ]);
    }
    /**
     * Updates the password which is used to protect cron jobs from unauthorized 
     * execution.
     * 
     * @param string $newPass The new password. If empty string is given, the password 
     * will be set to the string 'NO_PASSWORD'.
     * 
     * @since 1.5.2
     */
    public function updateCronPassword($newPass) {
        $this->writeAppConfig([
            'cron-pass' => $newPass
        ]);
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
     * @since 1.1
     */
    public function updateOrAddEmailAccount(SMTPAccount $emailAccount) {
        $accountsArr = $this->getSMTPAccounts();
        $accountsArr[$emailAccount->getAccountName()] = $emailAccount;
        $this->writeAppConfig([
            'smtp' => $accountsArr
        ]);
    }
    /**
     * Updates web site configuration based on some attributes.
     * 
     * @param array $websiteInfoArr an associative array. The array can 
     * have the following indices: 
     * <ul>
     * <li><b>primary-lang</b>: The main display language of the website.
     * <li><b>website-names</b>: A sub associative array. The index of the 
     * array should be language code (such as 'EN') and the value 
     * should be the name of the web site in the given language.</li>
     * <li><b>title-sep</b>: A character or a string that is used 
     * to separate web site name from web page title. Two common 
     * values are '-' and '|'.</li>
     * <li><b>home-page</b>: The URL of the home page of the web site. For example, 
     * If root URL of the web site is 'https://www.example.com', This page is served 
     * when the user visits this URL.</li>
     * <li><b>base-theme</b>: The name of the theme that will be used to style 
     * web site UI.</li>
     * <li><b>admin-theme</b>: If the web site has two UIs (One for normal 
     * users and another for admins), this one 
     * can be used to serve the UI for web site admins.</li>
     * <li><b>descriptions</b>: A sub associative array. The index of the 
     * array should be language code (such as 'EN') and the value 
     * should be the general web site description in the given language.</li></li>
     * </ul> 
     * 
     * @since 1.0
     */
    public function updateSiteInfo($websiteInfoArr) {
        $this->writeAppConfig($websiteInfoArr);
    }
    /**
     * Stores configuration variables into the application configuration class.
     * 
     * @param array $appConfigArr An array that holds configuration vatiables. 
     * The array can have the following indices:
     * <ul>
     * <li><b>db-connections</b>: An array that holds objects of type 
     * 'ConnectionInfo'.</li>
     * <li><b>smtp</b>: An array that holds objects of type 'SMTPAccount' that 
     * holds SMTP connection information.</li>
     * <li><b>website-names</b>: An associative array that holds website names 
     * in different display languages. The index should be language code such 
     * as 'EN' and the value of the index is website name in that language.</li>
     * <li><b>titles</b>: An associative array that holds default page titles 
     * in different display languages. The index should be language code such 
     * as 'EN' and the value of the index is page title in that language.</li>
     * <li><b>descriptions</b>: An associative array that holds page descriptions 
     * in different display languages. The index should be language code such 
     * as 'EN' and the value of the index is page description in that language.</li>
     * <li><b>home-page</b>: A link that represents home page of the website.</li>
     * <li><b>primary-lang</b>: The primary display language of the website.</li>
     * <li><b>base-theme</b>: The name of the theme that will be used in the 
     * pages that will be shown to all users (public and private)</li>
     * <li><b>admin-theme</b>: The name of the theme that will be shown 
     * in control pages of the system.</li>
     * <li><b>cron-pass</b>: The password which is used to protect cron jobs 
     * fron unauthorized access. If empty string is given, the string 
     * 'NO_PASSWORD' is used.</li>
     * </ul>
     * 
     * @since 1.5
     */
    public function writeAppConfig($appConfigArr) {
        $cFile = new File('AppConfig.php', ROOT_DIR.DS.APP_DIR_NAME);
        $cFile->remove();

        $this->_writeAppConfigAttrs($cFile);

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


        $this->_writeCronPass($cFile, $appConfigArr);

        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Adds an email account.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * The developer can use this method to add new account during runtime.");
        $this->a($cFile, "     * The account will be removed once the program finishes.");
        $this->a($cFile, $this->docEmptyLine, 1); 
        $this->a($cFile, "     * @param SMTPAccount \$acc an object of type SMTPAccount."); 
        $this->a($cFile, $this->docEmptyLine, 1); 
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function addAccount(SMTPAccount \$acc) {");
        $this->a($cFile, "        \$this->emailAccounts[\$acc->getAccountName()] = \$acc;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Adds new database connection or updates an existing one.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @param ConnectionInfo \$connectionInfo an object of type 'ConnectionInfo'");
        $this->a($cFile, "     * that will contain connection information.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function addDbConnection(ConnectionInfo \$connectionInfo) {");
        $this->a($cFile, "        \$this->dbConnections[\$connectionInfo->getName()] = \$connectionInfo;");
        $this->a($cFile, $this->blockEnd, 1);
        
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Adds new language or update the info of existing one.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @param string \$code The code of the language such as 'EN'.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @param string \$siteName The name of the application in the given language.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @param string \$defaultTitle The default title of a web page in the given language.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @param string \$defaultDescription The default description of a web page in the given language.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since101, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function addLanguage(\$code, \$siteName, \$defaultTitle, \$defaultDescription) {");
        $this->a($cFile, "        \$this->webSiteNames[\$code] = \$siteName;");
        $this->a($cFile, "        \$this->descriptions[\$code] = \$defaultDescription;");
        $this->a($cFile, "        \$this->defaultPageTitles[\$code] = \$defaultTitle;");
        $this->a($cFile, $this->blockEnd, 1);
        
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns SMTP account given its name.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * The method will search for an account with the given name in the set");
        $this->a($cFile, "     * of added accounts. If no account was found, null is returned.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @param string \$name The name of the account.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return SMTPAccount|null If the account is found, The method");
        $this->a($cFile, "     * will return an object of type SMTPAccount. Else, the");
        $this->a($cFile, "     * method will return null.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getAccount(\$name) {");
        $this->a($cFile, "        if (isset(\$this->emailAccounts[\$name])) {");
        $this->a($cFile, "            return \$this->emailAccounts[\$name];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns an associative array that contains all email accounts.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * The indices of the array will act as the names of the accounts.");
        $this->a($cFile, "     * The value of the index will be an object of type SMTPAccount.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return array An associative array that contains all email accounts.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getAccounts() {");
        $this->a($cFile, "        return \$this->emailAccounts;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns the name of the theme that is used in admin control pages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string The name of the theme that is used in admin control pages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getAdminThemeName() {");
        $this->a($cFile, "        return \$this->adminThemeName;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns the name of base theme that is used in website pages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * Usually, this theme is used for the normally visitors of the web site.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string The name of base theme that is used in website pages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getBaseThemeName() {");
        $this->a($cFile, "        return \$this->baseThemeName;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns the base URL that is used to fetch resources.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * The return value of this method is usually used by the tag 'base'");
        $this->a($cFile, "     * of web site pages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string the base URL.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getBaseURL() {");
        $this->a($cFile, "        return \$this->baseUrl;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns version number of the configuration file.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * This value can be used to check for the compatability of configuration file");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string The version number of the configuration file.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getConfigVersion() {");
        $this->a($cFile, "        return \$this->configVision;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns sha256 hash of the password which is used to prevent unauthorized");
        $this->a($cFile, "     * access to run the jobs or access CRON web interface.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return Password hash or the string 'NO_PASSWORD' if there is no password.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @since 1.0.1");
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getCRONPassword() {");
        $this->a($cFile, "        return \$this->cronPass;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns database connection information given connection name.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @param string \$conName The name of the connection.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return ConnectionInfo|null The method will return an object of type");
        $this->a($cFile, "     * ConnectionInfo if a connection info was found for the given connection name.");
        $this->a($cFile, "     * Other than that, the method will return null.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getDBConnection(\$conName) {");
        $this->a($cFile, "        \$conns = \$this->getDBConnections();");
        $this->a($cFile, "        \$trimmed = trim(\$conName);");
        $this->a($cFile, "        ");
        $this->a($cFile, "        if (isset(\$conns[\$trimmed])) {");
        $this->a($cFile, "            return \$conns[\$trimmed];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns an associative array that contain the information of database connections.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * The keys of the array will be the name of database connection and the");
        $this->a($cFile, "     * value of each key will be an object of type ConnectionInfo.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return array An associative array.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getDBConnections() {");
        $this->a($cFile, "        return \$this->dbConnections;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns the global title of the web site that will be");
        $this->a($cFile, "     * used as default page title.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @param string \$langCode Language code such as 'AR' or 'EN'.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string|null If the title of the page");
        $this->a($cFile, "     * does exist in the given language, the method will return it.");
        $this->a($cFile, "     * If no such title, the method will return null.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getDefaultTitle(\$langCode) {");
        $this->a($cFile, "        \$langs = \$this->getTitles();");
        $this->a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        $this->a($cFile, "        ");
        $this->a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        $this->a($cFile, "            return \$langs[\$langCode];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);


        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns the global description of the web site that will be");
        $this->a($cFile, "     * used as default page description.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @param string \$langCode Language code such as 'AR' or 'EN'.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string|null If the description for the given language");
        $this->a($cFile, "     * does exist, the method will return it. If no such description, the");
        $this->a($cFile, "     * method will return null.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getDescription(\$langCode) {");
        $this->a($cFile, "        \$langs = \$this->getDescriptions();");
        $this->a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        $this->a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        $this->a($cFile, "            return \$langs[\$langCode];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns an associative array which contains different website descriptions");
        $this->a($cFile, "     * in different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * Each index will contain a language code and the value will be the description");
        $this->a($cFile, "     * of the website in the given language.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return array An associative array which contains different website descriptions");
        $this->a($cFile, "     * in different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getDescriptions() {");
        $this->a($cFile, "        return \$this->descriptions;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns the home page URL of the website.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string The home page URL of the website.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getHomePage() {");
        $this->a($cFile, "        return \$this->homePage;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns the primary language of the website.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string Language code of the primary language such as 'EN'.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getPrimaryLanguage() {");
        $this->a($cFile, "        return \$this->primaryLang;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns the date at which the application was released at.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string The method will return a string in the format");
        $this->a($cFile, "     * 'YYYY-MM-DD' that represents application release date.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getReleaseDate() {");
        $this->a($cFile, "        return \$this->appReleaseDate;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns an array that holds the default page title for different display");
        $this->a($cFile, "     * languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return array An associative array. The indices of the array are language codes");
        $this->a($cFile, "     * and the values are pages titles.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getTitles() {");
        $this->a($cFile, "        return \$this->defaultPageTitles;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns the character (or string) that is used to separate page title from website name.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string A string such as ' - ' or ' | '. Note that the method");
        $this->a($cFile, "     * will add the two spaces by default.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getTitleSep() {");
        $this->a($cFile, "        return \$this->titleSep;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns version number of the application.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string The method should return a string in the");
        $this->a($cFile, "     * form 'x.x.x.x'.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getVersion() {");
        $this->a($cFile, "        return \$this->appVestion;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns a string that represents application release type.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string The method will return a string such as");
        $this->a($cFile, "     * 'Stable', 'Alpha', 'Beta' and so on.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getVersionType() {");
        $this->a($cFile, "        return \$this->appVersionType;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns the global website name.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @param string \$langCode Language code such as 'AR' or 'EN'.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return string|null If the name of the website for the given language");
        $this->a($cFile, "     * does exist, the method will return it. If no such name, the");
        $this->a($cFile, "     * method will return null.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getWebsiteName(\$langCode) {");
        $this->a($cFile, "        \$langs = \$this->getWebsiteNames();");
        $this->a($cFile, "        \$langCodeF = strtoupper(trim(\$langCode));");
        $this->a($cFile, "        ");
        $this->a($cFile, "        if (isset(\$langs[\$langCodeF])) {");
        $this->a($cFile, "            return \$langs[\$langCode];");
        $this->a($cFile, "        }");
        $this->a($cFile, $this->blockEnd, 1);

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * Returns an array which contains different website names in different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * Each index will contain a language code and the value will be the name");
        $this->a($cFile, "     * of the website in the given language.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @return array An array which contains different website names in different languages.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    public function getWebsiteNames() {");
        $this->a($cFile, "        return \$this->webSiteNames;");
        $this->a($cFile, $this->blockEnd, 1);

        $this->_writeDbCon($cFile, $appConfigArr);
        $this->_writeSiteInfo($cFile, $appConfigArr);
        $this->_writeSmtpConn($cFile, $appConfigArr);
        $this->_writeAppVersionInfo($cFile, $appConfigArr);

        $this->a($cFile, "}");
        $cFile->write(false, true);
        require_once ROOT_DIR.DS.APP_DIR_NAME.DS.'AppConfig.php';
    }
    private function _mkdir($dir) {
        if (!is_dir($dir)) {
            mkdir($dir);
        }
    }
    private function _writeAppConfigAttrs(&$cFile) {
        $this->a($cFile, "<?php");
        $this->a($cFile, "");
        $this->a($cFile, "namespace ".APP_DIR_NAME.";");
        $this->a($cFile, "");
        $this->a($cFile, "use webfiori\\database\\ConnectionInfo;");
        $this->a($cFile, "use webfiori\\framework\\mail\\SMTPAccount;");
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
        $this->a($cFile, "    private \$appVestion;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The name of base website UI Theme.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$baseThemeName;");

        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, "     * The base URL that is used by all web site pages to fetch resource files.");
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
        $this->a($cFile, "     * Password hash of CRON sub-system.");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, "     * @var string");
        $this->a($cFile, $this->docEmptyLine, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private \$cronPass;");

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
        $this->a($cFile, "     * The character which is used to saperate site name from page title.");
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
    private function _writeAppVersionInfo(&$cFile, $appConfigArr) {
        $this->a($cFile, [
            $this->docStart,
            $this->since10,
            $this->docEnd
        ], 1);

        $this->a($cFile, "private function initVersionInfo() {", 1);

        $versionInfo = $this->getAppVersionInfo();

        if (isset($appConfigArr['version-info'])) {
            if (isset($appConfigArr['version-info']['version'])) {
                $this->a($cFile, "\$this->appVestion = '".$appConfigArr['version-info']['version']."';", 2);
            } else {
                $this->a($cFile, "\$this->appVestion = '".$versionInfo['version']."';", 2);
            }

            if (isset($appConfigArr['version-info']['version-type'])) {
                $this->a($cFile, "\$this->appVersionType = '".$appConfigArr['version-info']['version-type']."';", 2);
            } else {
                $this->a($cFile, "\$this->appVersionType = '".$versionInfo['version-type']."';", 2);
            }

            if (isset($appConfigArr['version-info']['release-date'])) {
                $this->a($cFile, "\$this->appReleaseDate = '".$appConfigArr['version-info']['release-date']."';", 2);
            } else {
                $this->a($cFile, "\$this->appReleaseDate = '".$versionInfo['release-date']."';", 2);
            }
        } else {
            $this->a($cFile, [
                "\$this->appVestion = '".$versionInfo['version']."';",
                "\$this->appVersionType = '".$versionInfo['version-type']."';",
                "\$this->appReleaseDate = '".$versionInfo['release-date']."';"
            ], 2);
        }

        $this->a($cFile, $this->blockEnd, 1);
    }
    private function _writeCronPass(&$cFile, $appConfigArr) {
        if (isset($appConfigArr['cron-pass'])) {
            if (strlen(trim($appConfigArr['cron-pass'])) == 0) {
                $this->a($cFile, "        \$this->cronPass = 'NO_PASSWORD';");
            } else {
                $this->a($cFile, "        \$this->cronPass = '".hash('sha256', $appConfigArr['cron-pass'])."';");
            }
        } else {
            $password = $this->getCRONPassword();
            $this->a($cFile, "        \$this->cronPass = '".$password."';");
        }
    }
    private function _writeDbCon(&$cFile, $appConfigArr) {
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
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
                $this->a($cFile, "            '$cName' => new ConnectionInfo('".$connObj->getDatabaseType()."',"
                        ."'".$connObj->getUsername()."', "
                        ."'".$connObj->getPassword()."', "
                        ."'".$connObj->getDBName()."', "
                        ."'".$connObj->getHost()."', "
                        ."".$connObj->getPort().", [");
                $this->a($cFile, "                'connection-name' => '".str_replace("'", "\'", $cName)."'");
                $this->a($cFile, "            ]),");
            }
        }
        $this->a($cFile, "        ];");
        $this->a($cFile, $this->blockEnd, 1);
    }
    private function _writeSiteDescriptions(&$cFile, $appConfigArr) {
        if (isset($appConfigArr['descriptions']) && gettype($appConfigArr['descriptions']) == 'array') {
            $descArr = $appConfigArr['descriptions'];
        } else {
            $descArr = $this->getDescriptions();
        }
        $this->a($cFile, "        \$this->descriptions = [");

        foreach ($descArr as $langCode => $desc) {
            $desc = str_replace("'", "\'", $desc);
            $this->a($cFile, "            '$langCode' => '$desc',");
        }
        $this->a($cFile, "        ];");
    }
    private function _writeSiteInfo(&$cFile,  $appConfigArr) {
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
        $this->a($cFile, "    private function initSiteInfo() {");

        $this->_writeSiteNames($cFile, $appConfigArr);
        $this->_writeSiteTitles($cFile, $appConfigArr);
        $this->_writeSiteDescriptions($cFile, $appConfigArr);

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
            $this->a($cFile, "        \$this->baseThemeName = \\".trim($baseTheme, '\\')."::class;");
        } else {
            $this->a($cFile, "        \$this->baseThemeName = '$baseTheme';");
        }

        if (isset($appConfigArr['admin-theme'])) {
            $adminTheme = $appConfigArr['admin-theme'];
        } else {
            $adminTheme = $this->getBaseTheme();
        }

        if (class_exists($adminTheme)) {
            $this->a($cFile, "        \$this->adminThemeName = \\".trim($adminTheme, '\\')."::class;");
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

        $this->a($cFile, $this->blockEnd, 1);
    }
    private function _writeSiteNames(&$cFile, $appConfigArr) {
        if (isset($appConfigArr['website-names']) && gettype($appConfigArr['website-names']) == 'array') {
            $wNamesArr = $appConfigArr['website-names'];
        } else {
            $wNamesArr = $this->getWebsiteNames();
        }
        $this->a($cFile, "        \$this->webSiteNames = [");

        foreach ($wNamesArr as $langCode => $name) {
            $name = str_replace("'", "\'", $name);
            $this->a($cFile, "            '$langCode' => '$name',");
        }
        $this->a($cFile, "        ];");
        $this->a($cFile, "    ");
    }
    private function _writeSiteTitles($cFile, $appConfigArr) {
        if (isset($appConfigArr['titles']) && gettype($appConfigArr['titles']) == 'array') {
            $titlesArr = $appConfigArr['titles'];
        } else {
            $titlesArr = $this->getTitles();
        }
        $this->a($cFile, "        \$this->defaultPageTitles = [");

        foreach ($titlesArr as $langCode => $title) {
            $title = str_replace("'", "\'", $title);
            $this->a($cFile, "            '$langCode' => '$title',");

            if (!class_exists(APP_DIR_NAME.'\\langs\\Language'.$langCode)) {

                //This require a fix in the future
                $dir = $langCode == 'AR' ? 'rtl' : 'ltr';

                $writer = new LangClassWriter($langCode, $dir);
                $writer->writeClass();
                require_once $writer->getAbsolutePath();
            }
        }
        $this->a($cFile, "        ];");
    }
    private function _writeSmtpConn(&$cFile, $appConfigArr) {
        $this->a($cFile, $this->docStart, 1);
        $this->a($cFile, $this->since10, 1);
        $this->a($cFile, $this->docEnd, 1);
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
     * @param File $file
     * @param type $name
     * @param type $val
     * @param type $docBlock
     */
    private function addConst($file, $options) {
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
        $this->_mkdir(ROOT_DIR.$DS.APP_DIR_NAME);
        $this->_mkdir(ROOT_DIR.$DS.APP_DIR_NAME.$DS.'ini');
        $this->_mkdir(ROOT_DIR.$DS.APP_DIR_NAME.$DS.'ini'.$DS.'routes');
        $this->_mkdir(ROOT_DIR.$DS.APP_DIR_NAME.$DS.'pages');
        $this->_mkdir(ROOT_DIR.$DS.APP_DIR_NAME.$DS.'commands');
        $this->_mkdir(ROOT_DIR.$DS.APP_DIR_NAME.$DS.'jobs');
        $this->_mkdir(ROOT_DIR.$DS.APP_DIR_NAME.$DS.'middleware');
        $this->_mkdir(ROOT_DIR.$DS.APP_DIR_NAME.$DS.'langs');
        $this->_mkdir(ROOT_DIR.$DS.APP_DIR_NAME.$DS.'sto');
        $this->_mkdir(ROOT_DIR.$DS.APP_DIR_NAME.$DS.'sto'.$DS.'uploads');
        $this->_mkdir(ROOT_DIR.$DS.APP_DIR_NAME.$DS.'sto'.$DS.'logs');
        $this->_mkdir(ROOT_DIR.$DS.APP_DIR_NAME.$DS.'sto'.$DS.'sessions');
        $this->_mkdir(ROOT_DIR.$DS.'public');
        $this->_mkdir(ROOT_DIR.$DS.'themes');
    }
}
