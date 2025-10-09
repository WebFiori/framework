<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework;

/**
 * A class to manage user groups and privileges.
 *
 * @author Ibrahim
 * @version 1.0.4
 */
class Access {
    /**
     * An instance of the class.
     *
     * @var Access
     *
     * @since 1.0
     */
    private static $access;
    /**
     * An array which contains an objects of type UsersGroup.
     *
     * @var PrivilegesGroup
     *
     * @since 1.0
     */
    private $userGroups;
    /**
     * Creates new instance.
     *
     * @since 1.0
     */
    private function __construct() {
        $this->userGroups = [];
    }
    /**
     * Returns an array that represents all privileges groups and privileges.
     *
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
     *
     * @return array An array that contains all privileges and groups info.
     */
    public static function asArray(): array {
        $retVal = [];

        foreach (self::get()->userGroups as $group) {
            $retVal[] = self::get()->asArrayHelper($group);
        }

        return $retVal;
    }
    /**
     * Removes all created user groups and privileges.
     *
     * @since 1.0.4
     */
    public static function clear() {
        self::get()->userGroups = [];
    }
    /**
     * Creates a string of permissions given a user.
     *
     * This method can be handy in case the developer would like to store
     * user privileges in a database. The method might return a string which
     * might looks like the following string:
     * <p>'PRIVILEGE_1-1;PRIVILEGE_2-1;G-A_GROUP'</p>
     * where 'PRIVILEGE_1' and 'PRIVILEGE_2' are IDs of privileges and
     * 'A_GROUP' is the ID of a group that the user has all its privileges. The number
     * that comes after the dash is the status of the privilege. Each privilege
     * or a group will be separated from the other by a semicolon.
     * Also, the group will have the letter 'G' at the start. Note that if the group
     * has subgroups, this means the user will have the privileges of the sub-groups.
     *
     * @param User $user The user which the permissions string will be created from.
     *
     * @return string A string of user privileges and the groups that he belongs to
     * (if any).
     *
     * @since 1.0
     */
    public static function createPermissionsStr(User $user): string {
        return Access::get()->createPermissionsStrHelper2($user);
    }
    /**
     * Returns an object of type UsersGroup given its ID.
     * This method can be used to check if a group exist or not. If
     * the method has returned null, this means the group does not exist.
     *
     * @param string $groupId The ID of the group.
     *
     * @return PrivilegesGroup|null If a users group with the given ID was found,
     * It will be returned. If not, the method will return null.
     *
     * @since 1.0
     */
    public static function getGroup(string $groupId) {
        return Access::get()->getGroupHelper($groupId);
    }

    /**
     * Returns a privilege object given privilege ID.
     *
     * This method will search all created groups for a privilege which has the
     * given ID. If not found, the method will return null. This method also
     * can be used to check if a privilege exist or not. If the method
     * has returned null, this means the privilege does not exist.
     *
     * @param string $id The ID of the privilege.
     *
     * @return Privilege|null If a privilege with the given ID was found in
     * any user group, It will be returned. If not, the method will return
     * null.
     *
     * @since 1.0
     */
    public static function getPrivilege(string $id) {
        return Access::get()->getPrivilegeHelper0($id);
    }
    /**
     * Returns an array which contains all top-level user groups.
     *
     * The array will be empty if no user groups has been created.
     *
     * @return array An array that contains an objects of type UsersGroup.
     *
     * @since 1.0
     */
    public static function groups() {
        return Access::get()->userGroups;
    }
    /**
     * Checks if a users group does exist or not given its ID.
     *
     * @param string $groupId The ID of the group.
     *
     * @return bool The method will return true if a users group
     * with the given ID was found. false if not.
     *
     * @since 1.0
     */
    public static function hasGroup(string $groupId): bool {
        return self::getGroup($groupId) !== null;
    }
    /**
     * Checks if a privilege does exist or not given its ID.
     *
     * The method will search all created groups for a privilege with the
     * given ID.
     *
     * @param string $id The ID of the privilege.
     *
     * @param string $groupId If it is provided, the search for the privilege
     * will be limited to the group which has the given ID.
     *
     *
     * @return bool The method will return true if a privilege
     * with the given ID was found. false if not.
     *
     * @since 1.0
     */
    public static function hasPrivilege(string $id, ?string $groupId = null): bool {
        return Access::get()->hasPrivilegeHelper($id,$groupId);
    }
    /**
     * Creates new users group using specific ID.
     *
     * The group is the base for user privileges. After creating it, the developer
     * can add a set of privileges to the group. Note that the group will not be created
     * if the name of the group contains invalid characters, or it is already
     * created. In addition, If a parent group has the given new group name,
     * it will not be created.
     *
     * @param string $groupId The ID of the group. The ID must not contain
     * any of the following characters: ';','-',',' or a space. If the name contains
     * any of the given characters, the group will not be created.
     *
     * @return bool If the group is created, the method will return true.
     * If not, the method will return false.
     *
     * @since 1.0
     */
    public static function newGroup(string $groupId, ?string $parentGroupId = null): bool {
        return Access::get()->createGroupHelper($groupId,$parentGroupId);
    }
    /**
     * Creates new privilege in a specific group given its ID.
     *
     * The method will add the privilege only if it does not exist in any of
     * the created groups.
     *
     * @param string $groupId The ID of the group that the privilege will be
     * added to. It must be a group in the groups array of the access class.
     *
     * @param string $privilegeId The ID of the privilege. The ID must not contain
     * any of the following characters, ';','-',',' or a space.
     *
     * @return bool If the privilege was created, the method will return
     * true. Other than that, the method will return false.
     *
     * @since 1.0
     */
    public static function newPrivilege(string $groupId, string $privilegeId): bool {
        return Access::get()->createPrivilegeHelper($groupId, $privilegeId);
    }
    /**
     * Creates multiple privileges in a group given its ID.
     *
     * This method can be used as a shorthand to create multiple privileges in
     * a group instead of calling Access::newPrivilege() multiple times.
     *
     * @param string $groupId The ID of the group. The group must be created
     * before starting to create privileges in it.
     *
     * @param array $prNamesArr An associative array that contains the names of privileges.
     *
     * @return array The method will return an associative array.
     * The indices will be the IDs of the privileges and the values will be
     * booleans. Each boolean corresponds to the status of each privilege in the array of
     * privileges. If the privilege is added, the value will be true. If not,
     * it will be false.
     *
     * @since 1.0.1
     */
    public static function newPrivileges(string $groupId, array $prNamesArr): array {
        $retVal = [];
        $count = count($prNamesArr);

        for ($x = 0 ; $x < $count ; $x++) {
            $retVal[$prNamesArr[$x]] = self::newPrivilege($groupId, $prNamesArr[$x]);
        }

        return $retVal;
    }
    /**
     * Returns an array which contains all privileges
     * in a specific group.
     *
     * @param string|null $groupId The ID of the group which its
     * privileges will be returned. If null is given, all privileges will be
     * returned. Default is null.
     *
     * @return array An array which contains an objects of type Privilege. If
     * the given group ID does not exist, the returned array will be empty.
     *
     * @since 1.0
     */
    public static function privileges(?string $groupId = null): array {
        return Access::get()->getPrivilegesHelper($groupId);
    }
    /**
     * Adds privileges to a user given privileges string.
     *
     * @param string $str A string of privileges. The format of the string must
     * follow the following format: 'PRIVILEGE_1-0;PRIVILEGE_2-1;G-A_GROUP' where
     * 'PRIVILEGE_1' and 'PRIVILEGE_2' are IDs of privileges and 'A_GROUP'
     * is the ID of a group that the user belongs to. The number
     * that comes after the dash is the status of the privilege. If 0, then the
     * user will not have the given privilege. If 1, the user will have the
     * privilege. In the given example, The user will have only 'PRIVILEGE_2'. and
     * he will belong to the group that has the ID 'A_GROUP'. Each
     * privilege or a group must be separated from the other by a semicolon. Also,
     * the group must have the letter 'G' at the start. Note that in the given
     * example, if 'PRIVILEGE_1' is in 'A_GROUP', he will not have it even if it is
     * in group permissions.
     *
     * @param User $user The user which the permissions will be added to
     *
     * @since 1.0
     */
    public static function resolvePrivileges(string $str, User $user) {
        if (strlen($str) > 0) {
            $prInfo = self::getPrivilegesInfoHelper($str);

            foreach ($prInfo['groups-belongs-to'] as $groupId) {
                $user->addToGroup($groupId);
            }

            foreach ($prInfo['privileges-to-have'] as $privilegeId) {
                $user->addPrivilege($privilegeId);
            }

            foreach ($prInfo['privileges-to-not-have'] as $privilegeId) {
                $user->removePrivilege($privilegeId);
            }
        }
    }

    /**
     *
     * @param PrivilegesGroup $group
     * @return array
     */
    private function asArrayHelper(PrivilegesGroup $group): array {
        $retVal = [];

        $retVal['group-id'] = $group->getID();
        $retVal['given-title'] = $group->getName();
        $retVal['child-groups'] = [];
        $retVal['privileges'] = [];

        foreach ($group->childGroups() as $groupX) {
            $retVal['child-groups'][] = $this->asArrayHelper($groupX);
        }

        foreach ($group->privileges() as $pr) {
            $retVal['privileges'][] = [
                'privilege-id' => $pr->getID(),
                'given-title' => $pr->getName()
            ];
        }

        return $retVal;
    }
    /**
     * Checks if privilege or group ID is equal to another group.
     * @param string $id
     * @param PrivilegesGroup $group
     * @return bool If a group was found which have the given
     * ID, the method will return true.
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

    private function createGroupHelper($groupId, ?string $parentGroupID = null): bool {
        $trimmedId = trim($groupId);

        if ($this->validateId($trimmedId)) {
            foreach ($this->userGroups as $g) {
                if ($g->getID() == $trimmedId) {
                    return false;
                }
            }
            $group = new PrivilegesGroup();
            $group->setID($trimmedId);

            if ($parentGroupID === null) {
                $this->userGroups[] = $group;

                return true;
            }
            $parentG = $this->getGroup($parentGroupID);

            if ($parentG instanceof PrivilegesGroup) {
                $group->setParentGroup($parentG);

                return true;
            }
        }

        return false;
    }
    private function createPermissionsHelper2($userPrivileges, $groupsBelongsTo): string {
        $str = '';

        if (count($groupsBelongsTo) == 0) {
            foreach ($userPrivileges as $privilege) {
                $str .= $privilege->getID().'-1;';
            }

            return $str;
        }

        foreach ($userPrivileges as $privilege) {
            $privilegeHasGroup = false;

            foreach ($groupsBelongsTo as $group) {
                if ($group->hasPrivilege($privilege)) {
                    $privilegeHasGroup = true;
                    break;
                }
            }

            if (!$privilegeHasGroup) {
                $str .= $privilege->getID().'-1;';
            }
        }

        return $str;
    }

    /**
     * @param User $user
     * @param PrivilegesGroup $group
     * @param array $arr
     * @param string $str
     */
    private function createPermissionsStrHelper(User $user, PrivilegesGroup $group, array &$arr, string &$str) {
        if (!$user->inGroup($group->getID())) {
            foreach ($group->childGroups() as $groupX) {
                if (!$user->inGroup($groupX->getID())) {
                    $this->createPermissionsStrHelper($user, $groupX, $arr, $str);
                    continue;
                }
                $arr[] = $groupX;
                $str .= 'G-'.$groupX->getID().';';
            }

            return;
        }
        $arr[] = $group;
        $str .= 'G-'.$group->getID().';';
    }

    /**
     *
     * @param User $user
     * @return string
     */
    private function createPermissionsStrHelper2(User $user): string {
        $str = '';
        $groupsBelongsTo = [];

        foreach ($this->userGroups as $group) {
            $this->createPermissionsStrHelper($user, $group, $groupsBelongsTo, $str);
        }
        $str .= $this->createPermissionsHelper2($user->privileges(), $groupsBelongsTo);

        return trim($str,';');
    }
    /**
     *
     * @param string $groupId
     * @param string $privilegeId
     * @return bool Description
     * @since 1.0
     */
    private function createPrivilegeHelper(string $groupId, string $privilegeId): bool {
        if ($this->validateId($privilegeId)) {
            $pr = self::getPrivilege($privilegeId);

            if ($pr === null) {
                $g = $this->getGroupHelper($groupId);

                if (($g instanceof PrivilegesGroup) && $groupId == $g->getID()) {
                    foreach (Access::groups() as $xG) {
                        if ($this->checkID($privilegeId, $xG)) {
                            return false;
                        }
                    }
                    $p = new Privilege();
                    $p->setID($privilegeId);

                    if (!$g->hasPrivilege($p)) {
                        $g->addPrivilege($p);

                        return true;
                    }
                }
            }
        }

        return false;
    }
    /**
     * Returns a single instance of the class.
     * @return Access
     * @since 1.0
     */
    private static function get(): Access {
        if (self::$access !== null) {
            return self::$access;
        }
        self::$access = new Access();

        return self::$access;
    }
    /**
     *
     * @param string $groupId
     * @return PrivilegesGroup|null
     * @since 1.0
     */
    private function getGroupHelper(string $groupId) {
        $trimmedId = trim($groupId);

        foreach ($this->userGroups as $g) {
            if ($g->getID() != $trimmedId) {
                $g = $this->getGroupHelper1($g, $trimmedId);

                if ($g instanceof PrivilegesGroup) {
                    return $g;
                }
                continue;
            }

            return $g;
        }

        return null;
    }

    /**
     *
     * @param PrivilegesGroup $group
     * @param $groupId
     * @return PrivilegesGroup|null
     */
    private function getGroupHelper1(PrivilegesGroup $group, $groupId) {
        if ($groupId == $group->getID()) {
            return $group;
        }

        foreach ($group->childGroups() as $groupX) {
            $g = $this->getGroupHelper1($groupX, $groupId);

            if ($g instanceof PrivilegesGroup) {
                return $g;
            }
        }

        return null;
    }
    /**
     *
     * @param string $privilegeId
     * @return Privilege|null
     */
    private function getPrivilegeHelper0(string $privilegeId) {
        foreach ($this->userGroups as $g) {
            $p = $this->getPrivilegeHelper1($privilegeId, $g);

            if ($p !== null) {
                return $p;
            }
        }

        return null;
    }
    /**
     *
     * @param string $privilegeId
     * @param PrivilegesGroup $group
     * @return Privilege|null
     */
    private function getPrivilegeHelper1(string $privilegeId, PrivilegesGroup $group) {
        foreach ($group->privileges() as $p) {
            if ($p->getID() == $privilegeId) {
                return $p;
            }
        }

        foreach ($group->childGroups() as $g) {
            $p = $this->getPrivilegeHelper1($privilegeId, $g);

            if ($p !== null) {
                return $p;
            }
        }

        return null;
    }

    private function getPrivilegesHelper(?string $groupId = null): array {
        $prArr = [];

        foreach ($this->userGroups as $group) {
            $this->getPrivilegesHelper1($group, $prArr, $groupId);
        }

        return $prArr;
    }

    /**
     *
     * @param PrivilegesGroup $group
     * @param array $array
     * @param string|null $groupId
     */
    private function getPrivilegesHelper1(PrivilegesGroup $group, array &$array, ?string $groupId = null) {
        if ($groupId === null) {
            foreach ($group->privileges() as $pr) {
                $array[] = $pr;
            }

            foreach ($group->childGroups() as $g) {
                $this->getPrivilegesHelper1($g, $array,$groupId);
            }

            return;
        } else if ($group->getID() == $groupId) {
            foreach ($group->privileges() as $pr) {
                $array[] = $pr;
            }

            return;
        }

        foreach ($group->childGroups() as $g) {
            $this->getPrivilegesHelper1($g, $array,$groupId);
        }
    }
    private static function getPrivilegesInfoHelper(string $privilegesStr): array {
        $privilegesToHave = [];
        $privilegesToNotHave = [];
        $groupsBelongsTo = [];
        $privilegesSplit = explode(';', $privilegesStr);

        foreach ($privilegesSplit as $privilegeStr) {
            $prSplit = explode('-', $privilegeStr);

            if (count($prSplit) == 2) {
                if ($prSplit[0] != 'G') {
                    $privilegeId = $prSplit[0];

                    if ($prSplit[1] != '1') {
                        $privilegesToNotHave[] = $privilegeId;
                    }
                    //It means the user has the privilege.
                    $privilegesToHave[] = $privilegeId;
                    continue;
                }
                $groupsBelongsTo[] = $prSplit[1];
            }
        }
        $retVal = [];
        $retVal['privileges-to-have'] = $privilegesToHave;
        $retVal['privileges-to-not-have'] = $privilegesToNotHave;
        $retVal['groups-belongs-to'] = $groupsBelongsTo;

        return $retVal;
    }
    private function groupHasPrivilegeHelper($prId, $group): bool {
        $retVal = false;

        foreach ($group->privileges() as $p) {
            if ($p->getID() == $prId) {
                $retVal = true;
                break;
            }
        }

        return $retVal;
    }

    private function hasPrivilegeHelper($privilegeId, $groupId) {
        $retVal = false;

        foreach ($this->userGroups as $g) {
            $retVal = $this->hasPrivilegeHelper1($privilegeId, $g, $groupId);

            if ($retVal === true) {
                break;
            }
        }

        return $retVal;
    }

    /**
     *
     * @param string $prId
     * @param string $groupId
     * @param PrivilegesGroup $group
     * @return bool
     */
    private function hasPrivilegeHelper1(string $prId, PrivilegesGroup $group, ?string $groupId = null) : bool {
        if ($groupId === null || $group->getID() != $groupId) {
            if ($groupId !== null) {
                return $this->isChildGroupHasPrivilege($prId, $groupId, $group);
            }

            return $this->groupHasPrivilegeHelper($prId, $group)
                    || $this->isChildGroupHasPrivilege($prId, $groupId, $group);
        }

        return $this->groupHasPrivilegeHelper($prId, $group);
    }
    private function isChildGroupHasPrivilege($prId, $groupId, $group): bool {
        $retVal = false;

        foreach ($group->childGroups() as $g) {
            $b = $this->hasPrivilegeHelper1($prId, $g, $groupId);

            if ($b === true) {
                $retVal = true;
                break;
            }
        }

        return $retVal;
    }

    private function validateId($id): bool {
        $len = strlen($id);

        if ($len > 0) {
            $valid = true;

            for ($x = 0 ; $x < $len ; $x++) {
                $valid = $valid && $id[$x] != ';' && $id[$x] != ' ' && $id[$x] != '-' && $id[$x] != ',';
            }

            return $valid;
        }

        return false;
    }
}
