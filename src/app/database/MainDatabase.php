<?php
namespace app\database;

use webfiori\database\Database;
use webfiori\framework\Access;
use webfiori\framework\DB;
use webfiori\framework\session\SessionsManager;
use webfiori\framework\User;
/**
 * A sample database instance.
 *
 * @author Ibrahim
 */
class MainDatabase extends DB {
    private $userMappFunc;
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('conn-00');

        $this->register('app/database');

        //This function is used later to map the result of different queries
        //To an object of type user.
        $this->userMappFunc = function ($records)
        {
            $retVal = [];

            foreach ($records as $record) {
                $userObj = new User();
                $userObj->setID($record['user_id']);
                $userObj->setEmail($record['email']);
                $userObj->setUserName($record['username']);
                $userObj->setLastLogin($record['last_success_login']);
                $userObj->setRegDate($record['created_on']);
                Access::resolvePriviliges($record['privileges'], $userObj);

                $retVal[] = $userObj;
            }

            return $retVal;
        };
    }
    /**
     * Adds new user to the database.
     * 
     * @param User $userObj An object that holds user information.
     */
    public function addUser(User $userObj) {
        $this->table('users')->insert([
            'email' => $userObj->getEmail(),
            'username' => $userObj->getUserName(),
            'password' => hash('sha256', $userObj->getPassword()),
            'privileges' => Access::createPermissionsStr($userObj)
        ])->execute();
    }
    /**
     * Returns user information given its email address.
     * 
     * @param string $emailAddress The email address of the user.
     * 
     * @return User|null If a user which has the given email was found, the 
     * method will return it. If no such user was found, the method will return 
     * null.
     */
    public function getUserByEmail($emailAddress) {
        return $this->getUser('email', $emailAddress);
    }
    /**
     * Returns user information given its ID.
     * 
     * @param int $userId The ID of the user.
     * 
     * @return User|null If a user which has the given ID was found, the 
     * method will return it. If no such user was found, the method will return 
     * null.
     */
    public function getUserById($userId) {
        return $this->getUser('user-id', $userId);
    }
    /**
     * Returns user information given its username.
     * 
     * @param string $uName The username of the user.
     * 
     * @return User|null If a user which has the given username was found, the 
     * method will return it. If no such user was found, the method will return 
     * null.
     */
    public function getUserByUsername($uName) {
        return $this->getUser('username', $uName);
    }
    /**
     * Returns an array that holds objects of type 'User'.
     * 
     * @param int $pageNum An optional page number. Default is 1.
     * 
     * @param int $itmesPerPage The number of records per page. Default is 5.
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
                $userObj->setLastLogin($record['last_success_login']);
                $userObj->setRegDate($record['created_on']);
                Access::resolvePriviliges($record['privileges'], $userObj);

                $retVal[] = $userObj;
            }

            return $retVal;
        });

        return $resultSet->getMappedRows();
    }
    /**
     * Checks if user can login to the system or not.
     * 
     * @param string $userNameOrEmail The username of the user or his email 
     * address.
     * 
     * @param string $userPass The password of the user (not hashed).
     * 
     * @param int $sessionDuration An optional session duration (in minutes). Must be a number 
     * greater than 1. Default is 5.
     * 
     * @param boolean $isRef A boolean to indicate if the timeout of the session 
     * will be refreshed with every request or not.
     * 
     * @return null|User If the user is logged in, the method will return an 
     * object of type 'User'. If not, the method will return null.
     */
    public function login($userNameOrEmail, $userPass, $sessionDuration = 5, $isRef = true) {
        $this->table('users')->select()
        ->where('email', '=', $userNameOrEmail)
        ->andWhere('password', '=', hash('sha256', $userPass));
        $result = $this->execute();

        if ($result == null || $result->getRowsCount() == 0) {
            $result = $this->table('users')->select()
            ->where('username', '=', $userNameOrEmail)
            ->andWhere('password', '=', hash('sha256', $userPass))->execute();

            if ($result == null || $result->getRowsCount() == 0) {
                return null;
            }
        }

        $result->setMappingFunction($this->userMappFunc);
        $userObj = $result->getMappedRows()[0];

        $this->update([
            'last-success-login' => date('Y-m-d H:i:s')
        ])->where('user-id', '=', $userObj->getID());
        $session = SessionsManager::getActiveSession();

        if ($session !== null) {
            $session->setUser($userObj);
            $session->setDuration($sessionDuration > 1 ? $sessionDuration : 5);
            $session->setIsRefresh($isRef === true);
        }

        return $userObj;
    }
    /**
     * Update the password of a user given its ID.
     * 
     * @param User $user An object that holds the ID of the user 
     * alongside the new password.
     */
    public function updatePassword(User $user) {
        $this->table('users')->update([
            'password' => hash('sha256', $user->getPassword()),
        ])->where('user-id', '=', $user->getID())->execute();
    }
    /**
     * Update the privileges of a user given its ID.
     * 
     * @param User $user An object that holds the ID of the user 
     * alongside the new set of privileges.
     */
    public function updatePrivileges(User $user) {
        $this->table('users')->update([
            'privileges' => hash('sha256', $user->getPassword()),
        ])->where('user-id', '=', $user->getID())->execute();
    }
    /**
     * Update user information given its info stored in an object.
     * 
     * This method will update the following information:
     * <ul>
     * <li>email</li>
     * <li>username</li>
     * </ul>
     * 
     * @param User $userObj The object that holds user info.
     */
    public function updateUser(User $userObj) {
        $this->table('users')->update([
            'email' => $userObj->getEmail(),
            'username' => $userObj->getUserName(),
        ])->where('user-id', '=', $userObj->getID())->execute();
    }

    private function getUser($col, $val) {
        $result = $this->table('users')->select()->where($col, '=', $val)->execute();

        if ($result->getRowsCount() == 1) {
            $result->setMappingFunction($this->userMappFunc);

            return $result->getMappedRows()[0];
        }
    }
}
