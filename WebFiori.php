<?php
/*
 * The MIT License
 *
 * Copyright 2018 ibrah.
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
use webfiori\functions\SystemFunctions;
use webfiori\functions\WebsiteFunctions;
use webfiori\functions\BasicMailFunctions;
use webfiori\entity\AutoLoader;
use webfiori\entity\Logger;
use webfiori\entity\Util;
use webfiori\entity\router\APIRoutes;
use webfiori\entity\router\ViewRoutes;
use webfiori\entity\router\ClosureRoutes;
use webfiori\entity\router\OtherRoutes;
use webfiori\entity\router\Router;
use jsonx\JsonX;
use Exception;
/**
 * The instance of this class is used to control basic settings of 
 * the framework. Also, it is the entry point of any request.
 * @author Ibrahim
 * @version 1.3.3
 */
class WebFiori{
    /**
     * An associative array that contains database connection error that might 
     * happen during initialization.
     * @var array|NULL 
     * @since 1.3.3
     */
    private $dbErrDetails;
    /**
     * A variable to store system status. The variable will be set to TRUE 
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
    private $AU;
    /**
     * An instance of system functions class.
     * @var SystemFunctions 
     * @since 1.0
     */
    private $SF;
    /**
     * An instance of web site functions class.
     * @var WebsiteFunctions 
     * @since 1.0
     */
    private $WF;
    /**
     * An instance of basic mail functions class.
     * @var BasicMailFunctions 
     * @since 1.0
     */
    private $BMF;
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
     * @return WebFiori An instance of the class.
     * @since 1.0
     */
    public static function &getAndStart(){
        if(self::$classStatus == 'NONE'){
            if(self::$LC === NULL){
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
        Logger::logFuncCall(__METHOD__);
        Logger::logReturnValue(self::$classStatus);
        Logger::logFuncReturn(__METHOD__);
        return self::$classStatus;
    }
    /**
     * The entry point for initiating the system.
     * @since 1.0
     */
    private function __construct() {
        if(function_exists('mb_internal_encoding')){
            mb_internal_encoding('UTF-8');
            mb_http_output('UTF-8');
            mb_http_input('UTF-8');
            mb_regex_encoding('UTF-8');
        }
        /**
         * Set memory limit to 2GB
         */
        ini_set('memory_limit', '2048M');
        /**
         * See http://php.net/manual/en/timezones.php for supported time zones
         */
        date_default_timezone_set('Asia/Riyadh');
        /**
         * The root directory that is used to load all other required system files.
         */
        define('ROOT_DIR',__DIR__);

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
        require_once ROOT_DIR.'/entity/AutoLoader.php';
        $this->AU = AutoLoader::get();
        $this->initAutoloadDirectories();
        
        //uncomment next line to show runtime errors and warnings
        //also enable logging for info, warnings and errors 
        Logger::logName('initialization-log');
        //Logger::enabled(TRUE);
        Logger::clear();
        
        //display PHP warnings and errors
        Util::displayErrors();

        //enable logging of debug info.
        define('DEBUG', '');
        
        $this->SF = SystemFunctions::get();
        $this->WF = WebsiteFunctions::get();
        $this->BMF = BasicMailFunctions::get();
        
        $this->sysStatus = Util::checkSystemStatus(TRUE);
        
        $this->initRoutes();
        if($this->sysStatus == Util::MISSING_CONF_FILE || $this->sysStatus == Util::MISSING_SITE_CONF_FILE){
            Logger::log('One or more configuration file is missing. Attempting to create all configuration files.', 'warning');
            $this->SF->createConfigFile();
            $this->WF->createSiteConfigFile();
            $this->BMF->createEmailConfigFile();
            $this->sysStatus = Util::checkSystemStatus(TRUE);
            
            //supply TRUE to check database connection
            //$this->sysStatus = Util::checkSystemStatus(TRUE);
        }
        if(gettype($this->sysStatus) == 'array'){
            $this->dbErrDetails = $this->sysStatus;
            $this->sysStatus = Util::DB_NEED_CONF;
        }
        //initialize some settings...
        Logger::log('Initializing cron jobs...');
        $this->initCron();
        Logger::log('Initializing permissions...');
        $this->initPermissions();
        Logger::log('Setting Error Handler...');
        set_error_handler(function($errno, $errstr, $errfile, $errline){
            header("HTTP/1.1 500 Server Error");
            if(defined('API_CALL')){
                $j = new JsonX();
                $j->add('message',$errstr);
                $j->add('type','error');
                $j->add('error-number',$errno);
                $j->add('file',$errfile);
                $j->add('line',$errline);
                die($j);
            }
            //let php handle the error since it is not API call.
            return FALSE;
        });
        Logger::log('Setting exceptions handler...');
        set_exception_handler(function($ex){
            header("HTTP/1.1 500 Server Error");
            if(defined('API_CALL')){
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
                    $stackTrace->add('#'.$index,$arr['file'].' (Line '.$arr['line'].')');
                    $index++;
                }
                $j->add('stack-trace',$stackTrace);
                die($j);
            }
            else{
                die(''
                . '<!DOCTYPE html>'
                . '<html>'
                . '<head>'
                . '<title>Uncaught Exception</title>'
                . '</head>'
                . '<body>'
                . '<h1>500 - Server Error: Uncaught Exception.</h1>'
                . '<hr>'
                . '<p>'
                .'<b>Exception Message:</b> '.$ex->getMessage()."<br/>"
                .'<b>Exception Code:</b> '.$ex->getCode()."<br/>"
                .'<b>File:</b> '.$ex->getFile()."<br/>"
                .'<b>Line:</b> '.$ex->getLine()."<br>"
                .'<b>Stack Trace:</b> '."<br/>"
                . '</p>'
                . '<pre>'.$ex->getTraceAsString().'</pre>'
                . '</body>'
                . '</html>');
            }
        });
        Logger::log('Initializing completed.');
        
        //switch to the log file 'system-log.txt'.
        Logger::logName('system-log');
        Logger::section();
        self::$classStatus = 'INITIALIZED';
    }
    /**
     * Returns an instance of the class 'Config'.
     * The class will contain some of framework settings in addition to 
     * database connection information.
     * @return Config|NULL If class file is exist and the class is loaded, 
     * an object of type 'Config' is returned. Other than that, the method 
     * will return NULL.
     * @since 1.3.3
     */
    public static function &getConfig() {
        if(class_exists('webfiori\conf\Config')){
            return Config::get();
        }
        $n = NULL;
        return $n;
    }
    /**
     * Returns an instance of the class 'SiteConfig'.
     * The class will contain website settings such as main language and theme.
     * @return SiteConfig|NULL If class file is exist and the class is loaded, 
     * an object of type 'SiteConfig' is returned. Other than that, the method 
     * will return NULL.
     * @since 1.3.3
     */
    public static function &getSiteConfig() {
        if(class_exists('webfiori\conf\SiteConfig')){
            return SiteConfig::get();
        }
        $n = NULL;
        return $n;
    }
    /**
     * Returns an instance of the class 'MailConfig'.
     * The class will contain SMTP accounts information.
     * @return MailConfig|NULL If class file is exist and the class is loaded, 
     * an object of type 'MailConfig' is returned. Other than that, the method 
     * will return NULL.
     * @since 1.3.3
     */
    public static function &getMailConfig() {
        if(class_exists('webfiori\conf\MailConfig')){
            return MailConfig::get();
        }
        $n = NULL;
        return $n;
    }
    /**
     * Returns an associative array that contains database connection error 
     * information.
     * If an error happens while connecting with the database at initialization 
     * stage, this method can be used to get error details. The array will 
     * have two indices: 'error-code' and 'error-message'.
     * @return array|NULL An associative array that contains database connection error 
     * information. If no errors, the method will return NULL.
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
    public static function &getAutoloader() {
        return self::getAndStart()->AU;
    }
    /**
     * Returns a reference to an instance of 'BasicMailFunctions'.
     * @return BasicMailFunctions A reference to an instance of 'BasicMailFunctions'.
     * @since 1.2.1
     */
    public static function &getBasicMailFunctions() {
        return self::getAndStart()->BMF;
    }
    /**
     * Returns a reference to an instance of 'SystemFunctions'.
     * @return SystemFunctions A reference to an instance of 'SystemFunctions'.
     * @since 1.2.1
     */
    public static function &getSysFunctions(){
        return self::getAndStart()->SF;
    }
    /**
     * Returns a reference to an instance of 'WebsiteFunctions'.
     * @return WebsiteFunctions A reference to an instance of 'WebsiteFunctions'.
     * @since 1.2.1
     */
    public static function &getWebsiteFunctions() {
        return self::getAndStart()->WF;
    }
    /**
     * Returns the current status of the system.
     * @return boolean|string If the system is configured correctly, the method 
     * will return TRUE. If the file 'Config.php' was not found, The method will return 
     * 'Util::MISSING_CONF_FILE'. If the file 'SiteConfig.php' was not found, The method will return 
     * 'Util::MISSING_CONF_FILE'. If the system is not configured yet, the method 
     * will return 'Util::NEED_CONF'. If the system is unable to connect to 
     * the database, the method will return an associative array with two 
     * indices which gives more details about the error. The first index is 
     * 'error-code' and the second one is 'error-message'.
     * @since 1.0
     */
    public static function sysStatus(){
        Logger::logFuncCall(__METHOD__);
        $retVal = self::$classStatus;
        if(self::getClassStatus() == 'INITIALIZED'){
            $retVal = self::getAndStart()->getSystemStatus(TRUE);
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * 
     * @param type $refresh
     * @return boolean|string
     * @since 1.0
     */
    private function getSystemStatus($refresh=true,$testDb=false) {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Refresh status = '.$refresh, 'debug');
        if($refresh === TRUE){
            Logger::log('Updating system status.');
            $this->sysStatus = Util::checkSystemStatus($testDb);
        }
        Logger::logReturnValue($this->sysStatus);
        Logger::logFuncReturn(__METHOD__);
        return $this->sysStatus;
    }
    /**
     * Initialize routes.
     * This method will call 4 methods in 4 classes:
     * <ul>
     * <li>APIRoutes::create()</li>
     * <li>ViewRoutes::create()</li>
     * <li>ClosureRoutes::create()</li>
     * <li>OtherRoutes::create()</li>
     * </ul>
     * The developer can create routes inside the body of any of the 4 methods.
     * @since 1.0
     */
    public function initRoutes(){
        Logger::logFuncCall(__METHOD__);
        if(self::getClassStatus() == 'INITIALIZING'){
            Logger::log('Initializing routes...', 'info', 'initialization-log');
            APIRoutes::create();
            ViewRoutes::create();
            ClosureRoutes::create();
            OtherRoutes::create();
            Logger::log('Routes initialization completed.', 'info', 'initialization-log');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Initialize the directories at which the framework will try to load 
     * classes from. 
     * If the user has created new folder inside the root framework directory, 
     * this method will add the directories to include in search directories.
     * @since 1.2.1
     */
    public function initAutoloadDirectories(){
        if(self::getClassStatus()== 'INITIALIZING'){
            InitAutoLoad::init();
        }
    }
    /**
     * Initialize user groups and permissions.
     * This method will call the method InitPermissions::init() to initialize  
     * user groups and privileges.
     * @since 1.3.1
     */
    public function initPermissions(){
        Logger::logFuncCall(__METHOD__);
        if(self::getClassStatus() == 'INITIALIZING'){
            InitPrivileges::init();
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Initialize cron jobs.
     * This method will call the method InitCron::init() to initialize cron 
     * Jobs.
     * @since 1.3
     */
    public function initCron(){
        Logger::logFuncCall(__METHOD__);
        if(self::getClassStatus()== 'INITIALIZING'){
            InitCron::init();
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Show an error message that tells the user about system status and how to 
     * configure it.
     * @since 1.0
     */
    private function needConfigration(){
        Logger::logFuncCall(__METHOD__, 'initialization-log');
        Logger::requestCompleted();
        header('HTTP/1.1 503 Service Unavailable');
        if(defined('API_CALL')){
            header('content-type:application/json');
            $j = new JsonX();
            $j->add('message', '503 - Service Unavailable');
            $j->add('type', 'error');
            $j->add('description','This error means that the system is not configured yet. '
                    . 'Make sure to make the method Config::isConfig() return TRUE. '
                    . 'One way is to go to the file "ini/Config.php". Change attribute value at line 92 to TRUE. '
                    . 'Or Use the method SystemFunctions::configured(TRUE). You must supply \'TRUE\' as an attribute.');
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
            . 'Make sure to make the method Config::isConfig() return TRUE. There are two ways '
            . 'to change return value of this method:'
            . '<ul>'
            . '<li>Go to the file "ini/Config.php". Change attribute value at line 92 to TRUE.</li>'
            . '<li>Use the method SystemFunctions::configured(TRUE). You must supply \'TRUE\' as an attribute.</li>'
            . '</ul>'
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
        WebFiori::getAndStart()->needConfigration();
    }
}

//start the system
WebFiori::getAndStart();
define('INITIAL_SYS_STATUS',WebFiori::sysStatus());
Logger::log('INITIAL_SYS_STATUS = '.INITIAL_SYS_STATUS, 'debug');
if(INITIAL_SYS_STATUS === TRUE){
    Router::route(Util::getRequestedURL());
}
else if(INITIAL_SYS_STATUS == Util::DB_NEED_CONF){
    Logger::log('Unable to connect to database.', 'warning');
    Router::route(Util::getRequestedURL());
    Logger::requestCompleted();
}
else{
    WebFiori::configErr();
}
