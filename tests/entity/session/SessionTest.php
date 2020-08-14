<?php
use PHPUnit\Framework\TestCase;

use webfiori\entity\session\Session;
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
    /**
     * @test
     */
    public function testConstructor02() {
        $session = new Session();
        $this->assertEquals('wf-session', $session->getName());
        $this->assertEquals(120, $session->getDuration());
        $this->assertEquals(120*60, $session->getRemainingTime());
        $this->assertEquals(0, $session->getStartedAt());
        $this->assertEquals(0, $session->getResumedAt());
        $this->assertEquals(0, $session->getPassedTime());
        $this->assertNull($session->getLangCode());
        $this->assertNull($session->getUser());
        $this->assertEquals(Session::STATUS_INACTIVE, $session->getStatus());
        $this->assertEquals([
            'expires' => 60 * 120 + time(),
            'domain' => '127.0.0.1',
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ], $session->getCookieParams());
        $this->assertTrue($session->isPersistent());
        $this->assertFalse($session->isRunning());
        $this->assertFalse($session->isRefresh());
    }
    /**
     * @test
     */
    public function testConstructor03() {
        $session = new Session([
            'refresh' => true,
            'duration' => 0
        ]);
        $this->assertFalse($session->isPersistent());
        $this->assertFalse($session->isRunning());
        $this->assertTrue($session->isRefresh());
        $this->assertEquals([
            'expires' => 0,
            'domain' => '127.0.0.1',
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ], $session->getCookieParams());
    }
    
    /**
     * @test
     */
    public function testStart00() {
        $session = new Session();
        $this->assertEquals(Session::STATUS_INACTIVE,$session->getStatus());
        $this->assertEquals(0, $session->getStartedAt());
        $this->assertFalse($session->isRunning());
        $this->assertEquals(0, $session->getResumedAt());
        $session->set('hello', 'world');
        $this->assertNull($session->get('hello'));
        $session->start();
        $this->assertEquals(0, $session->getPassedTime());
        $this->assertEquals(Session::STATUS_NEW,$session->getStatus());
        $this->assertEquals(time(), $session->getStartedAt());
        $this->assertEquals(time(), $session->getResumedAt());
        $this->assertTrue($session->isRunning());
        $session->set('hello', 'world');
        $this->assertEquals('world', $session->get('hello'));
        
        return $session;
    }
    /**
     * @depends testStart00
     * @param Session $session
     * @test
     */
    public function testClose00($session) {
        $session->close();
        $this->assertFalse($session->isRunning());
        $this->assertEquals(0, $session->getStartedAt());
        $this->assertEquals(0, $session->getResumedAt());
        $this->assertNull($session->get('hello'));
        return $session;
    }
    /**
     * @depends testClose00
     * @param Session $session
     * @test
     */
    public function testStart01($session) {
        $this->assertEquals(0, $session->getPassedTime());
        sleep(10);
        $session->start();
        $this->assertEquals(Session::STATUS_RESUMED,$session->getStatus());
        $this->assertTrue(in_array($session->getStartedAt(),[
            time() - 8,
            time() - 9, 
            time() - 10, 
            time() - 11,
            time() - 12]));
        $this->assertEquals(time(), $session->getResumedAt());
        $this->assertTrue(in_array($session->getPassedTime(),[9,10,11,12]));
        $this->assertEquals('world', $session->get('hello'));
    }
}
