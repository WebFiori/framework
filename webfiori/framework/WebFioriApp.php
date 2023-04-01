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

use Error;
use Exception;
use ReflectionClass;
use webfiori\cli\Runner;
use webfiori\error\Handler;
use webfiori\file\exceptions\FileException;
use webfiori\file\File;
use webfiori\framework\scheduler\TasksManager;
use webfiori\framework\exceptions\InitializationException;
use webfiori\framework\handlers\APICallErrHandler;
use webfiori\framework\handlers\CLIErrHandler;
use webfiori\framework\handlers\HTTPErrHandler;
use webfiori\framework\middleware\AbstractMiddleware;
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
     * A constant that indicates that the status of the class is 'none'.
     * 
     * @since 1.3.7
     */
    const STATUS_NONE = 'NONE';
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
    private static $classStatus = self::STATUS_NONE;
    /**
     * 
     * @var Runner
     */
    private static $CliRunner;
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
     * @throws FileException
     * @throws InitializationException
     * @since 1.0
     */
    private function __construct() {
        $this->checkStdInOut();
        $this->initFrameworkVersionInfo();
        $this->checkAppDir();
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

        $this->initAutoLoader();
        $this->setHandlers();
        //Initialize CLI
        self::getRunner();

        $this->initAppConfig();



        $this->initThemesPath();
        $this->checkStandardLibs();

        if (!class_exists(APP_DIR.'\ini\InitPrivileges')) {
            ConfigController::get()->createIniClass('InitPrivileges', 'Initialize user groups and privileges.');
        }
        //Initialize privileges.
        //This step must be done before initializing anything.
        call_user_func(APP_DIR.'\ini\InitPrivileges::init');



        $this->initMiddleware();
        $this->initRoutes();
        $this->initScheduler();
        Response::beforeSend(function ()
        {
            register_shutdown_function(function()
            {
                SessionsManager::validateStorage();
                $uriObj = Router::getRouteUri();

                if ($uriObj !== null) {
                    foreach ($uriObj->getMiddleware() as $mw) {
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
                $uriObj->getMiddleware()->insertionSort();

                foreach ($uriObj->getMiddleware() as $mw) {
                    $mw->after(Request::get(), Response::get());
                }
            }
        });
        //class is now initialized
        self::$classStatus = self::STATUS_INITIALIZED;
    }
    /**
     * Register CLI commands or background tasks.
     * 
     * @param string $folder The name of the folder that contains the jobs or 
     * commands. It must be a folder inside 'app' folder or the folder which is defined 
     * by the constant 'APP_DIR'.
     * 
     * @param callable $regCallback A callback which is used to register the
     * classes of the folder.
     * 
     * @param string|null $suffix A string which is appended to class name.
     * For example, if class name is 'UsersTable', the suffix in this case would 
     * be 'Table' If provided, only classes with the specified suffix will 
     * be considered.
     * 
     * @param array $constructorParams An optional array that can hold constructor
     * parameters for objects that will be registered.
     * 
     * @param array $otherParams An optional array that can hold extra parameters 
     * which will be passed to the register callback.
     * 
     * @since 1.3.6
     */
    public static function autoRegister(string $folder, callable $regCallback, string $suffix = null, array $constructorParams = [], array $otherParams = []) {
        $dir = APP_PATH.$folder;

        if (!File::isDirectory($dir)) {
            //If directory is outside application folder.
            $dir = ROOT_PATH.DS.$folder;
        }

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

                    self::autoRegisterHelper([
                        'dir' => $dir, 
                        'php-file' => $phpFile, 
                        'folder' => $folder, 
                        'class-name' => $expl[0], 
                        'params' => $otherParams, 
                        'callback' => $regCallback,
                        'constructor-params' => $constructorParams
                    ]);
                }
            }
        }
    }
    /**
     * Returns the instance which is used as main application configuration class.
     * 
     * @return Config
     */
    public static function getAppConfig(): Config {
        if (self::$LC !== null) {
            return self::$LC->appConfig;
        }
        $constructor = '\\'.APP_DIR.'\\config\\AppConfig';

        return new $constructor();
    }
    /**
     * Returns a reference to an instance of 'AutoLoader'.
     * 
     * @return AutoLoader A reference to an instance of 'AutoLoader'.
     * 
     * @since 1.2.1
     */
    public static function getAutoloader(): AutoLoader {
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
     * Returns an instance which represents the class that is used to run the
     * terminal.
     *
     * @return Runner
     * @throws FileException
     */
    public static function getRunner() : Runner {
        if (!class_exists(APP_DIR.'\ini\InitCommands')) {
            ConfigController::get()->createIniClass('InitCommands', 'A method that can be used to initialize CLI commands.');
        }

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

                if (defined('ROOT_PATH')) {
                    $_SERVER['DOCUMENT_ROOT'] = ROOT_PATH;
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
            self::$CliRunner->setBeforeStart(function (Runner $r)
            {
                $commands = [
                    '\\webfiori\\framework\\cli\commands\\WHelpCommand',
                    '\\webfiori\\framework\\cli\\commands\\VersionCommand',
                    '\\webfiori\\framework\\cli\\commands\\SettingsCommand',
                    '\\webfiori\\framework\\cli\\commands\\SchedulerCommand',
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
                call_user_func(APP_DIR.'\ini\InitCommands::init');
            });
        }

        return self::$CliRunner;
    }
    /**
     * Sets the configuration object that will be used to configure part of
     * application settings.
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
     * @throws InitializationException
     * @since 1.0
     */
    public static function start(): WebFioriApp {
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
    private static function autoRegisterHelper($options) {
        $dir = $options['dir'];
        $phpFile = $options['php-file'];
        $folder = $options['folder'];
        $className = $options['class-name'];
        $otherParams = $options['params'];
        $regCallback = $options['callback'];
        $constructorParams = $options['constructor-params'];
        $instanceNs = require_once $dir.DS.$phpFile;

        if (strlen($instanceNs) == 0 || $instanceNs == 1) {
            $instanceNs = '\\'.APP_DIR.'\\'.$folder;
        }
        $class = $instanceNs.'\\'.$className;
        try {
            $reflectionClass = new ReflectionClass($class);  

            $toPass = [$reflectionClass->newInstanceArgs($constructorParams)];

            foreach ($otherParams as $param) {
                $toPass[] = $param;
            }
            call_user_func_array($regCallback, $toPass);
        } catch (Error $ex) {
        }
    }
    private function checkAppDir() {
        if (!defined('APP_DIR')) {
            /**
             * The name of the directory at which the developer will have his own application 
             * code.
             * 
             * @since 2.2.1
             */
            define('APP_DIR','app');
        }

        if (strpos(APP_DIR, ' ') !== false || strpos(APP_DIR, '-')) {
            http_response_code(500);
            die('Error: Unable to initialize the application. Invalid application directory name: "'.APP_DIR.'".');
        }
        /**
         * The absolute path to application directory.
         *
         */
        define('APP_PATH', ROOT_PATH.DIRECTORY_SEPARATOR.APP_DIR.DIRECTORY_SEPARATOR);
    }

    /**
     * Checks if framework standard libraries are loaded or not.
     *
     * If a library is missing, the method will throw an exception that tell
     * which library is missing.
     *
     * @throws InitializationException
     * @since 1.3.5
     */
    private function checkStandardLibs() {
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

    /**
     * Checks and initialize standard input and output streams.
     */
    private function checkStdInOut() {
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

    /**
     * Initialize application configuration class.
     *
     * @throws FileException If the method was not able to Initialize configuration class.
     */
    private function initAppConfig() {
        if (!class_exists(APP_DIR.'\\config\\AppConfig')) {
            ConfigController::get()->createAppConfigFile();
        }

        $constructor = '\\'.APP_DIR.'\\'.'config\\AppConfig';
        $this->appConfig = new $constructor();
        ConfigController::get()->setConfig($this->appConfig);
    }

    /**
     * @throws FileException
     * @throws Exception
     */
    private function initAutoLoader() {
        /**
         * Initialize autoloader.
         */
        if (!class_exists('webfiori\framework\AutoLoader',false)) {
            require_once WF_CORE_PATH.DS.'AutoLoader.php';
        }
        self::$AU = AutoLoader::get();

        if (!class_exists(APP_DIR.'\ini\InitAutoLoad')) {
            ConfigController::get()->createIniClass('InitAutoLoad', 'Add user-defined directories to the set of directories at which the framework will search for classes.');
        }
        call_user_func(APP_DIR.'\ini\InitAutoLoad::init');
    }

    /**
     * @throws FileException
     */
    private function initScheduler() {
        $uriObj = new RouterUri(Request::getRequestedURI(), '');
        $pathArr = $uriObj->getPathArray();

        if (!class_exists(APP_DIR.'\ini\InitTasks')) {
            ConfigController::get()->createIniClass('InitTasks', 'A method that can be used to register background tasks.');
        }

        if (Runner::isCLI() || (defined('SCHEDULER_THROUGH_HTTP') && SCHEDULER_THROUGH_HTTP && in_array('scheduler', $pathArr))) {
            if (defined('SCHEDULER_THROUGH_HTTP') && SCHEDULER_THROUGH_HTTP) {
                TasksManager::initRoutes();
            }
            TasksManager::password($this->appConfig->getSchedulerPassword());
            //initialize scheduler tasks only if in CLI or scheduler is enabled through HTTP.
            call_user_func(APP_DIR.'\ini\InitTasks::init');
            TasksManager::registerTasks();
        }
    }
    private function initFrameworkVersionInfo() {
        /**
         * A constant that represents version number of the framework.
         * 
         * @since 2.1
         */
        define('WF_VERSION', '3.0.0-RC3');
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

    /**
     * @throws FileException
     */
    private function initMiddleware() {
        WebFioriApp::autoRegister('middleware', function(AbstractMiddleware $inst)
        {
            MiddlewareManager::register($inst);
        });

        if (!class_exists(APP_DIR.'\ini\InitMiddleware')) {
            ConfigController::get()->createIniClass('InitMiddleware', 'Register middleware which are created outside the folder \'app/middleware\'.');
        }
        call_user_func(APP_DIR.'\ini\InitMiddleware::init');
    }

    /**
     * @throws FileException
     */
    private function initRoutes() {
        $routesClasses = ['APIsRoutes', 'PagesRoutes', 'ClosureRoutes', 'OtherRoutes'];

        foreach ($routesClasses as $className) {
            if (!class_exists(APP_DIR.'\\ini\\routes\\'.$className)) {
                ConfigController::get()->createRoutesClass($className);
            }
            call_user_func(APP_DIR.'\ini\routes\\'.$className.'::create');
        }

        if (Router::routesCount() != 0) {
            $home = trim(self::getAppConfig()->getHomePage());

            if (strlen($home) != 0) {
                Router::redirect('/', WebFioriApp::getAppConfig()->getHomePage());
            }
        }
    }
    private function initThemesPath() {
        if (!defined('THEMES_PATH')) {
            $themesDirName = 'themes';
            $themesPath = ROOT_PATH.DS.$themesDirName;
            /**
             * This constant represents the directory at which themes exist.
             * @since 1.0
             */
            define('THEMES_PATH', $themesPath);
        }
    }
    private function loadEnvVars() {
        if (!class_exists(APP_DIR.'\config\Env')) {
            $confControllerPath = ROOT_PATH.DIRECTORY_SEPARATOR.
                    'vendor'.DIRECTORY_SEPARATOR.
                    'webfiori'.DIRECTORY_SEPARATOR.
                    'framework'.DIRECTORY_SEPARATOR.
                    'webfiori'.DIRECTORY_SEPARATOR.
                    'framework'.DIRECTORY_SEPARATOR.
                    'ConfigController.php';

            if (!file_exists($confControllerPath)) {
                $confControllerPath = ROOT_PATH.DIRECTORY_SEPARATOR.
                        'webfiori'.DIRECTORY_SEPARATOR.
                        'framework'.DIRECTORY_SEPARATOR.
                        'ConfigController.php';
            }
            require_once $confControllerPath;
            $path = ROOT_PATH.DIRECTORY_SEPARATOR.APP_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Env.php';

            if (!file_exists($path)) {
                ConfigController::get()->createConstClass();
            }
            require_once ROOT_PATH.DIRECTORY_SEPARATOR.APP_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'Env.php';
        }
        call_user_func(APP_DIR.'\config\\Env::defineEnvVars');
    }
    /**
     * Sets new error and exception handler.
     */
    private function setHandlers() {
        error_reporting(E_ALL & ~E_ERROR & ~E_COMPILE_ERROR & ~E_CORE_ERROR & ~E_RECOVERABLE_ERROR);
        Handler::registerHandler(new CLIErrHandler());
        Handler::registerHandler(new APICallErrHandler());
        Handler::registerHandler(new HTTPErrHandler());
        Handler::unregisterHandler(Handler::getHandler('Default'));
    }
}
