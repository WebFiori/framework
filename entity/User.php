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
 * A class that represents a system user.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.7
 */
class User implements JsonI{
    /**
     * An array which contains user permissions.
     * @var array
     * @since 1.7 
     */
    private $userPrivileges;
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
     * Access token of the user.
     * @var string 
     * @since 1.0
     */
    private $userTok;
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
    /**
     * Creates new instance of the class.
     * @param string $username Username of the user.
     * @param string $password The login password of the user.
     * @param string $email Email address of the user.
     * @since 1.0
     */
    function __construct($username='',$password='',$email=''){
        $this->email = $email;
        $this->password = $password;
        $this->userName = $username;
        $this->resetPassCounts = 0;
        $this->id = -1;
        $this->userPrivileges = array();
    }
    /**
     * Adds a user to a users group given group ID.
     * @param string $groupId The ID of the group.
     * @since 1.7
     */
    public function addToGroup($groupId) {
        $g = Access::getGroup($groupId);
        if($g instanceof UsersGroup){
            foreach ($g->privileges() as $p){
                $this->addPrivilege($p->getID());
            }
        }
    }
    /**
     * Adds new privilege to the array of user privileges.
     * @param string $privilegeId The ID of the privilege. It must be exist in 
     * the class 'Access' or it won't be added. If the privilege is already 
     * added, It will be not added again. 
     * @return boolean The function will return TRUE if the privilege is 
     * added. FALSE if not.
     * @since 1.7
     */
    public function addPrivilege($privilegeId){
        $p = &Access::getPrivilege($privilegeId);
        if($p != NULL){
            foreach ($this->userPrivileges as $prev){
                if($prev->getID() == $p->getID()){
                    return FALSE;
                }
            }
            $this->userPrivileges[] = $p;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Reinitialize the array of user privileges.
     * @since 1.7
     */
    public function removeAllPrivileges() {
        $this->userPrivileges = array();
    }
    /**
     * Checks if the user belongs to a user group given its ID.
     * @param string $groupId The ID of the group.
     * @return boolean The function will return TRUE if the user belongs 
     * to the users group. The user will be considered a part of the group 
     * only if he has all the permissions in the group.
     * @since 1.7
     */
    public function inGroup($groupId) {
        $g = &Access::getGroup($groupId);
        if($g instanceof UsersGroup){
            $inGroup = TRUE;
            foreach ($g->privileges() as $groupPrivilege){
                $inGroup = $inGroup && $this->hasPrivilege($groupPrivilege->getID());
            }
            return $inGroup;
        }
        return FALSE;
    }
    /**
     * Returns an array which contains all user privileges.
     * @return array An array which contains an objects of type Privilege.
     * @since 1.7
     */
    public function privileges() {
        return $this->userPrivileges;
    }
    /**
     * Checks if a user has privilege or not given its ID.
     * @param string $privilegeId The ID of the privilege.
     * @return boolean The function will return TRUE if the user has the given 
     * privilege. FALSE if not.
     * @since 1.7
     */
    public function hasPrivilege($privilegeId) {
        foreach ($this->userPrivileges as $p){
            if($p->getID() == $privilegeId){
                return TRUE;
            }
        }
        return FALSE;
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
        $json->add('email', $this->getEmail());
        $json->add('display-name', $this->getDisplayName());
        $json->add('username', $this->getUserName());
        return $json;
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
