<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\Access;
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
}
