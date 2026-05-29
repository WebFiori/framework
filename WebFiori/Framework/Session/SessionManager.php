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

/**
 * A class which is used to manage user sessions.
 *
 * This is the concrete implementation. For static access, use SessionsManager facade.
 *
 * @author Ibrahim
 */
class SessionManager {
    /**
     * @var Session|null
     */
    private ?Session $activeSession = null;
    /**
     * @var int
     */
    private int $gcProbability = 1;
    /**
     * @var int
     */
    private int $gcDivisor = 100;
    /**
     * @var int
     */
    private int $gcBatchSize = 100;
    /**
     * @var array
     */
    private array $sessionsArr = [];
    /**
     * @var SessionStorage
     */
    private SessionStorage $sessionStorage;

    /**
     * Creates new instance of the class.
     *
     * @param SessionStorage $storage The storage engine to use for sessions.
     */
    public function __construct(SessionStorage $storage) {
        $this->sessionStorage = $storage;
    }
    /**
     * Saves the state of the active session and close it.
     */
    public function close(): void {
        $active = $this->getActiveSession();

        if ($active !== null) {
            $active->close();
            $this->activeSession = null;
        }
    }
    /**
     * Destroy the active session.
     */
    public function destroy(): void {
        $active = $this->getActiveSession();

        if ($active !== null) {
            $active->kill();
            $this->activeSession = null;
        }
    }
    /**
     * Returns the value of a session variable.
     *
     * @param string $varName The name of the variable.
     *
     * @return null|mixed
     */
    public function get(string $varName) {
        $active = $this->getActiveSession();

        if ($active !== null) {
            return $active->get($varName);
        }

        return null;
    }
    /**
     * Returns the currently active session.
     *
     * @return Session|null
     */
    public function getActiveSession(): ?Session {
        if ($this->activeSession !== null) {
            return $this->activeSession;
        }

        foreach ($this->sessionsArr as $session) {
            $status = $session->getStatus();

            if ($status == SessionStatus::NEW || $status == SessionStatus::RESUMED) {
                $this->activeSession = $session;

                return $session;
            }
        }

        return null;
    }
    /**
     * Returns an array that contains cookies headers values.
     *
     * @return array
     */
    public function getCookiesHeaders(): array {
        $retVal = [];

        foreach ($this->sessionsArr as $session) {
            $retVal[] = $session->getCookieHeader();
        }

        return $retVal;
    }
    /**
     * Returns a date string that represents the GC threshold time.
     *
     * @return string A date string in the format 'Y-m-d H:i:s'.
     */
    public function getGCTime(): string {
        $olderThan = time() - (Session::DEFAULT_SESSION_DURATION * 60 * 2);
        $fromEnv = getenv('SESSION_GC') !== false ? intval(getenv('SESSION_GC')) : 0;
        $fromConst = defined('SESSION_GC') && intval(SESSION_GC) > 0 ? intval(SESSION_GC) : 0;

        if ($fromEnv != 0) {
            $olderThan = $fromEnv;
        } else if ($fromConst != 0) {
            $olderThan = $fromConst;
        }

        return date('Y-m-d H:i:s', $olderThan);
    }
    /**
     * Sets the GC probability and divisor.
     *
     * @param int $probability The numerator. Must be >= 0.
     * @param int $divisor The denominator. Must be >= 0. Set to 0 to disable GC.
     */
    public function setGCProbability(int $probability, int $divisor): void {
        if ($probability >= 0) {
            $this->gcProbability = $probability;
        }

        if ($divisor >= 0) {
            $this->gcDivisor = $divisor;
        }
    }
    /**
     * Returns the GC probability numerator.
     *
     * @return int
     */
    public function getGCProbability(): int {
        return $this->gcProbability;
    }
    /**
     * Returns the GC probability divisor.
     *
     * @return int
     */
    public function getGCDivisor(): int {
        return $this->gcDivisor;
    }
    /**
     * Sets the maximum number of sessions to remove per GC run.
     *
     * @param int $size Maximum number. 0 means no limit.
     */
    public function setGCBatchSize(int $size): void {
        if ($size >= 0) {
            $this->gcBatchSize = $size;
        }
    }
    /**
     * Returns the maximum number of sessions to remove per GC run.
     *
     * @return int
     */
    public function getGCBatchSize(): int {
        return $this->gcBatchSize;
    }
    /**
     * Returns the ID of a session from a cookie given its name.
     *
     * @param string $sessionName The name of the session.
     *
     * @return bool|string
     */
    public function getSessionIDFromCookie(string $sessionName) {
        $sid = filter_input(INPUT_COOKIE, $sessionName);

        if ($sid !== null && $sid !== false) {
            return $sid;
        }

        return false;
    }
    /**
     * Return session ID from session cookie, get or post parameter.
     *
     * @param string $sessionName The name of the session.
     *
     * @return string|bool
     */
    public function getSessionIDFromRequest(string $sessionName) {
        $trimmedSName = trim($sessionName);
        $sid = $this->getSessionIDFromCookie($trimmedSName);

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
     * @return array
     */
    public function getSessions(): array {
        return $this->sessionsArr;
    }
    /**
     * Returns storage engine which is used to store sessions state.
     *
     * @return SessionStorage
     */
    public function getStorage(): SessionStorage {
        return $this->sessionStorage;
    }
    /**
     * Checks if the active session has a cookie or not.
     *
     * @return bool
     */
    public function hasCookie(): bool {
        $active = $this->getActiveSession();

        if ($active !== null) {
            $sid = $this->getSessionIDFromCookie($active->getName());

            return $sid !== false;
        }

        return false;
    }
    /**
     * Checks if the manager has specific session or not.
     *
     * @param string $sName The name of the session.
     *
     * @return bool
     */
    public function hasSession(string $sName): bool {
        try {
            $trimmed = trim($sName);

            if (!$this->checkLoadedSessions($trimmed)) {
                return $this->checkAndLoadFromCookie($trimmed);
            }
        } catch (SessionException $e) {
            return false;
        }

        return true;
    }
    /**
     * Generate new ID for the active session.
     *
     * @return string|null
     */
    public function newId(): ?string {
        $active = $this->getActiveSession();

        if ($active !== null) {
            return $active->reGenerateID();
        }

        return null;
    }
    /**
     * Stores the status of all sessions and pause them.
     */
    public function pauseAll(): void {
        $this->pauseSessions();
    }
    /**
     * Retrieves the value of a session variable and removes it from the session.
     *
     * @param string $varName The name of the variable.
     *
     * @return mixed|null
     */
    public function pull(string $varName) {
        $active = $this->getActiveSession();

        if ($active !== null) {
            return $active->pull($varName);
        }

        return null;
    }
    /**
     * Removes the value of a session variable from the active session.
     *
     * @param string $varName The name of the variable.
     *
     * @return bool
     */
    public function remove(string $varName): bool {
        $active = $this->getActiveSession();

        if ($active !== null) {
            return $active->remove($varName);
        }

        return false;
    }
    /**
     * Reset sessions manager to defaults.
     */
    public function reset(): void {
        $this->sessionsArr = [];
        $this->activeSession = null;
        $this->gcProbability = 1;
        $this->gcDivisor = 100;
        $this->gcBatchSize = 100;
    }
    /**
     * Sets session variable.
     *
     * @param string $varName The name of the variable.
     * @param mixed $value The value of the variable.
     *
     * @return bool
     */
    public function set(string $varName, $value): bool {
        $active = $this->getActiveSession();

        if ($active !== null) {
            return $active->set($varName, $value);
        }

        return false;
    }
    /**
     * Sets sessions storage engine.
     *
     * @param SessionStorage $storage The new session storage.
     */
    public function setStorage(SessionStorage $storage): void {
        $this->sessionStorage = $storage;
    }
    /**
     * Starts new session or resumes an existing one.
     *
     * @param string $sessionName The name of the session.
     * @param array $options Session options (duration, refresh).
     *
     * @throws SessionException
     */
    public function start(string $sessionName, array $options = []): void {
        $this->pauseSessions();

        if (!$this->hasSession($sessionName)) {
            $options[SessionOption::NAME] = $sessionName;
            $s = new Session($options);
            $s->start();
            $this->sessionsArr[] = $s;
        } else {
            foreach ($this->sessionsArr as $sessionObj) {
                if ($sessionObj->getName() == $sessionName) {
                    $sessionObj->start();
                }
            }
        }
    }
    /**
     * Validate the current status of the storage.
     */
    public function validateStorage(): void {
        foreach ($this->sessionsArr as $session) {
            $status = $session->getStatus();

            if ($status == SessionStatus::NEW ||
                $status == SessionStatus::PAUSED ||
                $status == SessionStatus::RESUMED) {
                $this->sessionStorage->save($session->getId(), $session->serialize());
            } else if ($status == SessionStatus::KILLED) {
                $this->sessionStorage->remove($session->getId());
            }
        }

        if ($this->shouldRunGC()) {
            $this->sessionStorage->gc($this->getGCTime(), $this->gcBatchSize);
        }
    }

    private function shouldRunGC(): bool {
        if ($this->gcDivisor <= 0) {
            return false;
        }

        if ($this->gcProbability <= 0) {
            return false;
        }

        return random_int(1, $this->gcDivisor) <= $this->gcProbability;
    }

    private function checkAndLoadFromCookie(string $sName): bool {
        $sId = $this->getSessionIDFromRequest($sName);

        if ($sId !== false) {
            $tempSession = new Session([
                'session-id' => $sId,
                'name' => 'x'
            ]);
            $tempSession->start();

            if ($tempSession->getStatus() == SessionStatus::RESUMED) {
                $this->sessionsArr[] = $tempSession;

                return true;
            }
        }

        return false;
    }

    private function checkLoadedSessions(string $sName): bool {
        foreach ($this->sessionsArr as $sessionObj) {
            if ($sessionObj->getName() == $sName) {
                return true;
            }
        }

        return false;
    }

    private function pauseSessions(): void {
        $this->activeSession = null;

        foreach ($this->sessionsArr as $session) {
            $session->close();
        }
    }
}
