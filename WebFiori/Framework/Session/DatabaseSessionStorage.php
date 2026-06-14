<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Session;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\DatabaseException;
use WebFiori\Framework\Exceptions\SessionException;

/**
 * A session storage engine which uses database to store session state.
 *
 * @author Ibrahim
 *
 * @version 2.0
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
     * Creates new instance of the class.
     *
     * @param string|ConnectionInfo $connection The name of the database connection to use,
     * or a ConnectionInfo object directly.
     *
     * @throws SessionException
     * @since 1.0
     */
    public function __construct($connection = 'sessions-connection') {
        try {
            $this->dbController = new SessionDB($connection);
        } catch (DatabaseException $ex) {
            $connName = $connection instanceof ConnectionInfo ? $connection->getName() : $connection;

            if (strpos($ex->getMessage(), $connName) !== false) {
                throw new SessionException("Connection '$connName' was not found in application configuration.");
            } else {
                throw $ex;
            }
        }
    }
    /**
     * Drop the Tables which are used to store session information.
     *
     * The method will drop two Tables, the table 'session_data' and the
     * table 'sessions'.
     */
    public function dropTables() {
        $this->getController()->table('session_data')->drop()->execute();
        $this->getController()->table('sessions')->drop()->execute();
    }
    /**
     * Removes sessions that are older than the given time.
     *
     * @param string $olderThan A date string in the format 'Y-m-d H:i:s'.
     * Sessions not modified since this time should be removed.
     *
     * @param int $maxCount Maximum number of sessions to remove in this run.
     * 0 means no limit.
     */
    public function gc(string $olderThan, int $maxCount = 0) {
        $this->dbController->gc($olderThan, $maxCount);
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
    /**
     * Reads session state.
     *
     * @param string $sessionId The unique identifier of the session.
     *
     * @return string|null The method will return a string that represents the
     * session if it was found. If no session was found which has the given ID, the method
     * will return null.
     *
     * @since 1.0
     */
    public function read(string $sessionId) {
        return $this->dbController->getSession($sessionId);
    }
    /**
     * Stops a session and remove its state from the database.
     *
     * @param string $sessionId The unique identifier of the session.
     *
     * @since 1.0
     */
    public function remove(string $sessionId) {
        $this->dbController->removeSession($sessionId);
    }
    /**
     * Store session state.
     *
     * @param string $sessionId The ID of the session that will be stored.
     *
     * @param string $serializedSession A string that represents the session in
     * serialized form.
     *
     * @since 1.0
     */
    public function save(string $sessionId, string $serializedSession) {
        $this->dbController->saveSession($sessionId, $serializedSession);
    }
}
