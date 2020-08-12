<?php

namespace webfiori\entity\sesstion;

/**
 * Description of SesstionsManager
 *
 * @author Ibrahim
 * 
 * @since 1.1.0
 * 
 * @version 1.0
 */
class SessionsManager {
    private $sesstionsArr;
    private $activeSesstion;
    /**
     *
     * @var SessionStorage 
     */
    private $sesstionStorage;
    public static function setStorage($storage) {
        if ($storage instanceof SesstionStorage) {
            self::get()->sesstionStorage = $storage;
        }
    }
    /**
     * 
     * @return SessionStorage
     */
    public static function getStorage() {
        return self::get()->sesstionStorage;
    }
    private static $inst;
    /**
     * 
     * @return SessionsManager
     */
    private static function get() {
        
        if (self::$inst === null) {
            self::$inst = new SessionsManager();
        }
        return self::$inst;
    }
    /**
     * 
     * @return Session|null
     * 
     * @since 1.0
     */
    public static function getActiveSesstion() {
        
        if (self::get()->activeSesstion !== null) {
            return self::get()->activeSesstion;
        }
        
        foreach (self::get()->sesstionsArr as $sesstion) {
            $sesstion instanceof Session;
            $status = $sesstion->getStatus();
            if ($status == Session::STATUS_NEW || $status == Session::STATUS_RESUMED) {
                self::get()->activeSesstion = $sesstion;
                return $sesstion;
            }
        }
    }
    private function __construct() {
        $this->sesstionsArr = [];
        $this->sesstionStorage = new DefaultSessionStorage();
    }
    public static function getSesstions() {
        return self::get()->sesstionsArr;
    }
    /**
     * 
     * @param type $sName
     * 
     * @return boolean
     * 
     * @since 1.0
     */
    public static function hasSesstion($sName) {
        
        foreach (self::get()->sesstionsArr as $sesstionObj) {
            
            if ($sesstionObj->getName() == $sName) {
                return true;
            }
        }
        
        $sId = self::getSessionIDFromCookie($sName);
        
        if ($sId !== false) {
            $tempSesstion = new Session([
                'session-id' => $sId
            ]);
            $tempSesstion->start();
            if ($tempSesstion->getStatus() == Session::STATUS_RESUMED) {
                self::get()->sesstionsArr[] = $tempSesstion;
                return true;
            }
        }
        
        return false;
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
    public static function getSessionIDFromRequest($seestionName) {
        $sid = self::getSessionIDFromCookie($seestionName);

        if ($sid === false) {
            $sid = filter_var($_POST[$seestionName], FILTER_SANITIZE_STRING);

            if ($sid === null || $sid === false) {
                $sid = filter_var($_GET[$seestionName], FILTER_SANITIZE_STRING);
            }
        }

        return $sid;
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
     * Returns the ID of a session from a cookie given its name.
     * 
     * @param string $sessionName The name of the session.
     * 
     * @return boolean|string If the ID is found, the method will return it. 
     * If the session cookie was not found, the method will return false.
     * 
     * @since 1.0
     */
    public static function getSessionIDFromCookie($sessionName) {
        $sid = filter_input(INPUT_COOKIE, $sessionName);

        if ($sid !== null && $sid !== false) {
            return $sid;
        }

        return false;
    }
    /**
     * Starts new session or resumes an existing one.
     * 
     * The method will first checks if a session which has the given name exist. 
     * if there was such session, it will pause all sessions and resumes selected 
     * one. If no session was found which has the given name, the method will 
     * initialize new session.
     * 
     * @param string $sessionName The name of the session that will be resumed 
     * or created.
     * 
     * @param array $options An associative array that contains options which 
     * are used if the session is new. Available options are:
     * <ul>
     * <li><b>duration</b>: The duration of the session in minutes. Must be a number 
     * greater than or equal to 0. If 0 is given, it means the session is not 
     * presestent.</li>
     * <li><b>refersh</b>: A boolean which is set to true if session timeout time 
     * will be refreshed with every request. Default is false.</li>
     * <li><b></b>: </li>
     * v<li><b></b>: </li>
     * </ul>
     */
    public static function start($sessionName, $options = []) {
        self::get()->_pauseSesstions();
        if (!self::hasSesstion($sessionName)) {
            $options['name'] = $sessionName;
            $s = new Session([
                'name' => $sessionName,
                'duration' => $duration
            ]);
            $s->start();
            self::get()->sesstionsArr[] = $s;
        } else {
            foreach (self::get()->sesstionsArr as $sesstionObj) {
                if ($sesstionObj->getName() == $sessionName) {
                    $sesstionObj->start();
                }
            }
        }
    }
    private function _pauseSesstions() {
        $this->activeSesstion = null;
        foreach ($this->sesstionsArr as $sesstion) {
            $sesstion->close();
        }
    }
    public static function validateStorage() {
        foreach (self::get()->sesstionsArr as $sesstion) {
            $status = $sesstion->getStatus();
            $sesstion instanceof Session;
            if ($status == Session::STATUS_NEW ||
                $status == Session::STATUS_PAUSED ||  
                $status == Session::STATUS_RESUMED){
                self::getStorage()->save($sesstion);
                $cookieParams = $sesstion->getCookieParams();
            } else if ($status == Session::STATUS_KILLED) {
                self::getStorage()->remove($sesstion->getId());
            }
            self::get()->setSesssionCookie($sesstion->getName(), $sesstion->getId(),$cookieParams);
        }
        
    }
    private function setSesssionCookie($name, $value, $cookieParams) {
        $httpOnly = $cookieParams['httponly'] === true ? '; HttpOnly' : '';
        $secure = $cookieParams['secure'] === true ? '; Secure' : '';
        $sameSite = $cookieParams['samesite'];
        $lifetime = date(DATE_COOKIE, $cookieParams['expires']);
        $cookieHeader = "set-cookie: $name=$value; "
                . "path=".$cookieParams['path']
                . "; expires=$lifetime"
                //. "$secure"
                //. "$httpOnly"
                . '; SameSite='.$sameSite;
        header($cookieHeader, false);
    }
}
