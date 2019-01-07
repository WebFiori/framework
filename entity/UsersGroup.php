<?php
namespace webfiori\entity;
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
use jsonx\JsonI;
/**
 * A class that represents a set of privileges
 *
 * @author Ibrahim
 * @version 1.0
 */
class UsersGroup implements JsonI{
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
        $this->setID($gId);
        $this->setName($gName);
    }
    /**
     * Sets the name of the group.
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
     * @param string $id The ID of the group.
     * @since 1.0
     */
    public function setID($id) {
        if(strlen($id) != 0){
            $this->groupId = $id;
        }
    }
    /**
     * Checks if the group has the given privilege or not.
     * @param Privilege $p An object of type 'Privilige'.
     * @return boolean The method will return TRUE if the group has the given 
     * privilege. FALSE if not.
     * @since 1.0
     */
    public function hasPrivilege($p) {
        if($p instanceof Privilege){
            foreach ($this->privileges() as $privilege){
                if($p->getID() == $privilege->getID()){
                    return TRUE;
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
    public function addPrivilage($pr) {
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

    public function toJSON() {
        $j = new JsonX();
        $j->add('group-id', $this->getID());
        $j->add('name', $this->getName());
        $j->add('privileges', $this->privileges());
        return $j;
    }

}
