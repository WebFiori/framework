<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_regex_encoding('UTF-8'); 
/**
 * See http://php.net/manual/en/timezones.php for supported time zones
 */
date_default_timezone_set('Asia/Riyadh');
/**
 * The root directory that is used to load all other required system files.
 */
define('ROOT_DIR',__DIR__);

//fallback for older php versions that does not 
//support the constant PHP_INT_MIN
if(!defined('PHP_INT_MIN')){
    define('PHP_INT_MIN', ~PHP_INT_MAX);
}

/**
 * A folder used to hold system resources (such as images).
 */
define('RES_FOLDER','res');
require_once ROOT_DIR.'/entity/AutoLoader.php';
Util::displayErrors();

//at this stage, only check for configuration files
//other errors checked as needed later.
$GLOBALS['SYS_STATUS'] = Util::checkSystemStatus();
if($GLOBALS['SYS_STATUS'] !== TRUE){
    if($GLOBALS['SYS_STATUS'] == Util::MISSING_CONF_FILE){
        SystemFunctions::get()->createConfigFile();
        $GLOBALS['SYS_STATUS'] = Util::checkSystemStatus();
    }
    if(gettype($GLOBALS['SYS_STATUS']) == 'string' && $GLOBALS['SYS_STATUS'] == Util::MISSING_SITE_CONF_FILE){
        SystemFunctions::get()->createSiteConfigFile();
        $GLOBALS['SYS_STATUS'] = Util::checkSystemStatus();
    }
    if(gettype($GLOBALS['SYS_STATUS']) == 'string' && $GLOBALS['SYS_STATUS'] == Util::NEED_CONF && !defined('SETUP_MODE')){
        header('content-type:application/json');
        http_response_code(500);
        die('{"message":"'.$GLOBALS['SYS_STATUS'].'","type":"error",'
                . '"details":"System needs to be configured before using it.",'
                . '"config-page":"'.Util::getBaseURL().'pages/setup/welcome"}');
    }
}
