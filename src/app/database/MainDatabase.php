<?php
namespace app\database;

use webfiori\database\Database;
use webfiori\framework\DB;
use webfiori\framework\User;
/**
 * A sample database instance.
 *
 * @author Ibrahim
 */
class MainDatabase extends DB {
    public function __construct() {
        parent::__construct('conn-00');

        //Add tables to the schema as needed.
        $this->addTable(new UsersTable());
    }
    /**
     * 
     * @param User $userObj
     */
    public function addUser($userObj) {
        $this->table('users')->insert([
            'email' => $userObj->getEmail(),
            'username' => $userObj->getUserName()
        ])->execute();
    }
    /**
     * 
     * @param type $pageNum
     * @param type $itmesPerPage
     */
    public function getUsers($pageNum = 1, $itmesPerPage = 5) {
        $this->table('users')->select()->page($pageNum, $itmesPerPage)->execute();
        $resultSet = $this->getLastResultSet();
        $resultSet->setMappingFunction(function ($records)
        {
            $retVal = [];

            foreach ($records as $record) {
                $userObj = new User();
                $userObj->setID($record['user_id']);
                $userObj->setEmail($record['email']);
                $userObj->setUserName($record['username']);
                $retVal[] = $userObj;
            }

            return $retVal;
        });
    }
}
