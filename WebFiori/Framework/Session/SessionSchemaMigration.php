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

        // Create sessions table if not exists.
        try {
            $db->table('sessions')->createIfNotExists()->execute();
        } catch (DatabaseException $e) {
            // Table may already exist; continue.
        }

        // Create session_kv_data table if not exists.
        try {
            $db->table('session_kv_data')->createIfNotExists()->execute();
        } catch (DatabaseException $e) {
            // May fail if CREATE IF NOT EXISTS is not supported; try plain CREATE.
            try {
                $db->table('session_kv_data')->create()->execute();
            } catch (DatabaseException $innerEx) {
                // Table already exists — run column-level migrations.
            }
        }

        // Add 'version' column if missing.
        static::addColumnIfMissing($db, $dbType, 'session_kv_data', 'version', 'INT NOT NULL DEFAULT 1');

        // Add 'updated_at' column if missing.
        $updatedAtType = ($dbType === 'mssql') ? 'DATETIME DEFAULT GETDATE()' : 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP';
        static::addColumnIfMissing($db, $dbType, 'session_kv_data', 'updated_at', $updatedAtType);
    }

    private static function addColumnIfMissing(
        SessionDB $db,
        string $dbType,
        string $table,
        string $column,
        string $definition
    ): void {
        try {
            $db->query("ALTER TABLE $table ADD COLUMN $column $definition")->execute();
        } catch (DatabaseException $e) {
            // Column likely already exists — ignore.
        }
    }
}
