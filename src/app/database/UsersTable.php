<?php
namespace app\database;

use webfiori\database\mysql\MySQLTable;

/**
 * A basic table which can be used to hold users information.
 * 
 * The developer can modify the class as needed and add extra columns to the table.
 * To create this table in your database, make sure that connection information 
 * is added in your 'Config.php' file and then run the command 
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
            ]
        ]);
    }
}
return __NAMESPACE__;