<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 */
namespace WebFiori\Framework\Session;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\DatabaseException;
use WebFiori\Framework\Exceptions\SessionException;

/**
 * A session storage engine which uses a database to store session state.
 *
 * Stores each session key as an individual row in the 'session_kv_data' table,
 * enabling real-time per-key reads and optimistic concurrency control.
 *
 * Run SessionSchemaMigration::run($db) once during deployment to create the
 * required 'session_kv_data' table (the old 'session_data' chunked blob table
 * is kept for backward compatibility but is no longer used by this class).
 *
 * @author Ibrahim
 * @since 2.1.0 (original), 3.1.0 (per-key interface)
 */
class DatabaseSessionStorage implements SessionStorage {
    private SessionDB $dbController;

    /**
     * @param string|ConnectionInfo $connection The connection name or info object.
     * @throws SessionException
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
     * {@inheritdoc}
     */
    public function destroy(string $sessionId): void {
        $this->dbController->removeSession($sessionId);
    }

    /**
     * Drops all session tables (sessions, session_data, session_kv_data).
     */
    public function dropTables(): void {
        $this->dbController->dropAllTables();
    }

    /**
     * {@inheritdoc}
     */
    public function gc(string $olderThan, int $maxCount = 0): void {
        $this->dbController->gc($olderThan, $maxCount);
    }

    /**
     * Returns the underlying SessionDB controller.
     */
    public function getController(): SessionDB {
        return $this->dbController;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $sessionId, string $key): ?array {
        return $this->dbController->getSessionKey($sessionId, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function readAll(string $sessionId): array {
        return $this->dbController->getSessionKeys($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $sessionId, string $key): void {
        $this->dbController->removeSessionKey($sessionId, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function write(
        string $sessionId,
        string $key,
        mixed $value,
        string|int|null $expectedVersion,
        ConflictStrategy $strategy
    ): int {
        return $this->dbController->writeSessionKey($sessionId, $key, $value, $expectedVersion, $strategy);
    }
}
