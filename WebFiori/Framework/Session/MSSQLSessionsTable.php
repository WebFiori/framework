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

use WebFiori\Database\MsSql\MSSQLTable;
/**
 * A class which represents the database table 'sessions'.
 * The table which is associated with this class will have the following columns:
 * <ul>
 * <li><b>s-id</b>: Name in database: 's_id'. Data type: 'varchar'.</li>
 * <li><b>started-at</b>: Name in database: 'started_at'. Data type: 'timestamp2'.</li>
 * <li><b>last-used</b>: Name in database: 'last_used'. Data type: 'datetime2'.</li>
 * <li><b>session-data</b>: Name in database: 'session_data'. Data type: 'varchar'.</li>
 * </ul>
 */
class MSSQLSessionsTable extends MSSQLTable {
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('sessions');
        $this->setComment('This table is used to store session related info.');
        $this->addColumns([
            's-id' => [
                'type' => 'nvarchar',
                'size' => '128',
                'primary' => true,
                'is-unique' => true,
                'comment' => 'Session identifier. Each session must have unique ID.',
            ],
            'started-at' => [
                'type' => 'datetime2',
                'default' => 'now',
                'comment' => 'The date and time at which the session was initiated.',
            ],
            'last-used' => [
                'type' => 'datetime2',
                'default' => 'now',
                'comment' => 'The date and time at which the session was used.',
            ],
        ]);
    }
}

return __NAMESPACE__;
