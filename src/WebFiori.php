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
namespace webfiori;
use webfiori\conf\Config;
use webfiori\conf\SiteConfig;
use webfiori\conf\MailConfig;
use webfiori\ini\InitPrivileges;
use webfiori\ini\InitAutoLoad;
use webfiori\ini\InitCron;
use webfiori\logic\ConfigController;
use webfiori\logic\WebsiteController;
use webfiori\logic\EmailController;
use webfiori\entity\AutoLoader;
use webfiori\entity\Util;
use webfiori\entity\ErrorBox;
use webfiori\entity\router\APIRoutes;
use webfiori\entity\router\ViewRoutes;
use webfiori\entity\router\ClosureRoutes;
use webfiori\entity\router\OtherRoutes;
use webfiori\entity\router\Router;
use jsonx\JsonX;
use webfiori\entity\CLI;
use Exception;
/**
 * The time at which the framework was booted in microseconds as a float.
 */
define('MICRO_START', microtime(true));
/**
 * The instance of this class is used to control basic settings of 
 * the framework. Also, it is the entry point of any request.
 * @author Ibrahim
 * @version 1.3.4
 */
class WebFiori{
    /**
     * An associative array that contains database connection error that might 
     * happen during initialization.
     * @var array|null 
     * @since 1.3.3
     */
    private $dbErrDetails;
    /**
     * A variable to store system status. The variable will be set to true 
     * if everything is Ok.
     * @var boolean|string 
     * @since 1.0
     */
    private $sysStatus;
    /**
     * An instance of autoloader class.
     * @var AutoLoader 
     * @since 1.0
     */
    private static $AU;
    /**
     * An instance of system functions class.
     * @var ConfigController 
     * @since 1.0
     */
    private static $SF;
    /**
     * An instance of web site functions class.
     * @var WebsiteController 
     * @since 1.0
     */
    private static $WF;
    /**
     * An instance of basic mail functions class.
     * @var EmailController 
     * @since 1.0
     */
    private static $BMF;
    /**
     * A single instance of the class.
     * @var WebFiori
     * @since 1.0 
     */
    private static $LC;
    /**
     * A mutex lock to disallow class access during initialization state.
     * @var int
     * @since 1.0 
     */
    private static $classStatus = 'NONE';
    /**
     * Initiate the framework and return a single instance of the class that can 
     * be used to update some settings.
     * The stages for initializing the framework are as follows:
     * <ul>
     * <li>Setting the encoding to UTF-8 for the 'mb' functions.</li>
     * <li>Setting memory limit to 2GB per script. Developer can change this if he wants.</li>
     * <li>Setting time zone. Default is set to 'Asia/Riyadh' For supported 
     * time zones, see <a target="_blank" href="http://php.net/manual/en/timezones.php">http://php.net/manual/en/timezones.php</a>.</li>
     * <li>Creating the constant ROOT_DIR.</li>
     * <li>Loading the auto loader class.</li>
     * <li>Initializing user-defined autoload directories.</li>
     * <li>Creating an instance of ConfigController, WebsiteController and EmailController.</li>
     * <li>Initializing routes.</li>
     * <li>Checking system status (database connection and configuration status)</li>
     * <li>Initializing CRON jobs.</li>
     * <li>Initializing privileges.</li>
     * <li>Setting a custom errors and exceptions handler.</li>
     * <li>Finally, routing if system configuration status is not 
     * equal to false. If it is false, A message will be displayed to tell 
     * the developer how do configure it.</li>
     * </ul>
     * @return WebFiori An instance of the class.
     * @since 1.0
     */
    public static function getAndStart(){
        if(self::$classStatus == 'NONE'){
            if(self::$LC === null){
                self::$classStatus = 'INITIALIZING';
                self::$LC = new WebFiori();
            }
        }
        else if(self::$classStatus == 'INITIALIZING'){
            throw new Exception('Using the core class while it is not fully initialized.');
        }
        return self::$LC;
    }
    /**
     * Returns the status of the class.
     * @return string The returned value will be one of 3 values: 'NONE' if 
     * the constructor of the class is not called. 'INITIALIZING' if the execution 
     * is happening inside the constructor of the class. 'INITIALIZED' once the 
     * code in the constructor is executed.
     * @since 1.0
     */
    public static function getClassStatus() {
        return self::$classStatus;
    }
    /**
     * The entry point for initiating the system.
     * @since 1.0
     */
    private function __construct() {
        /*
         * first, check for php streams if they are open or not.
         */
        if(!defined('STDIN')){
            define('STDIN', fopen('php://stdin', 'r'));
        }
        if(!defined('STDOUT')){
            define('STDOUT', fopen('php://stdout', 'w'));
        }
        if(!defined('STDERR')){
            define('STDERR',fopen('php://stderr', 'w'));
        }
        /**
         * Change encoding of mb_ functions to UTF-8
         */
        if(function_exists('mb_internal_encoding')){
            mb_internal_encoding('UTF-8');
            mb_http_output('UTF-8');
            mb_http_input('UTF-8');
            mb_regex_encoding('UTF-8');
        }
        /**
         * Set memory limit to 2GB per script
         */
        ini_set('memory_limit', '2048M');
        /**
         * See http://php.net/manual/en/timezones.php for supported time zones.
         * Change this as needed.
         */
        date_default_timezone_set('Asia/Riyadh');
        /**
         * The root directory that is used to load all other required system files.
         */
        if(!defined('ROOT_DIR')){
            define('ROOT_DIR',__DIR__);
        }

        /**
         * Fallback for older php versions that does not
         * support the constant PHP_INT_MIN
         */
        if(!defined('PHP_INT_MIN')){
            define('PHP_INT_MIN', ~PHP_INT_MAX);
        }

        /**
         * Initialize autoloader.
         */
        if(!class_exists('webfiori\entity\AutoLoader',false)){
           require_once ROOT_DIR.DIRECTORY_SEPARATOR.'entity'.DIRECTORY_SEPARATOR.'AutoLoader.php';
        }
        self::$AU = AutoLoader::get();
        InitAutoLoad::init();
        CLI::init();
        $this->_setHandlers();
        self::$SF = ConfigController::get();
        self::$WF = WebsiteController::get();
        self::$BMF = EmailController::get();
        
        $this->sysStatus = Util::checkSystemStatus(true);
        if($this->sysStatus == Util::MISSING_CONF_FILE || $this->sysStatus == Util::MISSING_SITE_CONF_FILE){
            self::$SF->createConfigFile();
            self::$WF->createSiteConfigFile();
            self::$BMF->createEmailConfigFile();
            $this->sysStatus = Util::checkSystemStatus(true);
        }
        if(gettype($this->sysStatus) == 'array'){
            $this->dbErrDetails = $this->sysStatus;
            $this->sysStatus = Util::DB_NEED_CONF;
        }
        
        //Initialize routes.
        APIRoutes::create();
        ViewRoutes::create();
        ClosureRoutes::create();
        OtherRoutes::create();
        
        //initialize cron and privileges...
        InitCron::init();
        InitPrivileges::init();
        
        //class is now initialized
        self::$classStatus = 'INITIALIZED';
        
        define('INITIAL_SYS_STATUS', $this->_getSystemStatus());
        if(php_sapi_name() != 'cli'){
            if(INITIAL_SYS_STATUS === true){
                
            }
            else if(INITIAL_SYS_STATUS == Util::DB_NEED_CONF){
                //??
            }
            else{
                //you can modify this part to make 
                //it do something else in case system 
                //configuration is not equal to true

                //change system config status to configured.
                //WebFiori::getSysController()->configured(true);

                //show error message to tell the developer how to configure the system.
                $this->_needConfigration();
            }
        }
    }
    /**
     * Sets new error and exception handler.
     */
    private function _setHandlers(){
        error_reporting(E_ALL & ~E_ERROR & ~E_COMPILE_ERROR & ~E_CORE_ERROR & ~E_RECOVERABLE_ERROR);
        set_error_handler(function($errno, $errstr, $errfile, $errline){
            if(php_sapi_name() == 'cli'){
                fprintf(STDERR, "\n<%s>\n",Util::ERR_TYPES[$errno]['type']);
                fprintf(STDERR, "Error Message    %5s %s\n",":",$errstr);
                fprintf(STDERR, "Error Number     %5s %s\n",":",$errno);
                fprintf(STDERR, "Error Description%5s %s\n",":",Util::ERR_TYPES[$errno]['description']);
                fprintf(STDERR, "Error File       %5s %s\n",":",$errfile);
                fprintf(STDERR, "Error Line:      %5s %s\n",":",$errline);
            }
            else{
                if(defined('API_CALL')){
                    header("HTTP/1.1 500 Server Error");
                    $j = new JsonX();
                    $j->add('message',$errstr);
                    $j->add('type',Util::ERR_TYPES[$errno]['type']);
                    $j->add('description', Util::ERR_TYPES[$errno]['description']);
                    $j->add('error-number',$errno);
                    $j->add('file',$errfile);
                    $j->add('line',$errline);
                    header('content-type: application/json');
                    die($j);
                }
                else{
                    $errBox = new ErrorBox();
                    $errBox->setError($errno);
                    $errBox->setDescription($errno);
                    $errBox->setFile($errfile);
                    $errBox->setMessage($errstr);
                    $errBox->setLine($errline);
                    echo $errBox;
                }
            }
            return true;
        });
        set_exception_handler(function($ex){
            if(php_sapi_name() == 'cli'){
                fprintf(STDERR, "\n<%s>\n","Uncaught Exception.");
                fprintf(STDERR, "Exception Message %5s %s\n",":",$ex->getMessage());
                fprintf(STDERR, "Exception Code    %5s %s\n",":",$ex->getMessage());
                fprintf(STDERR, "File              %5s %s\n",":",$ex->getFile());
                fprintf(STDERR, "Line              %5s %s\n",":",$ex->getLine());
                fprintf(STDERR, "Stack Trace:\n%s",$ex->getTraceAsString());
            }
            else{
                header("HTTP/1.1 500 Server Error");
                $routeUri = Router::getUriObjByURL(Util::getRequestedURL());
                if($routeUri !== null){
                    $routeType = $routeUri->getType();
                }
                else{
                    $routeType = Router::VIEW_ROUTE;
                }
                if($routeType == Router::API_ROUTE){
                    $j = new JsonX();
                    $j->add('message','500 - Server Error: Uncaught Exception.');
                    $j->add('type','error');
                    $j->add('exception-message',$ex->getMessage());
                    $j->add('exception-code',$ex->getMessage());
                    $j->add('file',$ex->getFile());
                    $j->add('line',$ex->getLine());
                    $stackTrace = new JsonX();
                    $index = 0;
                    $trace = $ex->getTrace();
                    foreach ($trace as $arr){
                        if(isset($arr['file'])){
                            $stackTrace->add('#'.$index,$arr['file'].' (Line '.$arr['line'].')');
                        }
                        else if(isset ($arr['function'])){
                            $stackTrace->add('#'.$index,$arr['function']);
                        }
                        $index++;
                    }
                    $j->add('stack-trace',$stackTrace);
                    header('content-type: application/json');
                    die($j);
                }
                else{
                    die(''
                    . '<!DOCTYPE html>'
                    . '<html>'
                    . '<head>'
                    . '<style>'
                    . '.nice-red{'
                    . 'color:#ff6666;'
                    . '}'
                    . '.mono{'
                    . 'font-family:monospace;'
                    . '}'
                    . '</style>'
                    . '<title>Uncaught Exception</title>'
                    . '</head>'
                    . '<body style="color:white;background-color:#1a000d;">'
                    . '<h1 style="color:#ff4d4d">500 - Server Error: Uncaught Exception.</h1>'
                    . '<hr>'
                    . '<p>'
                    .'<b class="nice-red mono">Exception Message:</b> <span class="mono">'.$ex->getMessage()."</span><br/>"
                    .'<b class="nice-red mono">Exception Code:</b> <span class="mono">'.$ex->getCode()."</span><br/>"
                    .'<b class="nice-red mono">File:</b> <span class="mono">'.$ex->getFile()."</span><br/>"
                    .'<b class="nice-red mono">Line:</b> <span class="mono">'.$ex->getLine()."</span><br>"
                    .'<b class="nice-red mono">Stack Trace:</b> '."<br/>"
                    . '</p>'
                    . '<pre>'.$ex->getTraceAsString().'</pre>'
                    . '</body>'
                    . '</html>');
                }
            }
        });
        register_shutdown_function(function(){
            $error = error_get_last();
            if($error !== null) {
                $errNo = $error['type'];
                if($errNo == E_WARNING || 
                   $errNo == E_NOTICE || 
                   $errNo == E_USER_ERROR || 
                   $errNo == E_USER_NOTICE){
                    return;
                }
                header("HTTP/1.1 500 Server Error");
                if(defined('API_CALL')){
                    $j = new JsonX();
                    $j->add('message',$error["message"]);
                    $j->add('type','error');
                    $j->add('error-number',$error["type"]);
                    $j->add('file',$error["file"]);
                    $j->add('line',$error["line"]);
                    die($j);
                }
                else{
                    die(''
                    . '<!DOCTYPE html>'
                    . '<html>'
                    . '<head>'
                    . '<style>'
                    . '.nice-red{'
                    . 'color:#ff6666;'
                    . '}'
                    . '.mono{'
                    . 'font-family:monospace;'
                    . '}'
                    . '</style>'
                    . '<title>Server Error - 500</title>'
                    . '</head>'
                    . '<body style="color:white;background-color:#1a000d;">'
                    . '<h1 style="color:#ff4d4d">500 - Server Error</h1>'
                    . '<hr>'
                    . '<p>'
                    .'<b class="nice-red mono">Type:</b> <span class="mono">'.Util::ERR_TYPES[$error["type"]]['type']."</span><br/>"
                    .'<b class="nice-red mono">Description:</b> <span class="mono">'.Util::ERR_TYPES[$error["type"]]['description']."</span><br/>"
                    .'<b class="nice-red mono">Message:</b> <span class="mono">'.$error["message"]."</span><br>"
                    .'<b class="nice-red mono">File:</b> <span class="mono">'.$error["file"]."</span><br/>"
                    .'<b class="nice-red mono">Line:</b> <span class="mono">'.$error["line"]."</span><br/>" 
                    . '</p>'
                    . '</body>'
                    . '</html>');
                }
            }
        });
        
    }

    /**
     * Returns an instance of the class 'Config'.
     * The class will contain some of framework settings in addition to 
     * database connection information.
     * @return Config|null If class file is exist and the class is loaded, 
     * an object of type 'Config' is returned. Other than that, the method 
     * will return null.
     * @since 1.3.3
     */
    public static function getConfig() {
        if(class_exists('webfiori\conf\Config')){
            return Config::get();
        }
        $n = null;
        return $n;
    }
    /**
     * Returns an instance of the class 'SiteConfig'.
     * The class will contain website settings such as main language and theme.
     * @return SiteConfig|null If class file is exist and the class is loaded, 
     * an object of type 'SiteConfig' is returned. Other than that, the method 
     * will return null.
     * @since 1.3.3
     */
    public static function getSiteConfig() {
        if(class_exists('webfiori\conf\SiteConfig')){
            return SiteConfig::get();
        }
        $n = null;
        return $n;
    }
    /**
     * Returns an instance of the class 'MailConfig'.
     * The class will contain SMTP accounts information.
     * @return MailConfig|null If class file is exist and the class is loaded, 
     * an object of type 'MailConfig' is returned. Other than that, the method 
     * will return null.
     * @since 1.3.3
     */
    public static function getMailConfig() {
        if(class_exists('webfiori\conf\MailConfig')){
            return MailConfig::get();
        }
        $n = null;
        return $n;
    }
    /**
     * Returns an associative array that contains database connection error 
     * information.
     * If an error happens while connecting with the database at initialization 
     * stage, this method can be used to get error details. The array will 
     * have two indices: 'error-code' and 'error-message'.
     * @return array|null An associative array that contains database connection error 
     * information. If no errors, the method will return null.
     * @since 1.3.3
     */
    public static function getDBErrDetails(){
        return self::getAndStart()->dbErrDetails;
    }
    /**
     * Returns a reference to an instance of 'AutoLoader'.
     * @return AutoLoader A reference to an instance of 'AutoLoader'.
     * @since 1.2.1
     */
    public static function getAutoloader() {
        return self::$AU;
    }
    /**
     * Returns a reference to an instance of 'EmailController'.
     * @return EmailController A reference to an instance of 'EmailController'.
     * @since 1.2.1
     */
    public static function getEmailController() {
        return self::$BMF;
    }
    /**
     * Returns a reference to an instance of 'ConfigController'.
     * @return ConfigController A reference to an instance of 'ConfigController'.
     * @since 1.2.1
     */
    public static function getSysController(){
        return self::$SF;
    }
    /**
     * Returns a reference to an instance of 'WebsiteController'.
     * @return WebsiteController A reference to an instance of 'WebsiteController'.
     * @since 1.2.1
     */
    public static function getWebsiteController() {
        return self::$WF;
    }
    /**
     * Returns the current status of the system.
     * @return boolean|string If the system is configured correctly, the method 
     * will return true. If the file 'Config.php' was not found, The method will return 
     * 'Util::MISSING_CONF_FILE'. If the file 'SiteConfig.php' was not found, The method will return 
     * 'Util::MISSING_CONF_FILE'. If the system is not configured yet, the method 
     * will return 'Util::NEED_CONF'. If the system is unable to connect to 
     * the database, the method will return an associative array with two 
     * indices which gives more details about the error. The first index is 
     * 'error-code' and the second one is 'error-message'.
     * @since 1.0
     */
    public static function sysStatus(){
        $retVal = self::$classStatus;
        if(self::getClassStatus() == 'INITIALIZED'){
            $retVal = self::getAndStart()->_getSystemStatus(true);
        }
        return $retVal;
    }
    /**
     * 
     * @param type $refresh
     * @return boolean|string
     * @since 1.0
     */
    private function _getSystemStatus($refresh=true,$testDb=true) {
        if($refresh === true){
            $this->sysStatus = Util::checkSystemStatus($testDb);
            if(gettype($this->sysStatus) == 'array'){
                $this->dbErrDetails = $this->sysStatus;
                $this->sysStatus = Util::DB_NEED_CONF;
            }
        }
        return $this->sysStatus;
    }
    /**
     * Show an error message that tells the user about system status and how to 
     * configure it.
     * @since 1.0
     */
    private function _needConfigration(){
        header('HTTP/1.1 503 Service Unavailable');
        $routeUri = Router::getUriObjByURL(Util::getRequestedURL());
        if($routeUri !== null){
            $routeType = $routeUri->getType();
        }
        else{
            $routeType = Router::VIEW_ROUTE;
        }
        if($routeType == Router::API_ROUTE){
            header('content-type:application/json');
            $j = new JsonX();
            $j->add('message', '503 - Service Unavailable');
            $j->add('type', 'error');
            $j->add('description','This error means that the system is not configured yet. '
                    . 'Make sure to make the method Config::isConfig() return true. '
                    . 'One way is to go to the file "conf/Config.php". Change attribute "isConfigured" value to true. '
                    . 'Or Use the method ConfigController::configured(true). You must supply \'true\' as an attribute. '
                    . 'If you want to make the system do something else if the return value of the '
                    . 'given method is false, then open the file \'WebFiori.php\' and '
                    . 'change the code in the \'else\' code block at the end of class constructor. (Inside the "if" block).');
            $j->add('powered-by', 'WebFiori Framework v'.Config::getVersion().' ('.Config::getVersionType().')');
            die($j);
        }
        else{
            die(''
            . '<!DOCTYPE html>'
            . '<html>'
            . '<head>'
            . '<title>Service Unavailable</title>'
            . '</head>'
            . '<body>'
            . '<h1>503 - Service Unavailable</h1>'
            . '<hr>'
            . '<p>'
            . 'This error means that the system is not configured yet. '
            . 'Make sure to make the method <a target="_blank" href="https://programmingacademia.com/webfiori/docs/webfiori/conf/Config#isConfig">Config::isConfig()</a> return true. There are two ways '
            . 'to change return value of this method:'
            . '</p>'
            . '<ul>'
            . '<li>Go to the file "conf/Config.php". Change attribute "isConfigured" value to true.</li>'
            . '<li>Use the method <a target="_blank" href="https://programmingacademia.com/webfiori/docs/webfiori/logic/ConfigController#configured">ConfigController::configured</a>(true). You must supply \'true\' as an attribute.</li>'
            . '<li>After that, reload the page and the system will work.</li>'
            . '</ul>'
            . '<p>'
            . 'If you want to make the system do something else if the return value of the '
            . 'given method is false, then open the file \'WebFiori.php\' and '
            . 'change the code in the \'else\' code block at the end of class constructor (Inside the "if" block).'
            . '</p>'
            . '<p>System Powerd By: <a href="https://github.com/usernane/webfiori" target="_blank"><b>'
                    . 'WebFiori Framework v'.Config::getVersion().' ('.Config::getVersionType().')'
                    . '</b></a></p>'
            . '</body>'
            . '</html>');
        }
    }
    /**
     * Show an error message that tells the user about system status and how to 
     * configure it.
     * @since 1.0
     */
    public static function configErr() {
        WebFiori::getAndStart()->_needConfigration();
    }
}
//start the system
WebFiori::getAndStart();
if(CLI::isCLI() === true){
    CLI::runCLI();
}
else{
    //route user request.
    Router::route(Util::getRequestedURL());
}
