<?php
/**
 * The entry point for all requests. 
 * This file contains main configuration settings which will be used across 
 * all scripts. Also, You will have to do some basic configuration your self. 
 * The configuration must be performed only one time. It is possible to 
 * change settings later. In general, setup involves 3 steps:
 * 1- Configuring web site attributes.
 * 2- Configuring database connection.
 * 3- Configuring SMTP email account(s).
 * 
 * It is possible to skip all but it is not recommended.
 */
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
AutoLoader::get();
Util::displayErrors();
$GLOBALS['SYS_STATUS'] = Util::checkSystemStatus();
createRoutes();
if($GLOBALS['SYS_STATUS'] == Util::MISSING_CONF_FILE || $GLOBALS['SYS_STATUS'] == Util::MISSING_SITE_CONF_FILE){
    SystemFunctions::get()->createConfigFile();
    WebsiteFunctions::get()->createSiteConfigFile();
    BasicMailFunctions::get()->createEmailConfigFile();
    $GLOBALS['SYS_STATUS'] = Util::checkSystemStatus();
}
//at this stage, only check for configuration files
if($GLOBALS['SYS_STATUS'] !== TRUE && $GLOBALS['SYS_STATUS'] == Util::NEED_CONF){
    //in this part, you can configure the ststem. 
    //the first thing you might need to do is to update basic website
    //attributes.
    
    $WF = WebsiteFunctions::get();
    $siteInfoArr = $WF->getSiteConfigVars();
    $siteInfoArr['base-url'] = '';
    $siteInfoArr['primary-language'] = '';
    $siteInfoArr['theme-name'] = '';
    $siteInfoArr['title-separator'] = '';
    $siteInfoArr['base-url'] = '';
    $siteInfoArr['site-descriptions'] = array('EN'=>'');
    $siteInfoArr['website-names'] = array('EN'=>'');
    $WF->updateSiteInfo($siteInfoArr);
    
    //After that, if your app uses MySQL database, you can set connection 
    //parameters here. If it does not, skip this step.
    
    $SF = SystemFunctions::get();
    $dbHost = '';
    $dbUser = '';
    $dbPass = '';
    $dbName = '';
    $SF->updateDBAttributes($dbHost, $dbUser, $dbPass, $dbName);
    
    
    //Also, you can add SMTP email account that you can use to send email 
    //messages if your system uses this functionality.
    
    $BMF = BasicMailFunctions::get();
    $account = new EmailAccount();
    $account->setName('no-replay');
    $account->setAddress('myAddress@example.com');
    $account->setPassword('xxx');
    $account->setUsername('hello@example.com');
    $account->setPort(25);
    $account->setServerAddress('mail.example.com');
    $BMF->updateOrAddEmailAccount($account);
    
    //once configuration is finished, call the function SystemFunctions::configured()
    
    //$SF->configured();
    if(!$SF->isSetupFinished()){
        die('System is not ready for use.');
    }
}
Router::route(Util::getRequestedURL());

/**
 * A function to create routes.
 */
function createRoutes(){
    APIRoutes::create();
    ViewRoutes::create();
    ClosureRoutes::create();
}