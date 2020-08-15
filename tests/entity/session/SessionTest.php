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
        $this->assertEquals(7200, $sesston->getDuration());
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
        $this->assertEquals(120, $sesston->getDuration());
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
        $session = new Session([
            'name' => 'wf-session'
        ]);
        $this->assertEquals('wf-session', $session->getName());
        $this->assertEquals(7200, $session->getDuration());
        $this->assertEquals(120*60, $session->getRemainingTime());
        $this->assertEquals(0, $session->getStartedAt());
        $this->assertEquals(0, $session->getResumedAt());
        $this->assertEquals(0, $session->getPassedTime());
        $this->assertNull($session->getLangCode());
        $this->assertNull($session->getUser());
        $this->assertEquals(Session::STATUS_INACTIVE, $session->getStatus());
        $this->assertEquals([
            'expires' => 60 * 120 + time(),
            'domain' => 'example.com',
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
            'duration' => 0,
            'name' => 'hello'
        ]);
        $this->assertFalse($session->isPersistent());
        $this->assertFalse($session->isRunning());
        $this->assertTrue($session->isRefresh());
        $this->assertEquals([
            'expires' => 0,
            'domain' => 'example.com',
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ], $session->getCookieParams());
    }
    /**
     * @test
     */
    public function testConstructor04() {
        $this->expectException(\Exception::class);
        $session = new Session();
    }
    /**
     * @test
     */
    public function testStart00() {
        $session = new Session(['name'=>'new']);
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
    /**
     * @test
     */
    public function testCookieHeader() {
        $s = new Session([
            'name' => 'super-session'
        ]);
        $params = $s->getCookieParams();
        $this->assertEquals('Set-Cookie: '
                . 'super-session='.$s->getId().'; '
                . 'expires='.date(DATE_COOKIE,$params['expires']).'; '
                . 'path=/; Secure; HttpOnly; SameSite=Lax', $s->getCookieHeader());
        $s->setSameSite('none');
        $this->assertEquals('Set-Cookie: '
                . 'super-session='.$s->getId().'; '
                . 'expires='.date(DATE_COOKIE,$params['expires']).'; '
                . 'path=/; Secure; HttpOnly; SameSite=None', $s->getCookieHeader());
        $s->setSameSite(' strict');
        $this->assertEquals('Set-Cookie: '
                . 'super-session='.$s->getId().'; '
                . 'expires='.date(DATE_COOKIE,$params['expires']).'; '
                . 'path=/; Secure; HttpOnly; SameSite=Strict', $s->getCookieHeader());
        $s->setDuration(0);
        $this->assertEquals('Set-Cookie: '
                . 'super-session='.$s->getId().'; '
                . 'path=/; Secure; HttpOnly; SameSite=Strict', $s->getCookieHeader());
    }
    /**
     * @test
     */
    public function testRemainingTime() {
        $s = new Session(['name'=>'session','duration'=>0.1]);
        $s->start();
        $this->assertEquals(6, $s->getDuration());
        $s->close();
        sleep(7);
        $s->start();
        
        $this->assertEquals(-1, $s->getRemainingTime());
    }
    /**
     * @test
     */
    public function testToJsonTest00() {
        $_POST['lang'] = 'fr';
        $s = new Session(['name'=>'session','duration'=>1]);
        $j = $s->toJSON();
        $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session", '
                . '"started_at":0, '
                . '"duration":60, '
                . '"resumed_at":0, '
                . '"passed_time":0, '
                . '"remaining_time":60, '
                . '"language":null, '
                . '"id":"'.$s->getId().'", '
                . '"is_refresh":false, '
                . '"is_persistent":true, '
                . '"status":"status_none", '
                . '"user":null, '
                . '"vars":[]}',$j.'');
        $s->start();
        $j = $s->toJSON();
        $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session", '
                . '"started_at":'.$s->getStartedAt().', '
                . '"duration":60, '
                . '"resumed_at":'.$s->getStartedAt().', '
                . '"passed_time":0, '
                . '"remaining_time":60, '
                . '"language":"EN", '
                . '"id":"'.$s->getId().'", '
                . '"is_refresh":false, '
                . '"is_persistent":true, '
                . '"status":"status_new", '
                . '"user":{"user-id":-1, "email":"", "display-name":null, "username":""}, '
                . '"vars":[]}',$j.'');
    }
    /**
     * @test
     */
    public function testToJsonTest01() {
        $_GET['lang'] = 'en';
        $s = new Session(['name'=>'session','duration'=>1]);
        $j = $s->toJSON();
        $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session", '
                . '"started_at":0, '
                . '"duration":60, '
                . '"resumed_at":0, '
                . '"passed_time":0, '
                . '"remaining_time":60, '
                . '"language":null, '
                . '"id":"'.$s->getId().'", '
                . '"is_refresh":false, '
                . '"is_persistent":true, '
                . '"status":"status_none", '
                . '"user":null, '
                . '"vars":[]}',$j.'');
        $s->start();
        $j = $s->toJSON();
        $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session", '
                . '"started_at":'.$s->getStartedAt().', '
                . '"duration":60, '
                . '"resumed_at":'.$s->getStartedAt().', '
                . '"passed_time":0, '
                . '"remaining_time":60, '
                . '"language":"EN", '
                . '"id":"'.$s->getId().'", '
                . '"is_refresh":false, '
                . '"is_persistent":true, '
                . '"status":"status_new", '
                . '"user":{"user-id":-1, "email":"", "display-name":null, "username":""}, '
                . '"vars":[]}',$j.'');
    }
}
