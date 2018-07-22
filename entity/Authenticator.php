<?php
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
 * The name of hash algorithm used to create password hash.
 * @deprecated since version 1.2 Use <b>Authenticator::HASH_ALGO_NAME </b> Instead.
 */
define('HASH_ALGO_NAME','sha256');
/**
 * A class that is used to authenticate system users.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.3
 */
class Authenticator{
    /**
     * The name of hash algorithm used to create password hash.
     * @var string The name of hash algorithm used to create password hash.
     * @since 1.2
     */
    const HASH_ALGO_NAME = 'sha256';
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
                    $salt = date("Y-m-d H:i:s", strtotime('+5 minutes'));
                    $this->user->setToken(hash(HASH_ALGO_NAME,$salt));
                    $query->updateLastLogin($userId);
                    $_SESSION['db']->executeQuery($query);
                    Access::resolvePriviliges($row['privileges'], $this->user);
                    return TRUE;
                }
                else{
                    return UserFunctions::NOT_AUTH;
                }
            }
            else{
                return UserFunctions::NOT_AUTH;
            }
        }
        else{
            return MySQLQuery::QUERY_ERR;
        }
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
                        $salt = date("Y-m-d H:i:s", strtotime('+5 minutes'));
                        $this->user->setToken(hash(HASH_ALGO_NAME,$salt));
                        $query->updateLastLogin($userId);
                        $_SESSION['db']->executeQuery($query);
                        Access::resolvePriviliges($row['privileges'], $this->user);
                        return TRUE;
                }
                else{
                    return UserFunctions::NOT_AUTH;
                }
            }
            else{
                return UserFunctions::NOT_AUTH;
            }
        }
        else{
            return MySQLQuery::QUERY_ERR;
        }
    }
    /**
     * Authenticate the user using his username and password. Username can be the 
     * email address of the user.
     * @return boolean|string The function will return true if the user is logged 
     * in. If the user is not authenticated, the function will return <b>UserFunctions::NOT_AUTH</b>. 
     * If an error has happend while checking the database, the function will return 
     * <b>MySQLQuery::QUERY_ERR</b>.
     */
    public function authenticate(){
        $pssswordHash = hash(HASH_ALGO_NAME, $this->user->getPassword());
        $u = $this->loginUsingMail($pssswordHash);
        if($u === TRUE){
            return TRUE;
        }
        else if($u == UserFunctions::NOT_AUTH){
            $u = $this->loginUsingUserName($pssswordHash);
            if($u === FALSE){
                return UserFunctions::NOT_AUTH;
            }
            else if($u === TRUE){
                return TRUE;
            }
            else if($u == MySQLQuery::QUERY_ERR){
                return MySQLQuery::QUERY_ERR;
            }
        }
        else if($u == MySQLQuery::QUERY_ERR){
            return MySQLQuery::QUERY_ERR;
        }
    }
    
}
