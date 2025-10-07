<?php
namespace webfiori\tests\database;

use phMysql\MySQLColumn;
use phMysql\MySQLQuery;
use phMysql\MySQLTable;
use webfiori\framework\User;
/**
 * Description of UsersQuery
 *
 * @author Ibrahim
 */
class UsersQuery extends MySQLQuery {
    /**
     *
     * @var MySQLTable
     */
    private $table;
    public function __construct() {
        parent::__construct();
        $this->table = new MySQLTable('user_meta');
        $this->table->addDefaultCols([
            'id' => [
                'key-name' => 'user-id',
                'db-name' => 'id'
            ],
            'created-on' => [],
            'last-updated' => []
        ]);
        $this->table->addColumn('username', new MySQLColumn('username', 'varchar', 125));
        $this->table->addColumn('password', new MySQLColumn('pass', 'varchar', 125));
        $this->setTable($this->table);
    }
    /**
     *
     * @param User $user
     */
    public function addUser($user) {
        $this->insertRecord([
            'username' => $user->getUserName(),
            'password' => $user->getPassword()
        ]);
    }
    /**
     * @return MySQLTable Description
     */
    public function getStructure() {
        return $this->table;
    }
}
