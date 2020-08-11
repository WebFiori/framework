<?php
namespace webfiori\entity\sesstion;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use webfiori\entity\User;
use jsonx\JsonX;
use jsonx\JsonI;
use webfiori\conf\SiteConfig;
use Serializable;
/**
 * Description of session
 *
 * @author Ibrahim
 * 
 * @since 1.1.0
 * 
 * @version 1.0
 */
class Session implements JsonI {

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
     * A constant that indicates the session was paused.
     * 
     * @since 1.0
     */
    const STATUS_PAUSED = 'status_paused';
    /**
     * A constant that indicates the session was just created.
     * 
     * @since 1.0
     */
    const STATUS_NEW = 'status_new';
    /**
     * A constant that indicates the session has been resumed.
     * 
     * @since 1.0
     */
    const STATUS_RESUMED = 'status_resumed';
    /**
     * A constant that indicates the session has been killed by calling the 
     * method 'Session::kill()'.
     * 
     * @since 1.0
     */
    const STATUS_KILLED = 'status_killed';
    /**
     * The default lifetime for any new session (in minutes).
     * 
     * @since 1.0
     */
    const DEFAULT_SESSION_DURATION = 120;
    /**
     * The lifetime of the session (in minutes).
     * 
     * @var int lifetime of the session (in minutes). The default is 10.
     * 
     * @since 1.0 
     */
    private $lifeTime;
    /**
     * The name of the index that contains session custom vars.
     * 
     * The variables are set using the method SessionManager::setSessionVar()'.
     * 
     * @var string
     * 
     * @since 1.0
     */
    private static $CSV = 'custom-session-vars';
    /**
     * An associative array that contains session cookie parameters.
     * 
     * @var array
     * 
     * @since 1.0 
     */
    private $cookieParams;
    /**
     * An array that holds session variables.
     * 
     * @var array
     * 
     * @since 1.0 
     */
    private $sessionArr;
    /**
     * An array that contains the names of main session variables.
     * 
     * The array has the following values:
     * <ul>
     * <li>lifetime: Session duration in minutes</li>
     * <li>startedAt: The timestamp at which the session started in (in seconds)</li>
     * <li>resumedAt: The timestamp at which the session resumed in (in seconds)</li>
     * <li>refresh: A boolean. Set to true if session is set to refresh on 
     * every request.</li>
     * <li>sessionName: The name of the session.</li>
     * <li>ipAddress: The IP address at which the request was made from.</li>
     * <li>user: An object of type 'User' that represents session user.</li>
     * <li>lang: The language of the session (such as 'EN' or 'AR').</li>
     * <ul>
     * 
     * @since 1.0
     * 
     * @var array 
     */
    const MAIN_VARS = [
        'lifetime',
        'startedAt',
        'resumedAt',
        'refresh',
        'sessionName',
        'ipAddress',
        'user',
        'lang',
        'status'
    ];
    
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
    public function isRefresh() {
        return $this->isRef;
    }
    private $sesstionUser;
    private $ipAddr;
    private $langCode;
    private $startedAt;
    private $resumedAt;
    private $passedTime;
    private function _initNewSesstionVars() {
        $this->resumedAt = time();
        $this->startedAt = time();
        $this->sesstionUser = new User();
        $this->sessionStatus = self::STATUS_NEW;
        $this->_initLang();
        
    }
    public function getIp() {
        return $this->ipAddr;
    }
    /**
     * Returns the number of seconds that has been passed since the session started.
     * 
     * @return int The number of seconds that has been passed since the session started. 
     * If the session is not running, the method will return 0.
     * 
     * @since 1.0
     */
    public function getPassedTime() {
        return $this->passedTime;
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
     * is not running or the language is not set, the method will return null.
     * 
     * @since 1.0
     */
    public function getLangCode($forceUpdate = false) {
        $this->_initLang($forceUpdate);
        return $this->langCode;
    }
    /**
     * Initialize session language. The initialization depends on the attribute 
     * 'lang'. 
     * 
     * It can be send via 'get' request, 'post' request or a cookie. 
     * The provided language must be in the array 'SessionManager::SUPOORTED_LANGS'. 
     * If the given language code is not in the given array, 
     * The used value will depend on the existence of the class 'SiteConfig'. 
     * If it is exist, The value that is returned by SiteConfig::getPrimaryLanguage()' .
     * If not, 'EN' is used by default.
     * Also if the language is set before, it will not be updated unless the parameter '$forceUpdate' is set to true.
     * 
     * @param boolean $forceUpdate Set to true if the language is set and want to 
     * reset it. Default is false.
     * 
     * @param boolean $useDefault If set to true, the method will 
     * use default language if no language attribute is found in request body.
     * 
     * @return boolean The method will return true if the language is set or 
     * updated. Other than that, the method will return false. Default is true.
     * 
     * @since 1.2
     */
    private function _initLang($forceUpdate = false,$useDefault = true) {
        if ($this->getStatus() == self::STATUS_NEW || $this->getStatus() == self::STATUS_RESUMED) {
            if ($this->langCode !== null && !$forceUpdate) {
                return false;
            }
            //the value of default language.
            //used in case no language found 
            //in $_GET['lang'], $_POST['lang'] or in cookie
            $defaultLang = class_exists('webfiori\conf\SiteConfig') ? SiteConfig::getPrimaryLanguage() : 'EN';
            $langCode = $this->_getLangFromRequest();
            $retVal = false;

            if ($this->langCode !== null && $langCode == null) {
                $retVal = false;
            } else if ($langCode == null && $useDefault === true) {
                $langCode = $defaultLang;
            } else if ($langCode == null && $useDefault !== true) {
                $retVal = false;
            }
            $langU = strtoupper($langCode);

            if (strlen($langU) == 2) {
                $this->langCode = $langU;
                $retVal = true;
            }

            if ($useDefault && !$retVal && $this->langCode === null) {
                $this->langCode = $defaultLang;
                $retVal = true;
            } else {
                $retVal = false;
            }
        }
    }
    /**
     * 
     * @return string|null
     */
    private function _getLangFromRequest() {
        $lang = null;
        $langIdx = 'lang';
        //get language code from $_GET
        if (isset($_GET[$langIdx])) {
            $lang = filter_var($_GET[$langIdx],FILTER_SANITIZE_STRING);
        }

        if (!$lang || $lang == null) {
            //if not in $_GET, check $_POST
            if (isset($_POST[$langIdx])) {
                $lang = filter_var($_POST[$langIdx],FILTER_SANITIZE_STRING);
            }

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
     * Checks if the session is started and running or not.
     * 
     * @return If the status of the session is Session::STATUS_NEW or Session::STATUS_RESUMED, 
     * the method will return true. Other than that, the method will return false.
     * 
     * @since 1.0
     */
    public function isRunning() {
        return $this->getStatus() == self::STATUS_NEW || $this->getStatus() == self::STATUS_RESUMED;
    }
    /**
     * Sets if the session timeout will be refreshed with every request 
     * or not.
     * 
     * @param boolean $bool If set to true, timeout time will be refreshed. 
     * Note that the property will be updated only if the session is running.
     * 
     * @since 1.5
     */
    public function setIsRefresh($bool) {
        $this->isRef = $bool === true;
    }
    public function resume() {
        if ($this->getStatus() == self::STATUS_PAUSED) {
            
        }
    }
    
    public function getCookieParams() {
        return $this->cookieParams;
    }
    
    private $sName;
    
    
    public function kill() {
        SessionsManager::getStorage()->remove($this->getId());
        $this->sessionStatus = self::STATUS_KILLED;
        $this->cookieParams['lifetime'] = time() - 1;
    }
    /**
     * Returns the time at at which the session was started at.
     * 
     * @return int The method will return the time in seconds. If the session 
     * is not running, the method will return 0.
     * 
     * @since 1.0
     */
    public function getStartedAt() {
        return $this->startedAt;
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
    public function getResumedAt() {
        return $this->resumedAt;
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
        $seestion = SessionsManager::getStorage()->read($this->getId());
        if ($seestion instanceof Session) {
            $this->_clone($seestion);
            $this->sessionStatus = self::STATUS_RESUMED;
            $this->_checkIfExpired();
            
        } else {
            $this->reGenerateID();
            $this->_initNewSesstionVars();
        }
    }
    private function _checkIfExpired() {
        if ($this->getDuration() != 0 && $this->getDuration() * 60 < $this->getPassedTime()) {
            $this->kill();
        }
    }
    public function set($name, $val) {
        if ($this->isRunning()) {
            $trimmed = trim($name);
            if (strlen($trimmed) > 0) {
                $this->sessionArr[$trimmed] = $val;
            }
        }
    }
    private function _clone($session) {
        $this->startedAt = $session->startedAt;
        $this->cookieParams = $session->cookieParams;
        $this->sessionArr = $session->sessionArr;
        $this->sName = $session->sName;
        $this->sId = $session->sId;
        $this->isRef = $session->isRef;
        $this->resumedAt = time();
        $this->lifeTime = $session->lifeTime;
        $this->langCode = $session->langCode;
        $this->passedTime = $this->getResumedAt() - $this->getStartedAt();
        $this->sesstionUser = $session->sesstionUser;
    }
    /**
     * Store session variables.
     */
    public function close() {
        SessionsManager::getStorage()->save($this);
        $this->sessionStatus = self::STATUS_PAUSED;
    }
    /**
     * Returns the status of the session.
     * 
     * @return string The status of the session.
     * 
     * @since 1.0
     */
    public function getStatus() {
        return $this->sessionStatus;
    }
    private $sessionStatus;
    /**
     * The name of random function which is used in session ID generation.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private static $randFunc;
    private $sId;
    public function __construct($options = []) {
        //used to support older PHP versions which does not have 'random_int'.
        self::$randFunc = is_callable('random_int') ? 'random_int' : 'rand';
        $this->sessionStatus = self::STATUS_INACTIVE;
        
        if (!(isset($options['duration']) && $this->setLifeTime($options['duration']))) {
            $this->setLifeTime(self::DEFAULT_SESSION_DURATION);
        }
        
        $this->sName = isset($options['name']) ? trim($options['name']) : 'new-sesstion';
        if (strlen($this->sName) == 0) {
            $this->sName = 'new-seestion';
        }
        
        $this->sId = isset($options['session-id']) ? trim($options['session-id']) : $this->_generateSessionID();
        $this->isRef = isset($options['refresh']) ? $options['refersh'] === true : false;
        
        $this->cookieParams = [
            'expires' => time() + $this->getDuration() * 60,
            'domain' => trim(filter_var($_SERVER['HTTP_HOST']),'/'),
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        $this->resumedAt = 0;
        $this->passedTime = 0;
        $this->startedAt = 0;
        $this->sessionArr = [];
        $ip = filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP);
        $this->passedTime = 0;
        if ($ip == '::1') {
            $ip = '127.0.0.1';
        }
        $this->ipAddr = $ip;
    }
    public function setSameSite($val) {
        $trimmed = strtolower(trim($val));
        if ($trimmed == 'lax' || $trimmed == 'none' || $trimmed == 'strict') {
            $this->cookieParams['samesite'] = strtoupper($trimmed[0]).substr($trimmed, 1);
        }
    }
    public function setLifeTime($time) {
        $asInt = intval($time);
        if ($time >= 0) {
            $this->lifeTime = $asInt;
            $this->cookieParams['lifetime'] = time() + $this->getDuration() * 60;
            return true;
        }
        return false;
    }
    /**
     * Returns the amount of time at which the session will be kept alive in.
     * 
     * @return int This method will return session duration in minutes. The
     * default duration of any new session is 120.
     * 
     * @since 1.0
     */
    public function getDuration() {
        return $this->lifeTime;
    }
    /**
     * Returns the name of the session.
     * 
     * @return string The name of the session as string.
     * 
     * @since 1.0
     */
    public function getName() {
        return $this->sName;
    }
    /**
     * Returns the ID of the session.
     * 
     * @return string The ID of the sesstion.
     */
    public function getId() {
        return $this->sId;
    }
    /**
     * Generate a random session ID.
     * 
     * @return string A new random session ID.
     * 
     * @since 1.6
     */
    private function _generateSessionID() {
        $date = date(DATE_ISO8601);
        $hash = hash('sha256', $date);
        $time = time() + call_user_func(self::$randFunc, 0, 100);
        
        return hash('sha256',$hash.$time.$this->getName());
    }

    public function reGenerateID() {
        $this->sId = $this->_generateSessionID();
        return $this->sId;
    }
    public function getVars() {
        return $this->sessionArr;
    }
    public function getUser() {
        return $this->sesstionUser;
    }
    public function toJSON() {
        return new JsonX([
            'name' => $this->getName(),
            'startedAt' => $this->getStartedAt(),
            'duration' => $this->getDuration(),
            'resumedAt' => $this->getResumedAt(),
            'passedTime' => $this->getPassedTime(),
            'id' => $this->getId(),
            'isRefresh' => $this->isRefresh(),
            'status' => $this->getStatus(),
            'user' => $this->getUser(),
            'vars' => $this->getVars()
        ]);
    }

    public function serialize() {
        $serializedSesstion = serialize($this);
        $encryptAlgo = 'aes-128-cbc-hmac-sha256';
        $key = $this->getId().$this->getIp();
        $iv = substr($this->getId(), 0,16);
        if (in_array($encryptAlgo, openssl_get_cipher_methods())) {
            $encrypted = openssl_encrypt($serializedSesstion, $encryptAlgo, $key,0, $iv);
            return $encrypted;
        }
    }

    public function unserialize($serialized) {
        $encryptAlgo = 'aes-128-cbc-hmac-sha256';
        if (in_array($encryptAlgo, openssl_get_cipher_methods())) {
            $key = $this->getId().$this->getIp();
            $iv = substr($this->getId(), 0,16);
            $encrypted = openssl_decrypt($serialized, $encryptAlgo, $key,0, $iv);
            $sesstionObj = unserialize($encrypted);
            if ($sesstionObj instanceof Session) {
                $this->_clone($sesstionObj);
            }
        }
    }

}
