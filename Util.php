<?php
/**
 * PHP utility class.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.2
 */
class Util{
    /**
     * A constant that is returned by <b>Util::checkSystemStatus()</b> to indicate 
     * that the file 'Config.php' is missing.
     * @since 1.2
     */
    const MISSING_CONF_FILE = 'missing_config_file';
    /**
     * A constant that is returned by <b>Util::checkSystemStatus()</b> to indicate 
     * that the file 'SiteConfig.php' is missing.
     * @since 1.2
     */
    const MISSING_SITE_CONF_FILE = 'missing_site_config_file';
    /**
     * A constant that is returned by <b>Util::checkSystemStatus()</b> to indicate 
     * that system is not configured yet.
     * @since 1.2
     */
    const NEED_CONF = 'sys_conf_err';
    /**
     * A constant that is returned by <b>Util::checkSystemStatus()</b> to indicate 
     * that database connection was not established.
     * @since 1.2
     */
    const DB_NEED_CONF = 'db_conf_err';
    /**
     *
     * @var DatabaseLink 
     */
    private static $dbTestInstance;
    /**
     * 
     * @return DatabaseLink
     */
    public static function getDatabaseTestInstance(){
        Util::checkSystemStatus();
        return self::$dbTestInstance;
    }
    /**
     * 
     * @return boolean|string The function will return <b>TRUE</b> in case everything 
     * was fine. If the file 'Config.php' was not found, The function will return 
     * <b>Util::MISSING_CONF_FILE</b>. If the file 'SiteConfig.php' was not found, The function will return 
     * <b>Util::MISSING_CONF_FILE</b>. If the system is not configured yet, the function 
     * will return <b>Util::NEED_CONF</b>. If the system is unable to connect to 
     * the database, the function will return <b>Util::DB_NEED_CONF</b>.
     * @since 1.2
     */
    public static function checkSystemStatus(){
        if(class_exists('Config')){
            if(class_exists('SiteConfig')){
                if(Config::get()->isConfig() === TRUE){
                    self::$dbTestInstance = new DatabaseLink(Config::get()->getDBHost(), Config::get()->getDBUser(), Config::get()->getDBPassword());
                    if(self::$dbTestInstance->isConnected()){
                        if(self::$dbTestInstance->setDB(Config::get()->getDBName())){
                            return TRUE;
                        }
                    }
                    return Util::DB_NEED_CONF;
                }
                else{
                    return Util::NEED_CONF;
                }
            }
            return Util::MISSING_SITE_CONF_FILE;
        }
        return Util::MISSING_CONF_FILE;
    }
    /**
     * Disallow creating instances of the class.
     */
    private function __construct() {
        
    }
    /**
     * Call the function 'print_r' and insert 'pre' around it.
     * @param mixed $expr
     */
    public static function print_r($expr){
        ?><pre><?php print_r($expr)?></pre><?php
    }
    /**
     * Returns unicode code of a character.
     * Common values: 32 = space, 10 = new line, 13 = carriage return.
     * @param type $u a character.
     * @return int
     * @since 0.2
     */
    public static function uniord($u) {
        $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
        $k1 = ord(substr($k, 0, 1));
        $k2 = ord(substr($k, 1, 1));
        return $k2 * 256 + $k1;
    }
    /**
     * Checks if a given character is an upper case letter or lower case letter.
     * @param char $char A character such as (A B C D " > < ...).
     * @return bool True if the given character is in upper case.
     * @since 0.1
     */
    public static function isUpper($char) {
        return mb_strtolower($char, "UTF-8") != $char;
    }
    /**
     * Call this function to display errors and warnings.
     * Used for debugging.
     * @since 0.2
     */
    public static function displayErrors(){
        ini_set('display_startup_errors', 1);
        ini_set('display_errors', 1);
        error_reporting(-1);
        define('DEBUG', 1);
    }
    /**
     * This function is used to filter scripting code such as 
     * JavaScript or PHP.
     * @param string $input
     * @return string
     */
    public static function filterScripts($input){
        $retVal = str_replace('<script>', '&lt;script&gt;', $input);
        $retVal = str_replace('</script>', '&lt;/script&gt;', $retVal);
        $retVal = str_replace('<?php', '&lt;?php', $retVal);
        return $retVal;
    }
    /**
     * Creates a new directory.
     * The method first checks if the directory is exist or not. If it is exist, 
     * it will return false. Else, it will create the directory and return true. If 
     * an error has happened while creating the directory, the method will return false.
     * @param string $dir a new directory (e.g. en/res/new-dir)
     * @return boolean True if the directory is created. Else, it will return false.
     * @since 0.1
     */
    public static function newDir($dir){
        if($dir){
            $dir = str_replace('\\', '/', $dir);
            //echo 'Final Dir = "'.$dir.'"<br/>';
            if(!is_dir($dir)){
                if(mkdir($dir, 0755 , true)){
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    /**
     * This function is used to construct a default base URL.
     * @return string The base URL (such as 'http//www.example.com/')
     * @since 0.2
     */
    public static function getBaseURL(){
        $host = $_SERVER['HTTP_HOST'];
        if(isset($_SERVER['HTTPS'])){
            $protocol = 'https://';
        }
        else{
            $protocol = 'http://';
        }
        $docRoot = $_SERVER['DOCUMENT_ROOT'];
        $len = strlen($docRoot);
        $toAppend = substr(__DIR__, $len, strlen(__DIR__) - $len);
        return $protocol.$host. str_replace('\\', '/', $toAppend).'/';
    }
    /**
     * Returns the URL of the requested resource.
     * @return string Requested URL resource.
     * @since 1.1
     */
    public static function getRequestedURL(){
        $protocol = "http://";
        if(isset($_SERVER['HTTPS'])){
            $protocol = "https://";
        }
        $server = filter_var(getenv('HTTP_HOST'));
        $requestedURI = filter_var(getenv('REQUEST_URI'));
        return $protocol.''.$server.''.$requestedURI;
    }
}