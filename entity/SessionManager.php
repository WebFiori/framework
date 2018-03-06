<?php
/**
 * A helper class to manage system sessions.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.2
 */
class SessionManager{
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
     * is not running, the function will return <b>NULL</b>
     */
    public function getLang(){
        if($this->isStarted()){
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
     * Initialize the session.
     * @param string $name The name of the session.
     * @param string $dbhost The name of database host.
     * @param string $dbUser The username of the database user.
     * @param string $dbPass The password of the user.
     * @param string $dbName The name of the selected database.
     * @return boolean <b>TRUE</b> if the initialization was successful. <b>FALSE</b> 
     * in case of error.
     * @since 1.0
     */
    public function initSession($name='pa-sid',$dbhost='localhost',$dbUser='root',$dbPass='1',$dbName='test'){
        if(!self::isStarted()){
            session_name($name);
            session_start();
            $_SESSION['db'] = new DatabaseLink($dbhost,$dbUser,$dbPass);
            if($_SESSION['db']->isConnected()){
                if($_SESSION['db']->setDB($dbName)){
                    $this->initLang();
                    return TRUE;
                }
                else{
                    self::kill();
                    return FALSE;
                }
            }
            else{
                self::kill();
                return FALSE;
            }
        }
        else{
            return TRUE;
        }
    }
    /**
     * Stops the session
     * @since 1.0
     */
    public function kill(){
        session_destroy();
    }

    /**
     * Returns the link that is used to connect to the database.
     * @return DatabaseLink|NULL An instance of <b>DatabaseLink</b> if the 
     * session is running. <b>NULL</b> if the session is not running.
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

