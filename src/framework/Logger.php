<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace webfiori\framework;

use webfiori\collections\Stack;
/**
 * A class that is used to log messages to a file.
 *
 * @author Ibrahim
 * @version 1.1.3
 * 
 */
class Logger {
    /**
     * An array which contains a key that describes the meaning of a log message.
     * 
     * @since 1.1
     */
    const MESSSAGE_TYPES = [
        'DEBUG','INFO','ERROR','WARNING'
    ];
    /** 
     * The directory at which the log file will be stored in.
     * 
     * @var string
     * 
     * @since 1.0  
     */
    private $directory;
    /**
     * A stack that contains all the called methods and functions.
     * 
     * @var Stack
     * 
     * @since 1.0 
     */
    private $functionsStack;
    /**
     * A link to the log file.
     * 
     * @var resource
     * 
     * @since 1.0   
     */
    private $handelr;
    /**
     * A boolean value that is set to true if logging is enabled.
     * 
     * @var boolean
     * 
     * @since 1.0
     *  
     */
    private $isEnabled;
    /**
     * The name of the log file.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $logFileName;
    /**
     * An instance of 'Logger'.
     * 
     * @var Logger 
     */
    private static $logger;
    /**
     * An array that contains all log messages and in which log file 
     * the message will be stored. 
     *
     * @var array
     * 
     * @since 1.0 
     */
    private $logMessagesArr;
    private function __construct() {
        if (defined('ROOT_DIR')) {
            $this->_setDirectory(ROOT_DIR.DS.'app'.DS.'storage'.DS.'logs');
        } else {
            $this->_setDirectory('/logs');
        }
        $this->logMessagesArr = [];
        $this->_setLogName('log');
        $this->isEnabled = false;
        $this->functionsStack = new Stack();
    }
    /**
     * Returns a stack which contains all the called functions and methods.
     * 
     * @return Stack An instance of the class 'Stack'.
     * 
     * @since 1.1.1
     */
    public static function callStack() {
        return self::_get()->functionsStack;
    }
    /**
     * Removes the whole content of the log file.
     * Once the content of the log is cleared, a message at the top of the log 
     * will appear. The message will say the following:
     * <p>---------------Log Cleared At YYYY-MM-DD HH:MM:SS +00---------------</p>
     * The '+00' is the code of the time zone.
     * 
     * @since 1.0
     */
    public static function clear() {
        self::_get()->_clearLog();
    }
    /**
     * Sets or returns the full directory of the log file.
     * Note that If the given directory does not exists, the method will 
     * try to create it. The default place for saving logs is ROOT_DIR.'/logs'.
     * @param string $new If provided, the save directory will be set to the 
     * given one. 
     * 
     * @return string The location where the log files are stored.
     * 
     * @since 1.0
     */
    public static function directory($new = null) {
        if ($new !== null && strlen($new) != 0) {
            self::_get()->_setDirectory($new, true);
        }

        return self::_get()->_getDirectory();
    }

    /**
     * Show log content as output on screen.
     * 
     * This function simply open the log file and display it as output using 
     * 'echo' command.
     * 
     * @since 1.1.1
     */
    public static function displayLog() {
        self::_get()->_displayLog();
    }
    /**
     * Enable, disable or check if logging is enabled.
     * 
     * @param boolean $isEnabled If provided and set to true, logging will be 
     * enabled. If provided and not true, logging will be disabled.
     * 
     * @return boolean The method will return true if logging is enabled. 
     * false otherwise. Default return value is false which means that the 
     * logger is disabled.
     * 
     * @since 1.0
     */
    public static function enabled($isEnabled = null) {
        if ($isEnabled !== null) {
            self::_get()->_setEnabled($isEnabled);
        }

        return self::_get()->_isEnabled();
    }
    /**
     * Returns a singleton of the class.
     * 
     * @return Logger
     * 
     * @since 1.1
     */
    public static function get() {
        return self::_get();
    }
    /**
     * Returns a string that represents the absolute path to the log file location.
     * 
     * @return string A string that represents the absolute path to the log file location. 
     * Note that the extension '.log' will be appended to the end of the string.
     * 
     * @since 1.1.3
     */
    public static function getAbsolutePath() {
        return self::get()->_getAbsolutePath();
    }
    /**
     * Returns an array that contains all log messages.
     * 
     * @param string $logName An optional log name. If provided, only log messages 
     * of the given log will be returned.
     * 
     * @return array The array that will be returned will be associative. The 
     * keys will be logs names and the values are logged messages.
     */
    public static function getLogsArray($logName = null) {
        if (isset(self::get()->logMessagesArr[$logName])) {
            return self::get()->logMessagesArr[$logName];
        }

        return self::get()->logMessagesArr;
    }
    /**
     * Writes a message to the log file.
     * 
     * @param string $message The message that will be written.
     * 
     * @param string $messageType The type of the message that will be logged. 
     * it can have one of 4 values, 'info', 'warning', 'error' or 'debug'. Note 
     * that the last type will be logged only if the constant 'DEBUG' is defined. 
     * The default value is 'info'.
     * 
     * @param string $logName The name of the log file. If it is not 
     * null, the log will be written to the given file name.
     * 
     * @param boolean $addDashes If set to true, a line of dashes will be inserted 
     * after the message. Used to organize log messages.
     * 
     * @since 1.0
     */
    public static function log($message,$messageType = 'info',$logName = null,$addDashes = false) {
        $logMessage = '';

        if (gettype($message) == 'array') {
            $logMessage = "\r\n".self::_createMessageArray($message);
        } else {
            $logMessage = $message;
        }
        self::logName($logName);
        self::_get()->_writeToLog($logMessage,$messageType,$addDashes);
    }
    /**
     * Adds a debug message to a log file that says the given method or function was called. 
     * 
     * The message will be logged only if the constant 'DEBUG' is defined.
     * 
     * @param string $funcName The name of the function or the method. To get the 
     * name of the function in its body, Use the magic constant '__FUNCTION__'. 
     * To get the name of a method inside class, use the magic constant '__METHOD__'.
     * It is recommended to always use '__METHOD__' as this constant will return 
     * class name with it if the method is inside a class.
     * 
     * @param string $logFileName The name of the log file. If it is not 
     * null, the log will be written to the given file name.
     * 
     * @param string $addDashes If set to true, a line of dashes will be inserted 
     * after the message. Used to organize log messages.
     * 
     * @since 1.1
     */
    public static function logFuncCall($funcName,$logFileName = null,$addDashes = false) {
        self::_get()->_logFuncCall($funcName, $logFileName, $addDashes);
    }
    /**
     * Adds a debug message to a log file that says the execution of a given 
     * function or a method was finished. 
     * 
     * Note that the message will be logged only if the constant 
     * 'DEBUG' is defined. To get the 
     * name of the function in its body, Use the magic constant '__FUNCTION__'. 
     * To get the name of a method inside class, use the magic constant '__METHOD__'.
     * It is recommended to always use '__METHOD__' as this constant will return 
     * class name with it if the function is inside a class.
     * 
     * @param string $funcName The name of the function or method. 
     * 
     * @param string $logFileName The name of the log file. If it is not 
     * null, the log will be written to the given file name. Default is null.
     * 
     * @param string $addDashes If set to true, a line of dashes will be inserted 
     * after the message. Used to organize log messages.
     * 
     * @since 1.1
     */
    public static function logFuncReturn($funcName,$logFileName = null,$addDashes = false) {
        self::_get()->_logFuncReturn($funcName, $logFileName, $addDashes);
    }
    /**
     * Sets or returns the name of the log file.
     * 
     * This method is used to switch between different log files. The 
     * name should be provided without any extentions (e.g. 'my-log'). 
     * Note that log files will always have the 
     * extention .txt The default log file name is 'log.txt'.
     * 
     * @param string $new The name of the log file that the system will be writing 
     * logs to.
     * 
     * @return string The method will return the name of the log file that the 
     * logger is using to write logs (without extension). 
     * 
     * @since 1.0
     */
    public static function logName($new = null) {
        if ($new !== null && strlen($new) != 0) {
            self::_get()->_setLogName($new);
        }

        return self::_get()->_getLogName();
    }
    /**
     * Adds a log message to log function or method's return value (debug).
     * 
     * @param mixed $val The return value of a function.
     * 
     * @param type $logName The name of the log file. If it is not 
     * null, the log will be written to the given file name.
     * 
     * @param boolean $addDashes If set to true, a line of dashes will be inserted 
     * after the message. Used to organize log messages.
     * 
     * @since 1.1
     */
    public static function logReturnValue($val,$logName = null,$addDashes = false) {
        if (gettype($val) == 'array') {
            Logger::log('Return value = (array).'."\r\n".self::_createMessageArray($val),'debug', $logName);
        } else {
            Logger::log('Return value = \''.$val.'\' ('.gettype($val).').','debug', $logName, $addDashes);
        }
    }
    /**
     * Adds a message to the last selected log file that states the client 
     * request was processed. 
     * 
     * This method is usually called after calling 
     * the function 'die()' or 'exit()'. Also if no server code will be 
     * executed after. The exact message that will be logged is:
     * <p>"Processing of client request is finished."</p>
     * 
     * @since 1.1
     */
    public static function requestCompleted() {
        Logger::log('Processing of client request is finished.', 'info', null, true);
    }
    /**
     * Adds a new line to separate log parts.
     * T
     * he line will have the following text:
     * <p>-+-*******************************************************-+-</p>
     * 
     * @since 1.1.1
     */
    public static function section() {
        self::_get()->_newSec();
    }
    public static function storeLogs() {
        $logsArr = self::get()->logMessagesArr;

        foreach ($logsArr as $logContent) {
            $storePath = $logContent['path'];
            $logStr = '';

            foreach ($logContent as $contentArr) {
                if (gettype($contentArr) == 'array') {
                    if ($contentArr['function'] !== null) {
                        $logStr .= '['.$contentArr['timestamp'].'] '.self::get()->addSpaces($contentArr['type']).': ['.$contentArr['function'].'] '.$contentArr['message']."\r\n";
                    } else {
                        $logStr .= '['.$contentArr['timestamp'].'] '.self::get()->addSpaces($contentArr['type']).': '.$contentArr['message']."\r\n";
                    }
                }
            }
            $handle = fopen($storePath, 'a+');
            fwrite($handle, $logStr);
            fclose($handle);
        }
    }
    /**
     * @since 1.0
     */
    private function _clearLog() {
        $this->logMessagesArr[$this->_getLogName()] = [
            'path' => $this->getAbsolutePath()
        ];
        $this->log('---------------Log Cleared At '.date('Y-m-d H:i:s (T)').'---------------'."\r\n");
    }
    /**
     * Generates a readable string which represents an array.
     * 
     * @param type $arr
     * 
     * @param type $depth
     * 
     * @return type
     * 
     * @since 1.1.2
     */
    private static function _createMessageArray($arr,$depth = 0,$outerSpace = '') {
        $retVal = 'Array:{x'."\r\n";
        $innerSpace = '';
        $loop = $depth != 0 ? (4) * ($depth + 1) : 4;

        for ($x = 0 ; $x < $loop ; $x++) {
            $innerSpace .= ' ';
        }

        foreach ($arr as $k => $v) {
            if (gettype($v) == 'array') {
                $retVal .= $innerSpace.'['.$k.']=>'.self::_createMessageArray($v, $depth + 1,$innerSpace)."\r\n";
            } else {
                $retVal .= $innerSpace.'['.$k.']=>'.$v."\r\n";
            }
        }

        return $retVal.$outerSpace.'}';
    }
    /**
     * Show log content in web browser.
     * 
     * @since 1.1.1
     */
    private function _displayLog() {
        $logDir = $this->getAbsolutePath();

        if (file_exists($logDir)) {
            $this->handelr = fopen($logDir, 'r');
            $logData = fread($this->handelr, filesize($logDir));
            Util::print_r($logData);
        } else {
            Util::print_r('------------NO LOG FILE WAS FOUND WHICH HAS GIVEN NAME------------');
        }
    }
    /**
     * Returns a singleton of the class.
     * 
     * @return Logger
     * 
     * @since 1.0
     */
    private static function _get() {
        if (self::$logger === null) {
            self::$logger = new Logger();
        }

        return self::$logger;
    }
    private function _getAbsolutePath() {
        return $this->directory.DS.$this->logFileName.'.log';
    }
    /**
     * 
     * @return type
     * 
     * @since 1.0
     */
    private function _getDirectory() {
        return $this->directory;
    }
    /**
     * 
     * @return type
     * 
     * @since 1.0
     */
    private function _getLogName() {
        return $this->logFileName;
    }
    /**
     * 
     * @return type
     * 
     * @since 1.0
     */
    private function _isEnabled() {
        return $this->isEnabled;
    }
    /**
     * 
     * @param type $funcName
     * @param type $logFileName
     * @param type $addDashes
     * @since 1.1
     */
    private function _logFuncCall($funcName,$logFileName = null,$addDashes = false) {
        $this->log('A call to the function <'.$funcName.'>', 'debug', $logFileName, $addDashes);
        $this->functionsStack->push($funcName);
    }
    private function _logFuncReturn($funcName,$logFileName = null,$addDashes = false) {
        $this->functionsStack->pop();
        $this->log('Return back from <'.$funcName.'>', 'debug', $logFileName,$addDashes);
    }
    /**
     * Add new line which contains asterisks to separate parts of log file.
     * @since 1.1.1
     */
    private function _newSec() {
        if (self::enabled()) {
            $this->log('-+-*******************************************************-+-');
        }
    }
    /**
     * 
     * @param type $dir
     * @param type $create
     * @since 1.0
     */
    private function _setDirectory($dir,$create = true) {
        if (Util::isDirectory($dir, $create)) {
            $this->directory = $dir;
        }
    }
    /**
     * 
     * @param type $bool
     * @since 1.0
     */
    private function _setEnabled($bool) {
        $this->isEnabled = $bool === true ? true : false;
    }
    /**
     * 
     * @param type $name
     * @since 1.0
     */
    private function _setLogName($name) {
        $trimmed = trim($name);

        if (strlen($trimmed) != 0) {
            $this->logFileName = $trimmed;

            if (!isset($this->logMessagesArr[$trimmed])) {
                $this->logMessagesArr[$trimmed] = [
                    'path' => $this->_getAbsolutePath()
                ];
            }
        }
    }
    /**
     * 
     * @param type $content
     * @param type $addDashes
     * @since 1.0
     */
    private function _writeToLog($content,$type = '',$addDashes = false) {
        if ($this->_isEnabled()) {
            $upperType = strtoupper($type);
            $bType = in_array($upperType, self::MESSSAGE_TYPES) ? $upperType : 'INFO';

            if (!($bType == 'DEBUG' && !(defined('DEBUG')))) {
                $this->logMessagesArr[$this->_getLogName()][] = [
                    'timestamp' => date('Y-m-d H:i:s T'),
                    'type' => $bType,
                    'function' => $this->functionsStack->peek(),
                    'message' => $content
                ];
                $addDashes === true ? $this->_newSec() : null;
            }
        }
    }
    private function addSpaces($bType) {
        for ($x = strlen($bType) ; $x < 10 ; $x++) {
            $bType = ' '.$bType;
        }

        return $bType;
    }
}
