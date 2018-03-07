<?php
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
 * @version 1.3
 */
class User implements JsonI{
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
        $this->setAccessLevel(ACCESS_LEVEL_2);
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
     * @param string $name Display name.
     * @since 1.2
     */
    public function setDisplayName($name){
        $this->dispName = $name;
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
     * @return string User access token.
     * @since 1.1
     */
    public function getToken(){
        return $this->userTok;
    }
    /**
     * Returns the value of the property <b>$accessLevel</b>
     * @return int 0 or 1 or 2.
     * @since 1.0
     */
    public function getAccessLevel(){
        return $this->accessLevel;
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
        if($this->getToken() !== NULL){
            $json->add('token', $this->getToken());
        }
        if($this->getActivationTok() !== NULL){
            $json->add('activation-token', $this->getActivationTok());
        }
        return $json;
    }
    /**
     * Returns the status of the user.
     * @return string The status of the user.
     * @since 1.3
     */
    public function getStatus(){
        return $this->status;
    }

    /**
     * Sets the value of the property <b>$status</b>
     * @param string $status Status of the user. 
     * @since 1.0
     */
    public function setStatus($status){
        $this->status = $status;
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
     * Returns a string representation of the user.
     * @return string
     * @since 1.0
     */
    public function __toString() {
        $retVal = 'Username: '.$this->getUserName().'<br/>';
        $retVal .= 'Password: '.$this->getPassword().'<br/>';
        $retVal .= 'Email: '.$this->getEmail().'<br/>';
        $retVal .= 'Access Level: '.$this->getAccessLevel().'<br/>';
        $retVal .= 'User ID: '.$this->getID().'<br/>';
        $retVal .= 'Token: '.$this->getToken().'<br/>';
        $retVal .= 'Activation Token: '.$this->getActivationTok().'<br/>';
        return $retVal;
    }
}
