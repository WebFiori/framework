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
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 404 Not Found");
    die('<!DOCTYPE html><html><head><title>Not Found</title></head><body>'
    . '<h1>404 - Not Found</h1><hr><p>The requested resource was not found on the server.</p></body></html>');
}
/**
 * A class to manage user groups and privileges.
 *
 * @author Ibrahim
 * @version 1.0.4
 */
class Access {
    /**
     * An instance of the class.
     * @var Access
     * @since 1.0 
     */
    private static $access;
    /**
     * An array which contains an objects of type UsersGroup.
     * @var PrivilegesGroup
     * @since 1.0 
     */
    private $userGroups;
    /**
     * Creates new instance.
     * @since 1.0
     */
    private function __construct() {
        $this->userGroups = array();
    }
    /**
     * Removes all created user groups and privileges.
     * @since 1.0.4
     */
    public static function clear() {
        self::get()->userGroups = [];
    }
    /**
     * Returns a single instance of the class.
     * @return Access
     * @since 1.0
     */
    private static function &get(){
        if(self::$access !== null){
            return self::$access;
        }
        self::$access = new Access();
        return self::$access;
    }
    /**
     * Returns an array which contains all privileges 
     * in a specific group.
     * @param string|null $groupId The ID of the group which its 
     * privileges will be returned. If null is given, all privileges will be 
     * returned. Default is null.
     * @return array An array which contains an objects of type Privilege. If 
     * the given group ID does not exist, the returned array will be empty.
     * @since 1.0
     */
    public static function privileges($groupId=null) {
        return Access::get()->_privileges($groupId);
    }
    /**
     * Adds privileges to a user given privileges string.
     * @param string $str A string of privileges. The format of the string must 
     * follow the following format: 'PRIVILEGE_1-0;PRIVILEGE_2-1;G-A_GROUP' where 
     * 'PRIVILEGE_1' and 'PRIVILEGE_2' are IDs of privileges and 'A_GROUP' 
     * is the ID of a group that the user belongs to. The number 
     * that comes after the dash is the status of the privilege. If 0, then the 
     * user will not have the given privilege. If 1, the user will have the 
     * privilege. In the given example, The user will have only 'PRIVILEGE_2'. and 
     * he will belong to the group that has the ID 'A_GROUP'. Each 
     * privilege or a group must be separated from the other by a semicolon. Also 
     * the group must have the letter 'G' at the start. Note that in the given 
     * example, if 'PRIVILEGE_1' is in 'A_GROUP', he will not have it even if it is 
     * in group permissions.
     * @param User $user The user which the permissions will be added to
     * @since 1.0
     */
    public static function resolvePriviliges($str,&$user) {
        if(strlen($str) > 0){
            if($user instanceof User){
                $privilegesSplit = explode(';', $str);
                $privilegesToHave = array();
                $privilegesToNotHave = array();
                $groupsBelongsTo = array();
                foreach ($privilegesSplit as $privilegeStr){
                    $prSplit = explode('-', $privilegeStr);
                    if(count($prSplit) == 2){
                        if($prSplit[0] == 'G'){
                            $groupsBelongsTo[] = $prSplit[1];
                        }
                        else{
                            $pirivelegeId = $prSplit[0];
                            $userHasPr = $prSplit[1] == '1' ? true : false;
                            if($userHasPr === true){
                                $privilegesToHave[] = $pirivelegeId;
                            }
                            else{
                                $privilegesToNotHave[] = $pirivelegeId;
                            }
                        }
                    }
                }
                foreach ($groupsBelongsTo as $groupId){
                    $user->addToGroup($groupId);
                }
                foreach ($privilegesToHave as $privilegeId){
                    $user->addPrivilege($privilegeId);
                }
                foreach ($privilegesToNotHave as $privilegeId){
                    $user->removePrivilege($privilegeId);
                }
            }
        }
    }
    /**
     * Returns an array that represents all privileges groups and privileges.
     * The returned array will be indexed array. At each index, there will be 
     * an associative array that represents a privileges group. 
     * The array will contain the following indices:
     * <ul>
     * <li>group-id</li>
     * <li>given-title</li>
     * <li>child-groups</li>
     * <li>privileges</li>
     * </ul>
     * The index 'child-groups' will contain an indexed array of all child groups 
     * of a parent group. The index 'privileges' will contain an indexed array that contains 
     * all the privileges within a group. Each index of the array will contain 
     * an associative array that represents a privilege. The array will have 
     * two indices:
     * <ul>
     * <li>privilege-id</li>
     * <li>given-title</li>
     * </ul>
     * @return array An array that contains all privileges and groups info.
     */
    public static function asArray() {
        return self::get()->_asArray();
    }
    private function _asArray() {
        $retVal = array();
        foreach ($this->userGroups as $group){
            $retVal[] = $this->_asArrayHelper($group);
        }
        return $retVal;
    }
    /**
     * 
     * @param PrivilegesGroup $group
     */
    private function _asArrayHelper($group) {
        $retVal = array(
            'group-id'=>$group->getID(),
            'given-title'=>$group->getName(),
            'child-groups'=>array(),
            'privileges'=>array()
        );
        foreach ($group->childGroups() as $groupX){
            $retVal['child-groups'][] = $this->_asArrayHelper($groupX);
        }
        foreach ($group->privileges() as $pr){
            $retVal['privileges'][] = array(
                'privilege-id'=>$pr->getID(),
                'given-title'=>$pr->getName()
            );
        }
        return $retVal;
    }
    /**
     * Creates a string of permissions given a user.
     * This method can be handy in case the developer would like to store 
     * user privileges in a database. The method might return a string which 
     * might looks like the following string:
     * <p>'PRIVILEGE_1-1;PRIVILEGE_2-1;G-A_GROUP'</p>  
     * where 'PRIVILEGE_1' and 'PRIVILEGE_2' are IDs of privileges and 
     * 'A_GROUP' is the ID of a group that the user has all its privileges. The number 
     * that comes after the dash is the status of the privilege. Each privilege 
     * or a group will be separated from the other by a semicolon. 
     * Also the group will have the letter 'G' at the start. Note that if the group 
     * has sub-groups, this means the user will have the privileges of the sub-groups.
     * @param User $user The user which the permissions string will be created from.
     * @return string A string of user privileges and the groups that he belongs to 
     * (if any).
     * @since 1.0
     */
    public static function createPermissionsStr($user){
        return Access::get()->_createPermissionsStr($user);
    }
    /**
     * 
     * @param User $user
     */
    private function _createPermissionsStr($user) {
        if($user instanceof User){
            $str = '';
            $groupsBelongsTo = array();
            foreach ($this->userGroups as $group){
                $this->__createPermissionsStrHelper($user, $group, $groupsBelongsTo, $str);
            }
            $userPrivileges = $user->privileges();
            if(count($groupsBelongsTo) != 0){
                foreach ($userPrivileges as $privilege){
                    $privilegeHasGroup = false;
                    foreach ($groupsBelongsTo as $group){
                        if($group->hasPrivilege($privilege)){
                            $privilegeHasGroup = true;
                            break;
                        }
                    }
                    if(!$privilegeHasGroup){
                        $str .= $privilege->getID().'-1;';
                    }
                }
            }
            else{
                foreach ($userPrivileges as $privilege){
                    $str .= $privilege->getID().'-1;';
                }
            }
            return trim($str,';');
        }
        return '';
    }
    /**
     * @param User $user Description
     * @param PrivilegesGroup $group
     * @param type $arr
     */
    private function __createPermissionsStrHelper($user,$group,&$arr,&$str){
        if($user->inGroup($group->getID())){
            $arr[] = $group;
            $str .= 'G-'.$group->getID().';';
        }
        else{
            foreach ($group->childGroups() as $groupX){
                if($user->inGroup($groupX->getID())){
                    $arr[] = $groupX;
                    $str .= 'G-'.$groupX->getID().';';
                }
                else{
                    $this->__createPermissionsStrHelper($user, $groupX, $arr, $str);
                }
            }
        }
    }

    private function _privileges($groupId=null){
        $prArr = array();
        foreach ($this->userGroups as $group){
            $this->_privilegesHelper($group, $prArr, $groupId);
        }
        return $prArr;
    }
    /**
     * 
     * @param PrivilegesGroup $group
     * @param type $array
     */
    private function _privilegesHelper($group,&$array,$groupId=null) {
        if($groupId === null){
            foreach ($group->privileges() as $pr){
                $array[] = $pr;
            }
            foreach ($group->childGroups() as $g){
                $this->_privilegesHelper($g, $array,$groupId);
            }
            return;
        }
        else{
            if($group->getID() == $groupId){
                foreach ($group->privileges() as $pr){
                    $array[] = $pr;
                }
                return;
            }
        }
        foreach ($group->childGroups() as $g){
            $this->_privilegesHelper($g, $array,$groupId);
        }
    }
    /**
     * Returns an array which contains all top-level user groups. 
     * The array will be empty if no user groups has been created.
     * @return array An array that contains an objects of type UsersGroup.
     * @since 1.0
     */
    public static function groups(){
        return Access::get()->_groups();
    }
    /**
     * 
     * @return type
     * @since 1.0
     */
    private function _groups(){
        return $this->userGroups;
    }
    /**
     * 
     * @param string $groupId
     * @return PrivilegesGroup|null
     * @since 1.0
     */
    private function &_getGroup($groupId) {
        foreach ($this->userGroups as $g){
            if($g->getID() == $groupId){
                return $g;
            }
            else{
                $g = $this->_getGroupHelper($g, $groupId);
                if($g instanceof PrivilegesGroup){
                    return $g;
                }
            }
        }
        $g = null;
        return $g;
    }
    /**
     * 
     * @param PrivilegesGroup $group
     */
    private function &_getGroupHelper(&$group,$groupId){
        if($groupId == $group->getID()){
            return $group;
        }
        foreach ($group->childGroups() as $groupX){
            $g = $this->_getGroupHelper($groupX, $groupId);
            if($g instanceof PrivilegesGroup){
                return $g;
            }
        }
        $null = null;
        return $null;
    }

    /**
     * Returns a privilege object given privilege ID. 
     * This method will search all created groups for a privilege which has the 
     * given ID. If not found, the method will return null. This method also 
     * can be used to check if a privilege is exist or not. If the method 
     * has returned null, this means the privilege does not exist.
     * @param string $id The ID of the privilege.
     * @return Privilege|null If a privilege with the given ID was found in 
     * any user group, It will be returned. If not, the method will return 
     * null.
     * @since 1.0
     */
    public static function &getPrivilege($id){
        $pr = &Access::get()->_getPrivilege($id);
        return $pr;
    }
    /**
     * 
     * @param type $privId
     * @return type
     */
    private function &_getPrivilege($privId) {
        foreach ($this->userGroups as $g){
            $p = $this->_getPrivilegeH($privId, $g);
            if($p !== null){
                return $p;
            }
        }
        return $p;
    }
    /**
     * 
     * @param type $privId
     * @param PrivilegesGroup $group
     * @return type
     */
    private function &_getPrivilegeH($privId,$group){
        foreach ($group->privileges() as $p){
            if($p->getID() == $privId){
                return $p;
            }
        }
        foreach ($group->childGroups() as $g){
            $p = $this->_getPrivilegeH($privId, $g);
            if($p !== null){
                return $p;
            }
        }
        $p = null;
        return $p;
    }
    /**
     * Checks if a privilege does exist or not given its ID. 
     * The method will search all created groups for a privilege with the 
     * given ID.
     * @param string $id The ID of the privilege.
     * @param string $groupId If it is provided, the search for the privilege 
     * will be limited to the group which has the given ID.
     * @param boolean $searchChildern If set to true and group ID is specified, 
     * the search for the privilege will include child groups.
     * @return boolean The method will return true if a privilege 
     * with the given ID was found. false if not.
     * @since 1.0
     */
    public static function hasPrivilege($id,$groupId=null) {
        return Access::get()->_hasPrivilege($id,$groupId);
    }
    
    private function _hasPrivilege($privilegId,$groupId) {
        $retVal = false;
        foreach ($this->userGroups as $g){
            $retVal = $this->_hasPrivilegeHelper($privilegId, $groupId, $g);
            if($retVal === true){
                break;
            }
        }
        return $retVal;
    }
    /**
     * 
     * @param type $prId
     * @param type $groupId
     * @param type $searchCh
     * @param PrivilegesGroup $group
     */
    private function _hasPrivilegeHelper($prId,$groupId,$group) {
        if($groupId !== null && $group->getID() == $groupId){
            foreach ($group->privileges() as $p){
                if($p->getID() == $prId){
                    return true;
                }
            }
            return false;
        }
        else if($groupId == null){
            foreach ($group->privileges() as $p){
                if($p->getID() == $prId){
                    return true;
                }
            }
            foreach ($group->childGroups() as $g){
                $b = $this->_hasPrivilegeHelper($prId, $groupId, $g);
                if($b === true){
                    return true;
                }
            }
        }
        else{
            foreach ($group->childGroups() as $g){
                $b = $this->_hasPrivilegeHelper($prId, $groupId, $g);
                if($b === true){
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Checks if a users group does exist or not given its ID.
     * @param string $groupId The ID of the group.
     * @return boolean The method will return true if a users group 
     * with the given ID was found. false if not.
     * @since 1.0
     */
    public static function hasGroup($groupId){
        return Access::get()->_hasGroup($groupId);
    }
    /**
     * Returns an object of type UsersGroup given its ID. 
     * This method can be used to check if a group is exist or not. If 
     * the method has returned null, this means the group does not exist.
     * @param string $groupId The ID of the group.
     * @return PrivilegesGroup|null If a users group with the given ID was found, 
     * It will be returned. If not, the method will return null.
     * @since 1.0
     */
    public static function &getGroup($groupId){
        $g = &Access::get()->_getGroup($groupId);
        return $g;
    }
    /**
     * 
     * @param string $groupId
     * @return boolean
     * @since 1.0
     */
    private function _hasGroup($groupId){
        return self::getGroup($groupId) !== null;
    }
    /**
     * Creates new users group using specific ID.
     * The group is the base for user privileges. After creating it, the developer 
     * can add a set of privileges to the group. Note that the group will not created 
     * if the name of the group contains invalid characters or it is already 
     * created. In addition, If a parent group has the given new group name, 
     * it will not be created.
     * @param string $groupId The ID of the group. The ID must not contain 
     * any of the following characters: ';','-',',' or a space. If the name contains 
     * any of the given characters, the group will not created.
     * @return boolean If the group is created, the method will return true. 
     * If not, the method will return false.
     * @since 1.0
     */
    public static function newGroup($groupId,$parentGroupId=null) {
        return Access::get()->_createGroup($groupId,$parentGroupId);
    }
    
    private function _createGroup($groupId,$parentGroupID=null){
        if($this->_validateId($groupId)){
            foreach ($this->userGroups as $g){
                if($g->getID() == $groupId){
                    return false;
                }
            }
            $group = new PrivilegesGroup();
            $group->setID($groupId);
            if($parentGroupID !== null){
                $parentG = &$this->getGroup($parentGroupID);
                if($parentG instanceof PrivilegesGroup){
                    $group->setParentGroup($parentG);
                    return true;
                }
            }
            else{
                $this->userGroups[] = $group;
                return true;
            }
        }
        return false;
    }
    
    private function _validateId($id){
        $len = strlen($id);
        if($len > 0){
            $valid = true;
            for($x = 0 ; $x < $len ; $x++){
                $valid = $valid && $id[$x] != ';' && $id[$x] != ' ' && $id[$x] != '-' && $id[$x] != ',';
            }
            return $valid;
        }
        return false;
    }
    /**
     * Creates new privilege in a specific group given its ID.
     * The method will add the privilege only if it does not exist in any of 
     * the created groups.
     * @param string $groupId The ID of the group that the privilege will be 
     * added to. It must be a group in the groups array of the access class.
     * @param string $privilegeId The ID of the privilege. The ID must not contain 
     * any of the following characters, ';','-',',' or a space.
     * @return boolean If the privilege was created, the method will return 
     * true. Other than that, the method will return false.
     * @since 1.0
     */
    public static function newPrivilege($groupId,$privilegeId){
        return Access::get()->_createPrivilege($groupId, $privilegeId);
    }
    /**
     * Creates multiple privileges in a group given its ID. 
     * This method can be used as a shorthand to create multiple privileges in 
     * a group instead of calling Access::newPrivilege() multiple times.
     * @param string $groupId The ID of the group. The group must be created 
     * before starting to create privileges in it.
     * @param array $prNamesArr An associative array that contains the names of privileges.
     * @return array The method will return an associative array. 
     * The indices will be the IDs of the privileges and the values will be 
     * booleans. Each boolean corresponds to the status of each privilege in the array of 
     * privileges. If the privilege is added, the value will be true. If not, 
     * it will be false.
     * @since 1.0.1 
     */
    public static function newPrivileges($groupId,$prNamesArr) {
        $retVal = array();
        $count = count($prNamesArr);
        for($x = 0 ; $x < $count ; $x++){
            $retVal[$prNamesArr[$x]] = self::newPrivilege($groupId, $prNamesArr[$x]);
        }
        return $retVal;
    }
    /**
     * 
     * @param type $groupId
     * @param type $privilegeId
     * @return boolean Description
     * @since 1.0
     */
    private function _createPrivilege($groupId,$privilegeId){
        if($this->_validateId($privilegeId)){
            $pr = self::getPrivilege($privilegeId);
            if($pr === null){
                $g = &$this->_getGroup($groupId);
                if(($g instanceof PrivilegesGroup) && $groupId == $g->getID()){
                    $p = new Privilege();
                    $p->setID($privilegeId);
                    if(!$g->hasPrivilege($p)){
                        $g->addPrivilage($p);
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
