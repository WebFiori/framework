<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework;

use Exception;
use WebFiori\Cli\Runner;
use WebFiori\Error\Config\HandlerConfig;
use WebFiori\Error\Handler;
use WebFiori\File\exceptions\FileException;
use WebFiori\File\File;
use WebFiori\Framework\Autoload\ClassLoader;
use WebFiori\Framework\Config\ConfigurationDriver;
use WebFiori\Framework\Config\Controller;
use WebFiori\Framework\Exceptions\InitializationException;
use WebFiori\Framework\Handlers\APICallErrHandler;
use WebFiori\Framework\Handlers\CLIErrHandler;
use WebFiori\Framework\Handlers\HTTPErrHandler;
use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Framework\Middleware\MiddlewareManager;
use WebFiori\Framework\Middleware\StartSessionMiddleware;
use WebFiori\Framework\Health;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Router\RouterUri;
use WebFiori\Framework\Scheduler\TasksManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;
use ReflectionMethod;
use WebFiori\Cache\CacheFacade;
use WebFiori\Container\Container;
use WebFiori\Container\ContainerFacade;
use WebFiori\Event\EventDispatcherFacade;
use WebFiori\Log\FileLogger;
use WebFiori\Log\Logger;
use WebFiori\Log\LoggerFacade;
use WebFiori\Log\LogLevel;
use WebFiori\Queue\FileQueueStorage;
use WebFiori\Queue\Queue;
use WebFiori\Queue\QueueFacade;
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
     * A constant that indicates that the status of the class is initiated.
     */
    const STATUS_INITIATED = 'INITIATED';
    /**
     * A constant that indicates that the status of the class is 'none'.
     *
     */
    const STATUS_NONE = 'NONE';
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
    private static $ConfigDriver = 'WebFiori\\Framework\\Config\\JsonDriver';
    /**
     * A single instance of the class.
     *
     * @var App
     *
     * @since 1.0
     */
    private static $LC;
    /**
     * Current request instance.
     *
     * @var Request
     */
    private static $Request;
    /**
     * Current response instance.
     *
     * @var Response
     */
    private static $Response;

    /**
     * The entry point for initiating the system.
     *
     * @throws FileException
     * @throws InitializationException
     * @since 1.0
     */
    private function __construct() {
        // Initialize logger
        $logDir = APP_PATH.'Storage'.DS.'Logs';
        $minLevel = (defined('WF_VERBOSE') && WF_VERBOSE === true) ? LogLevel::DEBUG : LogLevel::WARNING;
        LoggerFacade::setInstance(new FileLogger($logDir, $minLevel));

        // Initialize queue storage
        $queueDir = APP_PATH.'Storage'.DS.'Queue';
        QueueFacade::setInstance(
            new Queue(new FileQueueStorage($queueDir))
        );


        $this->checkAppDir();
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

        //Initialize Request and Response
        self::$Request = Request::createFromGlobals();
        self::$Response = new Response();

        $this->initThemesPath();

        if (!class_exists(APP_DIR.'\\Ini\\Privileges')) {
            Ini::get()->createIniClass('Privileges', 'Initialize user groups and privileges.');
        }
        //Initialize privileges.
        //This step must be done before initializing anything.
        self::call(APP_DIR.'\\Ini\\Privileges::initialize');

        $this->initMiddleware();
        $this->initListeners();
        $this->initRoutes();
        $this->initHealthCheck();
        $this->initScheduler();
        $this->initContainer();
        self::getResponse()->beforeSend(function ()
        {
            register_shutdown_function(function()
            {
                $uriObj = Router::getRouteUri();

                if ($uriObj !== null) {
                    $mdArr = $uriObj->getMiddleware();

                    for ($x = count($mdArr) - 1 ; $x >= 0  ; $x--) {
                        $mdArr[$x]->afterSend(self::getRequest(), self::getResponse());
                    }
                }
            });

            $uriObj = Router::getRouteUri();

            if ($uriObj !== null) {
                $mdArr = $uriObj->getMiddleware();

                for ($x = count($mdArr) - 1 ; $x >= 0  ; $x--) {
                    $mdArr[$x]->after(self::getRequest(), self::getResponse());
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
     * commands. It must be a folder inside [APP_DIR].
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
    public static function autoRegister(string $folder, callable $regCallback, ?string $suffix = null, array $constructorParams = [], array $otherParams = []) {
        ClassRegistrar::register($folder, $regCallback, $suffix, $constructorParams, $otherParams);
    }
    /**
     * Returns a reference to an instance of 'ClassLoader'.
     *
     * @return ClassLoader A reference to an instance of 'ClassLoader'.
     *
     * @since 1.2.1
     */
    public static function getClassLoader(): ClassLoader {
        return ClassLoader::get();
    }
    /**
     * Returns the status of the class.
     *
     * @return string The returned value will be one of 3 values: 'NONE' if
     * the constructor of the class is not called. 'INITIALIZING' if the execution
     * is happening inside the constructor of the class. 'INITIALIZED' once the
     * code in the constructor is executed.
     */
    public static function getClassStatus() : string {
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
     * Returns the current request instance.
     *
     * @return Request
     */
    public static function getRequest() : Request {
        return self::$Request;
    }
    /**
     * Returns the current response instance.
     *
     * @return Response
     */
    public static function getResponse() : Response {
        return self::$Response;
    }
    /**
     * Returns the application DI container.
     *
     * @return Container
     */
    public static function container(): Container {
        return ContainerFacade::getInstance();
    }
    /**
     * Returns the application logger instance.
     *
     * @return Logger
     */
    public static function log(): Logger {
        return LoggerFacade::getInstance();
    }
    /**
     * Returns an instance which represents the class that is used to run the
     * terminal.
     *
     * @return Runner
     * @throws FileException
     */
    public static function getRunner() : Runner {
        if (!class_exists(APP_DIR.'\Ini\Commands')) {
            Ini::get()->createIniClass('Commands', 'A method that can be used to register custom CLI commands.');
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
                    '\\WebFiori\\Framework\\Cli\\Commands\\WHelpCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\VersionCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\DownCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\UpCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\QueueStatusCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\QueueRetryCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\QueueWorkCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\SchedulerCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\SchedulerRunCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\SchedulerDaemonCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\AddDbConnectionCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\AddSmtpConnectionCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\AddLangCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\CreateMiddlewareCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\CreateTaskCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\CreateCommandCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\CreateEntityCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\CreateServiceCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\CreateTableCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\CreateRepositoryCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\CreateResourceCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\CreateMigrationCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\CreateSeederCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\RunMigrationsCommandNew',
                    '\\WebFiori\\Framework\\Cli\\Commands\\RollbackMigrationsCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\InitMigrationsCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\DryRunMigrationsCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\MigrationsStatusCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\FreshMigrationsCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\SkipMigrationsCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\StepMigrationsCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\ServicesListCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\RoutesCacheCommand',
                    '\\WebFiori\\Framework\\Cli\\Commands\\RoutesClearCommand',
                ];

                foreach ($commands as $c) {
                    $r->register(new $c());
                }
                $r->setDefaultCommand('help');
                self::call(APP_DIR.'\Ini\Commands::initialize');
            });
        }

        return self::$CliRunner;
    }
    /**
     * Handel the request.
     *
     * This method should only be called after the application has been initialized.
     * Its used to handle HTTP requests or start CLI processing.
     */
    public static function handle() {
        if (self::$ClassStatus == self::STATUS_NONE) {
            $publicFolderName = 'public';
            self::initiate('App', $publicFolderName, self::getRoot().DIRECTORY_SEPARATOR.$publicFolderName);
        }

        if (self::$ClassStatus == self::STATUS_INITIATED) {
            self::start();
        }

        if (self::$ClassStatus == self::STATUS_INITIALIZED) {
            if (App::getRunner()->isCLI() === true) {
                App::getRunner()->start();
            } else {
                //route user request.
                EventDispatcherFacade::dispatch(new Events\RequestReceived(self::getRequest()));
                Router::route(self::getRequest()->getRequestedURI());
                self::getResponse()->send();
                $duration = (microtime(true) - MICRO_START) * 1000;
                EventDispatcherFacade::dispatch(new Events\ResponseSent(self::getRequest(), self::getResponse(), $duration));
            }
        }
    }
    /**
     * Initialize global constants which has information about framework version.
     *
     * The constants which are defined by this method include the following:
     * <ul>
     * <li><b>WF_VERSION</b>: A string such as '3.0.0'.</li>
     * <li><b>WF_VERSION_TYPE</b>: Type of the release such as 'RC', 'Alpha' or 'Stable'.</li>
     * <li><b>WF_RELEASE_DATE</b>: The date at which the specified version was created at.</li>
     * </ul>
     */
    public static function initFrameworkVersionInfo() {
        AppBootstrapper::initFrameworkVersionInfo();
    }
    /**
     * Initiate main components of the application.
     *
     * This method is intended to be called in the index file of the project.
     * It should be first thing to be called.
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
    public static function initiate(string $appFolder = 'App', string $publicFolder = 'public', string $indexDir = __DIR__) {
        AppBootstrapper::boot($appFolder, $publicFolder, $indexDir);
        self::$ClassStatus = self::STATUS_INITIATED;
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
        if (self::$ClassStatus == self::STATUS_NONE || self::$ClassStatus == self::STATUS_INITIATED) {
            if (self::$LC === null) {
                self::$ClassStatus = self::STATUS_INITIALIZING;
                self::$LC = new App();
            }
        } else if (self::$ClassStatus == self::STATUS_INITIALIZING) {
            throw new InitializationException('Using the core class while it is not fully initialized.');
        }

        return self::$LC;
    }
    /**
     * Checks if a single argument matches a single reflected type.
     *
     * @param mixed $arg The argument value.
     * @param \ReflectionNamedType $type The reflected type to check against.
     *
     * @return bool
     */
    private static function argMatchesType($arg, \ReflectionNamedType $type): bool {
        $typeName = $type->getName();

        if ($arg === null) {
            return $type->allowsNull();
        }

        if ($type->isBuiltin()) {
            return match ($typeName) {
                'string' => is_string($arg),
                'int' => is_int($arg),
                'float' => is_float($arg) || is_int($arg),
                'bool' => is_bool($arg),
                'array' => is_array($arg),
                'callable' => is_callable($arg),
                'mixed' => true,
                default => false,
            };
        }

        return $arg instanceof $typeName;
    }
    /**
     * Safe function caller with CLI/web-aware exception handling.
     *
     * @param callable $func The function to call.
     * 
     * @return mixed
     */
    public static function call($func) {
        try {
            return call_user_func($func);
        } catch (Exception $ex) {
            if (self::getRunner()->isCLI()) {
                printf("WARNING: ".$ex->getMessage().' at '.$ex->getFile().':'.$ex->getLine()."\n");
            } else {
                throw new InitializationException($ex->getMessage(), $ex->getCode(), $ex);
            }
        }
    }
    /**
     * Validates and defines APP_DIR constant, checking for invalid characters.
     */
    private function checkAppDir() {
        if (!defined('APP_DIR')) {
            /**
             * The name of the directory at which the developer will have his own application
             * code.
             *
             * @since 2.2.1
             */
            define('APP_DIR','App');
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



    /**
     * @throws FileException
     */
    private function initListeners() {
        // Auto-discover listeners from App/Listeners/
        self::autoRegister('Listeners', function ($instance) {
            if (method_exists($instance, 'handle')) {
                $ref = new ReflectionMethod($instance, 'handle');
                $params = $ref->getParameters();

                if (count($params) > 0 && $params[0]->getType() !== null) {
                    $eventClass = $params[0]->getType()->getName();

                    if ($eventClass !== 'object') {
                        EventDispatcherFacade::listen($eventClass, $instance);
                    }
                }
            }
        });
    }
    private function initContainer() {
        $container = ContainerFacade::getInstance();
        $container->instance(Session\SessionManager::class, Session\SessionsManager::getInstance());
        $container->instance(Middleware\MiddlewareRegistry::class, MiddlewareManager::getInstance());
        $container->instance(Router::class, Router::getInstance());
        $container->instance(TasksManager::class, TasksManager::get());
        $container->instance(AccessManager::class, Access::getManager());
    }
    private function initMiddleware() {
        App::autoRegister('Middleware', function(AbstractMiddleware $inst)
        {
            MiddlewareManager::register($inst);
        });

        if (!class_exists(APP_DIR.'\Ini\Middleware')) {
            Ini::get()->createIniClass('Middleware', 'Register middleware which are created outside the folder \'[APP_DIR]/Middleware\'.');
        }
        MiddlewareManager::register(new StartSessionMiddleware());
        MiddlewareManager::register(new Middleware\CheckMaintenanceMode());
        self::call(APP_DIR.'\Ini\Middleware::initialize');
    }
    /**
     * @throws FileException
     */
    private function initHealthCheck() {
        // Register built-in checks
        Health\HealthCheck::register(new Health\Checks\StorageCheck());

        if (CacheFacade::isEnabled()) {
            Health\HealthCheck::register(new Health\Checks\CacheCheck());
        }

        // Auto-discover from App/Health/
        self::autoRegister('Health', function ($instance) {
            if ($instance instanceof Health\HealthCheckInterface) {
                Health\HealthCheck::register($instance);
            }
        });
    }
    private function initRoutes() {
        $routesClasses = ['APIsRoutes', 'PagesRoutes', 'ClosureRoutes', 'OtherRoutes'];

        foreach ($routesClasses as $className) {
            if (!class_exists(APP_DIR.'\\Ini\\Routes\\'.$className)) {
                Ini::get()->createRoutesClass($className);
            }
            $routesArr = self::call(APP_DIR.'\Ini\Routes\\'.$className.'::create');

            if (gettype($routesArr) == 'array') {
                foreach ($routesArr as $route) {
                    Router::addRoute($route);
                }
            }
        }

        if (Router::routesCount() != 0) {
            $home = trim(self::getConfig()->getHomePage());

            if (strlen($home) != 0) {
                Router::redirect('/', App::getConfig()->getHomePage());
            }

            // Register health check route only when app has user-defined routes
            $healthPath = defined('HEALTH_CHECK_PATH') ? HEALTH_CHECK_PATH : '/health';

            if ($healthPath !== '') {
                Router::api([
                    'path' => $healthPath,
                    'route-to' => Health\HealthCheckService::class,
                    'methods' => 'GET',
                ]);
            }
        }
    }

    /**
     * @throws FileException
     */
    private function initScheduler() {
        $uriObj = new RouterUri(self::getRequest()->getUri()->getUri(true, true), '');
        $pathArr = $uriObj->getPathArray();

        if (!class_exists(APP_DIR.'\Ini\Tasks')) {
            Ini::get()->createIniClass('Tasks', 'A method that can be used to register background tasks.');
        }

        if (Runner::isCLI() || (defined('SCHEDULER_THROUGH_HTTP') && SCHEDULER_THROUGH_HTTP && in_array('scheduler', $pathArr))) {
            TasksManager::getPassword(self::getConfig()->getSchedulerPassword());
            //initialize scheduler tasks only if in CLI or scheduler is enabled through HTTP.
            self::call(APP_DIR.'\Ini\Tasks::initialize');
            TasksManager::registerTasks();
        }
    }
    /**
     * Defines THEMES_PATH constant pointing to the themes directory.
     */
    private function initThemesPath() {
        if (!defined('THEMES_PATH')) {
            $themesDirName = 'Themes';
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
        Handler::registerHandler(new CLIErrHandler());
        // Handler::registerHandler(new APICallErrHandler());
        // Handler::registerHandler(new HTTPErrHandler());
        // Handler::unregisterHandler(Handler::getHandler('Default'));
        Handler::setConfig(HandlerConfig::createDevelopmentConfig());
    }
}
