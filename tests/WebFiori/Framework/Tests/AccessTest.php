<?php
namespace WebFiori\Framework\Test;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Access;
use WebFiori\Framework\User;
/**
 * A test class for testing the class 'WebFiori\Framework\Access'.
 *
 * @author Ibrahim
 */
class AccessTest extends TestCase {
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
        Access::newGroup('SUPER');
        Access::newGroup('SUB_SUPER', 'SUPER');
        Access::newGroup('SUB_SUB_SUPER', 'SUB_SUPER');
        $this->assertFalse(Access::newGroup('SUPER', 'SUB_SUB_SUPER'));
        $this->assertFalse(Access::newPrivilege('SUB_SUB_SUPER', 'SUPER'));
        Access::clear();
    }
    /**
     * @test
     */
    public function test09() {
        $this->assertTrue(Access::newGroup('ADMINS'));
        $r = Access::newPrivileges('ADMINS', [
            'MODIFY_SYS_SETTINGS'
        ]);

        foreach ($r as $bool) {
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
        $this->assertEquals([
            'DO_INTERVIEW' => true,
            'EVALUATE_APLICANT' => true
        ], Access::newPrivileges('EMPLOYER', [
            'DO_INTERVIEW','EVALUATE_APLICANT'
        ]));
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
     * @depends test09
     */
    public function testAsArray00() {
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
     * @depends test09
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
     * @depends test09
     */
    public function testCreatePrivilegesStr01() {
        $user = new User();
        $user->addToGroup('EMPLOYER');
        $str = Access::createPermissionsStr($user);
        $this->assertEquals('G-EMPLOYER',$str);
    }
    /**
     * @test
     * @depends test09
     */
    public function testCreatePrivilegesStr02() {
        $user = new User();
        $user->addToGroup('EMPLOYER');
        $user->addPrivilege('REVERSE_INVOICE');
        $str = Access::createPermissionsStr($user);
        $this->assertEquals('G-EMPLOYER;REVERSE_INVOICE-1',$str);
    }
    /**
     * @test
     * @depends test09
     */
    public function testCreatePrivilegesStr03() {
        $user = new User();
        $user->addToGroup('EMPLOYER');
        $user->addToGroup('ADMINS');
        $user->addPrivilege('REVERSE_INVOICE');
        $user->addPrivilege('RESET_PASSWORD_SELF');
        $str = Access::createPermissionsStr($user);
        $this->assertEquals('G-ADMINS;G-EMPLOYER;REVERSE_INVOICE-1;RESET_PASSWORD_SELF-1',$str);

        return $str;
    }
    /**
     *
     * @param User $user
     * @depends testResolvePrivilegesStr01
     */
    public function testCreatePrivilegesStr04($user) {
        $privilegesStr = Access::createPermissionsStr($user);
        $this->assertEquals('G-SUB_OF_SUB;SUB_PR_3-1;TOP_PR_2-1',$privilegesStr);
    }
    /**
     *
     * @depends testResolvePrivilegesStr01
     * @test
     */
    public function testCreatePrivilegesStr06() {
        $user = new User();
        $user->addPrivilege('SUB_PR_3');
        $user->addPrivilege('SUB_PR_2');
        $user->addPrivilege('SUB_OF_PR_2');
        $privilegesStr = Access::createPermissionsStr($user);
        $this->assertEquals('SUB_PR_3-1;SUB_PR_2-1;SUB_OF_PR_2-1',$privilegesStr);
    }
    /**
     * @depends testCreatePrivilegesStr03
     * @param type $str
     */
    public function testResolvePrivilegesStr00($str) {
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
        Access::newGroup('TOP_GROUP');
        Access::newGroup('SUB_GROUP', 'TOP_GROUP');
        Access::newGroup('SUB_OF_SUB', 'SUB_GROUP');
        Access::newPrivileges('TOP_GROUP', [
            'TOP_PR_1','TOP_PR_2','TOP_PR_3'
        ]);
        Access::newPrivileges('SUB_GROUP', [
            'SUB_PR_1','SUB_PR_2','SUB_PR_3'
        ]);
        Access::newPrivileges('SUB_OF_SUB', [
            'SUB_OF_PR_1','SUB_OF_PR_2','SUB_OF_PR_3'
        ]);
        Access::newGroup('SUB_OF_SUB2', 'SUB_GROUP');
        $user = new User();
        Access::newPrivileges('SUB_OF_SUB', [
            'SUB2_OF_PR_1','SUB2_OF_PR_2','SUB2_OF_PR_3'
        ]);
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

        return $user;
    }
}
