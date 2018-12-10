<?php
namespace webfiori;
use webfiori\entity\AutoLoader;
use webfiori\entity\Logger;
use webfiori\entity\Util;
use webfiori\functions\SystemFunctions;
use webfiori\functions\WebsiteFunctions;
use webfiori\functions\BasicMailFunctions;
use webfiori\entity\router\APIRoutes;
use webfiori\entity\router\ViewRoutes;
use webfiori\entity\router\ClosureRoutes;
use webfiori\entity\cron\Cron;
use webfiori\entity\router\Router;
use webfiori\entity\mail\EmailAccount;
use webfiori\entity\DatabaseSchema;
use Exception;
/**
 * The instance of this class is used to control basic settings of 
 * the framework. Also, it is the entry point of any request.
 * @author Ibrahim
 * @version 1.3.2
 */
class WebFiori{
    /**
     * The name of the session that will be used by the core logic files.
     * @since 1.3.2
     */
    const MAIN_SESSION_NAME = 'main';
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
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');
        mb_http_input('UTF-8');
        mb_regex_encoding('UTF-8');
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
         * A folder used to hold system resources (such as images).
         */
        define('RES_FOLDER','res');
        /**
         * Initialize autoloader.
         */
        require_once ROOT_DIR.'/entity/AutoLoader.php';
        $this->AU = AutoLoader::get();
        $this->setAutoloadDirectories();
        
        //uncomment next line to show runtime errors and warnings
        //also enable logging for info, warnings and errors 
        Logger::logName('initialization-log');
        Logger::enabled(TRUE);
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
     * Initiate routes.
     * @since 1.0
     */
    private function initRoutes(){
        Logger::log('Initializing routes...', 'info', 'initialization-log');
        APIRoutes::create();
        ViewRoutes::create();
        ClosureRoutes::create();
        Logger::log('Routes initialization completed.', 'info', 'initialization-log');
    }
    /**
     * This function is called when the status of the system does not equal 
     * to TRUE. It is used to configure some of the basic settings in case 
     * of first use. Modify the content of this function as needed.
     * @since 1.0
     */
    private function firstUse(){
        Logger::logFuncCall(__METHOD__, 'initialization-log');
        //in this part, you can configure the ststem. 
        
        //the first thing you might need to do is to update basic website
        //attributes. 
        $this->setWebsiteAttributes();

        //After that, if your app uses MySQL database, you can set connection 
        //parameters here. If it does not, skip this step by commenting 
        //the next line.
        //$this->setDatabaseConnection();


        //Also, you can add SMTP email account that you can use to send email 
        //messages if your system uses this functionality.
        $this->addSMTPAccounts();
        
        //once configuration is finished, call the function SystemFunctions::configured()
        $this->SF->configured();
        
        //do not remove next lines of code.
        //Used to show error message in case the 
        //system is not configured.
        if(!$this->SF->isSetupFinished()){
            Logger::log('Initialization faild.','error','initialization-log');
            $this->needConfigration();
        }
        Logger::logFuncReturn(__METHOD__, 'initialization-log');
    }
    /**
     * Adds SMTP accounts during initialization.
     * @since 1.1
     */
    private function addSMTPAccounts() {
        //$account = new EmailAccount();
        //$account->setName('no-replay');
        //$account->setAddress('myAddress@example.com');
        //$account->setPassword('xxx');
        //$account->setUsername('hello@example.com');
        //$account->setPort(25);
        //$account->setServerAddress('mail.example.com');
        //$this->BMF->updateOrAddEmailAccount($account);
    }
    /**
     * Updates basic settings of the web site.
     * @since 1.1
     */
    private function setWebsiteAttributes() {
        $siteInfoArr = $this->WF->getSiteConfigVars();
        $siteInfoArr['base-url'] = Util::getBaseURL();
        $siteInfoArr['primary-language'] = 'EN';
        $siteInfoArr['theme-name'] = 'Greeny By Ibrahim Ali';
        $siteInfoArr['title-separator'] = '|';
        $siteInfoArr['site-descriptions'] = array('AR'=>'','EN'=>'');
        $siteInfoArr['website-names'] = array('AR'=>'أكاديميا البرمجة','EN'=>'Programming Academia');
        $this->WF->updateSiteInfo($siteInfoArr);
    }
    /**
     * Add your own custom search folders in here. The autoloader will search 
     * inside them for classes.
     * @since 1.2.1
     */
    private function setAutoloadDirectories(){
        //$this->AU->addSearchDirectory('my-system/entities');
        //$this->AU->addSearchDirectory('my-system/logic');
        //$this->AU->addSearchDirectory('my-system/apis');
    }
    /**
     * Updates database settings.
     * @since 1.1
     */
    private function setDatabaseConnection() {
        //only change the values of the following 4 variables.
        $dbHost = 'localhost';
        $dbUser = 'root';
        $dbPass = '';
        $dbName = '';
        
        if($this->SF->updateDBAttributes($dbHost, $dbUser, $dbPass, $dbName) === TRUE){
            
            //since this is the first use, we need to initialize database schema.
            //create any query object to use it for executing SQL statements that 
            //is used to build the database.
            Logger::log('Initializing database...','info','initialization-log');
            $schema = DatabaseSchema::get();
            Logger::log('Database Schema: ', 'debug','initialization-log');
            Logger::log($schema->getSchema(), 'debug','initialization-log');
            $query = new ExampleQuery();
            $query->setQuery($schema->getSchema(), 'update');
            if($this->SF->excQ($query) !== TRUE){
                Logger::log('Initialization faild.', 'error','initialization-log');
                Logger::requestCompleted();
                header('HTTP/1.1 503 Service Unavailable');
                die($this->SF->getDBLink()->toJSON().'');
            }
        }
        else{
            Logger::log('Initialization faild.', 'error','initialization-log');
            $dbLink = $this->SF->getDBLink();
            Logger::requestCompleted();
            header('HTTP/1.1 503 Service Unavailable');
            die($dbLink->toJSON().'');
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
     * @since 1.3
     */
    private function initCron(){
        Logger::logFuncCall(__METHOD__);
        //initialize cron job manager
        
        //set access password
        //the password must be kept in order to disallow any 
        //unauthorized call to run cron jobs.
        Cron::password('123456');
        
        //add jobs
        //$job = new CronJob('*/5,*/3 * * * *');
        //$job->setOnExecution(function($params){
        //    $file = fopen('cron.txt', 'a+');
        //    fwrite($file, 'Job \''.$params[0]->getJobName().'\' executed at '.date(DATE_RFC1123)."\r\n");
        //},array($job));
        //Cron::scheduleJob($job);
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
Router::closure('/test', function(){
    $availabelTh = entity\Theme::getAvailableThemes();
    Util::print_r($availabelTh);
});
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
