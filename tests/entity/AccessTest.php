<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\Access;
use webfiori\entity\User;
/**
 * A test class for testing the class 'webfiori\entity\Access'.
 *
 * @author Ibrahim
 */
class AccessTest extends TestCase{
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
    }
    /**
     * @test
     */
    public function test02() {
        $this->assertTrue(Access::newGroup('ADMINS_Group'));
        $this->assertFalse(Access::newGroup('ADMINS_Group'));
        Access::clear();
    }
    /**
     * @test
     */
    public function test03() {
        $this->assertFalse(Access::newGroup('ADMINS_Group;'));
        $this->assertFalse(Access::newGroup('ADMINS_Group,'));
        $this->assertFalse(Access::newGroup('ADMINS-Group,'));
        Access::clear();
    }
    /**
     * @test
     */
    public function test04() {
        $this->assertTrue(Access::newGroup('ADMINS_Group'));
        $this->assertTrue(Access::newGroup('USERS_Group'));
        $this->assertEquals(2, count(Access::groups()));
        $this->assertEquals(0, count(Access::privileges()));
        Access::clear();
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
        Access::clear();
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
        Access::clear();
        
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
        Access::clear();
    }
    /**
     * @test
     */
    public function test08() {
        $this->assertTrue(Access::newGroup('ADMINS'));
        $r = Access::newPrivileges('ADMINS', [
            'MODIFY_SYS_SETTINGS'
        ]);
        foreach ($r as $bool){
            $this->assertTrue($bool);
        }
        Access::newGroup('SYS_USER');
        Access::newPrivilege('SYS_USER', 'RESET_PASSWORD_SELF');
        $this->assertTrue(Access::newGroup('USERS_MANAGERS', 'ADMINS'));
        Access::newPrivileges('USERS_MANAGERS', [
            'RESET_ANY_USER_PASSWORD',
            'CREATE_USER_ACCESS','UPDATE_USER_ACCESS',
            'BLOCK_USER'
        ]);
        $this->assertTrue(Access::newGroup('FINANCE','SYS_USER'));
        Access::newPrivileges('FINANCE', [
            'CREATE_INVOICE',
            'REVERSE_INVOICE','VIEW_SALES_REPORT',
            'RESET_PASSWORD_SELF',
            'DEPOSIT','WITHDRAW',
        ]);
        $this->assertTrue(Access::newGroup('HR','SYS_USER'));
        Access::newPrivileges('HR', [
            'VIEW_ATTENDANCE',
            'UPDATE_SALARY',
            'UPDATE_POSITION','RESET_PASSWORD_SELF',
            'DO_EMP_EVAL','WITHDRAW'
        ]);
        $this->assertTrue(Access::newGroup('EMPLOYER', 'HR'));
        Access::newPrivileges('EMPLOYER', [
            'DO_INTERVIEW','EVALUATE_APLICANT'
        ]);
        $this->assertFalse(Access::newGroup('SYS_USER','HR'));
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
     * @depends test08
     */
    public function testCreatePrivilegesStr00() {
        $user = new User();
        $user->addPrivilege('DO_INTERVIEW');
        $user->addPrivilege('EVALUATE_APLICANT');
        $str = Access::createPermissionsStr($user);
        $this->assertEquals('G-EMPLOYER',$str);
    }
    /**
     * @test
     * @depends test08
     */
    public function testCreatePrivilegesStr01() {
        $user = new User();
        $user->addToGroup('EMPLOYER');
        $str = Access::createPermissionsStr($user);
        $this->assertEquals('G-EMPLOYER',$str);
    }
}
