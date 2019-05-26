<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\User;
/**
 * A test class for testing the class 'webfiori\entity\User'.
 *
 * @author Ibrahim
 */
class UserTest extends TestCase{
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
     * @param User $user
     * @depends test00
     */
    public function toStringTest00($user) {
        $this->assertEquals('{"user-id":-1, "email":"", "display-name":null, "username":""}',$user.'');
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
    public function testSetResetCount() {
        $u = new User();
        $u->setResetCount('1');
        $this->assertEquals(0,$u->getResetCount());
        $u->setResetCount(-1);
        $this->assertEquals(0,$u->getResetCount());
        $u->setResetCount(32);
        $this->assertEquals(32,$u->getResetCount());
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
    public function testInGroup00() {
        $u = new User();
        $this->assertFalse($u->inGroup('not-exist'));
    }
    /**
     * @test
     */
    public function testHasPrivilege00() {
        $u = new User();
        $this->assertFalse($u->hasPrivilege('not-exist'));
    }
}
