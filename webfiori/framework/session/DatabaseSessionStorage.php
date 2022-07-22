<?php
/**
 * This file is licensed under MIT License.
 * 
 * Copyright (c) 2019 Ibrahim BinAlshikh
 * 
 * For more information on the license, please visit: 
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 * 
 */
namespace webfiori\framework\session;

/**
 * A session storage engine which uses database to store session state.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 * 
 * @since 2.1.0
 */
class DatabaseSessionStorage implements SessionStorage {
    /**
     *
     * @var SessionOperations 
     */
    private $dbController;
    /**
     * Creates new instance of the class
     * 
     * @since 1.0
     */
    public function __construct() {
        $this->dbController = new SessionOperations();
    }
    /**
     * Removes all inactive sessions from the database.
     */
    public function gc() {
        $this->dbController->gc();
    }
    /**
     * Reads session state.
     * 
     * 
     * @param string $sesstionId The unique identifier of the session.
     * 
     * @return string|null The method will return a string that represents the 
     * session if it was found. If no session was found which has the given ID, the method 
     * will return null.
     * 
     * @since 1.0
     */
    public function read($sesstionId) {
        return $this->dbController->getSession($sesstionId);
    }
    /**
     * Stops a session and remove its state from the database.
     * 
     * @param string $sesstionId The unique identifier of the session.
     * 
     * @since 1.0
     */
    public function remove($sesstionId) {
        $this->dbController->removeSession($sesstionId);
    }
    /**
     * Store session state.
     * 
     * 
     * @param string $sessionId The ID of the session that will be stored.
     * 
     * @param string $serializedSession A string that represents the session in 
     * serilized form.
     * 
     * @since 1.0
     */
    public function save($sessionId, $serializedSession) {
        $this->dbController->saveSession($sessionId, $serializedSession);
    }
}
