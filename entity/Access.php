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
 * A class to manage user groups and privileges.
 *
 * @author Ibrahim
 * @version 1.0.2
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
     * Returns a single instance of the class.
     * @return Access
     * @since 1.0
     */
    private static function &get(){
        if(self::$access != NULL){
            return self::$access;
        }
        self::$access = new Access();
        return self::$access;
    }
    /**
     * Returns an array which contains all privileges 
     * in a specific group.
     * @param string|NULL $groupId The ID of the group which its 
     * privileges will be returned. If NULL is given, all privileges will be 
     * returned. Default is NULL.
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
                            $userHasPr = $prSplit[1] == '1' ? TRUE : FALSE;
                            if($userHasPr === TRUE){
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
            'given-name'=>$group->getName(),
            'chiled-groups'=>array(),
            'privileges'=>array()
        );
        foreach ($group->childGroups() as $group){
            $retVal['child-groups'][] = $this->_asArrayHelper($group);
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
     * 'A_GROUP' is the ID of a group that the user belongs to. The number 
     * that comes after the dash is the status of the privilege. Each privilege 
     * or a group will be separated from the other by a semicolon. 
     * Also the group will have the letter 'G' at the start.
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
            $groups = Access::groups();
            $str = '';
            $groupsBelongsTo = array();
            foreach ($groups as $group){
                if($user->inGroup($group->getID())){
                    $groupsBelongsTo[] = $group;
                    $str .= 'G-'.$group->getID().';';
                }
            }
            $userPrivileges = $user->privileges();
            if(count($groupsBelongsTo) != 0){
                foreach ($userPrivileges as $privilege){
                    $privilegeHasGroup = FALSE;
                    foreach ($groupsBelongsTo as $group){
                        if($group->hasPrivilege($privilege)){
                            $privilegeHasGroup = TRUE;
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
    private function _clone($groupId,$newGroup,$newPermissions){
        if($this->_hasGroup($groupId)){
            if(!$this->hasGroup($newGroup)){
                if(self::newGroup($newGroup)){
                    $oldGroupPermissions = self::getGroup($groupId)->privileges();
                    foreach ($oldGroupPermissions as $p){
                        self::newPrivilege($newGroup, $p->getID());
                    }
                    foreach ($newPermissions as $p){
                        self::newPrivilege($newGroup, $p);
                    }
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    /**
     * Creates a clone of a group given its ID.
     * The clone group will contain all the privileges which where added to 
     * the original group. Also, it is possible to add extra privileges by 
     * suppling an array that contains the IDs of the new privileges.
     * @param string $groupId The ID of the group that will be cloned. 
     * @param type $newGroupId The ID of the new group. It must be unique.
     * @param type $newPermissions An optional array of new privileges array.
     * @return boolean If the group was cloned, the method will return 
     * TRUE. The method will return FALSE if one of the following conditions 
     * is met:
     * <ul>
     * <li>The group which will be cloned does not exist.</li>
     * <li>A group which has the ID of the new group is already exist.</li>
     * <li>If the new group was not created for some reason.</li>
     * </ul>
     */
    public static function cloneGroup($groupId,$newGroupId,$newPermissions=array()){
        return self::get()->_clone($groupId, $newGroupId, $newPermissions);
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
        if($groupId === NULL){
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
     * Returns an array which contains all user groups. 
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
     * @return PrivilegesGroup|NULL
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
        $g = NULL;
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
        $null = NULL;
        return $null;
    }

    /**
     * Returns a privilege object given privilege ID. 
     * This method will search all created groups for a privilege which has the 
     * given ID. If not found, the method will return NULL. This method also 
     * can be used to check if a privilege is exist or not. If the method 
     * has returned NULL, this means the privilege does not exist.
     * @param string $id The ID of the privilege.
     * @return Privilege|NULL If a privilege with the given ID was found in 
     * any user group, It will be returned. If not, the method will return 
     * NULL.
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
            if($p !== NULL){
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
            if($p !== NULL){
                return $p;
            }
        }
        $p = NULL;
        return $p;
    }
    /**
     * Checks if a privilege does exist or not given its ID. 
     * The method will search all created groups for a privilege with the 
     * given ID.
     * @param string $id The ID of the privilege.
     * @param string $groupId If it is provided, the search for the privilege 
     * will be limited to the group which has the given ID.
     * @param boolean $searchChildern If set to TRUE and group ID is specified, 
     * the search for the privilege will include child groups.
     * @return boolean The method will return TRUE if a privilege 
     * with the given ID was found. FALSE if not.
     * @since 1.0
     */
    public static function hasPrivilege($id,$groupId=null) {
        return Access::get()->_hasPrivilege($id,$groupId);
    }
    
    private function _hasPrivilege($privilegId,$groupId) {
        $retVal = FALSE;
        if($groupId !== NULL){
            foreach ($this->userGroups as $g){
                if($g->getID() == $groupId){
                    foreach ($g->privileges() as $p){
                        if($p->getID() == $privilegId){
                            $retVal = TRUE;
                        }
                        $retVal = FALSE;
                    }
                }
                $retVal = $this->_hasPrivilegeHelper($privilegId, $groupId, $g);
                if($retVal === TRUE){
                    break;
                }
            }
        }
        else{
            foreach ($this->userGroups as $g){
                foreach ($g->privileges() as $p){
                    if($p->getID() == $privilegId){
                        $retVal = TRUE;
                    }
                }
                $retVal = $this->_hasPrivilegeHelper($privilegId, $groupId, $g);
                if($retVal === TRUE){
                    break;
                }
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
        Util::print_r($groupId.' >> '.$group->getID());
        foreach ($group->childGroups() as $g){
            if($groupId !== NULL && $g->getID() == $groupId){
                foreach ($g->privileges() as $p){
                    if($p->getID() == $prId){
                        return TRUE;
                    }
                }
            }
            else{
                foreach ($g->privileges() as $p){
                    if($p->getID() == $prId){
                        return TRUE;
                    }
                }
            }
            return $this->_hasPrivilegeHelper($prId, $groupId, $g);
        }
        return FALSE;
    }
    /**
     * Checks if a users group does exist or not given its ID.
     * @param string $groupId The ID of the group.
     * @return boolean The method will return TRUE if a users group 
     * with the given ID was found. FALSE if not.
     * @since 1.0
     */
    public static function hasGroup($groupId){
        return Access::get()->_hasGroup($groupId);
    }
    /**
     * Returns an object of type UsersGroup given its ID. 
     * This method can be used to check if a group is exist or not. If 
     * the method has returned NULL, this means the group does not exist.
     * @param string $groupId The ID of the group.
     * @return PrivilegesGroup|NULL If a users group with the given ID was found, 
     * It will be returned. If not, the method will return NULL.
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
        return self::getGroup($groupId) !== NULL;
    }
    /**
     * Creates new users group using specific ID.
     * The group is the base for user privileges. After creating it, the developer 
     * can add a set of privileges to the group. Note that the group will not created 
     * only if the name of the group contains invalid characters or it is already 
     * created.
     * @param string $groupId The ID of the group. The ID must not contain 
     * any of the following characters: ';','-' or a space. If the name contains 
     * any of the given characters, the group will not created.
     * @return boolean If the group is created, the method will return TRUE. 
     * If not, the method will return FALSE.
     * @since 1.0
     */
    public static function newGroup($groupId,$parentGroupId=null) {
        return Access::get()->_createGroup($groupId,$parentGroupId);
    }
    
    private function _createGroup($groupId,$parentGroupID=null){
        if($this->_validateId($groupId)){
            foreach ($this->userGroups as $g){
                if($g->getID() == $groupId){
                    return FALSE;
                }
            }
            $group = new PrivilegesGroup();
            $group->setID($groupId);
            if($parentGroupID !== NULL){
                $parentG = &$this->getGroup($parentGroupID);
                if($parentG instanceof PrivilegesGroup){
                    $group->setParentGroup($parentG);
                    return TRUE;
                }
            }
            else{
                $this->userGroups[] = $group;
                return TRUE;
            }
        }
        return FALSE;
    }
    
    private function _validateId($id){
        $len = strlen($id);
        if($len > 0){
            $valid = TRUE;
            for($x = 0 ; $x < $len ; $x++){
                $valid = $valid && $id[$x] != ';' && $id[$x] != ' ' && $id[$x] != '-';
            }
            return $valid;
        }
        return FALSE;
    }
    /**
     * Creates new privilege in a specific group given its ID.
     * @param string $groupId The ID of the group that the privilege will be 
     * added to. It must be a group in the groups array of the access class.
     * @param string $privilegeId The ID of the privilege. The ID must not contain 
     * any of the following characters, ';','-' or a space.
     * @return boolean If the privilege was created, the method will return 
     * TRUE. Other than that, the method will return FALSE.
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
     * privileges. If the privilege is added, the value will be TRUE. If not, 
     * it will be FALSE.
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
            $g = &$this->_getGroup($groupId);
            if(($g instanceof PrivilegesGroup) && $groupId == $g->getID()){
                $p = new Privilege();
                $p->setID($privilegeId);
                if(!$g->hasPrivilege($p)){
                    $g->addPrivilage($p);
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
}
