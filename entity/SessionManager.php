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
 * @version 1.6
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
    public function __toString() {
        return $this->toJSON().'';
    }
    /**
     * Creates new session manager.
     * @param string $session_name The name of the session.
     * @since 1.0
     */
    public function __construct($session_name='pa-seesion') {
        $this->sessionName = $session_name;
        //initial life time: 120 minutes.
        $this->lifeTime = 120;
        $this->sessionStatus = 'Not Running';
        $this->resumed = FALSE;
        if(session_status() == PHP_SESSION_ACTIVE){
            session_write_close();
            session_id($this->generateSessionID());
        }
        //session_save_path(ROOT_DIR.'/tmp');
    }
    /**
     * Generate a random session ID.
     * @return string A new random session ID.
     * @since 1.6
     */
    private function generateSessionID() {
        $date = date(DATE_ISO8601);
        $hash = hash('sha256', $date);
        $time = time() + rand(0, 1000);
        $hash2 = hash('sha256',$hash.$time);
        return substr($hash2, 0, 27);
    }
    /**
     * Sets the lifetime of the session.
     * @param int $time Session lifetime (in minutes). it will be set only if 
     * the given value is greater than 0.
     * @return boolean <b>TRUE</b> if time is updated. <b>FALSE</b> otherwise.
     * @since 1.4
     */
    public function setLifetime($time){
        if($time > 0){
            if(session_status() == PHP_SESSION_ACTIVE){
                if(!$this->isTimeout()){
                    $this->lifeTime = $time;
                    $_SESSION['lifetime'] = $time;
                    $params = session_get_cookie_params();
                    setcookie($this->getName(), $this->getID(),time()+$this->getLifetime() * 60, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
                    return TRUE;
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
     * Sets if the session timeout will be refreshed with every request 
     * or not.
     * @param boolean $bool If set to <b>TRUE</b>, timeout time will be refreshed. 
     * Note that the property will be updated only if the session is running.
     * @since 1.5
     */
    public function setIsRefresh($bool){
        if(session_status() == PHP_SESSION_ACTIVE){
            if(gettype($bool) == 'boolean'){
                $_SESSION['refresh'] = $bool;
            }
        }
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
    private function initLang($forceUpdate=false,$useDefault=false){
        if(isset($_SESSION['lang']) && !$forceUpdate){
            return FALSE;
        }
        //the value of default language.
        //used in case no language found 
        //in $_GET['lang']
        $defaultLang = class_exists('SiteConfig') ? SiteConfig::get()->getPrimaryLanguage() : 'EN';
        $lang = NULL;
        if(isset($_GET['lang'])){
            $lang = filter_var($_GET['lang'],FILTER_SANITIZE_STRING);
        }
        if($lang == FALSE || $lang == NULL){
            if(isset($_POST['lang'])){
                $lang = filter_var($_POST['lang'],FILTER_SANITIZE_STRING);
            }
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
        else if($lang == NULL && $useDefault === TRUE){
            $lang = $defaultLang;
        }
        else if($lang == NULL && $useDefault !== TRUE){
            return FALSE;
        }
        $langU = strtoupper($lang);
        if(in_array($langU, self::SUPPORTED_LANGS)){
            $_SESSION['lang'] = $langU;
        }
        else if($useDefault === TRUE){
            $_SESSION['lang'] = $defaultLang;
        }
        else{
            return FALSE;
        }
    }
    /**
     * Returns session language code.
     * @return string|NULL two digit language code (such as 'EN'). If the session 
     * is not running or the language is not set, the function will return <b>NULL</b>.
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
        if($this->isResumed()){
            if($forceUpdate === TRUE){
                $this->initLang($forceUpdate);
            }
            if(isset($_SESSION['lang'])){
                return $_SESSION['lang'];
            }
        }
        return NULL;
    }
    /**
     * Checks the status of login user token. The function check the match between 
     * the token that is sent with the request and the token in the session. 
     * The token can be send via 'get' request, 'post' request or a cookie. The 
     * token must be stored in the parameter 'token'
     * @return boolean <b>TRUE</b> if the user token is valid. <b>FALSE</b> if 
     * not. Also the function will return <b>FALSE</b> if no user is logged in.
     * @since 1.1
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
     * @param User $user an object of type <b>User</b>. Once the user is set, 
     * a cookie with the name 'token' will be created. This cookie will contain 
     * user access token.
     * @return boolean <b>TRUE</b> in case the user is set. <b>FALSE</b> if not.
     * @since 1.0
     */
    public function setUser($user){
        if($this->isResumed()){
            if($user instanceof User){
                $_SESSION['user'] = $user;
                setcookie('token', $user->getToken(), time()+$this->getLifetime()*60, "/");
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
        if($this->isResumed()){
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
        if($this->isResumed()){
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
     * @param boolean $useDefaultLang [Optional] If the session is new and 
     * there was no language parameter was found in the request and this parameter 
     * is set to <b>TRUE</b>, default language will be used (EN). 
     * @param boolean $useDb [optional] If set to <b>TRUE</b>, an attempt to connect 
     * to a database will be done. 
     * @param array $dbAttributes Database connection info.
     * @return boolean|string <b>TRUE</b> if the initialization was successful. <b>FALSE</b> 
     * in case of error. Also it is possible that the function will return one 
     * of the database error messages.
     */
    public function initSession($refresh=false,$useDefaultLang=false,$useDb=false,$dbAttributes=array()){
        if(!$this->isResumed()){
            if($this->resume()){
                return TRUE;
            }
            else{
                $lifeTime = $this->getLifetime() * 60;
                return $this->start($refresh,$useDb, $lifeTime, $dbAttributes,$useDefaultLang);
            }
        }
        else{
            $this->setIsRefresh($refresh);
            $this->initLang(FALSE, $useDefaultLang);
        }
    }
    /**
     * Checks if session timeout time will be refreshed with every request or not. 
     * This function must be called only after calling the function <b>SessionManager::initSession()</b>. 
     * or it will throw an exception.
     * @return boolean <b>TRUE</b> If session timeout time will be refreshed with every request. 
     * <b>FALSE</b> if not.
     * @throws Exception If the session is not running. 
     * @since 1.5
     */
    public function isRefresh(){
        if(session_status() == PHP_SESSION_ACTIVE){
            if(isset($_SESSION['refresh'])){
                return $_SESSION['refresh'];
            }
        }
        throw new Exception('Session is not running');
    }
    private function start($refresh,$useDb,$lifeTime,$dbAttributes,$useDefaultLang=false){
        if($this->sessionStatus != NULL){
            $this->sessionStatus = 'New Session (A session with given name was exists. '.$this->sessionStatus.')';
        }
        else{
            $this->sessionStatus = 'New Session';
        }
        session_name($this->getName());
        session_set_cookie_params($lifeTime,"/");
        $started = session_start();
        if($started){
            $this->resumed = TRUE;
            $_SESSION['started-at'] = time();
            $_SESSION['resumed-at'] = time();
            $_SESSION['lifetime'] = $this->getLifetime();
            $_SESSION['name'] = $this->getName();
            if(gettype($refresh) === 'boolean'){
                $_SESSION['refresh'] = $refresh;
            }
            else{
                $_SESSION['refresh'] = FALSE;
            }
            $this->initLang(true,$useDefaultLang);
            if($useDb === TRUE){
                return $this->useDb($dbAttributes);
            }
        }
        return $started;
    }
    private function validateAttrs(){
        return isset($_SESSION['started-at']) &&
        isset($_SESSION['resumed-at']) &&
        isset($_SESSION['lifetime']) &&
        isset($_SESSION['refresh']);
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
                $this->sessionStatus = 'Killed';
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
        if($this->isResumed()){
            if(isset($_SESSION['db'])){
                return $_SESSION['db'];
            }
        }
        return NULL;
    }
    /**
     * Returns the ID of the session.
     * @return string The ID of the session. If the session is not active, 
     * the function will return -1.
     * @since 1.5
     */
    public function getID(){
        if(session_status() == PHP_SESSION_ACTIVE){
            return session_id();
        }
        return -1;
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
     * @return boolean|int <b>TRUE</b> if the session has timed out. <b>FALSE</b> if not. If no 
     * session is active, the function will return the constant <b>PHP_SESSION_NONE</b>. 
     * If sessions are disabled, the function will return the constant <b>PHP_SESSION_DISABLED</b>.
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
     * @return boolean <b>TRUE</b> if the session is resumed or its new. If the session is 
     *  not resumed, the function will return <b>FALSE</b>.
     * @since 1.5
     */
    public function isResumed(){
        return $this->resumed;
    }
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
     * @return boolean <b>TRUE</b> if there is a session with the given name 
     * and it is resumed. <b>FALSE</b> otherwise. If the session is timed out, the 
     * function will kill it.
     * @since 1.0
     */
    public function resume(){
        if($this->hasCookie()){
            session_name($this->getName());
            session_id(self::getSessionIDFromCookie($this->getName()));
            session_start();
            if($this->validateAttrs()){
                if(!$this->isTimeout()){
                    $this->resumed = true;
                    $this->sessionStatus = 'Session Resumed';
                    //update resume time
                    $_SESSION['resumed-at'] = time();
                    if($this->isRefresh()){
                        //refresh time till session cookie is dead
                        $params = session_get_cookie_params();
                        setcookie($this->getName(), $this->getID(),time()+$this->getLifetime() * 60, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
                        $this->setUser($this->getUser());
                    }
                    return TRUE;
                }
                else{
                    $this->kill();
                    $this->sessionStatus = 'Session timed out';
                }
            }
            else{
                $this->kill();
                $this->sessionStatus = 'Invalid session state';
            }
        }
        else{
            $this->kill();
            $this->sessionStatus = 'Killed. Eaher has no cookie or invalid cookie';
        }
        return FALSE;
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
     * Returns a <b>JsonX</b> object that represents the manager.
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

