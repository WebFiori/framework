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
namespace webfiori\entity;
use phpStructs\Stack;
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
 * A class that is used to log messages to a file.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.1.2
 */
class Logger {
    /**
     * An array which contains a key that describes the meaning of a log message.
     * @since 1.1
     */
    const MESSSAGE_TYPES = array(
        'DEBUG','INFO','ERROR','WARNING'
    );
    /**
     * A stack that contains all the called methods and functions.
     * @var Stack
     * @since 1.0 
     */
    private $functionsStack;
    /**
     * An instance of 'Logger'.
     * @var Logger 
     */
    private static $logger;
    /**
     * A link to the log file.
     * @var resource
     * @since 1.0   
     */
    private $handelr;
    /**
     * The name of the log file.
     * @var string
     * @since 1.0 
     */
    private $logFileName;
    /** 
     * The directory at which the log file will be stored in.
     * @var string
     * @since 1.0  
     */
    private $directory;
    /**
     * A boolean value that is set to true if logging is enabled.
     * @var boolean
     * @since 1.0
     *  
     */
    private $isEnabled;
    private function __construct() {
        if(defined('ROOT_DIR')){
            $this->_setDirectory(ROOT_DIR.'/logs');
        }
        else{
            $this->_setDirectory('/logs');
        }
        $this->_setLogName('log');
        $this->isEnabled = false;
        $this->functionsStack = new Stack();
    }
    /**
     * Returns a singleton of the class.
     * @return Logger
     * @since 1.0
     */
    private static function _get(){
        if(self::$logger === null){
            self::$logger = new Logger();
        }
        return self::$logger;
    }
    /**
     * Returns a singleton of the class.
     * @return Logger
     * @since 1.1
     */
    public static function get(){
        return self::_get();
    }
    /**
     * Returns a stack which contains all the called functions and methods.
     * @return Stack An instance of the class 'Stack'.
     * @since 1.1.1
     */
    public static function callStack(){
        return self::_get()->functionsStack;
    }
    /**
     * Adds a log message to log function or method's return value (debug).
     * @param mixed $val The return value of a function.
     * @param type $logName The name of the log file. If it is not 
     * null, the log will be written to the given file name.
     * @param boolean $addDashes If set to true, a line of dashes will be inserted 
     * after the message. Used to organize log messages.
     * @since 1.1
     */
    public static function logReturnValue($val,$logName=null,$addDashes=false) {
        if(gettype($val) == 'array'){
            Logger::log('Return value = (array).'."\r\n".self::_createMessageArray($val),'debug', $logName);
        }
        else{
            Logger::log('Return value = \''.$val.'\' ('. gettype($val).').','debug', $logName, $addDashes);
        }
    }
    /**
     * Enable, disable or check if logging is enabled.
     * @param boolean $isEnabled If provided and set to true, logging will be 
     * enabled. If provided and not true, logging will be disabled.
     * @return boolean The method will return true if logging is enabled. 
     * false otherwise. Default return value is false which means that the 
     * logger is disabled.
     * @since 1.0
     */
    public static function enabled($isEnabled=null) {
        if($isEnabled !== null){
            self::_get()->_setEnabled($isEnabled);
        }
        return self::_get()->_isEnabled();
    }
    /**
     * Writes a message to the log file.
     * @param string $message The message that will be written.
     * @param string $messageType The type of the message that will be logged. 
     * it can have one of 4 values, 'info', 'warning', 'error' or 'debug'. Note 
     * that the last type will be logged only if the constant 'DEBUG' is defined. 
     * The default value is 'info'.
     * @param string $logName The name of the log file. If it is not 
     * null, the log will be written to the given file name.
     * @param boolean $addDashes If set to true, a line of dashes will be inserted 
     * after the message. Used to organize log messages.
     * @since 1.0
     */
    public static function log($message,$messageType='info',$logName=null,$addDashes=false){
        $logMessage = '';
        if(gettype($message) == 'array'){
            $logMessage = "\r\n".self::_createMessageArray($message);
        }
        else{
            $logMessage = $message;
        }
        self::logName($logName);
        self::_get()->_writeToLog($logMessage,$messageType,$addDashes);
    }
    /**
     * Generates a readable string which represents an array.
     * @param type $arr
     * @param type $depth
     * @return type
     * @since 1.1.2
     */
    private static function _createMessageArray($arr,$depth=0,$outerSpace=''){
        $retVal = 'Array:{x'."\r\n";
        $innerSpace = '';
        $loop = $depth != 0 ? (4)*($depth + 1) : 4;
        for($x = 0 ; $x < $loop ; $x++){
            $innerSpace .= ' ';
        }
        foreach ($arr as $k => $v){
            if(gettype($v) == 'array'){
                $retVal .= $innerSpace.'['.$k.']=>'.self::_createMessageArray($v, $depth + 1,$innerSpace)."\r\n";
            }
            else{
                $retVal .= $innerSpace.'['.$k.']=>'.$v."\r\n";
            }
        }
        return $retVal.$outerSpace.'}';
    }
    /**
     * Adds a debug message to a log file that says the given method or function was called. 
     * The message will be logged only if the constant 'DEBUG' is defined.
     * @param string $funcName The name of the function or the method. To get the 
     * name of the function in its body, Use the magic constant '__FUNCTION__'. 
     * To get the name of a method inside class, use the magic constant '__METHOD__'.
     * It is recommended to always use '__METHOD__' as this constant will return 
     * class name with it if the method is inside a class.
     * @param string $logFileName The name of the log file. If it is not 
     * null, the log will be written to the given file name.
     * @param string $addDashes If set to true, a line of dashes will be inserted 
     * after the message. Used to organize log messages.
     * @since 1.1
     */
    public static function logFuncCall($funcName,$logFileName=null,$addDashes=false) {
        self::_get()->_logFuncCall($funcName, $logFileName, $addDashes);
    }
    /**
     * Adds a debug message to a log file that says the execution of a given 
     * function or a method was finished. 
     * Note that the message will be logged only if the constant 
     * 'DEBUG' is defined. To get the 
     * name of the function in its body, Use the magic constant '__FUNCTION__'. 
     * To get the name of a method inside class, use the magic constant '__METHOD__'.
     * It is recommended to always use '__METHOD__' as this constant will return 
     * class name with it if the function is inside a class.
     * @param string $funcName The name of the function or method. 
     * @param string $logFileName The name of the log file. If it is not 
     * null, the log will be written to the given file name. Default is null.
     * @param string $addDashes If set to true, a line of dashes will be inserted 
     * after the message. Used to organize log messages.
     * @since 1.1
     */
    public static function logFuncReturn($funcName,$logFileName=null,$addDashes=false) {
        self::_get()->_logFuncReturn($funcName, $logFileName, $addDashes);
    }
    /**
     * Adds a message to the last selected log file that states the client 
     * request was processed. 
     * This method is usually called after calling 
     * the function 'die()' or 'exit()'. Also if no server code will be 
     * executed after. The exact message that will be logged is:
     * <p>"Processing of client request is finished."</p>
     * @since 1.1
     */
    public static function requestCompleted() {
        Logger::log('Processing of client request is finished.', 'info', null, true);
    }
    /**
     * Sets or returns the full directory of the log file.
     * Note that If the given directory does not exists, the method will 
     * try to create it. The default place for saving logs is ROOT_DIR.'/logs'.
     * @param string $new If provided, the save directory will be set to the 
     * given one. 
     * @return string The location where the log files are stored.
     * @since 1.0
     */
    public static function directory($new=null) {
        if($new !== null && strlen($new) != 0){
            self::_get()->_setDirectory($new, true);
        }
        return self::_get()->_getDirectory();
    }
    /**
     * Sets or returns the name of the log file.
     * This method is used to switch between different log files. The 
     * name should be provided without any extentions (e.g. 'my-log'). 
     * Note that log files will always have the 
     * extention .txt The default log file name is 'log.txt'.
     * @param string $new The name of the log file that the system will be writing 
     * logs to.
     * @return string The method will return the name of the log file that the 
     * logger is using to write logs (without extension). 
     * @since 1.0
     */
    public static function logName($new=null) {
        if($new !== null && strlen($new) != 0){
            self::_get()->_setLogName($new);
        }
        return self::_get()->_getLogName();
    }
    /**
     * Adds a new line to separate log parts.
     * The line will have the following text:
     * <p>-+-*******************************************************-+-</p>
     * @since 1.1.1
     */
    public static function section(){
        self::_get()->_newSec();
    }
    /**
     * Removes the whole content of the log file.
     * Once the content of the log is cleared, a message at the top of the log 
     * will appear. The message will say the following:
     * <p>---------------Log Cleared At YYYY-MM-DD HH:MM:SS +00---------------</p>
     * The '+00' is the code of the time zone.
     * @since 1.0
     */
    public static function clear(){
        self::_get()->_clearLog();
    }
    /**
     * @since 1.0
     */
    private function _clearLog() {
        $this->handelr = fopen($this->_getDirectory().'/'.$this->_getLogName().'.txt', 'w+');
        $time = date('Y-m-d H:i:s T');
        fwrite($this->handelr, '---------------Log Cleared At '.$time.'---------------'."\r\n");
        fclose($this->handelr);
    }
    /**
     * 
     * @param type $funcName
     * @param type $logFileName
     * @param type $addDashes
     * @since 1.1
     */
    private function _logFuncCall($funcName,$logFileName=null,$addDashes=false) {
        $this->log('A call to the function <'.$funcName.'>', 'debug', $logFileName, $addDashes);
        $this->functionsStack->push($funcName);
    }
    private function _logFuncReturn($funcName,$logFileName=null,$addDashes=false) {
        $this->functionsStack->pop();
        $this->log('Return back from <'.$funcName.'>', 'debug', $logFileName,$addDashes);
    }
    /**
     * 
     * @param type $bool
     * @since 1.0
     */
    private function _setEnabled($bool){
        $this->isEnabled = $bool === true ? true : false;
    }
    /**
     * 
     * @return type
     * @since 1.0
     */
    private function _isEnabled() {
        return $this->isEnabled;
    }
    /**
     * 
     * @param type $name
     * @since 1.0
     */
    private function _setLogName($name) {
        $this->logFileName = $name;
    }
    /**
     * 
     * @return type
     * @since 1.0
     */
    private function _getLogName() {
        return $this->logFileName;
    }
    /**
     * 
     * @param type $dir
     * @param type $create
     * @since 1.0
     */
    private function _setDirectory($dir,$create=true){
        if(Util::isDirectory($dir, $create)){
            $this->directory = $dir;
        }
    }
    /**
     * 
     * @return type
     * @since 1.0
     */
    private function _getDirectory() {
        return $this->directory;
    }
    /**
     * 
     * @param type $content
     * @param type $addDashes
     * @since 1.0
     */
    private function _writeToLog($content,$type='',$addDashes=false) {
        if($this->_isEnabled()){
            $upperType = strtoupper($type);
            $bType = in_array($upperType, self::MESSSAGE_TYPES) ? $upperType : 'INFO';
            if($bType == 'DEBUG' && !(defined('DEBUG'))){
                
            }
            else{
                $this->handelr = fopen($this->_getDirectory().'/'.$this->_getLogName().'.txt', 'a+');
                $time = date('Y-m-d H:i:s T');
                if($this->functionsStack->size() != 0){
                    $message = '['.$time.'] '.$this->addSpaces($bType).': ['.$this->functionsStack->peek().'] '.$content."\r\n";
                    fwrite($this->handelr, $message);
                }
                else{
                    $message = '['.$time.'] '.$this->addSpaces($bType).': '.$content."\r\n";
                    fwrite($this->handelr, $message);
                }
                fclose($this->handelr);
                $addDashes === true ? $this->_newSec() : null;
            }
        }
    }
    /**
     * A function that is used to add spaces before message type name to make 
     * messages well formatted in the log.
     * @param string $bType
     * @return string
     */
    private function addSpaces($bType){
        for($x = strlen($bType) ; $x < 10 ; $x++){
            $bType = ' '.$bType;
        }
        return $bType;
    }

    /**
     * Show log content as output on screen.
     * This function simply open the log file and display it as output using 
     * 'echo' command.
     * @since 1.1.1
     */
    public static function displayLog() {
        self::_get()->_displayLog();
    }
    /**
     * Show log content in web browser.
     * @since 1.1.1
     */
    private function _displayLog(){
        $logDir = $this->_getDirectory().'/'.$this->_getLogName().'.txt';
        if(file_exists($logDir)){
            $this->handelr = fopen($logDir, 'r');
            $logData = fread($this->handelr, filesize($logDir));
            Util::print_r($logData);
        }
        else{
            Util::print_r('------------NO LOG FILE WAS FOUND WHICH HAS GIVEN NAME------------');
        }
    }
    /**
     * Add new line which contains asterisks to separate parts of log file.
     * @since 1.1.1
     */
    private function _newSec(){
        if(self::enabled()){
            $this->handelr = fopen($this->_getDirectory().'/'.$this->_getLogName().'.txt', 'a+');
            fwrite($this->handelr, '-+-*******************************************************-+-'."\r\n");
            fclose($this->handelr);
        }
    }
}
