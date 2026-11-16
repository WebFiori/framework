<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 */
namespace WebFiori\Framework\Session;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\DatabaseException;

/**
 * Helper class for migrating an existing session database schema to the
 * per-key format introduced in WebFiori Framework 3.1.0.
 *
 * Run this once during deployment after upgrading to 3.1.0+:
 *
 * ```php
 * use WebFiori\Framework\Session\SessionSchemaMigration;
 *
 * SessionSchemaMigration::run('sessions-connection');
 * ```
 *
 * What this migration does:
 * 1. Creates the 'sessions' table if it does not exist.
 * 2. Creates the 'session_kv_data' table (per-key rows with version tracking)
 *    if it does not exist.
 * 3. Adds a 'version' column to 'session_kv_data' if it is missing.
 * 4. Adds an 'updated_at' column to 'session_kv_data' if it is missing.
 * 5. Adds a nullable 'expires_at' column (and an index on it) to 'sessions'
 *    if it is missing. Garbage collection uses it to remove sessions by their
 *    own expiry time (see {@see ExpiringSessionStorage}). Until the migration
 *    is run, garbage collection falls back to the idle-time threshold.
 *
 * The legacy 'session_data' table (chunked blob) is NOT modified or removed;
 * existing sessions stored in the blob format will be treated as new sessions
 * by the per-key storage engine (they will not be automatically migrated).
 * Users will be asked to log in again after the migration.
 *
 * @since 3.1.0
 */
class SessionSchemaMigration {
    /**
     * Runs the schema migration.
     *
     * @param string|ConnectionInfo $connection The name of the database connection
     *        configured in the application, or a ConnectionInfo instance.
     *
     * @throws DatabaseException If the migration fails.
     */
    public static function run(string|ConnectionInfo $connection = 'sessions-connection'): void {
        $db = new SessionDB($connection);
        $dbType = strtolower($db->getConnectionInfo()->getDatabaseType());

        // Create the tables if they do not exist. createTable() fails when a
        // table already exists, which is the expected case on re-runs.
        foreach (['sessions', 'session_kv_data'] as $tableName) {
            try {
                $db->table($tableName)->createTable()->execute();
            } catch (DatabaseException $e) {
                // Table already exists; continue with column-level migrations.
                $db->clear();
            }
        }

        // Add 'version' column if missing.
        static::addColumnIfMissing($db, $dbType, 'session_kv_data', 'version', 'INT NOT NULL DEFAULT 1');

        // Add 'updated_at' column if missing.
        $updatedAtType = ($dbType === 'mssql') ? 'DATETIME DEFAULT GETDATE()' : 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP';
        static::addColumnIfMissing($db, $dbType, 'session_kv_data', 'updated_at', $updatedAtType);

        // Add 'expires_at' column and its index to 'sessions' if missing.
        static::addColumnIfMissing($db, $dbType, 'sessions', 'expires_at', 'DATETIME NULL');
        $db->createExpiryIndex();
    }

    private static function addColumnIfMissing(
        SessionDB $db,
        string $dbType,
        string $table,
        string $column,
        string $definition
    ): void {
        try {
            // SQL Server does not accept the COLUMN keyword in ADD.
            $keyword = $dbType === 'mssql' ? 'ADD' : 'ADD COLUMN';
            $db->raw("ALTER TABLE $table $keyword $column $definition")->execute();
        } catch (DatabaseException $e) {
            // Column likely already exists — ignore.
            $db->clear();
        }
    }
}
