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
use webfiori\framework\autoload\ClassLoader;
use webfiori\framework\config\ConfigurationDriver;
use webfiori\framework\config\Controller;
use webfiori\framework\exceptions\InitializationException;
use webfiori\framework\handlers\APICallErrHandler;
use webfiori\framework\handlers\CLIErrHandler;
use webfiori\framework\handlers\HTTPErrHandler;
use webfiori\framework\middleware\AbstractMiddleware;
use webfiori\framework\middleware\CacheMiddleware;
use webfiori\framework\middleware\MiddlewareManager;
use webfiori\framework\middleware\StartSessionMiddleware;
use webfiori\framework\router\Router;
use webfiori\framework\router\RouterUri;
use webfiori\framework\scheduler\TasksManager;
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
 * the application. Also, it is the entry point of the application.
 *
 * @author Ibrahim
 *
 */
class App {
    /**
     * A constant that indicates that the status of the class is 'initialized'.
     *
     */
    const STATUS_INITIALIZED = 'INITIALIZED';
    /**
     * A constant that indicates that the status of the class is 'initializing'.
     *
     */
    const STATUS_INITIALIZING = 'INITIALIZING';
    /**
     * A constant that indicates that the status of the class is 'none'.
     *
     */
    const STATUS_NONE = 'NONE';
    /**
     * An instance of autoloader class.
     *
     * @var ClassLoader
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
    private static $ClassStatus = self::STATUS_NONE;
    /**
     *
     * @var Runner
     */
    private static $CliRunner;
    /**
     * A string which points to the class that represents configuration driver.
     *
     * @var string
     */
    private static $ConfigDriver = 'webfiori\\framework\\config\\JsonDriver';
    /**
     * A single instance of the class.
     *
     * @var App
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
        self::initAutoLoader();
        $this->setHandlers();
        Controller::get()->updateEnv();
        /**
         * Set memory limit.
         */
        ini_set('memory_limit', defined('SCRIPT_MEMORY_LIMIT') ? SCRIPT_MEMORY_LIMIT : '2048M');
        /**
         * See http://php.net/manual/en/timezones.php for supported time zones.
         * Change this as needed.
         */
        date_default_timezone_set(defined('DATE_TIMEZONE') ? DATE_TIMEZONE : 'Asia/Riyadh');


        //Initialize CLI
        self::getRunner();

        $this->initThemesPath();
        $this->checkStandardLibs();

        if (!class_exists(APP_DIR.'\ini\InitPrivileges')) {
            Ini::get()->createIniClass('InitPrivileges', 'Initialize user groups and privileges.');
        }
        //Initialize privileges.
        //This step must be done before initializing anything.
        self::call(APP_DIR.'\ini\InitPrivileges::init');



        $this->initMiddleware();
        $this->initRoutes();
        $this->initScheduler();
        Response::beforeSend(function ()
        {
            register_shutdown_function(function()
            {
                $uriObj = Router::getRouteUri();

                if ($uriObj !== null) {
                    foreach ($uriObj->getMiddleware() as $mw) {
                        $mw->afterSend(Request::get(), Response::get());
                    }
                }
            });

            $uriObj = Router::getRouteUri();

            if ($uriObj !== null) {
                $uriObj->getMiddleware()->insertionSort();

                foreach ($uriObj->getMiddleware() as $mw) {
                    $mw->after(Request::get(), Response::get());
                }
            }
        });
        //class is now initialized
        self::$ClassStatus = self::STATUS_INITIALIZED;
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
     * Returns a reference to an instance of 'ClassLoader'.
     *
     * @return ClassLoader A reference to an instance of 'ClassLoader'.
     *
     * @since 1.2.1
     */
    public static function getClassLoader(): ClassLoader {
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
        return self::$ClassStatus;
    }
    /**
     * Returns the instance which is used as main application configuration class.
     *
     * @return ConfigurationDriver
     */
    public static function getConfig(): ConfigurationDriver {
        $driver = Controller::getDriver();

        if (get_class($driver) != self::$ConfigDriver) {
            Controller::setDriver(new self::$ConfigDriver());
            Controller::get()->updateEnv();
            $driver = Controller::getDriver();
        }

        return $driver;
    }
    /**
     * Returns the class that represents configuration driver.
     *
     * @return string  The full name of the class including namespace.
     */
    public static function getConfigDriver() : string {
        return self::$ConfigDriver;
    }
    /**
     * Handel the request.
     * 
     * This method should only be called after the application has been initialized.
     * Its used to handle HTTP requests or start CLI processing.
     */
    public static function handle() {
        if (self::$ClassStatus == self::STATUS_INITIALIZED) {
            if (App::getRunner()->isCLI() === true) {
                App::getRunner()->start();
            } else {
               //route user request.
               MiddlewareManager::register(new StartSessionMiddleware());
               MiddlewareManager::register(new CacheMiddleware());
               Router::route(Request::getRequestedURI());
               Response::send();
            }
        }
    }
    /**
     * Initiate main environment variables which are used by the framework.
     * 
     * This method is intended to be called in the index file of the project.
     * 
     * @param string $appFolder The name of the folder at which the application
     * is created at.
     * 
     * @param string $publicFolder A string that represent the name of the public
     * folder such as 'public'.
     * 
     * @param string $indexDir The directory at which index file exist at.
     * Usually, its the value of the constant __DIR__.
     */
    public static function initiate(string $appFolder, string $publicFolder = 'public', string $indexDir = __DIR__) {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', substr($indexDir,0, strlen($indexDir) - strlen(DS.$publicFolder)));
        }
        if (!defined('APP_DIR')) {
            define('APP_DIR', $appFolder);
        }
        if (!defined('APP_PATH')) {
            define('APP_PATH', ROOT_PATH.DIRECTORY_SEPARATOR.APP_DIR.DS);
        }
        if (!defined('WF_CORE_PATH')) {
            define('WF_CORE_PATH', ROOT_PATH.DS.'vendor\webfiori\framework\webfiori\framework');
        }
        self::initAutoLoader();
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
            Ini::get()->createIniClass('InitCommands', 'A method that can be used to initialize CLI commands.');
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
                self::call(APP_DIR.'\ini\InitCommands::init');
            });
        }

        return self::$CliRunner;
    }
    /**
     * Sets the class that will be used as configuration driver.
     *
     * This method must be used before calling the method 'App::start()' in order
     * to set proper configuration driver.
     *
     * @param string $clazz The full name of the class including namespace.
     */
    public static function setConfigDriver(string $clazz) {
        self::$ConfigDriver = $clazz;
    }

    /**
     * Start your WebFiori application.
     *
     * @return App An instance of the class.
     *
     * @throws InitializationException
     * @since 1.0
     */
    public static function start(): App {
        if (self::$ClassStatus == 'NONE') {
            if (self::$LC === null) {
                self::$ClassStatus = 'INITIALIZING';
                self::$LC = new App();
            }
        } else if (self::$ClassStatus == 'INITIALIZING') {
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
    private static function call($func) {
        try {
            call_user_func($func);
        } catch (Exception $ex) {
            if (self::getRunner()->isCLI()) {
                printf("WARNING: ".$ex->getMessage().' at '.$ex->getFile().':'.$ex->getLine()."\n");
            }
        }
    }
    private function checkAppDir() {
        if (!defined('DS')) {
            /**
             * Directory separator.
             */
            define('DS', DIRECTORY_SEPARATOR);
        }

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

        if (!defined('APP_PATH')) {
            /**
             * The absolute path to application directory.
             *
             * @var string
             */
            define('APP_PATH', ROOT_PATH.DIRECTORY_SEPARATOR.APP_DIR.DIRECTORY_SEPARATOR);
        }
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
            'webfiori/err' => 'webfiori\\error\\ErrorHandlerException',
            'webfiori/cache' => 'webfiori\\cache\\Cache'
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
     * @throws FileException
     * @throws Exception
     */
    private static function initAutoLoader() {
        /**
         * Initialize autoloader.
         */
        if (!class_exists('webfiori\framework\autoload\ClassLoader',false)) {
            require_once WF_CORE_PATH.DIRECTORY_SEPARATOR.'autoload'.DIRECTORY_SEPARATOR.'ClassLoader.php';
        }
        self::$AU = ClassLoader::get();

        if (!class_exists(APP_DIR.'\ini\InitAutoLoad')) {
            Ini::createAppDirs();
            Ini::get()->createIniClass('InitAutoLoad', 'Add user-defined directories to the set of directories at which the framework will search for classes.');
        }
        self::call(APP_DIR.'\ini\InitAutoLoad::init');
    }
    private function initFrameworkVersionInfo() {
        /**
         * A constant that represents version number of the framework.
         *
         * @since 2.1
         */
        define('WF_VERSION', '3.0.0-Beta.18');
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
        define('WF_RELEASE_DATE', '2024-12-04');
    }

    /**
     * @throws FileException
     */
    private function initMiddleware() {
        App::autoRegister('middleware', function(AbstractMiddleware $inst)
        {
            MiddlewareManager::register($inst);
        });

        if (!class_exists(APP_DIR.'\ini\InitMiddleware')) {
            Ini::get()->createIniClass('InitMiddleware', 'Register middleware which are created outside the folder \'[APP_DIR]/middleware\'.');
        }
        self::call(APP_DIR.'\ini\InitMiddleware::init');
    }
    /**
     * @throws FileException
     */
    private function initRoutes() {
        $routesClasses = ['APIsRoutes', 'PagesRoutes', 'ClosureRoutes', 'OtherRoutes'];

        foreach ($routesClasses as $className) {
            if (!class_exists(APP_DIR.'\\ini\\routes\\'.$className)) {
                Ini::get()->createRoutesClass($className);
            }
            self::call(APP_DIR.'\ini\routes\\'.$className.'::create');
        }

        if (Router::routesCount() != 0) {
            $home = trim(self::getConfig()->getHomePage());

            if (strlen($home) != 0) {
                Router::redirect('/', App::getConfig()->getHomePage());
            }
        }
    }

    /**
     * @throws FileException
     */
    private function initScheduler() {
        $uriObj = new RouterUri(Request::getRequestedURI(), '');
        $pathArr = $uriObj->getPathArray();

        if (!class_exists(APP_DIR.'\ini\InitTasks')) {
            Ini::get()->createIniClass('InitTasks', 'A method that can be used to register background tasks.');
        }

        if (Runner::isCLI() || (defined('SCHEDULER_THROUGH_HTTP') && SCHEDULER_THROUGH_HTTP && in_array('scheduler', $pathArr))) {
            if (defined('SCHEDULER_THROUGH_HTTP') && SCHEDULER_THROUGH_HTTP) {
                TasksManager::initRoutes();
            }
            TasksManager::getPassword(self::getConfig()->getSchedulerPassword());
            //initialize scheduler tasks only if in CLI or scheduler is enabled through HTTP.
            self::call(APP_DIR.'\ini\InitTasks::init');
            TasksManager::registerTasks();
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
