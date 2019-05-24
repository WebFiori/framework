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
}
