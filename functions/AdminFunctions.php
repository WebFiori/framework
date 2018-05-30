<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * A class that contains administrative functions
 *
 * @author Ibrahim
 * @version 1.1
 */
class AdminFunctions extends Functions{
    /**
     * An instance of <b>UserQuery</b>.
     * @var UserQuery
     * @since 1.0 
     */
    private $query;
    /**
     *
     * @var AdminFunctions 
     */
    private static $singleton;
    /**
     * Returns an instance of <b>AdminFunctions</b>.
     * @return AdminFunctions An instance of <b>AdminFunctions</b>.
     * @since 1.0
     */
    public static function get(){
        if(self::$singleton !== NULL){
            return self::$singleton;
        }
        self::$singleton = new AdminFunctions();
        return self::$singleton;
    }
    
    public function __construct() {
        parent::__construct();
        $this->query = new UserQuery();
    }
    /**
     * Run application setup process.
     * @param User $superAdmin The super user account information.
     * @return boolean|User|string If the setup completed successfully, 
     * the function will return <b>TRUE</b>. 
     * If a database error happens, the function will 
     * return <b>MySQLQuery::QUERY_ERR</b>. If the username, password or email address 
     * is missing, the function will return <b>Functions::EMPTY_STRING</b>.
     * <p><b>Important Note:</b> A side effect of calling this function is that 
     * all other super admin accounts will be removed.<p>
     * @since 1.1
     */
    public function runSetup($superAdmin) {
        if(!Config::get()->isConfig()){
            $r = $this->createDatabase();
            if($r === TRUE){
                $r = $this->createSuperAdminAccount($superAdmin);
                if($r instanceof User){
                    return TRUE;
                }
            }
            return $r;
        }
        return FALSE;
    }
    /**
     * Initialize database if not initialized.
     * @return boolean|string <b>TRUE</b> If initialized. <b>MySQLQuery::QUERY_ERR</b> otherwise.
     * @since 1.0
     */
    private function createDatabase(){
        $this->useDatabase();
        $schema = new DatabaseSchema();
        $schema->add('UserQuery');
        $schema->add('ActivationQuery');
        $schema->add('FileQuery');
        //creating any random query object just to execute create
        //tables statements.
        $q = new UserQuery();
        $q->setQuery($schema->getSchema(), 'create');
        if($this->excQ($q)){
            return TRUE;
        }
        return MySQLQuery::QUERY_ERR;
    }
    /**
     * Removes a user account from the database.
     * @param string $userId The ID of the user.
     * @return string|User The function will return an object of type <b>User</b> 
     * in case of success. If a database error happens, the function will 
     * return <b>MySQLQuery::QUERY_ERR</b>. If the user does not exist, the function 
     * will return <b>UserFunctions::NO_SUCH_USER</b>.
     * @since 1.0
     */
    public function removeAccount($userId){
        $user = UserFunctions::get()->getUserByID($userId);
        if($user instanceof User){
            $this->query->removeUser($userId);
            if($this->excQ($this->query)){
                return $user;
            }
            return MySQLQuery::QUERY_ERR;
        }
        return UserFunctions::NO_SUCH_USER;
    }
    /**
     * Checks if the system has a super admin account or not.
     * @return boolean <b>TRUE</b> is returned if the system has 
     * at least one super admin account. A super addmin account is a user 
     * with access level = 0.
     * @since 1.0
     */
    public function hasAdminAccount(){
        return count($this->getSuperAdminAccounts()) !== 0;
    }
    /**
     * Creates a super admin account.
     * @param User $user An object of type <b>User</b>. The user will be added 
     * as a super admin (access level = 0).
     * @return boolean|User|string If the user account is created, the 
     * function will return an object of type <b>User</b> which will contain 
     * all account information. If a database error happens, the function will 
     * return <b>MySQLQuery::QUERY_ERR</b>. If the username, password or email address 
     * is missing, the function will return <b>Functions::EMPTY_STRING</b>.
     * <p><b>Important Note:</b> A side effect of calling this function is that 
     * all other super admin accounts will be removed.<p>
     * @since 1.0
     */
    private function createSuperAdminAccount($user){
        if($user instanceof User){
            $user->setDisplayName('System Admin');
            $user->setAccessLevel(0);
            $users = $this->getSuperAdminAccounts();
            if(gettype($users) == 'array'){
                foreach ($users as $Tuser){
                    $this->removeAccount($Tuser->getID());
                }
                if(strlen($user->getEmail()) != 0){
                    if(strlen($user->getUserName()) != 0){
                        if(strlen($user->getPassword()) != 0){
                            $this->query->addUser($user);
                            if($this->excQ($this->query)){
                                MailFunctions::get()->sendFirstMail($user);
                                return TRUE;
                            }
                            else{
                                return MySQLQuery::QUERY_ERR;
                            }
                        }
                        else {
                            return Functions::EMPTY_STRING;
                        }
                    }
                    else {
                        return Functions::EMPTY_STRING;
                    }
                }
                else {
                    return Functions::EMPTY_STRING;
                }
            }
            else{
                return $users;
            }
        }
        return FALSE;
    }
    /**
     * Updates the access level of a user. Only super admin can change access level 
     * of a user.
     * @param int $acclvl User access level.
     * @param string $userId The ID of the user that its access level will be 
     * updated.
     * @return User|string An object of type <b>User</b> in case the access level 
     * is updated. <b>MySQLQuery::QUERY_ERR</b> in case of database query error. 
     * <b>UserFunctions::NO_SUCH_USER</b> in case no user was found with the 
     * given iD.
     * @since 1.1
     */
    public function updateAccessLevel($acclvl, $userId){
        $loggedInAccLevel = $this->getAccessLevel();
        if($loggedInAccLevel != NULL && $loggedInAccLevel == 0){
            $user = $this->getUserByID($userId);
            if($user instanceof User){
                $this->query->updateAccessLevel($acclvl, $userId);
                if($this->excQ($this->query)){
                    $user->setAccessLevel($acclvl);
                    return $user;
                }
                else{
                    return MySQLQuery::QUERY_ERR;
                }
            }
            else{
                return $user;
            }
        }
        else{
            return self::NOT_AUTH;
        }
    }
    /**
     * Returns an array of all system users.
     * @return array|string An array of all system users. If the currently logged in 
     * user is not authorized to view users, The function will return 
     * <b>Functions::NOT_AUTH</b>. Also the function will return <b>MySQLQuery::QUERY_ERR</b> 
     * in case of database query error.
     * @since 1.1
     */
    public function getUsers(){
        $loggedAccessLevel = $this->getAccessLevel();
        if($loggedAccessLevel != NULL && $loggedAccessLevel == 0){
            $this->query->getUsers();
            if($this->excQ($this->query)){
                $result = $this->getMainSession()->getDBLink()->getResult();
                $users = array();
                while($row = $result->fetch_assoc()){
                    $user = new User(
                            $row[$this->query->getStructure()->getCol('username')->getName()],
                            '',
                            $row[$this->query->getStructure()->getCol('email')->getName()]);
                    $user->setID($row[UserQuery::ID_COL]);
                    $user->setStatus($row[$this->query->getStructure()->getCol('status')->getName()]);
                    $user->setDisplayName($row[$this->query->getStructure()->getCol('disp-name')->getName()]);
                    $user->setAccessLevel($row[$this->query->getStructure()->getCol('acc-level')->getName()]);
                    $user->setLastLogin($row[$this->query->getStructure()->getCol('last-login')->getName()]);
                    $user->setRegDate($row[$this->query->getStructure()->getCol('reg-date')->getName()]);
                    array_push($users, $user);
                }
                return $users;
            }
            else{
                return MySQLQuery::QUERY_ERR;
            }
        }
        else{
            return self::NOT_AUTH;
        }
    }
    /**
     * Updates the password of a user given his ID. Only super admin can do the update.
     * @param string $oldPass The old password.
     * @param string $newPass The new password.
     * @param string $userId The ID of the user.
     * @return boolean|string The function will return <b>TRUE</b> in case the 
     * password is updated. In case of database query error, the function will 
     * return <b>MySQLQuery::QUERY_ERR</b> If the old password does not match with 
     * the one stored in the database, the function will return 
     * <b>UserFunctions::PASSWORD_MISSMATCH</b>. If the user is not authorized to 
     * update the password, the function will return <b>UserFunctions::NOT_AUTH</b>. 
     * If no user was found using the given ID, The function will return 
     * <b>UserFunctions::NO_SUCH_USER</b>
     * @since 1.1
     */
    public function updatePassword($oldPass, $newPass, $userId){
        $loggedAccessLevel = $this->getAccessLevel();
        if($loggedAccessLevel != NULL && $loggedAccessLevel == 0){
            $user = $this->getUserByID($userId);
            if($user instanceof User){
                if($user->getID() == $loggedAccessLevel){
                    if($user->getPassword() == hash(Authenticator::HASH_ALGO_NAME, $oldPass)){
                        $this->query->updatePassword(hash(Authenticator::HASH_ALGO_NAME, $newPass), $userId);
                        if($this->excQ($this->query)){
                            MailFunctions::get()->notifyOfPasswordChangeAdmin($user);
                            return TRUE;
                        }
                        else{
                            return MySQLQuery::QUERY_ERR;
                        }
                    }
                    else{
                        return self::PASSWORD_MISSMATCH;
                    }
                }
                else{
                    return self::NOT_AUTH;
                }
            }
            return $user;
        }
        else{
            return self::NOT_AUTH;
        }
    }
    /**
     * Updates the status of a user. Only super admin can use this function.
     * @param string $newStatus The new status. It must be a one letter value. A key 
     * from the array <b>User::USER_STATS</b>.
     * @param string $userId The ID of the user.
     * @return User|string  An object of type <b>User</b> in case the status is updated. 
     * In case no user was found, the function will return <b>UserFunctions::NO_SUCH_USER</b>. In 
     * case of query error, the function will return <b>MySQLQuery::QUERY_ERR</b>. 
     * If the user is not authorized to update user profile, the function will return 
     * <b>Functions::NOT_AUTH</b>. If the given status is not a key in the array 
     * <b>User::USER_STATS</b>, the function will return 
     * <b>UserFunctions::STATUS_NOT_ALLOWED</b>. <b>Functions::NOT_AUTH</b> is returned 
     * if the user is not authorized to update status.
     * @since 1.0 
     */
    public function updateStatus($newStatus, $userId){
        $loggedAccessLevel = $this->getAccessLevel();
        if($loggedAccessLevel != NULL && $loggedAccessLevel == 0){
            if(array_key_exists($newStatus, User::USER_STATS)){
                $user = $this->getUserByID($userId);
                if($user instanceof User){
                    $this->query->updateStatus($newStatus, $user->getID());
                    if($this->excQ($this->query)){
                        $user->setStatus($newStatus);
                        return $user;
                    }
                    else{
                        return MySQLQuery::QUERY_ERR;
                    }
                }
                return $user;
            }
            else{
                return self::STATUS_NOT_ALLOWED;
            }
        }
        else{
            return self::NOT_AUTH;
        }
    }
    /**
     * Returns an array that contains all users who have access level set to 0.
     * @return array|string An array that contains all users who have access level set to 0. 
     * If a database error happens, the function will return <b>MySQLQuery::QUERY_ERR</b>.
     * @since 1.0
     */
    public function getSuperAdminAccounts(){
        $this->query->getUsersByAccessLevel(0);
        if($this->excQ($this->query)){
            $result = $this->getMainSession()->getDBLink()->getResult();
            $users = array();
            while($row = $result->fetch_assoc()){
                $user = new User(
                        $row[$this->query->getStructure()->getCol('username')->getName()],
                        '',
                        $row[$this->query->getStructure()->getCol('email')->getName()]);
                $user->setID($row[UserQuery::ID_COL]);
                $user->setStatus($row[$this->query->getStructure()->getCol('status')->getName()]);
                $user->setDisplayName($row[$this->query->getStructure()->getCol('disp-name')->getName()]);
                $user->setAccessLevel($row[$this->query->getStructure()->getCol('acc-level')->getName()]);
                $user->setLastLogin($row[$this->query->getStructure()->getCol('last-login')->getName()]);
                $user->setRegDate($row[$this->query->getStructure()->getCol('reg-date')->getName()]);
                array_push($users, $user);
            }
            return $users;
        }
        else{
            return MySQLQuery::QUERY_ERR;
        }
    }
    /**
     * Returns an array that contains all users who have access level set to 1.
     * @return array|string An array that contains all users who have access level set to 0. 
     * If a database error happens, the function will return <b>MySQLQuery::QUERY_ERR</b>.
     * @since 1.1
     */
    public function getAdminAccounts(){
        $this->query->getUsersByAccessLevel(1);
        if($this->excQ($this->query)){
            $result = $this->getMainSession()->getDBLink()->getResult();
            $users = array();
            while($row = $result->fetch_assoc()){
                $user = new User(
                        $row[$this->query->getStructure()->getCol('username')->getName()],
                        '',
                        $row[$this->query->getStructure()->getCol('email')->getName()]);
                $user->setID($row[UserQuery::ID_COL]);
                $user->setStatus($row[$this->query->getStructure()->getCol('status')->getName()]);
                $user->setDisplayName($row[$this->query->getStructure()->getCol('disp-name')->getName()]);
                $user->setAccessLevel($row[$this->query->getStructure()->getCol('acc-level')->getName()]);
                $user->setLastLogin($row[$this->query->getStructure()->getCol('last-login')->getName()]);
                $user->setRegDate($row[$this->query->getStructure()->getCol('reg-date')->getName()]);
                array_push($users, $user);
            }
            return $users;
        }
        else{
            return MySQLQuery::QUERY_ERR;
        }
    }
}
