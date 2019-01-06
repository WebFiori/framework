<?php
namespace webfiori;
use webfiori\entity\AutoLoader;
use webfiori\entity\Logger;
use webfiori\entity\Util;
use webfiori\entity\mail\SMTPAccount;
use webfiori\functions\SystemFunctions;
use webfiori\functions\WebsiteFunctions;
use webfiori\functions\BasicMailFunctions;
use webfiori\entity\router\APIRoutes;
use webfiori\entity\router\ViewRoutes;
use webfiori\entity\router\ClosureRoutes;
use webfiori\entity\router\OtherRoutes;
use webfiori\entity\cron\InitCron;
use webfiori\entity\router\Router;
use webfiori\entity\DatabaseSchema;
use jsonx\JsonX;
use Exception;
/**
 * The instance of this class is used to control basic settings of 
 * the framework. Also, it is the entry point of any request.
 * @author Ibrahim
 * @version 1.3.2
 */
class WebFiori{
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
        
        $this->sysStatus = Util::checkSystemStatus();
        $this->initRoutes();
        if($this->sysStatus == Util::MISSING_CONF_FILE || $this->sysStatus == Util::MISSING_SITE_CONF_FILE){
            Logger::log('One or more configuration file is missing. Attempting to create all configuration files.', 'warning');
            $this->SF->createConfigFile();
            $this->WF->createSiteConfigFile();
            $this->BMF->createEmailConfigFile();
            $this->sysStatus = Util::checkSystemStatus();
        }
        if(!$this->SF->isSetupFinished()){
            $this->firstUse();
        }
        
        //initialize some settings...
        $this->initCron();
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
     * @return boolean|string If the system is configured correctly, the function 
     * will return TRUE. If the file 'Config.php' was not found, The function will return 
     * 'Util::MISSING_CONF_FILE'. If the file 'SiteConfig.php' was not found, The function will return 
     * 'Util::MISSING_CONF_FILE'. If the system is not configured yet, the function 
     * will return 'Util::NEED_CONF'. If the system is unable to connect to 
     * the database, the function will return 'Util::DB_NEED_CONF'.
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
     * This function is called when the status of the system does not equal 
     * to TRUE. It is used to configure some of the basic settings in case 
     * of first use. Modify the content of this function as needed.
     * @since 1.0
     */
    public function firstUse(){
        Logger::logFuncCall(__METHOD__, 'initialization-log');
        if(self::getClassStatus()== 'INITIALIZING'){
            //in this part, you can configure the ststem. 
        
            //the first thing you might need to do is to update basic website
            //attributes. 
            $this->initWebsiteAttributes();

            //After that, if your app uses MySQL database, you can set connection 
            //parameters here. If it does not, skip this step by commenting 
            //the next line.
            //$this->setDatabaseConnection();


            //Also, you can add SMTP email account that you can use to send email 
            //messages if your system uses this functionality.
            $this->initSMTPAccounts();
            
            //initialize database
            $this->initDatabase();
            
            //once configuration is finished, call the function SystemFunctions::configured()
            $this->SF->configured();

            //do not remove next lines of code.
            //Used to show error message in case the 
            //system is not configured.
            if(!$this->SF->isSetupFinished()){
                Logger::log('Initialization faild.','error','initialization-log');
                $this->needConfigration();
            }
        }
        Logger::logFuncReturn(__METHOD__, 'initialization-log');
    }
    /**
     * Adds SMTP accounts during initialization.
     * The developer does not have to call this method manually. It will be 
     * called only if it is the first run for the system.
     * @since 1.1
     */
    public function initSMTPAccounts() {
        Logger::logFuncCall(__METHOD__, 'initialization-log');
        if(self::getClassStatus()== 'INITIALIZING'){
            //$acc = new EmailAccount();
            //$acc->setName('System Admin');
            //$acc->setServerAddress('mail.example.com');
            //$acc->setUsername('no-replay@example.com');
            //$acc->setPassword('JQtnUE2VUm');
            //$acc->setAddress('no-replay@example.com');
            //$acc->setPort(587);
            //$this->BMF->updateOrAddEmailAccount($acc);
        }
        Logger::logFuncReturn(__METHOD__, 'initialization-log');
    }
    /**
     * Updates basic settings of the web site.
     * This function can be used to update the settings which is saved in the 
     * file 'SiteConfig.php'. The settings include:
     * <ul>
     * <li>Base URL of the web site.</li>
     * <li>Primary language of the web site.</li>
     * <li>The name of web site theme.</li>
     * <li>The name of admin theme of the web site.</li>
     * <li>General descriptions of the web site for different languages.</li>
     * <li>Names of web site in different languages.</li>
     * <li>The character or string that is used to separate the name of the web 
     * site from page title.</li>
     * </ul>
     * The developer does not have to call this function manually. It will be 
     * called only if it is the first run for the system.
     * @since 1.1
     */
    private function initWebsiteAttributes() {
        Logger::logFuncCall(__METHOD__, 'initialization-log');
        if(self::getClassStatus()== 'INITIALIZING'){
            $siteInfoArr = $this->WF->getSiteConfigVars();
            $siteInfoArr['base-url'] = Util::getBaseURL();
            $siteInfoArr['primary-language'] = 'EN';
            $siteInfoArr['theme-name'] = 'Greeny By Ibrahim Ali';
            $siteInfoArr['title-separator'] = '|';
            $siteInfoArr['site-descriptions'] = array('AR'=>'','EN'=>'');
            $siteInfoArr['website-names'] = array('AR'=>'أكاديميا البرمجة','EN'=>'Programming Academia');
            $this->WF->updateSiteInfo($siteInfoArr);
        }
        Logger::logFuncReturn(__METHOD__, 'initialization-log');
    }
    /**
     * Initialize the directories at which the framework will try to load 
     * classes from. 
     * If the user has created new folder inside the root framework directory, 
     * he can add the folder using this method.
     * @since 1.2.1
     */
    public function initAutoloadDirectories(){
        if(self::getClassStatus()== 'INITIALIZING'){
            //add your own custom folders here.
            //$this->AU->addSearchDirectory('my-system/entities');
            //$this->AU->addSearchDirectory('my-system/logic');
            //$this->AU->addSearchDirectory('my-system/apis');
        }
    }
    /**
     * Updates database settings.
     * @since 1.1
     */
    private function initDatabase() {
        if(self::getClassStatus() == 'INITIALIZING'){
            //only change the values of the following 5 variables.
            $dbHost = 'localhost';
            $dbUser = 'root';
            $dbPass = '';
            $dbName = '';
            $dbPort = '3306';

            if($this->SF->updateDBAttributes($dbHost, $dbUser, $dbPass, $dbName, $dbPort) === TRUE){

                //since this is the first use, we need to initialize database schema.
                //If schema already created, this step can be skipped.
                //create any query object to use it for executing SQL statements that 
                //is used to build the database.
                Logger::log('Initializing database...','info','initialization-log');
                $schema = DatabaseSchema::get();
                Logger::log('Database Schema: ', 'debug','initialization-log');
                Logger::log($schema->getSchema(), 'debug','initialization-log');
                //$query = new ExampleQuery();
                //$query->setQuery($schema->getSchema(), 'update');
                //if($this->SF->excQ($query) !== TRUE){
                //    Logger::log('Initialization faild.', 'error','initialization-log');
                //    Logger::requestCompleted();
                //    header('HTTP/1.1 503 Service Unavailable');
                //    die($this->SF->getDBLink()->toJSON().'');
                //}
            }
            else{
                Logger::log('Initialization faild.', 'error','initialization-log');
                $dbLink = $this->SF->getDBLink();
                Logger::requestCompleted();
                header('HTTP/1.1 503 Service Unavailable');
                die($dbLink->toJSON().'');
            }
        }
    }
    /**
     * Initialize access control class.
     * @since 1.3.1
     */
    private function initPermissions(){
        Logger::logFuncCall(__METHOD__);
        
        //create new group.
        //$AD = 'ADMIN_GROUP';
        //Access::newGroup($AD);
        //add permissions to the group.
        //Access::newPrivilege($AD, 'LOGIN');
        
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
            $j->add('description','This error means that the system is not configured yet or this is the first run.'
            . 'If you think that your system is configured, then refresh this page and the '
            . 'error should be gone. If you did not configure the system yet, then do the following:'
            . '</p>'
            . '<ul>'
            . '<li>Open the file \'WebFiori.php\' in any editor.</li>'
            . '<li>Inside the class \'WebFiori\', go to the body of the function \'firstUse()\'.</li>'
            . '<li>Modify the body of the function as instructed.</li>'
            . '</ul>'
            . '<p>'
            . 'Once you do that, you are ready to go and use the system.'
            . '</p>');
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
            . 'This error means that the system is not configured yet or this is the first run.'
            . 'If you think that your system is configured, then refresh this page and the '
            . 'error should be gone. If you did not configure the system yet, then do the following:'
            . '</p>'
            . '<ul>'
            . '<li>Open the file \'WebFiori.php\' in any editor.</li>'
            . '<li>Inside the class \'WebFiori\', go to the body of the function \'firstUse()\'.</li>'
            . '<li>Modify the body of the function as instructed.</li>'
            . '</ul>'
            . '<p>'
            . 'Once you do that, you are ready to go and use the system.'
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
