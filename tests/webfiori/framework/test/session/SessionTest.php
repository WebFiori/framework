<?php
namespace webfiori\framework\test\session;

use PHPUnit\Framework\TestCase;
use WebFiori\File\File;
use webfiori\framework\exceptions\SessionException;
use webfiori\framework\session\Session;
use webfiori\framework\session\SessionStatus;
/**
 * Description of SessionTest
 *
 * @author Eng.Ibrahim
 */
class SessionTest extends TestCase {
    /**
     * @depends testStart00
     * @param Session $session
     * @test
     */
    public function testClose00($session) {
        $session->close();
        $filePath = ROOT_PATH.DS.'app'.DS.'sto'.DS.'sessions'.DS.$session->getId();
        $this->assertTrue(File::isFileExist($filePath));
        $this->assertFalse($session->isRunning());
        $this->assertEquals(0,$session->getStartedAt());
        $this->assertEquals(0,$session->getResumedAt());
        $this->assertNull($session->get('hello'));

        return $session;
    }
    /**
     * @test
     */
    public function testConstructor00() {
        $sesston = new Session([
            'name' => 'my-new-sesstion'
        ]);
        $this->assertEquals('my-new-sesstion',$sesston->getName());
        $this->assertEquals(7200,$sesston->getDuration());
        $this->assertEquals(0,$sesston->getStartedAt());
        $this->assertEquals(0,$sesston->getResumedAt());
        $this->assertEquals(0,$sesston->getPassedTime());
        $this->assertEquals('', $sesston->getLangCode());
        //$this->assertNull($sesston->getUser());
        $this->assertNotNull($sesston->getId());
        $this->assertEquals(SessionStatus::INACTIVE,$sesston->getStatus());
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
        $this->assertEquals('my-new-sessionx',$sesston->getName());
        $this->assertEquals(120,$sesston->getDuration());
        $this->assertEquals(0,$sesston->getStartedAt());
        $this->assertEquals(0,$sesston->getResumedAt());
        $this->assertEquals(0,$sesston->getPassedTime());
        $this->assertEquals('', $sesston->getLangCode());
        $this->assertEquals('', $sesston->getLangCode(true));
        $this->assertNull($sesston->getUser());
        $this->assertEquals('super',$sesston->getId());
        $this->assertEquals(SessionStatus::INACTIVE,$sesston->getStatus());
    }
    /**
     * @test
     */
    public function testConstructor02() {
        $session = new Session([
            'name' => 'wf-session'
        ]);
        $this->assertEquals('wf-session',$session->getName());
        $this->assertEquals(7200,$session->getDuration());
        $this->assertEquals(120 * 60,$session->getRemainingTime());
        $this->assertEquals(0,$session->getStartedAt());
        $this->assertEquals(0,$session->getResumedAt());
        $this->assertEquals(0,$session->getPassedTime());
        $this->assertEquals('', $session->getLangCode());
        //$this->assertNull($session->getUser());
        $this->assertEquals(SessionStatus::INACTIVE,$session->getStatus());
        $cookie = $session->getCookie();
        $this->assertEquals(time() + 7200, $cookie->getExpires());
        $this->assertEquals(date(DATE_COOKIE, Session::DEFAULT_SESSION_DURATION * 60 + time()), $cookie->getLifetime());
        $this->assertEquals('/', $cookie->getPath());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertEquals('Lax', $cookie->getSameSite());
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
        $this->assertFalse($session->isRefresh());
        $cookie = $session->getCookie();
        $this->assertEquals('', $cookie->getLifetime());
        $this->assertEquals('/', $cookie->getPath());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertEquals('Lax', $cookie->getSameSite());
    }
    /**
     * @test
     */
    public function testConstructor04() {
        $this->expectException(SessionException::class);
        $this->expectExceptionMessage("Invalid session name: ''.");
        $session = new Session();
    }
    /**
     * @test
     */
    public function testConstructor05() {
        $_SERVER['REMOTE_ADDR'] = '::1';
        $session = new Session([
            'refresh' => true,
            'duration' => 0,
            'name' => 'hello'
        ]);
        $this->assertEquals('127.0.0.1', $session->getIp());
    }
    /**
     * @test
     */
    public function testCookieHeader() {
        $s = new Session([
            'name' => 'super-session'
        ]);
        $cookie = $s->getCookie();
        $cookie->setDomain();
        $this->assertEquals('super-session='.$s->getId().'; '
                .'expires='.$cookie->getLifetime().'; '
                .'path=/; Secure; HttpOnly; SameSite=Lax',$s->getCookieHeader());
        $s->setSameSite('None');
        $this->assertEquals('super-session='.$s->getId().'; '
                .'expires='.$cookie->getLifetime().'; '
                .'path=/; Secure; HttpOnly; SameSite=None',$s->getCookieHeader());
        $s->setSameSite(' Strict');
        $this->assertEquals('super-session='.$s->getId().'; '
                .'expires='.$cookie->getLifetime().'; '
                .'path=/; Secure; HttpOnly; SameSite=Strict',$s->getCookieHeader());
        $s->setDuration(0);
        $this->assertEquals('super-session='.$s->getId().'; '
                .'path=/; Secure; HttpOnly; SameSite=Strict',$s->getCookieHeader());
    }
    /**
     * @test
     */
    public function testRemainingTime() {
        $s = new Session(['name' => 'session','duration' => 0.1]);
        $s->start();
        $this->assertEquals(6, $s->getDuration());
        $sessionId = $s->getId();
        $s->close();
        sleep(1);
        
        // Create new session with same ID to simulate cookie persistence
        $s2 = new Session(['name' => 'session','duration' => 0.1, 'session-id' => $sessionId]);
        $s2->start();

        $this->assertEquals(-1, $s2->getRemainingTime());
    }
    /**
     * @test
     */
    public function testSetVar00() {
        $session = new Session(['name' => 'new']);
        $this->assertFalse($session->has('test'));
        $session->start();
        $this->assertFalse($session->has('test'));
        $session->set('super', 700);
        $this->assertTrue($session->has('super'));
        $this->assertEquals(700, $session->get('super'));
        $var = $session->pull('super');
        $this->assertEquals(700, $var);
        $this->assertFalse($session->has('super'));
    }
    /**
     * @test
     */
    public function testStart00() {
        $_POST['lang'] = 'EN';
        putenv('REQUEST_METHOD=POST');
        $session = new Session(['name' => 'new']);
        $this->assertEquals(SessionStatus::INACTIVE,$session->getStatus());
        $this->assertEquals(0,$session->getStartedAt());
        $this->assertFalse($session->isRunning());
        $this->assertEquals(0,$session->getResumedAt());
        $session->set('hello','world');
        $this->assertNull($session->get('hello'));
        $this->assertEquals('', $session->getLangCode());
        $session->start();
        $this->assertEquals('EN', $session->getLangCode());
        $this->assertEquals('EN', $session->getLangCode(true));
        $_POST['lang'] = 'AR';
        putenv('REQUEST_METHOD=POST');
        $this->assertEquals('EN', $session->getLangCode());
        $this->assertEquals('AR', $session->getLangCode(true));
        $this->assertEquals(0,$session->getPassedTime());
        $this->assertEquals(SessionStatus::NEW,$session->getStatus());
        $this->assertEquals(time(),$session->getStartedAt());
        $this->assertEquals(time(),$session->getResumedAt());
        $this->assertTrue($session->isRunning());
        $session->set('hello','world');
        $this->assertEquals('world',$session->get('hello'));

        return $session;
    }
    /**
     * @depends testClose00
     * @param Session $session
     * @test
     */
    public function testStart01($session) {
        $this->assertEquals(0,$session->getPassedTime());
        sleep(1);
        $session->start();
        $this->assertEquals(SessionStatus::RESUMED,$session->getStatus());
        $this->assertTrue(in_array($session->getStartedAt(),[
            time() - 8,
            time() - 9,
            time() - 10,
            time() - 11,
            time() - 12]));
        $this->assertEquals(time(),$session->getResumedAt());
        $this->assertTrue(in_array($session->getPassedTime(),[9,10,11,12]));
        $this->assertEquals('world',$session->get('hello'));
    }
    /**
     * @test
     */
    public function testToJsonTest00() {
        $_POST['lang'] = 'fr';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $s = new Session(['name' => 'session','duration' => 1]);
        $j = $s->toJSON();
        $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session",'
                .'"started_at":0,'
                .'"duration":60,'
                .'"resumed_at":0,'
                .'"passed_time":0,'
                .'"remaining_time":60,'
                .'"language":"",'
                .'"id":"'.$s->getId().'",'
                .'"is_refresh":false,'
                .'"is_persistent":true,'
                .'"status":"none",'
                .'"user":null,'
                .'"vars":{}}',$j.'');
        $s->start();
        // $j = $s->toJSON();
        // $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session",'
                .'"startedAt":'.$s->getStartedAt().','
                .'"duration":60,'
                .'"resumedAt":'.$s->getStartedAt().','
                .'"passedTime":0,'
                .'"remainingTime":60,'
                .'"language":"FR",'
                .'"id":"'.$s->getId().'",'
                .'"isRefresh":false,'
                .'"isPersistent":true,'
                .'"status":"new",'
                .'"user":null,'
                .'"vars":{}}',$s.'');
    }
    /**
     * @test
     */
    public function testToJsonTest01() {
        $_POST['lang'] = 'fr';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $s = new Session(['name' => 'session','duration' => 1]);
        $j = $s->toJSON();
        $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session",'
                .'"started_at":0,'
                .'"duration":60,'
                .'"resumed_at":0,'
                .'"passed_time":0,'
                .'"remaining_time":60,'
                .'"language":"",'
                .'"id":"'.$s->getId().'",'
                .'"is_refresh":false,'
                .'"is_persistent":true,'
                .'"status":"none",'
                .'"user":null,'
                .'"vars":{}}',$j.'');
        $s->start();
        $j = $s->toJSON();
        $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session",'
                .'"started_at":'.$s->getStartedAt().','
                .'"duration":60,'
                .'"resumed_at":'.$s->getStartedAt().','
                .'"passed_time":0,'
                .'"remaining_time":60,'
                .'"language":"FR",'
                .'"id":"'.$s->getId().'",'
                .'"is_refresh":false,'
                .'"is_persistent":true,'
                .'"status":"new",'
                .'"user":null,'
                .'"vars":{}}',$j.'');
        $_POST['lang'] = 'enx';
        $this->assertEquals('FR', $s->getLangCode(true));
        $_POST['lang'] = 'En';
        $this->assertEquals('EN', $s->getLangCode(true));
        $j = $s->toJSON();
        $j->setPropsStyle('snake');
        $this->assertEquals('{"name":"session",'
                .'"started_at":'.$s->getStartedAt().','
                .'"duration":60,'
                .'"resumed_at":'.$s->getStartedAt().','
                .'"passed_time":0,'
                .'"remaining_time":60,'
                .'"language":"EN",'
                .'"id":"'.$s->getId().'",'
                .'"is_refresh":false,'
                .'"is_persistent":true,'
                .'"status":"new",'
                .'"user":null,'
                .'"vars":{}}',$j.'');
    }
}
