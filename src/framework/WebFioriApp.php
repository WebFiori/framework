<?php
/*
 * The MIT License
 *
 * Copyright 2019, WebFiori Framework.
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

use app\AppConfig;
use webfiori\framework\cli\CLI;
use webfiori\framework\exceptions\InitializationException;
use webfiori\framework\middleware\MiddlewareManager;
use webfiori\framework\router\APIRoutes;
use webfiori\framework\router\ClosureRoutes;
use webfiori\framework\router\OtherRoutes;
use webfiori\framework\router\Router;
use webfiori\framework\router\RouterUri;
use webfiori\framework\router\ViewRoutes;
use webfiori\framework\session\SessionsManager;
use webfiori\framework\ui\ErrorBox;
use webfiori\framework\ui\ServerErrView;
use webfiori\http\Request;
use webfiori\http\Response;
use webfiori\ini\GlobalConstants;
use webfiori\ini\InitAutoLoad;
use webfiori\ini\InitCron;
use webfiori\ini\InitMiddleware;
use webfiori\ini\InitPrivileges;
use webfiori\json\Json;
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
 * @version 1.3.6
 */
class WebFioriApp {
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
     * An associative array that contains database connection error that might 
     * happen during initialization.
     * 
     * @var array|null 
     * 
     * @since 1.3.3
     */
    private $dbErrDetails;
    /**
     * A single instance of the class.
     * 
     * @var WebFioriApp
     * 
     * @since 1.0 
     */
    private static $LC;
    /**
     * An instance of system functions class.
     * 
     * @var ConfigController 
     * 
     * @since 1.0
     */
    private static $SF;
    /**
     * A variable to store system status. The variable will be set to true 
     * if everything is Ok.
     * 
     * @var boolean|string 
     * 
     * @since 1.0
     */
    private $sysStatus;
    /**
     * The entry point for initiating the system.
     * 
     * @since 1.0
     */
    private function __construct() {
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
        /**
         * A constant that represents version number of the framework.
         * 
         * @since 2.1
         */
        define('WF_VERSION', '2.1.0');
        /**
         * A constant that tells the type of framework version.
         * 
         * The constant can have values such as 'Alpha', 'Beta' or 'Stable'.
         * 
         * @since 2.1
         */
        define('WF_VERSION_TYPE', 'Beta 1');
        /**
         * The date at which the framework version was released.
         * 
         * The value of the constant will be a string in the format YYYY-MM-DD.
         * 
         * @since 2.1
         */
        define('WF_RELEASE_DATE', '2021-03-01');
        /**
         * Change encoding of mb_ functions to UTF-8
         */
        if (function_exists('mb_internal_encoding')) {
            $encoding = 'UTF-8';
            mb_internal_encoding($encoding);
            mb_http_output($encoding);
            mb_http_input($encoding);
            mb_regex_encoding($encoding);
        }

        if (!class_exists('webfiori\ini\GlobalConstants')) {
            require_once ROOT_DIR.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'ini'.DIRECTORY_SEPARATOR.'GlobalConstants.php';
        }
        GlobalConstants::defineConstants();

        /**
         * Set memory limit.
         */
        ini_set('memory_limit', SCRIPT_MEMORY_LIMIT);
        /**
         * See http://php.net/manual/en/timezones.php for supported time zones.
         * Change this as needed.
         */
        date_default_timezone_set(DATE_TIMEZONE);

        /**
         * Initialize autoloader.
         */
        if (!class_exists('webfiori\framework\AutoLoader',false)) {
            require_once ROOT_DIR.DS.'framework'.DS.'AutoLoader.php';
        }
        self::$AU = AutoLoader::get();
        InitAutoLoad::init();

        //Initialize CLI
        CLI::init();
        

        $this->_initThemesPath();
        $this->_setHandlers();
        $this->_checkStandardLibs();

        //Initialize privileges.
        //This step must be done before initializing any controler.
        InitPrivileges::init();

        self::$SF = ConfigController::get();

        $this->sysStatus = Util::checkSystemStatus();
        
        if ($this->sysStatus == Util::MISSING_CONF_FILE || $this->sysStatus == Util::MISSING_SITE_CONF_FILE) {
            self::$SF->createAppConfigFile();
            $this->sysStatus = Util::checkSystemStatus();
        }

        $this->appConfig = new AppConfig();
        
        if (gettype($this->sysStatus) == 'array') {
            $this->dbErrDetails = $this->sysStatus;
            $this->sysStatus = Util::DB_NEED_CONF;
        }
        WebFioriApp::autoRegister('middleware', function($inst)
        {
            MiddlewareManager::register($inst);
        });
        InitMiddleware::init();
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
                        $mw->afterTerminate();
                    }
                }
            });
            $sessionsCookiesHeaders = SessionsManager::getCookiesHeaders();

            foreach ($sessionsCookiesHeaders as $headerVal) {
                Response::addHeader('set-cookie', $headerVal);
            }
            $uriObj = Router::getRouteUri();

            if ($uriObj !== null) {
                $uriObj->getMiddlewar()->insertionSort();

                foreach ($uriObj->getMiddlewar() as $mw) {
                    $mw->after();
                }
            }
        });
        //class is now initialized
        self::$classStatus = 'INITIALIZED';

        define('INITIAL_SYS_STATUS', $this->_getSystemStatus());
    }
    /**
     * Register CLI commands or cron jobs.
     * @param string $folder The name of the folder that contains the jobs or 
     * commands. It must be a folder inside 'app' folder.
     * 
     * @param Closure $regCallback A callback which is used to register the 
     * classes of the folder.
     * 
     * @since 1.3.6
     */
    public static function autoRegister($folder, $regCallback) {
        $jobsDir = ROOT_DIR.DS.'app'.DS.$folder;

        if (Util::isDirectory($jobsDir)) {
            $dirContent = array_diff(scandir($jobsDir), ['.','..']);

            foreach ($dirContent as $phpFile) {
                $expl = explode('.', $phpFile);

                if (count($expl) == 2 && $expl[1] == 'php') {
                    $instanceNs = require_once $jobsDir.DS.$phpFile;

                    if (strlen($instanceNs) == 0 || $instanceNs == 1) {
                        $instanceNs = '';
                    }
                    $class = $instanceNs.'\\'.$expl[0];
                    try {
                        call_user_func_array($regCallback, [new $class()]);
                    } catch (\Error $ex) {
                    }
                }
            }
        }
    }
    /**
     * Initiate the framework and return a single instance of the class that can 
     * be used to control basic settings of the framework.
     * 
     * @return WebFioriApp An instance of the class.
     * 
     * @since 1.0
     */
    public static function getAndStart() {
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
    /**
     * 
     * @return AppConfig
     */
    public static function getAppConfig() {
        if (self::$LC !== null) {
            return self::$LC->appConfig;
        }

        return new AppConfig();
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
     * Returns an associative array that contains database connection error 
     * information.
     * 
     * If an error happens while connecting with the database at initialization 
     * stage, this method can be used to get error details. The array will 
     * have two indices: 'error-code' and 'error-message'.
     * 
     * @return array|null An associative array that contains database connection error 
     * information. If no errors, the method will return null.
     * 
     * @since 1.3.3
     */
    public static function getDBErrDetails() {
        return self::getAndStart()->dbErrDetails;
    }
    /**
     * Returns a reference to an instance of 'ConfigController'.
     * 
     * @return ConfigController A reference to an instance of 'ConfigController'.
     * 
     * @since 1.2.1
     */
    public static function getSysController() {
        return self::$SF;
    }
    /**
     * Returns the current status of the system.
     * 
     * @return boolean|string If the system is configured correctly, the method 
     * will return true. If the file 'Config.php' was not found, The method will return 
     * 'Util::MISSING_CONF_FILE'. If the file 'SiteConfig.php' was not found, The method will return 
     * 'Util::MISSING_CONF_FILE'. If the system is not configured yet, the method 
     * will return 'Util::NEED_CONF'. If the system is unable to connect to 
     * the database, the method will return an associative array with two 
     * indices which gives more details about the error. The first index is 
     * 'error-code' and the second one is 'error-message'.
     * 
     * @since 1.0
     */
    public static function sysStatus() {
        $retVal = self::$classStatus;

        if (self::getClassStatus() == 'INITIALIZED') {
            $retVal = self::getAndStart()->_getSystemStatus(true);
        }

        return $retVal;
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
        if (!class_exists('webfiori\collections\Node')) {
            throw new InitializationException("The standard library 'webfiori/collections' is missing.");
        }

        if (!class_exists('webfiori\ui\HTMLNode')) {
            throw new InitializationException("The standard library 'webfiori/ui' is missing.");
        }

        if (!class_exists('webfiori\json\Json')) {
            throw new InitializationException("The standard library 'webfiori/jsonx' is missing.");
        }

        if (!class_exists('webfiori\database\ResultSet')) {
            throw new InitializationException("The standard library 'webfiori/database' is missing.");
        }

        if (!class_exists('webfiori\http\Response')) {
            throw new InitializationException("The standard library 'webfiori/http' is missing.");
        }
    }
    /**
     * 
     * @param type $refresh
     * @return boolean|string
     * @since 1.0
     */
    private function _getSystemStatus($refresh = true) {
        if ($refresh === true) {
            $this->sysStatus = Util::checkSystemStatus();

            if (gettype($this->sysStatus) == 'array') {
                $this->dbErrDetails = $this->sysStatus;
                $this->sysStatus = Util::DB_NEED_CONF;
            }
        }

        return $this->sysStatus;
    }
    private function _initCRON() {
        $uriObj = new RouterUri(Util::getRequestedURL(), '');
        $pathArr = $uriObj->getPathArray();

        if (CLI::isCLI() || (defined('CRON_THROUGH_HTTP') && CRON_THROUGH_HTTP && count($pathArr) != 0 && $pathArr[0] == 'cron')) {
            //initialize cron jobs only if in CLI or cron is enabled throgh HTTP.
            //
            InitCron::init();
        }
    }
    private function _initRoutes() {
        APIRoutes::create();
        ViewRoutes::create();
        ClosureRoutes::create();
        OtherRoutes::create();
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
    private function _setErrHandler() {
        set_error_handler(function($errno, $errstr, $errfile, $errline)
        {
            $isCli = class_exists('webfiori\framework\cli\CLI') ? CLI::isCLI() : http_response_code() === false;
            Response::clear();
            $routerObj = Router::getUriObjByURL(Util::getRequestedURL());

            if ($isCli) {
                if (class_exists('webfiori\framework\cli\CLI')) {
                    CLI::displayErr($errno, $errstr, $errfile, $errline);
                } else {
                    fprintf(STDERR, "\n<%s>\n",Util::ERR_TYPES[$errno]['type']);
                    fprintf(STDERR, "Error Message    %5s %s\n",":",$errstr);
                    fprintf(STDERR, "Error Number     %5s %s\n",":",$errno);
                    fprintf(STDERR, "Error Description%5s %s\n",":",Util::ERR_TYPES[$errno]['description']);
                    fprintf(STDERR, "Error File       %5s %s\n",":",$errfile);
                    fprintf(STDERR, "Error Line:      %5s %s\n",":",$errline);
                }

                if (defined('STOP_CLI_ON_ERR') && STOP_CLI_ON_ERR === true) {
                    exit(-1);
                }
            } else if ($routerObj !== null && $routerObj->getType() == Router::API_ROUTE) {
                Response::setCode(500);
                $j = new Json([
                    'message' => $errstr,
                    'type' => Util::ERR_TYPES[$errno]['type'],
                    'description' => Util::ERR_TYPES[$errno]['description'],
                    'error-number' => $errno
                ], true);
                
                if (defined('WF_VERBOSE') && WF_VERBOSE) {
                    $j->add('file',$errfile);
                    $j->add('line',$errline);
                    $stackTrace = new Json([], true);
                    $index = 0;
                    $trace = debug_backtrace();

                    foreach ($trace as $arr) {
                        if (isset($arr['file'])) {
                            $stackTrace->add('#'.$index,$arr['file'].' (Line '.$arr['line'].')');
                        } else if (isset($arr['function'])) {
                            $stackTrace->add('#'.$index,$arr['function']);
                        }
                        $index++;
                    }
                    $j->add('stack-trace',$stackTrace);
                }
                Response::addHeader('content-type', 'application/json');
                Response::write($j);
                Response::send();
            } else {
                $errBox = new ErrorBox();
                if ($errBox->getBody() !== null) {
                    $errBox->setError($errno);
                    $errBox->setDescription($errno);
                    $errBox->setFile($errfile);
                    $errBox->setMessage($errstr);
                    $errBox->setLine($errline);
                    $errBox->setTrace();
                    Response::write($errBox);
                }
            }

            return true;
        });
    }
    private function _setExceptionHandler() {
        set_exception_handler(function($ex)
        {
            $useResponsClass = class_exists('webfiori\\http\\Response');
            $isCli = class_exists('webfiori\framework\cli\CLI') ? CLI::isCLI() : php_sapi_name() == 'cli';

            if ($useResponsClass) {
                Response::clear();
            }

            if ($isCli) {
                CLI::displayException($ex);
            } else {
                $routeUri = Router::getUriObjByURL(Util::getRequestedURL());

                if ($routeUri !== null) {
                    $routeType = $routeUri->getType();
                } else {
                    $routeType = Router::VIEW_ROUTE;
                }

                if ($routeType == Router::API_ROUTE || defined('API_CALL')) {
                    $j = new Json([
                        'message' => '500 - Server Error: Uncaught Exception.',
                        'type' => 'error',
                        'exception-class' => get_class($ex),
                        'exception-message' => $ex->getMessage(),
                        'exception-code' => $ex->getMessage()
                    ], true);

                    if (defined('WF_VERBOSE') && WF_VERBOSE) {
                        $j->add('file', $ex->getFile());
                        $j->add('line', $ex->getLine());
                        $stackTrace = new Json([], true);
                        $index = 0;
                        $trace = $ex->getTrace();

                        foreach ($trace as $arr) {
                            if (isset($arr['file'])) {
                                $stackTrace->add('#'.$index,$arr['file'].' (Line '.$arr['line'].')');
                            } else if (isset($arr['function'])) {
                                $stackTrace->add('#'.$index,$arr['function']);
                            }
                            $index++;
                        }
                        $j->add('stack-trace',$stackTrace);
                    }

                    if ($useResponsClass) {
                        Response::addHeader('content-type', 'application/json');
                        Response::write($j);
                        Response::setCode(500);
                        Response::send();
                    } else {
                        http_response_code(500);
                        header('content-type:application/json');
                        echo $j;
                    }
                } else {
                    $exceptionView = new ServerErrView($ex, $useResponsClass);
                    $exceptionView->show(500);
                }
            }
        });
    }
    /**
     * Sets new error and exception handler.
     */
    private function _setHandlers() {
        error_reporting(E_ALL & ~E_ERROR & ~E_COMPILE_ERROR & ~E_CORE_ERROR & ~E_RECOVERABLE_ERROR);
        $this->_setErrHandler();
        $this->_setExceptionHandler();
        register_shutdown_function(function()
        {
            if (!Response::isSent()) {
                $isCli = class_exists('webfiori\framework\cli\CLI') ? CLI::isCLI() : php_sapi_name() == 'cli';
                $error = error_get_last();

                if ($error !== null) {
                    Response::clear();
                    $errNo = $error['type'];

                    if ($errNo == E_WARNING || 
                       $errNo == E_NOTICE || 
                       $errNo == E_USER_ERROR || 
                       $errNo == E_USER_NOTICE) {
                        return;
                    }

                    if (!$isCli) {
                        Response::setCode(500);
                    }
                    $uri = Router::getUriObjByURL(Request::getRequestedURL());

                    if ($uri !== null) {
                        if ($uri->getType() == Router::API_ROUTE) {
                            $j = new Json([
                                'message' => $error["message"],
                                'type' => 'error',
                                'error-number' => $error["type"],
                            ], true);

                            if (defined('WF_VERBOSE') && WF_VERBOSE) {
                                $j->add('file', $error["file"]);
                                $j->add('line', $error["line"]);
                            }
                            Response::write($j);
                        } else {
                            $errPage = new ServerErrView($error);
                            $errPage->show(500);
                        }
                    } else {
                        if ($isCli) {
                            CLI::displayErr($error['type'], $error["message"], $error["file"], $error["line"]);
                        } else {
                            $errPage = new ServerErrView($error);
                            $errPage->show(500);
                        }
                    }
                }
                Response::send();
            }
        });
    }
}
