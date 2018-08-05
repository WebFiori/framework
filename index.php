<?php
/**
 * The instance of this class is used to control basic settings of 
 * the framework. Also, it is the entry point of any request.
 * @author Ibrahim Ali <ibinshikh@hotmail.com>
 * @version 1.0
 */
class LisksCode{
    /**
     *
     * @var boolean|string 
     */
    private $sysStatus;
    /**
     *
     * @var AutoLoader 
     */
    private $AU;
    /**
     * 
     * @var SystemFunctions 
     */
    private $SF;
    /**
     *
     * @var WebsiteFunctions 
     */
    private $WF;
    /**
     *
     * @var BasicMailFunctions 
     */
    private $BMF;
    /**
     *
     * @var LisksCode 
     */
    private static $LC;
    /**
     * 
     * @return LisksCode
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
    }
    /**
     * 
     * @param type $refresh
     * @return boolean|string
     */
    public static function sysStatus($refresh=true){
        return self::get()->getSystemStatus($refresh);
    }
    /**
     * 
     * @param type $refresh
     * @return boolean|string
     */
    private function getSystemStatus($refresh=true) {
        if($refresh === TRUE){
            $this->sysStatus = Util::checkSystemStatus();
        }
        return $this->sysStatus;
    }
    
    private function initRoutes(){
        APIRoutes::create();
        ViewRoutes::create();
        ClosureRoutes::create();
    }
    
    private function firstUse(){
        //in this part, you can configure the ststem. 
        //the first thing you might need to do is to update basic website
        //attributes.

        $siteInfoArr = $this->WF->getSiteConfigVars();
        $siteInfoArr['base-url'] = Util::getBaseURL();
        $siteInfoArr['primary-language'] = '';
        $siteInfoArr['theme-name'] = '';
        $siteInfoArr['title-separator'] = '';
        $siteInfoArr['base-url'] = '';
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

        //$SF->configured();
        if(!$this->SF->isSetupFinished()){
            die('System is not ready for use.');
        }
    }
}

LisksCode::getAndStart();