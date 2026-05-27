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

use WebFiori\Framework\App;
use WebFiori\Framework\Exceptions\SessionException;
use WebFiori\Http\Request;
/**
 * A class which is used to manage user sessions.
 *
 * @author Ibrahim
 *
 * @since 1.1.0
 *
 * @version 1.1
 */
class SessionsManager {
    /**
     * The current active session.
     *
     * @var Session|null
     *
     * @since 1.0
     */
    private $activeSession;
    /**
     * Maximum number of sessions to remove per GC run.
     *
     * @var int
     */
    private $gcBatchSize;
    /**
     * GC probability divisor.
     *
     * @var int
     */
    private $gcDivisor;
    /**
     * GC probability numerator.
     *
     * @var int
     */
    private $gcProbability;
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
    private $sessionsArr;
    /**
     * The storage interface which is used to store session state.
     *
     * @var SessionStorage
     *
     * @since 1.0
     */
    private $sessionStorage;

    /**
     * @throws SessionException
     * @since 1.0
     */
    private function __construct() {
        $this->sessionsArr = [];
        $this->gcProbability = 1;
        $this->gcDivisor = 100;
        $this->gcBatchSize = 100;

        if (defined('WF_SESSION_STORAGE')) {
            $constructor = WF_SESSION_STORAGE.'';
            $classObj = new $constructor();

            if (is_subclass_of($classObj, '\WebFiori\Framework\Session\SessionStorage')) {
                $this->sessionStorage = $classObj;
            } else {
                throw new SessionException('The class "'.WF_SESSION_STORAGE.'" does not implement "\WebFiori\Framework\Session\SessionStorage".');
            }
        }

        if ($this->sessionStorage === null) {
            $this->sessionStorage = new DefaultSessionStorage();
        }
    }
    /**
     * Saves the state of the active session and close it.
     *
     * @since 1.0
     */
    public static function close() {
        $active = self::getActiveSession();

        if ($active !== null) {
            $active->close();
            self::getInstance()->activeSession = null;
        }
    }
    /**
     * Destroy the active session.
     *
     * Calling this method when there is no active session will have no effect.
     *
     * @since 1.0
     */
    public static function destroy() {
        $active = self::getActiveSession();

        if ($active !== null) {
            $active->kill();
            self::getInstance()->activeSession = null;
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
    public static function get(string $varName) {
        $active = self::getActiveSession();

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
    public static function getActiveSession() {
        if (self::getInstance()->activeSession !== null) {
            return self::getInstance()->activeSession;
        }

        foreach (self::getInstance()->sessionsArr as $session) {
            $status = $session->getStatus();

            if ($status == SessionStatus::NEW || $status == SessionStatus::RESUMED) {
                self::getInstance()->activeSession = $session;

                return $session;
            }
        }
    }
    /**
     * Returns an array that contains cookies headers values.
     *
     * The returned values can be used to create cookies for sessions.
     *
     * @return array The method will return an array that contains headers values
     * that can be used to create sessions cookies.
     *
     * @since 1.0
     */
    public static function getCookiesHeaders() : array {
        $sessions = self::getSessions();
        $retVal = [];

        foreach ($sessions as $session) {
            $retVal[] = $session->getCookieHeader();
        }

        return $retVal;
    }
    /**
     * Returns the maximum number of sessions to remove per GC run.
     *
     * @return int
     */
    public static function getGCBatchSize(): int {
        return self::getInstance()->gcBatchSize;
    }
    /**
     * Returns the GC probability divisor.
     *
     * @return int
     */
    public static function getGCDivisor(): int {
        return self::getInstance()->gcDivisor;
    }
    /**
     * Returns the GC probability numerator.
     *
     * @return int
     */
    public static function getGCProbability(): int {
        return self::getInstance()->gcProbability;
    }
    /**
     * Returns a date string that represents the time at which sessions
     * older than it will be cleared by GC.
     *
     * The method uses the session default duration to compute the threshold.
     * If the environment variable 'SESSION_GC' or the constant 'SESSION_GC'
     * is set, it will be used as a Unix timestamp instead.
     *
     * @return string A date string in the format 'Y-m-d H:i:s'.
     */
    public static function getGCTime() : string {
        $olderThan = time() - (Session::DEFAULT_SESSION_DURATION * 60 * 2);
        $fromEnv = getenv('SESSION_GC') !== false ? intval(getenv('SESSION_GC')) : 0;
        $fromConst = defined('SESSION_GC') && intval(SESSION_GC) > 0 ? intval(SESSION_GC) : 0;

        if ($fromEnv != 0) {
            $olderThan = $fromEnv;
        } else {
            if ($fromConst != 0) {
                $olderThan = $fromConst;
            }
        }

        return date('Y-m-d H:i:s', $olderThan);
    }
    /**
     * Returns the ID of a session from a cookie given its name.
     *
     * @param string $sessionName The name of the session.
     *
     * @return bool|string If the ID is found, the method will return it.
     * If the session cookie was not found, the method will return false.
     *
     * @since 1.0
     */
    public static function getSessionIDFromCookie(string $sessionName) {
        $sid = filter_input(INPUT_COOKIE, $sessionName);

        if ($sid !== null && $sid !== false) {
            return $sid;
        }

        return false;
    }
    /**
     * Return session ID from session cookie, get or post parameter.
     *
     * @return string|bool If session ID is found, the method will
     * return it. Note that if it is in a cookie, the name of the cookie must
     * be the name of the session in order to take the ID from it. If it is
     * in GET or POST request, it must be in a parameter with the name of the session.
     *
     * @since 1.0
     */
    public static function getSessionIDFromRequest(string $sessionName) {
        $trimmedSName = trim($sessionName);
        $sid = self::getSessionIDFromCookie($trimmedSName);

        if ($sid === false) {
            $sid = App::getRequest()->getParam($sessionName);

            if ($sid === null) {
                return false;
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
    public static function getSessions() : array {
        return self::getInstance()->sessionsArr;
    }
    /**
     * Returns storage engine which is used to store sessions state.
     *
     * @return SessionStorage
     *
     * @since 1.0
     */
    public static function getStorage() : SessionStorage {
        return self::getInstance()->sessionStorage;
    }
    /**
     * Checks if the active session has a cookie or not.
     *
     * Note that in command line, the method will always return false.
     *
     * @return bool true if The active session has a cookie. False if not. If no
     * session is active, false is returned.
     */
    public static function hasCookie() : bool {
        $active = self::getActiveSession();

        if ($active !== null) {
            $sid = self::getSessionIDFromCookie($active->getName());

            return $sid !== false;
        }

        return false;
    }
    /**
     * Checks if the manager has specific session or not.
     *
     * @param string $sName The name of the session.
     *
     * @return bool If the manager manages a session which has the given name,
     * the method will return true. False otherwise.
     *
     * @since 1.0
     */
    public static function hasSession(string $sName): bool {
        try {
            $trimmed = trim($sName);

            if (!self::checkLoadedSessions($trimmed)) {
                return self::checkAndLoadFromCookie($trimmed);
            }
        } catch (SessionException $e) {
            return false;
        }

        return true;
    }
    /**
     * Generate new ID for the active session.
     *
     * @return string|null If there was an active session and new ID is generated
     * for it, the method will return the new ID. Other than that, the method
     * will return null.
     *
     * @since 1.0
     */
    public static function newId() {
        $active = self::getActiveSession();

        if ($active !== null) {
            return $active->reGenerateID();
        }
    }
    /**
     * Stores the status of all sessions and pause them.
     *
     * @since 1.0
     */
    public static function pauseAll() {
        self::getInstance()->pauseSessions();
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
    public static function pull(string $varName) {
        $active = self::getActiveSession();

        if ($active !== null) {
            return $active->pull($varName);
        }
    }
    /**
     * Removes the value of a session variable from the active session.
     *
     * @param string $varName The name of the variable.
     *
     * @return bool If the value was deleted, the method will return true.
     * If the does not exist or no session is active, the method will return false.
     *
     * @since 1.0
     */
    public static function remove(string $varName): bool {
        $active = self::getActiveSession();

        if ($active !== null) {
            return $active->remove($varName);
        }

        return false;
    }
    /**
     * Reset sessions manager to defaults.
     *
     * This method will clear all sessions, set session storage to 'DefaultSessionStorage',
     * and set active session to null.
     *
     * @since 1.0
     */
    public static function reset() {
        self::getInstance()->sessionsArr = [];
        self::getInstance()->sessionStorage = new DefaultSessionStorage();
        self::getInstance()->activeSession = null;
        self::getInstance()->gcProbability = 1;
        self::getInstance()->gcDivisor = 100;
        self::getInstance()->gcBatchSize = 100;
    }
    /**
     * Sets session variable.
     *
     * Note that session variable will be set only if there was an active session.
     *
     * @param string $varName The name of the variable. Must be non-empty string.
     *
     * @param mixed $value The value of the variable. It can be anything.
     *
     * @return bool If the variable is set, the method will return true. If
     * not, the method will return false.
     *
     * @since 1.0
     */
    public static function set(string $varName, $value): bool {
        $active = self::getActiveSession();

        if ($active !== null) {
            return $active->set($varName, $value);
        }

        return false;
    }
    /**
     * Sets the maximum number of sessions to remove per GC run.
     *
     * @param int $size Maximum number of sessions to remove. 0 means no limit.
     */
    public static function setGCBatchSize(int $size) {
        if ($size >= 0) {
            self::getInstance()->gcBatchSize = $size;
        }
    }
    /**
     * Sets the GC probability and divisor.
     *
     * GC will run with a probability of $probability/$divisor on each request.
     * For example, setGCProbability(1, 100) means 1% chance per request.
     *
     * @param int $probability The numerator. Must be >= 0.
     * @param int $divisor The denominator. Must be > 0. Set to 0 to disable GC.
     */
    public static function setGCProbability(int $probability, int $divisor) {
        if ($probability >= 0) {
            self::getInstance()->gcProbability = $probability;
        }

        if ($divisor >= 0) {
            self::getInstance()->gcDivisor = $divisor;
        }
    }
    /**
     * Sets sessions storage engine.
     *
     * This method is used to create a custom sessions storage engine. The
     * framework by default provide one type which is file storage. The
     * developer can implement a custom storage engine using the interface
     * 'SessionStorage'.
     *
     * @param SessionStorage $storage The new session storage.
     *
     * @since 1.0
     */
    public static function setStorage(SessionStorage $storage) {
        self::getInstance()->sessionStorage = $storage;
    }
    /**
     * Starts new session or resumes an existing one.
     *
     * The method will first check if a session which has the given name exist.
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
    public static function start(string $sessionName, array $options = []) {
        self::getInstance()->pauseSessions();

        if (!self::hasSession($sessionName)) {
            $options[SessionOption::NAME] = $sessionName;
            $s = new Session($options);
            $s->start();
            self::getInstance()->sessionsArr[] = $s;
        } else {
            foreach (self::getInstance()->sessionsArr as $sessionObj) {
                if ($sessionObj->getName() == $sessionName) {
                    $sessionObj->start();
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
     * sessions which are not active anymore based on GC probability settings.
     *
     * @since 1.0
     */
    public static function validateStorage() {
        foreach (self::getInstance()->sessionsArr as $session) {
            $status = $session->getStatus();

            if ($status == SessionStatus::NEW ||
                $status == SessionStatus::PAUSED ||
                $status == SessionStatus::RESUMED) {
                self::getStorage()->save($session->getId(), $session->serialize());
            } else if ($status == SessionStatus::KILLED) {
                self::getStorage()->remove($session->getId());
            }
        }

        if (self::shouldRunGC()) {
            self::getStorage()->gc(self::getGCTime(), self::getGCBatchSize());
        }
    }

    /**
     *
     * @param string $sName
     *
     * @return bool
     * @throws SessionException
     * @since 1.0
     */
    private static function checkAndLoadFromCookie(string $sName): bool {
        $sId = self::getSessionIDFromRequest($sName);

        if ($sId !== false) {
            $tempSession = new Session([
                'session-id' => $sId,
                'name' => 'x'
            ]);
            $tempSession->start();

            if ($tempSession->getStatus() == SessionStatus::RESUMED) {
                self::getInstance()->sessionsArr[] = $tempSession;

                return true;
            }
        }

        return false;
    }

    /**
     *
     * @param string $sName
     *
     *
     * @return bool
     * @since 1.0
     */
    private static function checkLoadedSessions(string $sName): bool {
        foreach (self::getInstance()->sessionsArr as $sessionObj) {
            if ($sessionObj->getName() == $sName) {
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
    private static function getInstance(): SessionsManager {
        if (self::$inst === null) {
            self::$inst = new SessionsManager();
        }

        return self::$inst;
    }
    /**
     * @since 1.0
     */
    private function pauseSessions() {
        $this->activeSession = null;

        foreach ($this->sessionsArr as $session) {
            $session->close();
        }
    }
    /**
     * Determines if GC should run on this request based on probability settings.
     *
     * @return bool True if GC should run.
     */
    private static function shouldRunGC(): bool {
        $divisor = self::getInstance()->gcDivisor;

        if ($divisor <= 0) {
            return false;
        }

        $probability = self::getInstance()->gcProbability;

        if ($probability <= 0) {
            return false;
        }

        return random_int(1, $divisor) <= $probability;
    }
}
