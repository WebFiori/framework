<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2020 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\session;

use webfiori\framework\exceptions\SessionException;
use webfiori\framework\User;
use webfiori\framework\WebFioriApp;
use webfiori\json\Json;
use webfiori\json\JsonI;
use webfiori\http\Request;
use webfiori\http\HttpCookie;
/**
 * A class that represents a session.
 *
 * @author Ibrahim
 * 
 * @since 1.1.0
 * 
 * @version 1.0
 */
class Session implements JsonI {
    /**
     * The default lifetime for any new session (in minutes).
     * 
     * @since 1.0
     */
    const DEFAULT_SESSION_DURATION = 120;

    /**
     * A constant that indicates the session was expired.
     * 
     * @since 1.0
     */
    const STATUS_EXPIERED = 'status_expiered';
    /**
     * A constant that indicates the session was initialized but not started or 
     * resumed.
     * 
     * @since 1.0
     */
    const STATUS_INACTIVE = 'status_none';
    /**
     * A constant that indicates the session has been killed by calling the 
     * method 'Session::kill()'.
     * 
     * @since 1.0
     */
    const STATUS_KILLED = 'status_killed';
    /**
     * A constant that indicates the session was just created.
     * 
     * @since 1.0
     */
    const STATUS_NEW = 'status_new';
    /**
     * A constant that indicates the session was paused.
     * 
     * @since 1.0
     */
    const STATUS_PAUSED = 'status_paused';
    /**
     * A constant that indicates the session has been resumed.
     * 
     * @since 1.0
     */
    const STATUS_RESUMED = 'status_resumed';
    private $sessionCookie;
    /**
     * The IP address of the user who is using the session.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $ipAddr;
    /**
     * A boolean which is set to true if the session timeout will be refreshed 
     * with every request.
     * 
     * @var boolean
     * 
     * @since 1.8.8 
     */
    private $isRef;
    /**
     * A string that represents language code of the session.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private $langCode;
    /**
     * The lifetime of the session (in minutes).
     * 
     * @var int lifetime of the session (in minutes). The default is 10.
     * 
     * @since 1.0 
     */
    private $lifeTime;
    /**
     * Number of seconds passed since the session was started.
     * 
     * @var int
     * 
     * @since 1.0 
     */
    private $passedTime;
    /**
     * The name of random function which is used in session ID generation.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private static $randFunc;
    /**
     * The timestamp at which the session was resumed at as Unix timestamp.
     * 
     * @var int 
     * 
     * @since 1.0
     */
    private $resumedAt;
    /**
     * An array that holds session variables.
     * 
     * @var array
     * 
     * @since 1.0 
     */
    private $sessionArr;
    private $sessionStatus;
    /**
     * An object of type 'User' that represents session user.
     * 
     * @var SessionUser
     * 
     * @since 1.0 
     */
    private $sesstionUser;

    /**
     * The timestamp at which the session was started in as Unix timestamp.
     * 
     * @var int 
     * 
     * @since 1.0
     */
    private $startedAt;
    /**
     * Creates new instance of the class.
     * 
     * @param array $options An array that contains session options. Available 
     * options are:
     * <ul>
     * <li><b>name</b>: The name of the session. A valid name can only 
     * consist of [a-z], [A-Z], [0-9], dash and underscore. This must be 
     * provided or the method will throw an exception.</li>
     * <li><b>duration</b>: The duration of the session in minutes. Must be a number 
     * greater than or equal to 0. If 0 is given, it means the session is not 
     * persistent. If the duration is invalid, it will be set to Session::DEFAULT_SESSION_DURATION</li>
     * <li><b>refresh</b>: A boolean which is set to true if session timeout time 
     * will be refreshed with every request. Default is false.</li>
     * </ul>
     * 
     * @throws SessionException If session name is missing or invalid.
     * 
     * @since 1.0
     */
    public function __construct(array $options = []) {
        //used to support older PHP versions which does not have 'random_int'.
        self::$randFunc = is_callable('random_int') ? 'random_int' : 'rand';
        $this->sessionCookie = new HttpCookie();
        $this->sesstionUser = null;
        
        $this->sessionStatus = self::STATUS_INACTIVE;
        $this->passedTime = 0;
        $this->langCode = '';
        
        
        if (isset($options['refresh'])) {
            $this->setIsRefresh($options['refresh']);
        } else {
            $this->setIsRefresh(false);
        }
        
        if (!(isset($options['duration']) && $this->setDuration($options['duration']))) {
            $this->setDuration(self::DEFAULT_SESSION_DURATION);
        }
        if ($this->getDuration() == 0) {
            $this->setIsRefresh(false);
        }
        $tempSName = isset($options['name']) ? trim($options['name']) : null;

        if (!$this->_setName($tempSName)) {
            throw new SessionException('Invalid session name: \''.$tempSName.'\'.');
        }

        $this->getCookie()->setValue(isset($options['session-id']) ? trim($options['session-id']) : $this->_generateSessionID());
        $this->resumedAt = 0;
        $this->startedAt = 0;
        $this->sessionArr = [];
        $this->passedTime = 0;
        $this->ipAddr = Request::getClientIP();
        $this->getCookie()->setSameSite('Lax');
        $this->getCookie()->setIsSecure(true);
        $this->getCookie()->setIsHttpOnly(true);
        
    }
    /**
     * Returns a JSON string that represents the session.
     * 
     * @return string
     * 
     * @since 1.0
     */
    public function __toString() {
        return $this->toJSON().'';
    }
    /**
     * Store session state and pause the session.
     * 
     * Note that session state will be stored only if it is running.
     * 
     * @since 1.0
     */
    public function close() {
        if ($this->isRunning()) {
            SessionsManager::getStorage()->save($this->getId(), $this->serialize());
            $this->sessionStatus = self::STATUS_PAUSED;
            SessionsManager::pauseAll();
        }
    }
    /**
     * Returns the value of a session variable.
     * 
     * @param string $varName The name of the variable.
     * 
     * @return null|mixed If a variable which has the given name is found, its 
     * value is returned. If no such variable exist, the method will return null.
     * 
     * @since 1.0
     */
    public function get(string $varName) {
        if ($this->isRunning()) {
            $trimmed = trim($varName);

            if (isset($this->sessionArr[$trimmed])) {
                return $this->sessionArr[$trimmed];
            }
        }
    }
    /**
     * Returns a string which can be passed to the function 'header()' to set session 
     * cookie.
     * 
     * @return string The string that will be returned will have the following 
     * format: 
     * '&lt;cookie-name&gt;=&lt;val&gt;; expires=&lt;time&gt;; path=/ 
     * SameSite=&lt;Lax|None|Strict&gt;'
     * 
     * @since 1.0
     */
    public function getCookieHeader() : string {
        return $this->sessionCookie.'';;
    }
    /**
     * Returns the cookie which is associated with the cookie.
     * 
     * @return HttpCookie An object that holds session cookie information.
     */
    public function getCookie() : HttpCookie {
        return $this->sessionCookie;
    }
    /**
     * Returns the amount of time at which the session will live for in seconds.
     * 
     * @return int This method will return session duration in seconds. The
     * default duration of any new session is 120 minutes (7200 seconds).
     * 
     * @since 1.0
     */
    public function getDuration() : int {
        return intval(round($this->lifeTime * 60));
    }
    /**
     * Returns the ID of the session.
     * 
     * @return string The ID of the session.
     */
    public function getId() : string {
        return $this->getCookie()->getValue();
    }
    /**
     * Returns the IP address of the client at which the request has come from.
     * 
     * @return string
     * 
     * @since 1.0
     */
    public function getIp() : string {
        return $this->ipAddr;
    }
    /**
     * Returns session language code.
     * 
     * @param boolean $forceUpdate Set to true if the language is set and want to 
     * reset it. The reset process depends on the attribute 
     * 'lang'. It can be send via 'get' request, 'post' request or a cookie. If 
     * no language code is provided and the parameter '$forceUpdate' is set 
     * to true, 'EN' will be used. If the given 
     * language code is not in the given array and the parameter '$forceUpdate' is set 
     * to true, 'EN' will be used.
     * 
     * @return string|null two digit language code (such as 'EN'). If the session 
     * is not running or the language is not set, the method will return empty string.
     * 
     * @since 1.0
     */
    public function getLangCode(bool $forceUpdate = false) : string {
        $this->_initLang($forceUpdate);

        return $this->langCode;
    }
    /**
     * Returns the name of the session.
     * 
     * @return string The name of the session as string.
     * 
     * @since 1.0
     */
    public function getName() : string {
        return $this->getCookie()->getName();
    }
    /**
     * Returns the number of seconds that has been passed since the session started.
     * 
     * @return int The number of seconds that has been passed since the session started. 
     * If the session status is Session::STATUS_INACTIVE, the method will return 0.
     * 
     * @since 1.0
     */
    public function getPassedTime() : int {
        return $this->passedTime;
    }
    /**
     * Returns number of seconds remaining before the session timeout.
     * 
     * @return int If the session is persistent or set to refresh for every request, 
     * the method will return 0. Other than that, it will return remaining time. 
     * If the session has no remaining time, it will return -1.
     * 
     * @since 1.0
     */
    public function getRemainingTime() : int {
        if ($this->isRefresh()) {
            return $this->getDuration();
        }

        if (!$this->isPersistent()) {
            return 0;
        }
        $remainingTime = $this->getDuration() - $this->getPassedTime();

        if ($remainingTime < 0) {
            return -1;
        }

        return $remainingTime;
    }
    /**
     * Returns the time at which the session was resumed at in seconds.
     * 
     * @return int The time at which the session was resumed at in seconds. If 
     * the session is not running, the time will be 0. If the session is new, 
     * the time will be the same as start time.
     * 
     * @since 1.0
     */
    public function getResumedAt() : int {
        if ($this->isRunning()) {
            return $this->resumedAt;
        }

        return 0;
    }
    /**
     * Returns the time at at which the session was started at.
     * 
     * @return int The method will return the time in seconds. If the session 
     * is not running, the method will return 0.
     * 
     * @since 1.0
     */
    public function getStartedAt() : int {
        if ($this->isRunning()) {
            return $this->startedAt;
        }

        return 0;
    }
    /**
     * Returns the status of the session.
     * 
     * @return string The status of the session. 
     * 
     * @since 1.0
     */
    public function getStatus() : string {
        return $this->sessionStatus;
    }
    /**
     * Returns an object of type 'SessionUser' that represents session user.
     * 
     * @return SessionUser|null An object of type 'User' that represents session user.
     * If session user is not set, the method will return null.
     * 
     * @since 1.0
     */
    public function getUser() {
        return $this->sesstionUser;
    }
    /**
     * Returns an associative array that contains all session variables.
     * 
     * @return array An associative array that contains all session variables. 
     * The indices will be variables names and the value of each index is the 
     * variable value.
     * 
     * @since 1.0
     */
    public function getVars() : array {
        return $this->sessionArr;
    }
    /**
     * Checks if the session has a given value or not.
     * 
     * Note that the method will always return false if the session is not running.
     * 
     * @param string $varName The name of the variable that has the value.
     * 
     * @return boolean If the value exist, the method will return true. Other 
     * than that, the method will return false.
     * 
     * @since 1.0
     */
    public function has(string $varName) : bool {
        if ($this->isRunning()) {
            $trimmed = trim($varName);

            return isset($this->sessionArr[$trimmed]);
        }
        
        return false;
    }
    /**
     * Checks if the session cookie is persistent or not. 
     * 
     * A session is persistent if its duration is greater than 0 minutes (has a 
     * duration).
     * 
     * @return boolean If the session cookie is persistent, the method will return true. 
     * false otherwise.
     * 
     * @since 1.0
     */
    public function isPersistent() : bool {
        return $this->getDuration() != 0;
    }
    /**
     * Checks if session timeout time will be refreshed with every request or not. 
     * 
     * This method must be called only after calling the method 'SessionManager::initSession()'. 
     * or it will throw an exception.
     * 
     * @return boolean true If session timeout time will be refreshed with every request. 
     * false if not.
     * 
     * @throws SessionException If the session is not running. 
     * 
     * @since 1.5
     */
    public function isRefresh() : bool {
        return $this->isRef;
    }
    /**
     * Checks if the session is started and running or not.
     * 
     * @return If the status of the session is Session::STATUS_NEW or Session::STATUS_RESUMED, 
     * the method will return true. Other than that, the method will return false.
     * 
     * @since 1.0
     */
    public function isRunning() : bool {
        return $this->getStatus() == self::STATUS_NEW || $this->getStatus() == self::STATUS_RESUMED;
    }


    public function kill() {
        SessionsManager::getStorage()->remove($this->getId());
        $this->sessionStatus = self::STATUS_KILLED;
        $this->sessionCookie->kill();
    }
    /**
     * Retrieves the value of a session variable and removes it from the session.
     *  
     * @param string $varName The name of the variable.
     * 
     * @return mixed|null If the variable exist and its value is set, the method 
     * will return its value. If the value is not set or the session is not 
     * running, the method will return null.
     * 
     * @since 1.0
     */
    public function pull(string $varName) {
        if ($this->isRunning()) {
            $varVal = $this->get($varName);
            $this->remove($varName);

            return $varVal;
        }
    }
    /**
     * Re-create session ID.
     * 
     * @return string The new ID of the session.
     * 
     * @since 1.0
     */
    public function reGenerateID() : string {
        $this->getCookie()->setValue($this->_generateSessionID());

        return $this->getCookie()->getValue();
    }
    /**
     * Removes the value of a session variable.
     * 
     * @param string $varName The name of the variable.
     * 
     * @return boolean If the value was deleted, the method will return true. 
     * If the variable does not exist or the variable does not exist, the method 
     * will return false.
     * 
     * @since 1.0
     */
    public function remove(string $varName) : bool {
        if ($this->isRunning()) {
            $trimmed = trim($varName);

            if (isset($this->sessionArr[$trimmed])) {
                unset($this->sessionArr[$trimmed]);

                return true;
            }
        }

        return false;
    }
    /**
     * Serialize the session.
     * 
     * @return string The method will return a string that represents serialized 
     * session data.
     * 
     * @since 1.0
     */
    public function serialize() : string {
        $serializedSesstion = serialize($this);
        $cipherMeth = 'aes-256-ctr';

        //Need to do more research about the security of this approach.

        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'Other';
        //Shall we use IP address in key or not?
        //It would add more security.
        $key = $this->getId().$userAgent;

        $iv = substr(hash('sha256', $key), 0,16);



        if (in_array($cipherMeth, openssl_get_cipher_methods())) {
            return openssl_encrypt($serializedSesstion, $cipherMeth, $key,0, $iv);
        }

        return $serializedSesstion;
    }
    /**
     * Sets session variable. 
     * 
     * Note that session variable will be set only if the session is running.
     * 
     * @param string $name The name of the variable. Must be non-empty string.
     * 
     * @param mixed $val The value of the variable. It can be any thing.
     * 
     * @return boolean If the variable is set, the method will return true. If 
     * not, the method will return false.
     * 
     * @since 1.0
     */
    public function set(string $name, $val) : bool {
        if ($this->isRunning()) {
            $trimmed = trim($name);

            if (strlen($trimmed) > 0) {
                $this->sessionArr[$trimmed] = $val;

                return true;
            }
        }

        return false;
    }
    /**
     * Sets session duration.
     * 
     * Note that this method will also updates the 'expires' attribute of session 
     * cookie. Also, note that if the new duration less than the passed time, 
     * the session will expire.
     * 
     * @param float $time Session duration in minutes.
     * 
     * @return boolean If session duration is updated, the method will return true. 
     * False otherwise.
     * 
     * @since 1.0
     */
    public function setDuration(float $time) : bool {
        $asFloat = floatval($time);

        if ($asFloat >= 0) {
            $this->lifeTime = $asFloat;
            $this->sessionCookie->setExpires($asFloat);
            $this->_checkIfExpired();

            return true;
        }

        return false;
    }
    /**
     * Sets if the session timeout will be refreshed with every request 
     * or not.
     * 
     * @param boolean $bool If set to true, timeout time will be refreshed. 
     * Note that the property will be updated only if the session is running.
     * 
     * @since 1.0
     */
    public function setIsRefresh(bool $bool) {
        $this->isRef = $bool === true;
    }
    /**
     * Sets the value of the property 'SameSite' of session cookie.
     * 
     * @param string $val It can be one of the following values, 'Lax', 'Strict' 
     * or 'None'. If any other value is provided, it will be ignored.
     * 
     * @since 1.0
     */
    public function setSameSite(string $val) {
        $this->getCookie()->setSameSite($val);
    }
    /**
     * Sets the user that represents session user.
     * 
     * Note that the user will be set only if the session is active.
     * 
     * @param SessionUser $userObj An object of type 'User'.
     * 
     * @since 1.0
     */
    public function setUser(SessionUser $userObj) {
        if ($this->isRunning()) {
            $this->sesstionUser = $userObj;
        }
    }
    /**
     * Resumes or starts new session.
     * 
     * This method works as follows, it tries to read a session from sessions 
     * storage using the ID of the session. If a session is found, it will 
     * populate the instance with session values taken from the storage. If no 
     * session was found, the method will initialize new one. 
     * 
     * @since 1.0
     */
    public function start() {
        if (!$this->isRunning()) {
            $seesionStr = SessionsManager::getStorage()->read($this->getId());

            if ($this->getStatus() == self::STATUS_KILLED || $seesionStr === null || !$this->unserialize($seesionStr)) {
                $this->reGenerateID();
                $this->_initNewSesstionVars();
            } else {
                $this->_checkIfExpired();
            }
        }
    }
    /**
     * Returns an object of type 'Json' that represents the session.
     * 
     * @return Json
     * 
     * @since 1.0
     */
    public function toJSON() : Json {
        return new Json([
            'name' => $this->getName(),
            'startedAt' => $this->getStartedAt(),
            'duration' => $this->getDuration(),
            'resumedAt' => $this->getResumedAt(),
            'passedTime' => $this->getPassedTime(),
            'remainingTime' => $this->getRemainingTime(),
            'language' => $this->getLangCode(),
            'id' => $this->getId(),
            'isRefresh' => $this->isRefresh(),
            'isPersistent' => $this->isPersistent(),
            'status' => $this->getStatus(),
            'user' => $this->getUser(),
            'vars' => $this->getVars()
        ]);
    }
    /**
     * Unserialize a session and restore its data in the instance at which the 
     * method is called on.
     * 
     * @param string $serialized The serialized session as string.
     * 
     * @return boolean If the Unserialize was successfully completed, the method 
     * will return true. If Unserialize fails, the method will return false.
     * 
     * @since 1.0
     */
    public function unserialize(string $serialized) {
        $cipherMeth = 'aes-256-ctr';

        if (in_array($cipherMeth, openssl_get_cipher_methods())) {
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'Other';

            //Shall we use IP address in key or not?
            //It would add more security. But the session will be invalid
            //If user changes network.
            $key = $this->getId().$userAgent;

            $iv = substr(hash('sha256', $key), 0,16);
            $encrypted = openssl_decrypt($serialized, $cipherMeth, $key,0, $iv);

            if (strlen($encrypted) > 0) {
                $sesstionObj = @unserialize($encrypted);

                if ($sesstionObj instanceof Session) {
                    $this->sessionStatus = self::STATUS_RESUMED;
                    $this->_clone($sesstionObj);

                    return true;
                }
            }
        } else {
            $sesstionObj = @unserialize($serialized);

            if ($sesstionObj instanceof Session) {
                $this->sessionStatus = self::STATUS_RESUMED;
                $this->_clone($sesstionObj);

                return true;
            }
        }

        return false;
    }
    private function _checkIfExpired() {
        if ($this->getRemainingTime() < 0) {
            SessionsManager::getStorage()->remove($this->getId());
            $this->sessionStatus = self::STATUS_EXPIERED;
            $this->sessionCookie->kill();
        } else if ($this->isRefresh()) {
            $this->sessionCookie->setExpires($this->getDuration());
        }
    }
    private function _clone(Session $session) {
        $this->startedAt = $session->startedAt;
        $this->sessionCookie = $session->sessionCookie;
        $this->sessionArr = $session->sessionArr;
        $this->isRef = $session->isRef;
        $this->resumedAt = time();
        $this->lifeTime = $session->lifeTime;
        $langCodeR = $this->_getLangFromRequest();

        if ($langCodeR) {
            $this->langCode = $this->getLangCode(true);
        } else {
            $this->langCode = $session->langCode;
        }
        $this->passedTime = $this->getResumedAt() - $this->getStartedAt();
        $this->sesstionUser = $session->sesstionUser;
    }
    /**
     * Generate a random session ID.
     * 
     * @return string A new random session ID.
     * 
     * @since 1.0
     */
    private function _generateSessionID() {
        $date = date(DATE_ISO8601);
        $hash = hash('sha256', $date);
        $salt = time() + call_user_func(self::$randFunc, 0, 100);

        return hash('sha256',$hash.$salt.$this->getName());
    }
    /**
     * 
     * @return string|null
     */
    private function _getLangFromRequest() {
        $langIdx = 'lang';
        //get language code from $_GET
        $lang = filter_input(INPUT_GET, $langIdx);

        if (!$lang || $lang == null) {
            //if not in $_GET, check $_POST
            $lang = filter_input(INPUT_POST, $langIdx);

            //If not in $_POST, check cookie.
            if (!$lang || $lang == null) {
                $lang = filter_input(INPUT_COOKIE, $langIdx);

                if (!$lang || $lang == null) {
                    $lang = null;
                }
            }
        }

        return $lang;
    }
    /**
     * Initialize session language. 
     * 
     * The initialization depends on the attribute 'lang'. 
     * It can be send via 'get' request, 'post' request or a cookie. 
     * If the given language code is not in the given array, 
     * The used value will depend on the existence of the class 'AppConfig'. 
     * If it is exist, The value that is returned by AppConfig::getPrimaryLanguage()' .
     * If not, 'EN' is used by default.
     * Also if the language is set before, it will not be updated unless the 
     * parameter '$forceUpdate' is set to true.
     * 
     * @param boolean $forceUpdate Set to true if the language is set and want to 
     * reset it. Default is false.
     * 
     * @param boolean $useDefault If set to true, the method will 
     * use default language if no language attribute is found in request body.
     * 
     * @return boolean The method will return true if the language is set or 
     * updated. Other than that, the method will return false. Default is true which 
     * happens when session is not running.
     * 
     * @since 1.2
     */
    private function _initLang($forceUpdate = false,$useDefault = true) {
        if ($this->isRunning()) {
            if ($this->langCode != '' && !$forceUpdate) {
                return false;
            }
            //the value of default language.
            //used in case no language found 
            //in $_GET['lang'], $_POST['lang'] or in cookie
            $defaultLang = WebFioriApp::getAppConfig()->getPrimaryLanguage();
            $langCodeFromReq = $this->_getLangFromRequest();
            $retVal = false;
            $isNullCode = $langCodeFromReq === null;
            
            if ($isNullCode) {
                if ($this->langCode != '' || $useDefault === false) {
                    $retVal = false;
                } else if ($useDefault) {
                    $langCodeFromReq = $defaultLang;
                }
            }

            if ($langCodeFromReq !== null) {
                $langU = strtoupper($langCodeFromReq);

                if (strlen($langU) == 2) {
                    $this->langCode = $langU;
                    $retVal = true;
                }

                if ($useDefault && !$retVal && $this->langCode == '') {
                    $this->langCode = $defaultLang;
                    $retVal = true;
                } else {
                    $retVal = false;
                }
            }

            return $retVal;
        }

        return true;
    }
    private function _initNewSesstionVars() {
        $this->sessionArr = [];
        $this->resumedAt = time();
        $this->startedAt = time();
        
        $this->sessionStatus = self::STATUS_NEW;
        $this->_initLang();
    }
    private function _setName($name) {
        $trimmed = trim($name);

        if (strlen($trimmed) == 0) {
            return false;
        }

        for ($x = 0 ; $x < strlen($trimmed) ; $x++) {
            $char = $trimmed[$x];

            if (!($char == '-' || $char == '_' || ($char <= 'Z' && $char >= 'A') || ($char <= 'z' && $char >= 'a') || ($char >= '0' && $char <= '9'))) {
                return false;
            }
        }
        $this->getCookie()->setName($trimmed);
        
        return true;
    }
}
