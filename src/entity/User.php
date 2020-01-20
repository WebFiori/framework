<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
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
namespace webfiori\entity;
use jsonx\JsonI;
use jsonx\JsonX;
/**
 * A class that represents a system user.
 * @author Ibrahim
 * @version 1.7.2
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
     * The time and date at which user password was last reseed.
     * @var string
     * @since 1.6 
     */
    private $lastPasswordReseted;
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
        $this->setEmail($email);
        $this->setPassword($password);
        $this->setUserName($username);
        $this->setResetCount(0);
        $this->setID(-1);
        $this->userPrivileges = [];
        $this->setLastPasswordResetDate(null);
    }
    /**
     * Adds a user to a privileges group given group ID.
     * @param string $groupId The ID of the group.
     * @since 1.7
     */
    public function addToGroup($groupId) {
        $g = Access::getGroup($groupId);
        if($g instanceof PrivilegesGroup){
            $this->_addToGroup($g);
        }
    }
    /**
     * 
     * @param PrivilegesGroup $group
     */
    private function _addToGroup($group) {
        foreach ($group->privileges() as $p){
            $this->addPrivilege($p->getID());
        }
        foreach ($group->childGroups() as $g){
            $this->_addToGroup($g);
        }
    }
    /**
     * Adds new privilege to the array of user privileges.
     * @param string $privilegeId The ID of the privilege. It must be exist in 
     * the class 'Access' or it won't be added. If the privilege is already 
     * added, It will be not added again. 
     * @return boolean The method will return true if the privilege is 
     * added. false if not.
     * @since 1.7
     */
    public function addPrivilege($privilegeId){
        $p = Access::getPrivilege($privilegeId);
        if($p != null){
            foreach ($this->userPrivileges as $prev){
                if($prev->getID() == $p->getID()){
                    return false;
                }
            }
            $this->userPrivileges[] = $p;
            return true;
        }
        return false;
    }
    /**
     * Removes a privilege from user privileges array given its ID.
     * @param string $privilegeId The ID of the privilege.
     * @return boolean If the privilege is removed, the method will 
     * return true. Other than that, the method will return false.
     * @since 1.7.1
     */
    public function removePrivilege($privilegeId) {
        $p = Access::getPrivilege($privilegeId);
        if($p != null){
            $count = count($this->userPrivileges);
            for($x = 0 ; $x < $count ; $x++){
                $privilege = $this->userPrivileges[$x];
                if($privilege->getID() == $privilegeId){
                    while ($x < $count){
                        if(isset($this->userPrivileges[$x + 1])){
                            $this->userPrivileges[$x] = $this->userPrivileges[$x + 1];
                        }
                        $x++;
                    }
                    unset($this->userPrivileges[$x - 1]);
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Reinitialize the array of user privileges.
     * @since 1.7
     */
    public function removeAllPrivileges() {
        $this->userPrivileges = [];
    }
    /**
     * Checks if the user belongs to a privileges group given its ID.
     * A user will be a part of privileges group only if the group has at least 
     * one privilege and he has all the 
     * privileges of that group. In addition, he must have all the privileges 
     * of all child groups of that group.
     * @param string $groupId The ID of the group.
     * @return boolean The method will return true if the user belongs 
     * to the users group. The user will be considered a part of the group 
     * only if he has all the permissions in the group.
     * @since 1.7
     */
    public function inGroup($groupId) {
        $g = Access::getGroup($groupId);
        if($g instanceof PrivilegesGroup){
            return $this->_inGroup($g);
        }
        return false;
    }
    /**
     * 
     * @param PrivilegesGroup $group
     * @return type
     */
    private function _inGroup($group){
        $inGroup = true;
        if(count($group->privileges()) !== 0){
            foreach ($group->privileges() as $groupPrivilege){
                $inGroup = $inGroup && $this->hasPrivilege($groupPrivilege->getID());
            }
        }
        else{
            $inGroup = false;
        }
        if($inGroup === true){
            foreach ($group->childGroups() as $g){
                $inGroup = $inGroup && $this->_inGroup($g);
            }
        }
        return $inGroup;
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
     * @return boolean The method will return true if the user has the given 
     * privilege. false if not.
     * @since 1.7
     */
    public function hasPrivilege($privilegeId) {
        foreach ($this->userPrivileges as $p){
            if($p->getID() == $privilegeId){
                return true;
            }
        }
        return false;
    }
    /**
     * Returns the value of the property '$lastLogin'.
     * @return string|null Last login date. If not set, the method will 
     * return null.
     * @since 1.4
     */
    public function getLastLogin(){
        return $this->lastLogin;
    }
    /**
     * Returns the value of the property '$regDate'.
     * @param string|null $date Registration date. If not set, the method will
     * return null.
     * @since 1.4
     */
    public function getRegDate(){
        return $this->regDate;
    }
    /**
     * Returns the date at which user password was reseted.
     * @return string|null the date at which user password was reseted. 
     * If not set, the method will return null.
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
     * Returns the number of times the user has requested that his password 
     * to be reseted.
     * @return int The number of times the user has requested that his password 
     * to be reseted. Default value is 0.
     * @since 1.6
     */
    public function getResetCount() {
        return $this->resetPassCounts;
    }
    /**
     * Sets the number of times the user has requested that his password 
     * to be reseted.
     * @param int $times The number of times the user has requested that his password 
     * to be reseted. Must be an integer greater than -1.
     * @since 1.6
     */
    public function setResetCount($times) {
        if(gettype($times) == 'integer' && $times >= 0){
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
     * Sets the value of the property '$regDate'.
     * @param string $date Registration date.
     * @since 1.4
     */
    public function setRegDate($date){
        $this->regDate = $date;
    }
    /**
     * Returns the display name of the user.
     * @return string|null The display name of the user. Default value is 
     * null.
     * @since 1.2
     */
    public function getDisplayName() {
        return $this->dispName;
    }
    /**
     * Sets the display name of the user.
     * @param string $name Display name. It will be set only if it was a string 
     * with length that is greater than 0 (Not empty string). Note that the method will 
     * remove any extra spaces in the name.
     * @since 1.2
     */
    public function setDisplayName($name){
        $trimmed = trim($name);
        if(strlen($trimmed) != 0){
            $this->dispName = $trimmed;
        }
    }
    /**
     * Returns a JsonX object that represents the user.
     * The JsonX object will create a JSON string which has the following 
     * format:
     * <p>{<br/>
     * &nbsp;&nbsp;"use-id":-1<br/>
     * &nbsp;&nbsp;"email":""<br/>
     * &nbsp;&nbsp;"display-name":""<br/>
     * &nbsp;&nbsp;"username":""<br/>
     * }</p>
     * @return JsonX An object of type JsonX.
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
     * @param string $username The username to set. Note that the method will 
     * use the method 'trim()' in order to trim passed value.
     * @since 1.0
     */
    function setUserName($username){
        $this->userName = trim($username);
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
     * Sets the value of the property '$email'.
     * @param string $email The email to set. Note that the method will 
     * use the method 'trim()' in order to trim passed value.
     * @since 1.0
     */
    public function setEmail($email){
        $this->email = trim($email);
    }
    /**
     * Returns the value of the property '$userName'.
     * @return string The value of the property '$userName'. Default value is 
     * empty string.
     * @since 1.0
     */
    function getUserName(){
        return $this->userName;
    }
    /**
     * Returns the value of the property '$password'.
     * @return string The value of the property '$password'. Default value is 
     * empty string.
     * @since 1.0
     */
    function getPassword(){
        return $this->password;
    }
    /**
     * Returns the value of the property '$email'.
     * @return string The value of the property '$email'. Default value is 
     * empty string.
     * @since 1.0
     */
    function getEmail(){
        return $this->email;
    }
    /**
     * Returns a JSON string representation of the user.
     * The JsonX object will create a JSON string which has the following 
     * format:
     * <p>{<br/>
     * &nbsp;&nbsp;"use-id":-1<br/>
     * &nbsp;&nbsp;"email":""<br/>
     * &nbsp;&nbsp;"display-name":""<br/>
     * &nbsp;&nbsp;"username":""<br/>
     * }</p>
     * @return string
     * @since 1.0
     */
    public function __toString() {
        return $this->toJSON().'';
    }
}
