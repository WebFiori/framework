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
 * @version 1.5
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
     * A constant that indicates a user registration is closed.
     * @var string Constant that indicates a user registration is closed.
     * @since 1.5
     */
    const REG_CLOSED = 'reg_closed';
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
     * A constant that indicates a user account is not allowed to login.
     * @var string Constant constant that indicates a user account is not allowed to login.
     * @since 1.5
     */
    const LOGIN_SUSPENDED = 'login_suspended';
    
    /**
     * Checks if a given user info can grant him access to the system or not. It 
     * uses the email address or the username of the user. One is enough to provide.
     * @param string $u Username.
     * @param string $p password.
     * @param string $e Email address.
     * @param int $sessionTimeout The duration of user session (in minutes). It must be a 
     * positive number greater than 0. If invalid number is given, 10 will be used.
     * @param boolean $refreshTimeout If set to TRUE, session timeout time will 
     * be refreshed every time user sends a request.
     * @return boolean|string TRUE if the user is authenticated. Else, it will 
     * return FALSE. In case of database error, the function will return 
     * MySQLQuery::QUERY_ERR. In case the user is suspended from login, 
     * the function will return UserFunctions::LOGIN_SUSPENDED
     * @since 1.0
     */
    public function authenticate($u='',$p='',$e='',$sessionTimeout=10,$refreshTimeout=false){
        if(strlen($p) != 0){
            if(strlen($u) != 0 || strlen($e) != 0){
                $user = new User($u, $p, $e);
                $auth = new Authenticator($user);
                $result = $auth->authenticate();
                if($result === TRUE){
                    $this->getMainSession()->setUser($auth->getUser());
                    if($this->hasPrivilege('LOGIN') === TRUE){
                        $user = $this->getUserByID($this->getUserID());
                        $user->setActivationTok('');
                        $user->setToken($auth->getUser()->getToken());
                        $this->getMainSession()->setUser($user);
                        if($sessionTimeout > 0){
                            $this->getMainSession()->setLifetime($sessionTimeout);
                        }
                        $this->getMainSession()->initSession($refreshTimeout, TRUE);
                        return TRUE;
                    }
                    $this->getMainSession()->kill();
                    return self::LOGIN_SUSPENDED;
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
    public function getUsers() {
        if($this->hasPrivilege('GET_USER_PROFILE_ALL')){
            $this->query->getUsers();
            if($this->excQ($this->query)){
                $result = $this->getDBLink()->getResult();
                $usersArr = array();
                while($row = $result->fetch_assoc()){
                    $usersArr[] = $this->createUserFromRow($row);
                }
                return $usersArr;
            }
            return MySQLQuery::QUERY_ERR;
        }
        return self::NOT_AUTH;
    }
    /**
     * Updates the status of a user account.
     * @param User $user An object of type User.
     * @return boolean|string If the user status is updated, the function will 
     * return TRUE. If not, The function will return FALSE. If the logged in 
     * user does not have the privilege 'UPDATE_USER_STATUS', the function 
     * will return Functions::NOT_AUTH.
     * @since 1.0
     */
    public function updateStatus($user=null) {
        if($this->hasPrivilege('UPDATE_USER_STATUS')){
            if($user instanceof User){
                $this->query->updateStatus($user->getStatusCode(), $user->getID());
                if($this->excQ($this->query)){
                    return TRUE;
                }
                return MySQLQuery::QUERY_ERR;
            }
            return FALSE;
        }
        return self::NOT_AUTH;
    }
    /**
     * Activate user account given his Activation token.
     * @param string $activationTok The activation token of the user.
     * @param string $userId [Optional] If specified, The user which his account will 
     * be activated is the one with the given ID.
     * @return User|string|boolean  An object of type User in case the activation process 
     * is completed. In case of query error, the function will return 
     * MySQLQuery::QUERY_ERR. If the user account is already activated, the 
     * function will return UserFunctions::ALREADY_ACTIVATED. If the given token 
     * does not match, the function will return FALSE. In case the user is not 
     * logged in or the logged in user does not have the privilege 'ACTIVATE_ACCOUNT' while 
     * trying to activate another account, 
     * the function will return Functions::NOT_AUTH.
     * @since 1.0
     */
    public function activateAccount($activationTok,$userId=null){
        if($userId === NULL){
            $id = $this->getUserID();
            if($id !== NULL){
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
        else{
            $user = $this->getUserByID($userId);
            if($user instanceof User){
                $this->acQuery->getActivationCode($user->getID());
                if($this->excQ($this->acQuery)){
                    if($this->rows() != 0){
                        $tok = $this->getRow()[$this->acQuery->getStructure()->getCol('code')->getName()];
                        if($tok == $activationTok){
                            if($this->hasPrivilege('ACTIVATE_ACCOUNT')){
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
                                return self::NOT_AUTH;
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
            return $user;
        }
    }
    /**
     * Updates the display name of a legged in user or another user given his ID.
     * @param string $newDispName The new display name.
     * @param string $userId [Optional] The ID of the user. If omitted, the display name of the 
     * logged in user will be updated.
     * @return User|string  An object of type User in case the display name is updated. 
     * In case no user was found, the function will return UserFunctions::NO_SUCH_USER. In 
     * case of query error, the function will return MySQLQuery::QUERY_ERR. 
     * If the legged in user does not have the permission 'UPDATE_USER_DISPLAY_NAME' or 
     * the privilege 'UPDATE_USER_DISPLAY_NAME_ALL', 
     * the function will return Functions::NOT_AUTH.
     * @since 1.0
     */
    public function updateDisplayName($newDispName, $userId=null){
        if($userId !== NULL && $userId != $this->getUserID()){
            if($this->hasPrivilege('UPDATE_USER_DISPLAY_NAME_ALL')){
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
            return self::NOT_AUTH;
        }
        else{
            if($this->hasPrivilege('UPDATE_USER_DISPLAY_NAME')){
                $this->query->updateDisplayName($newDispName, $this->getUserID());
                if($this->excQ($this->query)){
                    $this->getMainSession()->getUser()->setDisplayName($newDispName);
                    return $this->getMainSession()->getUser();
                }
                else{
                    return MySQLQuery::QUERY_ERR;
                }
            }
            return self::NOT_AUTH;
        }
    }
    /**
     * Updates the email address of a logged in user or another user given his ID.
     * @param string $email The new Email address.
     * @param string $userId [Optional] The ID of the user. If not provided, the email 
     * address of the logged in user will be updated.
     * @return User|string  An object of type User in case the email is updated. 
     * In case no user was found which has the given ID, 
     * the function will return UserFunctions::NO_SUCH_USER. In 
     * case of query error, the function will return MySQLQuery::QUERY_ERR. 
     * If the user does not have the privilege 'UPDATE_USER_EMAIL_ALL' or the privilege 
     * 'UPDATE_USER_EMAIL', the function will return Functions::NOT_AUTH. 
     * If the given email address belongs to a user who is already registered, 
     * the function will return UserFunctions::USER_ALREAY_REG.
     * @since 1.0
     */
    public function updateEmail($email, $userId=null){
        if($userId !== NULL && $userId != $this->getUserID()){
            if($this->hasPrivilege('UPDATE_USER_EMAIL_ALL')){
                $user = $this->getUserByID($userId);
                if($user instanceof User){
                    $check = $this->getUserByEmail($email);
                    if($check == UserFunctions::NO_SUCH_USER){
                        $this->query->updateEmail($email, $user->getID());
                        if($this->excQ($this->query)){
                            $user->setEmail($email);
                            return $user;
                        }
                        else{
                            return MySQLQuery::QUERY_ERR;
                        }
                    }
                    else{
                        return UserFunctions::USER_ALREAY_REG;
                    }
                }
                return $user;
            }
            else{
                return Functions::NOT_AUTH;
            }
        }
        else{
            if($this->hasPrivilege('UPDATE_USER_EMAIL')){
                $check = $this->getUserByEmail($email);
                if($check == UserFunctions::NO_SUCH_USER){
                    $this->query->updateEmail($email, $this->getUserID());
                    if($this->excQ($this->query)){
                        $this->getMainSession()->getUser()->setEmail($email);
                        return $this->getMainSession()->getUser();
                    }
                    else{
                        return MySQLQuery::QUERY_ERR;
                    }
                }
                else{
                    return UserFunctions::USER_ALREAY_REG;
                }
            }
            else{
                return Functions::NOT_AUTH;
            }
        }
    }
    /**
     * Returns logged in user profile or another user given his ID.
     * @param string $id [Optional] The ID of the user. If omitted, the returned 
     * info will belong to the logged in user.
     * @return User|string An object of type User if found. In case no user 
     * was found, the function will return UserFunctions::NO_SUCH_USER. In 
     * case of query error, the function will return MySQLQuery::QUERY_ERR. 
     * If the logged in user does not have the privilege 'GET_USER_PROFILE_ALL' or 
     * the privilege 'GET_USER_PROFILE', the function will return 
     * Functions::NOT_AUTH.
     * @since 1.1
     */
    public function getUserByID($id=null){
        if($id !== NULL){
            if($this->getUserID() != -1 && $this->getUserID() != $id && $this->hasPrivilege('GET_USER_PROFILE_ALL')){
                $this->query->getUserByID($id);
                if($this->excQ($this->query)){
                    if($this->rows() != 0){
                        $row = $this->getRow();
                        return $this->createUserFromRow($row);
                    }
                    return self::NO_SUCH_USER;
                }
                return MySQLQuery::QUERY_ERR;
            }
            else if($this->getUserID() == $id && $this->hasPrivilege('GET_USER_PROFILE')){
                $this->query->getUserByID($id);
                if($this->excQ($this->query)){
                    if($this->rows() != 0){
                        $row = $this->getRow();
                        return $this->createUserFromRow($row);
                    }
                    return self::NO_SUCH_USER;
                }
                return MySQLQuery::QUERY_ERR;
            }
            return self::NOT_AUTH;
        }
        else if($this->hasPrivilege('GET_USER_PROFILE')){
            $this->query->getUserByID($this->getUserID());
            if($this->excQ($this->query)){
                $user = new User();
                $row = $this->getRow();
                return $user;
            }
            else{
                return MySQLQuery::QUERY_ERR;
            }
        }
        return self::NOT_AUTH;
    }
    /**
     * Returns logged in user profile or another user given his username.
     * @param string $username [Optional] The username of the user. If omitted, 
     * the returned profile will belong to the logged in user. In this case, 
     * this parameter is ignored.
     * @return User|string An object of type User if found. If the user is not 
     * found, the function will return UserFunctions::NO_SUCH_USER. If 
     * an error occur while running the query on the database, The function will 
     * return MySQLQuery::QUERY_ERR. If the given username is an empty string, 
     * the function will return UserFunctions::EMPTY_STRING. 
     * If the logged in user does not have the privilege 'GET_USER_PROFILE_ALL' 
     * or the privilege 'GET_USER_PROFILE', the function will return 
     * Functions::NOT_AUTH.
     * @since 1.0
     */
    public function getUserByUsername($username=null){
        if($username !== null){
            if($this->getUserID() != -1){
                if($this->getMainSession()->getUser()->getUserName() != $username && $this->hasPrivilege('GET_USER_PROFILE_ALL')){
                    $this->query->getUserByUsername($username);
                    if($this->excQ($this->query)){
                        $row = $this->getRow();
                        if($row != null){
                            return $this->createUserFromRow($row);
                        }
                        return self::NO_SUCH_USER;
                    }
                    return MySQLQuery::QUERY_ERR;
                }
                else if($this->getMainSession()->getUser()->getUserName() == $username && $this->hasPrivilege('GET_USER_PROFILE')){
                    return $this->getMainSession()->getUser();
                }
                return self::NOT_AUTH;
            }
        }
        else if($this->hasPrivilege('GET_USER_PROFILE')){
            return $this->getMainSession()->getUser();
        }
        return self::NOT_AUTH;
    }
    /**
     * 
     * @param User $user
     * @since 1.5
     */
    public function updateUserPrivileges($user) {
        if($user instanceof User){
            if($this->hasPrivilege('UPDATE_USER_PERMISSIONS') && $this->getUserID() != $user->getID()){
                $this->query->updateUserPermissions(Access::createPermissionsStr($user),$user->getID());
                if($this->excQ($this->query)){
                    return TRUE;
                }
                return MySQLQuery::QUERY_ERR;
            }
            return self::NOT_AUTH;
        }
        return FALSE;
    }
    /**
     * 
     * @param type $row
     * @return User
     * @since 1.5
     */
    private function createUserFromRow($row){
        $user = new User(
                $row[$this->query->getColName('username')],
                '',
                $row[$this->query->getColName('email')]);
        $user->setID($row[MySQLQuery::ID_COL]);
        $user->setDisplayName($row[$this->query->getStructure()->getCol('disp-name')->getName()]);
        $user->setLastLogin($row[$this->query->getStructure()->getCol('last-login')->getName()]);
        $user->setRegDate($row[$this->query->getStructure()->getCol('reg-date')->getName()]);
        $user->setLastPasswordResetDate($row[$this->query->getColName('last-password-reset')]);
        $user->setResetCount($row[$this->query->getColName('reset-pass-count')]);
        $tok = $this->getRegTok($user->getID());
        $user->setActivationTok($tok);
        Access::resolvePriviliges($row[$this->query->getColName('privileges')], $user);
        return $user;
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
    public function getUserByEmail($email=null){
        if($email !== NULL){
            if($this->getUserID() != -1){
                if($email != $this->getMainSession()->getUser()->getEmail() && $this->hasPrivilege('GET_USER_PROFILE_ALL')){
                    $this->query->getUserByEmail($email);
                    if($this->excQ($this->query)){
                        $row = $this->getRow();
                        if($row != null){
                            return $this->createUserFromRow($row);
                        }
                        return self::NO_SUCH_USER;
                    }
                    return MySQLQuery::QUERY_ERR;
                }
                else if($email != $this->getMainSession()->getUser()->getEmail() && $this->hasPrivilege('GET_USER_PROFILE')){
                    return $this->getMainSession()->getUser();
                }
                return self::NOT_AUTH;
            }
        }
        else if($this->hasPrivilege('GET_USER_PROFILE')){
            return $this->getMainSession()->getUser();
        }
        return self::NOT_AUTH;
    }
    /**
     * Checks if a given username is taken or not.
     * @param string $username The username that will be checked.
     * @return boolean TRUE if the user name is taken. FALSE if 
     * not taken. MySQLQuery::QUERY_ERR in case of database error. 
     * If the given username is an empty string, 
     * the function will return UserFunctions::EMPTY_STRING.
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
     * @return boolean TRUE if the user email is found. FALSE if 
     * not. MySQLQuery::QUERY_ERR in case of database query error.
     * If the given email is an empty string, 
     * the function will return UserFunctions::EMPTY_STRING.
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
     * the function will return NULL. If something went wrong while running 
     * database query, the function will return MySQLQuery::QUERY_ERR. 
     * If the given user ID is an empty string, 
     * the function will return UserFunctions::EMPTY_STRING.
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
     * @return boolean TRUE in case the token is created. The function 
     * will return MySQLQuery::QUERY_ERR in case of database query error. 
     * If the given user ID is an empty string, 
     * the function will return UserFunctions::EMPTY_STRING.
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
     * @param User $user An object of type User.
     * @return string|User The function will return an object of type User 
     * in case the account is created. Also the function may return MySQLQuery::QUERY_ERR 
     * in case of database error. Also the function might return UserFunctions::USERNAME_TAKEN if 
     * the username of the user is taken. Also the function might return <b>UserFunctions::USER_ALREAY_REG if 
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
                    $user->addToGroup('BASIC_USER');
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
     * @param User $user An object of type User.
     * @return string|User An object of type User if the user is added. 
     * MySQLQuery::QUERY_ERR in case of database query error. 
     * UserFunctions::USERNAME_TAKEN in case the username is taken. 
     * UserFunction::USER_ALREAY_REG if the user email is found in the 
     * system. FALSE in case the given parameter is not an object of 
     * type User. If the given user object has an empty email, username or password, 
     * the function will return UserFunctions::EMPTY_STRING. If the registration 
     * is closed or the admin has no permission to create users, the function will 
     * return Functions::NOT_AUTH.
     * @since 1.0
     */
    public function register($user){
        if($user instanceof User){
            if(Config::get()->getUserRegStatus() == 'O'){
                $u = $this->addUser($user);
                if($u instanceof User){
                    MailFunctions::get()->sendWelcomeEmail($u);
                }
                return $u;
            }
            else if(Config::get()->getUserRegStatus() == 'AO'){
                if($this->hasPrivilege('ADD_USER')){
                    $u = $this->addUser($user);
                    if($u instanceof User){
                        MailFunctions::get()->sendWelcomeEmail($u);
                    }
                    return $u;
                }
            }
            else{
                return self::REG_CLOSED;
            }
        }
        return FALSE;
    }
}