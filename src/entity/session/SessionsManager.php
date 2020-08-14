<?php
/*
 * The MIT License
 *
 * Copyright 2020 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity\session;

use webfiori\entity\cli\CLI;
/**
 * A class which is used to manage user sessions.
 *
 * @author Ibrahim
 * 
 * @since 1.1.0
 * 
 * @version 1.0
 */
class SessionsManager {
    /**
     * The current active session.
     * 
     * @var Session|null
     * 
     * @since 1.0 
     */
    private $activeSesstion;
    /**
     *
     * @var SessionsManager 
     * 
     * @since 1.0
     */
    private static $inst;
    /**
     * An array that contains all initialized sessions.
     * 
     * @var array
     * 
     * @since 1.0 
     */
    private $sesstionsArr;
    /**
     * The storage interface which is used to store session state.
     * 
     * @var SessionStorage 
     * 
     * @since 1.0
     */
    private $sesstionStorage;
    /**
     * @since 1.0
     */
    private function __construct() {
        $this->sesstionsArr = [];
        $this->sesstionStorage = new DefaultSessionStorage();
    }
    /**
     * Destroy the active session.
     * 
     * Calling this method when there is no active session will have no effect.
     * 
     * @since 1.0
     */
    public static function destroy() {
        $active = self::getActiveSesstion();

        if ($active !== null) {
            $active->kill();
            self::_get()->activeSesstion = null;
        }
    }
    /**
     * Returns the value of a session variable.
     * 
     * The value will be taken from the active session.
     * 
     * @param string $varName The name of the variable.
     * 
     * @return null|mixed If a variable which has the given name is found, its 
     * value is returned. If no such variable exist or there was no active session,
     * the method will return null.
     * 
     * @since 1.0
     */
    public static function get($varName) {
        $active = self::getActiveSesstion();

        if ($active !== null) {
            return $active->get($varName);
        }
    }
    /**
     * Returns the currently active session.
     * 
     * @return Session|null If a session is active, the method will return an 
     * object of type 'Session' that contains session information. If no 
     * session is active, the method will return null.
     * 
     * @since 1.0
     */
    public static function getActiveSesstion() {
        if (self::_get()->activeSesstion !== null) {
            return self::_get()->activeSesstion;
        }

        foreach (self::_get()->sesstionsArr as $sesstion) {
            $sesstion instanceof Session;
            $status = $sesstion->getStatus();

            if ($status == Session::STATUS_NEW || $status == Session::STATUS_RESUMED) {
                self::_get()->activeSesstion = $sesstion;

                return $sesstion;
            }
        }
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
            $sid = filter_input(INPUT_POST, $seestionName);

            if ($sid === null || $sid === false) {
                $sid = filter_input(INPUT_POST, $seestionName);
            }
        }

        return $sid;
    }
    /**
     * Returns an indexed array that contains all created sessions.
     * 
     * @return array An array that contains objects of type 'Session'.
     * 
     * @since 1.0
     */
    public static function getSesstions() {
        return self::_get()->sesstionsArr;
    }
    /**
     * Returns storage engine which is used to store sessions state.
     * 
     * @return SessionStorage
     * 
     * @since 1.0
     */
    public static function getStorage() {
        return self::_get()->sesstionStorage;
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
     * Checks if the manager has specific session or not.
     * 
     * @param string $sName The name of the session.
     * 
     * @return boolean If the manager manages a session which has the given name, 
     * the method will return true. False otherwise.
     * 
     * @since 1.0
     */
    public static function hasSesstion($sName) {
        $trimmed = trim($sName);

        if (!self::_checkLoadedSesstions($trimmed)) {
            return self::_checkAndLoadFromCookie($trimmed);
        }

        return true;
    }
    /**
     * Stores the status of all sessions and pause them.
     * 
     * @since 1.0
     */
    public static function pauseAll() {
        self::_get()->_pauseSessions();
    }
    /**
     * Retrieves the value of a session variable and removes it from the session.
     *  
     * @param string $varName The name of the variable.
     * 
     * @return mixed|null If the variable exist and its value is set, the method 
     * will return its value. If the value is not set or no session is
     * active, the method will return null.
     * 
     * @since 1.0
     */
    public static function pull($varName) {
        $active = self::getActiveSesstion();

        if ($active !== null) {
            return $active->pull($varName);
        }
    }
    /**
     * Removes the value of a session variable from the active session.
     * 
     * @param string $varName The name of the variable.
     * 
     * @return boolean If the value was deleted, the method will return true. 
     * If the does not exist or no session is active, the method will return false.
     * 
     * @since 1.0
     */
    public static function remove($varName) {
        $active = self::getActiveSesstion();

        if ($active !== null) {
            return $active->remove($varName);
        }

        return false;
    }
    /**
     * Sets session variable. 
     * 
     * Note that session variable will be set only if there was an active session.
     * 
     * @param string $varName The name of the variable. Must be non-empty string.
     * 
     * @param mixed $value The value of the variable. It can be any thing.
     * 
     * @return boolean If the variable is set, the method will return true. If 
     * not, the method will return false.
     * 
     * @since 1.0
     */
    public static function set($varName, $value) {
        $active = self::getActiveSesstion();

        if ($active !== null) {
            return $active->set($varName, $value);
        }

        return false;
    }
    /**
     * Sets sessions storage engine.
     * 
     * This method is used to create a custom sessions storage engine. The 
     * framework by default provide one type which is file storage. The 
     * developer can implement a custom storage engine using the interface 
     * 'SessionStorage'.
     * 
     * @param SesstionStorage $storage The new sessions storage.
     * 
     * @since 1.0
     */
    public static function setStorage($storage) {
        if ($storage instanceof SesstionStorage) {
            self::_get()->sesstionStorage = $storage;
        }
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
     * @param array $options An array that contains session options. Available 
     * options are:
     * <ul>
     * <li><b>duration</b>: The duration of the session in minutes. Must be a number 
     * greater than or equal to 0. If 0 is given, it means the session is not 
     * persistent.</li>
     * <li><b>refresh</b>: A boolean which is set to true if session timeout time 
     * will be refreshed with every request. Default is false.</li>
     * </ul>
     * 
     * @throws SessionException If session name is missing or invalid.
     */
    public static function start($sessionName, $options = []) {
        self::_get()->_pauseSessions();

        if (!self::hasSesstion($sessionName)) {
            $options['name'] = $sessionName;
            $s = new Session($options);
            $s->start();
            self::_get()->sesstionsArr[] = $s;
        } else {
            foreach (self::_get()->sesstionsArr as $sesstionObj) {
                if ($sesstionObj->getName() == $sessionName) {
                    $sesstionObj->start();
                }
            }
        }
    }
    /**
     * Validate the current status of the storage.
     * 
     * This method will go through all sessions which was activated and check the 
     * status of each one. If the session is new, paused or resumed, the method 
     * will store session state using specified storage engine. If the status 
     * of the session is killed, the method will remove session state from 
     * the storage. In addition to that, the method will garbage-collect all 
     * sessions which are not active any more. The garbage collection algorithm 
     * will depends on the way the developer have implemented his own sessions 
     * storage engine.
     * 
     * @since 1.0
     */
    public static function validateStorage() {
        foreach (self::_get()->sesstionsArr as $session) {
            $status = $session->getStatus();
            $session instanceof Session;

            if ($status == Session::STATUS_NEW ||
                $status == Session::STATUS_PAUSED ||  
                $status == Session::STATUS_RESUMED) {
                self::getStorage()->save($session->getId(), $session->serialize());
            } else {
                if ($status == Session::STATUS_KILLED) {
                    self::getStorage()->remove($session->getId());
                }
            }

            if (!CLI::isCLI()) {
                header($session->getCookieHeader());
            }
        }
        self::getStorage()->gc();
    }
    /**
     * 
     * @param type $sName
     * 
     * @return boolean
     * 
     * @since 1.0
     */
    private static function _checkAndLoadFromCookie($sName) {
        $sId = self::getSessionIDFromCookie($sName);

        if ($sId !== false) {
            $tempSesstion = new Session([
                'session-id' => $sId
            ]);
            $tempSesstion->start();

            if ($tempSesstion->getStatus() == Session::STATUS_RESUMED) {
                self::_get()->sesstionsArr[] = $tempSesstion;

                return true;
            }
        }

        return false;
    }
    /**
     * 
     * @param type $sName
     * 
     * @return boolean
     * 
     * @since 1.0
     */
    private static function _checkLoadedSesstions($sName) {
        foreach (self::_get()->sesstionsArr as $sesstionObj) {
            if ($sesstionObj->getName() == $sName) {
                return true;
            }
        }

        return false;
    }
    /**
     * 
     * @return SessionsManager
     * 
     * @since 1.0
     */
    private static function _get() {
        if (self::$inst === null) {
            self::$inst = new SessionsManager();
        }

        return self::$inst;
    }
    /**
     * @since 1.0
     */
    private function _pauseSessions() {
        $this->activeSesstion = null;

        foreach ($this->sesstionsArr as $sesstion) {
            $sesstion->close();
        }
    }
}
