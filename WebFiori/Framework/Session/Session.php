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
namespace WebFiori\Framework\Session;

use WebFiori\Framework\Exceptions\SessionException;
use WebFiori\Http\HttpCookie;
use WebFiori\Http\Request;
use WebFiori\Framework\App;
use WebFiori\Json\Json;
use WebFiori\Json\JsonI;
/**
 * A class that represents a session.
 *
 * @author Ibrahim
 *
 * @since 1.1.0
 *
 */
class Session implements JsonI {
    /**
     * The default lifetime for any new session (in minutes).
     *
     *
     */
    const DEFAULT_SESSION_DURATION = 120;
    /**
     * The IP address of the user who is using the session.
     *
     * @var string
     *
     *
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
     */
    private $langCode;
    /**
     * The lifetime of the session (in minutes).
     *
     * @var int lifetime of the session (in minutes). The default is 10.
     *
     */
    private $lifeTime;
    /**
     * Number of seconds passed since the session was started.
     *
     * @var int
     *
     */
    private $passedTime;
    /**
     * The name of random function which is used in session ID generation.
     *
     * @var string
     *
     */
    private static $randFunc;
    /**
     * The timestamp at which the session was resumed at as Unix timestamp.
     *
     * @var int
     *
     */
    private $resumedAt;
    private $sessionCookie;
    private $sessionStatus;
    /**
     * An object of type 'User' that represents session user.
     *
     * @var SessionUser
     *
     */
    private $sessionUser;
    /**
     * An array that holds session variables.
     *
     * @var array
     *
     */
    private $sessionVariables;

    /**
     * The timestamp at which the session was started in as Unix timestamp.
     *
     * @var int
     *
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
     */
    public function __construct(array $options = []) {
        //used to support older PHP versions which does not have 'random_int'.
        self::$randFunc = is_callable('random_int') ? 'random_int' : 'rand';
        $this->sessionCookie = new HttpCookie();
        $this->sessionUser = null;

        $this->sessionStatus = SessionStatus::INACTIVE;
        $this->passedTime = 0;
        $this->langCode = '';


        if (isset($options['refresh'])) {
            $this->setIsRefresh($options['refresh']);
        } else {
            $this->setIsRefresh(false);
        }

        if (!(isset($options[SessionOption::DURATION]) && $this->setDuration($options[SessionOption::DURATION]))) {
            $this->setDuration(self::DEFAULT_SESSION_DURATION);
        }

        if ($this->getDuration() == 0) {
            $this->setIsRefresh(false);
        }
        $tempSName = isset($options[SessionOption::NAME]) ? trim($options[SessionOption::NAME]) : '';

        if (!$this->setNameHelper($tempSName)) {
            throw new SessionException('Invalid session name: \''.$tempSName.'\'.');
        }

        $this->getCookie()->setValue(isset($options[SessionOption::SESSION_ID]) ? trim($options[SessionOption::SESSION_ID]) : self::generateSessionID($tempSName));
        $this->resumedAt = 0;
        $this->startedAt = 0;
        $this->sessionVariables = [];
        $this->passedTime = 0;
        $this->ipAddr = App::getRequest()->getClientIP();
        $this->getCookie()->setSameSite('Lax');
        App::getRequest()->getUri()->getScheme();

        if ((defined('USE_HTTP') && USE_HTTP === true) || App::getRequest()->getUri()->getScheme() == 'http') {
            $this->getCookie()->setIsSecure(false);
        } else {
            $this->getCookie()->setIsSecure(true);
        }
        $this->getCookie()->setIsHttpOnly(true);
    }
    /**
     * Returns a JSON string that represents the session.
     *
     * @return string
     *
     */
    public function __toString() {
        return $this->toJSON().'';
    }
    /**
     * Store session state and pause the session.
     *
     * Note that session state will be stored only if it is running.
     *
     */
    public function close() {
        if ($this->isRunning()) {
            SessionsManager::getStorage()->save($this->getId(), $this->serialize());
            $this->sessionStatus = SessionStatus::PAUSED;
            SessionsManager::pauseAll();
        }
    }

    /**
     * Deserialize a session and restore its data in the instance at which the
     * method is called on.
     *
     * @param string $serialized The serialized session as string.
     *
     * @return bool If the Un-serialize was successfully completed, the method
     * will return true. If Deserialize fails, the method will return false.
     *
     * @throws SessionException
     */
    public function deserialize(string $serialized): bool {
        $cipherMeth = 'aes-256-ctr';
        $split = explode('_', $serialized);
        $len = $split[0];
        $serialized = $split[1];
        // [Decrypt] => decode => deserialize

        if (in_array($cipherMeth, openssl_get_cipher_methods())) {
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'Other';

            //Shall we use IP address in key or not?
            //It would add more security. But the session will be invalid
            //If user changes network.
            $key = $this->getId().$userAgent;

            $iv = substr(hash('sha256', $key), 0,16);
            $decrypted = substr(openssl_decrypt(substr($serialized, 0, $len), $cipherMeth, $key,0, $iv), 0, $len);

            if (strlen($decrypted) > 0) {
                set_error_handler(function ($errNo, $errStr, $errFile, $errLine)
                {
                    throw  new SessionException($errStr.' at line '.$errLine, $errNo);
                });
                try {
                    $sessionObj = unserialize(base64_decode(trim($decrypted)));
                    restore_error_handler();
                } catch (SessionException $ex) {
                    restore_error_handler();
                    return false;
                }
                

                if ($sessionObj instanceof Session) {
                    $this->sessionStatus = SessionStatus::RESUMED;
                    $this->cloneHelper($sessionObj);

                    return true;
                }
            }
        } else {
            set_error_handler(function ($errNo, $errStr)
            {
                throw  new SessionException($errStr, $errNo);
            });
            try {
                $sessionObj = unserialize(base64_decode($serialized));
                restore_error_handler();
            } catch (SessionException $ex) {
                restore_error_handler();
                return false;
            }

            if ($sessionObj instanceof Session) {
                $this->sessionStatus = SessionStatus::RESUMED;
                $this->cloneHelper($sessionObj);

                return true;
            }
        }

        return false;
    }
    /**
     * Generate a random session ID.
     *
     * @param string|null $sessionName The name of the session.
     *
     * @return string A new random session ID.
     *
     */
    public static function generateSessionID(?string $sessionName = null): string {
        $date = date('Y-m-d\TH:i:sO');
        $hash = hash('sha256', $date);
        $salt = time() + call_user_func(self::$randFunc, 0, 100);

        return hash('sha256',$hash.$salt.$sessionName);
    }
    /**
     * Returns the value of a session variable.
     *
     * @param string $varName The name of the variable.
     *
     * @return null|mixed If a variable which has the given name is found, its
     * value is returned. If no such variable exist, the method will return null.
     *
     */
    public function get(string $varName) {
        if ($this->isRunning()) {
            $trimmed = trim($varName);

            if (isset($this->sessionVariables[$trimmed])) {
                return $this->sessionVariables[$trimmed];
            }
        }
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
     * Returns a string which can be passed to the function 'header()' to set session
     * cookie.
     *
     * @return string The string that will be returned will have the following
     * format:
     * '&lt;cookie-name&gt;=&lt;val&gt;; expires=&lt;time&gt;; path=/
     * SameSite=&lt;Lax|None|Strict&gt;'
     *
     */
    public function getCookieHeader() : string {
        return $this->sessionCookie.'';
    }
    /**
     * Returns the amount of time at which the session will live for in seconds.
     *
     * @return int This method will return session duration in seconds. The
     * default duration of any new session is 120 minutes (7200 seconds).
     *
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
     */
    public function getIp() : string {
        return $this->ipAddr;
    }
    /**
     * Returns session language code.
     *
     * @param bool $forceUpdate Set to true if the language is set and want to
     * reset it. The reset process depends on the attribute
     * 'lang'. It can be sent via 'get' request, 'post' request or a cookie. If
     * no language code is provided and the parameter '$forceUpdate' is set
     * to true, 'EN' will be used. If the given
     * language code is not in the given array and the parameter '$forceUpdate' is set
     * to true, 'EN' will be used.
     *
     * @return string|null two digit language code (such as 'EN'). If the session
     * is not running or the language is not set, the method will return empty string.
     *
     */
    public function getLangCode(bool $forceUpdate = false) {
        $this->initLang($forceUpdate);

        return $this->langCode;
    }
    /**
     * Returns the name of the session.
     *
     * @return string The name of the session as string.
     *
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
     */
    public function getResumedAt() : int {
        if ($this->isRunning()) {
            return $this->resumedAt;
        }

        return 0;
    }
    /**
     * Returns the time at which the session was started at.
     *
     * @return int The method will return the time in seconds. If the session
     * is not running, the method will return 0.
     *
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
     */
    public function getUser() {
        return $this->sessionUser;
    }
    /**
     * Returns an associative array that contains all session variables.
     *
     * @return array An associative array that contains all session variables.
     * The indices will be variables names and the value of each index is the
     * variable value.
     *
     */
    public function getVars() : array {
        return $this->sessionVariables;
    }
    /**
     * Checks if the session has a given value or not.
     *
     * Note that the method will always return false if the session is not running.
     *
     * @param string $varName The name of the variable that has the value.
     *
     * @return bool If the value exist, the method will return true.
     * Other than that, the method will return false.
     *
     */
    public function has(string $varName) : bool {
        if ($this->isRunning()) {
            $trimmed = trim($varName);

            return isset($this->sessionVariables[$trimmed]);
        }

        return false;
    }
    /**
     * Checks if the session cookie is persistent or not.
     *
     * A session is persistent if its duration is greater than 0 minutes (has a
     * duration).
     *
     * @return bool If the session cookie is persistent, the method will return true.
     * false otherwise.
     *
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
     * @return bool true If session timeout time will be refreshed with every request.
     * false if not.
     *
     *
     * @since 1.5
     */
    public function isRefresh() : bool {
        return $this->isRef;
    }
    /**
     * Checks if the session is started and running or not.
     *
     * @return bool If the status of the session is Session::STATUS_NEW or Session::STATUS_RESUMED,
     * the method will return true. Other than that, the method will return false.
     *
     */
    public function isRunning() : bool {
        return $this->getStatus() == SessionStatus::NEW || $this->getStatus() == SessionStatus::RESUMED;
    }


    public function kill() {
        SessionsManager::getStorage()->remove($this->getId());
        $this->sessionStatus = SessionStatus::KILLED;
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
     */
    public function reGenerateID() : string {
        $this->getCookie()->setValue($this->generateSessionID($this->getName()));

        return $this->getCookie()->getValue();
    }
    /**
     * Removes the value of a session variable.
     *
     * @param string $varName The name of the variable.
     *
     * @return bool If the value was deleted, the method will return true.
     * If the variable does not exist or the variable does not exist, the method
     * will return false.
     *
     */
    public function remove(string $varName) : bool {
        if ($this->isRunning()) {
            $trimmed = trim($varName);

            if (isset($this->sessionVariables[$trimmed])) {
                unset($this->sessionVariables[$trimmed]);

                return true;
            }
        }

        return false;
    }
    /**
     * Serialize the session.
     *
     * @return string The method will return a string that represents serialized
     * session data. Note that if openssl is enabled and the cipher aes-256-ctr
     * is supported, returned string will be encrypted.
     *
     */
    public function serialize() : string {
        // Serialize => Encode => [Encrypt]
        $serializedSession = base64_encode(trim(serialize($this)));
        $len = strlen($serializedSession);

        $cipherMeth = 'aes-256-ctr';

        if (in_array($cipherMeth, openssl_get_cipher_methods())) {
            //Need to do more research about the security of this approach.

            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'Other';
            //Shall we use IP address in key or not?
            //It would add more security.
            $key = $this->getId().$userAgent;

            $iv = substr(hash('sha256', $key), 0,16);
            $serializedSession = openssl_encrypt($serializedSession, $cipherMeth, $key,0, $iv);
            $len = strlen($serializedSession);
        }

        return $len.'_'.$serializedSession;
    }
    /**
     * Sets session variable.
     *
     * Note that session variable will be set only if the session is running.
     *
     * @param string $name The name of the variable. Must be non-empty string.
     *
     * @param mixed $val The value of the variable. It can be anything.
     *
     * @return bool If the variable is set, the method will return true. If
     * not, the method will return false.
     *
     */
    public function set(string $name, $val) : bool {
        if ($this->isRunning()) {
            $trimmed = trim($name);

            if (strlen($trimmed) > 0) {
                $this->sessionVariables[$trimmed] = $val;

                return true;
            }
        }

        return false;
    }
    /**
     * Sets session duration.
     *
     * Note that this method will also update the 'expires' attribute of session
     * cookie. Also, note that if the new duration less than the passed time,
     * the session will expire.
     *
     * @param float $time Session duration in minutes.
     *
     * @return bool If session duration is updated, the method will return true.
     * False otherwise.
     *
     */
    public function setDuration(float $time) : bool {
        $asFloat = $time;

        if ($asFloat >= 0) {
            $this->lifeTime = $asFloat;
            $this->sessionCookie->setExpires($asFloat);
            $this->checkIfExpired();

            return true;
        }

        return false;
    }
    /**
     * Sets if the session timeout will be refreshed with every request
     * or not.
     *
     * @param bool $bool If set to true, timeout time will be refreshed.
     * Note that the property will be updated only if the session is running.
     *
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
     */
    public function setUser(SessionUser $userObj) {
        if ($this->isRunning()) {
            $this->sessionUser = $userObj;
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
     * @throws SessionException
     */
    public function start() {
        if (!$this->isRunning()) {
            $sessionStr = SessionsManager::getStorage()->read($this->getId());

            if ($this->getStatus() == SessionStatus::KILLED || $sessionStr === null || !$this->deserialize($sessionStr)) {
                $this->reGenerateID();
                $this->initNewSessionVars();
            } else {
                $this->checkIfExpired();
            }
        }
    }
    /**
     * Returns an object of type 'Json' that represents the session.
     *
     * @return Json
     *
     */
    public function toJSON() : Json {
        $json = new Json([
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
        ]);
        $json->addArray('vars', $this->getVars(), true);

        return $json;
    }
    private function checkIfExpired() {
        if ($this->getRemainingTime() < 0) {
            SessionsManager::getStorage()->remove($this->getId());
            $this->sessionStatus = SessionStatus::EXPIRED;
            $this->sessionCookie->kill();
        } else if ($this->isRefresh()) {
            $this->sessionCookie->setExpires($this->getDuration());
        }
    }
    private function cloneHelper(Session $session) {
        $this->startedAt = $session->startedAt;
        $this->sessionCookie = $session->sessionCookie;
        $this->sessionVariables = $session->sessionVariables;
        $this->isRef = $session->isRef;
        $this->resumedAt = time();
        $this->lifeTime = $session->lifeTime;
        $this->sessionUser = $session->sessionUser;

        $langCodeR = $this->getLangFromRequest();

        if ($langCodeR) {
            $this->langCode = $this->getLangCode(true);
        } else {
            $this->langCode = $session->langCode;
        }
        $this->passedTime = $this->getResumedAt() - $this->getStartedAt();
    }
    /**
     *
     * @return string|null
     */
    private function getLangFromRequest() {
        $langIdx = 'lang';
        $lang = App::getRequest()->getParam($langIdx);

        if ($lang === null) {
            $lang = filter_input(INPUT_COOKIE, $langIdx);

            if ($lang === null) {
                $lang = null;
            }
        }

        return $lang;
    }
    /**
     * Initialize session language.
     *
     * The initialization depends on the attribute 'lang'.
     * It can be sent via 'get' request, 'post' request or a cookie.
     * If the given language code is not in the given array,
     * The used value will depend on the existence of the class 'AppConfig'.
     * If it is existed, The value that is returned by AppConfig::getPrimaryLanguage()' .
     * If not, 'EN' is used by default.
     * Also, if the language is set before, it will not be updated unless the
     * parameter '$forceUpdate' is set to true.
     *
     * @param bool $forceUpdate Set to true if the language is set and want to
     * reset it. Default is false.
     *
     * @return void The method will return true if the language is set or
     * updated. Other than that, the method will return false. Default is true which
     * happens when session is not running.
     *
     * @since 1.2
     */
    private function initLang(bool $forceUpdate = false) {
        if ($this->isRunning()) {
            if ($this->langCode != '' && !$forceUpdate) {
                return;
            }
            //the value of default language.
            //used in case no language found
            //in $_GET['lang'], $_POST['lang'] or in cookie
            $defaultLang = App::getConfig()->getPrimaryLanguage();
            $langCodeFromReq = $this->getLangFromRequest();
            $isLangSet = false;
            $isNullCode = $langCodeFromReq === null;

            if ($isNullCode) {
                if ($this->langCode == '') {
                    $langCodeFromReq = $defaultLang;
                }
            }

            if ($langCodeFromReq !== null) {
                $langU = strtoupper($langCodeFromReq);

                if (strlen($langU) == 2) {
                    $this->langCode = $langU;
                    $isLangSet = true;
                }

                if (!$isLangSet && $this->langCode == '') {
                    $this->langCode = $defaultLang;
                }
            }
        }
    }
    private function initNewSessionVars() {
        $this->sessionVariables = [];
        $this->resumedAt = time();
        $this->startedAt = time();

        $this->sessionStatus = SessionStatus::NEW;
        $this->initLang();
    }
    private function setNameHelper($name): bool {
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
