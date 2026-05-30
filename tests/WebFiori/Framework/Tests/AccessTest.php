<?php
namespace WebFiori\Framework\Test;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Access;
use WebFiori\Framework\AccessManager;
use WebFiori\Framework\Permission;
use WebFiori\Framework\Role;
use WebFiori\Framework\Storage\InMemoryAccessStorage;
use WebFiori\Framework\Storage\FileAccessStorage;
use WebFiori\Framework\Storage\DatabaseAccessStorage;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Database;
use WebFiori\Framework\User;
/**
 * A test class for testing the class 'WebFiori\Framework\Access'.
 *
 * @author Ibrahim
 */
class AccessTest extends TestCase {
    public function setUp(): void {
        Access::clear();
    }
    /**
     * @test
     */
    public function test00() {
        $this->assertEquals(0, count(Access::groups()));
        $this->assertEquals(0, count(Access::privileges()));
    }
    /**
     * @test
     */
    public function test01() {
        $this->assertFalse(Access::newGroup('ADMINS Group'));
        $this->assertFalse(Access::newGroup(''));
        $this->assertFalse(Access::newGroup('  '));
    }
    /**
     * @test
     */
    public function test02() {
        $this->assertTrue(Access::newGroup('ADMINS_Group'));
        $this->assertFalse(Access::newGroup('ADMINS_Group'));
    }
    /**
     * @test
     */
    public function test03() {
        $this->assertFalse(Access::newGroup('ADMINS_Group;'));
        $this->assertFalse(Access::newGroup('ADMINS_Group,'));
        $this->assertFalse(Access::newGroup('ADMINS-Group,'));
    }
    /**
     * @test
     */
    public function test04() {
        $this->assertTrue(Access::newGroup('ADMINS_Group'));
        $this->assertTrue(Access::newGroup('USERS_Group'));
        $this->assertEquals(2, count(Access::groups()));
        $this->assertEquals(0, count(Access::privileges()));
    }
    /**
     * @test
     */
    public function test05() {
        $this->assertTrue(Access::newGroup('ADMINS_Group'));
        $this->assertFalse(Access::newGroup('USERS_MANAGEMENT_GROUP', 'not_exist'));
        $this->assertTrue(Access::newGroup('USERS_Group'));
        $this->assertEquals(2, count(Access::groups()));
        $this->assertEquals(0, count(Access::privileges()));
    }
    /**
     * @test
     */
    public function test06() {
        $this->assertTrue(Access::newGroup('ADMINS_Group'));
        $this->assertTrue(Access::newGroup('USERS_MANAGEMENT_GROUP', 'ADMINS_Group'));
        $this->assertTrue(Access::newGroup('USERS_Group'));
        $this->assertEquals(2, count(Access::groups()));
        $this->assertTrue(Access::hasGroup('ADMINS_Group'));
        $this->assertTrue(Access::hasGroup('USERS_MANAGEMENT_GROUP'));
        $this->assertTrue(Access::hasGroup('USERS_Group'));
        $this->assertEquals(0, count(Access::privileges()));
    }
    /**
     * @test
     */
    public function test07() {
        $this->assertTrue(Access::newGroup('ADMINS_Group'));
        $this->assertTrue(Access::newGroup('USERS_MANAGEMENT_GROUP', 'ADMINS_Group'));
        $this->assertTrue(Access::newGroup('USERS_Group'));
        $this->assertEquals(2, count(Access::groups()));
        $this->assertTrue(Access::hasGroup('ADMINS_Group'));
        $this->assertTrue(Access::hasGroup('USERS_MANAGEMENT_GROUP'));
        $this->assertTrue(Access::hasGroup('USERS_Group'));
        $this->assertEquals(0, count(Access::privileges()));
        $this->assertFalse(Access::newPrivilege('not_exist', 'new_pr'));
        $this->assertFalse(Access::newPrivilege('USERS_MANAGEMENT_GROUP', 'new-pr'));
        $this->assertFalse(Access::newPrivilege('USERS_MANAGEMENT_GROUP', 'new,pr'));
        $this->assertFalse(Access::newPrivilege('USERS_MANAGEMENT_GROUP', 'new;pr'));
        $this->assertFalse(Access::newPrivilege('USERS_MANAGEMENT_GROUP', 'new pr'));
        $this->assertTrue(Access::newPrivilege('USERS_MANAGEMENT_GROUP', 'new_pr'));
        $this->assertTrue(Access::newPrivilege('USERS_Group', 'new_pr_2'));
        $this->assertEquals(2, count(Access::privileges()));
        $this->assertEquals(1, count(Access::privileges('USERS_Group')));
        $this->assertEquals(1, count(Access::privileges('USERS_MANAGEMENT_GROUP')));
        $this->assertEquals(0, count(Access::privileges('ADMINS_Group')));
        $this->assertTrue(Access::hasPrivilege('new_pr'));
        $this->assertFalse(Access::hasPrivilege('new_pr','USERS_Group'));
        $this->assertFalse(Access::hasPrivilege('new_pr','ADMINS_Group'));
        $this->assertTrue(Access::hasPrivilege('new_pr','USERS_MANAGEMENT_GROUP'));
    }
    /**
     * @test
     */
    public function test08() {
        Access::newGroup('SUPER');
        Access::newGroup('SUB_SUPER', 'SUPER');
        Access::newGroup('SUB_SUB_SUPER', 'SUB_SUPER');
        $this->assertFalse(Access::newGroup('SUPER', 'SUB_SUB_SUPER'));
        $this->assertFalse(Access::newPrivilege('SUB_SUB_SUPER', 'SUPER'));
    }
    /**
     * @test
     */
    public function test09() {
        $this->setupComplexGroups();
        $this->assertEquals(2,count(Access::groups()));
        $this->assertEquals(1,count(Access::privileges('ADMINS')));
        $this->assertEquals(1,count(Access::privileges('SYS_USER')));
        $this->assertEquals(4,count(Access::privileges('USERS_MANAGERS')));
        $this->assertEquals(5,count(Access::privileges('FINANCE')));
        $this->assertEquals(4,count(Access::privileges('HR')));
        $this->assertEquals(2,count(Access::privileges('EMPLOYER')));
        $this->assertEquals(17,count(Access::privileges()));
    }
    /**
     * @test
     */
    public function testAsArray00() {
        $this->setupComplexGroups();
        $asArr = Access::asArray();
        $this->assertEquals(2,count($asArr));
        $this->assertEquals('ADMINS',$asArr[0]['group-id']);
        $this->assertEquals(1,count($asArr[0]['privileges']));
        $this->assertEquals(1,count($asArr[0]['child-groups']));
        $this->assertEquals('USERS_MANAGERS',$asArr[0]['child-groups'][0]['group-id']);
        $this->assertEquals(4,count($asArr[0]['child-groups'][0]['privileges']));
        $this->assertEquals('SYS_USER',$asArr[1]['group-id']);
    }
    /**
     * @test
     */
    public function testCreatePrivilegesStr00() {
        $this->setupComplexGroups();
        $user = new User();
        $user->addPrivilege('DO_INTERVIEW');
        $user->addPrivilege('EVALUATE_APLICANT');
        $str = Access::createPermissionsStr($user);
        $this->assertEquals('G-EMPLOYER',$str);
    }
    /**
     * @test
     */
    public function testCreatePrivilegesStr01() {
        $this->setupComplexGroups();
        $user = new User();
        $user->addToGroup('EMPLOYER');
        $str = Access::createPermissionsStr($user);
        $this->assertEquals('G-EMPLOYER',$str);
    }
    /**
     * @test
     */
    public function testCreatePrivilegesStr02() {
        $this->setupComplexGroups();
        $user = new User();
        $user->addToGroup('EMPLOYER');
        $user->addPrivilege('REVERSE_INVOICE');
        $str = Access::createPermissionsStr($user);
        $this->assertEquals('G-EMPLOYER;REVERSE_INVOICE-1',$str);
    }
    /**
     * @test
     */
    public function testCreatePrivilegesStr03() {
        $this->setupComplexGroups();
        $user = new User();
        $user->addToGroup('EMPLOYER');
        $user->addToGroup('ADMINS');
        $user->addPrivilege('REVERSE_INVOICE');
        $user->addPrivilege('RESET_PASSWORD_SELF');
        $str = Access::createPermissionsStr($user);
        $this->assertEquals('G-ADMINS;G-EMPLOYER;REVERSE_INVOICE-1;RESET_PASSWORD_SELF-1',$str);
    }
    /**
     * @test
     */
    public function testCreatePrivilegesStr04() {
        $this->setupSubGroups();
        $user = new User();
        $user->addToGroup('SUB_OF_SUB');
        $user->addPrivilege('SUB_PR_3');
        $user->addPrivilege('TOP_PR_2');
        $privilegesStr = Access::createPermissionsStr($user);
        $this->assertEquals('G-SUB_OF_SUB;SUB_PR_3-1;TOP_PR_2-1',$privilegesStr);
    }
    /**
     * @test
     */
    public function testCreatePrivilegesStr06() {
        $this->setupSubGroups();
        $user = new User();
        $user->addPrivilege('SUB_PR_3');
        $user->addPrivilege('SUB_PR_2');
        $user->addPrivilege('SUB_OF_PR_2');
        $privilegesStr = Access::createPermissionsStr($user);
        $this->assertEquals('SUB_PR_3-1;SUB_PR_2-1;SUB_OF_PR_2-1',$privilegesStr);
    }
    /**
     * @test
     */
    public function testResolvePrivilegesStr00() {
        $this->setupComplexGroups();
        $str = 'G-ADMINS;G-EMPLOYER;REVERSE_INVOICE-1;RESET_PASSWORD_SELF-1';
        $user = new User();
        Access::resolvePrivileges($str, $user);
        $this->assertTrue($user->hasPrivilege('REVERSE_INVOICE'));
        $this->assertTrue($user->hasPrivilege('RESET_PASSWORD_SELF'));
        $this->assertTrue($user->inGroup('ADMINS'));
        $this->assertFalse($user->hasPrivilege('VIEW_SALES_REPORT'));
        $this->assertTrue($user->inGroup('EMPLOYER'));
        $this->assertFalse($user->inGroup('HR'));
    }
    /**
     * @test
     */
    public function testResolvePrivilegesStr01() {
        $this->setupSubGroups();
        $user = new User();
        Access::resolvePrivileges('G-SUB_OF_SUB;SUB_PR_1-0;SUB_PR_3-1;TOP_PR_2-1;SUB2_OF_PR_1-1', $user);

        $this->assertTrue($user->hasPrivilege('SUB_OF_PR_1'));
        $this->assertTrue($user->hasPrivilege('SUB_OF_PR_2'));
        $this->assertTrue($user->hasPrivilege('SUB_OF_PR_3'));
        $this->assertTrue($user->inGroup('SUB_OF_SUB'));

        $this->assertFalse($user->hasPrivilege('SUB_PR_1'));
        $this->assertFalse($user->hasPrivilege('SUB_PR_2'));
        $this->assertTrue($user->hasPrivilege('SUB_PR_3'));
        $this->assertFalse($user->inGroup('SUB_GROUP'));

        $this->assertFalse($user->hasPrivilege('TOP_PR_1'));
        $this->assertTrue($user->hasPrivilege('TOP_PR_2'));
        $this->assertTrue($user->hasPrivilege('SUB2_OF_PR_1'));
        $this->assertFalse($user->hasPrivilege('TOP_PR_3'));
        $this->assertFalse($user->inGroup('TOP_GROUP'));
    }

    private function setupComplexGroups(): void {
        Access::newGroup('ADMINS');
        Access::newPrivileges('ADMINS', ['MODIFY_SYS_SETTINGS']);
        Access::newGroup('SYS_USER');
        Access::newPrivilege('SYS_USER', 'RESET_PASSWORD_SELF');
        Access::newGroup('USERS_MANAGERS', 'ADMINS');
        Access::newPrivileges('USERS_MANAGERS', [
            'RESET_ANY_USER_PASSWORD','CREATE_USER_ACCESS','UPDATE_USER_ACCESS','BLOCK_USER'
        ]);
        Access::newGroup('FINANCE','SYS_USER');
        Access::newPrivileges('FINANCE', [
            'CREATE_INVOICE','REVERSE_INVOICE','VIEW_SALES_REPORT',
            'RESET_PASSWORD_SELF','DEPOSIT','WITHDRAW',
        ]);
        Access::newGroup('HR','SYS_USER');
        Access::newPrivileges('HR', [
            'VIEW_ATTENDANCE','UPDATE_SALARY','UPDATE_POSITION',
            'RESET_PASSWORD_SELF','DO_EMP_EVAL','WITHDRAW'
        ]);
        Access::newGroup('EMPLOYER', 'HR');
        Access::newPrivileges('EMPLOYER', ['DO_INTERVIEW','EVALUATE_APLICANT']);
        Access::newGroup('SYS_USER','HR');
    }

    private function setupSubGroups(): void {
        Access::newGroup('TOP_GROUP');
        Access::newGroup('SUB_GROUP', 'TOP_GROUP');
        Access::newGroup('SUB_OF_SUB', 'SUB_GROUP');
        Access::newPrivileges('TOP_GROUP', ['TOP_PR_1','TOP_PR_2','TOP_PR_3']);
        Access::newPrivileges('SUB_GROUP', ['SUB_PR_1','SUB_PR_2','SUB_PR_3']);
        Access::newPrivileges('SUB_OF_SUB', ['SUB_OF_PR_1','SUB_OF_PR_2','SUB_OF_PR_3']);
        Access::newGroup('SUB_OF_SUB2', 'SUB_GROUP');
        Access::newPrivileges('SUB_OF_SUB', ['SUB2_OF_PR_1','SUB2_OF_PR_2','SUB2_OF_PR_3']);
    }

    // --- New RBAC/ABAC Tests ---

    /** @test */
    public function testRoleCreation() {
        $manager = new AccessManager();
        $role = $manager->role('admin', ['VIEW_ALL', 'EDIT_ALL']);
        $this->assertEquals('admin', $role->getName());
        $this->assertTrue($role->hasPermission('VIEW_ALL', $manager));
        $this->assertTrue($role->hasPermission('EDIT_ALL', $manager));
    }
    /** @test */
    public function testRoleInheritance() {
        $manager = new AccessManager();
        $manager->role('viewer', ['VIEW_POSTS']);
        $manager->role('editor', ['EDIT_POSTS'])->inherits('viewer');

        $editor = $manager->getRole('editor');
        $this->assertTrue($editor->hasPermission('EDIT_POSTS', $manager));
        $this->assertTrue($editor->hasPermission('VIEW_POSTS', $manager));
        $this->assertFalse($editor->hasPermission('DELETE_POSTS', $manager));
    }
    /** @test */
    public function testRoleWildcard() {
        $manager = new AccessManager();
        $manager->role('superadmin', ['*']);
        $manager->assignRoleToUser(1, 'superadmin');

        $this->assertTrue($manager->can(1, 'ANY_PERMISSION'));
        $this->assertTrue($manager->can(1, 'ANOTHER_ONE'));
    }
    /** @test */
    public function testCanWithRBAC() {
        $manager = new AccessManager();
        $manager->role('editor', ['EDIT_POST', 'VIEW_POST']);
        $manager->assignRoleToUser(42, 'editor');

        $this->assertTrue($manager->can(42, 'EDIT_POST'));
        $this->assertTrue($manager->can(42, 'VIEW_POST'));
        $this->assertFalse($manager->can(42, 'DELETE_POST'));
    }
    /** @test */
    public function testCanWithNoRole() {
        $manager = new AccessManager();
        $manager->role('admin', ['MANAGE']);

        $this->assertFalse($manager->can(99, 'MANAGE'));
    }
    /** @test */
    public function testCanWithPolicy() {
        $manager = new AccessManager();
        $manager->role('author', ['EDIT_OWN_POST']);
        $manager->assignRoleToUser(10, 'author');

        $manager->policy('EDIT_OWN_POST', function ($user, $resource) {
            return $resource->authorId === $user;
        });

        $ownPost = (object) ['authorId' => 10];
        $otherPost = (object) ['authorId' => 20];

        $this->assertTrue($manager->can(10, 'EDIT_OWN_POST', $ownPost));
        $this->assertFalse($manager->can(10, 'EDIT_OWN_POST', $otherPost));
    }
    /** @test */
    public function testCanWithPolicyObject() {
        $manager = new AccessManager();
        $manager->role('user', ['VIEW_PRIVATE']);
        $manager->assignRoleToUser(5, 'user');

        $policy = new class {
            public function getPermission(): string { return 'VIEW_PRIVATE'; }
            public function evaluate($user, $resource): bool {
                return $resource->isPublic || $resource->ownerId === $user;
            }
        };
        $manager->registerPolicy($policy);

        $public = (object) ['isPublic' => true, 'ownerId' => 99];
        $private = (object) ['isPublic' => false, 'ownerId' => 5];
        $otherPrivate = (object) ['isPublic' => false, 'ownerId' => 99];

        $this->assertTrue($manager->can(5, 'VIEW_PRIVATE', $public));
        $this->assertTrue($manager->can(5, 'VIEW_PRIVATE', $private));
        $this->assertFalse($manager->can(5, 'VIEW_PRIVATE', $otherPrivate));
    }
    /** @test */
    public function testAssignAndRemoveRole() {
        $manager = new AccessManager();
        $manager->role('temp', ['DO_STUFF']);
        $manager->assignRoleToUser(1, 'temp');
        $this->assertTrue($manager->can(1, 'DO_STUFF'));

        $manager->removeRoleFromUser(1, 'temp');
        $this->assertFalse($manager->can(1, 'DO_STUFF'));
    }
    /** @test */
    public function testGetUserRoles() {
        $manager = new AccessManager();
        $manager->role('a');
        $manager->role('b');
        $manager->assignRoleToUser(1, 'a');
        $manager->assignRoleToUser(1, 'b');
        $this->assertEquals(['a', 'b'], $manager->getUserRoles(1));
    }
    /** @test */
    public function testChainedInheritance() {
        $manager = new AccessManager();
        $manager->role('base', ['READ']);
        $manager->role('mid', ['WRITE'])->inherits('base');
        $manager->role('top', ['DELETE'])->inherits('mid');
        $manager->assignRoleToUser(1, 'top');

        $this->assertTrue($manager->can(1, 'DELETE'));
        $this->assertTrue($manager->can(1, 'WRITE'));
        $this->assertTrue($manager->can(1, 'READ'));
    }
    /** @test */
    public function testAccessFacadeNewAPI() {
        Access::getManager()->reset();
        Access::role('tester', ['RUN_TESTS']);
        Access::assignRoleToUser(1, 'tester');
        $this->assertTrue(Access::can(1, 'RUN_TESTS'));
        $this->assertFalse(Access::can(1, 'DEPLOY'));
    }
    /** @test */
    public function testAccessFacadeBackwardCompat() {
        Access::clear();
        Access::newGroup('devs');
        Access::newPrivilege('devs', 'CODE');
        $this->assertTrue(Access::hasPrivilege('CODE'));
        $this->assertFalse(Access::hasPrivilege('DEPLOY'));
    }
    /** @test */
    public function testInMemoryStorage() {
        $storage = new InMemoryAccessStorage();
        $role = new Role('stored-role');
        $role->addPermission('PERM_A');
        $storage->saveRole($role);
        $storage->assignRoleToUser(1, 'stored-role');

        $loaded = $storage->loadRoles();
        $this->assertCount(1, $loaded);
        $this->assertEquals('stored-role', $loaded[0]->getName());
        $this->assertEquals(['stored-role'], $storage->loadUserRoles(1));

        $storage->removeRoleFromUser(1, 'stored-role');
        $this->assertEquals([], $storage->loadUserRoles(1));

        $storage->removeRole('stored-role');
        $this->assertCount(0, $storage->loadRoles());
    }
    /** @test */
    public function testPermissionClass() {
        $p = new Permission('ADD_USER', 'Can add users');
        $this->assertEquals('ADD_USER', $p->getID());
        $this->assertEquals('Can add users', $p->getDescription());
        $p->setDbId(5);
        $this->assertEquals(5, $p->getDbId());
    }
    /** @test */
    public function testRoleClass() {
        $r = new Role('moderator');
        $r->addPermission('BAN_USER');
        $r->setDescription('Can moderate');
        $r->setDbId(3);
        $this->assertEquals('moderator', $r->getName());
        $this->assertEquals('Can moderate', $r->getDescription());
        $this->assertEquals(3, $r->getDbId());
        $manager = new AccessManager();
        $this->assertTrue($r->hasPermission('BAN_USER', $manager));
    }
    /** @test */
    public function testManagerReset() {
        $manager = new AccessManager();
        $manager->role('x', ['Y']);
        $manager->assignRoleToUser(1, 'x');
        $manager->reset();
        $this->assertEmpty($manager->getRoles());
        $this->assertEmpty($manager->getUserRoles(1));
    }
    /** @test */
    public function testFileStorageSaveAndLoad() {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_access_test_'.getmypid().'.json';
        $storage = new FileAccessStorage($path);

        $role = new Role('file-admin');
        $role->addPermission('MANAGE_ALL');
        $role->setDescription('File admin role');
        $storage->saveRole($role);
        $storage->assignRoleToUser(1, 'file-admin');

        // Load fresh
        $storage2 = new FileAccessStorage($path);
        $roles = $storage2->loadRoles();
        $this->assertCount(1, $roles);
        $this->assertEquals('file-admin', $roles[0]->getName());
        $this->assertEquals(['file-admin'], $storage2->loadUserRoles(1));

        // Remove
        $storage2->removeRoleFromUser(1, 'file-admin');
        $this->assertEquals([], $storage2->loadUserRoles(1));
        $storage2->removeRole('file-admin');
        $this->assertCount(0, $storage2->loadRoles());

        unlink($path);
    }
    /** @test */
    public function testFileStorageInheritance() {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_access_inh_'.getmypid().'.json';
        $storage = new FileAccessStorage($path);

        $viewer = new Role('viewer');
        $viewer->addPermission('VIEW');
        $storage->saveRole($viewer);

        $editor = new Role('editor');
        $editor->addPermission('EDIT');
        $editor->inherits('viewer');
        $storage->saveRole($editor);

        $loaded = $storage->loadRoles();
        $editorLoaded = null;
        foreach ($loaded as $r) {
            if ($r->getName() === 'editor') {
                $editorLoaded = $r;
            }
        }
        $this->assertNotNull($editorLoaded);
        $this->assertEquals('viewer', $editorLoaded->getParentRoleName());

        unlink($path);
    }
    /** @test */
    public function testFileStorageEmptyFile() {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_access_empty_'.getmypid().'.json';
        $storage = new FileAccessStorage($path);
        $this->assertCount(0, $storage->loadRoles());
        $this->assertEquals([], $storage->loadUserRoles(99));
    }
    /** @test */
    public function testDatabaseStorageSQLite() {
        $dbPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_access_db_'.getmypid().'.db';
        $conn = new ConnectionInfo('sqlite', '', '', $dbPath, '');
        $db = new Database($conn);

        try {
            $storage = new DatabaseAccessStorage($db);
            $db->createTables();

            // Save role with permissions
            $role = new Role('db-admin');
            $role->addPermission('DB_MANAGE');
            $role->addPermission('DB_READ');
            $role->setDescription('Database admin');
            $storage->saveRole($role);

            // Assign to user
            $storage->assignRoleToUser(42, 'db-admin');

            // Load
            $roles = $storage->loadRoles();
            $this->assertCount(1, $roles);
            $this->assertEquals('db-admin', $roles[0]->getName());

            $userRoles = $storage->loadUserRoles(42);
            $this->assertEquals(['db-admin'], $userRoles);

            // Remove user role
            $storage->removeRoleFromUser(42, 'db-admin');
            $this->assertEquals([], $storage->loadUserRoles(42));

            // Remove role
            $storage->removeRole('db-admin');
            $this->assertCount(0, $storage->loadRoles());
        } finally {
            if (file_exists($dbPath)) {
                unlink($dbPath);
            }
        }
    }
    /** @test */
    public function testDatabaseStorageWithInheritance() {
        $dbPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_access_dbinh_'.getmypid().'.db';
        $conn = new ConnectionInfo('sqlite', '', '', $dbPath, '');
        $db = new Database($conn);

        try {
            $storage = new DatabaseAccessStorage($db);
            $db->createTables();

            $viewer = new Role('viewer');
            $viewer->addPermission('VIEW');
            $storage->saveRole($viewer);

            $editor = new Role('editor');
            $editor->addPermission('EDIT');
            $editor->inherits('viewer');
            $storage->saveRole($editor);

            $roles = $storage->loadRoles();
            $editorLoaded = null;
            foreach ($roles as $r) {
                if ($r->getName() === 'editor') {
                    $editorLoaded = $r;
                }
            }
            $this->assertNotNull($editorLoaded);
            $this->assertEquals('viewer', $editorLoaded->getParentRoleName());
        } finally {
            if (file_exists($dbPath)) {
                unlink($dbPath);
            }
        }
    }
    /** @test */
    public function testFullFlowWithDatabaseStorage() {
        $dbPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_access_flow_'.getmypid().'.db';
        $conn = new ConnectionInfo('sqlite', '', '', $dbPath, '');
        $db = new Database($conn);

        try {
            $storage = new DatabaseAccessStorage($db);
            $db->createTables();

            // Setup via AccessManager
            $manager = new AccessManager($storage);
            $manager->role('admin', ['*']);
            $manager->role('editor', ['EDIT', 'VIEW'])->inherits('admin');
            $manager->saveToStorage();

            // Fresh manager loads from storage
            $manager2 = new AccessManager($storage);
            $manager2->loadFromStorage();
            $manager2->assignRoleToUser(1, 'editor');
            $storage->assignRoleToUser(1, 'editor');
            $manager2->loadUserRolesFromStorage(1);

            $this->assertTrue($manager2->can(1, 'EDIT'));
            $this->assertTrue($manager2->can(1, 'VIEW'));
        } finally {
            if (file_exists($dbPath)) {
                unlink($dbPath);
            }
        }
    }
    /** @test */
    public function testPermissionDbId() {
        $perm = new \WebFiori\Framework\Permission('TEST_PERM', 'A test permission');
        $this->assertNull($perm->getDbId());
        $perm->setDbId(42);
        $this->assertEquals(42, $perm->getDbId());
        $this->assertEquals('A test permission', $perm->getDescription());
        $perm->setDescription('Updated');
        $this->assertEquals('Updated', $perm->getDescription());
    }
}
