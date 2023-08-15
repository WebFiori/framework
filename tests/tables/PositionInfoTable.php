<?php
namespace tables;

use webfiori\database\mysql\MySQLTable;

/**
 * Description of UserInfoTable
 *
 */
class PositionInfoTable extends MySQLTable {
    public function __construct() {
        parent::__construct('users');
        $this->addColumns([
            'id' => [
                'type' => 'int',
                'unique' => true
            ],
            'name' => [
                'type' => 'varchar',
                'size' => 128,
                'unique' => true
            ],
            'company' => [
                'type' => 'varchar',
                'size' => 128
            ],
            'salary' => [
                'type' => 'decimal',
                'size' => 10,
                'scale' => 2,
                'default' => 0
            ],
            'created-on' => [
                'type' => 'timestamp',
                'default' => 'now()'
            ],
            'last-updated' => [
                'type' => 'datetime',
                'is-null' => true
            ]
        ]);
    }
}
