<?php
/**
 * A helper class to manage system sessions.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.3
 */
class SessionManager{
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
        'EN'
    );
    private function __construct() {
        
    }
    /**
     * Creates a single instance of <b>SessionManager</b>.
     * @return SessionManager An object of type <b>SessionManager</b>.
     * @since 1.0
     */
    public static function get(){
        if(self::$singleton != NULL){
            return self::$singleton;
        }
        self::$singleton = new SessionManager();
        return self::$singleton;
    }
    /**
     * Initialize session language. The initialization depends on the attribute 
     * 'lang'. It can be send via 'get' request, 'post' request or a cookie. If 
     * no language code is provided, 'EN' will be used. The provided language 
     * must be in the array <b>SessionManager::SUPOORTED_LANGS</b>.
     * @since 1.2
     */
    private function initLang(){
        $lang = filter_input(INPUT_GET, 'lang');
        if($lang == FALSE || $lang == NULL){
            $lang = filter_input(INPUT_POST, 'lang');
            if($lang == FALSE || $lang == NULL){
                $lang = filter_input(INPUT_COOKIE, 'lang');
                if($lang == FALSE || $lang == NULL){
                    $lang = 'EN';
                }
            }
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
     * Returns language code.
     * @return string|NULL two digit language code (such as 'EN'). If the session 
     * is not running, the function will return <b>NULL</b>.
     */
    public function getLang(){
        if($this->isStarted()){
            if(!isset($_SESSION['lang'])){
                $this->initLang();
            }
            return $_SESSION['lang'];
        }
        return NULL;
    }
    /**
     * Checks the status of the user token. The function check the match between 
     * the token that is sent with the request and the token in the session.
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
                setcookie('token', $user->getToken());
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
            return $_SESSION['user'];
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
     * @return boolean|string <b>TRUE</b> if the initialization was successful. <b>FALSE</b> 
     * in case of error. Also it is possible that the function will return one 
     * of the database error messages.
     * @see SessionManager::useDb($dbAttrs=array())
     * @since 1.0
     */
    public function initSession($name='pa-sid',$useDb=false,$dbAttributes=array()){
        if(!self::isStarted()){
            session_name($name);
            $started = session_start();
        }
        if($started){
            if($useDb == TRUE){
                return $this->useDb($dbAttributes);
            }
        }
        return $started;
    }
    /**
     * Stops the session
     * @return boolean <b>TRUE</b> if the session stopped. <b>FALSE</b> if not.
     * @since 1.0
     */
    public function kill(){
        return session_destroy();
    }

    /**
     * Returns the link that is used to connect to the database.
     * @return DatabaseLink|NULL An instance of <b>DatabaseLink</b> if the 
     * session is running. <b>NULL</b> if the session is not running or the 
     * session does not use database.
     */
    public function getDBLink(){
        if(self::isStarted()){
            return $_SESSION['db'];
        }
        return NULL;
    }
    /**
     * Checks if the session is running or not.
     * @return boolean <b>TRUE</b> if the session is running. <b>FALSE</b> otherwise.
     * @since 1.0
     */
    public function isStarted(){
        return !(session_status() == PHP_SESSION_NONE);
    }
}

