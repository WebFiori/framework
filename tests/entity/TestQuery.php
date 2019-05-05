<?php
namespace webfiori\tests\entity;
use phMysql\MySQLQuery;
use phMysql\MySQLTable;
use phMysql\Column;
use phMysql\MySQLTable;
/**
 * Description of TestQuery
 *
 * @author Eng.Ibrahim
 */
class TestQuery extends MySQLQuery{
    /**
     *
     * @var MySQLTable 
     */
    private $table;
    public function __construct() {
        parent::__construct();
        $this->table = new MySQLTable('users_table');
        $this->table->addColumn('user-id', new Column('user_id', 'int', 3));
        $this->getCol('user-id')->setIsPrimary(true);
        $this->getCol('user-id')->setIsAutoInc(true);
        $this->table->addColumn('username', new Column('username', 'varchar',20));
        $this->table->addColumn('password', new Column('password', 'varchar',20));
    }
    /**
     * 
     * @return type
     */
    public function getStructure(){
        return $this->table;
    }

}
