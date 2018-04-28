<?php
/**
 * A helper class to manage system sessions.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.5
 */
class SessionManager implements JsonI{
    /**
     * The name of the session.
     * @var string
     * @since 1.5 
     */
    private $sessionName;
    /**
     * A variable that must be set to true if the user would like to refresh 
     * expiry time of a session every time session resumes.
     * @var boolean 
     */
    private $refreshEndTime;
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
     * @see 
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
    private static $singleton;
    /**
     * An array of supported languages.
     * @var array An array of supported languages.
     * @since 1.2
     */
    const SUPOORTED_LANGS = array(
        'EN','AR'
    );
    /**
     * Creates new session manager.
     * @param string $session_name The name of the session.
     * @since 1.0
     */
    private function __construct($session_name='pa-seesion',$refreshEndTime=false) {
        $this->sessionName = $session_name;
        //initial life time: 100 minutes.
        $this->lifeTime = 100;
        $this->refreshEndTime = $refreshEndTime;
    }
    /**
     * Creates a single instance of <b>SessionManager</b>.
     * @return SessionManager An object of type <b>SessionManager</b>.
     * @param boolean $use_default If set to true, The session will be started 
     * using default parameters. If set to false, the session will not start till 
     * the user call the function <b>SessionManager::initSession()</b>.
     * @since 1.0
     */
    public static function get($session_name='pa-session',$autostart=true){
        if(self::$singleton != NULL){
            return self::$singleton;
        }
        self::$singleton = new SessionManager($session_name);
        if($autostart){
            self::$singleton->initSession($session_name);
        }
        return self::$singleton;
    }
    /**
     * Sets the lifetime of the session.
     * @param int $time Session lifetime (in minutes). it will be set only if 
     * the given value is greater than 0. Also if the session is started and refresh 
     * timeout is set to true, the time will be updated. If started and refresh is 
     * set to false, Time will not updated.
     * @return boolean <b>TRUE</b> if time is updated. <b>FALSE</b> otherwise.
     * @since 1.4
     */
    public function setLifetime($time){
        if($time > 0){
            if(session_status() == PHP_SESSION_ACTIVE){
                if(!$this->isTimeout()){
                    if($this->isRefresh()){
                        $this->lifeTime = $time;
                        $_SESSION['lifetime'] = $time;
                        return TRUE;
                    }
                }
            }
            else{
                $this->lifeTime = $time;
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Returns the lifetime of the session (in minutes). 
     * @return int the lifetime of the session (in minutes).
     * @since 1.4
     */
    public function getLifetime(){
        if(session_status() == PHP_SESSION_ACTIVE){
            if(isset($_SESSION['lifetime'])){
                return $_SESSION['lifetime'];
            }
        }
        return $this->lifeTime;
    }
    /**
     * Initialize session language. The initialization depends on the attribute 
     * 'lang'. It can be send via 'get' request, 'post' request or a cookie. If 
     * no language code is provided, 'EN' will be used. The provided language 
     * must be in the array <b>SessionManager::SUPOORTED_LANGS</b>. If the given 
     * language code is not in the given array, 'EN' will be used. Also if the 
     * language is set before, it will not be updated unless the parameter <b>$forceUpdate</b> 
     * is set to true.
     * @since 1.2
     * @param boolean $forceUpdate Set to <b>TRUE</b> if the language is set and want to 
     * reset it.
     * @return boolean The function will return <b>TRUE</b> if the language is set or 
     * updated. Other than that, the function will return <b>FALSE</b>.
     */
    private function initLang($forceUpdate=false){
        if(isset($_SESSION['lang']) && !$forceUpdate){
            return FALSE;
        }
        $lang = filter_input(INPUT_GET, 'lang');
        if($lang == FALSE || $lang == NULL){
            $lang = filter_input(INPUT_POST, 'lang');
            if($lang == FALSE || $lang == NULL){
                $lang = filter_input(INPUT_COOKIE, 'lang');
                if($lang == FALSE || $lang == NULL){
                    $lang = NULL;
                }
            }
        }
        if(isset($_SESSION['lang']) && $lang == NULL){
            return FALSE;
        }
        else if($lang == NULL){
            $lang = 'EN';
        }
        $langU = strtoupper($lang);
        if(in_array($langU, self::SUPOORTED_LANGS)){
            $_SESSION['lang'] = $langU;
        }
        else{
            $_SESSION['lang'] = 'EN';
        }
    }
    /**
     * Returns session language code.
     * @return string|NULL two digit language code (such as 'EN'). If the session 
     * is not running, the function will return <b>NULL</b>.
     * @param boolean $forceUpdate Set to <b>TRUE</b> if the language is set and want to 
     * reset it. The reset process depends on the attribute 
     * 'lang'. It can be send via 'get' request, 'post' request or a cookie. If 
     * no language code is provided and the parameter <b>$forceUpdate</b> is set 
     * to <b>TRUE</b>, 'EN' will be used. The provided language 
     * must be in the array <b>SessionManager::SUPOORTED_LANGS</b>. If the given 
     * language code is not in the given array and the parameter <b>$forceUpdate</b> is set 
     * to <b>TRUE</b>, 'EN' will be used.
     */
    public function getLang($forceUpdate=false){
        if($this->isStarted()){
            $this->initLang($forceUpdate);
            return $_SESSION['lang'];
        }
        return NULL;
    }
    /**
     * Checks the status of login user token. The function check the match between 
     * the token that is sent with the request and the token in the session. 
     * The token can be send via 'get' request, 'post' request or a cookie. The 
     * token must be stored in the parameter 'token'
     * @return boolean <b>TRUE</b> if the user token is valid. <b>FALSE</b> if 
     * not. 
     * @since 1.1
     */
    public function validateToken(){
        $tok = filter_input(INPUT_COOKIE, 'token');
        if($tok === FALSE || $tok === NULL){
            $tok = filter_input(INPUT_GET, 'token');
            if($tok === FALSE || $tok === NULL){
                $tok = filter_input(INPUT_POST, 'token');
                if($tok === FALSE || $tok === NULL){
                    return FALSE;
                }
            }
        }
        $user = $this->getUser();
        if($user != NULL){
            return $user->getToken() == $tok;
        }
    }
    /**
     * Sets the user who is using the system. It is used in case of log in.
     * @param User $user an object of type <b>User</b>. Once the user is set, 
     * a cookie with the name 'token' will be created. This cookie will contain 
     * user access token.
     * @return boolean <b>TRUE</b> in case the user is set. <b>FALSE</b> if not.
     * @since 1.0
     */
    public function setUser($user){
        if(self::isStarted()){
            if($user instanceof User){
                $_SESSION['user'] = $user;
                setcookie('token', $user->getToken(), time()+$this->getLifetime()*60, '/');
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Returns the user who is logged in.
     * @return User|NULL an object of type <b>User</b>. If the session is not started 
     * or no used is logged in, the function will return <b>NULL</b>.
     * @since 1.0
     */
    public function getUser(){
        if(self::isStarted()){
            if(isset($_SESSION['user'])){
                return $_SESSION['user'];
            }
        }
        return NULL;
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
        if($this->isStarted()){
            if(isset($dbAttrs['host'])){
                if(isset($dbAttrs['user'])){
                    if(isset($dbAttrs['pass'])){
                        if(isset($dbAttrs['db-name'])){
                            $_SESSION['db'] = new DatabaseLink($dbAttrs['host'],$dbAttrs['user'],$dbAttrs['pass']);
                            if($_SESSION['db']->isConnected()){
                                if($_SESSION['db']->setDB($dbAttrs['db-name'])){
                                    return TRUE;
                                }
                            }
                            else{
                                return self::DB_CONNECTION_ERR;
                            }
                        }
                        else{
                            return self::MISSING_DB_NAME;
                        }
                    }
                    else{
                        return self::MISSING_DB_PASS;
                    }
                }
                else{
                    return self::MISSING_DB_USER;
                }
            }
            else{
                return self::MISSING_DB_HOST;
            }
        }
    }
    /**
     * Initialize the session.
     * @see SessionManager::useDb($dbAttrs=array())
     * @since 1.0
     * @param boolean $refresh [optional] If set to true, The due time of the session will 
     * be refreshed if the session is not timed out. Default is <b>FALSE</b>. 
     * @param boolean $useDb [optional] If set to <b>TRUE</b>, an attempt to connect 
     * to a database will be done. 
     * @param array $dbAttributes Database connection info.
     * @return boolean|string <b>TRUE</b> if the initialization was successful. <b>FALSE</b> 
     * in case of error. Also it is possible that the function will return one 
     * of the database error messages.
     */
    public function initSession($refresh=false,$useDb=false,$dbAttributes=array()){
        $lifeTime = $this->getLifetime() * 60;
        if($this->hasCookie()){
            if($this->isStarted()){
                return TRUE;
            }
            else{
                return $this->start($useDb, $lifeTime, $dbAttributes);
            }
        }
        else{
            $this->refreshEndTime = $refresh;
            $_SESSION['refresh'] = $refresh;
            return $this->start($useDb, $lifeTime, $dbAttributes);
        }
    }
    public function isRefresh(){
        if(session_status() == PHP_SESSION_ACTIVE){
            if(isset($_SESSION['refresh'])){
                return $_SESSION['refresh'];
            }
        }
        return $this->refreshEndTime;
    }
    private function start($useDb,$lifeTime,$dbAttributes){
        session_name($this->getName());
        session_set_cookie_params($lifeTime,"/");
        $started = session_start();
        $_SESSION['started-at'] = time();
        $_SESSION['resumed-at'] = time();
        $_SESSION['lifetime'] = $this->getLifetime();
        if($started){
            if($useDb == TRUE){
                return $this->useDb($dbAttributes);
            }
        }
        return $started;
    }
    /**
     * Stops the session and delete all stored session variables.
     * @return boolean <b>TRUE</b> if the session stopped. <b>FALSE</b> if not.
     * @since 1.0
     */
    public function kill(){
        if(isset($_SESSION)){
            $params = session_get_cookie_params();
            if(session_status() == PHP_SESSION_ACTIVE){
                setcookie($this->getName(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
                session_destroy();
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Returns the link that is used to connect to the database.
     * @return DatabaseLink|NULL An instance of <b>DatabaseLink</b> if the 
     * session is running. <b>NULL</b> if the session is not running or the 
     * session does not use database connection.
     */
    public function getDBLink(){
        if(self::isStarted()){
            if(isset($_SESSION['db'])){
                return $_SESSION['db'];
            }
        }
        return NULL;
    }
    /**
     * Returns the ID of the session.
     * @return string The ID of the session.
     * @since 1.5
     */
    public function getID(){
        return session_id();
    }
    /**
     * Checks if the given session name has a cookie or not.
     * @return boolean <b>TRUE</b> if a cookie with the name of 
     * the session is fount. <b>FALSE</b> otherwise.
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
     * session is active, the function will return the constant <b>PHP_SESSION_NONE</b>. 
     * If sessions are disabled, the function will return the constant <b>PHP_SESSION_DISABLED</b>.
     * @since 1.5
     */
    public function getResumTime(){
        if(session_status() == PHP_SESSION_ACTIVE){
            return $_SESSION['resumed-at'];
        }
        return session_status();
    }
    /**
     * Returns the time at which the session was started in (in seconds).
     * @return int The time at which the session was started in. If no 
     * session is active, the function will return the constant <b>PHP_SESSION_NONE</b>. 
     * If sessions are disabled, the function will return the constant <b>PHP_SESSION_DISABLED</b>.
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
        return $this->getLifetime()*60 - $this->getPassedTime();
    }
    /**
     * Returns the number of seconds that has been passed since session started.
     * @return int The number of seconds that has been passed since session started. If no 
     * session is active, the function will return the constant <b>PHP_SESSION_NONE</b>. 
     * If sessions are disabled, the function will return the constant <b>PHP_SESSION_DISABLED</b>.
     * @since 1.5
     */
    public function getPassedTime() {
        if(session_status() == PHP_SESSION_ACTIVE){
            if(isset($_SESSION['started-at'])){
                return time() - $_SESSION['started-at'];
            }
            return 0;
        }
        return session_status();
    }
    /**
     * Checks if the session has timed out or not.
     * @return boolean|int <b>TRUE</b> if the session has timed out. <b>FALSE</b> if not. If no 
     * session is active, the function will return the constant <b>PHP_SESSION_NONE</b>. 
     * If sessions are disabled, the function will return the constant <b>PHP_SESSION_DISABLED</b>.
     * @since 1.5
     */
    public function isTimeout(){
        if(session_status() == PHP_SESSION_ACTIVE){
            if($this->refreshEndTime){
                return $this->getResumTime() > 60 * $this->getLifetime();
            }
            else{
                return $this->getPassedTime() > 60 * $this->getLifetime();
            }
        }
        return session_status();
    }
    /**
     * Checks if there exist a session with the given session name or not. If there 
     * is a one and it is not timed out, the function will resume it.
     * @return boolean <b>TRUE</b> if there is a session with the given name 
     * and it is resumed. <b>FALSE</b> otherwise. If the session is timed out, the 
     * function will kill it.
     * @since 1.0
     */
    public function isStarted(){
        if($this->hasCookie()){
            $sid = filter_input(INPUT_COOKIE, $this->getName());
            session_name($this->getName());
            session_start();
            if($sid == session_id()){
                if(!$this->isTimeout()){
                    //update resume time
                    $_SESSION['resumed-at'] = time();
                    if($this->isRefresh()){
                        //refresh time till session cookie is dead
                        session_set_cookie_params($this->getLifetime() * 60,"/");
                        //also refresh start time
                        $_SESSION['resumed-at'] = time();
                    }
                    return TRUE;
                }
            }
        }
        $this->kill();
        return FALSE;
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
     * Returns a <b>JsonX</b> object that represents the manager.
     * @return JsonX
     * @since 1.5
     */
    public function toJSON() {
        $j = new JsonX();
        $j->add('name', $this->getName());
        $j->add('duration', $this->getLifetime()*60);
        $j->add('refresh', $this->isRefresh());
        $j->add('passed-time', $this->getPassedTime());
        $j->add('remaining-time', $this->getRemainingTime());
        $stTm = $this->getStartTime();
        if($stTm != PHP_SESSION_NONE && $stTm != PHP_SESSION_ACTIVE && $stTm != PHP_SESSION_DISABLED){
            $j->add('started-at', date('Y-m-d H:i:s',$this->getStartTime()));
        }
        $rsTm = $this->getStartTime();
        if($rsTm != PHP_SESSION_NONE && $rsTm != PHP_SESSION_ACTIVE && $rsTm != PHP_SESSION_DISABLED){
            $j->add('resumed-at', date('Y-m-d H:i:s',$this->getResumTime()));
        }
        return $j;
    }

}

