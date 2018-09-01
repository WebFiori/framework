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
 * A helper class to manage system sessions.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.7
 */
class SessionManager implements JsonI{
    /**
     * A variable is set to <b>TRUE</b> if the session is resumed or its new.
     * @var boolean
     * @since 1.5 
     */
    private $resumed;
    /**
     * A string that stores session status.
     * @var string
     * @since 1.5
     */
    private $sessionStatus;
    /**
     * The name of the session.
     * @var string
     * @since 1.5 
     */
    private $sessionName;
    /**
     * The lifetime of the session (in minutes).
     * @var int lifetime of the session (in minutes). The default is 10.
     * @since 1.4 
     */
    private $lifeTime;
    /**
     * A constant that indicates the name of database host is missing.
     * @var string Constant that indicates the name of database host is missing.
     * @since 1.3
     * @see @see SessionManager::useDb($dbAttrs=array())
     */
    const MISSING_DB_HOST = 'missing_db_host';
    /**
     * A constant that indicates the name of the database is missing.
     * @var string Constant that indicates the name of the database is missing.
     * @since 1.3
     */
    const MISSING_DB_NAME = 'missing_db_name';
    /**
     * A constant that indicates username of the database is missing.
     * @var string Constant that indicates username of the database is missing.
     * @since 1.3
     * @see 
     */
    const MISSING_DB_USER = 'missing_db_user';
    /**
     * A constant that indicates the user password of the database is missing.
     * @var string Constant that indicates the user password of the database is missing.
     * @since 1.3
     * @see 
     */
    const MISSING_DB_PASS = 'missing_db_password';
    /**
     * A constant that indicates a database connection error has occur.
     * @var string Constant that indicates a database connection error has occur.
     * @since 1.3
     * @see 
     */
    const DB_CONNECTION_ERR = 'unable_to_connect_to_db';
    /**
     * An array of supported languages.
     * @var array An array of supported languages.
     * @since 1.2
     */
    const SUPPORTED_LANGS = array(
        'EN','AR'
    );
    /**
     * A constant that indicates the session is not started yet.
     * @since 1.7
     */
    const NOT_RUNNING = 'status_not_running';
    /**
     * A constant that indicates the session has timed out.
     * @since 1.7
     */
    const EXPIRED = 'status_session_timeout';
    /**
     * A constant that indicates the session has just started.
     * @since 1.7
     */
    const NEW_SESSION = 'status_new_session';
    /**
     * A constant that indicates the session is resumed.
     * @since 1.7
     */
    const RESUMED = 'status_session_resumed';
    /**
     * A constant that indicates the session has invalid state (Usually one 
     * missing session variable).
     * @since 1.7
     */
    const INV_STATE = 'status_invalid_state';
    /**
     * A constant that indicates the session has invalid cookie.
     * @since 1.7
     */
    const INV_COOKIE = 'status_inv_cookie';
    /**
     * A constant that indicates the IP address of the request does not match 
     * the one stored in the session.
     * @since 1.7
     */
    const INV_IP_ADDRESS = 'status_inv_ip_address';
    /**
     * A constant that indicates the session has been killed by calling the 
     * function 'SessionManager::kill()'.
     * @since 1.7
     */
    const KILLED = 'status_session_killed';
    /**
     * Returns a JSON string that represents the session.
     * @return string
     * @since 1.0
     */
    public function __toString() {
        return $this->toJSON().'';
    }
    /**
     * Creates new session manager.
     * @param string $session_name [Optional] The name of the session. It 
     * must be non-empty string to be set. The default value is 'pa-session'.
     * @since 1.0
     */
    public function __construct($session_name='pa-seesion') {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Validating session name...');
        if(strlen($session_name) != 0){
            Logger::log('Valid session name.');
            $this->sessionName = $session_name;
        }
        else{
            Logger::log('Invalid session name. Using default.','warning');
            $this->sessionName = 'pa-session';
        }
        Logger::log('Session name is set to \''.$this->sessionName.'\'.', 'debug');
        //initial life time: 120 minutes.
        $this->setLifetime(120);
        $this->sessionStatus = self::NOT_RUNNING;
        $this->resumed = FALSE;
        if(session_status() == PHP_SESSION_ACTIVE){
            Logger::log('A session is active. Writing session variables and creating new one.', 'warning');
            Logger::log('Active session name = \''. session_name().'\'.', 'debug');
            Logger::log('Active session ID = \''. session_id().'\'.', 'debug');
            //if the session is active, we might need to switch
            //to new session
            session_write_close();
            session_id($this->generateSessionID());
        }
        Logger::logFuncReturn(__METHOD__);
        //session_save_path(ROOT_DIR.'/tmp');
    }
    /**
     * Generate a random session ID.
     * @return string A new random session ID.
     * @since 1.6
     */
    private function generateSessionID() {
        Logger::logFuncCall(__METHOD__);
        $date = date(DATE_ISO8601);
        $hash = hash('sha256', $date);
        $time = time() + rand(0, 1000);
        $hash2 = hash('sha256',$hash.$time);
        $id = substr($hash2, 0, 27);
        Logger::logReturnValue($id);
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Sets the lifetime of the session.
     * @param int $time Session lifetime (in minutes). it will be set only if 
     * the given value is greater than 0.
     * @return boolean TRUE if time is updated. FALSE otherwise.
     * @since 1.4
     */
    public function setLifetime($time){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        Logger::log('Validating given time...');
        Logger::log('Given time = \''.$time.'\' ('. gettype($time).').', 'debug');
        if($time > 0){
            Logger::log('Checking if session is active or not...');
            if(session_status() == PHP_SESSION_ACTIVE){
                Logger::log('It is active. Checking if the session has timed out...');
                if(!$this->isTimeout()){
                    Logger::log('Session duration updated.');
                    $this->lifeTime = $time;
                    $_SESSION['lifetime'] = $time;
                    $params = session_get_cookie_params();
                    setcookie($this->getName(), $this->getID(),time()+$this->getLifetime() * 60, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
                    $retVal = TRUE;
                }
                else{
                    Logger::log('Session has timed out.', 'warning');
                }
            }
            else{
                Logger::log('Session duration updated.');
                $this->lifeTime = $time;
                $retVal = TRUE;
            }
        }
        else{
            Logger::log('Invalid time.','warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns the lifetime of the session (in minutes). 
     * @return int the lifetime of the session (in minutes).
     * @since 1.4
     */
    public function getLifetime(){
        Logger::logFuncCall(__METHOD__);
        $retVal = $this->lifeTime;
        if(session_status() == PHP_SESSION_ACTIVE){
            if(isset($_SESSION['lifetime'])){
                Logger::log('Time taken from $_SESSION[\'lifetime\']');
                $retVal = $_SESSION['lifetime'];
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $this->lifeTime;
    }
    /**
     * Sets if the session timeout will be refreshed with every request 
     * or not.
     * @param boolean $bool If set to TRUE, timeout time will be refreshed. 
     * Note that the property will be updated only if the session is running.
     * @since 1.5
     */
    public function setIsRefresh($bool){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Passed value = \''.$bool.'\'.', 'debug');
        if(session_status() == PHP_SESSION_ACTIVE){
            $_SESSION['refresh'] = $bool === TRUE ? TRUE : FALSE;
            Logger::log('New property value = \''.$_SESSION['refresh'].'\'.', 'debug');
            Logger::log('Property updated.');
        }
        else{
            Logger::log('Session is not running. Property not updated.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Initialize session language. The initialization depends on the attribute 
     * 'lang'. It can be send via 'get' request, 'post' request or a cookie. If 
     * no language code is provided, 'EN' will be used. The provided language 
     * must be in the array 'SessionManager::SUPOORTED_LANGS'. If the given 
     * language code is not in the given array, The used value will depend on the existence 
     * of the class 'SiteConfig'. If it is exist, The value that is returned by 
     * 'SiteConfig::getPrimaryLanguage()' will be used. If the class does not 
     * exist, 'EN' will be used. Also if the language is set before, it will 
     * not be updated unless the parameter '$forceUpdate' is set to TRUE.
     * @since 1.2
     * @param boolean $forceUpdate Set to TRUE if the language is set and want to 
     * reset it.
     * @return boolean The function will return TRUE if the language is set or 
     * updated. Other than that, the function will return FALSE.
     */
    private function initLang($forceUpdate=false,$useDefault=false){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Force update = \''.$forceUpdate.'\'.', 'debug');
        Logger::log('Use default = \''.$useDefault.'\'.', 'debug');
        if(isset($_SESSION['lang']) && !$forceUpdate){
            Logger::log('Language did not updated.', 'warning');
            return FALSE;
        }
        //the value of default language.
        //used in case no language found 
        //in $_GET['lang']
        $defaultLang = class_exists('SiteConfig') ? SiteConfig::get()->getPrimaryLanguage() : 'EN';
        Logger::log('Default language = \''.$defaultLang.'\'.', 'debug');
        $lang = NULL;
        Logger::log('Trying to get language variable from the array $_GET[].');
        if(isset($_GET['lang'])){
            $lang = filter_var($_GET['lang'],FILTER_SANITIZE_STRING);
            Logger::log('$_GET[\'lang\'] = \''.$lang.'\'.', 'debug');
        }
        else{
            Logger::log('The variable $_GET[\'lang\'] is not set.', 'warning');
        }
        Logger::log('Validating value...');
        if($lang == FALSE || $lang == NULL){
            Logger::log('It is NULL or FALSE.', 'warning');
            Logger::log('Trying to get language variable from the array $_POST[].');
            if(isset($_POST['lang'])){
                $lang = filter_var($_POST['lang'],FILTER_SANITIZE_STRING);
                Logger::log('$_POST[\'lang\'] = \''.$lang.'\'.', 'debug');
            }
            else{
                Logger::log('The variable $_POST[\'lang\'] is not set.', 'warning');
            }
            if($lang == FALSE || $lang == NULL){
                Logger::log('It is NULL or FALSE.', 'warning');
                Logger::log('Trying to get language variable from the cookie \'lang\'.');
                $lang = filter_input(INPUT_COOKIE, 'lang');
                Logger::log('Language from cookie = \''.$lang.'\'.', 'debug');
                if($lang == FALSE || $lang == NULL){
                    Logger::log('The cookie \'lang\' is not set.', 'warning');
                    $lang = NULL;
                }
            }
        }
        $retVal = FALSE;
        if(isset($_SESSION['lang']) && $lang == NULL){
            Logger::log('Language did not updated.', 'warning');
            $retVal = FALSE;
        }
        else if($lang == NULL && $useDefault === TRUE){
            Logger::log('The default language will be used.', 'warning');
            $lang = $defaultLang;
        }
        else if($lang == NULL && $useDefault !== TRUE){
            Logger::log('Language did not updated.', 'warning');
            $retVal = FALSE;
        }
        $langU = strtoupper($lang);
        if(in_array($langU, self::SUPPORTED_LANGS)){
            $_SESSION['lang'] = $langU;
            $retVal = TRUE;
        }
        else{
            Logger::log('The given language is not in the array SessionManager::SUPPORTED_LANGS', 'warning');
        }
        if($useDefault === TRUE && $retVal == FALSE){
            $_SESSION['lang'] = $defaultLang;
            $retVal = TRUE;
        }
        else{
            $retVal = FALSE;
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncCall(__METHOD__);
    }
    /**
     * Returns session language code.
     * @return string|NULL two digit language code (such as 'EN'). If the session 
     * is not running or the language is not set, the function will return <b>NULL</b>.
     * @param boolean $forceUpdate Set to TRUE if the language is set and want to 
     * reset it. The reset process depends on the attribute 
     * 'lang'. It can be send via 'get' request, 'post' request or a cookie. If 
     * no language code is provided and the parameter '$forceUpdate' is set 
     * to TRUE, 'EN' will be used. The provided language 
     * must be in the array 'SessionManager::SUPOORTED_LANGS'. If the given 
     * language code is not in the given array and the parameter '$forceUpdate' is set 
     * to TRUE, 'EN' will be used.
     */
    public function getLang($forceUpdate=false){
        Logger::logFuncCall(__METHOD__);
        $retVal = NULL;
        if($this->isResumed()){
            if($forceUpdate === TRUE){
                $this->initLang($forceUpdate);
            }
            if(isset($_SESSION['lang'])){
                $retVal = $_SESSION['lang'];
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Checks the status of login user token. The function check the match between 
     * the token that is sent with the request and the token in the session. 
     * The token can be send via 'get' request, 'post' request or a cookie. The 
     * token must be stored in the parameter 'token'
     * @return boolean TRUE if the user token is valid. FALSE if 
     * not. Also the function will return FALSE if no user is logged in.
     * @since 1.1
     * @deprecated since version 1.7
     */
    public function validateToken(){
        $tok = filter_input(INPUT_COOKIE, 'token');
        if($tok === FALSE || $tok === NULL){
            if(isset($_GET['token'])){
                $tok = filter_var($_GET['token'],FILTER_SANITIZE_STRING);
            }
            if($tok === FALSE || $tok === NULL){
                if(isset($_POST['token'])){
                    $tok = filter_var($_POST['token'],FILTER_SANITIZE_STRING);
                }
                if($tok === FALSE || $tok === NULL){
                    return FALSE;
                }
            }
        }
        $user = $this->getUser();
        if($user != NULL){
            return $user->getToken() == $tok;
        }
        return FALSE;
    }
    /**
     * Sets the user who is using the system. It is used in case of log in.
     * @param User $user an object of type User. Once the user is set, 
     * a cookie with the name 'token' will be created. This cookie will contain 
     * user access token.
     * @return boolean TRUE in case the user is set. FALSE if not.
     * @since 1.0
     */
    public function setUser($user){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        Logger::log('Checking if session is resumed...');
        if($this->isResumed()){
            Logger::log('Checking if passed variable is an instance of \'User\'.');
            if($user instanceof User){
                Logger::log('User updated.');
                $_SESSION['user'] = $user;
                setcookie('token', $user->getToken(), time()+$this->getLifetime()*60, "/");
                $retVal = TRUE;
            }
            else{
                Logger::log('Passed value is not an instance of \'User\'.', 'warning');
            }
        }
        else{
            Logger::log('Session is not running or not resumed.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns the user who is logged in.
     * @return User|NULL an object of type User. If the session is not started 
     * or no used is logged in, the function will return NULL.
     * @since 1.0
     */
    public function getUser(){
        Logger::logFuncCall(__METHOD__);
        $retVal = NULL;
        if($this->isResumed()){
            if(isset($_SESSION['user'])){
                $retVal = $_SESSION['user'];
            }
            else{
                Logger::log('Variable $_SESSION[\'user\'] is not set.', 'warning');
            }
        }
        else{
            Logger::log('Session is not running or not resumed.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Use database connection in the session.
     * @param array $dbAttrs An associative array that contains database connection 
     * parameters. The indices are: <b>'host'</b> (the value should be host name), 
     * <b>'user'</b> (the value should database username), 
     * <b>'pass'</b> (the value should be database username password)
     * and <b>'db-name'</b> (the value should be database instance name). 
     * @return boolean If the connection is established, the function will return <b>TRUE</b>. 
     * In case the host name is missing, the function will return 
     * <b>SessionManager::MISSING_DB_HOST</b>.
     * In case the username is missing, the function will return 
     * <b>SessionManager::MISSING_DB_USER</b>.
     * In case the password is missing, the function will return 
     * <b>SessionManager::MISSING_DB_PASS</b>.
     * In case the database name is missing, the function will return 
     * <b>SessionManager::MISSING_DB_NAME</b>. 
     * In case of connection error, the function will return <b>SessionManger::DB_CONNECTION_ERR</b>. 
     * To get more information about the connection error, you can get <b>DatabaseLink<b> 
     * object using the function <b>SessionManager->getDBLink()</b>. 
     * @since 1.3
     */
    public function useDb($dbAttrs=array()){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        if($this->isResumed()){
            if(isset($dbAttrs['host'])){
                if(isset($dbAttrs['user'])){
                    if(isset($dbAttrs['pass'])){
                        if(isset($dbAttrs['db-name'])){
                            $_SESSION['db'] = new DatabaseLink($dbAttrs['host'],$dbAttrs['user'],$dbAttrs['pass']);
                            if($_SESSION['db']->isConnected()){
                                if($_SESSION['db']->setDB($dbAttrs['db-name'])){
                                    $retVal = TRUE;
                                }
                            }
                            else{
                                $retVal = self::DB_CONNECTION_ERR;
                            }
                        }
                        else{
                            $retVal = self::MISSING_DB_NAME;
                        }
                    }
                    else{
                        $retVal = self::MISSING_DB_PASS;
                    }
                }
                else{
                    $retVal = self::MISSING_DB_USER;
                }
            }
            else{
                $retVal = self::MISSING_DB_HOST;
            }
        }
        Logger::log('Return value = '.$retVal, 'debug');
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Initialize the session.
     * @see SessionManager::useDb($dbAttrs=array())
     * @since 1.0
     * @param boolean $refresh [optional] If set to true, The due time of the session will 
     * be refreshed if the session is not timed out. Default is FALSE. 
     * @param boolean $useDefaultLang [Optional] If the session is new and 
     * there was no language parameter was found in the request and this parameter 
     * is set to TRUE, default language will be used (EN). 
     * @param boolean $useDb [optional] If set to <b>TRUE</b>, an attempt to connect 
     * to a database will be done. 
     * @param array $dbAttributes Database connection info.
     * @return boolean|string TRUE if the initialization was successful. FALSE 
     * in case of error. Also it is possible that the function will return one 
     * of the database error messages.
     */
    public function initSession($refresh=false,$useDefaultLang=false,$useDb=false,$dbAttributes=array()){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        Logger::log('Checking if session is resumed...');
        if(!$this->isResumed()){
            Logger::log('Trying to resume or start the session...');
            if($this->resume()){
                Logger::log('Session resumed.');
                $retVal = TRUE;
            }
            else{
                Logger::log('Starting new session...');
                $lifeTime = $this->getLifetime() * 60;
                $retVal = $this->start($refresh,$useDb, $lifeTime, $dbAttributes,$useDefaultLang);
            }
        }
        else{
            Logger::log('Session already running.');
            $this->setIsRefresh($refresh);
            $this->initLang(FALSE, $useDefaultLang);
            $retVal = TRUE;
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if session timeout time will be refreshed with every request or not. 
     * This function must be called only after calling the function 'SessionManager::initSession()'. 
     * or it will throw an exception.
     * @return boolean TRUE If session timeout time will be refreshed with every request. 
     * FALSE if not.
     * @throws Exception If the session is not running. 
     * @since 1.5
     */
    public function isRefresh(){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Checking if session is active...');
        if(session_status() == PHP_SESSION_ACTIVE){
            if(isset($_SESSION['refresh'])){
                Logger::logReturnValue($_SESSION['refresh']);
                Logger::logFuncReturn(__METHOD__);
                return $_SESSION['refresh'];
            }
            else{
                Logger::log('Variable $_SESSION[\'refresh\'] is not set. An exception is thrown.', 'error');
            }
        }
        else{
            Logger::log('Calling the function while session is not active. An exception is thrown.', 'error');
        }
        throw new Exception('Session is not running');
    }
    private function start($refresh,$useDb,$lifeTime,$dbAttributes,$useDefaultLang=false){
        Logger::logFuncCall(__METHOD__);
        $this->sessionStatus = self::NEW_SESSION;
        Logger::log('Setting session related ini directives and session variables...');
        ini_set('session.gc_maxlifetime', $this->getLifetime());
        ini_set('session.cookie_lifetime', $this->getLifetime());
        ini_set('session.use_cookies', 1);
        session_name($this->getName());
        session_set_cookie_params($lifeTime,"/");
        Logger::log('Strating session...');
        $started = session_start();
        if($started){
            Logger::log('Session started.');
            $this->resumed = TRUE;
            $_SESSION['started-at'] = time();
            $_SESSION['resumed-at'] = time();
            $_SESSION['lifetime'] = $this->getLifetime();
            $_SESSION['name'] = $this->getName();
            $_SESSION['user'] = new User();
            $ip = filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP);
            if($ip == '::1'){
                $ip = '127.0.0.1';
            }
            $_SESSION['ip-address'] = $ip;
            $_SESSION['refresh'] = $refresh === TRUE ? TRUE : FALSE;
            $this->initLang(true,$useDefaultLang);
            if($useDb === TRUE){
                Logger::log('Using database connection with session.');
                $started = $this->useDb($dbAttributes);
                if($started !== TRUE){
                    Logger::log('Unable to connect to the database.', 'warning');
                }
                else{
                    Logger::log('Connected to the database.');
                }
            }
        }
        else{
            Logger::log('The function session_start() has returned FALSE.', 'warning');
        }
        Logger::logReturnValue($started);
        Logger::logFuncReturn(__METHOD__);
        return $started;
    }
    /**
     * Validate session variables. Must be called after session is started.
     * @return boolean  If the variables 'started-at', 'resumed-at', 'lifetime', 
     * 'refresh' and 'ip-address' are 
     * set, The function will return TRUE. Other than that, it will return 
     * FALSE.
     */
    private function validateAttrs(){
        return isset($_SESSION['started-at']) &&
        isset($_SESSION['resumed-at']) &&
        isset($_SESSION['lifetime']) &&
        isset($_SESSION['refresh']) && isset($_SESSION['ip-address']);
    }
    /**
     * Stops the session and delete all stored session variables.
     * @return boolean TRUE if the session stopped. FALSE if not.
     * @since 1.0
     */
    public function kill(){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        if(isset($_SESSION)){
            $params = session_get_cookie_params();
            if(session_status() == PHP_SESSION_ACTIVE){
                setcookie($this->getName(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
                session_destroy();
                $this->sessionStatus = self::KILLED;
                $retVal = TRUE;
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }

    /**
     * Returns the link that is used to connect to the database.
     * @return DatabaseLink|NULL An instance of 'DatabaseLink' if the 
     * session is running. NULL if the session is not running or the 
     * session does not use database connection.
     */
    public function getDBLink(){
        Logger::logFuncCall(__METHOD__);
        $retVal = NULL;
        if($this->isResumed()){
            if(isset($_SESSION['db'])){
                $retVal = $_SESSION['db'];
            }
            else{
                Logger::log('The variable $_SESSION[\'db\'] is not set.', 'warning');
            }
        }
        else{
            Logger::log('Session is not active.', 'warning');
        }
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns the ID of the session.
     * @return string The ID of the session. If the session is not active, 
     * the function will return -1.
     * @since 1.5
     */
    public function getID(){
        Logger::logFuncCall(__METHOD__);
        $retVal = -1;
        if(session_status() == PHP_SESSION_ACTIVE){
            $retVal = session_id();
        }
        else{
            Logger::log('Session is not active.', 'warning');
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if the given session name has a cookie or not.
     * @return boolean TRUE if a cookie with the name of 
     * the session is fount. FALSE otherwise.
     * @since 1.5
     */
    public function hasCookie(){
        $sid = filter_input(INPUT_COOKIE, $this->getName());
        if($sid !== NULL && $sid !== FALSE){
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns the time at which the session was resumed in (in seconds).
     * @return int The time at which the session was resumed in. If the session 
     * is new, this value will be the same as the session start time. If no 
     * session is active, the function will return the constant 'PHP_SESSION_NONE'. 
     * If sessions are disabled, the function will return the constant 'PHP_SESSION_DISABLED'.
     * @since 1.5
     */
    public function getResumTime(){
        if(session_status() == PHP_SESSION_ACTIVE){
            return $_SESSION['resumed-at'];
        }
        return session_status();
    }
    /**
     * Returns the IP address at which the session was started running from.
     * @return string The IP address at which the session was started running from. If no 
     * session is active, the function will return the constant 'PHP_SESSION_NONE'. 
     * If sessions are disabled, the function will return the constant 'PHP_SESSION_DISABLED'.
     * @since 1.7
     */
    public function getStartIpAddress(){
        if(session_status() == PHP_SESSION_ACTIVE){
            return $_SESSION['ip-address'];
        }
        return session_status();
    }
    /**
     * Returns the time at which the session was started in (in seconds).
     * @return int The time at which the session was started in. If no 
     * session is active, the function will return the constant 'PHP_SESSION_NONE'. 
     * If sessions are disabled, the function will return the constant 'PHP_SESSION_DISABLED'.
     * @since 1.5
     */
    public function getStartTime(){
        if(session_status() == PHP_SESSION_ACTIVE){
            return $_SESSION['started-at'];
        }
        return session_status();
    }
    /**
     * Returns the remaining time till the session dies (in seconds).
     * @return int The remaining time till the session dies (in seconds).
     * @since 1.5
     * 
     */
    public function getRemainingTime() {
        if(session_status() == PHP_SESSION_ACTIVE && $this->isRefresh()){
            return $this->getLifetime()*60; 
        }
        else{
            return $this->getLifetime()*60 - $this->getPassedTime();
        }
    }
    /**
     * Returns the number of seconds that has been passed since session started.
     * @return int The number of seconds that has been passed since session started. If no 
     * session is active, the function will return 0. 
     * If sessions are disabled, the function will return 0.
     * @since 1.5
     */
    public function getPassedTime() {
        if(session_status() == PHP_SESSION_ACTIVE){
            if(isset($_SESSION['started-at'])){
                return time() - $_SESSION['started-at'];
            }
            return 0;
        }
        return 0;
    }
    /**
     * Checks if the session has timed out or not.
     * @return boolean|int TRUE if the session has timed out. FALSE if not. If no 
     * session is active, the function will return the constant 'PHP_SESSION_NONE'. 
     * If sessions are disabled, the function will return the constant 'PHP_SESSION_DISABLED'.
     * @since 1.5
     */
    public function isTimeout(){
        if(session_status() == PHP_SESSION_ACTIVE){
            return $this->getRemainingTime() < 0;
        }
        return session_status();
    }
    /**
     * Checks if the session is resumed or not.
     * @return boolean TRUE if the session is resumed or its new. If the session is 
     *  not resumed, the function will return FALSE.
     * @since 1.5
     */
    public function isResumed(){
        return $this->resumed;
    }
    /**
     * Returns the ID of a session from a cookie given its name.
     * @param string $sessionName The name of the session.
     * @return boolean|string If the ID is found, the function will return it. 
     * If the session cookie was not found, the function will return FALSE.
     * @since 1.6
     */
    public static function getSessionIDFromCookie($sessionName) {
        $sid = filter_input(INPUT_COOKIE, $sessionName);
        if($sid !== NULL && $sid !== FALSE){
            return $sid;
        }
        return FALSE;
    }
    /**
     * Checks if there exist a session with the given session name or not. If there 
     * is a one and it is not timed out, the function will resume it.
     * @return boolean TRUE if there is a session with the given name 
     * and it is resumed. FALSE= otherwise. If the session is timed out, the 
     * function will kill it.
     * @since 1.0
     */
    public function resume(){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        Logger::log('Checking if session has a cookie...');
        if($this->hasCookie()){
            session_name($this->getName());
            ini_set('session.gc_maxlifetime', $this->getLifetime());
            ini_set('session.cookie_lifetime', $this->getLifetime());
            ini_set('session.use_cookies', 1);
            session_id(self::getSessionIDFromCookie($this->getName()));
            session_start();
            Logger::log('Validating session attributes...');
            if($this->validateAttrs()){
                Logger::log('Checking if session has timed out...');
                if(!$this->isTimeout()){
                    $ip = filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP);
                    if($ip == '::1'){
                        $ip = '127.0.0.1';
                    }
                    Logger::log('Comparing stored IP address and request source IP address...');
                    if($this->getStartIpAddress() == $ip){
                        Logger::log('Session status updated to \'resumed\'.');
                        $this->resumed = true;
                        $this->sessionStatus = self::RESUMED;
                        //update resume time
                        $_SESSION['resumed-at'] = time();
                        Logger::log('Resumed at: '.$_SESSION['resumed-at'], 'debug');
                        if($this->isRefresh()){
                            Logger::log('Refreshing session timeout time...');
                            //refresh time till session cookie is dead
                            $params = session_get_cookie_params();
                            setcookie($this->getName(), $this->getID(),time()+$this->getLifetime() * 60, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
                            $this->setUser($this->getUser());
                        }
                        $retVal = TRUE;
                    }
                    else{
                        Logger::log('The Request source IP address does not match stored IP Address.', 'warning');
                        $this->kill();
                        $this->sessionStatus = self::INV_IP_ADDRESS;
                    }
                }
                else{
                    Logger::log('Session has timed out.', 'warning');
                    $this->kill();
                    $this->sessionStatus = self::EXPIRED;
                }
            }
            else{
                Logger::log('The session has missing or invalid attributes.', 'warning');
                $this->kill();
                $this->sessionStatus = self::INV_STATE;
            }
        }
        else{
            Logger::log('Session has invalid cookie or has no cookie.','warning');
            $this->kill();
            $this->sessionStatus = self::INV_COOKIE;
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Returns the status of the session that the manager is currently manages.
     * @return string The status of the session that the manager is currently manages.
     * @since 1.5
     */
    public function getSessionStatus(){
        return $this->sessionStatus;
    }
    /**
     * Returns the name of the session.
     * @return string The name of the session.
     * @since 1.5
     */
    public function getName() {
        return $this->sessionName;
    }
    /**
     * Returns a 'JsonX' object that represents the manager.
     * @return JsonX
     * @since 1.5
     */
    public function toJSON() {
        $j = new JsonX();
        $j->add('name', $this->getName());
        $j->add('duration', $this->getLifetime()*60);
        $j->add('has-cookie', $this->hasCookie());
        $j->add('session-id', $this->getID());
        $j->add('language', $this->getLang());
        try{
            $j->add('refresh', $this->isRefresh());
        } catch (Exception $ex) {

        }
        $j->add('passed-time', $this->getPassedTime());
        $j->add('timeout-after', $this->getRemainingTime());
        $stTm = $this->getStartTime();
        if($stTm != PHP_SESSION_NONE && $stTm != PHP_SESSION_ACTIVE && $stTm != PHP_SESSION_DISABLED){
            $j->add('started-at', date('Y-m-d H:i:s',$this->getStartTime()));
        }
        $rsTm = $this->getStartTime();
        if($rsTm != PHP_SESSION_NONE && $rsTm != PHP_SESSION_ACTIVE && $rsTm != PHP_SESSION_DISABLED){
            $j->add('resumed-at', date('Y-m-d H:i:s',$this->getResumTime()));
        }
        $j->add('status', $this->sessionStatus);
        $j->add('user', $this->getUser());
        return $j;
    }

}

