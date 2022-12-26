<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2019 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework;

use Closure;
use Error;
use webfiori\cli\Runner;
use webfiori\error\Handler;
use webfiori\file\File;
use webfiori\framework\cron\Cron;
use webfiori\framework\exceptions\InitializationException;
use webfiori\framework\handlers\APICallErrHandler;
use webfiori\framework\handlers\CLIErrHandler;
use webfiori\framework\handlers\HTTPErrHandler;
use webfiori\framework\middleware\MiddlewareManager;
use webfiori\framework\router\Router;
use webfiori\framework\router\RouterUri;
use webfiori\framework\session\SessionsManager;
use webfiori\http\Request;
use webfiori\http\Response;
/**
 * The time at which the framework was booted in microseconds as a float.
 * 
 * @since 1.1.0
 */
define('MICRO_START', microtime(true));
/**
 * The instance of this class is used to control basic settings of 
 * the framework. Also, it is the entry point of any request.
 * 
 * @author Ibrahim
 * 
 * @version 1.3.7
 */
class WebFioriApp {
    /**
     * 
     * @var Runner
     */
    private static $CliRunner;
    /**
     * A constant that indicates that the status of the class is 'none'.
     * 
     * @since 1.3.7
     */
    const STATUS_NONE = 'NONE';
    /**
     * A constant that indicates that the status of the class is 'initialized'.
     * 
     * @since 1.3.7
     */
    const STATUS_INITIALIZED = 'INITIALIZED';
    /**
     * A constant that indicates that the status of the class is 'initializing'.
     * 
     * @since 1.3.7
     */
    const STATUS_INITIALIZING = 'INITIALIZING';
    /**
     * 
     * @var Config
     */
    private $appConfig;
    /**
     * An instance of autoloader class.
     * 
     * @var AutoLoader 
     * 
     * @since 1.0
     */
    private static $AU;
    /**
     * A mutex lock to disallow class access during initialization state.
     * 
     * @var int
     * 
     * @since 1.0 
     */
    private static $classStatus = 'NONE';
    /**
     * A single instance of the class.
     * 
     * @var WebFioriApp
     * 
     * @since 1.0 
     */
    private static $LC;
    /**
     * The entry point for initiating the system.
     * 
     * @since 1.0
     */
    private function __construct() {
        $this->_checkStdInOut();
        $this->_initVersionInfo();
        $this->_checkAppDir();
        /**
         * Change encoding of mb_ functions to UTF-8
         */
        if (function_exists('mb_internal_encoding')) {
            $encoding = 'UTF-8';
            mb_internal_encoding($encoding);
            mb_http_output($encoding);
            mb_regex_encoding($encoding);
        }
        $this->loadEnvVars();
        /**
         * Set memory limit.
         */
        ini_set('memory_limit', defined('SCRIPT_MEMORY_LIMIT') ? SCRIPT_MEMORY_LIMIT : '2048M');
        /**
         * See http://php.net/manual/en/timezones.php for supported time zones.
         * Change this as needed.
         */
        date_default_timezone_set(defined('DATE_TIMEZONE') ? DATE_TIMEZONE : 'Asia/Riyadh');

        $this->_initAutoLoader();
        $this->_setHandlers();
        //Initialize CLI
        self::getRunner();
        
        $this->_initAppConfig();



        $this->_initThemesPath();
        $this->_checkStandardLibs();

        if (!class_exists(APP_DIR_NAME.'\ini\InitPrivileges')) {
            ConfigController::get()->createIniClass('InitPrivileges', 'Initialize user groups and privileges.');
        }
        //Initialize privileges.
        //This step must be done before initializing anything.
        call_user_func(APP_DIR_NAME.'\ini\InitPrivileges::init');



        $this->_initMiddleware();
        $this->_initRoutes();
        $this->_initCRON();
        Response::beforeSend(function ()
        {
            register_shutdown_function(function()
            {
                SessionsManager::validateStorage();
                $uriObj = Router::getRouteUri();

                if ($uriObj !== null) {
                    foreach ($uriObj->getMiddlewar() as $mw) {
                        $mw->afterSend(Request::get(), Response::get());
                    }
                }
            });
            try {
                $sessionsCookiesHeaders = SessionsManager::getCookiesHeaders();

                foreach ($sessionsCookiesHeaders as $headerVal) {
                    Response::addHeader('set-cookie', $headerVal);
                }
            } catch (Error $exc) {
            }

            $uriObj = Router::getRouteUri();

            if ($uriObj !== null) {
                $uriObj->getMiddlewar()->insertionSort();

                foreach ($uriObj->getMiddlewar() as $mw) {
                    $mw->after(Request::get(), Response::get());
                }
            }
        });
        //class is now initialized
        self::$classStatus = 'INITIALIZED';
    }
    /**
     * Register CLI commands or cron jobs.
     * @param string $folder The name of the folder that contains the jobs or 
     * commands. It must be a folder inside 'app' folder or the folder which is defined 
     * by the constant 'APP_DIR_NAME'.
     * 
     * @param Closure $regCallback A callback which is used to register the 
     * classes of the folder.
     * 
     * @param string|null $suffix A string which is appended to class name.
     * For example, if class name is 'UsersTable', the suffix in this case would 
     * be 'Table' If provided, only classes with the specified suffix will 
     * be considered.
     * 
     * @param array $otherParams An optional array that can hold extra parameters 
     * which will be passed to the register callback.
     * 
     * @since 1.3.6
     */
    public static function autoRegister($folder, $regCallback, $suffix = null, array $otherParams = []) {
        $dir = ROOT_DIR.DS.APP_DIR_NAME.DS.$folder;

        if (File::isDirectory($dir)) {
            $dirContent = array_diff(scandir($dir), ['.','..']);

            //Since it will be used to build class namespace, each
            //backslash must be replaced with forward slash.
            $folder = str_replace('/', '\\', $folder);

            foreach ($dirContent as $phpFile) {
                $expl = explode('.', $phpFile);

                if (count($expl) == 2 && $expl[1] == 'php') {
                    if ($suffix !== null) {
                        $classSuffix = substr($expl[0], -1 * strlen($suffix));

                        if ($classSuffix !== $suffix) {
                            continue;
                        }
                    }

                    self::_autoRegisterHelper([
                        'dir' => $dir, 
                        'php-file' => $phpFile, 
                        'folder' => $folder, 
                        'class-name' => $expl[0], 
                        'params' => $otherParams, 
                        'callback' => $regCallback
                    ]);
                }
            }
        }
    }
    private static function _autoRegisterHelper($options) {
        $dir = $options['dir'];
        $phpFile = $options['php-file'];
        $folder = $options['folder'];
        $className = $options['class-name'];
        $otherParams = $options['params'];
        $regCallback = $options['callback'];
        $instanceNs = require_once $dir.DS.$phpFile;

        if (strlen($instanceNs) == 0 || $instanceNs == 1) {
            $instanceNs = '\\'.APP_DIR_NAME.'\\'.$folder;
        }
        $class = $instanceNs.'\\'.$className;
        try {
            $toPass = [new $class()];

            foreach ($otherParams as $param) {
                $toPass[] = $param;
            }
            call_user_func_array($regCallback, $toPass);
        } catch (Error $ex) {
        }
    }
    /**
     * Returns the instance which is used as main application configuration class.
     * 
     * @return Config
     */
    public static function getAppConfig() {
        if (self::$LC !== null) {
            return self::$LC->appConfig;
        }
        $constructor = '\\'.APP_DIR_NAME.'\\config\\AppConfig';

        return new $constructor();
    }
    /**
     * Returns a reference to an instance of 'AutoLoader'.
     * 
     * @return AutoLoader A reference to an instance of 'AutoLoader'.
     * 
     * @since 1.2.1
     */
    public static function getAutoloader() {
        return self::$AU;
    }
    /**
     * Returns the status of the class.
     * 
     * @return string The returned value will be one of 3 values: 'NONE' if 
     * the constructor of the class is not called. 'INITIALIZING' if the execution 
     * is happening inside the constructor of the class. 'INITIALIZED' once the 
     * code in the constructor is executed.
     * 
     * @since 1.0
     */
    public static function getClassStatus() {
        return self::$classStatus;
    }
    /**
     * Sets the configuration object that will be used to configure some of the 
     * framework settings.
     * 
     * @param Config $conf
     * 
     * @since 2.1.0
     */
    public static function setConfig(Config $conf) {
        if (self::$LC) {
            self::$LC->appConfig = $conf;
        }
    }
    /**
     * Start your WebFiori application.
     * 
     * @return WebFioriApp An instance of the class.
     * 
     * @since 1.0
     */
    public static function start() {
        if (self::$classStatus == 'NONE') {
            if (self::$LC === null) {
                self::$classStatus = 'INITIALIZING';
                self::$LC = new WebFioriApp();
            }
        } else if (self::$classStatus == 'INITIALIZING') {
            throw new InitializationException('Using the core class while it is not fully initialized.');
        }

        return self::$LC;
    }
    private function _checkAppDir() {
        if (!defined('APP_DIR_NAME')) {
            /**
             * The name of the directory at which the developer will have his own application 
             * code.
             * 
             * @since 2.2.1
             */
            define('APP_DIR_NAME','app');
        }

        if (strpos(APP_DIR_NAME, ' ') !== false || strpos(APP_DIR_NAME, '-')) {
            http_response_code(500);
            die('Error: Unable to initialize the application. Invalid application directory name: "'.APP_DIR_NAME.'".');
        }
    }
    /**
     * Checks if framework standard libraries are loaded or not.
     * 
     * If a library is missing, the method will throw an exception that tell 
     * which library is missing.
     * 
     * @since 1.3.5
     */
    private function _checkStandardLibs() {
        $standardLibsClasses = [
            'webfiori/collections' => 'webfiori\\collections\\Node',
            'webfiori/ui' => 'webfiori\\ui\\HTMLNode',
            'webfiori/jsonx' => 'webfiori\\json\\Json',
            'webfiori/database' => 'webfiori\\database\\ResultSet',
            'webfiori/http' => 'webfiori\\http\\Response',
            'webfiori/file' => 'webfiori\\file\\File',
            'webfiori/mailer' => 'webfiori\\email\\SMTPAccount',
            'webfiori/cli' => 'webfiori\\cli\\CLICommand',
            'webfiori/err' => 'webfiori\\error\\ErrorHandlerException'
        ];

        foreach ($standardLibsClasses as $lib => $class) {
            if (!class_exists($class)) {
                throw new InitializationException("The standard library '$lib' is missing.");
            }
        }
    }
    private function _checkStdInOut() {
        /*
         * first, check for php streams if they are open or not.
         */
        if (!defined('STDIN')) {
            /**
             * A constant that represents standard input stream of PHP.
             * 
             * The value of the constant is a 'resource' which can be used with 
             * all file related PHP functions.
             * 
             */
            define('STDIN', fopen('php://stdin', 'r'));
        }

        if (!defined('STDOUT')) {
            /**
             * A constant that represents standard output stream of PHP.
             * 
             * The value of the constant is a 'resource' which can be used with 
             * all file related PHP functions.
             */
            define('STDOUT', fopen('php://stdout', 'w'));
        }

        if (!defined('STDERR')) {
            /**
             * A constant that represents standard error output stream of PHP.
             * 
             * The value of the constant is a 'resource' which can be used with 
             * all file related PHP functions.
             * 
             */
            define('STDERR',fopen('php://stderr', 'w'));
        }
    }
    private function _initAppConfig() {

        if (!class_exists(APP_DIR_NAME.'\\config\\AppConfig')) {
            ConfigController::get()->createAppConfigFile();
        }

        $constructor = '\\'.APP_DIR_NAME.'\\'.'config\\AppConfig';
        $this->appConfig = new $constructor();
        ConfigController::get()->setConfig($this->appConfig);
    }
    private function _initAutoLoader() {
        /**
         * Initialize autoloader.
         */
        if (!class_exists('webfiori\framework\AutoLoader',false)) {
            require_once WF_CORE_PATH.DS.'AutoLoader.php';
        }
        self::$AU = AutoLoader::get();

        if (!class_exists(APP_DIR_NAME.'\ini\InitAutoLoad')) {
            ConfigController::get()->createIniClass('InitAutoLoad', 'Add user-defined directories to the set of directories at which the framework will search for classes.');
        }
        call_user_func(APP_DIR_NAME.'\ini\InitAutoLoad::init');
    }
    private function _initCRON() {
        $uriObj = new RouterUri(Request::getRequestedURI(), '');
        $pathArr = $uriObj->getPathArray();

        if (!class_exists(APP_DIR_NAME.'\ini\InitCron')) {
            ConfigController::get()->createIniClass('InitCron', 'A method that can be used to initialize cron jobs.');
        }

        if (Runner::isCLI() || (defined('CRON_THROUGH_HTTP') && CRON_THROUGH_HTTP && in_array('cron', $pathArr))) {
            if (defined('CRON_THROUGH_HTTP') && CRON_THROUGH_HTTP) {
                Cron::initRoutes();
            }
            Cron::password($this->appConfig->getCRONPassword());
            //initialize cron jobs only if in CLI or cron is enabled throgh HTTP.
            call_user_func(APP_DIR_NAME.'\ini\InitCron::init');
            Cron::registerJobs();
        }
    }
    /**
     * Returns an instance which represents the class that is used to run the
     * terminal.
     * 
     * @return Runner
     */
    public static function getRunner() : Runner {
        if (self::$CliRunner === null) {
            self::$CliRunner = new Runner();
            if (Runner::isCLI()) {
                if (defined('CLI_HTTP_HOST')) {
                    $host = CLI_HTTP_HOST;
                } else {
                    $host = '127.0.0.1';
                    define('CLI_HTTP_HOST', $host);
                }
                $_SERVER['HTTP_HOST'] = $host;
                $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

                if (defined('ROOT_DIR')) {
                    $_SERVER['DOCUMENT_ROOT'] = ROOT_DIR;
                }
                $_SERVER['REQUEST_URI'] = '/';
                putenv('HTTP_HOST='.$host);
                putenv('REQUEST_URI=/');

                if (defined('USE_HTTP') && USE_HTTP === true) {
                    $_SERVER['HTTPS'] = 'no';
                } else {
                    $_SERVER['HTTPS'] = 'yes';
                }
            }
            self::$CliRunner->setBeforeStart(function (Runner $r) {
                $commands = [
                    '\\webfiori\\framework\\cli\commands\\WHelpCommand',
                    '\\webfiori\\framework\\cli\\commands\\VersionCommand',
                    '\\webfiori\\framework\\cli\\commands\\SettingsCommand',
                    '\\webfiori\\framework\\cli\\commands\\CronCommand',
                    '\\webfiori\\framework\\cli\\commands\\CreateCommand',
                    '\\webfiori\\framework\\cli\\commands\\AddCommand',
                    '\\webfiori\\framework\\cli\\commands\\ListRoutesCommand',
                    '\\webfiori\\framework\\cli\\commands\\ListThemesCommand',
                    '\\webfiori\\framework\\cli\\commands\\RunSQLQueryCommand',
                    '\\webfiori\\framework\\cli\\commands\\UpdateSettingsCommand',
                    '\\webfiori\\framework\\cli\\commands\\UpdateTableCommand',
                ];
                foreach ($commands as $c) {
                    $r->register(new $c());
                }
                $r->setDefaultCommand('help');
            });
        }
        return self::$CliRunner;
    }
    private function _initMiddleware() {
        WebFioriApp::autoRegister('middleware', function($inst)
        {
            MiddlewareManager::register($inst);
        });

        if (!class_exists(APP_DIR_NAME.'\ini\InitMiddleware')) {
            ConfigController::get()->createIniClass('InitMiddleware', 'Register middleware which are created outside the folder \'app/middleware\'.');
        }
        call_user_func(APP_DIR_NAME.'\ini\InitMiddleware::init');
    }
    private function _initRoutes() {
        $routesClasses = ['APIsRoutes', 'PagesRoutes', 'ClosureRoutes', 'OtherRoutes'];

        foreach ($routesClasses as $className) {
            if (!class_exists(APP_DIR_NAME.'\\ini\\routes\\'.$className)) {
                ConfigController::get()->createRoutesClass($className);
            }
            call_user_func(APP_DIR_NAME.'\ini\routes\\'.$className.'::create');
        }

        if (Router::routesCount() != 0) {
            $home = trim(self::getAppConfig()->getHomePage());

            if (strlen($home) != 0) {
                Router::redirect('/', WebFioriApp::getAppConfig()->getHomePage());
            }
        }
    }
    private function _initThemesPath() {
        if (!defined('THEMES_PATH')) {
            $themesDirName = 'themes';
            $themesPath = ROOT_DIR.DS.$themesDirName;
            /**
             * This constant represents the directory at which themes exist.
             * @since 1.0
             */
            define('THEMES_PATH', $themesPath);
        }
    }
    private function _initVersionInfo() {
        /**
         * A constant that represents version number of the framework.
         * 
         * @since 2.1
         */
        define('WF_VERSION', '3.0.0-RC1');
        /**
         * A constant that tells the type of framework version.
         * 
         * The constant can have values such as 'Alpha', 'Beta' or 'Stable'.
         * 
         * @since 2.1
         */
        define('WF_VERSION_TYPE', 'Beta');
        /**
         * The date at which the framework version was released.
         * 
         * The value of the constant will be a string in the format YYYY-MM-DD.
         * 
         * @since 2.1
         */
        define('WF_RELEASE_DATE', '2022-12-25');
    }
    private function loadEnvVars() {
        if (!class_exists(APP_DIR_NAME.'\config\Env')) {
            $confControllerPath = ROOT_DIR.DIRECTORY_SEPARATOR.
                    'vendor'.DIRECTORY_SEPARATOR.
                    'webfiori'.DIRECTORY_SEPARATOR.
                    'framework'.DIRECTORY_SEPARATOR.
                    'webfiori'.DIRECTORY_SEPARATOR.
                    'framework'.DIRECTORY_SEPARATOR.
                    'ConfigController.php';

            if (!file_exists($confControllerPath)) {
                $confControllerPath = ROOT_DIR.DIRECTORY_SEPARATOR.
                        'webfiori'.DIRECTORY_SEPARATOR.
                        'framework'.DIRECTORY_SEPARATOR.
                        'ConfigController.php';
            }
            require_once $confControllerPath;
            $path = ROOT_DIR.DIRECTORY_SEPARATOR.APP_DIR_NAME.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Env.php';

            if (!file_exists($path)) {
                ConfigController::get()->createConstClass();
            }
            require_once ROOT_DIR.DIRECTORY_SEPARATOR.APP_DIR_NAME.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Env.php';
        }
        call_user_func(APP_DIR_NAME.'\config\\Env::defineEnvVars');
    }
    
    private function setHandler() {
        Handler::registerHandler(new CLIErrHandler());
        Handler::registerHandler(new APICallErrHandler());
        Handler::registerHandler(new HTTPErrHandler());
        Handler::unregisterHandler(Handler::getHandler('Default'));
    }
    /**
     * Sets new error and exception handler.
     */
    private function _setHandlers() {
        error_reporting(E_ALL & ~E_ERROR & ~E_COMPILE_ERROR & ~E_CORE_ERROR & ~E_RECOVERABLE_ERROR);
        $this->setHandler();
    }
}
