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
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
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
        $schema = DatabaseSchema::get();
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
            if(strlen($user->getEmail()) != 0){
                if(strlen($user->getUserName()) != 0){
                    if(strlen($user->getPassword()) != 0){
                        $user->setDisplayName('System Super Admin');
                        $privileges = Access::privileges();
                        foreach ($privileges as $p){
                            $user->addPrivilege($p->getID());
                        }
                        $user->setStatus('A');
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
        return FALSE;
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
