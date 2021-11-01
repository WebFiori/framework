<?php
namespace webfiori\framework\session;

use webfiori\database\mssql\MSSQLTable;
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
                'type' => 'datetime2',
                'default' => 'now',
                'comment' => 'The date and time at which the session started.',
            ],
            'last-used' => [
                'type' => 'datetime2',
                'default' => 'now',
                'comment' => 'The date and time at which the user has activity during the session.',
            ],
            'session-data' => [
                'type' => 'varbinary',
                'comment' => 'Session state.',
            ],
        ]);
    }
}

return __NAMESPACE__;

