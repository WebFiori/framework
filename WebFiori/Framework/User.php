<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework;

use WebFiori\Framework\Session\SessionUser;
use WebFiori\Json\Json;
use WebFiori\Json\JsonI;
/**
 * A class that represents a system user.
 *
 * @author Ibrahim
 *
 * @version 1.7.2
 */
class User implements JsonI, SessionUser {
    /**
     * @since 1.2
     *
     * @var string
     */
    private $displayName;
    /**
     * The email address of the user.
     *
     * @var string
     *
     * @since 1.0
     */
    private $email;
    /**
     * The ID of the user.
     *
     * @var int
     *
     * @since 1.0
     */
    private $id;
    /**
     * The last date at which the user did use the system.
     *
     * @var string
     *
     * @since 1.4
     */
    private $lastLogin;
    /**
     * The time and date at which user password was last reseed.
     *
     * @var string
     *
     * @since 1.6
     */
    private $lastPasswordReset;
    /**
     * The password of the user.
     *
     * @var string
     *
     * @since 1.0
     */
    private $password;
    /**
     * The date at which the user registered in the system.
     *
     * @var string
     *
     * @since 1.4
     */
    private $regDate;
    /**
     * The number of times the user has requested a password reset.
     *
     * @var int
     *
     * @since 1.6
     */
    private $resetPassCounts;
    /**
     * The username of the user.
     *
     * @var string
     *
     * @since 1.0
     */
    private $userName;
    /**
     * An array which contains user permissions.
     *
     * @var array
     *
     * @since 1.7
     */
    private $userPrivileges;
    /**
     * Creates new instance of the class.
     *
     * @param string $username Username of the user.
     *
     * @param string $password The login password of the user.
     *
     * @param string $email Email address of the user.
     *
     * @since 1.0
     */
    function __construct(string $username = '', string $password = '', string $email = '') {
        $this->setEmail($email);
        $this->setPassword($password);
        $this->setUserName($username);
        $this->setResetCount(0);
        $this->setID(-1);
        $this->userPrivileges = [];
        $this->setLastPasswordResetDate(null);
    }
    /**
     * Returns a JSON string representation of the user.
     *
     * The Json object will create a JSON string which has the following
     * format:
     * <p>{<br/>
     * &nbsp;&nbsp;"use-id":-1<br/>
     * &nbsp;&nbsp;"email":""<br/>
     * &nbsp;&nbsp;"display-name":""<br/>
     * &nbsp;&nbsp;"username":""<br/>
     * }</p>
     *
     * @return string
     *
     * @since 1.0
     */
    public function __toString() {
        return $this->toJSON().'';
    }
    /**
     * Adds new privilege to the array of user privileges.
     *
     * @param string $privilegeId The ID of the privilege. It must be existed in
     * the class 'Access' or it won't be added. If the privilege is already
     * added, It will be not added again.
     *
     * @return bool The method will return true if the privilege is
     * added. false if not.
     *
     * @since 1.7
     */
    public function addPrivilege(string $privilegeId): bool {
        $p = Access::getPrivilege($privilegeId);

        if ($p != null) {
            foreach ($this->userPrivileges as $prev) {
                if ($prev->getID() == $p->getID()) {
                    return false;
                }
            }
            $this->userPrivileges[] = $p;

            return true;
        }

        return false;
    }
    /**
     * Adds a user to a privileges group given group ID.
     *
     * @param string $groupId The ID of the group.
     *
     * @since 1.7
     */
    public function addToGroup(string $groupId) {
        $g = Access::getGroup($groupId);

        if ($g instanceof PrivilegesGroup) {
            $this->addToGroupHelper($g);
        }
    }
    /**
     * Returns the display name of the user.
     *
     * @return string|null The display name of the user. Default value is
     * null.
     *
     * @since 1.2
     */
    public function getDisplayName() {
        return $this->displayName;
    }
    /**
     * Returns the value of the property '$email'.
     *
     * @return string The value of the property '$email'. Default value is
     * empty string.
     *
     * @since 1.0
     */
    function getEmail(): string {
        return $this->email;
    }
    /**
     * Returns The ID of the user.
     *
     * @return int The ID of the user.
     *
     * @since 1.0
     */
    public function getId() : int {
        return $this->id;
    }
    /**
     * Returns the value of the property '$lastLogin'.
     *
     * @return string|null Last login date. If not set, the method will
     * return null.
     *
     * @since 1.4
     */
    public function getLastLogin() {
        return $this->lastLogin;
    }
    /**
     * Returns the date at which user password was last rested.
     *
     * @return string|null the date at which user password was last rested.
     * If not set, the method will return null.
     *
     * @since 1.6
     */
    public function getLastPasswordResetDate() {
        return $this->lastPasswordReset;
    }
    /**
     * Returns the value of the property '$password'.
     *
     * @return string The value of the property '$password'. Default value is
     * empty string.
     *
     * @since 1.0
     */
    function getPassword(): string {
        return $this->password;
    }

    /**
     * Returns the value of the property '$regDate'.
     *
     * @return string|null
     *
     * @since 1.4
     */
    public function getRegDate() {
        return $this->regDate;
    }
    /**
     * Returns the number of times the user has requested that his password
     * to be rested.
     *
     * @return int The number of times the user has requested that his password
     * to be rested. Default value is 0.
     *
     * @since 1.6
     */
    public function getResetCount(): int {
        return $this->resetPassCounts;
    }
    /**
     * Returns the value of the property '$userName'.
     *
     * @return string The value of the property '$userName'. Default value is
     * empty string.
     *
     * @since 1.0
     */
    function getUserName(): string {
        return $this->userName;
    }
    /**
     * Checks if the user has one of multiple privileges.
     *
     * @param array $privilegesIdsArr An array that contains the IDs of the
     * privileges.
     *
     * @return bool If the user has one of the given privileges, the method
     * will return true. Other than that, the method will return false.
     */
    public function hasAnyPrivilege(array $privilegesIdsArr): bool {
        $hasPr = false;

        foreach ($privilegesIdsArr as $prId) {
            $hasPr = $this->hasPrivilege($prId);

            if ($hasPr) {
                break;
            }
        }

        return $hasPr;
    }
    /**
     * Checks if a user has privilege or not given its ID.
     *
     * @param string $privilegeId The ID of the privilege.
     *
     * @return bool The method will return true if the user has the given
     * privilege. false if not.
     *
     * @since 1.7
     */
    public function hasPrivilege(string $privilegeId) : bool {
        foreach ($this->userPrivileges as $p) {
            if ($p->getID() == $privilegeId) {
                return true;
            }
        }

        return false;
    }
    /**
     * Checks if the user belongs to a privileges group given its ID.
     * A user will be a part of privileges group only if the group has at least
     * one privilege, and he has all the
     * privileges of that group. In addition, he must have all the privileges
     * of all child groups of that group.
     *
     * @param string $groupId The ID of the group.
     *
     * @return bool The method will return true if the user belongs
     * to the users group. The user will be considered a part of the group
     * only if he has all the permissions in the group.
     *
     * @since 1.7
     */
    public function inGroup(string $groupId): bool {
        $g = Access::getGroup($groupId);

        if ($g instanceof PrivilegesGroup) {
            return $this->inGroupHelper($g);
        }

        return false;
    }

    /**
     * Returns an array which contains all user privileges.
     *
     * @return array An array which contains an objects of type Privilege.
     *
     * @since 1.7
     */
    public function privileges(): array {
        return $this->userPrivileges;
    }
    /**
     * Reinitialize the array of user privileges.
     *
     * @since 1.7
     */
    public function removeAllPrivileges() {
        $this->userPrivileges = [];
    }
    /**
     * Removes a privilege from user privileges array given its ID.
     *
     * @param string $privilegeId The ID of the privilege.
     *
     * @return bool If the privilege is removed, the method will
     * return true. Other than that, the method will return false.
     *
     * @since 1.7.1
     */
    public function removePrivilege(string $privilegeId): bool {
        $p = Access::getPrivilege($privilegeId);

        if ($p != null) {
            $count = count($this->userPrivileges);

            for ($x = 0 ; $x < $count ; $x++) {
                $privilege = $this->userPrivileges[$x];

                if ($privilege->getID() == $privilegeId) {
                    while ($x < $count) {
                        if (isset($this->userPrivileges[$x + 1])) {
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
     * Sets the display name of the user.
     *
     * @param string $name Display name. It will be set only if it was a string
     * with length that is greater than 0 (Not empty string). Note that the method will
     * remove any extra spaces in the name.
     *
     * @since 1.2
     */
    public function setDisplayName(string $name) {
        $trimmed = trim($name);

        if (strlen($trimmed) != 0) {
            $this->displayName = $trimmed;
        }
    }
    /**
     * Sets the value of the property '$email'.
     *
     * @param string $email The email to set. Note that the method will
     * use the method 'trim()' in order to trim passed value.
     *
     * @since 1.0
     */
    public function setEmail(string $email) {
        $this->email = trim($email);
    }

    /**
     * Sets the ID of the user.
     *
     * @param int $id The ID of the user.
     *
     * @since 1.0
     */
    public function setID(int $id) {
        $this->id = $id;
    }
    /**
     * Sets the value of the property <b>$lastLogin</b>.
     *
     * @param string $date Last login date.
     *
     * @since 1.4
     */
    public function setLastLogin(string $date) {
        $this->lastLogin = $date;
    }
    /**
     * Sets the date at which user password was rested.
     *
     * @param string $date The date at which user password was rested.
     *
     * @since 1.6
     */
    public function setLastPasswordResetDate(?string $date = null) {
        $this->lastPasswordReset = $date;
    }
    /**
     * Sets the password of a user.
     *
     * @param string $password The password to set.
     *
     * @since 1.0
     */
    function setPassword(string $password) {
        $this->password = $password;
    }
    /**
     * Sets the value of the property '$regDate'.
     *
     * @param string $date Registration date.
     *
     * @since 1.4
     */
    public function setRegDate(string $date) {
        $this->regDate = $date;
    }
    /**
     * Sets the number of times the user has requested that his password
     * to be rested.
     *
     * @param int $times The number of times the user has requested that his password
     * to be rested. Must be an integer greater than -1.
     *
     * @since 1.6
     */
    public function setResetCount(int $times) {
        if ($times >= 0) {
            $this->resetPassCounts = $times;
        }
    }
    /**
     * Sets the username of a user.
     *
     * @param string $username The username to set. Note that the method will
     * use the method 'trim()' in order to trim passed value.
     *
     * @since 1.0
     */
    function setUserName(string $username) {
        $this->userName = trim($username);
    }
    /**
     * Returns a Json object that represents the user.
     *
     * The Json object will create a JSON string which has the following
     * format:
     * <p>{<br/>
     * &nbsp;&nbsp;"useId":-1<br/>
     * &nbsp;&nbsp;"email":""<br/>
     * &nbsp;&nbsp;"displayName":""<br/>
     * &nbsp;&nbsp;"username":""<br/>
     * }</p>
     *
     * @return Json An object of type Json.
     *
     * @since 1.0
     */
    public function toJSON() : Json {
        $json = new Json();
        $json->add('userId', $this->getID());
        $json->add('email', $this->getEmail());
        $json->add('displayName', $this->getDisplayName());
        $json->add('username', $this->getUserName());

        return $json;
    }
    /**
     *
     * @param PrivilegesGroup $group
     */
    private function addToGroupHelper(PrivilegesGroup $group) {
        foreach ($group->privileges() as $p) {
            $this->addPrivilege($p->getID());
        }

        foreach ($group->childGroups() as $g) {
            $this->addToGroupHelper($g);
        }
    }
    /**
     *
     * @param PrivilegesGroup $group
     * @return bool
     */
    private function inGroupHelper(PrivilegesGroup $group): bool {
        $inGroup = true;

        if (count($group->privileges()) === 0) {
            return false;
        }

        foreach ($group->privileges() as $groupPrivilege) {
            $inGroup = $inGroup && $this->hasPrivilege($groupPrivilege->getID());
        }

        if ($inGroup === true) {
            foreach ($group->childGroups() as $g) {
                $inGroup = $inGroup && $this->inGroupHelper($g);
            }
        }

        return $inGroup;
    }
}
