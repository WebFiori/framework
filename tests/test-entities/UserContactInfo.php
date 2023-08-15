<?php
namespace webfiori\tests\database;

use phMysql\MySQLColumn;
use phMysql\MySQLQuery;
use phMysql\MySQLTable;
/**
 * Description of UserContactInfo
 *
 * @author Ibrahim
 */
class UserContactInfo extends MySQLQuery {
    /**
     *
     * @var MySQLTable
     */
    private $table;
    public function __construct() {
        parent::__construct();
        $this->table = new MySQLTable('user_contact_info');
        $this->table->addDefaultCols([
            'id' => [
                'key-name' => 'user-id',
                'db-name' => 'user_id'
            ]
        ]);
        $this->getCol('user-id')->setIsUnique(false);
        $this->table->addColumn('contact-type', new MySQLColumn('contact_type', 'varchar',100));
        $this->getCol('contact-type')->setIsUnique(false);
        $this->getCol('contact-type')->setIsPrimary(true);
        $this->table->addColumn('contact-info', new MySQLColumn('contact_info', 'varchar',320));
        $this->table->addReference('webfiori\tests\database\UsersQuery', [
            'user-id' => 'user-id'
        ], 'user_contact_info_fk', 'cadcade', 'cascade');
        $this->setTable($this->table);
    }

    public function addInfo($useId,$contactName,$info) {
        $this->insertRecord([
            'user-id' => $useId,
            'contact-type' => $contactName,
            'contact-info' => $info
        ]);
    }
    /**
     * @return MySQLTable Description
     */
    public function getStructure() {
        return $this->table;
    }
    public function getUserContactInfo($userId) {
        $this->select([
            'where' => [
                'user-id' => $userId
            ]
        ]);
    }
}
