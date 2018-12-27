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
namespace webfiori\functions;
use phMysql\MySQLLink;
use webfiori\entity\SessionManager;
use webfiori\entity\Logger;
use webfiori\entity\DBConnectionFactory;
use phMysql\MySQLQuery;
use webfiori\Config;
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
 * The base class for creating application logic.
 * This class provides the basic utilities to connect to database and manage 
 * the connection. In addition, it can be used to manage system sessions if 
 * the system uses any. The developer can extend this class to add his own 
 * logic to the application that he is creating.
 * @author Ibrahim
 * @version 1.3.4
 */
class Functions {
    /**
     *
     * @var MySQLLink
     * @since 1.3.3 
     */
    private $databaseLink;
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
     * An array that will hold database connection error info.
     * @var type 
     * @since 1.3.4
     */
    private $connErrDetails;
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
     * An associative array that contains variables which are used in case no 
     * session options where provided while initiating a new session.
     * The array have the following values:
     * <ul>
     * <li>duration: 120 </li>
     * <li>refresh: TRUE </li>
     * <li>name: '' </li>
     * <li>user: NULL </li>
     * <li>variables: empty array. </li>
     * </ul>
     * @since 1.3.4
     */
    const DEFAULT_SESSTION_OPTIONS = array(
        'duration'=>120,
        'refresh'=>true,
        'name'=>'',
        'user'=>null,
        'variables'=>array()
    );
    /**
     * Creates new instance of the class.
     * @param string $linkedSessionName The name of the session that will 
     * be linked with the class instance. The name can consist of any character 
     * other than space, comma, semi-colon and equal sign. If the name has one 
     * of the given characters, the session will have new randomly generated name.
     * @since 1.0
     */
    public function __construct() {
        Logger::logFuncCall(__METHOD__);
        if(self::$sessions === NULL){
            Logger::log('Initializing sessions array...');
            self::$sessions = array();
        }
        $this->connErrDetails = array();
        $this->connErrDetails['error-code'] = '';
        $this->connErrDetails['error-message'] = '';
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Initiate database connection.
     * @param array $optionalConnectionParams [Optional] An associative array 
     * which contains database connection information. The indices of the 
     * array are:
     * <ul>
     * <li><b>host</b>: Database host address.</li>
     * <li><b>port</b>: Port number.</li>
     * <li><b>user</b>: Database username.</li>
     * <li><b>pass</b>: Database user's password.</li>
     * <li><b>db-name</b>: The name of the database.</li>
     * </ul> 
     * If this parameter is not provided, connection information will be taken 
     * from the class 'Config'.
     * @return boolean If the connection is established, the function will 
     * return TRUE. If not, the function will return FALSE.
     * @since 1.1
     */
    public function useDatabase($optionalConnectionParams=array()) {
        Logger::logFuncCall(__METHOD__);
        $dbLink = &$this->getDBLink();
        if($dbLink instanceof MySQLLink){
            Logger::log('Already connected to database.');
            Logger::log('Checking if optional database parameters are provided or not...');
            if($optionalConnectionParams != NULL && isset($optionalConnectionParams['host'])
                    && isset($optionalConnectionParams['user']) &&
                    isset($optionalConnectionParams['pass']) && 
                    isset($optionalConnectionParams['db-name']) && 
                    isset($optionalConnectionParams['port'])){
                Logger::log('They are provided.');
                $retVal = FALSE;
                $result = $this->_connect($optionalConnectionParams);
                if($result === TRUE){
                    Logger::log('Connected to database using given parameters.');
                    $retVal = TRUE;
                }
                else{
                    Logger::log('Unable to connect to the database.', 'warning');
                }
                Logger::logReturnValue($retVal);
                Logger::logFuncReturn(__METHOD__);
                return $retVal;
            }
            else{
                Logger::log('Some or all database parameters are missing.');
                Logger::logFuncReturn(__METHOD__);
                return TRUE;
            }
        }
        else{
            Logger::log('Checking if optional database parameters are provided or not...');
            if($optionalConnectionParams != NULL && isset($optionalConnectionParams['host'])
                    && isset($optionalConnectionParams['user']) &&
                    isset($optionalConnectionParams['pass']) && 
                    isset($optionalConnectionParams['db-name'])&& 
                    isset($optionalConnectionParams['port'])){
                Logger::log('They are provided.');
                $retVal = $this->_connect($optionalConnectionParams);
                if($retVal === TRUE){
                    Logger::log('Connected to database using given parameters.');
                }
                else{
                    Logger::log('Unable to connect to the database.', 'warning');
                }
                Logger::logFuncReturn(__METHOD__);
                return $retVal;
            }
            else{
                Logger::log('No database connection parameters. Trying to connect using the parameters from Config.php...');
                $conf = Config::get();
                $retVal = $this->_connect(array(
                    'host'=>$conf->getDBHost(),
                    'user'=>$conf->getDBUser(),
                    'pass'=>$conf->getDBPassword(),
                    'db-name'=> $conf->getDBName(),
                    'port'=>$conf->getDBPort()
                ));
                if($retVal === TRUE){
                    Logger::log('Connection to the database was established.');
                }
                else{
                    Logger::log('Unable to connect to the database.', 'warning');
                }
                Logger::logFuncReturn(__METHOD__);
                return $retVal;
            }
        }
    }
    /**
     * Try to connect to database.
     * @param array $connParams [Optional] An associative array 
     * which contains database connection information. The indices of the 
     * array are:
     * <ul>
     * <li><b>host</b>: Database host address.</li>
     * <li><b>port</b>: Port number.</li>
     * <li><b>user</b>: Database username.</li>
     * <li><b>pass</b>: Database user's password.</li>
     * <li><b>db-name</b>: The name of the database.</li>
     * <ul> 
     * @return boolean If the connection is established, the function will 
     * return TRUE. If not, It will return FALSE.
     */
    private function _connect($connParams){
        Logger::logFuncCall(__METHOD__);
        $result = DBConnectionFactory::mysqlLink(array(
            'host'=>$connParams['host'],
            'user'=>$connParams['user'],
            'pass'=>$connParams['pass'],
            'db-name'=>$connParams['db-name'],
            'port'=>$connParams['port']
        ));
        if($result instanceof MySQLLink){
            Logger::log('Connected to database.');
            $this->databaseLink = $result;
            Logger::logFuncReturn(__METHOD__);
            return TRUE;
        }
        else{
            $this->connErrDetails = $result;
            Logger::log('Unable to connect to the database while in setup mode.', 'warning');
            Logger::log('Error Code: '.$result['error-code'], 'error');
            Logger::log('Error Message: '.$result['error-message'], 'error');
            Logger::logFuncReturn(__METHOD__);
            return FALSE;
        }
    }
    /**
     * Returns an associative array that contains database error info (if any)
     * @return array An associative array. The array has two 
     * indices: 
     * <ul>
     * <li><b>error-code</b>: Error code.</li>
     * <li><b>error-code</b>: A message that tells more information about 
     * the error.</li>
     * If no errors, the indices will have empty strings.
     * @since 1.3.4
     */
    public function getDBErrDetails() {
        $dbLink = $this->getDBLink();
        if($dbLink !== NULL){
            $this->connErrDetails['error-code'] = $dbLink->getErrorCode();
            $this->connErrDetails['error-message'] = $dbLink->getErrorMessage();
        }
        return $this->connErrDetails;
    }
    /**
     * Execute a database query.
     * @param MySQLQuery $qObj An object of type 'MySQLQuery'. Note that 
     * this function will call the function 'Functions::useDatabase()' by 
     * default.
     * @return boolean 'TRUE' if no errors occur while executing the query.
     * FAlSE in case of error.
     * @since 1.0
     */
    public function excQ($qObj){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        if($qObj instanceof MySQLQuery){
            if($this->useDatabase()){
                $dbLink = &$this->getDBLink();
                Logger::log('Query = \''.$qObj->getQuery().'\'.', 'debug');
                Logger::log('Executing database query...');
                $result = $dbLink->executeQuery($qObj);
                if($result !== TRUE){
                    Logger::log('An error has occured while executing the query.', 'error');
                    Logger::log('Error Code: '.$dbLink->getErrorCode(), 'error');
                    Logger::log('Error Message: '.$dbLink->getErrorMessage(), 'error');
                }
                $retVal = $result;
            }
            else{
                Logger::log('Unable to use database connection.', 'warning');
            }
        }
        else{
            Logger::log('The given instance is not a sub-class of \'MySQLQuery\'.', 'warning');
        }
        if($retVal === TRUE){
            Logger::log('Query execited.');
        }
        else{
            Logger::log('Query did not execute.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
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
     * Initiate new session or use a session which is already initiated.
     * @param array $options An associative array of options. The available options 
     * are: 
     * <ul>
     * <li>name: The name of the session that will be used or created.</li>
     * <li>create-new: If no session was found which has the given name and 
     * this index is set to TRUE, new session will be created.</li>
     * <li>duration: The duration of the session in minutes (optional). Used only if 
     * the session is new.</li>
     * <li>refresh: An optional boolean variable. If set to TRUE, the session timeout time 
     * will be refreshed with every request. Used only if the session is new.</li>
     * <li>user: An optional object of type user that represents session user. Used only if 
     * the session is new.</li>
     * * <li>variables: An optional associative array of variables to set in the session. Used only if 
     * the session is new.</li>
     * </ul>
     * @return boolean If the session is exist or created, the function will 
     * return TRUE. Other than that, the function will return FALSE.
     */
    public function useSession($options=array()){
        if(gettype($options) == 'array'){
            if(isset($options['name'])){
                $sessionName = $options['name'];
                if(isset(self::$sessions[$sessionName])){
                    $this->sessionName = $sessionName;
                    return TRUE;
                }
                else{
                    if(isset($options['create-new']) && $options['create-new'] === TRUE){
                        $mngr = new SessionManager($sessionName);
                        
                        $sTime = isset($options['duration']) ? $options['duration'] : self::DEFAULT_SESSTION_OPTIONS['duration'];
                        $mngr->setLifetime($sTime);
                        
                        $isRef = isset($options['refresh']) ? $options['refresh'] : self::DEFAULT_SESSTION_OPTIONS['refresh'];
                        $mngr->setIsRefresh($isRef);
                        
                        if($mngr->initSession($isRef)){
                            $this->sessionName = $sessionName;
                            $sUser = isset($options['user']) ? $options['user'] : self::DEFAULT_SESSTION_OPTIONS['user'];
                            $mngr->setUser($sUser);
                            self::$sessions[$mngr->getName()] = $mngr;
                            if(isset($options['variables'])){
                                foreach ($options['variables'] as $k => $v){
                                    $mngr->setSessionVar($k,$v);
                                }
                            }
                            return TRUE;
                        }
                    }
                }
            }
        }
        return FALSE;
    }
    /**
     * Returns the instance of 'SessionManager' that is used by the class.
     * If the name of the session is NULL, the function will return NULL.
     * @return SessionManager|NULL An instance of 'SessionManager'.
     * @since 1.3
     */
    public function &getSession() {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Session name = \''.$this->sessionName.'\'.', 'debug');
        $retVal = NULL;
        if($this->sessionName !== NULL){
            $retVal = &self::$sessions[$this->sessionName];
        }
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns language code from the currently used session manager.
     * Note that if the name of the session is not set, the function will 
     * return NULL.
     * @param boolean $forceUpdate If set to TRUE, language code will 
     * be forced to update based on the value of the attribute 'lang' 
     * of a GET or POST request or a cookie.
     * @return string|NULL A two characters  that represents language code.
     * @since 1.2
     */
    public final function getSessionLang($forceUpdate=true){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Force Update = \''.$forceUpdate.'\'', 'debug');
        $session = $this->getSession();
        if($session !== NULL){
            return $session->getLang($forceUpdate);
        }
        Logger::logFuncReturn(__METHOD__);
        return NULL;
    }
    /**
     * Returns the link that is used to connect to the database.
     * @return MySQLLink|NULL The link that is used to connect to the database. 
     * If no link is established with the database, the function will return 
     * NULL.
     * @since 1.2
     */
    public function &getDBLink() {
        Logger::logFuncCall(__METHOD__);
        Logger::logFuncReturn(__METHOD__);
        return $this->databaseLink;
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
     * Returns the first that is resulted from executing a query.
     * @return array|NULL An array that contains row info. The 
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
     * Returns the ID of the user from session manager.
     * @return int The ID of the user taken from session manager. The 
     * function will return -1 in case no user is set in session manager or in 
     * case no session is active.
     * @since 1.0
     */
    public function getUserID(){
        Logger::logFuncCall(__METHOD__);
        $retVal = -1;
        Logger::log('Getting user from session manager...');
        $sesstion = $this->getSession();
        if($sesstion !== NULL){
            $user = &$this->getSession()->getUser();
            Logger::log('Checking if session user is null or not...');
            if($user !== NULL){
                $retVal = intval($user->getID());
            }
            else{
                Logger::log('Session user is NULL.', 'warning');
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
}
