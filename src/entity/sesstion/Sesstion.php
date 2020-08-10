<?php
namespace webfiori\entity\sesstion;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use SessionHandlerInterface;
use SessionIdInterface;
/**
 * Description of Sesstion
 *
 * @author Ibrahim
 */
class Sesstion {
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
    public function pause() {
        $this->sessionStatus = self::STATUS_PAUSED;
    }
    public function resume() {
        $this->sessionStatus = self::STATUS_RESUMED;
    }
    /**
     * Returns the ID of a session from a cookie given its name.
     * 
     * @param string $sessionName The name of the session.
     * 
     * @return boolean|string If the ID is found, the method will return it. 
     * If the session cookie was not found, the method will return false.
     * 
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
     * Checks if the given session name has a cookie or not.
     * 
     * @return boolean true if a cookie with the name of 
     * the session is fount. false otherwise.
     * 
     * @since 1.0
     */
    public function hasCookie() {
        $sid = self::getSessionIDFromCookie(INPUT_COOKIE, $this->getName());

        return $sid !== false;
    }
    /**
     * Return session ID from session cookie, get or post parameter.
     * 
     * @return string|boolean If session ID is found, the method will 
     * return it. Note that if it is in a cookie, the name of the cookie must 
     * be the name of the session in order to take the ID from it. If it is 
     * in GET or POST request, it must be in a parameter with the name 
     * 'session-id'.
     * 
     * @since 1.0
     */
    public function getSessionIDFromRequest() {
        $sid = self::getSessionIDFromCookie($this->getName());

        if ($sid === false) {
            $sid = filter_var($_POST['session-id'],FILTER_SANITIZE_STRING);

            if ($sid === null || $sid === false) {
                $sid = filter_var($_GET['session-id'],FILTER_SANITIZE_STRING);
            }
        }

        return $sid;
    }
    private $sName;
    private $sesstionData;
    
    
    public function kill() {
        
    }
    public function open() {
        $seestion = SesstionsManager::getStorage()->read($this->getId());
    }
    public function close() {
        if ($this->getStorage() !== null) {
            $this->sesstionData = $this->getStorage()->save($this->getId());
        }
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
    public function __construct() {
        //used to support older PHP versions which does not have 'random_int'.
        self::$randFunc = is_callable('random_int') ? 'random_int' : 'rand';
        $this->sessionStatus = self::STATUS_NONE;
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
