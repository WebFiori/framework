<?php
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
if($GLOBALS['SYS_STATUS'] == Util::MISSING_CONF_FILE || $GLOBALS['SYS_STATUS'] == Util::MISSING_SITE_CONF_FILE){
    SystemFunctions::get()->createConfigFile();
    WebsiteFunctions::get()->createSiteConfigFile();
    MailFunctions::get()->createEmailConfigFile();
    $GLOBALS['SYS_STATUS'] = Util::checkSystemStatus();
}
createRoutes();
//at this stage, only check for configuration files
//other errors checked as needed later.
if($GLOBALS['SYS_STATUS'] !== TRUE && $GLOBALS['SYS_STATUS'] == Util::NEED_CONF){
    $requestedURI = Util::getRequestedURL();
    $b = SiteConfig::get()->getBaseURL();
    if($requestedURI == $b.'s/welcome' ||
       $requestedURI == $b.'s/database-setup' ||
       $requestedURI == $b.'s/smtp-account' || 
       $requestedURI == $b.'s/admin-account' ||
       $requestedURI == $b.'s/website-config' ||
       $requestedURI == $b.'SysAPIs'){
        Router::route($requestedURI);
    }
    else{
        //Util::print_r($GLOBALS);
        Router::route(SiteConfig::get()->getBaseURL().Util::NEED_CONF);
    }
}
else{
    Router::route(Util::getRequestedURL());
}
/**
 * A function to create routes.
 */
function createRoutes(){
    APIRoutes::create();
    ViewRoutes::create();
    ClosureRoutes::create();
}