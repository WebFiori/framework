<?php
namespace webfiori\entity;
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
use jsonx\JsonI;
use webfiori\SiteConfig;
use Exception;
/**
 * A helper class to manage system sessions.
 * @author Ibrahim 
 * @version 1.8.4
 */
class SessionManager implements JsonI{
    /**
     * The default lifetime for any new session (in minutes).
     * @version 1.8.4
     */
    const DEFAULT_SESSION_DURATION = 120;
    /**
     * The ID of the session.
     * @var string
     * @since 1.8.2 
     */
    private $sId;
    /**
     * A variable is set to TRUE if the session is resumed and set to FALSE if new.
     * @var boolean
     * @since 1.5 
     */
    private $resumed;
    /**
     * A variable is set to TRUE if the session is new and set to FALSE if resumed.
     * @var boolean
     * @since 1.8 
     */
    private $new;
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
     * @param string $session_name [Optional] The name of the session. The name 
     * can consist of any character other than space, comma, semi-colon and 
     * equal sign. If the name has one of the given characters, the session 
     * will have new randomly generated name.
     * @since 1.0
     */
    public function __construct($session_name='pa-seesion') {
        Logger::logFuncCall(__METHOD__);
        Logger::log('Validating session name...');
        if($this->_validateName($session_name) === TRUE){
            Logger::log('Valid session name.');
            $this->sessionName = $session_name;
        }
        else{
            Logger::log('Invalid session name. Generating random name.','warning');
            $this->sessionName = $this->_generateRandSessionName();
        }
        Logger::log('Session name is set to \''.$this->sessionName.'\'.', 'debug');
        if(session_status() == PHP_SESSION_ACTIVE){
            Logger::log('A session is active. Writing session variables and creating new one.', 'warning');
            Logger::log('Active session name = \''. session_name().'\'.', 'debug');
            Logger::log('Active session ID = \''. session_id().'\'.', 'debug');
            //if the session is active, we might need to switch
            //to new session
            session_write_close();
        }
        
        //-1 is used to check if a 
        //call to the function 'setLifetime()' 
        //was made
        $this->lifeTime = -1;
        
        $this->sessionStatus = self::NOT_RUNNING;
        $this->resumed = FALSE;
        $this->new = FALSE;
        $this->sId = $this->generateSessionID();
        Logger::logFuncReturn(__METHOD__);
        
        //$sesionSavePath = 'sessions';
        //if(Util::isDirectory($sesionSavePath, TRUE)){
            //session_save_path(ROOT_DIR.'/'.$sesionSavePath);
        //}
    }
    /**
     * Switch between sessions. The function first checks if a session is active. 
     * If a session is active, the function checks if the name that is stored 
     * in the instance is equal to the name stored in the $_SESSION. if the 
     * two are different, the function will stop the first session and activate 
     * the second one.
     * @return boolean If the session was switched, the function will return TRUE.
     * @since 1.8
     */
    private function _switchToSession() {
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        Logger::log('Checking if a session is active...');
        if(session_status() == PHP_SESSION_ACTIVE){
            Logger::log('Validating session attributes...');
            if($this->_validateAttrs() === TRUE){
                Logger::log('Session is active. Validating stored name with instance name...');
                $sName = $_SESSION['session-name'];
                $iName = $this->getName();
                Logger::log('$_SESSION\'session-name\' = \''.$sName.'\'.', 'debug');
                Logger::log('$this->getName() = \''.$iName.'\'.', 'debug');
                Logger::log('Session ID = \''.$this->sId.'\'.', 'debug');
                if($sName == $iName){
                    Logger::log('Both names are the same. No need to switch.');
                    $retVal = TRUE;
                }
                else{
                    Logger::log('Different names. Writing session variables and closing session.');
                    session_write_close();
                    Logger::log('Setting session name to \''.$iName.'\'.','debug');
                    session_name($iName);
                    Logger::log('Setting session ID to \''.$this->sId.'\'.','debug');
                    session_id($this->sId);
                    Logger::log('Starting session.');
                    session_start();
                    Logger::log('Validating session attributes...');
                    if($this->_validateAttrs() === TRUE){
                        Logger::log('Switched to session.');
                        $retVal = TRUE;
                    }
                    else{
                        Logger::log('Unable to switch. Eather the session is new or it was never resumed. Closing session', 'warning');
                        session_write_close();
                    }
                }
            }
            else{
                Logger::log('One or more of the attributes of the session are missing. Killing the session.','warning');
                $this->kill();
            }
        }
        else{
            Logger::log('No session is active. Activating session...');
            Logger::log('Session Name: \''.$this->getName().'\'.', 'debug');
            Logger::log('Session ID = \''.$this->sId.'\'.', 'debug');
            if(strlen($this->sId) != 0){
                session_name($this->getName());
                session_id($this->sId);
                session_start();
                if($this->_validateAttrs() === TRUE){
                    Logger::log('Switched to session.');
                    $retVal = TRUE;
                }
                else{
                    Logger::log('One or more of the attributes of the session is missing. Closing the session.','warning');
                    session_write_close();
                }
            }
            else{
                Logger::log('Session ID is empty string or null.','warning');
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Generate a random session name.
     * @return string A random session name in the formate 'session-xxxxxxxx'.
     * @since 1.8
     */
    public static function _generateRandSessionName(){
        $retVal = 'session-';
        for($x = 0 ; $x < 8 ; $x++){
            $hash = hash('sha256', rand(0, 100).$retVal);
            $retVal .= $hash[$x + rand(0, 40)];
        }
        return $retVal;
    }
    /**
     * Validate the name of the session.
     * @param string $name The name of the session. The following characters are 
     * invalid in session name: space, comma, semi-colon and equal sign.
     * @return boolean The function will return TRUE if the name of the session 
     * is valid.
     * @since 1.8
     */
    private function _validateName($name) {
        Logger::logFuncCall(__METHOD__);
        $len = strlen($name);
        $retVal = TRUE;
        Logger::log('Validating name length...');
        if($len != 0){
            Logger::log('Validating characters in name...');
            for($x = 0 ; $x < $len ; $x++){
                $char = $name[$x];
                Logger::log('Character = \''.$char.'\'.', 'debug');
                if($char == ' ' || $char == ',' || $char == ';' || $char == '='){
                    Logger::log('Invalid character was found.','warning');
                    $retVal = FALSE;
                    break;
                }
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
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
        return $id;
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
            if($this->isSessionActive()){
                $this->lifeTime = $time;
                $_SESSION['lifetime'] = $time*60;
                $params = session_get_cookie_params();
                setcookie($this->getName(), $this->getID(),time()+$this->getLifetime() * 60, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
                $retVal = TRUE;
                Logger::log('Session duration updated.');
                Logger::log('Checking if the session has timed out...');
                if($this->isTimeout()){
                    Logger::log('Session has timed out. Killing it.', 'warning');
                    $this->kill();
                }
            }
            else{
                Logger::log('Trying to switch between sessions...');
                if($this->_switchToSession()){
                    $this->lifeTime = $time;
                    $_SESSION['lifetime'] = $time*60;
                    $params = session_get_cookie_params();
                    setcookie($this->getName(), $this->getID(),time()+$this->getLifetime() * 60, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
                    $retVal = TRUE;
                    Logger::log('Session duration updated.');
                    Logger::log('It is active. Checking if the session has timed out...');
                    if($this->isTimeout()){
                        Logger::log('Session has timed out. Killing it.', 'warning');
                        $this->kill();
                    }
                }
                else{
                    $this->lifeTime = $time;
                    Logger::log('Time updated only in object (Not in session variable).', 'warning');
                }
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
     * @return int the lifetime of the session (in minutes). The value is taken 
     * from the object attributes or the variable $_SESSION['lifetime']. If the 
     * session is new and the time was not set, the function will return -1.
     * @since 1.4
     */
    public function getLifetime(){
        Logger::logFuncCall(__METHOD__);
        $retVal = $this->lifeTime;
        if(session_status() == PHP_SESSION_ACTIVE && $this->_switchToSession()){
            if(isset($_SESSION['lifetime'])){
                Logger::log('Time taken from $_SESSION[\'lifetime\']');
                Logger::log('$_SESSION[\'lifetime\'] = \''.$_SESSION['lifetime'].'\'.','debug');
                $retVal = $_SESSION['lifetime']/60;
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
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
        if($this->_switchToSession()){
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
     * 'lang'. It can be send via 'get' request, 'post' request or a cookie. 
     * The provided language must be in the array 'SessionManager::SUPOORTED_LANGS'. 
     * If the given language code is not in the given array, 
     * The used value will depend on the existence of the class 'SiteConfig'. 
     * If it is exist, The value that is returned by SiteConfig::getPrimaryLanguage()' .
     * If not, 'EN' is used by default.
     * Also if the language is set before, it will not be updated unless the parameter '$forceUpdate' is set to TRUE.
     * @param boolean $forceUpdate Set to TRUE if the language is set and want to 
     * reset it.
     * @param boolean $useDefault [Optional] If set to TRUE, the function will 
     * use default language if no language attribute is found in request body.
     * @return boolean The function will return TRUE if the language is set or 
     * updated. Other than that, the function will return FALSE.
     * @since 1.2
     */
    private function _initLang($forceUpdate=false,$useDefault=true){
        Logger::logFuncCall(__METHOD__);
        Logger::log('Force update = \''.$forceUpdate.'\'.', 'debug');
        Logger::log('Use default = \''.$useDefault.'\'.', 'debug');
        if(isset($_SESSION['lang']) && !$forceUpdate){
            Logger::log('Language did not updated.', 'warning');
            Logger::logFuncReturn(__METHOD__);
            return FALSE;
        }
        //the value of default language.
        //used in case no language found 
        //in $_GET['lang']
        $defaultLang = class_exists('webfiori\SiteConfig') ? SiteConfig::getPrimaryLanguage() : 'EN';
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
        if($useDefault === TRUE && $retVal == FALSE && !isset($_SESSION['lang'])){
            Logger::log('Using default language.');
            $_SESSION['lang'] = $defaultLang;
            $retVal = TRUE;
        }
        else{
            $retVal = FALSE;
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
    }
    /**
     * Returns session language code.
     * @return string|NULL two digit language code (such as 'EN'). If the session 
     * is not running or the language is not set, the function will return NULL.
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
        Logger::log('Checking if session is active...');
        $isActive = $this->isSessionActive() === TRUE ? TRUE : $this->_switchToSession();
        if($isActive){
            Logger::log('Session is active. Checking if language need update...');
            if($forceUpdate === TRUE){
                Logger::log('Updating languae...');
                $this->_initLang($forceUpdate);
                $retVal = $_SESSION['lang'];
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
        Logger::log('Checking if session is active...');
        $isActive = $this->isSessionActive() === TRUE ? TRUE : $this->_switchToSession();
        if($isActive){
            Logger::log('Checking if passed variable is an instance of \'User\'.');
            if($user instanceof User){
                Logger::log('User updated.');
                $_SESSION['user'] = $user;
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
     * @return User|NULL an object of type User. If the session is not started, 
     * the function will return NULL.
     * @since 1.0
     */
    public function &getUser(){
        Logger::logFuncCall(__METHOD__);
        $retVal = NULL;
        Logger::log('Checking if session is active...');
        $isActive = $this->isSessionActive() === TRUE ? TRUE : $this->_switchToSession();
        if($isActive){
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
     * Initialize the session.
     * @since 1.0
     * @param boolean $refresh [optional] If set to true, The due time of the session will 
     * be refreshed if the session is not timed out. Default is FALSE. 
     * @param boolean $useDefaultLang [Optional] If the session is new and 
     * there was no language parameter was found in the request and this parameter 
     * is set to TRUE, default language will be used (EN). 
     * @return boolean|string TRUE if the initialization was successful. FALSE 
     * in case of error. Also it is possible that the function will return one 
     * of the database error messages.
     */
    public function initSession($refresh=false,$useDefaultLang=true){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        Logger::log('Trying to switch to session...');
        if(!$this->_switchToSession()){
            Logger::log('Unable to switch. Trying to resume session...');
            if(!$this->resume()){
                Logger::log ('Unable to resume. Starting new session...');
                $lifeTime = $this->getLifetime();
                if($lifeTime == -1){
                    //set default time to two hours.
                    $this->setLifetime(self::DEFAULT_SESSION_DURATION);
                    $lifeTime = self::DEFAULT_SESSION_DURATION*60;
                } 
                $retVal = $this->_start($refresh, $lifeTime,$useDefaultLang);
            }
            else{
                Logger::log('Session resumed.');
            }
        }
        else{
            Logger::log('Switched to session.');
            $this->setIsRefresh($refresh);
            $this->_initLang(FALSE, $useDefaultLang);
            $retVal = TRUE;
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if the current session instance is the active one or not.
     * @return boolean If session is running and the stored session name is the same 
     * as the session name in the instance, the function will return TRUE. Other than that, 
     * the function will return FALSE.
     * @since 1.8
     */
    public function isSessionActive(){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        Logger::log('Checking if session is running...');
        if(session_status() == PHP_SESSION_ACTIVE){
            Logger::log('It is running. Checking if current instance is active...');
            $retVal = isset($_SESSION['session-name']) && $_SESSION['session-name'] == $this->getName();
            if($retVal === TRUE){
                Logger::log('Current instance is active.');
            }
            else{
                Logger::log('Another instance is active. Might need to switch between sessions.','warning');
            }
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
        $isActive = $this->isSessionActive() === TRUE ? TRUE : $this->_switchToSession();
        if($isActive){
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
        throw new Exception('Session is not running.');
    }
    /**
     * The core function of the class. 
     * @param type $refresh
     * @param type $lifeTime
     * @param type $useDefaultLang
     * @return type
     * @since 1.0
     */
    private function _start($refresh,$lifeTime,$useDefaultLang=false){
        Logger::logFuncCall(__METHOD__);
        $this->sessionStatus = self::NEW_SESSION;
        Logger::log('Setting session related ini directives and session variables...');
        ini_set('session.gc_maxlifetime', $lifeTime);
        ini_set('session.cookie_lifetime', $lifeTime);
        ini_set('session.use_cookies', 1);
        session_name($this->getName());
        session_id($this->sId);
        session_set_cookie_params($lifeTime,"/");
        Logger::log('Strating session...');
        $started = session_start();
        if($started){
            Logger::log('Session started.');
            $this->resumed = FALSE;
            $this->new = TRUE;
            $_SESSION['session-name'] = $this->getName();
            $_SESSION['started-at'] = time();
            $_SESSION['resumed-at'] = time();
            $_SESSION['lifetime'] = $lifeTime;
            $_SESSION['name'] = $this->getName();
            $_SESSION['user'] = new User();
            $ip = filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP);
            if($ip == '::1'){
                $ip = '127.0.0.1';
            }
            $_SESSION['ip-address'] = $ip;
            $_SESSION['refresh'] = $refresh === TRUE ? TRUE : FALSE;
            $this->_initLang(true,$useDefaultLang);
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
     * 'refresh', 'session-name' and 'ip-address' are 
     * set, The function will return TRUE. Other than that, it will return 
     * FALSE.
     */
    private function _validateAttrs(){
        $retVal = FALSE;
        Logger::log('Checking if $_SESSION[\'started-at\'] is set...');
        if(isset($_SESSION['started-at'])){
            Logger::log('Checking if $_SESSION[\'resumed-at\'] is set...');
            if(isset($_SESSION['resumed-at'])){
                Logger::log('Checking if $_SESSION[\'lifetime\'] is set...');
                if(isset($_SESSION['lifetime'])){
                    Logger::log('Checking if $_SESSION[\'refresh\'] is set...');
                    if(isset($_SESSION['refresh'])){
                        Logger::log('Checking if $_SESSION[\'ip-address\'] is set...');
                        if(isset($_SESSION['ip-address'])){
                            Logger::log('Checking if $_SESSION[\'session-name\'] is set...');
                            if(isset($_SESSION['session-name'])){
                                Logger::log('All session variables are set.');
                                $retVal = TRUE;
                            }
                            else{
                                Logger::log('The session variable is missing.','warning');
                            }
                        }
                        else{
                            Logger::log('The session variable is missing.','warning');
                        }
                    }
                    else{
                        Logger::log('The session variable is missing.','warning');
                    }
                }
                else{
                    Logger::log('The session variable is missing.','warning');
                }
            }
            else{
                Logger::log('The session variable is missing.','warning');
            }
        }
        else{
            Logger::log('The session variable is missing.','warning');
        }
        return $retVal;
    }
    /**
     * Stops the session and delete all stored session variables.
     * @return boolean TRUE if the session stopped. FALSE if not.
     * @since 1.0
     */
    public function kill(){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        Logger::log('Checking if session is active...');
        $isActive = session_status() == PHP_SESSION_ACTIVE;
        if($isActive){
            Logger::log('Session is active. Validating session attributes...');
            if($this->_validateAttrs()){
                Logger::log('Session has valid attributes. Validating stored name with instance name...');
                $sName = $_SESSION['session-name'];
                $iName = $this->getName();
                Logger::log('$_SESSION\'session-name\' = \''.$sName.'\'.', 'debug');
                Logger::log('$this->getName() = \''.$iName.'\'.', 'debug');
                if($_SESSION['session-name'] == $this->getName()){
                    Logger::log('Killing the session...');
                    $this->_kill();
                    $retVal = TRUE;
                }
                else{
                    Logger::log('The names are not the same. Switching sessions...');
                    session_write_close();
                    session_name($this->getName());
                    session_start();
                    Logger::log('Session switched. Validating session attributes...');
                    if($this->_validateAttrs()){
                        Logger::log('Killing the session...');
                        $this->_kill();
                        $retVal = TRUE;
                    }
                    else{
                        $this->_invAttrKill();
                        $retVal = TRUE;
                    }
                }
            }
            else{
                Logger::log('Session has invalid attributes. Killing it.');
                $this->_kill();
                $retVal = TRUE;
            }
        }
        else{
            Logger::log('Session is not running or not resumed. Trying to resume session...');
            session_name($this->getName());
            session_start();
            Logger::log('Session switched. Validating session attributes...');
            if($this->_validateAttrs()){
                Logger::log('Killing the session...');
                $this->_kill();
                $retVal = TRUE;
            }
            else{
                $this->_invAttrKill();
                $retVal = TRUE;
            }
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * @since 1.8.1
     */
    private function _invAttrKill(){
        Logger::log('Session has invalid attributes. Killing it.');
        $this->_kill(); 
    }
    /**
     * @since 1.8.1
     */
    private function _kill(){
        $params = session_get_cookie_params();
        setcookie($this->getName(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
        session_destroy();
        $this->sessionStatus = self::KILLED;
        Logger::log('Session Killed.');
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
        Logger::log('Checking if session is active...');
        $isActive = $this->isSessionActive() === TRUE ? TRUE : $this->_switchToSession();
        if($isActive){
            $retVal = session_id();
        }
        else{
            Logger::log('Session is not running or not active.', 'warning');
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
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        $sid = filter_input(INPUT_COOKIE, $this->getName());
        Logger::log('ID from cookie = \''.$sid.'\' ('.gettype($sid).').', 'debug');
        if($sid !== NULL && $sid !== FALSE){
            $retVal = TRUE;
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
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
        $isActive = $this->isSessionActive() === TRUE ? TRUE : $this->_switchToSession();
        if($isActive){
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
        $isActive = $this->isSessionActive() === TRUE ? TRUE : $this->_switchToSession();
        if($isActive){
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
        $isActive = $this->isSessionActive() === TRUE ? TRUE : $this->_switchToSession();
        if($isActive){
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
        $isActive = $this->isSessionActive() === TRUE ? TRUE : $this->_switchToSession();
        if($isActive && $this->isRefresh()){
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
        Logger::logFuncCall(__METHOD__);
        $retVal = 0;
        Logger::log('Checking if session is active...');
        $isActive = $this->isSessionActive() === TRUE ? TRUE : $this->_switchToSession();
        if($isActive){
            Logger::log('Session is active. Calculating remaining time...');
            $retVal = time() - $_SESSION['started-at'];
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if the session has timed out or not.
     * @return boolean|int TRUE if the session has timed out. FALSE if not. If no 
     * session is active, the function will return the constant 'PHP_SESSION_NONE'. 
     * If sessions are disabled, the function will return the constant 'PHP_SESSION_DISABLED'.
     * @since 1.5
     */
    public function isTimeout(){
        Logger::logFuncCall(__METHOD__);
        $retVal = FALSE;
        Logger::log('Checking if session is active...');
        $isActive = $this->isSessionActive() === TRUE ? TRUE : $this->_switchToSession();
        if($isActive){
            Logger::log('Session is active. Checking remaining time...');
            $remTime = $this->getRemainingTime();
            Logger::log('Remaining time = \''.$remTime.'\'.', 'debug');
            $retVal = $remTime < 0;
        }
        else{
            Logger::log('No session is active.', 'warning');
            $retVal = session_status();
        }
        Logger::logReturnValue($retVal);
        Logger::logFuncReturn(__METHOD__);
        return $retVal;
    }
    /**
     * Checks if the session is resumed or not.
     * @return boolean TRUE if the session is resumed. If the session is 
     * not resumed, or not running, the function will return FALSE.
     * @since 1.5
     */
    public function isResumed(){
        return $this->resumed;
    }
    /**
     * Checks if the session is resumed or not.
     * @return boolean TRUE if the session is new. If the session is 
     * not new, or not running, the function will return FALSE.
     * @since 1.8
     */
    public function isNew() {
        return $this->new;
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
     * Return session ID from session cookie, get or post parameter.
     * @return string|boolean If session ID is found, the function will 
     * return it. Note that if it is in a cookie, the name of the cookie must 
     * be the name of the session in order to take the ID from it. If it is 
     * in GET or POST request, it must be in a parameter with the name 
     * 'session-id'.
     * @since 1.8.3
     */
    public function getSessionIDFromRequest(){
        $sid = self::getSessionIDFromCookie($this->getName());
        if($sid === FALSE){
            $sid = filter_var($_POST['session-id'],FILTER_SANITIZE_STRING);
            if($sid === NULL || $sid === FALSE){
                $sid = filter_var($_GET['lang'],FILTER_SANITIZE_STRING);
            }
        }
        return $sid;
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
            Logger::log('Session has a cookie.');
            session_name($this->getName());
            Logger::log('Updating the value of \'session.use_cookies\'...');
            ini_set('session.use_cookies', 1);
            $sid = $this->getSessionIDFromRequest();
            if($sid !== FALSE){
                $tmpId = $sid;
                session_id($tmpId);
            }
            else{
                session_id($this->sId);
            }
            Logger::log('Session Name: \''.$this->getName().'\'.', 'debug');
            Logger::log('Session ID = \''.$this->sId.'\'.', 'debug');
            //get time before resuming to check if updated.
            //if not updated, this value will be -1.
            $sessionTime = $this->getLifetime();
            session_start();
            Logger::log('Validating session attributes...');
            if($this->_validateAttrs()){
                Logger::log('Session attributes are valid.');
                Logger::log('Checking if session has timed out...');
                if(!$this->isTimeout()){
                    Logger::log('Session still has time.');
                    $ip = filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP);
                    if($ip == '::1'){
                        $ip = '127.0.0.1';
                    }
                    Logger::log('Comparing stored IP address and request source IP address...');
                    if($this->getStartIpAddress() == $ip){
                        Logger::log('Session status updated to \'resumed\'.');
                        $this->sId = $tmpId;
                        $this->resumed = true;
                        $this->sessionStatus = self::RESUMED;
                        //update resume time
                        $_SESSION['resumed-at'] = time();
                        $_SESSION['session-name'] = $this->getName();
                        
                        //if time is -1, then get stored one.
                        //else, update session time.
                        $sessionTime = $sessionTime == -1 ? $this->getLifetime()*60 : $sessionTime*60;
                        
                        $_SESSION['lifetime'] = $sessionTime;
                        Logger::log('Session time = \''.$sessionTime.'\' seconds.', 'debug');
                        Logger::log('Updating the value of \'session.gc_maxlifetime\'...');
                        ini_set('session.gc_maxlifetime', $sessionTime);
                        Logger::log('Updating the value of \'session.gc_maxlifetime\'...');
                        ini_set('session.cookie_lifetime', $sessionTime);
                        $this->resumed = TRUE;
                        $this->new = FALSE;
                        Logger::log('Resumed at: '.$_SESSION['resumed-at'], 'debug');
                        if($this->isRefresh()){
                            Logger::log('Refreshing session timeout time...');
                            //refresh time till session cookie is dead
                            $params = session_get_cookie_params();
                            setcookie($this->getName(), $this->getID(),time()+$sessionTime, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
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
     * @return string The name of the session. The returned value will be not 
     * the one stored in $_SESSION['session-name'].
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

