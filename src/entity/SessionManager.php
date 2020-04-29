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
namespace webfiori\entity;

use webfiori\entity\exceptions\SessionException;
use jsonx\JsonI;
use jsonx\JsonX;
use webfiori\conf\SiteConfig;
/**
 * A helper class to manage system sessions.
 * @author Ibrahim 
 * @version 1.8.7
 */
class SessionManager implements JsonI {
    /**
     * The name of random function which is used in session ID generation.
     * @var string
     * @since 1.8.7 
     */
    private static $randFunc;
    /**
     * The default lifetime for any new session (in minutes).
     * @version 1.8.4
     */
    const DEFAULT_SESSION_DURATION = 120;
    /**
     * A constant that indicates the session has timed out.
     * @since 1.7
     */
    const EXPIRED = 'status_session_timeout';
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
     * A constant that indicates the session has invalid state (Usually one 
     * missing session variable).
     * @since 1.7
     */
    const INV_STATE = 'status_invalid_state';
    /**
     * A constant that indicates the session has been killed by calling the 
     * method 'SessionManager::kill()'.
     * @since 1.7
     */
    const KILLED = 'status_session_killed';
    /**
     * The maximum value at which a session can stay alive without activity (in 
     * minutes). 
     * @var int The maximum value at which a session can stay alive without activity. 
     * The value of the constant is around 5 years (2,629,746 minutes exactly).
     * @since 1.8.5
     */
    const MAX_LIFETIME = 2629746;
    /**
     * A constant that indicates the session has just started.
     * @since 1.7
     */
    const NEW_SESSION = 'status_new_session';
    /**
     * A constant that indicates the session is not started yet.
     * @since 1.7
     */
    const NOT_RUNNING = 'status_not_running';
    /**
     * A constant that indicates the session is resumed.
     * @since 1.7
     */
    const RESUMED = 'status_session_resumed';
    /**
     * An array that contains the names of main session variables.
     * The array has the following values:
     * <ul>
     * <li>lifetime</li>
     * <li>started-at</li>
     * <li>resumed-at</li>
     * <li>refresh</li>
     * <li>session-name</li>
     * <li>ip-address</li>
     * <li>user</li>
     * <li>lang</li>
     * <li></li>
     * <ul>
     * @since 1.8.6
     * @var array 
     */
    const MAIN_VARS = [
        'lifetime',
        'started-at',
        'resumed-at',
        'refresh',
        'session-name',
        'ip-address',
        'user',
        'lang'
    ];
    /**
     * An array of supported languages.
     * @var array An array of supported languages.
     * @since 1.2
     * @deprecated since version 1.8.5
     */
    const SUPPORTED_LANGS = [
        'EN','AR'
    ];
    /**
     * The lifetime of the session (in minutes).
     * @var int lifetime of the session (in minutes). The default is 10.
     * @since 1.4 
     */
    private $lifeTime;
    /**
     * The name of the index that contains session vara.
     * @var string
     * @since 1.8.6 
     */
    private static $SV = 'session-vars';
    /**
     * A variable is set to true if the session is new and set to false if resumed.
     * @var boolean
     * @since 1.8 
     */
    private $new;
    /**
     * A variable is set to true if the session is resumed and set to false if new.
     * @var boolean
     * @since 1.5 
     */
    private $resumed;
    /**
     * The name of the session.
     * @var string
     * @since 1.5 
     */
    private $sessionName;
    /**
     * A string that stores session status.
     * @var string
     * @since 1.5
     */
    private $sessionStatus;
    /**
     * The ID of the session.
     * @var string
     * @since 1.8.2 
     */
    private $sId;
    /**
     * Creates new session manager.
     * @param string $session_name The name of the session. The name 
     * can consist of any character other than space, comma, semi-colon and 
     * equal sign. If the name has one of the given characters, the session 
     * will have new randomly generated name.
     * @since 1.0
     */
    public function __construct($session_name = 'pa-seesion') {
        
        //used to support older PHP versions which does not have 'random_int'.
        self::$randFunc = is_callable('random_int') ? 'random_int' : 'rand';
        
        if ($this->_validateName($session_name) === true) {
            $this->sessionName = $session_name;
        } else {
            $this->sessionName = $this->_generateRandSessionName();
        }

        if (session_status() == PHP_SESSION_ACTIVE) {
            //if the session is active, we might need to switch
            //to new session
            session_write_close();
        }

        //-1 is used to check if a 
        //call to the function 'setLifetime()' 
        //was made
        $this->lifeTime = -1;

        $this->sessionStatus = self::NOT_RUNNING;
        $this->resumed = false;
        $this->new = false;
        $this->sId = $this->_generateSessionID();

        //$sesionSavePath = 'sessions';
        //if(Util::isDirectory($sesionSavePath, true)){
            //session_save_path(ROOT_DIR.'/'.$sesionSavePath);
        //}
    }
    /**
     * Returns the user who is logged in.
     * @return User|null an object of type User. If the session is not started, 
     * the method will return null.
     * @since 1.0
     */
    public function getUser() {
        $retVal = null;
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive && isset($_SESSION[self::$SV][self::MAIN_VARS[6]])) {
            $retVal = $_SESSION[self::$SV][self::MAIN_VARS[6]];
        }

        return $retVal;
    }
    /**
     * Returns a JSON string that represents the session.
     * @return string
     * @since 1.0
     */
    public function __toString() {
        return $this->toJSON().'';
    }
    /**
     * Generate a random session name.
     * @return string A random session name in the formate 'session-xxxxxxxx'.
     * @since 1.8
     */
    public static function _generateRandSessionName() {
        $retVal = 'session-';

        for ($x = 0 ; $x < 8 ; $x++) {
            $hash = hash('sha256', self::$randFunc(0, 100).$retVal);
            $retVal .= $hash[$x + self::$randFunc(0, 40)];
        }

        return $retVal;
    }
    /**
     * Returns the ID of the session.
     * @return string The ID of the session. If the session is not active, 
     * the method will return -1.
     * @since 1.5
     */
    public function getID() {
        $retVal = -1;
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive) {
            $retVal = session_id();
        }

        return $retVal;
    }
    /**
     * Returns session language code.
     * @return string|null two digit language code (such as 'EN'). If the session 
     * is not running or the language is not set, the method will return null.
     * @param boolean $forceUpdate Set to true if the language is set and want to 
     * reset it. The reset process depends on the attribute 
     * 'lang'. It can be send via 'get' request, 'post' request or a cookie. If 
     * no language code is provided and the parameter '$forceUpdate' is set 
     * to true, 'EN' will be used. The provided language 
     * must be in the array 'SessionManager::SUPOORTED_LANGS'. If the given 
     * language code is not in the given array and the parameter '$forceUpdate' is set 
     * to true, 'EN' will be used.
     */
    public function getLang($forceUpdate = false) {
        $retVal = null;
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive) {
            if ($forceUpdate === true) {
                $this->_initLang($forceUpdate);
                $retVal = $_SESSION[self::$SV][self::MAIN_VARS[7]];
            }

            if (isset($_SESSION[self::$SV][self::MAIN_VARS[7]])) {
                $retVal = $_SESSION[self::$SV][self::MAIN_VARS[7]];
            }
        }

        return $retVal;
    }
    /**
     * Returns the lifetime of the session (in minutes). 
     * @return int the lifetime of the session (in minutes). If the 
     * session is new and the time was not set, the method will return -1.
     * @since 1.4
     */
    public function getLifetime() {
        $retVal = $this->lifeTime;

        if (session_status() == PHP_SESSION_ACTIVE && $this->_switchToSession() 
                && isset($_SESSION[self::$SV][self::MAIN_VARS[0]])) {
                $retVal = $_SESSION[self::$SV][self::MAIN_VARS[0]] / 60;
        }

        return $retVal;
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
     * Returns the number of seconds that has been passed since session started.
     * @return int The number of seconds that has been passed since session started. If no 
     * session is active, the method will return 0. 
     * If sessions are disabled, the method will return 0.
     * @since 1.5
     */
    public function getPassedTime() {
        $retVal = 0;
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive) {
            $retVal = time() - $_SESSION[self::$SV][self::MAIN_VARS[1]];
        }

        return $retVal;
    }
    /**
     * Returns the remaining time till the session dies (in seconds).
     * @return int The remaining time till the session dies (in seconds).
     * @since 1.5
     * 
     */
    public function getRemainingTime() {
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive && $this->isRefresh()) {
            return $this->getLifetime() * 60;
        } else {
            $lifetime = $this->getLifetime();

            if ($lifetime == -1) {
                $lifetime = self::DEFAULT_SESSION_DURATION;
            }

            return $lifetime * 60 - $this->getPassedTime();
        }
    }
    /**
     * Returns the time at which the session was resumed in (in seconds).
     * @return int The time at which the session was resumed in. If the session 
     * is new, this value will be the same as the session start time. If no 
     * session is active, the method will return the constant 'PHP_SESSION_NONE'. 
     * If sessions are disabled, the method will return the constant 'PHP_SESSION_DISABLED'.
     * @since 1.5
     */
    public function getResumTime() {
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive) {
            return $_SESSION[self::$SV][self::MAIN_VARS[2]];
        }

        return session_status();
    }
    /**
     * Returns the ID of a session from a cookie given its name.
     * @param string $sessionName The name of the session.
     * @return boolean|string If the ID is found, the method will return it. 
     * If the session cookie was not found, the method will return false.
     * @since 1.6
     */
    public static function getSessionIDFromCookie($sessionName) {
        $sid = filter_input(INPUT_COOKIE, $sessionName);

        if ($sid !== null && $sid !== false) {
            return $sid;
        }

        return false;
    }
    /**
     * Return session ID from session cookie, get or post parameter.
     * @return string|boolean If session ID is found, the method will 
     * return it. Note that if it is in a cookie, the name of the cookie must 
     * be the name of the session in order to take the ID from it. If it is 
     * in GET or POST request, it must be in a parameter with the name 
     * 'session-id'.
     * @since 1.8.3
     */
    public function getSessionIDFromRequest() {
        $sid = self::getSessionIDFromCookie($this->getName());

        if ($sid === false) {
            $sid = filter_var($_POST['session-id'],FILTER_SANITIZE_STRING);

            if ($sid === null || $sid === false) {
                $sid = filter_var($_GET[self::MAIN_VARS[7]],FILTER_SANITIZE_STRING);
            }
        }

        return $sid;
    }
    /**
     * Returns the status of the session that the manager is currently manages.
     * @return string The status of the session that the manager is currently manages.
     * @since 1.5
     */
    public function getSessionStatus() {
        return $this->sessionStatus;
    }
    /**
     * Returns the value of a session variable given its name.
     * @param string $varName The name of the variable.
     * @return mixed If the session is active and the value of the given variable 
     * is set, its value will be returned. If the session is not active or 
     * the variable is not set, the method will return null.
     * @since 1.8.5
     */
    public function getSessionVar($varName) {
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive) {
            return isset($_SESSION[$varName]) ? $_SESSION[$varName] : null;
        }

        return null;
    }
    /**
     * Returns an associative array that contains all session variables. 
     * The values in array are the ones which is used to manage the session. 
     * The indices of the array are:
     * <ul>
     * <li><b>session-name</b>: The name of the session.</li>
     * <li><b>started-at</b>: The timestamp at which the session was started.</li>
     * <li><b>resumed-at</b>: The timestamp at which the session was resumed at.</li>
     * <li><b>ip-address</b>: The IP address at which the request was coming from.</li>
     * <li><b>refresh</b>: A boolean variable. If set to true, it means the session 
     * timeout-after time will be reset to session duration for every request.</li>
     * <li><b>never-expier</b>: A boolean variable. If set to true, it means the session 
     * will expire after SessionManager::MAX_LIFETIME.</li>
     * <li><b>lang</b>: A two characters string that represents the language 
     * of the session.</li>
     * <li><b>user</b>: An object of type User that represents the user of the 
     * current session.</li>
     * </ul>
     * If the session is not active, the array will be empty.
     * @return array An associative array that contains the variables which are 
     * used to manage the session.
     * @since 1.8.5
     */
    public function getSessionVars() {
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive) {
            return $_SESSION[self::$SV];
        }

        return [];
    }
    /**
     * Returns the array $_SESSTION of the session at which the session manager is managing.
     * @return array|null If the session is active, the array $_SESSION is returned. 
     * If the session is not active, the method will return null.
     * @since 1.8.5
     */
    public function getSesstionArray() {
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive) {
            return $_SESSION;
        }

        return null;
    }
    /**
     * Returns the IP address at which the session was started running from.
     * @return string The IP address at which the session was started running from. If no 
     * session is active, the method will return the constant 'PHP_SESSION_NONE'. 
     * If sessions are disabled, the method will return the constant 'PHP_SESSION_DISABLED'.
     * @since 1.7
     */
    public function getStartIpAddress() {
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive) {
            return $_SESSION[self::$SV][self::MAIN_VARS[5]];
        }

        return session_status();
    }
    /**
     * Returns the time at which the session was started in (in seconds).
     * @return int The time at which the session was started in. If no 
     * session is active, the method will return the constant 'PHP_SESSION_NONE'. 
     * If sessions are disabled, the method will return the constant 'PHP_SESSION_DISABLED'.
     * @since 1.5
     */
    public function getStartTime() {
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive) {
            return $_SESSION[self::$SV][self::MAIN_VARS[1]];
        }

        return session_status();
    }
    /**
     * Checks if the given session name has a cookie or not.
     * @return boolean true if a cookie with the name of 
     * the session is fount. false otherwise.
     * @since 1.5
     */
    public function hasCookie() {
        $retVal = false;
        $sid = filter_input(INPUT_COOKIE, $this->getName());

        if ($sid !== null && $sid !== false) {
            $retVal = true;
        }

        return $retVal;
    }
    /**
     * Initialize the session.
     * @since 1.0
     * @param boolean $refresh If set to true, The due time of the session will 
     * be refreshed in every request if the session is not timed out. Default is false. 
     * @param boolean $useDefaultLang If the session is new and 
     * there was no language parameter was found in the request and this parameter 
     * is set to true, default language will be used (EN). 
     * @return boolean true if the initialization was successful. false 
     * in case of error.
     */
    public function initSession($refresh = false,$useDefaultLang = true) {
        $retVal = false;

        if (!$this->_switchToSession()) {
            if (!$this->resume()) {
                $mlifeTime = $this->getLifetime();

                if ($mlifeTime == -1) {
                    //set default time to two hours.
                    $this->setLifetime(self::DEFAULT_SESSION_DURATION);
                    $mlifeTime = self::DEFAULT_SESSION_DURATION * 60;
                } 
                $retVal = $this->_start($refresh, $mlifeTime,$useDefaultLang);
            } else {
                $retVal = true;
            }
        } else {
            $this->setIsRefresh($refresh);
            $this->_initLang(false, $useDefaultLang);
            $retVal = true;
        }

        return $retVal;
    }
    /**
     * Checks if the session is resumed or not.
     * @return boolean true if the session is new. If the session is 
     * not new, or not running, the method will return false.
     * @since 1.8
     */
    public function isNew() {
        return $this->new;
    }
    /**
     * Checks if session timeout time will be refreshed with every request or not. 
     * This method must be called only after calling the method 'SessionManager::initSession()'. 
     * or it will throw an exception.
     * @return boolean true If session timeout time will be refreshed with every request. 
     * false if not.
     * @throws SessionException If the session is not running. 
     * @since 1.5
     */
    public function isRefresh() {
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive && isset($_SESSION[self::$SV][self::MAIN_VARS[3]])) {
            return $_SESSION[self::$SV][self::MAIN_VARS[3]];
        }
        throw new SessionException('Session is not running.');
    }
    /**
     * Checks if the session is resumed or not.
     * @return boolean true if the session is resumed. If the session is 
     * not resumed, or not running, the method will return false.
     * @since 1.5
     */
    public function isResumed() {
        return $this->resumed;
    }
    /**
     * Checks if the current session instance is the active one or not.
     * @return boolean If session is running and the stored session name is the same 
     * as the session name in the instance, the method will return true. Other than that, 
     * the method will return false.
     * @since 1.8
     */
    public function isSessionActive() {
        $retVal = false;

        if (session_status() == PHP_SESSION_ACTIVE) {
            $retVal = isset($_SESSION[self::$SV][self::MAIN_VARS[4]]) && $_SESSION[self::$SV][self::MAIN_VARS[4]] == $this->getName();
        }

        return $retVal;
    }
    /**
     * Checks if the session has timed out or not.
     * @return boolean|int true if the session has timed out. false if not. If no 
     * session is active, the method will return the constant 'PHP_SESSION_NONE'. 
     * If sessions are disabled, the method will return the constant 'PHP_SESSION_DISABLED'.
     * @since 1.5
     */
    public function isTimeout() {
        $retVal = false;
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive) {
            $remTime = $this->getRemainingTime();
            $retVal = $remTime < 0;
        } else {
            $retVal = session_status();
        }

        return $retVal;
    }
    /**
     * Stops the session and delete all stored session variables.
     * @return boolean true if the session stopped. false if not.
     * @since 1.0
     */
    public function kill() {
        $retVal = false;
        $isActive = session_status() == PHP_SESSION_ACTIVE;

        if ($isActive) {
            if ($this->_validateAttrs()) {
                
                if ($_SESSION[self::$SV][self::MAIN_VARS[4]] == $this->getName()) {
                    $this->_kill();
                    $retVal = true;
                } else {
                    session_write_close();
                    session_name($this->getName());
                    session_start();

                    if ($this->_validateAttrs()) {
                        $this->_kill();
                        $retVal = true;
                    } else {
                        $this->_invAttrKill();
                        $retVal = true;
                    }
                }
            } else {
                $this->_kill();
                $retVal = true;
            }
        } else {
            session_name($this->getName());
            session_start();

            if ($this->_validateAttrs()) {
                $this->_kill();
                $retVal = true;
            } else {
                $this->_invAttrKill();
                $retVal = true;
            }
        }

        return $retVal;
    }
    /**
     * Checks if there exist a session with the given session name or not. If there 
     * is a one and it is not timed out, the method will resume it.
     * @return boolean true if there is a session with the given name 
     * and it is resumed. false= otherwise. If the session is timed out, the 
     * method will kill it.
     * @since 1.0
     */
    public function resume() {
        $retVal = false;

        if ($this->hasCookie()) {
            session_name($this->getName());
            ini_set('session.use_cookies', 1);
            $sid = $this->getSessionIDFromRequest();

            if ($sid !== false) {
                $tmpId = $sid;
                session_id($tmpId);
            } else {
                session_id($this->sId);
            }
            //get time before resuming to check if updated.
            //if not updated, this value will be -1.
            $sessionTime = $this->getLifetime();
            session_start();

            if ($this->_validateAttrs()) {
                if (!$this->isTimeout()) {
                    $ip = filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP);

                    if ($ip == '::1') {
                        $ip = '127.0.0.1';
                    }

                    if ($this->getStartIpAddress() == $ip) {
                        $this->sId = $tmpId;
                        $this->resumed = true;
                        $this->sessionStatus = self::RESUMED;
                        //update resume time
                        $_SESSION[self::$SV][self::MAIN_VARS[2]] = time();
                        $_SESSION[self::$SV][self::MAIN_VARS[4]] = $this->getName();

                        //if time is -1, then get stored one.
                        //else, update session time.
                        $sessionTime = $sessionTime == -1 ? $this->getLifetime() * 60 : $sessionTime * 60;

                        $_SESSION[self::$SV][self::MAIN_VARS[0]] = $sessionTime;
                        $this->resumed = true;
                        $this->new = false;

                        if ($this->isRefresh()) {
                            //refresh time till session cookie is dead
                            $params = session_get_cookie_params();
                            setcookie($this->getName(), $this->getID(),time() + $sessionTime, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
                            $this->setUser($this->getUser());
                        }
                        $retVal = true;
                    } else {
                        $this->kill();
                        $this->sessionStatus = self::INV_IP_ADDRESS;
                    }
                } else {
                    $this->kill();
                    $this->sessionStatus = self::EXPIRED;
                }
            } else {
                $this->kill();
                $this->sessionStatus = self::INV_STATE;
            }
        } else {
            $this->kill();
            $this->sessionStatus = self::INV_COOKIE;
        }

        return $retVal;
    }
    /**
     * Sets if the session timeout will be refreshed with every request 
     * or not.
     * @param boolean $bool If set to true, timeout time will be refreshed. 
     * Note that the property will be updated only if the session is running.
     * @since 1.5
     */
    public function setIsRefresh($bool) {
        if ($this->_switchToSession()) {
            $_SESSION[self::$SV][self::MAIN_VARS[3]] = $bool === true ? true : false;
        }
    }
    private function _setLifetimeHelper($time) {
        $this->lifeTime = $time;
        $_SESSION[self::$SV][self::MAIN_VARS[0]] = $time * 60;

        $params = session_get_cookie_params();
        $secure = isset($params['secure']) ? $params['secure'] : false;
        $httponly = isset($params['httponly']) ? $params['httponly'] : false;
        $path = isset($params['path']) ? $params['path'] : '/';
        session_set_cookie_params(time() + $this->getLifetime() * 60, $path, $params['domain'], $secure, $httponly);

        $retVal = true;

        if ($this->isTimeout()) {
            $this->kill();
        }
        return $retVal;
    }
    /**
     * Sets the lifetime of the session.
     * @param int $time Session lifetime (in minutes). it will be set only if 
     * the given value is greater than 0 and less than SessionManager::MAX_LIFETIME. 
     * If the given value is greater than SessionManager::MAX_LIFETIME, the 
     * value of the constant is used.
     * @return boolean true if time is updated. false otherwise.
     * @since 1.4
     */
    public function setLifetime($time) {
        $retVal = false;

        if ($time > self::MAX_LIFETIME) {
            $time = self::MAX_LIFETIME;
        }

        if ($time > 0 && ($this->isSessionActive() || $this->_switchToSession())) {
            $retVal = $this->_setLifetimeHelper($time);
        } else {
            $this->lifeTime = $time;
        }

        return $retVal;
    }
    /**
     * Adds new variable to the array $_SESSION of the current instance.
     * Note that the variable will be set only if the session is active.
     * @param string $name The name of the index at which the variable will 
     * be stored in.
     * @param mixed $value The value of the variable.
     * @return boolean If the variable is set, the method will return true. 
     * If not set, it will return false.
     * @since 1.8.5
     */
    public function setSessionVar($name,$value) {
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive) {
            $_SESSION[$name] = $value;

            return true;
        }

        return false;
    }
    /**
     * Sets the user who is using the system. It is used in case of log in.
     * @param User $user an object of type User.
     * @return boolean true in case the user is set. false if not.
     * @since 1.0
     */
    public function setUser($user) {
        $retVal = false;
        $isActive = $this->isSessionActive() === true ? true : $this->_switchToSession();

        if ($isActive) {
            if ($user instanceof User) {
                $_SESSION[self::$SV][self::MAIN_VARS[6]] = $user;
                $retVal = true;
            }
        }

        return $retVal;
    }
    /**
     * Returns a 'JsonX' object that represents the manager.
     * @return JsonX
     * @since 1.5
     */
    public function toJSON() {
        $j = new JsonX();
        $j->add('name', $this->getName());
        $lifetime = $this->getLifetime();

        if ($lifetime == -1) {
            $lifetime = self::DEFAULT_SESSION_DURATION;
        }
        $j->add('duration', $lifetime * 60);
        $j->add('has-cookie', $this->hasCookie());
        $j->add('session-id', $this->getID());
        $j->add('language', $this->getLang());
        try {
            $j->add(self::MAIN_VARS[3], $this->isRefresh());
        } catch (Exception $ex) {
        }
        $j->add('passed-time', $this->getPassedTime());
        $j->add('timeout-after', $this->getRemainingTime());
        $stTm = $this->getStartTime();

        if ($stTm != PHP_SESSION_NONE && $stTm != PHP_SESSION_ACTIVE && $stTm != PHP_SESSION_DISABLED) {
            $j->add(self::MAIN_VARS[1], date('Y-m-d H:i:s',$this->getStartTime()));
        }
        $rsTm = $this->getStartTime();

        if ($rsTm != PHP_SESSION_NONE && $rsTm != PHP_SESSION_ACTIVE && $rsTm != PHP_SESSION_DISABLED) {
            $j->add(self::MAIN_VARS[2], date('Y-m-d H:i:s',$this->getResumTime()));
        }
        $j->add('status', $this->sessionStatus);
        $j->add(self::MAIN_VARS[6], $this->getUser());

        return $j;
    }
    /**
     * Generate a random session ID.
     * @return string A new random session ID.
     * @since 1.6
     */
    private function _generateSessionID() {
        $date = date(DATE_ISO8601);
        $hash = hash('sha256', $date);
        $time = time() + self::$randFunc(0, 1000);
        $hash2 = hash('sha256',$hash.$time);
        
        return substr($hash2, 0, 27);
    }
    /**
     * Initialize session language. The initialization depends on the attribute 
     * 'lang'. It can be send via 'get' request, 'post' request or a cookie. 
     * The provided language must be in the array 'SessionManager::SUPOORTED_LANGS'. 
     * If the given language code is not in the given array, 
     * The used value will depend on the existence of the class 'SiteConfig'. 
     * If it is exist, The value that is returned by SiteConfig::getPrimaryLanguage()' .
     * If not, 'EN' is used by default.
     * Also if the language is set before, it will not be updated unless the parameter '$forceUpdate' is set to true.
     * @param boolean $forceUpdate Set to true if the language is set and want to 
     * reset it.
     * @param boolean $useDefault If set to true, the method will 
     * use default language if no language attribute is found in request body.
     * @return boolean The method will return true if the language is set or 
     * updated. Other than that, the method will return false.
     * @since 1.2
     */
    private function _initLang($forceUpdate = false,$useDefault = true) {
        if (isset($_SESSION[self::$SV][self::MAIN_VARS[7]]) && !$forceUpdate) {
            return false;
        }
        //the value of default language.
        //used in case no language found 
        //in $_GET['lang']
        $defaultLang = class_exists('webfiori\conf\SiteConfig') ? SiteConfig::getPrimaryLanguage() : 'EN';
        $lang = null;

        if (isset($_GET[self::MAIN_VARS[7]])) {
            $lang = filter_var($_GET[self::MAIN_VARS[7]],FILTER_SANITIZE_STRING);
        }

        if ($lang == false || $lang == null) {
            if (isset($_POST[self::MAIN_VARS[7]])) {
                $lang = filter_var($_POST[self::MAIN_VARS[7]],FILTER_SANITIZE_STRING);
            }

            if ($lang == false || $lang == null) {
                $lang = filter_input(INPUT_COOKIE, self::MAIN_VARS[7]);

                if ($lang == false || $lang == null) {
                    $lang = null;
                }
            }
        }
        $retVal = false;

        if (isset($_SESSION[self::$SV][self::MAIN_VARS[7]]) && $lang == null) {
            $retVal = false;
        } else {
            if ($lang == null && $useDefault === true) {
                $lang = $defaultLang;
            } else {
                if ($lang == null && $useDefault !== true) {
                    $retVal = false;
                }
            }
        }
        $langU = strtoupper($lang);

        if (strlen($langU) == 2) {
            $_SESSION[self::$SV][self::MAIN_VARS[7]] = $langU;
            $retVal = true;
        }

        if ($useDefault && !$retVal && !isset($_SESSION[self::$SV][self::MAIN_VARS[7]])) {
            $_SESSION[self::$SV][self::MAIN_VARS[7]] = $defaultLang;
            $retVal = true;
        } else {
            $retVal = false;
        }
    }
    /**
     * @since 1.8.1
     */
    private function _invAttrKill() {
        $this->_kill();
    }
    /**
     * @since 1.8.1
     */
    private function _kill() {
        $params = session_get_cookie_params();
        $secure = isset($params['secure']) ? $params['secure'] : false;
        $httponly = isset($params['httponly']) ? $params['httponly'] : false;
        $path = isset($params['path']) ? $params['path'] : '/';
        session_destroy();
        session_set_cookie_params(0, $path, $params['domain'], $secure, $httponly);
        $this->sessionStatus = self::KILLED;
    }
    /**
     * The core method of the class. 
     * @param type $refresh
     * @param type $lifeTime
     * @param type $useDefaultLang
     * @return type
     * @since 1.0
     */
    private function _start($refresh,$lifeTime,$useDefaultLang = false) {
        $this->sessionStatus = self::NEW_SESSION;
        ini_set('session.gc_maxlifetime', $lifeTime);
        ini_set('session.cookie_lifetime', $lifeTime);
        ini_set('session.use_cookies', 1);
        session_name($this->getName());
        session_id($this->sId);
        session_set_cookie_params($lifeTime,"/");
        $started = session_start();

        if ($started) {
            $this->resumed = false;
            $this->new = true;
            $_SESSION[self::$SV] = [];
            $_SESSION[self::$SV][self::MAIN_VARS[4]] = $this->getName();
            $_SESSION[self::$SV][self::MAIN_VARS[1]] = time();
            $_SESSION[self::$SV][self::MAIN_VARS[2]] = time();
            $_SESSION[self::$SV][self::MAIN_VARS[0]] = $lifeTime;
            $_SESSION[self::$SV][self::MAIN_VARS[6]] = new User();
            $ip = filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP);

            if ($ip == '::1') {
                $ip = '127.0.0.1';
            }
            $_SESSION[self::$SV][self::MAIN_VARS[5]] = $ip;
            $_SESSION[self::$SV][self::MAIN_VARS[3]] = $refresh === true ? true : false;
            $this->_initLang(true,$useDefaultLang);
        }

        return $started;
    }
    /**
     * Switch between sessions. 
     * The method first checks if a session is active. 
     * If a session is active, the method checks if the name that is stored 
     * in the instance is equal to the name stored in the $_SESSION. if the 
     * two are different, the method will stop the first session and activate 
     * the second one.
     * @return boolean If the session was switched, the method will return true.
     * @since 1.8
     */
    private function _switchToSession() {
        $retVal = false;

        if (session_status() == PHP_SESSION_ACTIVE) {
            if ($this->_validateAttrs() === true) {
                $sName = $_SESSION[self::$SV][self::MAIN_VARS[4]];
                $iName = $this->getName();

                if ($sName == $iName) {
                    $retVal = true;
                } else {
                    session_write_close();
                    session_name($iName);
                    session_id($this->sId);
                    session_start();

                    if ($this->_validateAttrs() === true) {
                        $retVal = true;
                    } else {
                        session_write_close();
                    }
                }
            } else {
                $this->kill();
            }
        } else {
            if (strlen($this->sId) != 0) {
                session_name($this->getName());
                session_id($this->sId);
                session_start();

                if ($this->_validateAttrs() === true) {
                    $retVal = true;
                } else {
                    session_write_close();
                }
            }
        }

        return $retVal;
    }
    /**
     * Validate session variables. Must be called after session is started.
     * @return boolean  If the variables 'started-at', 'resumed-at', 'lifetime', 
     * 'refresh', 'session-name' and 'ip-address' are 
     * set, The method will return true. Other than that, it will return 
     * false.
     */
    private function _validateAttrs() {
        $retVal = false;

        if (isset($_SESSION[self::$SV][self::MAIN_VARS[1]]) 
         && isset($_SESSION[self::$SV][self::MAIN_VARS[2]])
         && isset($_SESSION[self::$SV][self::MAIN_VARS[0]])
         && isset($_SESSION[self::$SV][self::MAIN_VARS[3]])
         && isset($_SESSION[self::$SV][self::MAIN_VARS[5]])
         && isset($_SESSION[self::$SV][self::MAIN_VARS[4]])) {
            $retVal = true;
        }

        return $retVal;
    }
    /**
     * Validate the name of the session.
     * @param string $name The name of the session. The following characters are 
     * invalid in session name: space, comma, semi-colon and equal sign.
     * @return boolean The method will return true if the name of the session 
     * is valid.
     * @since 1.8
     */
    private function _validateName($name) {
        $len = strlen($name);
        $retVal = true;

        if ($len != 0) {
            for ($x = 0 ; $x < $len ; $x++) {
                $char = $name[$x];

                if ($char == ' ' || $char == ',' || $char == ';' || $char == '=') {
                    $retVal = false;
                    break;
                }
            }
        }

        return $retVal;
    }
}
