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
 * @version 1.0
 */
class AdminFunctions extends Functions{
    /**
     *
     * @var UserQuery 
     */
    private $uQuery;
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
        $this->uQuery = new UserQuery();
    }
    /**
     * Initialize database if not initialized.
     * @return boolean <b>TRUE</b> If initialized. <b>FALSE</b> otherwise.
     * @since 1.0
     */
    public function createDatabase(){
        $this->useDatabase();
        $schema = new DatabaseSchema();
        //creating any random query object just to execute create
        //tables statements.
        $q = new UserQuery();
        $q->setQuery($schema->getSchema(), 'update');
        if($this->excQ($q)){
            return TRUE;
        }
        else{
            return MySQLQuery::QUERY_ERR;
        }
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
            $this->uQuery->removeUser($userId);
            if($this->excQ($this->uQuery)){
                return $user;
            }
            return MySQLQuery::QUERY_ERR;
        }
        return UserFunctions::NO_SUCH_USER;
    }
    /**
     * Checks if the system has an admin account or not.
     * @return boolean <b>TRUE</b> is returned if the system has 
     * at least one admin account.
     * @since 1.0
     */
    public function hasAdminAccount(){
        return count($this->getAdminAccounts()) !== 0;
    }
    /**
     * Creates an admin account.
     * @param User $user An object of type <b>User</b>.
     * @return boolean|User|string If the user account is created, the 
     * function will return an object of type <b>User</b> which will contain 
     * all account information. If a database error happens, the function will 
     * return <b>MySQLQuery::QUERY_ERR</b>. If the username, password or email address 
     * is missing, the function will return <b>Functions::EMPTY_STRING</b>.
     * <p><b>Important Note:</b> A side effect of calling this function is that 
     * all other admin accounts will be removed.<p>
     * @since 1.0
     */
    public function createFirstAdminAccount($user){
        if($user instanceof User){
            $user->setDisplayName('System Admin');
            $users = $this->getAdminAccounts();
            foreach ($users as $Tuser){
                $this->removeAccount($Tuser->getID());
            }
            if(strlen($user->getEmail()) != 0){
                if(strlen($user->getUserName()) != 0){
                    if(strlen($user->getPassword()) != 0){
                        $this->uQuery->addUser($user);
                        if($this->excQ($this->uQuery)){
                            return UserFunctions::get()->getUserByEmail($user->getEmail());
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
        return FALSE;
    }
    /**
     * Returns an array that contains all users who have access level set to 0.
     * @return array|string An array that contains all users who have access level set to 0. 
     * If a database error happens, the function will return <b>MySQLQuery::QUERY_ERR</b>.
     * @since 1.0
     */
    public function getAdminAccounts(){
        $this->uQuery->getUsersByAccessLevel(0);
        if($this->excQ($this->uQuery)){
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
