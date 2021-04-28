<?php
namespace app\database;

use webfiori\database\mysql\MySQLTable;

/**
 * A basic table which can be used to hold users information.
 * 
 * The developer can modify the class as needed and add extra columns to the table.
 * To create this table in your database, make sure that connection information 
 * is added in your 'AppConfig.php' file and then run the command 
 * 'php webfiori create'.
 *
 * @author Ibrahim
 */
class UsersTable extends MySQLTable {
    public function __construct() {
        parent::__construct('users');

        $this->addColumns([
            'user-id' => [
                'type' => 'int',
                'size' => 11,
                'primary' => true,
                'auto-inc' => true
            ],
            'email' => [
                'type' => 'varchar',
                'size' => 256,
                'is-unique' => true
            ],
            'username' => [
                'type' => 'varchar',
                'size' => 20,
                'is-unique' => true
            ],
            'password' => [
                'type' => 'varchar',
                'size' => 256
            ],
            'created-on' => [
                'type' => 'timestamp',
                'default' => 'now()',
            ],
            'last-success-login' => [
                'type' => 'datetime',
                'is-null' => true
            ],
            'privileges' => [
                'type' => 'varchar',
                'size' => 5000,
                'is-null' => true
            ]
        ]);
    }
}

return __NAMESPACE__;
