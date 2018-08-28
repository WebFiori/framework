<?php
/*
 * The MIT License
 *
 * Copyright 2018 ibrah.
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
 * @version 1.1
 */
class Logger {
    /**
     * An array which contains a key that describes the meaning of a log message.
     * @since 1.0
     */
    const MESSSAGE_TYPES = array(
        'DEBUG','INFO','ERROR','WARNING'
    );

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
        $this->isEnabled = FALSE;
    }
    /**
     * Returns a singleton of the class.
     * @return Logger
     * @since 1.0
     */
    private static function _get(){
        if(self::$logger === NULL){
            self::$logger = new Logger();
        }
        return self::$logger;
    }
    /**
     * Enable, disable or check if logging is enabled.
     * @param boolean $isEnabled If provided and set to true, logging will be 
     * enabled. If provided and not true, logging will be disabled.
     * @return boolean The function will return TRUE if logging is enabled. 
     * false otherwise. Default return value is false which means that the 
     * logger is disabled initially.
     * @since 1.0
     */
    public static function enabled($isEnabled=null) {
        if($isEnabled !== NULL){
            self::_get()->_setEnabled($isEnabled);
        }
        return self::_get()->_isEnabled();
    }
    /**
     * Writes a message to the log file.
     * @param string $message The message that will be written.
     * @param string $logName [Optional] The name of the log file. If it is not 
     * NULL, the log will be written to the given file name.
     * @param boolean $addDashes If set to true, a line of dashes will be inserted 
     * after the message. Used to organize log messages.
     * @since 1.0
     */
    public static function log($message,$messageType='info',$logName=null,$addDashes=false){
        self::logName($logName);
        self::_get()->writeToLog($message,$messageType,$addDashes);
    }
    /**
     * Sets or returns the full directory of the log file.
     * @param string $new If provided, the save directory will be set to the 
     * given one. If the given directory does not exists, the function will 
     * try to create it. The default place for saving logs is ROOT_DIR.'/logs'.
     * @return string The location where the log files are stored. The default 
     * place for saving logs is ROOT_DIR.'/logs'.
     * @since 1.0
     */
    public static function directory($new=null) {
        if($new !== NULL && strlen($new) != 0){
            self::_get()->_setDirectory($new, TRUE);
        }
        return self::_get()->_getDirectory();
    }
    /**
     * Sets or returns the name of the log file.
     * @param string $new The name of the log file that the system will be writing 
     * logs to. This function is used to switch between different log files. The 
     * name should be provided without any extentions (e.g. 'my-log').
     * @return string The function will return the name of the log file that the 
     * logger is using to write logs. Note that log files will always have the 
     * extention .txt The default log file name is 'log.txt'.
     * @since 1.0
     */
    public static function logName($new=null) {
        if($new !== NULL && strlen($new) != 0){
            self::_get()->_setLogName($new);
        }
        return self::_get()->_getLogName();
    }
    /**
     * Removes the whole content of the log file.
     * @since 1.0
     */
    public static function clear(){
        self::_get()->clearLog();
    }
    /**
     * @since 1.0
     */
    private function clearLog() {
        file_put_contents($this->_getLogName(), "");
    }
    /**
     * 
     * @param type $bool
     * @since 1.0
     */
    private function _setEnabled($bool){
        $this->isEnabled = $bool === TRUE ? TRUE : FALSE;
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
    private function writeToLog($content,$type='',$addDashes=false) {
        if($this->_isEnabled()){
            $upperType = strtoupper($type);
            $bType = in_array($upperType, self::MESSSAGE_TYPES) ? $upperType : 'INFO';
            $this->handelr = fopen($this->_getDirectory().'/'.$this->_getLogName().'.txt', 'a+');
            $time = date('Y-m-d h:i:s T');
            fwrite($this->handelr, '['.$time.']  '.$bType.': '.$content."\n");
            if($addDashes === TRUE){
                fwrite($this->handelr, '-------------------------------------'."\n");
            }
            fclose($this->handelr);
        }
    }
}
