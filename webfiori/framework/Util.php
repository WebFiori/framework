<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework;

use webfiori\framework\cli\Runner;
use webfiori\framework\ui\MessageBox;
use WebFiori\Http\Response;
/**
 * Framework utility class.
 *
 * @author Ibrahim
 *
 * @version 1.3.9
 */
class Util {
    /**
     * A constant that is returned by Util::checkSystemStatus() to indicate
     * that database connection was not established.
     *
     * @since 1.2
     */
    const DB_NEED_CONF = 'db_conf_err';
    /**
     * A constant array that contains all PHP error codes in
     * addition to a description for each error.
     *
     * It is possible to access error information by simply using error
     * number as an index. For example, to access E_ERROR info, do the following:<br/>
     * <code>
     * $errInf = ERR_TYPES[E_ERROR];<br/>
     * echo $errInf['type'];<br/>
     * echo $errInf['description'];<br/>
     * </code>
     *
     * @since 1.3.9
     */
    const ERR_TYPES = [
        E_ERROR => [
            'type' => 'E_ERROR',
            'description' => 'Fatal run-time error.'
        ],
        E_WARNING => [
            'type' => 'E_WARNING',
            'description' => 'Run-time warning.'
        ],
        E_PARSE => [
            'type' => 'E_PARSE',
            'description' => 'Compile-time parse error.'
        ],
        E_NOTICE => [
            'type' => 'E_NOTICE',
            'description' => 'Run-time notice.'
        ],
        E_CORE_ERROR => [
            'type' => 'E_CORE_ERROR',
            'description' => 'Fatal error during initialization.'
        ],
        E_CORE_WARNING => [
            'type' => 'E_CORE_WARNING',
            'description' => 'Warning during initialization.'
        ],
        E_COMPILE_ERROR => [
            'type' => 'E_COMPILE_ERROR',
            'description' => 'Fatal compile-time error.'
        ],
        E_COMPILE_WARNING => [
            'type' => 'E_COMPILE_WARNING',
            'description' => 'Compile-time warning.'
        ],
        E_USER_ERROR => [
            'type' => 'E_USER_ERROR',
            'description' => 'User-generated error message.'
        ],
        E_USER_WARNING => [
            'type' => 'E_USER_WARNING',
            'description' => 'User-generated warning message.'
        ],
        E_USER_NOTICE => [
            'type' => 'E_USER_NOTICE',
            'description' => 'User-generated notice message.'
        ],
        E_STRICT => [
            'type' => 'E_STRICT',
            'description' => 'PHP suggest a change.'
        ],
        E_RECOVERABLE_ERROR => [
            'type' => 'E_RECOVERABLE_ERROR',
            'description' => 'Catchable fatal error.'
        ],
        E_DEPRECATED => [
            'type' => 'E_DEPRECATED',
            'description' => 'Run-time notice.'
        ],
        E_USER_DEPRECATED => [
            'type' => 'E_USER_DEPRECATED',
            'description' => 'User-generated warning message.'
        ],
    ];
    /**
     * A constant that is returned by Util::checkSystemStatus() to indicate
     * that the file 'Config.php' is missing.
     *
     * @since 1.2
     */
    const MISSING_CONF_FILE = 'missing_config_file';
    /**
     * A constant that is returned by Util::checkSystemStatus() to indicate
     * that the file 'SiteConfig.php' is missing.
     *
     * @since 1.2
     */
    const MISSING_SITE_CONF_FILE = 'missing_site_config_file';
    /**
     * A constant that is returned by Util::checkSystemStatus() to indicate
     * that system is not configured yet.
     *
     * @since 1.2
     */
    const NEED_CONF = 'sys_conf_err';
    /**
     *
     * @var DatabaseLink
     *
     * @since 1.2
     */
    private static $dbTestInstance;
    /**
     * Disallow creating instances of the class.
     */
    private function __construct() {
    }
    /**
     * Converts a positive integer value to binary string.
     *
     * @param int $intVal The number that will be converted.
     *
     * @return boolean|string If the given value is an integer and it is greater
     * than -1, a string of zeros and ones is returned. Other than that,
     * false is returned.
     *
     * @since 1.3.8
     */
    public static function binaryString($intVal) {
        if (gettype($intVal) == 'integer' && $intVal >= 0) {
            $retVal = '';

            if ($intVal == 0) {
                $retVal = '0';
            } else {
                while ($intVal > 0) {
                    $q = floor($intVal / 2);
                    $bit = $intVal % 2;
                    $retVal = $bit.$retVal;
                    $intVal = $q;
                }
            }

            return $retVal;
        }

        return false;
    }
    public static function extractClassName($filePath) {
        $expl = explode(DS, $filePath);
        $classFile = $expl[count($expl) - 1];
        $firstChar = $classFile[0];

        return strtoupper($firstChar).''.explode('.', substr($classFile, 1))[0];
    }
    /**
     * This method is used to filter scripting code such as
     * JavaScript or PHP.
     *
     * @param string $input
     *
     * @return string
     *
     * @since 0.2
     */
    public static function filterScripts($input) {
        $retVal = str_replace('<script>', '&lt;script&gt;', $input);
        $retVal = str_replace('</script>', '&lt;/script&gt;', $retVal);
        $retVal = str_replace('<?', '&lt;?', $retVal);

        return str_replace('<?php', '&lt;?php', $retVal);
    }
    /**
     * Returns the base URL of the framework.
     *
     * The returned value will depend on the folder where the framework files
     * are located. For example, if your domain is 'example.com' and the framework
     * is placed at the root and the requested resource is 'http://example.com/x/y/z',
     * then the base URL will be 'http://example.com/'. If the framework is
     * placed inside a folder in the server which has the name 'system', and
     * the same resource is requested, then the base URL will be
     * 'http://example.com/system'.
     *
     * @return string The base URL (such as 'http//www.example.com/')
     *
     * @since 0.2
     */
    public static function getBaseURL() {
        $host = trim(filter_var($_SERVER['HTTP_HOST']),'/');

        if (isset($_SERVER['HTTPS'])) {
            $secureHost = filter_var($_SERVER['HTTPS']);
        } else {
            $secureHost = '';
        }
        $protocol = 'http://';
        $useHttp = defined('USE_HTTP') && USE_HTTP === true;

        if (strlen($secureHost) != 0 && !$useHttp) {
            $protocol = "https://";
        }
        $docRoot = filter_var($_SERVER['DOCUMENT_ROOT']);
        $len = strlen($docRoot);
        $toAppend = substr(ROOT_PATH, $len, strlen(ROOT_PATH) - $len);

        if (isset($_SERVER['HTTP_WEBFIORI_REMOVE_PATH'])) {
            $toAppend = str_replace($_SERVER['HTTP_WEBFIORI_REMOVE_PATH'],'' ,$toAppend);
        }
        $xToAppend = str_replace('\\', '/', $toAppend);

        if (strlen($xToAppend) == 0) {
            return $protocol.$host;
        } else {
            return $protocol.$host.'/'.$xToAppend;
        }
    }
    /**
     * Returns the IP address of the user who is connected to the server.
     *
     * @return string The IP address of the user who is connected to the server.
     * The value is taken from the array $_SERVER at index 'REMOTE_ADDR'.
     *
     * @since 1.3.1
     */
    public static function getClientIP() {
        $ip = filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP);

        if ($ip == '::1') {
            return '127.0.0.1';
        } else {
            return $ip;
        }
    }
    /**
     * Returns the instance of 'MySQLLink' which is used to check database
     * connection using the method 'Util::checkDbConnection()'.
     *
     * @return MySQLLink|null The instance of 'MySQLLink' which is used to check database
     * connection using the method 'Util::checkDbConnection()'. If no test was
     * performed, the method will return null.
     *
     * @since 1.2
     */
    public static function getDatabaseTestInstance() {
        return self::$dbTestInstance;
    }
    /**
     * Returns an array that contains the dates of current week's days in Gregorian calendar.
     *
     * The returned array will contain the dates starting from Sunday. The format
     * of the dates will be 'Y-m-d'.
     *
     * @return array an array that contains the dates of current week's days.
     *
     * @since 1.3.4
     */
    public static function getGWeekDates() {
        $startDay = '';
        $startYear = '';
        $startMonth = '';

        $todayNumberInMonth = intval(date('d'));

        $weekStartDayNum = 7;
        $todayNumberInWeek = date('N') ;
        $thisMonth = intval(date('m'));
        $thisYear = date('Y');

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN,$thisMonth,$thisYear);

        if ($todayNumberInWeek >= $weekStartDayNum) {
            $acctuallDayNumberInWeek = $todayNumberInWeek - $weekStartDayNum;
        } else {
            $acctuallDayNumberInWeek = 7 - $startDay + $todayNumberInWeek;
        }
        $backInTime = $todayNumberInMonth - $acctuallDayNumberInWeek;

        if ($backInTime > 0) {
            //we are ok.
            //in the same month.
            $startDay = $backInTime;
            $startMonth = $thisMonth < 10 ? '0'.$thisMonth : $thisMonth;
        } else {
            //we need to go back to prevuse month.
            $prevMonthNum = $thisMonth - 1 != 0 ? $thisMonth - 1 : 12;
            $startMonth = $prevMonthNum < 10 ? '0'.$prevMonthNum : $prevMonthNum;
            $startYear = $prevMonthNum == 12 ? $thisYear - 1 : $thisYear;
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN,$prevMonthNum,$startYear);
            $startDay = $daysInMonth - (-1) * $backInTime;
        }

        return self::_buildGdatesArr($startDay, $daysInMonth, $startMonth);
    }
    /**
     * Returns the number of a day in the week given a date.
     *
     * @param string $date A date string that has the month, the date and
     * year number. The string must be provided in the format 'YYYY-MM-DD'.
     *
     * @return int|boolean ISO-8601 numeric representation of the day that
     * represents the given date in the week. 1 for Monday and 7 for Sunday.
     * If the method fails, it will return false.
     *
     * @since 1.3.4
     */
    public static function getGWeekday($date) {
        $format = 'YYYY-MM-DD';

        if ($format == 'YYYY-MM-DD') {
            return date('N', strtotime($date));
        } else {
            $split = explode('-', $format);
            $dateSplit = explode('-', $date);

            if (count($split) == 3 && count($dateSplit) == 3) {
                //$yearIndex = array_
            }
        }

        return false;
    }
    /**
     * Returns the IPv4 address of server host.
     *
     * @return string The IPv4 address of server host.
     *
     * @since 1.3.1
     */
    public static function getHostIP() {
        $host = gethostname();

        return gethostbyname($host);
    }
    /**
     * Returns the URI of the requested resource.
     *
     * @return string The URI of the requested resource.
     *
     * @since 1.1
     */
    public static function getRequestedURL() {
        $base = self::getBaseURL();
        $uri = getenv('REQUEST_URI');

        if ($uri === false) {
            // Using builtin server, it will be false
            $uri = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';

            return $base.'/'.$uri;
        }
        $requestedURI = trim(filter_var($uri),'/');

        return $base.'/'.$requestedURI;
    }
    /**
     * Returns HTTP request headers.
     *
     * This method will try to extract request headers using two ways,
     * first, it will check if the method 'apache_request_headers()' is
     * exist or not. If it does, then request headers will be taken from
     * there. If it does not exist, it will try to extract request headers
     * from the super global $_SERVER.
     *
     * @return array An associative array of request headers. The indices
     * will represents the headers and the values are the values of the
     * headers. The indices will be all in lower case.
     *
     * @since 1.3.3
     */
    public static function getRequestHeaders() {
        $retVal = [];

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();

            foreach ($headers as $k => $v) {
                $retVal[strtolower($k)] = filter_var($v, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            }
        } else if (isset($_SERVER)) {
            $retVal = self::_getRequestHeadersFromServer();
        }

        return $retVal;
    }
    /**
     * Checks if a given directory exists or not.
     *
     * @param string $dir A string in a form of directory (Such as 'root/home/res').
     *
     * @param boolean $createIfNot If set to true and the given directory does
     * not exists, The method will try to create the directory.
     *
     * @return boolean In general, the method will return false if the
     * given directory does not exists. The method will return true only
     * in two cases, If the directory exits or it does not exists but was created.
     *
     * @since 0.1
     */
    public static function isDirectory($dir,$createIfNot = false) {
        $dirFix = str_replace('\\', '/', $dir);

        if (!is_dir($dirFix)) {
            if ($createIfNot === true && mkdir($dir, 0777 , true)) {
                return true;
            }

            return false;
        }

        return true;
    }
    /**
     * Checks if a given character is an upper case letter or lower case letter.
     *
     * @param char $char A character such as (A B C D " > < ...).
     *
     * @return bool True if the given character is in upper case.
     *
     * @since 0.1
     */
    public static function isUpper($char) {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($char, "UTF-8") != $char;
        } else {
            return strtolower($char) != $char;
        }
    }
    /**
     * Converts a string to its numeric value.
     *
     * @param string $str A string that represents a number.
     *
     * @return int|float|boolean If the given string represents an integer,
     * the value is returned as an integer. If the given string represents a float,
     * the value is returned as a float. If the method is unable to convert
     * the string to its numerical value, it will return false.
     *
     * @since 1.3.5
     */
    public static function numericValue(string $str) {
        $strToConvert = trim($str);
        $len = strlen($strToConvert);

        if ($len == 0) {
            return false;
        }
        $isFloat = false;
        $retVal = false;

        for ($y = 0 ; $y < $len ; $y++) {
            $char = $strToConvert[$y];

            if ($char == '.' && !$isFloat) {
                $isFloat = true;
            } else if ($char == '-' && $y == 0) {
            } else if ($char == '.' && $isFloat) {
                return $retVal;
            } else if (!($char <= '9' && $char >= '0')) {
                return $retVal;
            }
        }

        if ($isFloat) {
            $retVal = floatval($strToConvert);
        } else {
            $retVal = intval($strToConvert);
        }

        return $retVal;
    }
    /**
     * Call the method 'print_r' and insert 'pre' around it.
     *
     * The method is used to make the output well formatted and user
     * readable. Note that if the framework is running through command line
     * interface, the output will be sent to STDOUTE.
     *
     * @param mixed $expr Any variable or value that can be passed to the
     * function 'print_r'.
     *
     * @param boolean $asMessageBox If this attribute is set to true, the output
     * will be shown in a floating message box which can be moved around inside
     * the web page. Default is true. It has no effect in case the framework
     * is running through CLI.
     *
     * @since 1.0
     */
    public static function print_r($expr,$asMessageBox = true) {
        if ($expr === null) {
            $expr = 'null';
        } else if ($expr === true) {
            $expr = 'true';
        } else if ($expr === false) {
            $expr = 'false';
        }
        $debugTrace = debug_backtrace();
        $lastTrace = $debugTrace[0];
        $lineNumber = $lastTrace['line'];
        $file = $lastTrace['file'];
        $readable = print_r($expr, true);

        if (Runner::isCLI()) {
            fprintf(STDOUT, "%s - %s:\n%s\n",$file, $lineNumber, $readable);
        } else {
            $htmlEntityAdded = str_replace('<', '&lt;', str_replace('>', '&gt;', $readable));
            $toOutput = '<pre>'.$htmlEntityAdded.'</pre>';

            if ($asMessageBox === true) {
                $messageBox = new MessageBox();

                if ($messageBox->getBody() !== null) {
                    $messageBox->getBody()->addTextNode($toOutput,false);
                    $messageBox->getHeader()->text($file.' - '.$lineNumber);
                    Response::write($messageBox);
                }
            } else {
                Response::write('<pre>'.$file.' - '.$lineNumber."\n".'</pre>'.$toOutput);
            }
        }
    }
    /**
     * Returns the reverse of a string.
     *
     * This method can be used to reverse the order of any string.
     * For example, if the given string is '   Good Morning Buddy', the
     * method will return 'ydduB gninriM dooG   '. If null is given, the
     * method will return empty string. Note that if the given string is
     * a unicode string, then the method needs mb_ extension to be exist for
     * the output to be correct.
     *
     * @param string $str The string that will be reversed.
     *
     * @return string The string after reversing its order.
     *
     * @since 1.3.7
     */
    public static function reverse($str) {
        $strToReverse = $str.'';
        $retV = '';

        if (function_exists('mb_strlen')) {
            $strLen = mb_strlen($strToReverse);
            $usemb = true;
        } else {
            $strLen = strlen($strToReverse);
            $usemb = false;
        }

        for ($x = $strLen - 1 ; $x >= 0 ; $x--) {
            if ($usemb) {
                $retV .= mb_substr($strToReverse, $x, 1);
            } else {
                $retV .= $strToReverse[$x];
            }
        }

        return $retV;
    }
    /**
     * Returns unicode code of a character.
     *
     * Common values: 32 = space, 10 = new line, 13 = carriage return.
     * Note that this method depends on mb_ functions.
     *
     * @param type $u a character.
     *
     * @return int|false The unicode code of a character. If mb_ library is not
     * loaded, the method will return false.
     *
     * @since 0.2
     */
    public static function uniord($u) {
        if ('mb_convert_encoding') {
            $k = mb_convert_encoding($u, 'UCS-2LE', 'UTF-8');
            $k1 = ord(substr($k, 0, 1));
            $k2 = ord(substr($k, 1, 1));

            return $k2 * 256 + $k1;
        }

        return false;
    }
    private static function _buildGdatesArr($startDay, $daysInMonth, $startMonth) {
        $datesArr = [];

        for ($x = 0 ; $x < 7 ; $x++) {
            if ($startDay > $daysInMonth) {
                $startDay = 1;
                $startMonth += 1;
                $startMonth = $startMonth > 12 ? 1 : $startMonth;
                $startMonth = $startMonth < 10 ? '0'.$startMonth : $startMonth;
                $startYear = $startMonth == 1 ? $startYear + 1 : $startYear;
            }
            $startDay = $startDay < 10 ? '0'.$startDay : $startDay;
            $datesArr[] = $startYear.'-'.$startMonth.'-'.$startDay;
            $startDay += 1;
        }

        return $datesArr;
    }
    /**
     * Collect request headers from the array $_SERVER.
     * @return array
     */
    private static function _getRequestHeadersFromServer() {
        $retVal = [];

        foreach ($_SERVER as $k => $v) {
            $split = explode('_', $k);

            if ($split[0] == 'HTTP') {
                $headerName = '';
                $count = count($split);

                for ($x = 0 ; $x < $count ; $x++) {
                    if ($x + 1 == $count && $split[$x] != 'HTTP') {
                        $headerName = $headerName.$split[$x];
                    } else if ($x == 1 && $split[$x] != 'HTTP') {
                        $headerName = $split[$x].'-';
                    } else if ($split[$x] != 'HTTP') {
                        $headerName = $headerName.$split[$x].'-';
                    }
                }
                $retVal[strtolower($headerName)] = filter_var($v, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            }
        }

        return $retVal;
    }
}
