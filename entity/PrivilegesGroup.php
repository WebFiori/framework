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
use jsonx\JsonI;
/**
 * A class that represents a set of privileges.
 *
 * @author Ibrahim
 * @version 1.1
 */
class PrivilegesGroup implements JsonI{
    /**
     * A parent group that this group belongs to.
     * @var PrivilegesGroup 
     * @since 1.1
     */
    private $parentGroup;
    /**
     * An array that contains all child groups of this group.
     * @var array 
     * @since 1.1
     */
    private $childGroups;
    /**
     * The name of the group.
     * @var string
     * @since 1.0 
     */
    private $groupName;
    /**
     * The unique Identifier of the group.
     * @var string 
     * @since 1.0
     */
    private $groupId;
    /**
     * An array which contains group privileges.
     * @var array
     * @since 1.0 
     */
    private $privilegesArr;
    /**
     * Creates new instance of the class.
     * @param string $gId The ID of the group. Default is 'GROUP'.
     * @param string $gName The name of the group. Default is 'G_NAME'.
     * @since 1.0
     */
    public function __construct($gId='GROUP',$gName='G_NAME') {
        $this->privilegesArr = array();
        $this->childGroups = array();
        $this->setID($gId);
        $this->setName($gName);
    }
    /**
     * Sets or unset parent privileges group.
     * @param PrivilegesGroup|NULL $group If the given parameter is an object of 
     * type 'PrivilegesGroup', the parent group will be set if it has different 
     * ID other than 'this' group. If NULL is passed, the parent group will be 
     * unset.
     * @return boolean If the class attribute value was updated, the method will 
     * return TRUE. Other than that, the method will return FALSE.
     * @since 1.1
     */
    public function setParentGroup(&$group=null) {
        if($group instanceof PrivilegesGroup){
            if($group !== $this && $group->getID() != $this->getID()){
                $this->parentGroup = $group;
                $this->parentGroup->childGroups[] = &$this;
                return TRUE;
            }
        }
        else if($group === NULL){
            if($this->parentGroup !== NULL){
                $this->parentGroup->_removeChildGroup($this->getID());
            }
            $this->parentGroup = NULL;
            return TRUE;
        }
        return FALSE;
    }
    
    private function _removeChildGroup($gId){
        for($x = 0 ; $x < count($this->childGroups()) ; $x++){
            $xG = $this->childGroups[$x];
            if($xG->getID() == $gId){
                unset($this->childGroups[$x]);
                return;
            }
        }
    }

    /**
     * Returns an object of type 'PrivilegesGroup' that represents the parent 
     * group of 'this' group.
     * @return PrivilegesGroup|NULL If the parent group is set, the method will 
     * return it. If it is not set, the method will return NULL.
     * @since 1.1
     */
    public function &getParentGroup() {
        return $this->parentGroup;
    }
    /**
     * Returns an array that contains all child groups of the group.
     * @return array An array that contains an objects of type 'PrivilegesGroup'.
     * @since 1.1
     */
    public function childGroups() {
        return $this->childGroups;
    }
    /**
     * Sets the name of the group.
     * The name is used just to give a meaning to the group.
     * @param string $name The name of the group. It must be non-empty string 
     * in order to update.
     * @since 1.0
     */
    public function setName($name) {
        if(strlen($name) > 0){
            $this->groupName = $name;
        }
    }
    /**
     * Returns the ID of the group.
     * @return string The ID of the group.
     * @since 1.0
     */
    public function getID() {
        return $this->groupId;
    }
    /**
     * Sets the ID of the group.
     * The ID of the group can only consist of the following characters: [A-Z], 
     * [a-z], [0-9] and underscore. In addition, it must not be the same as the 
     * ID of any of the parent groups or child groups.
     * @param string $id The ID of the group.
     * @return boolean If the ID of the group is updated, the method will return 
     * TRUE. If not updated, it will return FALSE.
     * @since 1.0
     */
    public function setID($id) {
        $xid = ''.$id;
        $len = strlen($xid);
        $parentG = $this->getParentGroup();
        if($parentG !== NULL){
            while ($parentG !== NULL){
                if($id == $parentG->getID()){
                    return FALSE;
                }
                $parentG = $parentG->getParentGroup();
            }
        }
        foreach ($this->childGroups() as $g){
            if($g->getID() == $id){
                return FALSE;
            }
        }
        for ($x = 0 ; $x < $len ; $x++){
            $ch = $xid[$x];
            if($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9')){

            }
            else{
                return FALSE;
            }
        }
        $this->groupId = $id;
        return TRUE;
    }
    /**
     * Checks if the group has the given privilege or not.
     * This method will only check the given group (does not include parent). 
     * @param Privilege $p An object of type 'Privilige'.
     * @param boolean $checkChildGroups If this parameter is set to TRUE, the 
     * search for the privilege will include child groups. By default, it will 
     * be set to TRUE.
     * @return boolean The method will return TRUE if the group has the given 
     * privilege. FALSE if not.
     * @since 1.0
     */
    public function hasPrivilege($p,$checkChildGroups=true) {
        if($p instanceof Privilege){
            foreach ($this->privileges() as $privilege){
                if($p->getID() == $privilege->getID()){
                    return TRUE;
                }
            }
            if($checkChildGroups === TRUE){
                foreach ($this->childGroups() as $g){
                    foreach ($g->privileges() as $privilege){
                        if($p->getID() == $privilege->getID()){
                            return TRUE;
                        }
                    }
                }
            }
        }
        return FALSE;
    }
    /**
     * Returns the name of the group.
     * @return string The name of the group.
     * @since 1.0
     */
    public function getName(){
        return $this->groupName;
    }
    /**
     * Returns an array that contains all group privileges.
     * @return array An array that contains an objects of type 'Privilege'.
     * @since 1.0
     */
    public function &privileges() {
        return $this->privilegesArr;
    }
    /**
     * Adds new privilege to the array of group privileges.
     * @param Privilege $pr An object of type Privilege. Note that 
     * the privilege will be added only if there us no privilege in 
     * the group which has the same ID.
     * @return boolean The method will return TRUE if the privilege was 
     * added.
     * @since 1.0
     */
    public function addPrivilage(&$pr) {
        if($pr instanceof Privilege){
            foreach ($this->privilegesArr as $prev){
                if($prev->getID() == $pr->getID()){
                    return FALSE;
                }
            }
            $this->privilegesArr[] = $pr;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns an object of type JsonX that contains group info as JSON string.
     * The generated JSON string will have the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"group-id":"",<br/>
     * &nbsp;&nbsp;"parent-group-id":"",<br/>
     * &nbsp;&nbsp;"name":"",<br/>
     * &nbsp;&nbsp;"privileges":[]<br/>
     * &nbsp;&nbsp;"child-groups":[]<br/>
     * }
     * </p> 
     * See the class "Privilege" for more information on the JSON string that 
     * will be generated by each privilege in the privileges array.
     * @return JsonX
     */
    public function toJSON() {
        $j = new JsonX();
        $parentId = $this->getParentGroup() !== NULL ? $this->getParentGroup()->getID() : NULL;
        $j->add('group-id', $this->getID());
        $j->add('parent-group-id',$parentId);
        $j->add('name', $this->getName());
        $j->add('privileges', $this->privileges());
        $j->add('child-groups', $this->childGroups());
        return $j;
    }

}
