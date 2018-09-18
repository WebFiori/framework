<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
 * @version 1.0
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
     * @var UsersGroup
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
     * Returns an array which contains all privileges or privileges 
     * in a specific user group.
     * @param string|NULL $groupId [Optional] The ID of the group which its 
     * privileges will be returned. If NULL is given, all privileges will be 
     * returned. 
     * Default is NULL.
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
     * follow the following format: 'PRIVILEGE_1-0;PRIVILEGE_2-1;' where 
     * 'PRIVILEGE_1' and 'PRIVILEGE_2' are names of privileges and the number 
     * that comes after the dash is the status of the privilege. If 0, then the 
     * user will not have the given privilege. If 1, the user will have the 
     * privilege. In the given example, The user will have only 'PRIVILEGE_2'. Each 
     * privilege must be separated from the other by a semicolon.
     * @param User $user The user which the permissions will be added to
     * @since 1.0
     */
    public static function resolvePriviliges($str,&$user) {
        if(strlen($str) > 0){
            if($user instanceof User){
                $privilegesSplit = explode(';', $str);
                foreach ($privilegesSplit as $privilegeStr){
                    $prSplit = explode('-', $privilegeStr);
                    if(count($prSplit) == 2){
                        $pirivelegeId = $prSplit[0];
                        $userHasPr = $prSplit[1] == '1' ? TRUE : FALSE;
                        if($userHasPr === TRUE){
                            $user->addPrivilege($pirivelegeId);
                        }
                    }
                }
            }
        }
    }
    /**
     * Creates a string of permissions given a user.
     * @param User $user The user which the permissions string 
     * will be created from.
     * @return string A string of privileges. The format of the string will 
     * follow the following format: 'PRIVILEGE_1-0;PRIVILEGE_2-1;' where 
     * 'PRIVILEGE_1' and 'PRIVILEGE_2' are names of privileges and the number 
     * that comes after the dash is the status of the privilege. If 0, then the 
     * user will not have the given privilege. If 1, the user will have the 
     * privilege. In the given example, The user will have only 'PRIVILEGE_2'. Each 
     * privilege will be separated from the other by a semicolon.
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
            $privileges = Access::privileges();
            $str = '';
            $count = count($privileges);
            $index = 0;
            foreach ($privileges as $pr){
                if($user->hasPrivilege($pr->getID())){
                    if($index + 1 == $count){
                        $str .= $pr->getID().'-1';
                    }
                    else{
                        $str .= $pr->getID().'-1;';
                    }
                }
                else{
                    if($index + 1 == $count){
                        $str .= $pr->getID().'-0';
                    }
                    else{
                        $str .= $pr->getID().'-0;';
                    }
                }
                $index++;
            }
            return $str;
        }
        return '';
    }
    
    private function _privileges($groupId=null){
        if($groupId != NULL){
            foreach ($this->userGroups as $group){
                if($group->getID() == $groupId){
                    return $group->privileges();
                }
            }
            return array();
        }
        else{
            $prArr = array();
            foreach ($this->userGroups as $group){
                foreach ($group->privileges() as $pr){
                    $prArr[] = $pr;
                }
            }
            return $prArr;
        }
    }
    /**
     * Adds new group with new ID.
     * @param string $groupId The ID of the group. If a group with the given 
     * ID already exist, The function will not add it.
     * @return boolean The function will return TRUE if a new group with the 
     * given ID is created. FALSE if not.
     * @since 1.0
     */
    public function addGroup($groupId) {
        foreach ($this->userGroups as $group){
            if($groupId == $group->getID()){
                return FALSE;
            }
            $g = new UsersGroup();
            $g->setID($groupId);
            $this->userGroups[] = $g;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns an array which contains all user groups.
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
     * @return UsersGroup|NULL
     * @since 1.0
     */
    private function _getGroup($groupId) {
        
        foreach ($this->userGroups as $g){
            if($g->getID() == $groupId){
                return $g;
            }
        }
        $g = NULL;
        return $g;
    }
    /**
     * Returns a privilege object given its ID.
     * @param string $id The ID of the privilege.
     * @return Privilege|NULL If a privilege with the given ID was found in 
     * any user group, It will be returned. If not, the function will return 
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
            foreach ($g->privileges() as $p){
                if($p->getID() == $privId){
                    return $p;
                }
            }
        }
        $p = NULL;
        return $p;
    }
    /**
     * Checks if a privilege does exist or not given its ID.
     * @param string $id The ID of the privilege.
     * @return boolean The function will return TRUE if a privilege 
     * with the given ID was found. FALSE if not.
     * @since 1.0
     */
    public static function hasPrivilege($id) {
        return Access::get()->_hasPrivilege($id);
    }
    
    private function _hasPrivilege($privilegId) {
        foreach ($this->userGroups as $g){
            foreach ($g->privileges() as $p){
                if($p->getID() == $privilegId){
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    /**
     * Checks if a users group does exist or not given its ID.
     * @param string $groupId The ID of the group.
     * @return boolean The function will return TRUE if a users group 
     * with the given ID was found. FALSE if not.
     * @since 1.0
     */
    public static function hasGroup($groupId){
        return Access::get()->_hasGroup($groupId);
    }
    /**
     * Returns a UsersGroup object given its ID.
     * @param string $groupId The ID of the users group.
     * @return UsersGroup|NULL If a users group with the given ID was found, 
     * It will be returned. If not, the function will return NULL.
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
        foreach ($this->userGroups as $group){
            if($groupId == $group->getID()){
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Creates new users group using specific ID.
     * @param string $groupId The ID of the group. The ID must not contain 
     * any of the following characters, ';','-' or a space.
     * @since 1.0
     */
    public static function newGroup($groupId) {
        Access::get()->_createGroup($groupId);
    }
    
    private function _createGroup($groupId){
        if($this->_validateId($groupId)){
            foreach ($this->userGroups as $g){
                if($g->getID() == $groupId){
                    return;
                }
            }
            $group = new UsersGroup();
            $group->setID($groupId);
            $this->userGroups[] = $group;
        }
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
     * @since 1.0
     */
    public static function newPrivilege($groupId,$privilegeId){
        Access::get()->_createPrivilege($groupId, $privilegeId);
    }
    /**
     * 
     * @param type $groupId
     * @param type $privilegeId
     * @since 1.0
     */
    private function _createPrivilege($groupId,$privilegeId){
        if($this->_validateId($privilegeId)){
            if($this->_hasGroup($groupId)){
                $g = $this->_getGroup($groupId);
                $p = new Privilege();
                $p->setID($privilegeId);
                if(!$g->hasPrivilege($p)){
                    $this->_getGroup($groupId)->addPrivilage($p);
                    $this->_getGroup('SUPER_ADMIN')->addPrivilage($p);
                }
            }
        }
    }
}
