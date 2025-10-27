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

use WebFiori\Json\Json;
use WebFiori\Json\JsonI;
/**
 * A class that represents a set of privileges.
 *
 * @author Ibrahim
 *
 * @version 1.1.1
 */
class PrivilegesGroup implements JsonI {
    /**
     * An array that contains all child groups of this group.
     *
     * @var array
     *
     * @since 1.1
     */
    private $childGroups;
    /**
     * The unique Identifier of the group.
     *
     * @var string
     *
     * @since 1.0
     */
    private $groupId;
    /**
     * The name of the group.
     *
     * @var string
     *
     * @since 1.0
     */
    private $groupName;
    /**
     * A parent group that this group belongs to.
     *
     * @var PrivilegesGroup
     *
     * @since 1.1
     */
    private $parentGroup;
    /**
     * An array which contains group privileges.
     *
     * @var array
     *
     * @since 1.0
     */
    private $privilegesArr;
    /**
     * Creates new instance of the class.
     *
     * @param string $gId The ID of the group. Default is 'GROUP'.
     *
     * @param string $gName The name of the group. Default is 'G_NAME'.
     *
     * @since 1.0
     */
    public function __construct(string $gId = 'GROUP', string $gName = 'G_NAME') {
        $this->privilegesArr = [];
        $this->childGroups = [];
        $this->groupId = '';
        $this->groupName = '';

        if (!$this->setID($gId)) {
            $this->setID('GROUP');
        }

        if (!$this->setName($gName)) {
            $this->setName('G_NAME');
        }
    }
    /**
     * Returns an array that contains all group privileges.
     *
     * The array does not include the privileges of parent group or child
     * groups.
     *
     * @return array An array that contains an objects of type 'Privilege'.
     *
     * @since 1.0
     */
    public function &privileges() : array {
        return $this->privilegesArr;
    }
    /**
     * Adds new privilege to the array of group privileges.
     *
     * @param Privilege $pr An object of type Privilege. Note that
     * the privilege will be added only if there is no privilege in
     * the group which has the same ID.
     *
     * @return boolean The method will return true if the privilege was
     * added. false otherwise.
     *
     * @since 1.0
     */
    public function addPrivilege(Privilege $pr) : bool {
        foreach ($this->privilegesArr as $prev) {
            if ($prev->getID() == $pr->getID()) {
                return false;
            }
        }
        $this->privilegesArr[] = $pr;

        return true;
    }
    /**
     * Returns an array that contains all child groups of the group.
     *
     * @return array An array that contains an objects of type 'PrivilegesGroup'.
     *
     * @since 1.1
     */
    public function childGroups() : array {
        return $this->childGroups;
    }
    /**
     * Returns the ID of the group.
     *
     * @return string The ID of the group. Default value is 'GROUP'.
     *
     * @since 1.0
     */
    public function getID() : string {
        return $this->groupId;
    }
    /**
     * Returns the name of the group.
     *
     * The name can be used to give a meaningful description of the group
     * (like a label).
     *
     * @return string The name of the group. Default value is 'G_NAME'.
     *
     * @since 1.0
     */
    public function getName() : string {
        return $this->groupName;
    }

    /**
     * Returns an object of type 'PrivilegesGroup' that represents the parent
     * group of 'this' group.
     *
     * @return PrivilegesGroup|null If the parent group is set, the method will
     * return it. If it is not set, the method will return null.
     *
     * @since 1.1
     */
    public function getParentGroup() {
        return $this->parentGroup;
    }

    /**
     * Checks if the group has the given privilege or not.
     *
     * This method will only check the given group (does not include parent).
     *
     * @param Privilege $p An object of type 'Privilege'.
     *
     * @param boolean $checkChildGroups If this parameter is set to true, the
     * search for the privilege will include child groups. By default, it will
     * be set to true.
     *
     * @return boolean The method will return true if the group has the given
     * privilege. false if not.
     *
     * @since 1.0
     */
    public function hasPrivilege(Privilege $p, bool $checkChildGroups = true) : bool {
        $hasPr = false;

        foreach ($this->privileges() as $privilege) {
            if ($p->getID() == $privilege->getID()) {
                $hasPr = true;
            }
        }

        if (!$hasPr && $checkChildGroups === true) {
            foreach ($this->childGroups() as $g) {
                $hasPr = $this->hasPrivilegeHelper($g, $p);

                if ($hasPr) {
                    break;
                }
            }
        }

        return $hasPr;
    }
    /**
     * Checks if provided group ID is valid or not.
     *
     * @param string $id The ID of the privilege or the group.
     *
     * @return bool If valid, true is returned. False otherwise.
     */
    public static function isValidID(string $id) : bool {
        $xid = trim($id);
        $len = strlen($xid);

        for ($x = 0 ; $x < $len ; $x++) {
            $ch = $xid[$x];

            if (!($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9'))) {
                return false;
            }
        }

        return true;
    }
    /**
     * Sets the ID of the group.
     *
     * The ID of the group can only consist of the following characters: [A-Z],
     * [a-z], [0-9] and underscore. In addition, it must not be the same as the
     * ID of the parent groups or child groups.
     *
     * @param string $id The ID of the group.
     *
     * @return boolean If the ID of the group is updated, the method will return
     * true. If not updated, it will return false.
     *
     * @since 1.0
     */
    public function setID(string $id) : bool {
        if (!self::isValidID($id)) {
            return false;
        }
        $taken = true;
        $parentG = $this->getParentGroup();

        if ($parentG === null) {
            $taken = $this->checkID($id, $this);

            if ($taken === true) {
                return false;
            }
        }

        $testInst = $parentG;

        while ($parentG !== null) {
            $parentG = $parentG->getParentGroup();

            if ($parentG !== null) {
                $testInst = $parentG;
            }
        }

        if ($testInst !== null) {
            $taken = $this->checkID($id, $testInst);
        }


        if ($taken === true) {
            return false;
        }
        $this->groupId = trim($id);

        return true;
    }
    /**
     * Sets the name of the group.
     *
     * The name is used just to give a meaning to the group.
     *
     * @param string $name The name of the group. It must be non-empty string
     * in order to update.
     *
     * @return boolean If group name is updated, the method will return true.
     * If not updated, the method will return false.
     *
     * @since 1.0
     */
    public function setName(string $name) : bool {
        $trimmed = trim($name);

        if (strlen($trimmed) > 0) {
            $this->groupName = $trimmed;

            return true;
        }

        return false;
    }
    /**
     * Sets or unset parent privileges group.
     *
     * @param PrivilegesGroup|null $group If the given parameter is an object of
     * type 'PrivilegesGroup', the parent group will be set if it has different
     * ID other than 'this' group. If null reference is passed, the parent group will be
     * unset. Default value is null.
     *
     * @return boolean If the class attribute value was updated, the method will
     * return true. Other than that, the method will return false.
     *
     * @since 1.1
     */
    public function setParentGroup(?PrivilegesGroup $group = null) : bool {
        if ($group !== null) {
            if ($group !== $this && $group->getID() != $this->getID()) {
                $this->parentGroup = $group;
                $this->parentGroup->childGroups[] = $this;

                return true;
            }
        } else if ($this->parentGroup !== null) {
            $this->parentGroup->removeChildGroupHelper($this->getID());
            $this->parentGroup = null;

            return true;
        }

        return false;
    }
    /**
     * Returns an object of type Json that contains group info as JSON string.
     *
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
     *
     * @return Json
     */
    public function toJSON() : Json {
        $j = new Json();
        $parentId = $this->getParentGroup() !== null ? $this->getParentGroup()->getID() : null;
        $j->add('group-id', $this->getID());
        $j->add('parent-group-id',$parentId);
        $j->add('name', $this->getName());
        $j->add('privileges', $this->privileges());
        $j->add('child-groups', $this->childGroups());

        return $j;
    }

    /**
     *
     * @param string $id
     * @param PrivilegesGroup $group
     * @return bool
     */
    private function checkID(string $id, PrivilegesGroup $group): bool {
        if ($group->getID() == $id) {
            return true;
        }
        $bool = false;

        foreach ($group->childGroups() as $g) {
            $bool = $bool || $this->checkID($id, $g);
        }

        return $bool;
    }
    /**
     * Checks if a group has specific privilege or not.
     *
     * @param PrivilegesGroup $group The group that will be checked
     *
     * @param Privilege $p The privilege that will be checked.
     */
    private function hasPrivilegeHelper(PrivilegesGroup $group, Privilege $p) {
        $hasPr = false;

        foreach ($group->privileges() as $privilege) {
            if ($p->getID() == $privilege->getID()) {
                $hasPr = true;
                break;
            }
        }

        if (!$hasPr) {
            foreach ($group->childGroups() as $g) {
                $hasPr = $this->hasPrivilegeHelper($g, $p);

                if ($hasPr === true) {
                    break;
                }
            }
        }

        return $hasPr;
    }

    /**
     * Private helper that removes a child group by ID from the current group's children array.
     * 
     * @param string $gId The ID of the child group to remove.
     */
    private function removeChildGroupHelper($gId) {
        for ($x = 0 ; $x < count($this->childGroups()) ; $x++) {
            $xG = $this->childGroups[$x];

            if ($xG->getID() == $gId) {
                unset($this->childGroups[$x]);

                return;
            }
        }
    }
}
