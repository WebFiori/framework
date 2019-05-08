<?php
namespace webfiori\tests\entity;
use phMysql\MySQLQuery;
use phMysql\Column;
use phMysql\MySQLTable;
/**
 * Description of TestQuery
 *
 * @author Eng.Ibrahim
 */
class TestQuery_2 extends MySQLQuery{
    /**
     *
     * @var MySQLTable 
     */
    private $table;
    public function __construct() {
        parent::__construct();
        $this->table = new MySQLTable('contact_info');
        $this->table->addColumn('user-id', new Column('user_id', 'int', 3));
        $this->getCol('user-id')->setIsPrimary(true);
        $this->table->addColumn('contact_type', new Column('contact_type', 'varchar',20));
        $this->getCol('contact_type')->setIsPrimary(true);
        $this->table->addColumn('contact_info', new Column('contact_info', 'varchar',20));
    }
    /**
     * 
     * @return type
     */
    public function getStructure(){
        return $this->table;
    }
    
    
}
