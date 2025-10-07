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
namespace webfiori\framework\session;

use WebFiori\Database\MySql\MySQLTable;

/**
 * A class which represents the database table '`sessions`'.
 * The table which is associated with this class will have the following columns:
 * <ul>
 * <li><b>s-id</b>: Name in database: '`s_id`'. Data type: 'varchar'.</li>
 * <li><b>started-at</b>: Name in database: '`started_at`'. Data type: 'timestamp'.</li>
 * <li><b>last-used</b>: Name in database: '`last_used`'. Data type: 'datetime'.</li>
 * <li><b>session-data</b>: Name in database: '`session_data`'. Data type: 'mediumtext'.</li>
 * </ul>
 */
class MySQLSessionsTable extends MySQLTable {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('sessions');
        $this->setComment('This table is used to store session related data');
        $this->addColumns([
            's-id' => [
                'type' => 'varchar',
                'size' => '128',
                'primary' => true,
                'is-unique' => true,
                'comment' => 'The unique ID of the session.',
            ],
            'started-at' => [
                'type' => 'timestamp',
                'default' => 'now()',
                'comment' => 'The date and time at which the session started.',
            ],
            'last-used' => [
                'type' => 'datetime',
                'default' => 'now()',
                'comment' => 'The date and time at which the user has activity during the session.',
            ],
        ]);
    }
}

return __NAMESPACE__;
