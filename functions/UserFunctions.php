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
 * A class that contains all static methods for altering user attributes.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.4
 * @uses User The basic user class.
 * @uses UserQuery It uses the class to send user related queries.
 * @uses ActivationQuery Used for user activation related queries.
 * @uses Authenticator Used to log in the user.
 * @uses DatabaseLink Used to connect to MySQL database.
 */
class UserFunctions extends Functions{
    /**
     * An instance of <b>UserQuery</b>.
     * @var UserQuery
     * @since 1.0 
     */
    private $query;
    /**
     * An instance of <b>ActivationQuery</b>.
     * @var ActivationQuery 
     * @since 1.0
     */
    private $acQuery;
    /**
     * An instance of <b>UserFunctions</b>.
     * @var UserFunctions
     * @since 1.1 
     */
    private static $instance;
    /**
     * Returns a singleton of the class <b>UserFunctions</b>.
     * @return UserFunctions an instance of <b>UserFunctions</b>.
     * @since 1.0
     */
    public static function get(){
        if(self::$instance != NULL){
            return self::$instance;
        }
        self::$instance = new UserFunctions();
        return self::$instance;
    }
    public function __construct() {
        parent::__construct();
        $this->query = new UserQuery();
        $this->acQuery = new ActivationQuery();
        parent::useDatabase();
    }
    /**
     * A constant that indicates a user is not found.
     * @var string Constant that indicates a user is not found.
     * @since 1.0
     */
    const NO_SUCH_USER = 'user_not_found';
    /**
     * A constant that indicates that username is taken.
     * @var string Constant that indicates that username is taken.
     * @since 1.0
     */
    const USERNAME_TAKEN = 'username_taken';
    /**
     * A constant that indicates a user is already registered.
     * @var string Constant that indicates a user is already registered. A user 
     * is considered a registered user if his email address is in the system 
     * database.
     * @since 1.0
     */
    const USER_ALREAY_REG = 'already_registered';
    /**
     * A constant that indicates a given user status is not allowed.
     * @var string Constant that indicates a given user status is not allowed.
     * @since 1.1
     * @see User::USER_STATS
     */
    const STATUS_NOT_ALLOWED = 'status_not_allowed';
    /**
     * A constant that indicates a user account is already activated.
     * @var string Constant that indicates a user account is already activated.
     * @since 1.1
     */
    const ALREADY_ACTIVATED = 'account_already_active';
    
    /**
     * Checks if a given user info can grant him access to the system or not. It 
     * uses the email address or the username of the user. One is enough to provide.
     * @param string $u Username.
     * @param string $p password.
     * @param string $e Email address.
     * @param int $sessionTimeout The duration of user session (in minutes). It must be a 
     * positive number greater than 0. If invalid number is given, 10 will be used.
     * @param boolean $refreshTimeout If set to <b>TRUE</b> session timeout time will 
     * be refreshed every time user sends a request.
     * @return boolean|string <b>TRUE</b> if the user is authenticated. Else, it will 
     * return <b>FALSE</b>. In case of database error, the function will return 
     * <b>MySQLQuery::QUERY_ERR</b>.
     * @since 1.0
     */
    public function authenticate($u='',$p='',$e='',$sessionTimeout=10,$refreshTimeout=false){
        if(strlen($p) != 0){
            if(strlen($u) != 0 || strlen($e) != 0){
                $user = new User($u, $p, $e);
                $auth = new Authenticator($user);
                $result = $auth->authenticate();
                if($result === TRUE){
                    $tmpu = $this->getUserByID($auth->getUser()->getID());
                    $tmpu->setEmail('');
                    $tmpu->setActivationTok('');
                    $tmpu->setToken($auth->getUser()->getToken());
                    $this->getMainSession()->setUser($tmpu);
                    if($sessionTimeout > 0){
                        $this->getMainSession()->setLifetime($sessionTimeout);
                    }
                    $this->getMainSession()->initSession($refreshTimeout, TRUE);
                    return TRUE;
                }
                else if($result === UserFunctions::NOT_AUTH){
                    return FALSE;
                }
                else if($result == MySQLQuery::QUERY_ERR){
                    return MySQLQuery::QUERY_ERR;
                }
            }
        }
        return FALSE;
    }
    public function updateStatus($user) {
        if($user instanceof User){
            $this->query->updateStatus($user->getStatusCode(), $user->getID());
            if($this->excQ($this->query)){
                return TRUE;
            }
            return MySQLQuery::QUERY_ERR;
        }
        return FALSE;
    }
    /**
     * Activate user account given his Activation token. The user must be 
     * logged in before calling this function.
     * @param string $activationTok The activation token of the user.
     * @return User|string|boolean  An object of type <b>User</b> in case the activation process 
     * is completed. In case of query error, the function will return 
     * <b>MySQLQuery::QUERY_ERR</b>. If the user account is already activated, the 
     * function will return <b>UserFunctions::ALREADY_ACTIVATED</b>. If the given token 
     * does not match, the function will return <b>FALSE</b>. In case the user is not 
     * logged in, the function will return <b>UserFunctions::NOT_AUTH</b>.
     * @since 1.0
     */
    public function activateAccount($activationTok){
        $id = $this->getUserID();
        if($id != NULL){
            $this->acQuery->getActivationCode($id);
            if($this->excQ($this->acQuery)){
                if($this->rows() != 0){
                    $tok = $this->getRow()[$this->acQuery->getStructure()->getCol('code')->getName()];
                    if($tok == $activationTok){
                        $this->acQuery->activate($id);
                        if($this->excQ($this->acQuery)){
                            $user = $this->getMainSession()->getUser();
                            $user->setStatus('A');
                            return $this->updateStatus($user);
                        }
                        else{
                            return MySQLQuery::QUERY_ERR;
                        }
                    }
                    else{
                        return FALSE;
                    }
                }
                else{
                    return self::ALREADY_ACTIVATED;
                }
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
     * Updates the display name of a user given his ID.
     * @param string $newDispName The new display name.
     * @param string $userId The ID of the user.
     * @return User|string  An object of type <b>User</b> in case the display name is updated. 
     * In case no user was found, the function will return <b>UserFunctions::NO_SUCH_USER</b>. In 
     * case of query error, the function will return <b>MySQLQuery::QUERY_ERR</b>. 
     * If the user is not authorized to update user profile, the function will return 
     * <b>Functions::NOT_AUTH</b>.
     */
    public function updateDisplayName($newDispName, $userId){
        $loggedId = $this->getUserID();
        if($loggedId != NULL){
            if($loggedId == $userId || $this->getAccessLevel() == 0){
                $user = $this->getUserByID($userId);
                if($user instanceof User){
                    $this->query->updateDisplayName($newDispName, $user->getID());
                    if($this->excQ($this->query)){
                        $user->setDisplayName($newDispName);
                        if($user->getID() == $this->getUserID()){
                            $this->getMainSession()->getUser()->setDisplayName($newDispName);
                        }
                        return $user;
                    }
                    else{
                        return MySQLQuery::QUERY_ERR;
                    }
                }
                return $user;
            }
            else{
                return self::NOT_AUTH;
            }
        }
        else{
            return self::NOT_AUTH;
        }
    }
    /**
     * Updates the email address of a user given his ID.
     * @param string $email The new Email address.
     * @param string $userId The ID of the user.
     * @return User|string  An object of type <b>User</b> in case the email is updated. 
     * In case no user was found, the function will return <b>UserFunctions::NO_SUCH_USER</b>. In 
     * case of query error, the function will return <b>MySQLQuery::QUERY_ERR</b>. 
     * If the user is not authorized to update user profile, the function will return 
     * <b>Functions::NOT_AUTH</b>. If the given email address belongs to a user 
     * who is already registered, the function will return <b>UserFunctions::USER_ALREAY_REG</b>.
     * @since 1.0
     */
    public function updateEmail($email, $userId){
        $loggedId = $this->getUserID();
        if($loggedId != NULL){
            if($loggedId == $userId || $this->getAccessLevel() == 0){
                $check = $this->getUserByEmail($email);
                if($check == self::NO_SUCH_USER){
                    $user = $this->getUserByID($userId);
                    if($user instanceof User){
                        $this->query->updateEmail($email, $user->getID());
                        if($this->excQ($this->query)){
                            $user->setEmail($email);
                            if($user->getID() == $this->getUserID()){
                                $this->getMainSession()->getUser()->setEmail($email);
                            }
                            return $user;
                        }
                        else{
                            return MySQLQuery::QUERY_ERR;
                        }
                    }
                    return $user;
                }
                else{
                    return $check;
                }
            }
            else{
                return self::NOT_AUTH;
            }
        }
        else{
            return self::NOT_AUTH;
        }
    }
    /**
     * Return a user given his ID.
     * @param string $id The ID of the user.
     * @return User|string An object of type <b>User</b> if found. In case no user 
     * was found, the function will return <b>UserFunctions::NO_SUCH_USER</b>. In 
     * case of query error, the function will return <b>MySQLQuery::QUERY_ERR</b>. 
     * If the user is not authorized to get user profile, the function will return 
     * <b>Functions::NOT_AUTH</b>.
     * @since 1.1
     */
    public function getUserByID($id){
        $this->query->getUserByID($id);
        if($this->excQ($this->query)){
            if($this->rows() != 0){
                $user = new User();
                $row = $this->getRow();
                $user->setPassword($row[$this->query->getStructure()->getCol('password')->getName()]);
                $user->setEmail($row[$this->query->getStructure()->getCol('email')->getName()]);
                $user->setID($row[MySQLQuery::ID_COL]);
                $user->setStatus($row[$this->query->getStructure()->getCol('status')->getName()]);
                $user->setUserName($row[$this->query->getStructure()->getCol('username')->getName()]);
                $user->setAccessLevel($row[$this->query->getStructure()->getCol('acc-level')->getName()]);
                $user->setDisplayName($row[$this->query->getStructure()->getCol('disp-name')->getName()]);
                $user->setLastLogin($row[$this->query->getStructure()->getCol('last-login')->getName()]);
                $user->setRegDate($row[$this->query->getStructure()->getCol('reg-date')->getName()]);
                $user->setLastPasswordResetDate($row[$this->query->getColName('last-password-reset')]);
                $user->setResetCount($row[$this->query->getColName('reset-pass-count')]);
                $tok = $this->getRegTok($id);
                $user->setActivationTok($tok);
                return $user;
            }
            else{
                return self::NO_SUCH_USER;
            }
        }
        else{
            return MySQLQuery::QUERY_ERR;
        }
    }
    /**
     * Return a user given his username.
     * @param string $username The username of the user.
     * @return User|string An object of type <b>User</b> if found. If the user is not 
     * found, the function will return <b>UserFunctions::NO_SUCH_USER</b>. If 
     * an error occur while running the query on the database, The function will 
     * return <b>MySQLQuery::QUERY_ERR</b>. If the given username is an empty string, 
     * the function will return <b>UserFunctions::EMPTY_STRING</b>.
     * @since 1.0
     */
    public function getUserByUsername($username){
        if(strlen($username) != 0){
            $this->query->getUserByUsername($username);
            if($this->excQ($this->query)){
                $row = $this->getRow();
                if($row != null){
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
                    $user->setLastPasswordResetDate($row[$this->query->getColName('last-password-reset')]);
                    $user->setResetCount($row[$this->query->getColName('reset-pass-count')]);
                    return $user;
                }
                else{
                    return self::NO_SUCH_USER;
                }
            }
            else{
                return MySQLQuery::QUERY_ERR;
            }
        }
        else{
            return self::EMPTY_STRING;
        }
    }
    /**
     * Return a user given his email address.
     * @param string $email The email address of the user.
     * @return User|string An object of type <b>User</b> if found. If the user is not 
     * found, the function will return <b>UserFunctions::NO_SUCH_USER</b>. If 
     * an error occur while running the query on the database, The function will 
     * return <b>MySQLQuery::QUERY_ERR</b>. If the given email is an empty string, 
     * the function will return <b>UserFunctions::EMPTY_STRING</b>.
     * @since 1.0
     */
    public function getUserByEmail($email){
        if(strlen($email) != 0){
            $this->query->getUserByEmail($email);
            if($this->excQ($this->query)){
                $row = $this->getRow();
                if($row != null){
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
                    $user->setLastPasswordResetDate($row[$this->query->getColName('last-password-reset')]);
                    $user->setResetCount($row[$this->query->getColName('reset-pass-count')]);
                    return $user;
                }
                else{
                    return self::NO_SUCH_USER;
                }
            }
            else{
                return MySQLQuery::QUERY_ERR;
            }
        }
        else{
            return self::EMPTY_STRING;
        }
    }
    /**
     * Checks if a given username is taken or not.
     * @param string $username The username that will be checked.
     * @return boolean <b>TRUE</b> if the user name is taken. <b>FALSE</b> if 
     * not taken. <b>MySQLQuery::QUERY_ERR</b> in case of database error. 
     * If the given username is an empty string, 
     * the function will return <b>UserFunctions::EMPTY_STRING</b>.
     * @since 1.0
     */
    public function isUsernameTaken($username){
        $user = $this->getUserByUsername($username);
        if($user == self::NO_SUCH_USER){
            return FALSE;
        }
        else if($user instanceof User){
            return TRUE;
        }
        return $user;
    }
    

    /**
     * Checks if a user is already a registered user. A user is considered registered if 
     * his email is already on the system database.
     * @param string $email The email address of the user.
     * @return boolean <b>TRUE</b> if the user email is found. <b>FALSE</b> if 
     * not. <b>MySQLQuery::QUERY_ERR</b> in case of database query error.
     * If the given email is an empty string, 
     * the function will return <b>UserFunctions::EMPTY_STRING</b>.
     * @since 1.0
     */
    public function isUserRegistered($email){
        $user = $this->getUserByEmail($email);
        if($user == self::NO_SUCH_USER){
            return FALSE;
        }
        else if($user instanceof User){
            return TRUE;
        }
        return $user;
    }
    /**
     * Returns the registration token of a user given his ID.
     * @param string $userId The ID of the user.
     * @return string The activation token as a string. If no user was found, 
     * the function will return <b>NULL</b>. If something went wrong while running 
     * database query, the function will return <b>MySQLQuery::QUERY_ERR</b>. 
     * If the given user ID is an empty string, 
     * the function will return <b>UserFunctions::EMPTY_STRING</b>.
     * @since 1.0
     */
    private function getRegTok($userId){
        if(strlen($userId) != 0){
            $this->acQuery->getActivationCode($userId);
            if($this->excQ($this->acQuery)){
                if($this->rows() == 1){
                    $row = $this->getRow();
                    return $row[$this->acQuery->getStructure()->getCol('code')->getName()];
                }
                else{
                    return NULL;
                }
            }
            else{
                return MySQLQuery::QUERY_ERR;
            }
        }
        else{
            return self::EMPTY_STRING;
        }
    }
    /**
     * Adds a new token to the set of activation tokens.
     * @param string $userId The user ID.
     * @return boolean <b>TRUE</b> in case the token is created. The function 
     * will return <b>MySQLQuery::QUERY_ERR</b> in case of database query error. 
     * If the given user ID is an empty string, 
     * the function will return <b>UserFunctions::EMPTY_STRING</b>.
     * @since 1.0
     */
    private function createRegTok($userId){
        if(strlen($userId) != 0){
            $this->acQuery->addNew($userId);
            if($this->excQ($this->acQuery)){
                return TRUE;
            }
            else{
                return MySQLQuery::QUERY_ERR;
            }
        }
        else{
            return self::EMPTY_STRING;
        }
    }
    /**
     * Adds a new user account to the system.
     * @param User $user An object of type <b>User</b>.
     * @return string|User The function will return an object of type <b>User</b> 
     * in case the account is created. Also the function may return <b>MySQLQuery::QUERY_ERR</b> 
     * in case of database error. Also the function might return <b>UserFunctions::USERNAME_TAKEN</b> if 
     * the username of the user is taken. Also the function might return <b>UserFunctions::USER_ALREAY_REG</b> if 
     * the email account of the user is found in the system.
     * @since 1.0
     */
    private function addUser($user){
        $emailCheck = $this->isUserRegistered($user->getEmail());
        if($emailCheck == FALSE){
            $usernameCheck = $this->isUsernameTaken($user->getUserName());
            if($usernameCheck == FALSE){
                if(strlen($user->getPassword()) != 0){
                    $user->setStatus('N');
                    $this->query->addUser($user);
                    if($this->excQ($this->query)){
                        $user = $this->getUserByEmail($user->getEmail());
                        if($this->createRegTok($user->getID()) == TRUE){
                            $tok = $this->getRegTok($user->getID());
                            $user->setActivationTok($tok);
                            return $user;
                        }
                        else{
                            return MySQLQuery::QUERY_ERR;
                        }
                    }
                    else{
                        return MySQLQuery::QUERY_ERR;
                    }
                }
                else{
                    return self::EMPTY_STRING;
                }
            }
            else{
                if($usernameCheck == TRUE){
                    return self::USERNAME_TAKEN;
                }
                return $usernameCheck;
            }
        }
        else{
            if($emailCheck == TRUE){
                return self::USER_ALREAY_REG;
            }
            return $emailCheck;
        }
    }

    /**
     * Adds a new user to the database of the system.
     * @param User $user An object of type <b>User</b>.
     * @return string|User An object of type <b>User</b> if the user is added. 
     * <b>MySQLQuery::QUERY_ERR</b> in case of database query error. 
     * <b>UserFunctions::USERNAME_TAKEN</b> in case the username is taken. 
     * <b>UserFunction::USER_ALREAY_REG</b> if the user email is found in the 
     * system. <b>FALSE</b> in case the given parameter is not an object of 
     * type <b>User</b>. If the given user object has an empty email, username or password, 
     * the function will return <b>UserFunctions::EMPTY_STRING</b>.
     * @since 1.0
     */
    public function register($user){
        if($user instanceof User){
            if($user->getAccessLevel() != 0 && $user->getAccessLevel() != 1){
                $u = $this->addUser($user);
                if($u instanceof User){
                    MailFunctions::get()->sendWelcomeEmail($u);
                }
                return $u;
            }
            else{
                $loggedAccLevel = $this->getAccessLevel();
                if($loggedAccLevel != NULL){
                    if($loggedAccLevel == 0){
                        $u = $this->addUser($user);
                        if($u instanceof User){
                            MailFunctions::get()->sendWelcomeEmail($u);
                        }
                        return $u;
                    }
                    else{
                        return self::NOT_AUTH;
                    }
                }
                else{
                    return self::NOT_AUTH;
                }
            }
        }
        return FALSE;
    }
}