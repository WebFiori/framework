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

/**
 * A static facade for the SessionManager class.
 *
 * Provides a convenient static API that delegates to a default SessionManager instance.
 * For dependency injection or testing, use SessionManager directly.
 *
 * @author Ibrahim
 */
class SessionsManager {
    /**
     * @var SessionManager|null
     */
    private static ?SessionManager $inst = null;
    /**
     * Returns the default SessionManager instance.
     *
     * @return SessionManager
     */
    public static function getInstance(): SessionManager {
        if (self::$inst === null) {
            self::$inst = self::createDefault();
        }

        return self::$inst;
    }
    /**
     * Replaces the default SessionManager instance.
     *
     * @param SessionManager $manager The manager to use as default.
     */
    public static function setManager(SessionManager $manager): void {
        self::$inst = $manager;
    }
    /**
     * @see SessionManager::close()
     */
    public static function close(): void {
        self::getInstance()->close();
    }
    /**
     * @see SessionManager::destroy()
     */
    public static function destroy(): void {
        self::getInstance()->destroy();
    }
    /**
     * @see SessionManager::get()
     */
    public static function get(string $varName) {
        return self::getInstance()->get($varName);
    }
    /**
     * @see SessionManager::getActiveSession()
     */
    public static function getActiveSession(): ?Session {
        return self::getInstance()->getActiveSession();
    }
    /**
     * @see SessionManager::getCookiesHeaders()
     */
    public static function getCookiesHeaders(): array {
        return self::getInstance()->getCookiesHeaders();
    }
    /**
     * @see SessionManager::getGCTime()
     */
    public static function getGCTime(): string {
        return self::getInstance()->getGCTime();
    }
    /**
     * @see SessionManager::setGCProbability()
     */
    public static function setGCProbability(int $probability, int $divisor): void {
        self::getInstance()->setGCProbability($probability, $divisor);
    }
    /**
     * @see SessionManager::getGCProbability()
     */
    public static function getGCProbability(): int {
        return self::getInstance()->getGCProbability();
    }
    /**
     * @see SessionManager::getGCDivisor()
     */
    public static function getGCDivisor(): int {
        return self::getInstance()->getGCDivisor();
    }
    /**
     * @see SessionManager::setGCBatchSize()
     */
    public static function setGCBatchSize(int $size): void {
        self::getInstance()->setGCBatchSize($size);
    }
    /**
     * @see SessionManager::getGCBatchSize()
     */
    public static function getGCBatchSize(): int {
        return self::getInstance()->getGCBatchSize();
    }
    /**
     * @see SessionManager::getSessionIDFromCookie()
     */
    public static function getSessionIDFromCookie(string $sessionName) {
        return self::getInstance()->getSessionIDFromCookie($sessionName);
    }
    /**
     * @see SessionManager::getSessionIDFromRequest()
     */
    public static function getSessionIDFromRequest(string $sessionName) {
        return self::getInstance()->getSessionIDFromRequest($sessionName);
    }
    /**
     * @see SessionManager::getSessions()
     */
    public static function getSessions(): array {
        return self::getInstance()->getSessions();
    }
    /**
     * @see SessionManager::getStorage()
     */
    public static function getStorage(): SessionStorage {
        return self::getInstance()->getStorage();
    }
    /**
     * @see SessionManager::hasCookie()
     */
    public static function hasCookie(): bool {
        return self::getInstance()->hasCookie();
    }
    /**
     * @see SessionManager::hasSession()
     */
    public static function hasSession(string $sName): bool {
        return self::getInstance()->hasSession($sName);
    }
    /**
     * @see SessionManager::newId()
     */
    public static function newId(): ?string {
        return self::getInstance()->newId();
    }
    /**
     * @see SessionManager::pauseAll()
     */
    public static function pauseAll(): void {
        self::getInstance()->pauseAll();
    }
    /**
     * @see SessionManager::pull()
     */
    public static function pull(string $varName) {
        return self::getInstance()->pull($varName);
    }
    /**
     * @see SessionManager::remove()
     */
    public static function remove(string $varName): bool {
        return self::getInstance()->remove($varName);
    }
    /**
     * Destroys the default instance and creates a fresh one.
     */
    public static function reset(): void {
        self::$inst = null;
    }
    /**
     * @see SessionManager::set()
     */
    public static function set(string $varName, $value): bool {
        return self::getInstance()->set($varName, $value);
    }
    /**
     * @see SessionManager::setStorage()
     */
    public static function setStorage(SessionStorage $storage): void {
        self::getInstance()->setStorage($storage);
    }
    /**
     * @see SessionManager::start()
     */
    public static function start(string $sessionName, array $options = []): void {
        self::getInstance()->start($sessionName, $options);
    }
    /**
     * @see SessionManager::validateStorage()
     */
    public static function validateStorage(): void {
        self::getInstance()->validateStorage();
    }
    /**
     * Creates the default SessionManager with appropriate storage.
     *
     * @return SessionManager
     */
    private static function createDefault(): SessionManager {
        if (defined('WF_SESSION_STORAGE')) {
            $constructor = WF_SESSION_STORAGE.'';
            $classObj = new $constructor();

            if ($classObj instanceof SessionStorage) {
                return new SessionManager($classObj);
            }
        }

        return new SessionManager(new DefaultSessionStorage());
    }
}
