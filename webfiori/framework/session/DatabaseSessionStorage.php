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

use webfiori\database\DatabaseException;
use webfiori\framework\exceptions\SessionException;

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
     * @var SessionDB 
     */
    private $dbController;
    /**
     * Creates new instance of the class
     * 
     * @since 1.0
     */
    public function __construct() {
        try {
            $this->dbController = new SessionDB();
        } catch (DatabaseException $ex) {
            if ($ex->getMessage() == "No connection was found which has the name 'sessions-connection'.") {
                throw new SessionException("Connection 'sessions-connection' was not found in application configuration.", $ex->getCode(), $ex);
            }
            throw new DatabaseException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
    /**
     * Drop the tables which are used to store sessions information.
     * 
     * The method will drop two tables, the table 'session_data' and the
     * table 'sessions'.
     */
    public function dropTables() {
        $this->getController()->table('session_data')->drop()->execute();
        $this->getController()->table('sessions')->drop()->execute();
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
    /**
     * Returns the instance at which the storage is using to send queries to
     * database and read sessions.
     * 
     * @return SessionDB An instance of the class SessionDB.
     */
    public function getController() : SessionDB {
        return $this->dbController;
    }
}
