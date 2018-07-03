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
