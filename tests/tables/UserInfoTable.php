<?php
namespace tables;

use webfiori\database\mysql\MySQLTable;

/**
 * Description of UserInfoTable
 *
 */
class UserInfoTable extends MySQLTable {
    public function __construct() {
        parent::__construct('users');
        $this->addColumns([
            'id' => [
                'type' => 'int',
                'primary' => true,
                'auto-inc' => true
            ],
            'email' => [
                'type' => 'varchar',
                'size' => 128
            ]
        ]);
    }
}
