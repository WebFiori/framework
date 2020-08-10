<?php
namespace webfiori\entity\sesstion;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use webfiori\entity\User;
/**
 * Description of session
 *
 * @author Ibrahim
 */
class Session {

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
    const STATUS_NONE = 'status_none';
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
     * A variable is set to true if the session is new and set to false if resumed.
     * 
     * @var boolean
     * 
     * @since 1.0 
     */
    private $new;
    /**
     * A variable is set to true if the session is resumed and set to false if new.
     * 
     * @var boolean
     * 
     * @since 1.0 
     */
    private $resumed;
    public function pause() {
        $this->sessionStatus = self::STATUS_PAUSED;
    }
    private function _initNewSesstionVars() {
        $this->resumed = false;
        $this->new = true;
        $this->sessionArr[self::$SV][self::MAIN_VARS[4]] = $this->getName();
        $this->sessionArr[self::$SV][self::MAIN_VARS[1]] = time();
        $this->sessionArr[self::$SV][self::MAIN_VARS[2]] = time();
        $this->sessionArr[self::$SV][self::MAIN_VARS[0]] = $lifeTime;
        $this->sessionArr[self::$SV][self::MAIN_VARS[6]] = new User();
        $this->sessionArr[self::$SV][self::MAIN_VARS[8]] = self::STATUS_NEW;
        $this->sessionStatus = self::STATUS_NEW;
        $ip = filter_var($_SERVER['REMOTE_ADDR'],FILTER_VALIDATE_IP);

        if ($ip == '::1') {
            $ip = '127.0.0.1';
        }
        $this->sessionArr[self::$SV][self::MAIN_VARS[5]] = $ip;
        $this->sessionArr[self::$SV][self::MAIN_VARS[3]] = $refresh === true;
    }
    public function resume() {
        $this->sessionStatus = self::STATUS_RESUMED;
    }
    
    public function getCookieParams() {
        return $this->cookieParams;
    }
    
    private $sName;
    
    
    public function kill() {
        SesstionsManager::getStorage()->remove($this->getId());
    }
    public function open() {
        $seestion = SesstionsManager::getStorage()->read($this->getId());
        if ($seestion instanceof Session) {
            $this->cookieParams = $seestion->cookieParams;
            $this->sessionArr = $seestion->sessionArr;
            $this->sName = $seestion->sName;
        }
    }
    public function close() {
        SesstionsManager::getStorage()->save($this);
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
    /**
     * The name of the index that contains session vars.
     * 
     * @var string
     * 
     * @since 1.0
     */
    private static $SV = 'session-vars';
    public function __construct($options = []) {
        //used to support older PHP versions which does not have 'random_int'.
        self::$randFunc = is_callable('random_int') ? 'random_int' : 'rand';
        $this->sessionStatus = self::STATUS_NONE;
        $this->lifeTime = self::DEFAULT_SESSION_DURATION;
        $this->sName = isset($options['name']) ? trim($options['name']) : 'new-sesstion';
        if (strlen($this->sName) == 0) {
            $this->sName = 'new-seestion';
        }
        $this->cookieParams = [
            'lifetime' => time() + $this->getLifeTime() * 60,
            'domain' => trim(filter_var($_SERVER['HTTP_HOST']),'/'),
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        $this->sessionArr = [
            self::$CSV => [],
            self::$SV => []
        ];
    }
    /**
     * 
     * @return type
     */
    public function getLifeTime() {
        return $this->lifeTime;
    }
    public function getName() {
        return $this->sName;
    }
    public function getId() {
        return $this->sId;
    }
    public function start() {
        $this->createSid();
        $this->sessionStatus = self::STATUS_NEW;
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
        $hash2 = hash('sha256',$hash.$time);

        return substr($hash2, 0, 27);
    }

    public function createSid() {
        $this->sId = $this->_generateSessionID();
        return $this->sId;
    }

}
