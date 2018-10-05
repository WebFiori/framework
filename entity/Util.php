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
 * @version 1.3.5
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
     * @since 1.2
     */
    private static $dbTestInstance;
    /**
     * Returns the instance of 'DatabaseLink' which is used to check database 
     * connection using the function 'Util::checkDbConnection()'.
     * @return DatabaseLink|NULL The instance of 'DatabaseLink' which is used to check database 
     * connection using the function 'Util::checkDbConnection()'. If no test was 
     * performed, the function will return NULL.
     * @since 1.2
     */
    public static function getDatabaseTestInstance(){
        return self::$dbTestInstance;
    }
    /**
     * Converts a string to its numeric value.
     * @param string $str A string that represents a number.
     * @return int|float|boolean If the given string represents an integer, 
     * the value is returned as an integer. If the given string represents a float, 
     * the value is returned as a float. If the function is unable to convert 
     * the string to its numerical value, it will return FALSE.
     * @since 1.3.5
     */
    public static function numericValue($str){
        $str = trim($str);
        $len = strlen($str);
        $isFloat = FALSE;
        $retVal = FALSE;
        for($y = 0 ; $y < $len ; $y++){
            $char = $str[$y];
            if($char == '.' && !$isFloat){
                $isFloat = TRUE;
            }
            else if($char == '-' && $y == 0){
                
            }
            else if($char == '.' && $isFloat){
                return $retVal;
            }
            else{
                if(!($char <= '9' && $char >= '0')){
                    return $retVal;
                }
            }
        }
        if($isFloat){
            $retVal = floatval($str);
        }
        else{
            $retVal = intval($str);
        }
        return $retVal;
    }
    /**
     * Returns HTTP request headers.
     * @return array An associative array of request headers.
     * @since 1.3.3
     */
    public static function getRequestHeaders(){
        $retVal = array();
        if(function_exists('apache_request_headers')){
            $headers = apache_request_headers();
            foreach ($headers as $k=>$v){
                $retVal[strtolower($k)] = $v; 
            }
        }
        else{
            foreach ($_SERVER as $k => $v){
                $split = explode('_', $k);
                if($split[0] == 'HTTP'){
                    $headerName = '';
                    $count = count($split);
                    for($x = 0 ; $x < $count ; $x++){
                        if($x + 1 == $count && $split[$x] != 'HTTP'){
                            $headerName = $headerName.$split[$x];
                        }
                        else if($x == 1 && $split[$x] != 'HTTP'){
                            $headerName = $split[$x].'-';
                        }
                        else if($split[$x] != 'HTTP'){
                            $headerName = $headerName.$split[$x].'-';
                        }
                    }
                    $retVal[strtolower($headerName)] = $v;
                }
            }
        }
        return $retVal;
    }
    /**
     * An alias for the function 'Util::getClientIP()'.
     * @return string The IP address of the user who has initiated the request.
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
     * Returns the IP address of the user who is connected to the server.
     * @return string The IP address of the user who is connected to the server. 
     * The value is taken from the array $_SERVER at index 'REMOTE_ADDR'.
     * @since 1.3.1
     */
    public static function getClientIP() {
        $ip = filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP);
        if($ip == '::1'){
            return '127.0.0.1';
        }
        else{
            return $ip;
        }
    }
    /**
     * Returns the IPv4 address of server host.
     * @return string The IPv4 address of server host.
     * @since 1.3.1
     */
    public static function getHostIP(){
        $host= gethostname();
        $ip = gethostbyname($host);
        return $ip;
    }
    /**
     * Test a connection to system databas or external one.
     * @param array $dbAttrs [Optional] An associative array. The array can 
     * have 4 indices:
     * <ul>
     * <li><b>host</b>: The name of database host. It can be a URL, an IP address 
     * or 'localhost'.</li>
     * <li><b>user</b>: The username of the user that will be used to connect to 
     * the database.</li>
     * <li><b>pass</b>: The password of the user.</li>
     * <li><b>db-name</b>: The name of the database.</li>
     * </ul>
     * If the given parameter is not provided, the function will try to test 
     * database settings that where set in the class 'Config'.
     * @return boolean|string If the connection was established, the function will 
     * return TRUE. If no connection was established, the function will 
     * return 'Util::DB_NEED_CONF'.
     * @since 1.3.2
     */
    public static function checkDbConnection($dbAttrs=array()){
        Logger::logFuncCall(__METHOD__);
        $C = Config::get();
        $host = isset($dbAttrs['host']) ? $dbAttrs['host'] : $C->getDBHost();
        $user = isset($dbAttrs['user']) ? $dbAttrs['user'] : $C->getDBHost();
        $pass = isset($dbAttrs['pass']) ? $dbAttrs['pass'] : $C->getDBHost();
        $dbName = isset($dbAttrs['db-name']) ? $dbAttrs['db-name'] : $C->getDBHost();
        Logger::log('Trying to connect to the database...');
        Logger::log('DB Host: \''.$host.'\'.', 'debug');
        Logger::log('DB User: \''.$user.'\'.', 'debug');
        Logger::log('DB Pass: \''.$pass.'\'.', 'debug');
        Logger::log('DB Name: \''.$dbName.'\'.', 'debug');
        self::$dbTestInstance = new DatabaseLink($host, $user, $pass);
        if(self::$dbTestInstance->isConnected()){
            Logger::log('Connected to host. Setting database...');
            if(self::$dbTestInstance->setDB($dbName)){
                Logger::log('Database set.');
                $returnValue = TRUE;
            }
            else{
                Logger::log('Unable to set database.','warning');
                Logger::log('Message: \''.self::$dbTestInstance->getErrorMessage().'\'.');
                Logger::log('Code: \''.self::$dbTestInstance->getErrorCode().'\'.');
                $returnValue = Util::DB_NEED_CONF;
            }
        }
        else{
            Logger::log('Unable to connect.','warning');
            Logger::log('Message: \''.self::$dbTestInstance->getErrorMessage().'\'.');
            Logger::log('Code: \''.self::$dbTestInstance->getErrorCode().'\'.');
            $returnValue = Util::DB_NEED_CONF;
        }
        Logger::logReturnValue($returnValue);
        Logger::logFuncReturn(__METHOD__);
        return $returnValue;
    }

    /**
     * Check the overall status of the system.
     * @param boolean $checkDb If set to TRUE, the function will also check 
     * database connection status. The settings of the connection will 
     * be taken from the class 'Config'.
     * @return boolean|string The function will return TRUE in case everything 
     * was fine. If the file 'Config.php' was not found, The function will return 
     * 'Util::MISSING_CONF_FILE'. If the file 'SiteConfig.php' was not found, The function will return 
     * 'Util::MISSING_CONF_FILE'. If the system is not configured yet, the function 
     * will return 'Util::NEED_CONF'. If the system is unable to connect to 
     * the database, the function will return 'Util::DB_NEED_CONF'.
     * @since 1.2
     */
    public static function checkSystemStatus($checkDb=false){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking system status...');
        $returnValue = '';
        if(class_exists('Config')){
            if(class_exists('SiteConfig')){
                if(Config::get()->isConfig() === TRUE || WebFiori::getClassStatus() == 'INITIALIZING'){
                    if($checkDb === TRUE){
                        Logger::log('Checking database connection...');
                        $returnValue = self::checkDbConnection();
                    }
                    else{
                        Logger::log('No need to check database connection');
                        $returnValue = TRUE;
                    }
                }
                else{
                    Logger::log('The function \'Config::get()->isConfig()\' returned FALSE or the core is still initializing.', 'warning');
                    $returnValue = Util::NEED_CONF;
                }
            }
            else{
                Logger::log('The file \'SiteConfig.php\' is missing.', 'warning');
                $returnValue = Util::MISSING_SITE_CONF_FILE;
            }
        }
        else{
            Logger::log('The file \'Config.php\' is missing.', 'warning');
            $returnValue = Util::MISSING_CONF_FILE;
        }
        Logger::logReturnValue($returnValue);
        Logger::logFuncReturn(__METHOD__);
        return $returnValue;
    }
    /**
     * Disallow creating instances of the class.
     */
    private function __construct() {
        
    }
    /**
     * Returns the number of a day in the week given a date.
     * @param string $date A date string that has the month, the date and 
     * year number.
     * @param $format The format of the given date. The default value is 
     * 'YYYY-MM-DD'.
     * @return int|boolean A number between 0 and 6 inclusive. 0 is for Sunday and 
     * 6 is for Saturday. If the function fails, it will return FALSE.
     */
    public static function getGWeekday($date,$format='YYYY-MM-DD') {
        if($format == 'YYYY-MM-DD'){
            return date('w', strtotime($date));
        }
        else{
            $split = explode('-', $format);
            $dateSplit = explode('-', $date);
            if(count($split) == 3 && count($dateSplit) == 3){
                //$yearIndex = array_
            }
        }
    }
    /**
     * Returns an array that contains the dates of current week's days in 
     * Gregorian calendar.
     * @return array an array that contains the dates of current week's days. 
     * The default format of the dates will be 'Y-m-d'.
     */
    public static function getGWeekDates(){
        $dayFormatters = array('d'=>'01','j'=>'1');
        $monthFormatters = array('F'=>'January','m'=>'01','M'=>'Jan','n'=>'1');
        $yearFormatters = array('y'=>'99','Y'=>'1999');
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
            $datesArr[] = $startYear.'-'.$startDay.'-'.$startMonth;
            $startDay += 1;
        }
        return $datesArr;
    }
    /**
     * Call the function 'print_r' and insert 'pre' around it.
     * @param mixed $expr
     */
    public static function print_r($expr){
        $expr = str_replace('<', '&lt;', $expr);
        $expr = str_replace('>', '&gt;', $expr);
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
     * Used for debugging. Also, enable logging for info, warning and error 
     * messages. To enable logging for debug info, define the constant 
     * 'DEBUG'
     * @since 0.2
     */
    public static function displayErrors(){
        ini_set('display_startup_errors', 1);
        ini_set('display_errors', 1);
        error_reporting(-1);
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