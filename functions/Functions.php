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
 * @version 1.3.2
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
    public function __construct($linkedSessionName='main') {
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
     * @param array $optionalConnectionParams [Optional] An associative array 
     * which contains database connection information. The indices of the 
     * array are:
     * <ul>
     * <li><b>host</b>: Database host address.</li>
     * <li><b>user</b>: Database username.</li>
     * <li><b>pass</b>: Database user's password.</li>
     * <li><b>db-name</b>: The name of the database.</li>
     * </ul> 
     * If this parameter is not provided, connection information will be taken 
     * from the class 'Config'.
     * @return boolean If the connection is established, the function will 
     * return TRUE. If the constant 'SETUP_MODE' is defined and the function 
     * was unable to establish a connection, it will return FALSE. 
     * If the constant 'SETUP_MODE' is NOT defined and the function 
     * was unable to establish a connection, the execution of PHP code will 
     * stop and the function will send a JSON response to indicate that 
     * a database connection error has occurred.
     * @since 1.1
     */
    public function useDatabase($optionalConnectionParams=array()) {
        Logger::logFuncCall(__METHOD__);
        $systemStatus = Util::checkSystemStatus();
        $dbLink = &$this->getDBLink();
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
                if($dbLink !== NULL && !$dbLink->isConnected()){
                    Logger::log('No database connection. Trying to connect...');
                    $conf = Config::get();
                    $retVal = $this->_connect(array(
                        'host'=>$conf->getDBHost(),
                        'user'=>$conf->getDBUser(),
                        'pass'=>$conf->getDBPassword(),
                        'db-name'=> $conf->getDBName()
                    ));
                    Logger::logFuncReturn(__METHOD__);
                    return $retVal;
                }
                else if($dbLink === NULL){
                    Logger::log('No database connection. Trying to connect...');
                    $conf = Config::get();
                    $retVal = $this->_connect(array(
                        'host'=>$conf->getDBHost(),
                        'user'=>$conf->getDBUser(),
                        'pass'=>$conf->getDBPassword(),
                        'db-name'=> $conf->getDBName()
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
                    . '"error-code":"'.$dbLink->getErrorCode().'",'
                    . '"details":"'.JsonX::escapeJSONSpecialChars($dbLink->getErrorMessage()).'"}');
        }
        else{
            Logger::log('Invalid system status.', 'error');
            Logger::requestCompleted();
            die('{"message":"Invalid system status.","details":"'.$systemStatus.'"}');
        }
    }
    /**
     * Try to connect to database.
     * @param array $connParams [Optional] An associative array 
     * which contains database connection information. The indices of the 
     * array are:
     * <ul>
     * <li><b>host</b>: Database host address.</li>
     * <li><b>user</b>: Database username.</li>
     * <li><b>pass</b>: Database user's password.</li>
     * <li><b>db-name</b>: The name of the database.</li>
     * <ul> 
     * @return boolean If the connection is established, the function will 
     * return TRUE. If the constant 'SETUP_MODE' is defined and the function 
     * was unable to establish a connection, it will return FALSE. 
     * If the constant 'SETUP_MODE' is NOT defined and the function 
     * was unable to establish a connection, the execution of PHP code will 
     * stop and the function will send a JSON response to indicate that 
     * a database connection error has occurred.
     */
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
            http_response_code(500);
            header('content-type:application/json');
            die('{"message":"Unable to connect to the database.",'
                    .'"type":"error",'
                    . '"error-code":"'.$dbLink->getErrorCode().'",'
                    . '"details":"'.JsonX::escapeJSONSpecialChars($dbLink->getErrorMessage()).'",'
                    . '"host":"'.$connParams['host'].'"}');
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
     * Execute a query.
     * @param MySQLQuery $qObj An object of type 'MySQLQuery'. Note that 
     * this function will call the function 'Functions::useDatabase()' by 
     * default.
     * @return boolean 'TRUE' if no errors occur while executing the query.
     * FAlSE in case of error.
     * @since 1.0
     */
    public function excQ($qObj){
        Logger::logFuncCall(__METHOD__);
        if($qObj instanceof MySQLQuery){
            if($this->useDatabase()){
                $dbLink = &$this->getDBLink();
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
     * @deprecated since version 1.3 Use 'Functions::getSession()'
     */
    public function &getMainSession(){
        Logger::logFuncCall(__METHOD__);
        $retVal = &$this->getSession();
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns the instance of 'SessionManager' that is used by the class.
     * @return SessionManager An instance of 'SessionManager'.
     * @since 1.3
     */
    public function &getSession() {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Session name = \''.$this->sessionName.'\'.', 'debug');
        $retVal = &self::$sessions[$this->sessionName];
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
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
        Logger::log('Getting Database link...');
        $dbLink = &$this->getDBLink();
        Logger::log('Checking if database link is not null...');
        if($dbLink !== NULL){
            $retVal = $dbLink->rows();
        }
        else{
            Logger::log('Database link is NULL.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns an array which contains all fetched rows from executing a database 
     * query.
     * @return array An array which contains all fetched rows from executing a 
     * database query.
     * @since 1.3.2
     */
    public function getRows(){
        Logger::logFuncCall(__METHOD__);
        $retVal = array();
        Logger::log('Getting Database link...');
        $dbLink = &$this->getDBLink();
        Logger::log('Checking if database link is not null...');
        if($dbLink !== NULL){
            $retVal = $dbLink->getRows();
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
        Logger::log('Getting Database link...');
        $dbLink = $this->getDBLink();
        Logger::log('Checking if database link is not null...');
        if($dbLink !== NULL){
            $retVal = $dbLink->getRow();
        }
        else{
            Logger::log('Database link is NULL.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns the next row in the result set that was generated from executing 
     * a query.
     * @return array|NULL An associative array that represents the row. If 
     * there was no result set generated from executing the query or the 
     * result has no rows, the function will return NULL.
     * @since 1.3.1
     */
    public function nextRow() {
        Logger::logFuncCall(__METHOD__);
        $retVal = NULL;
        Logger::log('Getting Database link...');
        $dbLink = &$this->getDBLink();
        Logger::log('Checking if database link is not null...');
        if($dbLink !== NULL){
            $retVal = $dbLink->nextRow();
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
        Logger::log('Getting user from session manager...');
        $user = $this->getSession()->getUser();
        Logger::log('Checking if user is null or not...');
        if($user !== NULL){
            $retVal = intval($user->getID());
        }
        else{
            Logger::log('The linked user is NULL.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
}
