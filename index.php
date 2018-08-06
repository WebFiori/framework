<?php
/**
 * The instance of this class is used to control basic settings of 
 * the framework. Also, it is the entry point of any request.
 * @author Ibrahim Ali <ibinshikh@hotmail.com>
 * @version 1.0
 */
class LisksCode{
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
     * A single instance of the class LisksCode.
     * @var LisksCode
     * @since 1.0 
     */
    private static $LC;
    /**
     * Initiate the framework and return a single instance of the class that can 
     * be used to update some settings.
     * @return LisksCode An instance of 'LisksCode'.
     * @since 1.0
     */
    public static function &getAndStart(){
        if(self::$LC != NULL){
            return self::$LC;
        }
        self::$LC = new LisksCode();
        return self::$LC;
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
        $this->SF = SystemFunctions::get();
        $this->WF = WebsiteFunctions::get();
        $this->BMF = BasicMailFunctions::get();

        //uncomment next line to show runtime errors and warnings
        Util::displayErrors();

        $this->sysStatus = Util::checkSystemStatus();
        $this->initRoutes();
        if($this->sysStatus == Util::MISSING_CONF_FILE || $this->sysStatus == Util::MISSING_SITE_CONF_FILE){
            $this->SF->createConfigFile();
            $this->WF->createSiteConfigFile();
            $this->BMF->createEmailConfigFile();
            $this->sysStatus = Util::checkSystemStatus();
        }
        if(!$this->SF->isSetupFinished()){
            $this->firstUse();
        }
        Router::route(Util::getRequestedURL());
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
        return self::get()->getSystemStatus(TRUE);
    }
    /**
     * 
     * @param type $refresh
     * @return boolean|string
     * @since 1.0
     */
    private function getSystemStatus($refresh=true) {
        if($refresh === TRUE){
            $this->sysStatus = Util::checkSystemStatus();
        }
        return $this->sysStatus;
    }
    /**
     * Initiate routes.
     * @since 1.0
     */
    private function initRoutes(){
        APIRoutes::create();
        ViewRoutes::create();
        ClosureRoutes::create();
    }
    /**
     * This function is called when the status of the system does not equal 
     * to TRUE. It is used to configure some of the basic settings in case 
     * of first use. Modify the content of this function as needed.
     * @since 1.0
     */
    private function firstUse(){
        //in this part, you can configure the ststem. 
        //the first thing you might need to do is to update basic website
        //attributes.

        $siteInfoArr = $this->WF->getSiteConfigVars();
        $siteInfoArr['base-url'] = Util::getBaseURL();
        $siteInfoArr['primary-language'] = '';
        $siteInfoArr['theme-name'] = '';
        $siteInfoArr['title-separator'] = '';
        $siteInfoArr['site-descriptions'] = array('EN'=>'');
        $siteInfoArr['website-names'] = array('EN'=>'');
        $this->WF->updateSiteInfo($siteInfoArr);

        //After that, if your app uses MySQL database, you can set connection 
        //parameters here. If it does not, skip this step.
        $dbHost = '';
        $dbUser = '';
        $dbPass = '';
        $dbName = '';
        $this->SF->updateDBAttributes($dbHost, $dbUser, $dbPass, $dbName);


        //Also, you can add SMTP email account that you can use to send email 
        //messages if your system uses this functionality.
        $account = new EmailAccount();
        $account->setName('no-replay');
        $account->setAddress('myAddress@example.com');
        $account->setPassword('xxx');
        $account->setUsername('hello@example.com');
        $account->setPort(25);
        $account->setServerAddress('mail.example.com');
        $this->BMF->updateOrAddEmailAccount($account);

        //once configuration is finished, call the function SystemFunctions::configured()
        //$this->SF->configured();
        if(!$this->SF->isSetupFinished()){
            die('System is not ready for use.');
        }
        
    }
}

LisksCode::getAndStart();