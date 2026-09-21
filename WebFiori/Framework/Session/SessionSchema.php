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

use WebFiori\Database\Factory\TableFactory;
use WebFiori\Database\Table;

/**
 * A class that provides unified session table schema for all supported database engines.
 *
 * @author Ibrahim
 */
class SessionSchema {
    /**
     * Creates the 'session_data' table for the given database type.
     *
     * @param string $dbType One of 'mysql', 'mssql', or 'sqlite'.
     *
     * @return Table
     */
    public static function createSessionDataTable(string $dbType): Table {
        $sessionsTable = self::createSessionsTable($dbType);
        $table = TableFactory::create($dbType, 'session_data', [
            's_id' => [
                'type' => 'varchar',
                'size' => 128,
                'primary' => true,
                'comment' => 'The ID of the session. Taken from main sessions table.',
            ],
            'chunk_number' => [
                'type' => 'int',
                'primary' => true,
                'comment' => 'The number of data chunk.',
            ],
            'data' => [
                'type' => 'varchar',
                'size' => 1000,
                'comment' => 'One data chunk of size 1000.',
            ],
        ]);
        $table->addReference($sessionsTable, ['s_id'], 'session_data_fk', 'cascade', 'cascade');

        return $table;
    }
    /**
     * Creates the 'session_kv_data' table for per-key session storage.
     *
     * Used by DatabaseSessionStorage in the new per-key mode (3.1.0+).
     * One row per session key; supports real-time reads and optimistic
     * concurrency control via the version column.
     *
     * @param string $dbType One of 'mysql', 'mssql', or 'sqlite'.
     *
     * @return Table
     */
    public static function createSessionKvDataTable(string $dbType): Table {
        $sessionsTable = self::createSessionsTable($dbType);
        $table = TableFactory::create($dbType, 'session_kv_data', [
            's_id' => [
                'type' => 'varchar',
                'size' => 128,
                'primary' => true,
                'comment' => 'The ID of the session.',
            ],
            'skey' => [
                'type' => 'varchar',
                'size' => 255,
                'primary' => true,
                'comment' => 'The session key name.',
            ],
            'svalue' => [
                'type' => 'mediumtext',
                'comment' => 'The JSON-encoded session value.',
            ],
            'version' => [
                'type' => 'int',
                'default' => 1,
                'comment' => 'Version counter for optimistic concurrency control.',
            ],
            'updated_at' => [
                'type' => 'datetime',
                'default' => 'now()',
                'comment' => 'Last modification time.',
            ],
        ]);
        $table->addReference($sessionsTable, ['s_id'], 'session_kv_data_fk', 'cascade', 'cascade');

        return $table;
    }
    /**
     * Creates the 'sessions' table for the given database type.
     *
     * @param string $dbType One of 'mysql', 'mssql', or 'sqlite'.
     *
     * @return Table
     */
    public static function createSessionsTable(string $dbType): Table {
        return TableFactory::create($dbType, 'sessions', [
            's_id' => [
                'type' => 'varchar',
                'size' => 128,
                'primary' => true,
                'is-unique' => true,
                'comment' => 'The unique ID of the session.',
            ],
            'started_at' => [
                'type' => 'datetime',
                'default' => 'now()',
                'comment' => 'The date and time at which the session started.',
            ],
            'last_used' => [
                'type' => 'datetime',
                'default' => 'now()',
                'comment' => 'The date and time at which the user had activity during the session.',
            ],
        ]);
    }
}
