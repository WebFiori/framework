<?php
use PHPUnit\Framework\TestCase;

use webfiori\entity\sesstion\Session;
/**
 * Description of SessionTest
 *
 * @author Eng.Ibrahim
 */
class SessionTest extends TestCase {
    /**
     * @test
     */
    public function testConstructor00() {
        $sesston = new Session([
            'name' => 'my-new-sesstion'
        ]);
        $this->assertEquals('my-new-sesstion', $sesston->getName());
        $this->assertEquals(120, $sesston->getDuration());
        $this->assertEquals(0, $sesston->getStartedAt());
        $this->assertEquals(0, $sesston->getResumedAt());
        $this->assertEquals(0, $sesston->getPassedTime());
        $this->assertNull($sesston->getLangCode());
        $this->assertNull($sesston->getUser());
        $this->assertNotNull($sesston->getId());
        $this->assertEquals(Session::STATUS_INACTIVE, $sesston->getStatus());
    }
    /**
     * @test
     */
    public function testConstructor01() {
        $sesston = new Session([
            'name' => 'my-new-sessionx',
            'duration' => 2,
            'session-id' => 'super'
        ]);
        $this->assertEquals('my-new-sessionx', $sesston->getName());
        $this->assertEquals(2, $sesston->getDuration());
        $this->assertEquals(0, $sesston->getStartedAt());
        $this->assertEquals(0, $sesston->getResumedAt());
        $this->assertEquals(0, $sesston->getPassedTime());
        $this->assertNull($sesston->getLangCode());
        $this->assertNull($sesston->getUser());
        $this->assertEquals('super',$sesston->getId());
        $this->assertEquals(Session::STATUS_INACTIVE, $sesston->getStatus());
    }
}
