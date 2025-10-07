<?php
namespace WebFiori\Framework\Test;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Access;
use WebFiori\Framework\User;
/**
 * A test class for testing the class 'WebFiori\framework\User'.
 *
 * @author Ibrahim
 */
class UserTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $u = new User();
        $this->assertEquals(-1,$u->getID());
        $this->assertEquals('',$u->getUserName());
        $this->assertEquals('',$u->getPassword());
        $this->assertEquals('',$u->getEmail());
        $this->assertNull($u->getLastLogin());
        $this->assertNull($u->getLastPasswordResetDate());
        $this->assertNull($u->getRegDate());
        $this->assertNull($u->getDisplayName());
        $this->assertEquals(0,$u->getResetCount());

        return $u;
    }
    /**
     * @test
     */
    public function testAddPrivilege00() {
        $this->initPrivileges();
        $u = new User();
        $this->assertTrue($u->addPrivilege('TOP_PR_1'));
        $this->assertFalse($u->addPrivilege('TOP_PR_1'));
        $this->assertTrue($u->hasAnyPrivilege([
            'TOP_PR_1','LOW_PR_1'
        ]));
        $this->assertFalse($u->hasAnyPrivilege([
            'LOW_PR_1'
        ]));

        return $u;
    }
    /**
     * @test
     */
    public function testAddPrivilege01() {
        $this->initPrivileges();
        $u = new User();
        $this->assertFalse($u->addPrivilege('NOT_EXIST'));
    }
    /**
     * @test
     */
    public function testHasPrivilege00() {
        $u = new User();
        $this->assertFalse($u->hasPrivilege('not-exist'));
    }
    /**
     * @test
     */
    public function testInGroup00() {
        $u = new User();
        $this->assertFalse($u->inGroup('not-exist'));
    }
    /**
     * @test
     */
    public function testInGroup01() {
        $u = new User();
        $this->assertFalse($u->inGroup('EMPTY_GROUP'));
    }
    /**
     * @test
     */
    public function testInGroup02() {
        $u = new User();
        $u->addToGroup('LOW_GROUP');
        $this->assertTrue($u->inGroup('LOW_GROUP'));
        $u->removePrivilege('LOW_PR_2');
        $this->assertFalse($u->inGroup('LOW_GROUP'));
    }
    /**
     *
     * @test
     */
    public function testRemoveAllPrivilege00() {
        $u = new User();
        $u->addToGroup('TOP_GROUP');
        $this->assertEquals(6,count($u->privileges()));
        $u->removeAllPrivileges();
        $this->assertEquals(0,count($u->privileges()));
    }
    /**
     *
     * @param User $u
     * @test
     * @depends testAddPrivilege00
     */
    public function testRemovePrivilege00($u) {
        $this->assertTrue($u->removePrivilege('TOP_PR_1'));
        $this->assertFalse($u->hasPrivilege('TOP_PR_1'));
    }
    /**
     *
     * @test
     */
    public function testRemovePrivilege01() {
        $u = new User();
        $u->addToGroup('TOP_GROUP');
        $this->assertTrue($u->hasPrivilege('TOP_PR_1'));
        $this->assertTrue($u->removePrivilege('TOP_PR_1'));
        $this->assertFalse($u->hasPrivilege('TOP_PR_1'));
        $this->assertTrue($u->hasPrivilege('TOP_PR_3'));
        $this->assertTrue($u->removePrivilege('TOP_PR_3'));
        $this->assertFalse($u->hasPrivilege('TOP_PR_3'));
        $this->assertTrue($u->hasPrivilege('LOW_PR_1'));
        $this->assertTrue($u->removePrivilege('LOW_PR_1'));
        $this->assertFalse($u->hasPrivilege('LOW_PR_1'));
    }
    /**
     *
     * @test
     */
    public function testRemovePrivilege02() {
        $u = new User();
        $u->addToGroup('TOP_GROUP');
        $this->assertFalse($u->removePrivilege('NOT_EXIST'));
    }
    /**
     * @test
     */
    public function testSetDisplayName() {
        $u = new User();
        $u->setDisplayName('');
        $this->assertNull($u->getDisplayName());
        $u->setDisplayName('Hello');
        $this->assertEquals('Hello',$u->getDisplayName());
        $u->setDisplayName("   Hello User   \n");
        $this->assertEquals('Hello User',$u->getDisplayName());
    }
    /**
     * @test
     */
    public function testSetLastLogin() {
        $u = new User();
        $u->setLastLogin('2018-09-09');
        $this->assertEquals('2018-09-09',$u->getLastLogin());
    }
    /**
     * @test
     */
    public function testSetRegDate() {
        $u = new User();
        $u->setRegDate('2018-09-09 07:09:44');
        $this->assertEquals('2018-09-09 07:09:44',$u->getRegDate());
    }
    /**
     * @test
     */
    public function testSetResetCount() {
        $u = new User();
        $this->assertEquals(0,$u->getResetCount());
        $u->setResetCount('1');
        $this->assertEquals(1,$u->getResetCount());
        $u->setResetCount(-1);
        $this->assertEquals(1,$u->getResetCount());
        $u->setResetCount(32);
        $this->assertEquals(32,$u->getResetCount());
    }
    /**
     * @test
     * @param User $user
     * @depends test00
     */
    public function toStringTest00($user) {
        $j = $user->toJSON();
        $j->setPropsStyle('camel');
        $this->assertEquals('{"userId":-1,"email":"","displayName":null,"username":""}',$j.'');
    }
    /**
     * @test
     * @param User $user
     * @depends test00
     */
    public function toStringTest01($user) {
        $this->assertEquals('{"userId":-1,"email":"","displayName":null,"username":""}',$user.'');
    }
    private function initPrivileges() {
        Access::clear();
        Access::newGroup('TOP_GROUP');
        Access::newGroup('LOW_GROUP','TOP_GROUP');
        Access::newGroup('EMPTY_GROUP');
        Access::newPrivilege('TOP_GROUP', 'TOP_PR_1');
        Access::newPrivilege('TOP_GROUP', 'TOP_PR_2');
        Access::newPrivilege('TOP_GROUP', 'TOP_PR_3');
        Access::newPrivilege('LOW_GROUP', 'LOW_PR_1');
        Access::newPrivilege('LOW_GROUP', 'LOW_PR_2');
        Access::newPrivilege('LOW_GROUP', 'LOW_PR_3');
    }
}
