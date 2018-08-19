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

/**
 * A class that is used to log messages to a file.
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 */
class Logger {
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
    private function __construct() {
        $this->_setDirectory(ROOT_DIR.'/logs');
        $this->_setLogName('log');
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
     * Writes a message to the log file.
     * @param string $message The message that will be written.
     * @param boolean $addDashes If set to true, a line of dashes will be inserted 
     * after the message. Used to organize log messages.
     * @since 1.0
     */
    public static function log($message,$addDashes=false){
        self::_get()->writeToLog($message,$addDashes);
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
    
    private function _setLogName($name) {
        $this->logFileName = $name;
    }
    private function _getLogName() {
        return $this->logFileName;
    }
    private function _setDirectory($dir,$create=true){
        if(Util::isDirectory($dir, $create)){
            $this->directory = $dir;
        }
    }
    private function _getDirectory() {
        return $this->directory;
    }
    private function writeToLog($content,$addDashes=false) {
        $this->handelr = fopen($this->_getDirectory().'/'.$this->_getLogName().'.txt', 'a+');
        $time = date('Y-m-d h:i:s');
        fwrite($this->handelr, '['.$time.']  '.$content."\n");
        if($addDashes === TRUE){
            fwrite($this->handelr, '-------------------------------------'."\n");
        }
        fclose($this->handelr);
    }
}
