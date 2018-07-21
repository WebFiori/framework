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

/**
 * Description of Access
 *
 * @author Ibrahim
 */
class Access {
    private static $access;
    private $userGroups;
    public function __construct() {
        $this->userGroups = array();
        $superAdminG = new UsersGroup();
        $superAdminG->setID('SU_GROUP');
        $this->userGroups[] = $superAdminG;
    }
    /**
     * 
     * @return Access
     */
    public static function get(){
        if(self::$access != NULL){
            return self::$access;
        }
        self::$access = new Access();
        return self::$access;
    }
    
    public static function privileges($groupId=null) {
        return Access::get()->_privileges($groupId);
    }
    /**
     * 
     * @param type $str
     * @param User $user
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
     * 
     * @param User $user
     * @return string
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


    public function addGroup($groupName) {
        foreach ($this->userGroups as $group){
            if($groupName == $group->getName()){
                return;
            }
            $g = new UsersGroup();
            $g->setName($groupName);
            $this->userGroups[] = $g;
        }
    }
    public static function groups(){
        return Access::get()->_groups();
    }
    private function _groups(){
        return $this->userGroups;
    }
    private function _getGroup($groupId) {
        foreach ($this->userGroups as $g){
            if($g->getID() == $groupId){
                return $g;
            }
        }
        return NULL;
    }
    
    public static function getPrivilege($id){
        return Access::get()->_getPrivilege($id);
    }

    private function _getPrivilege($privId) {
        foreach ($this->userGroups as $g){
            foreach ($g->privileges() as $p){
                if($p->getID() == $privId){
                    return $p;
                }
            }
        }
        return NULL;
    }
    
    public static function hasPrivilege($id) {
        return Access::get()->_hasPrivilege($id);
    }
    
    public function _hasPrivilege($privilegId) {
        foreach ($this->userGroups as $g){
            foreach ($g->privileges() as $p){
                if($p->getID() == $privilegId){
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    
    public static function hasGroup($groupId){
        return Access::get()->_hasGroup($groupId);
    }
    
    public static function getGroup($groupId){
        return Access::get()->_getGroup($groupId);
    }
    
    private function _hasGroup($groupId){
        foreach ($this->userGroups as $group){
            if($groupId == $group->getID()){
                return TRUE;
            }
        }
        return FALSE;
    }
    
    public static function newGroup($groupId) {
        Access::get()->_createGroup($groupId);
    }
    
    private function _createGroup($groupId){
        foreach ($this->userGroups as $g){
            if($g->getID() == $groupId){
                return;
            }
        }
        $group = new UsersGroup();
        $group->setID($groupId);
        $this->userGroups[] = $group;
    }
    
    public static function newPrivilege($groupId,$privilegeId){
        Access::get()->_createPrivilege($groupId, $privilegeId);
    }

        private function _createPrivilege($groupId,$privilegeId){
        if($this->_hasGroup($groupId)){
            if(!$this->_hasPrivilege($privilegeId)){
                $p = new Privilege();
                $p->setID($privilegeId);
                $this->_getGroup($groupId)->addPrivilage($p);
            }
        }
    }
}
