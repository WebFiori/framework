<?php
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
/**
 * PHP utility class.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.3
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
     * Returns the IP address of the user who is connected to the server.
     * @return string The IP address of the user who is connected to the server. 
     * The value is taken from the array $_SERVER at index 'REMOTE_ADDR'.
     * @since 1.3
     */
    public static function getIpAddress() {
        $ip = filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP);
        if($ip == '::1'){
            return '127.0.0.1';
        }
        else{
            return $ip;
        }
    }

    /**
     * 
     * @return boolean|string The function will return TRUE in case everything 
     * was fine. If the file 'Config.php' was not found, The function will return 
     * 'Util::MISSING_CONF_FILE'. If the file 'SiteConfig.php' was not found, The function will return 
     * 'Util::MISSING_CONF_FILE'. If the system is not configured yet, the function 
     * will return 'Util::NEED_CONF'. If the system is unable to connect to 
     * the database, the function will return 'Util::DB_NEED_CONF'.
     * @since 1.2
     */
    public static function checkSystemStatus(){
        if(class_exists('Config')){
            if(class_exists('SiteConfig')){
                if(Config::get()->isConfig() === TRUE || LisksCode::getClassStatus() == 'INITIALIZING'){
                    if(class_exists('DatabaseLink')){
                        self::$dbTestInstance = new DatabaseLink(Config::get()->getDBHost(), Config::get()->getDBUser(), Config::get()->getDBPassword());
                        if(self::$dbTestInstance->isConnected()){
                            if(self::$dbTestInstance->setDB(Config::get()->getDBName())){
                                return TRUE;
                            }
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
    public function getWeekDates(){
        $datesArr = array();
        $startDay = '';
        $startYear = '';
        $startMonth = '';
        
        $todayNumberInMonth = intval(date('d'));
        
        $weekStartDay = 7;
        $todayNumberInWeek = date('N');
        
        $thisMonth = intval(date('m'));
        $thisYear = date('Y');
        
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN,$thisMonth,$thisYear);
        
        if($todayNumberInWeek == $weekStartDay){
            $startDay = $todayNumberInMonth < 10 ? '0'.$todayNumberInMonth : $todayNumberInMonth;
            $startYear = $thisYear;
            $startMonth = $thisMonth < 10 ? '0'.$thisMonth : $thisMonth;
        }
        else{
            //same week but in the middle
            $backInTime = $todayNumberInMonth - $todayNumberInWeek;
            if($backInTime > 0){
                $startDay =  $backInTime < 10 ? '0'.$backInTime : $backInTime;
                $startMonth = $thisMonth < 10 ? '0'.$thisMonth : $thisMonth;
                $startYear = $thisYear;
            }
            else{
                $prevMonthNum = $thisMonth - 1 != 0 ? $thisMonth - 1 : 12;
                $startMonth = $prevMonthNum < 10 ? '0'.$prevMonthNum : $prevMonthNum;
                $startYear = $prevMonthNum == 12 ? $thisYear - 1 : $thisYear;
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN,$prevMonthNum,$startYear);
                $startDay = $daysInMonth - (-1)*$backInTime;
            }
        }
        for($x = 0 ; $x < 7 ; $x++){
            if($startDay > $daysInMonth){
                $startDay = 1;
                $startMonth += 1;
                $startMonth = $startMonth > 12 ? 1 : $startMonth;
                $startMonth = $startMonth < 10 ? '0'.$startMonth : $startMonth;
                $startYear = $startMonth == 1 ? $startYear + 1 : $startYear;
            }
            $startDay = $startDay < 10 ? '0'.$startDay : $startDay;
            $datesArr[] = $startDay.'-'.$startMonth.'-'.$startYear;
            $startDay += 1;
        }
        return $datesArr;
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
     * Used for debugging. Also, enable logging.
     * @since 0.2
     */
    public static function displayErrors(){
        ini_set('display_startup_errors', 1);
        ini_set('display_errors', 1);
        error_reporting(-1);
        Logger::enabled(TRUE);
        Logger::log('Logging Mode Enabled',null,TRUE);
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
     * Checks if a given directory exists or not.
     * @param string $dir A string in a form of directory (Such as 'root/home/res').
     * @param boolean $createIfNot If set to <b>TRUE</b> and the given directory does 
     * not exists, The function will try to create the directory.
     * @return boolean In general, the function will return <b>FALSE</b> if the 
     * given directory does not exists. The function will return <b>TRUE</b> only 
     * in two cases, If the directory exits or it does not exists but was created.
     * @since 0.1
     */
    public static function isDirectory($dir,$createIfNot=false){
        if($dir){
            $dir = str_replace('\\', '/', $dir);
            if(!is_dir($dir)){
                if($createIfNot === TRUE){
                    if(mkdir($dir, 0755 , true)){
                        return TRUE;
                    }
                }
            }
            else{
                return TRUE;
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
        $toAppend = substr(ROOT_DIR, $len, strlen(ROOT_DIR) - $len);
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