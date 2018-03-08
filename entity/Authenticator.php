<?php
/**
 * The name of hash algorithm used to create password hash.
 */
define('HASH_ALGO_NAME','sha256');
/**
 * A class that is used to authenticate system users.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.2
 */
class Authenticator{
    /**
     * The user object that is associated with the instance.
     * @var User 
     */
    private $user;
    /**
     * Creates new instance of the class using specific user.
     * @param type $user
     */
    public function __construct($user) {
        $this->user = $user;
    }
    /**
     * Returns the user object that is associated with the instance.
     * @return User an object of type <b>User</b>
     */
    public function getUser(){
        return $this->user;
    }
    
    private function loginUsingMail($passhash){
        $query = new UserQuery();
        //first try to get user by 
        $query->getUserByEmail($this->user->getEmail());
        $r = $_SESSION['db']->executeQuery($query);
        if($r){
            if($_SESSION['db']->rows() == 1){
                $row = $_SESSION['db']->getRow();
                if($passhash == $row['pass']){
                    $userId = $row['id'];
                    $username = $row['username'];
                    $email = $row['email'];
                    $this->user = new User($username,'',$email);
                    $this->user->setID($userId);
                    $this->user->setAccessLevel($row['acc_level']);
                    $this->user->setStatus($row['status']);
                    $exp_time = date("Y-m-d H:i:s", strtotime('+5 minutes'));
                    $this->user->setToken(hash(HASH_ALGO_NAME,$exp_time));
                    $query->updateLastLogin($userId);
                    $_SESSION['db']->executeQuery($query);
                    return TRUE;
                }
                
            }
        }
        return FALSE;
    }
    private function loginUsingUserName($passhash){
        $query = new UserQuery();
        $query->getUserByUsername($this->user->getUserName());
        $r = $_SESSION['db']->executeQuery($query);
        if($r){
            if($_SESSION['db']->rows() == 1){
                $row = $_SESSION['db']->getRow();
                if($passhash == $row['pass']){
                        $userId = $row['id'];
                        $username = $row['username'];
                        $email = $row['email'];
                        $this->user = new User($username,'',$email);
                        $this->user->setID($userId);
                        $this->user->setAccessLevel($row['acc_level']);
                        $this->user->setStatus($row['status']);
                        $exp_time = date("Y-m-d H:i:s", strtotime('+5 minutes'));
                        $this->user->setToken(hash(HASH_ALGO_NAME,$exp_time));
                        $query->updateLastLogin($userId);
                        $_SESSION['db']->executeQuery($query);
                        return TRUE;
                }
            }
        }
        return FALSE;
    }
    /**
     * Authenticate the user using his username and password. Username can be the 
     * email address of the user.
     * @return boolean
     */
    public function authenticate(){
        $pssswordHash = hash(HASH_ALGO_NAME, $this->user->getPassword());
        if($this->loginUsingMail($pssswordHash) || $this->loginUsingUserName($pssswordHash)){
            return TRUE;
        }
        return FALSE;
    }
    
}
