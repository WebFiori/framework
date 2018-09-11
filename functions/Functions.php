<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
 * The base class for creating application logic and connecting to the database.
 *
 * @author Ibrahim
 * @version 1.3
 */
class Functions {
    /**
     *
     * @var string
     * @since 1.3
     */
    private $sessionName;
    /**
     *
     * @var array
     * @since 1.3 
     */
    private static $sessions;
    /**
     * A constant that indicates a user is not authorized to perform specific 
     * function.
     * @var string 
     * @since 1.0
     */
    const NOT_AUTH = 'not_autherized';
    /**
     * A constant that indicates a given method parameter is an empty string.
     * @var string Constant that indicates a given method parameter is an empty string.
     * @since 1.0
     */
    const EMPTY_STRING = 'emp_string';
    /**
     * Creates new instance of the class.
     * @param string $linkedSessionName The name of the session that will 
     * be linked with the class instance. The name can consist of any character 
     * other than space, comma, semi-colon and equal sign. If the name has one 
     * of the given characters, the session will have new randomly generated name.
     * @since 1.0
     */
    public function __construct($linkedSessionName='pa-session') {
        Logger::logFuncCall(__METHOD__);
        if(self::$sessions === NULL){
            Logger::log('Initializing sessions array...');
            self::$sessions = array();
        }
        Logger::log('Initializing linked session...');
        $linkedSession = new SessionManager($linkedSessionName);
        $linkedSession->initSession();
        Logger::log('Setting linked session name...');
        $this->sessionName = $linkedSession->getName();
        Logger::log('Linked session name = \''.$this->sessionName.'\'.', 'debug');
        self::$sessions[$linkedSession->getName()] = $linkedSession;
        Logger::log('Finished initializing linked session.');
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Initiate database connection.
     * @since 1.1
     */
    public function useDatabase($optionalConnectionParams=array()) {
        Logger::logFuncCall(__METHOD__);
        $systemStatus = Util::checkSystemStatus();
        $dbLink = $this->getDBLink();
        if($systemStatus === TRUE){
            Logger::log('Checking if optional database parameters are provided or not...');
            if($optionalConnectionParams != NULL && isset($optionalConnectionParams['host'])
                    && isset($optionalConnectionParams['user']) &&
                    isset($optionalConnectionParams['pass']) && 
                    isset($optionalConnectionParams['db-name'])){
                Logger::log('They are provided.');
                $retVal =  $this->_connect($optionalConnectionParams);
                Logger::logFuncReturn(__METHOD__);
                return $retVal;
            }
            else{
                Logger::log('No optional paameters give. Cheking if already connected...');
                if(!$dbLink->isConnected()){
                    Logger::log('No database connection. Trying to connect...');
                    $retVal = $this->_connect(array(
                        'host'=>Config::get()->getDBHost(),
                        'user'=>Config::get()->getDBUser(),
                        'pass'=>Config::get()->getDBPassword(),
                        'db-name'=> Config::get()->getDBName()
                    ));
                    Logger::logFuncReturn(__METHOD__);
                    return $retVal;
                }
                else{
                    Logger::log('Already connected.');
                    Logger::logFuncReturn(__METHOD__);
                    return TRUE;
                }
            }
        }
        else if($systemStatus == Util::DB_NEED_CONF && !defined('SETUP_MODE')){
            Logger::log('Unable to connect to the database.', 'error');
            Logger::log('Error Code: '.$dbLink->getErrorCode(), 'error');
            Logger::log('Error Message: '.$dbLink->getErrorMessage(), 'error');
            Logger::requestCompleted();
            header('content-type:application/json');
            http_response_code(500);
            die('{"message":"'.$systemStatus.'","type":"error",'
                    . '"details":"It seems the system is unable to connect to the database.",'
                    . '"db-instance":'.Util::getDatabaseTestInstance()->toJSON().'}');
        }
        else{
            Logger::log('Invalid system status.', 'error');
            Logger::requestCompleted();
            die('{"message":"Invalid system status.","details":"'.$systemStatus.'"}');
        }
    }
    private function _connect($connParams){
        Logger::logFuncCall(__METHOD__);
        $result = $this->getSession()->useDb(array(
            'host'=>$connParams['host'],
            'user'=>$connParams['user'],
            'pass'=>$connParams['pass'],
            'db-name'=>$connParams['db-name']
        ));
        if($result !== TRUE && !defined('SETUP_MODE')){
            Logger::log('Unable to connect to the database.', 'error');
            $dbLink = $this->getDBLink();
            Logger::log('Error Code: '.$dbLink->getErrorCode(), 'error');
            Logger::log('Error Message: '.$dbLink->getErrorMessage(), 'error');
            Logger::requestCompleted();
            header('content-type:application/json');
            die('{"error-code":"'.$this->getDBLink()->getErrorCode().'","details":"'.JsonX::escapeJSONSpecialChars($this->getDBLink()->getErrorMessage()).'"}');
        }
        else if($result !== TRUE && defined('SETUP_MODE')){
            Logger::log('Unable to connect to the database while in setup mode.', 'warning');
            $dbLink = $this->getDBLink();
            Logger::log('Error Code: '.$dbLink->getErrorCode(), 'error');
            Logger::log('Error Message: '.$dbLink->getErrorMessage(), 'error');
            Logger::logFuncReturn(__METHOD__);
            return FALSE;
        }
    }

    /**
     * A session manager.
     * @var SessionManager an instance of 'SessionManager'.
     * @since 1.0 
     * @deprecated since version 1.3
     */
    private $mainSession;
    /**
     * Execute a query.
     * @param MySQLQuery $qObj An object of type 'MySQLQuery'.
     * @return boolean 'TRUE' if no errors occur while executing the query.
     * FAlSE in case of error.
     * @since 1.0
     */
    public function excQ($qObj){
        Logger::logFuncCall(__METHOD__);
        if($qObj instanceof MySQLQuery){
            $this->useDatabase();
            $dbLink = $this->getDBLink();
            if($dbLink != NULL){
                Logger::log('Executing database query...');
                $result = $dbLink->executeQuery($qObj);
                if($result !== TRUE){
                    Logger::log('An error has occured while executing the query.', 'error');
                    Logger::log('Error Code: '.$dbLink->getErrorCode(), 'error');
                    Logger::log('Error Message: '.$dbLink->getErrorMessage(), 'error');
                }
                return $result;
            }
            Logger::log('Database link is null.', 'warning');
        }
        else{
            Logger::log('The given instance is not a sub-class of \'MySQLQuery\'.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
        return FALSE;
    }
    /**
     * Checks if the current session user has a privilege or not given privilege 
     * ID.
     * @param string $pId The ID of the privilege.
     * @return boolean If the user has the given privilege, the function will 
     * return TRUE. If the user does not have the privilege, the function will 
     * return FALSE.
     * @since 1.2
     */
    public function hasPrivilege($pId){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Prevalege ID = \''.$pId.'\'.', 'debug');
        $retVal = FALSE;
        if($this->getUserID() != -1){
            $retVal = $this->getSession()->getUser()->hasPrivilege($pId);
        }
        else{
            Logger::log('Invalid user in session variable.', 'warning');
        }
        Logger::logReturnValue($retVal);
        return $retVal;
    }
    /**
     * Returns the instance of <b>SessionManager</b> that is used by the logic.
     * @return SessionManager An object of type <b>SessionManager</b>
     * @since 1.0
     * @deprecated since version 1.3
     */
    public function getMainSession(){
        return $this->getSession();
    }
    /**
     * 
     * @return SessionManager
     * @since 1.3
     */
    public function getSession() {
        return self::$sessions[$this->sessionName];
    }
    /**
     * Returns language code from the session manager.
     * @param boolean $forceUpdate If set to TRUE, language code will 
     * be forced to update based on the value of the attribute 'lang' 
     * of a GET or POST request or a cookie.
     * @return strint A two characters  that represents language code.
     * @since 1.2
     */
    public final function getSessionLang($forceUpdate=true){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Force Update = \''.$forceUpdate.'\'', 'debug');
        $retVal = $this->getSession()->getLang($forceUpdate);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns the link that is used to connect to the database.
     * @return DatabaseLink The link that is used to connect to the database.
     * @since 1.2
     */
    public function &getDBLink() {
        Logger::logFuncCall(__METHOD__);
        $retVal = &$this->getSession()->getDBLink();
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns the number of rows resulted from executing a query.
     * @return int|NULL Number of rows resulted from executing a query. The 
     * function will return <b>-1</b> in case no connection was established to 
     * the database.
     * @since 1.0
     */
    public function rows(){
        Logger::logFuncCall(__METHOD__);
        $retVal = -1;
        if($this->getSession()->getDBLink() !== NULL){
            $retVal = $this->getSession()->getDBLink()->rows();
        }
        else{
            Logger::log('Database link is NULL.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns A row that is resulted from executing a query.
     * @return array| NULL An array that contains row info. The 
     * function will return NULL in case no connection was established to 
     * the database.
     * @since 1.0
     */
    public function getRow(){
        Logger::logFuncCall(__METHOD__);
        $retVal = NULL;
        if($this->getSession()->getDBLink() != NULL){
            $retVal = $this->getSession()->getDBLink()->getRow();
        }
        else{
            Logger::log('Database link is NULL.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns the ID of the user who is currently logged in.
     * @return int The ID of the user who is currently logged in. The 
     * function will return -1 in case no user is logged in.
     * @since 1.0
     */
    public function getUserID(){
        Logger::logFuncCall(__METHOD__);
        $retVal = -1;
        if($this->getSession()->getUser() != NULL){
            $retVal = intval($this->getSession()->getUser()->getID());
        }
        else{
            Logger::log('The linked user is NULL.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
}
