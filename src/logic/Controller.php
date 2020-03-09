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
namespace webfiori\logic;
use phMysql\MySQLLink;
use phpStructs\Stack;
use webfiori\entity\SessionManager;
use webfiori\entity\DBConnectionFactory;
use webfiori\entity\DBConnectionInfo;
use phMysql\MySQLQuery;
use webfiori\conf\Config;
/**
 * The base class for creating application logic.
 * This class provides the basic utilities to connect to database and manage 
 * the connection. In addition, it can be used to manage system sessions if 
 * the system uses any. The developer can extend this class to add his own 
 * logic to the application that he is creating.
 * @author Ibrahim
 * @version 1.3.8
 */
class Controller {
    /**
     * A stack that contains all executed and non-executed query objects.
     * @var Stack 
     * @since 1.3.8
     */
    private static $QueryStack;
    /**
     * A stack that contains multiple data sets which was fetched from executing 
     * database queries.
     * @var Stack 
     */
    private $dataStack;
    /**
     * An array that contains current active data set info.
     * @var array 
     */
    private $currentDataset;
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
    private $dbErrDetails;
    /**
     * The default query object which is used to construct SQL queries.
     * @var MySQLQuery
     * @since 1.3.6
     */
    private $defaultQueryObj;
    /**
     * A constant that indicates a user is not authorized to perform specific 
     * actions.
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
     * <li>duration: 10080 (one week) </li>
     * <li>refresh: true </li>
     * <li>name: 'wf-session' </li>
     * <li>create-new: true</li>
     * <li>user: null </li>
     * <li>variables: empty array. </li>
     * </ul>
     * @since 1.3.4
     */
    const DEFAULT_SESSTION_OPTIONS = array(
        'duration'=>10080,
        'refresh'=>true,
        'name'=>'wf-session',
        'create-new'=>true,
        'user'=>null,
        'variables'=>[]
    );
    /**
     * A constant that indicates a given database connection was 
     * not found.
     * @since 1.3.6
     */
    const NO_SUCH_CONNECTION = 'no_such_conn';
    /**
     * A constant that indicates no query object is set.
     * @since 1.3.6
     */
    const NO_QUERY = 'no_query';
    /**
     * A default database to connect to.
     * @var type 
     */
    private $defaultConn;
    /**
     * Sets a default connection information.
     * @param string $connName The name of database connection. It 
     * should be a key name taken from the array of database connections 
     * which is stored in the class 'Config'.
     * @return boolean|string If a connection was found which has the given 
     * name, the method will return true. If If no connection information was found in the class 
     * 'Config' for the given database, the method will return 
     * Functions::NO_SUCH_CONNECTION.
     * @since 1.3.5
     */
    public function setConnection($connName) {
        $connInfo = Config::getDBConnection($connName);
        if($connInfo instanceof DBConnectionInfo){
            $this->defaultConn = $connInfo;
            return true;
        }
        return self::NO_SUCH_CONNECTION;
    }
    /**
     * Returns the linked query object which is used to create MySQL quires.
     * @return MySQLQuery|null If the query object is set, the method will 
     * return an object of type 'MySQLQuery'. If not set, the method will return 
     * null.
     * @since 1.3.6
     */
    public function getQueryObject() {
        return $this->defaultQueryObj;
    }
    /**
     * Sets the linked query object which is used to create MySQL quires.
     * @param MySQLQuery $qObj An instance of the class 'MySQLQuery'.
     * @return boolean If the query object is set, the method will return 
     * true. Other than that, it will return false.
     * @since 1.3.6
     */
    public function setQueryObject($qObj) {
        if($qObj instanceof MySQLQuery){
            $this->defaultQueryObj = $qObj;
            return true;
        }
        return false;
    }
    /**
     * Creates new instance of the class.
     * When a new instance of the class is created, a session with name 'wf-sesstion' 
     * will be linked with it by default.
     * @param string $linkedSessionName The name of the session that will 
     * be linked with the class instance. The name can consist of any character 
     * other than space, comma, semi-colon and equal sign. If the name has one 
     * of the given characters, the session will have new randomly generated name.
     * @since 1.0
     */
    public function __construct() {
        if(self::$sessions === null){
            self::$sessions = [];
            self::$QueryStack = new Stack();
        }
        $this->dataStack = new Stack();
        $this->currentDataset = null;
        $this->_setDBErrDetails(0, 'NO_ERR');
        $this->useSession(self::DEFAULT_SESSTION_OPTIONS);
    }
    /**
     * 
     * @return Stack
     * @since 1.3.8
     */
    private function getDataStack() {
        return $this->dataStack;
    }
    /**
     * 
     * @return array
     * @since 1.3.8
     */
    private function &getCurrentDataset() {
        return $this->currentDataset;
    }
    /**
     * Initiate database connection.
     * This method is used to establish a connection with a database system. 
     * The developer have only to provide the name of the database to the method. 
     * Another option is to set a default database name using the method Functions::setDefaultDB(). 
     * Note that the connection information of the database must exist in the 
     * class 'Config'.
     * @param string $connName The name of database connection. The connection information 
     * will be taken from the class 'Config'.
     * @return boolean|string If the connection is established, the method will 
     * return true. If not, the method will return false. Note that the method will return 
     * false in case the connection was established but the database is not set. 
     * In this case, error code will be 1046 for 'No database selected' or 1049 
     * for 'Unknown database'. If a connection name 
     * is given but its information is not found, the method will return 
     * Functions::NO_SUCH_CONNECTION.
     * @since 1.1
     */
    public function useDatabase($connName=null) {
        $retVal = false;
        $dbLink = $this->getDBLink();
        $dbConn = Config::getDBConnection($connName);
        if($dbLink instanceof MySQLLink){
            if($dbConn instanceof DBConnectionInfo){
                $retVal = $this->_connect($dbConn);
            }
            else{
                $this->_setDBErrDetails(-1, 'No database connection was found which has the name \''.$connName.'\'.');
                $retVal = self::NO_SUCH_CONNECTION;
            }
        }
        else{
            if($dbConn instanceof DBConnectionInfo){
                $retVal = $this->_connect($dbConn);
            }
            else{
                if($this->defaultConn !== null){
                    $retVal = $this->_connect($this->defaultConn);
                }
                else{
                    if($connName !== null){
                        $this->_setDBErrDetails(-1, 'No database connection was found which has the name \''.$connName.'\'.');
                        $retVal = self::NO_SUCH_CONNECTION;
                    }
                    else{
                        $this->_setDBErrDetails(-2, 'No database connection was set.');
                        $retVal = false;
                    }
                }
            }
        }
        return $retVal;
    }
    /**
     * Try to connect to database.
     * @param DBConnectionInfo $connParams An object of type DBConnectionInfo 
     * that contains connection parameters.
     * @return boolean If the connection is established, the method will 
     * return true. If not, It will return false. Note that the method will return 
     * false in case the connection was established but the database is not set. 
     * In this case, error code will be 1046 for 'No database selected' or 1049 
     * for 'Unknown database'.
     */
    private function _connect($connParams){
        $result = DBConnectionFactory::mysqlLink(array(
            'host'=>$connParams->getHost(),
            'user'=>$connParams->getUsername(),
            'pass'=>$connParams->getPassword(),
            'db-name'=>$connParams->getDBName(),
            'port'=>$connParams->getPort()
        ));
        if($result instanceof MySQLLink){
            $this->databaseLink = $result;
            if($result->getErrorCode() == 0){
                return true;
            }
            //might be connected but database is not set.
            $this->_setDBErrDetails($this->getDBLink()->getErrorCode(), $this->getDBLink()->getErrorMessage());
            return false;
        }
        else{
            $this->_setDBErrDetails($result['error-code'], $result['error-message']);
            return false;
        }
    }
    /**
     * Returns an associative array that contains database error info (if any)
     * @return array An associative array. The array has two 
     * indices: 
     * <ul>
     * <li><b>error-code</b>: Error code.</li>
     * <li><b>error-message</b>: A message that tells more information about 
     * the error.</li>
     * If no errors, the first index will have the value 0 and 
     * the second index will have the value 'NO_ERR'. If the error was a result 
     * of executing SQL query, there will be extra index which contains the 
     * query. The index name will be 'query'.
     * @since 1.3.4
     */
    public function getDBErrDetails() {
        return $this->dbErrDetails;
    }
    /**
     * Execute a database query.
     * The method will use the given query object and connection information 
     * if provided. If not given, the method will attempt to use the database 
     * connection which was set by the method Functions::setConnection() and 
     * the query which was set by the method Functions::setQueryObject().
     * @param MySQLQuery|null $qObj An optional object of type 'MySQLQuery'.
     * @param string $connName An optional connection name. The query will be executed 
     * against it if provided.
     * @return boolean 'true' if no errors occur while executing the query.
     * false in case of error. To access database error information, the developer can 
     * use the method Functions::getDBErrDetails().
     * @since 1.0
     */
    public function excQ($qObj=null,$connName=null){
        $retVal = false;
        if(!($qObj instanceof MySQLQuery)){
            $qObj = $this->getQueryObject();
            if($qObj === null){
                $this->_setDBErrDetails(self::NO_QUERY, 'No query object was set to execute.');
                return false;
            }
        }
        if($qObj instanceof MySQLQuery){
            if($connName !== null){
                $connectResult = $this->useDatabase($connName);
                if($connectResult === true){
                    $retVal = $this->_runQuery($qObj);
                }
                else if($connectResult == self::NO_SUCH_CONNECTION){
                    return self::NO_SUCH_CONNECTION;
                }
                else{
                    return false;
                }
            }
            else{
                if($this->getDBLink() !== null){
                    $retVal = $this->_runQuery($qObj);
                }
                else{
                    $retVal = $this->useDatabase();
                    if($retVal === true){
                        $retVal = $this->_runQuery($qObj);
                    }
                }
            }
        }
        return $retVal;
    }
    /**
     * 
     * @param type $errCode
     * @param type $errMessage
     * @since 1.3.6
     */
    private function _setDBErrDetails($errCode,$errMessage) {
        $this->dbErrDetails = array(
            'error-message'=>$errMessage,
            'error-code'=>$errCode
        );
    }
    /**
     * 
     * @param MySQLQuery $query
     * @return type
     */
    private function _runQuery($query) {
        $link = $this->getDBLink();
        self::$QueryStack->push($query);
        $result = $link->executeQuery($query);
        if($result !== true){
            $this->_setDBErrDetails($link->getErrorCode(),$link->getErrorMessage());
            $this->dbErrDetails['query'] = $query->getQuery();
        }
        $qType = $query->getType();
        if($result === true && ($qType == 'select' || $qType == 'show')){
            $current = $this->getCurrentDataset();
            if($current !== null){
                $this->getDataStack()->push($current);
            }
            $this->currentDataset = [
                'rows-count'=>$link->rows(),
                'current-position'=>0,
                'data'=>$link->getRows()
            ];
        }
        return $result;
    }
    /**
     * Returns a stack that contains all executed queries for current request.
     * @return Stack An object of type 'Stack' that contains an objects of 
     * type 'MySQLQuery'.
     * @since 1.3.8
     */
    public static function getQueriesStack() {
        return self::$QueryStack;
    }
    /**
     * Returns the last executed query object.
     * @return MySQLQuery|null The method will return an object of type 
     * 'MySQLQuery' that contains query info. If no query was executed, the 
     * method will return null.
     * @since 1.3.8
     */
    public function getLastQuery() {
        return $this->getQueriesStack()->peek();
    }
    /**
     * Checks if the current session user has a privilege or not given privilege 
     * ID.
     * @param string $pId The ID of the privilege.
     * @return boolean If the user has the given privilege, the method will 
     * return true. If the user does not have the privilege, the method will 
     * return false.
     * @since 1.2
     */
    public function hasPrivilege($pId){
        $retVal = false;
        if($this->getUserID() != -1){
            $retVal = $this->getSession()->getUser()->hasPrivilege($pId);
        }
        return $retVal;
    }
    /**
     * Initiate new session or use a session which is already initialized.
     * Note that sessions cannot be used when running the framework through CLI. 
     * @param array $options An associative array of options. The available options 
     * are: 
     * <ul>
     * <li>name: (Required) The name of the session that will be used or created.</li>
     * <li>create-new: If no session was found which has the given name and 
     * this index is set to true, new session will be created. Ignored if 
     * a session which has the given name is already created.</li>
     * <li>duration: The duration of the session in minutes (optional). Used only if 
     * the session is new.</li>
     * <li>refresh: An optional boolean variable. If set to true, the session timeout time 
     * will be refreshed with every request. Used only if the session is new.</li>
     * <li>user: An optional object of type user that represents session user. Used only if 
     * the session is new.</li>
     * * <li>variables: An optional associative array of variables to set in the session. Used only if 
     * the session is new.</li>
     * </ul>
     * @return boolean If the session is exist or created, the method will 
     * return true. Other than that, the method will return false.
     */
    public function useSession($options=array()){
        if(php_sapi_name() == 'cli'){
            return false;
        }
        else{
            if(gettype($options) == 'array'){
                if(isset($options['name'])){
                    $sessionName = trim($options['name']);
                    if(isset(self::$sessions[$sessionName])){
                        $this->sessionName = $sessionName;
                        return true;
                    }
                    else{
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
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    /**
     * Returns the instance of 'SessionManager' that is used by the class.
     * Before trying to get a session manager, the name of the session must 
     * be supplied to the method Functions::useSession(). Note that it is not possible 
     * to use session in command line interface. If the framework is running through 
     * CLI, This method will always return null.
     * @return SessionManager|null An instance of 'SessionManager'. If no 
     * session is running or the method is called in CLI, the method will return null.
     * @since 1.3
     */
    public function getSession() {
        $retVal = null;
        if($this->sessionName !== null){
            $retVal = self::$sessions[$this->sessionName];
        }
        return $retVal;
    }
    /**
     * Returns session variable given its name.
     * @param string $varName The name of session variable.
     * @return mixed If the session is running and the variable is set, its 
     * value is returned. If no session is running or the variable is not set, 
     * the method will return null.
     * @since 1.3.7
     */
    public function getSessionVar($varName) {
        $session = $this->getSession();
        if($session !== null){
            return $session->getSessionVar($varName);
        }
    }
    /**
     * Adds or updates the value of a session variable.
     * @param string $varName The name of session variable. Must be non-empty 
     * string.
     * @param mixed $varVal The value of session variable.
     * @return boolean If the variable is set, the method will return true. 
     * Other than that, the method will return false.
     * @since 1.3.7
     */
    public function setSessionVar($varName,$varVal) {
        $trimmedName = trim($varName);
        if(strlen($trimmedName) != 0){
            $sesstion = $this->getSession();
            if($sesstion !== null){
                return $sesstion->setSessionVar($trimmedName, $varVal);
            }
        }
        return false;
    }
    /**
     * Returns language code from the currently used session manager.
     * Note that if the name of the session is not set, the method will 
     * return null.
     * @param boolean $forceUpdate If set to true, language code will 
     * be forced to update based on the value of the attribute 'lang' 
     * of a GET or POST request or a cookie.
     * @return string|null A two characters  that represents language code.
     * @since 1.2
     */
    public final function getSessionLang($forceUpdate=true){
        $session = $this->getSession();
        if($session !== null){
            return $session->getLang($forceUpdate);
        }
        return null;
    }
    /**
     * Returns the link that is used to connect to the database.
     * @return MySQLLink|null The link that is used to connect to the database. 
     * If no link is established with the database, the method will return 
     * null.
     * @since 1.2
     */
    private function getDBLink() {
        return $this->databaseLink;
    }
    /**
     * Returns the number of rows resulted from executing a query.
     * The method will return the number of rows for current active dataset.
     * @return int Number of rows resulted from executing a query. The 
     * method will return <b>-1</b> in case no connection was established to 
     * the database or in case no dataset was fetched.
     * @since 1.0
     */
    public function rows(){
        $retVal = -1;
        $dataset = $this->getCurrentDataset();
        if($dataset !== null){
            $retVal = $dataset['rows-count'];
        }
        return $retVal;
    }
    /**
     * Returns an array which contains all fetched rows from executing a database 
     * query.
     * Note that the method can be used only once to get a data set. The 
     * method might return different data set on next call or it might return 
     * an empty array.
     * @return array An array which contains all fetched rows from executing a 
     * database query. The array can have sub associative arrays or it can 
     * have objects if the result of the executed query was mapped to an entity 
     * class.
     * @since 1.3.2
     */
    public function getRows(){
        $retVal = [];
        $dataset = $this->getCurrentDataset();
        if($dataset !== null){
            $this->currentDataset = $this->dataStack->pop();
            $retVal = $dataset['data'];
        }
        return $retVal;
    }
   /**
    * Returns the first row that is resulted from executing a query.
    * Note that if the number of fetched rows is 1, the method can be only 
    * used once to get that row. In addition, If there are other data sets which was resulted from 
    * executing other select queries, The method will switch to the last 
    * data set.
    * @return array|null|object An associative array that contains row info. The 
    * method will return null in case no connection was established to 
    * the database or there is no active data set. Also, if the result of the 
    * select query was mapped to an entity class, the method will return an 
    * instance of the mapped class.
    * @since 1.0
    */
    public function getRow(){
        $retVal = null;
        $current = &$this->getCurrentDataset();
        if($current !== null){
            if($current['rows-count'] != 0){
                if(isset($current['data'][$current['current-position']])){
                    $retVal = $current['data'][$current['current-position']];
                }
            }
            if($current['rows-count'] == 1){
                $this->currentDataset = $this->dataStack->pop();
            }
        }
        return $retVal;
    }
    /**
     * Returns the next row in the result set that was generated from executing 
     * a query.
     * Note that if current active data set has been traversed, the method 
     * will return null. In addition, If there are other data sets which was resulted from 
     * executing other select queries, The method will switch to the last 
     * data set.
     * @return array|null|object An associative array that represents the row. If 
     * there was no result set generated from executing the query or the 
     * result has no rows or the current data set has been traversed, the 
     * method will return null. Also, if the result of the 
     * select query was mapped to an entity class, the method will return an 
     * instance of the mapped class.
     * @since 1.3.1
     */
    public function nextRow() {
        $retVal = null;
        $dataset = &$this->getCurrentDataset();
        if($dataset !== null){
            if(isset($dataset['data'][$dataset['current-position']])){
                $retVal = $dataset['data'][$dataset['current-position']];
                $dataset['current-position'] = $dataset['current-position'] + 1;
            }
            else{
                $this->currentDataset = $this->dataStack->pop();
            }
        }
        return $retVal;
    }
    /**
     * Returns the ID of the user from session manager.
     * @return string|int The ID of the user taken from session manager. The 
     * method will return -1 in case no user is set in session manager or in 
     * case no session is active.
     * @since 1.0
     */
    public function getUserID(){
        $retVal = -1;
        $sesstion = $this->getSession();
        if($sesstion !== null){
            $user = $this->getSession()->getUser();
            if($user !== null){
                $retVal = $user->getID().'';
            }
        }
        return $retVal;
    }
}
