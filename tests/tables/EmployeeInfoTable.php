<?php
namespace tables;

use webfiori\database\mysql\MySQLTable;

/**
 * Description of UserInfoTable
 *
 */
class EmployeeInfoTable extends MySQLTable {
    public function __construct() {
        parent::__construct('users');
        $this->addColumns([
            'id' => [
                'type' => 'int',
            ],
            'email' => [
                'type' => 'varchar',
                'size' => 128
            ],
            'first-name' => [
                'type' => 'varchar',
                'size' => 128
            ],
            'last-name' => [
                'type' => 'varchar',
                'size' => 128,
                'is-null' => true
            ],
            'joining-date' => [
                'type' => 'datetime',
            ],
            'created-on' => [
                'type' => 'timestamp',
                'default' => 'now()'
            ]
        ]);
    }
}
