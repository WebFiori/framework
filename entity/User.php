<?php
if(!defined('ROOT_DIR')){
    http_response_code(403);
    die('{"message":"Forbidden"}');
}
/**
 * A user with this level of access have control over every thing (An Admin).
 */
define('ACCESS_LEVEL_0',0);
/**
 * You define what this user can do.
 */
define('ACCESS_LEVEL_1',1);
/**
 * You define what this user can do.
 */
define('ACCESS_LEVEL_2',2);
/**
 * You define what this user can do.
 */
define('ACCESS_LEVEL_3',3);
/**
 * You define what this user can do.
 */
define('ACCESS_LEVEL_4',4);
/**
 * You define what this user can do.
 */
define('ACCESS_LEVEL_5',5);
/**
 * A class that represents a system user.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.6
 */
class User implements JsonI{
    /**
     * A set of possible user status.
     * @var array An array of user status.
     * @since 1.5
     */
    const USER_STATS = array(
        'N'=>'New',
        'A'=>'Active',
        'S'=>'Suspended'
    );
    /**
     * The number of times the user has requested a password reset.
     * @var int
     * @since 1.6 
     */
    private $resetPassCounts;
    /**
     * The reset token that is used to reset user's password
     * @var string
     * @since 1.6 
     */
    private $resetTok;
    /**
     * The time and date at which user password was last reseed.
     * @var string
     * @since 1.6 
     */
    private $lastPasswordReseted;
    /**
     * The time and date at which the user has requested password reset
     * @var string 
     * @since 1.6
     */
    private $resetRequestTime;
    /**
     * A code for the user status.
     * @var string
     * @since 1.5 
     */
    private $statusCode;
    /**
     * The last date at which the user did use the system.
     * @var string
     * @since 1.4 
     */
    private $lastLogin;
    /**
     * The date at which the user registered in the system.
     * @var string
     * @since 1.4 
     */
    private $regDate;
    /**
     * The username of the user.
     * @var string 
     * @since 1.0
     */
    private $userName;
    /**
     * The password of the user.
     * @var string 
     * @since 1.0
     */
    private $password;
    /**
     * The email address of the user.
     * @var string 
     * @since 1.0
     */
    private $email;
    /**
     * The ID of the user.
     * @var int 
     * @since 1.0
     */
    private $id;
    /**
     * Access level of the user.
     * @var int 
     * @since 1.0
     */
    private $accessLevel;
    /**
     * Access token of the user.
     * @var string 
     * @since 1.0
     */
    private $userTok;
    /**
     * The status of the user profile (active, suspended ...)
     * @var string 
     * @since 1.1
     */
    private $status;
    /**
     * The activation token of the user.
     * @var string
     * @since 1.3 
     */
    private $activationTok;
    /**
     * @since 1.2
     * @var string 
     */
    private $dispName;
    function __construct($username='',$password='',$email=''){
        $this->email = $email;
        $this->password = $password;
        $this->userName = $username;
        $this->resetPassCounts = 0;
        $this->id = 0;
        $this->setAccessLevel(ACCESS_LEVEL_2);
    }
    /**
     * Returns the value of the property <b>$lastLogin</b>.
     * @return string Last login date.
     * @since 1.4
     */
    public function getLastLogin(){
        return $this->lastLogin;
    }
    /**
     * Returns the value of the property <b>$regDate</b>.
     * @param string $date Registration date.
     * @since 1.4
     */
    public function getRegDate(){
        return $this->regDate;
    }
    /**
     * Returns a string that represents the time at which the user has 
     * request password reset.
     * @return string|NULL A string that represents the time at which the user has 
     * request password reset. If not set, the function will return <b>NULL</b>
     * @since 1.6
     */
    public function getResetRequestTime() {
        return $this->resetRequestTime;
    }
    /**
     * Sets the time at which the user has requested password reset.
     * @param string $time The time at which the user has requested password reset.
     * @since 1.6
     */
    public function setResetRequestTime($time) {
        $this->resetRequestTime = $time;
    }
    /**
     * Returns the date at which user password was reseted.
     * @return string|NULL the date at which user password was reseted. 
     * If not set, the function will return <b>NULL</b>.
     * @since 1.6
     */
    public function getLastPasswordResetDate() {
        return $this->lastPasswordReseted;
    }
    /**
     * Sets the date at which user password was reseted.
     * @param string $date The date at which user password was reseted.
     * @since 1.6
     */
    public function setLastPasswordResetDate($date) {
        $this->lastPasswordReseted = $date;
    }
    /**
     * Returns password reset token.
     * @return string|NULL Password reset token. If not set, 
     * the function will return <b>NULL</b>.
     * @since 1.6
     */
    public function getResetToken() {
        return $this->resetTok;
    }
    /**
     * Sets password reset token.
     * @param string $tok Password reset token.
     * @since 1.6
     */
    public function setResetToken($tok) {
        $this->resetTok = $tok;
    }
    /**
     * Returns the number of times the user has requested that his password 
     * to be reseted.
     * @return int The number of times the user has requested that his password 
     * to be reseted.
     * @since 1.6
     */
    public function getResetCount() {
        return $this->resetPassCounts;
    }
    /**
     * Sets the number of times the user has requested that his password 
     * to be reseted.
     * @param int $times The number of times the user has requested that his password 
     * to be reseted.
     * @since 1.6
     */
    public function setResetCount($times) {
        if(gettype($times) == 'integer'){
            $this->resetPassCounts = $times;
        }
    }
    /**
     * Sets the value of the property <b>$lastLogin</b>.
     * @param string $date Last login date date.
     * @since 1.4
     */
    public function setLastLogin($date){
        $this->lastLogin = $date;
    }
    /**
     * Sets the value of the property <b>$regDate</b>.
     * @param string $date Registration date.
     * @since 1.4
     */
    public function setRegDate($date){
        $this->regDate = $date;
    }
    /**
     * Sets the activation token of the user.
     * @param string $tok User account activation token.
     * @since 1.3
     */
    public function setActivationTok($tok){
        $this->activationTok = $tok;
    }
    /**
     * Returns the activation token of the user.
     * @return string Activation token.
     * @since 1.3
     */
    public function getActivationTok(){
        return $this->activationTok;
    }

    /**
     * Returns the display name of the user.
     * @return string The display name of the user.
     * @since 1.2
     */
    public function getDisplayName() {
        return $this->dispName;
    }
    /**
     * Sets the display name of the user.
     * @param string $name Display name. It will be set only if it was a string 
     * with length that is greater than 0 (Not empty string).
     * @since 1.2
     */
    public function setDisplayName($name){
        if(gettype($name) == 'string' && strlen($name) != 0){
            $this->dispName = $name;
        }
    }

    /**
     * Sets the value of the property <b>$userTok</b>
     * @param string $tok User token.
     * @since 1.1
     */
    public function setToken($tok){
        $this->userTok = $tok;
    }
    /**
     * Returns the value of the property <b>$userTok</b>
     * @return string|NULL User access token. If not set, the function 
     * will return <b>NULL</b>.
     * @since 1.1
     */
    public function getToken(){
        return $this->userTok;
    }
    /**
     * Returns the value of the property <b>$accessLevel</b>
     * @return int 0 or 1 or 2 etc...
     * @since 1.0
     */
    public function getAccessLevel(){
        return $this->accessLevel;
    }
    /**
     * Returns status code.
     * @return string|NULL The status code of the user (such as 'A'). 
     * If user status is not set, the function will return <b>NULL</b>.
     * @see User::setStatus($status)
     * @since 1.5
     */
    public function getStatusCode(){
        return $this->statusCode;
    }
    /**
     * Returns a JsonX object that represents the user.
     * @return string A JSON string.
     * @since 1.0
     */
    public function toJSON(){
        $json = new JsonX();
        $json->add('user-id', $this->getID());
        $json->add('access-level', $this->getAccessLevel());
        $json->add('email', $this->getEmail());
        $json->add('status', $this->getStatus());
        $json->add('status-code', $this->getStatusCode());
        $json->add('reg-date', $this->getRegDate());
        $json->add('last-login', $this->getLastLogin());
        $json->add('display-name', $this->getDisplayName());
        $json->add('token', $this->getToken());
        $json->add('reset-count', $this->getResetCount());
        $json->add('reset-time', $this->getResetRequestTime());
        $json->add('reset-token', $this->getResetToken());
        $json->add('last-reset-time', $this->getLastPasswordResetDate());
        return $json;
    }
    /**
     * Returns the status of the user.
     * @return string|NULL The status of the user. 
     * If user status is not set, the function will return <b>NULL</b>.
     * @see User::setStatus($status)
     * @since 1.3
     */
    public function getStatus(){
        return $this->status;
    }

    /**
     * Sets the value of the property <b>$status</b>
     * @param string $status Status code. It must be a key value 
     * from the array <b>User::USER_STATS</b>.
     * @return boolean The function will return <b>TRUE</b> if the status 
     * is updated.
     * @since 1.0
     */
    public function setStatus($status){
        if(array_key_exists($status, User::USER_STATS)){
            $this->status = User::USER_STATS[$status];
            $this->statusCode = $status;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Sets the value of the property <b>accessLevel</b>
     * @param int $acc 0, 1, 2, 3, 4 or 5. If the given value does not equal to any one 
     * of the three, 5 will be used.
     * @since 1.0
     */
    public function setAccessLevel($acc){
        if($acc == ACCESS_LEVEL_0 || 
                $acc == ACCESS_LEVEL_1 || 
                $acc == ACCESS_LEVEL_2 || 
                $acc == ACCESS_LEVEL_3 || 
                $acc == ACCESS_LEVEL_4 || 
                $acc == ACCESS_LEVEL_5){
            $this->accessLevel = $acc;
        }
        else{
            $this->accessLevel = 5;
        }
    }
    /**
     * Sets the ID of the user.
     * @param int $id The ID of the user.
     * @since 1.0
     */
    public function setID($id){
        $this->id = $id;
    }
    /**
     * Returns The ID of the user.
     * @return int The ID of the user.
     * @since 1.0
     */
    public function getID(){
        return $this->id;
    }
    /**
     * Sets the user name of a user.
     * @param string $username The username to set.
     * @since 1.0
     */
    function setUserName($username){
        $this->userName = $username;
    }
    /**
     * Sets the password of a user.
     * @param string $password The password to set.
     * @since 1.0
     */
    function setPassword($password){
        $this->password = $password;
    }
    /**
     * Sets the value of the property <b>$email</b>.
     * @param string $email The email to set.
     * @since 1.0
     */
    public function setEmail($email){
        $this->email = $email;
    }
    /**
     * Returns the value of the property <b>$userName</b>.
     * @return string The value of the property <b>$userName</b>.
     * @since 1.0
     */
    function getUserName(){
        return $this->userName;
    }
    /**
     * Returns the value of the property <b>$password</b>.
     * @return string The value of the property <b>$password</b>.
     * @since 1.0
     */
    function getPassword(){
        return $this->password;
    }
    /**
     * Returns the value of the property <b>$email</b>.
     * @return string The value of the property <b>$email</b>.
     * @since 1.0
     */
    function getEmail(){
        return $this->email;
    }
    /**
     * Returns a JSON string representation of the user.
     * @return string
     * @since 1.0
     */
    public function __toString() {
        return $this->toJSON().'';
    }
}
