<?php
namespace webfiori\tests\database;
use phMysql\MySQLQuery;
use phMysql\MySQLTable;
use phMysql\Column;
use webfiori\entity\User;
/**
 * Description of UsersQuery
 *
 * @author Ibrahim
 */
class UsersQuery extends MySQLQuery{
    
    /**
     *
     * @var MySQLTable 
     */
    private $table;
    public function __construct() {
        parent::__construct();
        $this->table = new MySQLTable('user_meta');
        $this->table->addDefaultCols([
            'id'=>[
                'key-name'=>'user-id',
                'db-name'=>'id'
            ],
            'created-on'=>[],
            'last-updated'=>[]
        ]);
        $this->table->addColumn('username', new Column('username', 'varchar', 125));
        $this->table->addColumn('password', new Column('pass', 'varchar', 125));
    }
    /**
     * @return MySQLTable Description
     */
    public function getStructure() {
        return $this->table;
    }
    /**
     * 
     * @param User $user
     */
    public function addUser($user) {
        $this->insertRecord([
            'username'=>$user->getUserName(),
            'password'=>$user->getPassword()
        ]);
    }
}
