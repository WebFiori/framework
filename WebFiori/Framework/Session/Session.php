<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Session;

use WebFiori\Framework\App;
use WebFiori\Framework\Exceptions\SessionException;
use WebFiori\Http\HttpCookie;
use WebFiori\Http\Request;
use WebFiori\Json\Json;
use WebFiori\Json\JsonI;/**
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
     * Conflict strategy controlling how set() handles concurrent modifications.
     *
     * @var ConflictStrategy
     */
    private ConflictStrategy $conflictStrategy;
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
     * Local in-memory snapshot used by SNAPSHOT_WITH_MISS and MANUAL_SYNC strategies.
     *
     * @var array<string, mixed>
     */
    private array $localSnapshot = [];
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
     * Read strategy controlling how get() fetches values.
     *
     * @var ReadStrategy
     */
    private ReadStrategy $readStrategy;
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
        $this->readStrategy = ReadStrategy::REALTIME;
        $this->conflictStrategy = ConflictStrategy::LAST_WRITE_WINS;
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
     * In the new per-key session system, writes are already persisted as they
     * happen. close() updates session metadata and marks the session as paused.
     *
     */
    public function close() {
        if ($this->isRunning()) {
            // Persist updated metadata (lifetime, lang, refresh flag).
            $this->persistMeta();
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
        if (str_starts_with($serialized, 'ENC:')) {
            $plaintext = $this->decryptSession(substr($serialized, 4));
        } else if (str_starts_with($serialized, 'RAW:')) {
            $plaintext = substr($serialized, 4);
        } else {
            // Legacy format (len_ciphertext)
            $plaintext = $this->decryptLegacy($serialized);
        }

        if ($plaintext === null) {
            return false;
        }

        return $this->restoreFromPlaintext($plaintext);
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
        if (!$this->isRunning()) {
            return null;
        }

        $key = trim($varName);

        switch ($this->readStrategy) {
            case ReadStrategy::REALTIME:
                $entry = SessionsManager::getStorage()->read($this->getId(), $key);

                return $entry !== null ? $this->decryptValue($entry['value']) : null;

            case ReadStrategy::SNAPSHOT_WITH_MISS:
                if (array_key_exists($key, $this->localSnapshot)) {
                    return $this->localSnapshot[$key];
                }
                $entry = SessionsManager::getStorage()->read($this->getId(), $key);

                if ($entry !== null) {
                    $decrypted = $this->decryptValue($entry['value']);
                    $this->localSnapshot[$key] = $decrypted;

                    return $decrypted;
                }

                return null;

            case ReadStrategy::MANUAL_SYNC:
                return $this->localSnapshot[$key] ?? null;
        }

        return null;
    }
    /**
     * Returns the current conflict strategy.
     *
     * @return ConflictStrategy
     */
    public function getConflictStrategy(): ConflictStrategy {
        return $this->conflictStrategy;
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
     * Returns the current read strategy.
     *
     * @return ReadStrategy
     */
    public function getReadStrategy(): ReadStrategy {
        return $this->readStrategy;
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
        if (!$this->isRunning()) {
            return [];
        }

        $all = SessionsManager::getStorage()->readAll($this->getId());
        $result = [];

        foreach ($all as $k => $entry) {
            if ($k !== '_meta') {
                $result[$k] = $this->decryptValue($entry['value']);
            }
        }

        return $result;
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
        if (!$this->isRunning()) {
            return false;
        }

        return SessionsManager::getStorage()->read($this->getId(), trim($varName)) !== null;
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
        SessionsManager::getStorage()->destroy($this->getId());
        $this->sessionStatus = SessionStatus::KILLED;
        $this->localSnapshot = [];
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
     * Reloads all session keys from storage into the local snapshot.
     *
     * Only relevant when using ReadStrategy::MANUAL_SYNC. Call this at
     * explicit sync boundaries (e.g. at the start of each SSE event loop
     * iteration) to pick up writes from other processes.
     */
    public function refresh(): void {
        $all = SessionsManager::getStorage()->readAll($this->getId());
        $this->localSnapshot = [];

        foreach ($all as $k => $entry) {
            if ($k !== '_meta') {
                $this->localSnapshot[$k] = $this->decryptValue($entry['value']);
            }
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
        if (!$this->isRunning()) {
            return false;
        }

        $key = trim($varName);
        $exists = SessionsManager::getStorage()->read($this->getId(), $key) !== null;

        if ($exists) {
            SessionsManager::getStorage()->remove($this->getId(), $key);
            unset($this->localSnapshot[$key]);

            return true;
        }

        return false;
    }
    /**
     * Serialize the session.
     *
     * @return string The method will return a string that represents serialized
     * session data. If the constant SESSION_KEY is defined and non-empty,
     * the data will be encrypted using AES-256-GCM. Otherwise, it will be
     * stored as base64-encoded plaintext.
     *
     */
    public function serialize() : string {
        $plaintext = base64_encode(serialize($this));
        $sessionKey = defined('SESSION_KEY') ? SESSION_KEY : null;

        if ($sessionKey !== null && $sessionKey !== '') {
            $key = hash('sha256', $sessionKey.$this->getId(), true);
            $iv = random_bytes(12);
            $tag = '';
            $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

            return 'ENC:'.base64_encode($iv.$tag.$ciphertext);
        }

        return 'RAW:'.$plaintext;
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
    public function set(string $name, $val, ?ConflictStrategy $strategyOverride = null, ?callable $conflictCallback = null) : bool {
        if (!$this->isRunning()) {
            return false;
        }

        $key = trim($name);

        if (strlen($key) === 0) {
            return false;
        }

        $strategy = $strategyOverride ?? $this->conflictStrategy;

        if ($strategy === ConflictStrategy::RETRY_WITH_CALLBACK && $conflictCallback !== null) {
            return $this->writeWithRetry($key, $val, $conflictCallback);
        }

        // For REJECT, pass the current version so the storage can detect conflicts.
        $expectedVersion = null;

        if ($strategy === ConflictStrategy::REJECT) {
            $current = SessionsManager::getStorage()->read($this->getId(), $key);
            $expectedVersion = $current !== null ? $current['version'] : null;
        }

        SessionsManager::getStorage()->write($this->getId(), $key, $this->encryptValue($val), $expectedVersion, $strategy);

        // Update local snapshot for non-REALTIME strategies.
        if ($this->readStrategy !== ReadStrategy::REALTIME) {
            $this->localSnapshot[$key] = $val;
        }

        // Keep legacy sessionVariables in sync for serialize() backward compat.
        $this->sessionVariables[$key] = $val;

        return true;
    }
    /**
     * Sets the conflict resolution strategy for this session.
     *
     * @param ConflictStrategy $strategy The conflict strategy to use.
     */
    public function setConflictStrategy(ConflictStrategy $strategy): void {
        $this->conflictStrategy = $strategy;
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
     * Sets the read strategy for this session.
     *
     * @param ReadStrategy $strategy The read strategy to use.
     */
    public function setReadStrategy(ReadStrategy $strategy): void {
        $this->readStrategy = $strategy;
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
            if ($this->getStatus() == SessionStatus::KILLED) {
                $this->reGenerateID();
                $this->initNewSessionVars();

                return;
            }

            $metaEntry = SessionsManager::getStorage()->read($this->getId(), '_meta');

            if ($metaEntry === null) {
                $this->initNewSessionVars();
            } else {
                $meta = $metaEntry['value'];

                if (!is_array($meta)) {
                    $this->initNewSessionVars();

                    return;
                }

                $this->startedAt = $meta['startedAt'] ?? time();
                $this->lifeTime = $meta['lifeTime'] ?? self::DEFAULT_SESSION_DURATION / 60;
                $this->isRef = $meta['isRef'] ?? false;
                $this->langCode = $meta['langCode'] ?? '';
                $this->resumedAt = time();
                $this->sessionStatus = SessionStatus::RESUMED;
                $this->passedTime = $this->resumedAt - $this->startedAt;

                // Populate local snapshot for non-REALTIME strategies.
                if ($this->readStrategy !== ReadStrategy::REALTIME) {
                    $all = SessionsManager::getStorage()->readAll($this->getId());

                    foreach ($all as $k => $entry) {
                        if ($k !== '_meta') {
                            $decrypted = $this->decryptValue($entry['value']);
                            $this->localSnapshot[$k] = $decrypted;
                            // Keep legacy array in sync.
                            $this->sessionVariables[$k] = $decrypted;
                        }
                    }
                }

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
            SessionsManager::getStorage()->destroy($this->getId());
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
    private function decryptLegacy(string $serialized): ?string {
        $split = explode('_', $serialized, 2);

        if (count($split) !== 2) {
            return null;
        }

        $len = intval($split[0]);
        $data = $split[1];
        $cipherMeth = 'aes-256-ctr';

        if (in_array($cipherMeth, openssl_get_cipher_methods())) {
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'Other';
            $key = $this->getId().$userAgent;
            $iv = substr(hash('sha256', $key), 0, 16);
            $decrypted = openssl_decrypt(substr($data, 0, $len), $cipherMeth, $key, 0, $iv);

            if ($decrypted !== false && strlen($decrypted) > 0) {
                $decoded = base64_decode(substr($decrypted, 0, $len), true);

                if ($decoded !== false && $decoded !== '') {
                    return substr($decrypted, 0, $len);
                }
            }
        }

        // Try as unencrypted legacy (no openssl or decryption produced invalid data)
        return $data;
    }
    private function decryptSession(string $encoded): ?string {
        $sessionKey = defined('SESSION_KEY') ? SESSION_KEY : null;

        if ($sessionKey === null || $sessionKey === '') {
            return null;
        }

        $raw = base64_decode($encoded, true);

        if ($raw === false || strlen($raw) < 29) {
            return null;
        }

        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $ciphertext = substr($raw, 28);

        $key = hash('sha256', $sessionKey.$this->getId(), true);
        $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        return $plaintext !== false ? $plaintext : null;
    }
    /**
     * Decrypts a session value that was encrypted by encryptValue().
     *
     * Returns the original $stored value unchanged when it is not in the
     * expected encrypted format or when decryption fails (wrong key, corrupted
     * data). This makes the method safe to call on legacy plaintext values.
     *
     * @param mixed $stored The stored (potentially encrypted) value.
     *
     * @return mixed The decrypted value, or $stored on failure.
     */
    private function decryptValue(mixed $stored): mixed {
        if (!is_string($stored) || !str_starts_with($stored, 'ENC:')) {
            return $stored;
        }

        $sessionKey = defined('SESSION_KEY') ? SESSION_KEY : null;

        if ($sessionKey === null || $sessionKey === '') {
            return $stored;
        }

        $raw = base64_decode(substr($stored, 4), true);

        if ($raw === false || strlen($raw) < 29) {
            return $stored;
        }

        $key = hash('sha256', $sessionKey.$this->getId(), true);
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $ciphertext = substr($raw, 28);

        $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($plaintext === false) {
            return $stored;
        }

        $decoded = json_decode($plaintext, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $plaintext;
    }
    /**
     * Encrypts a session value using AES-256-GCM when SESSION_KEY is defined.
     *
     * The value is JSON-encoded before encryption to handle any PHP type.
     * Key derivation: SHA-256(SESSION_KEY + sessionId) — each session has a
     * unique key, matching the scheme used for old whole-blob encryption.
     *
     * Returns the value unchanged when SESSION_KEY is not set.
     *
     * @param mixed $value The value to encrypt.
     *
     * @return mixed The encrypted base64 string prefixed with 'ENC:', or the
     *               original value if SESSION_KEY is not set.
     */
    private function encryptValue(mixed $value): mixed {
        $sessionKey = defined('SESSION_KEY') ? SESSION_KEY : null;

        if ($sessionKey === null || $sessionKey === '') {
            return $value;
        }

        $plaintext = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $key = hash('sha256', $sessionKey.$this->getId(), true);
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        return 'ENC:'.base64_encode($iv.$tag.$ciphertext);
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
        $this->localSnapshot = [];
        $this->resumedAt = time();
        $this->startedAt = time();
        $this->sessionStatus = SessionStatus::NEW;
        $this->initLang();
        $this->persistMeta();
    }
    /**
     * Persists session metadata to the _meta key in storage.
     */
    private function persistMeta(): void {
        SessionsManager::getStorage()->write(
            $this->getId(),
            '_meta',
            [
                'startedAt' => $this->startedAt,
                'lifeTime' => $this->lifeTime,
                'isRef' => $this->isRef,
                'langCode' => $this->langCode,
            ],
            null,
            ConflictStrategy::LAST_WRITE_WINS
        );
    }
    private function restoreFromPlaintext(string $plaintext): bool {
        set_error_handler(function ($errNo, $errStr)
        {
            throw new SessionException($errStr, $errNo);
        });

        try {
            $sessionObj = unserialize(base64_decode($plaintext));
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

        return false;
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
    /**
     * Writes a key using RETRY_WITH_CALLBACK: re-reads current value and calls
     * the callback to compute the new value, retrying up to 5 times.
     */
    private function writeWithRetry(string $key, mixed $initialVal, callable $callback, int $maxRetries = 5): bool {
        for ($i = 0; $i < $maxRetries; $i++) {
            $current = SessionsManager::getStorage()->read($this->getId(), $key);
            $currentVersion = $current !== null ? $current['version'] : null;
            // Decrypt the current value so the callback works with plaintext.
            $currentDecrypted = $current !== null ? $this->decryptValue($current['value']) : null;
            $newVal = $callback($currentDecrypted);

            try {
                SessionsManager::getStorage()->write(
                    $this->getId(),
                    $key,
                    $this->encryptValue($newVal),
                    $currentVersion,
                    ConflictStrategy::REJECT
                );

                if ($this->readStrategy !== ReadStrategy::REALTIME) {
                    $this->localSnapshot[$key] = $newVal;
                }

                $this->sessionVariables[$key] = $newVal;

                return true;
            } catch (SessionConflictException $e) {
                // Another process modified the key; retry.
                continue;
            }
        }

        return false;
    }
}
