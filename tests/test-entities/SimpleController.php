<?php
use webfiori\logic\Controller;
Use webfiori\tests\database\UsersQuery;
use webfiori\tests\testEntity\UserWithContact;
use webfiori\tests\database\UserContactInfo;
use webfiori\entity\DBConnectionInfo;
use webfiori\WebFiori;
/**
 * Description of SimpleController
 *
 * @author Ibrahim
 */
class SimpleController extends Controller{
    public function __destruct() {
        $this->cleanDb();
    }
    /**
     *
     * @var UsersQuery 
     */
    private $userQuery;
    /**
     *
     * @var UserContactInfo 
     */
    private $userInfoQuery;
    public function __construct() {
        parent::__construct();
        $this->userQuery = new UsersQuery();
        $this->userInfoQuery = new UserContactInfo();
        $connection = new DBConnectionInfo('root', '123456', 'testing_db');
        $connection->setConnectionName('test-connection');
        WebFiori::getConfig()->addDbConnection($connection);
        $this->setConnection('test-connection');
        if($this->initDb()){
            $this->addInitialData();
        }
    }
    public function initDb() {
        $this->userQuery->createStructure();
        if($this->excQ($this->userQuery)){
            $this->userInfoQuery->createStructure();
            if($this->excQ($this->userInfoQuery)){
                return true;
            }
            else{
                fprintf(STDERR, "DB Err: \n<%s> \n", $this->getDBErrDetails()['error-message']);
                fprintf(STDERR, "Query: \n<%s> \n", $this->getLastQuery()->getQuery());
            }
        }
        else{
            fprintf(STDERR, "DB Err: \n<%s> \n", $this->getDBErrDetails()['error-message']);
            fprintf(STDERR, "Query: \n<%s> \n", $this->getLastQuery()->getQuery());
        }
        return false;
    }
    public function cleanDb() {
        $q = 'drop table '.$this->userInfoQuery->getStructureName();
        $this->userInfoQuery->setQuery($q, 'drop');
        if($this->excQ($this->userInfoQuery)){
            $q = 'drop table '.$this->userQuery->getStructureName();
            $this->userInfoQuery->setQuery($q, 'drop');
            if($this->excQ($this->userInfoQuery)){
                return true;
            }
            else{
                fprintf(STDERR, "DB Err: \n<%s> \n", $this->getDBErrDetails()['error-message']);
                fprintf(STDERR, "Query: \n<%s> \n", $this->getLastQuery()->getQuery());
            }
        }
        else{
            fprintf(STDERR, "DB Err: \n<%s> \n", $this->getDBErrDetails()['error-message']);
            fprintf(STDERR, "Query: \n<%s> \n", $this->getLastQuery()->getQuery());
        }
        return false;
    }
    public function addInitialData() {
        $u00 = new UserWithContact();
        $u00->setUserName('user-00');
        $u00->setPassword('pass-00');
        $u00->addContactInfo('email-00', 'user-email@example.com');
        $this->addUser($u00);
        $u01 = new UserWithContact();
        $u01->setUserName('user-01');
        $u01->setPassword('pass-01');
        $u01->addContactInfo('email-01', 'user2-email@example.com');
        $u01->addContactInfo('mobile-01', '0555');
        $this->addUser($u01);
        $u02 = new UserWithContact();
        $u02->setUserName('user-02');
        $u02->setPassword('pass-02');
        $u02->addContactInfo('email-1', 'user3-email-1@example.com');
        $u02->addContactInfo('mobile-1', '0555');
        $u02->addContactInfo('email-2', 'user3-email-2@example.com');
        $u02->addContactInfo('mobile-2', '044');
        $this->addUser($u02);
    }
    public function addUser($user) {
        if($user instanceof UserWithContact){
            $this->userQuery->addUser($user);
            if($this->excQ($this->userQuery)){
                $user->setID($this->getLastUserID());
                $this->addContactInfo($user);
            }
            else{
                fprintf(STDERR, "DB Err: \n<%s> \n", $this->getDBErrDetails()['error-message']);
                fprintf(STDERR, "Query: \n<%s> \n", $this->getLastQuery()->getQuery());
            }
        }
    }
    public function getUsers() {
        $this->userQuery->select();
        if($this->excQ($this->userQuery)){
            $retVal = [];
            while ($row = $this->nextRow()){
                $user = new UserWithContact();
                $user->setPassword($row['pass']);
                $user->setUserName($row['username']);
                $user->setID($row['id']);
                $this->userInfoQuery->getUserContactInfo($user->getID());
                if($this->excQ($this->userInfoQuery)){
                    while ($row2 = $this->nextRow()){
                        $user->addContactInfo($row2['contact_type'], $row2['contact_info']);
                    }
                }
                else{
                    fprintf(STDERR, "DB Err: \n<%s> \n", $this->getDBErrDetails()['error-message']);
                    fprintf(STDERR, "Query: \n<%s> \n", $this->getLastQuery()->getQuery());
                }
                $retVal[] = $user;
            }
            return $retVal;
        }
        else{
            fprintf(STDERR, "DB Err: \n<%s> \n", $this->getDBErrDetails()['error-message']);
            fprintf(STDERR, "Query: \n<%s> \n", $this->getLastQuery()->getQuery());
        }
    }
    public function addContactInfo($user) {
        if($user instanceof UserWithContact){
            foreach ($user->getContactInfo()as $k => $v){
                $this->userInfoQuery->addInfo($user->getID(), $k, $v);
                if(!$this->excQ($this->userInfoQuery)){
                    fprintf(STDERR, "DB Err: \n<%s> \n", $this->getDBErrDetails()['error-message']);
                    fprintf(STDERR, "Query: \n<%s> \n", $this->getLastQuery()->getQuery());
                }
            }
        }
    }
    public function getLastUserID() {
        $this->userQuery->selectMax('user-id');
        if($this->excQ($this->userQuery)){
            if($this->rows() == 1){
                return intval($this->getRow()['max']);
            }
            else{
                fprintf(STDERR, "DB Err: \n<%s> \n", $this->getDBErrDetails()['error-message']);
                fprintf(STDERR, "Query: \n<%s> \n", $this->getLastQuery()->getQuery());
            }
        }
        else{
            fprintf(STDERR, "DB Err: \n<%s> \n", $this->getDBErrDetails()['error-message']);
            fprintf(STDERR, "Query: \n<%s> \n", $this->getLastQuery()->getQuery());
        }
        return 1;
    }
}
