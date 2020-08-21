<?php
use PHPUnit\Framework\TestCase;
use webfiori\entity\session\SessionsManager;
use webfiori\entity\session\Session;

/**
 * Description of SessionsManagerTest
 *
 * @author Ibrahim
 */
class SessionsManagerTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $this->assertEquals(0, count(SessionsManager::getSessions()));
        $this->assertNull(SessionsManager::getActiveSession());
        $this->assertNull(SessionsManager::get('xyz'));
        $this->assertFalse(SessionsManager::remove('xyz'));
        $this->assertFalse(SessionsManager::set('xyz','hello'));
        $this->assertNull(SessionsManager::pull('xyz'));
        
        SessionsManager::start('hello');
        $this->assertEquals(1, count(SessionsManager::getSessions()));
        $activeSesstion = SessionsManager::getActiveSession();
        $this->assertFalse($activeSesstion->isRefresh());
        $this->assertTrue($activeSesstion->isRunning());
        
        $this->assertNotNull($activeSesstion);
        $this->assertEquals('hello', $activeSesstion->getName());
        $this->assertEquals(7200, $activeSesstion->getDuration());
        $this->assertEquals(Session::STATUS_NEW, $activeSesstion->getStatus());
        
        $activeSesstion->set('var-1', 'Good');
        $activeSesstion->set('var-2', 'Bad');
        $activeSesstion->set('var-3', 'Average');
        $activeSesstion->set('var-4', 'Almost Good');
        $this->assertEquals('Good', SessionsManager::get('var-1 '));
        $this->assertEquals('Bad', SessionsManager::get(' var-2 '));
        $this->assertEquals('Average', SessionsManager::get('var-3 '));
        $this->assertEquals('Almost Good', SessionsManager::get('var-4 '));
        
        SessionsManager::pauseAll();
        $this->assertFalse($activeSesstion->isRunning());
        $this->assertNull(SessionsManager::get('var-1 '));
        $this->assertNull(SessionsManager::get(' var-2 '));
        $this->assertNull(SessionsManager::get('var-3 '));
        $this->assertNull(SessionsManager::get('var-4 '));
        
        $this->assertNull(SessionsManager::getActiveSession());
        $this->assertEquals(Session::STATUS_PAUSED, $activeSesstion->getStatus());
        
        $activeSesstion->start();
        $this->assertTrue($activeSesstion->isRunning());
        
        $this->assertNotNull(SessionsManager::getActiveSession());
        $this->assertEquals(Session::STATUS_RESUMED, $activeSesstion->getStatus());
        $this->assertEquals('Good', SessionsManager::get('var-1 '));
        $this->assertEquals('Bad', SessionsManager::get(' var-2 '));
        $this->assertEquals('Average', SessionsManager::get('var-3 '));
        $this->assertEquals('Almost Good', SessionsManager::get('var-4 '));
        
        $this->assertEquals('Good', SessionsManager::pull('var-1 '));
        $this->assertNull(SessionsManager::pull('var-1 '));
        $this->assertTrue(SessionsManager::remove('var-2'));
        $this->assertFalse(SessionsManager::remove('var-2'));
        $this->assertNull(SessionsManager::get('var-1 '));
        $this->assertNull(SessionsManager::get(' var-2 '));
        
        //Start New Sesstion
        SessionsManager::start('another-one', [
            'duration' => 5,
            'refresh' => true
        ]);
        $this->assertFalse($activeSesstion->isRunning());
        $this->assertEquals(Session::STATUS_PAUSED, $activeSesstion->getStatus());
        $active2 = SessionsManager::getActiveSession();
        $this->assertEquals(300, $active2->getDuration());
        $this->assertTrue($active2->isRefresh());
        $this->assertTrue($active2->isRunning());
        $this->assertNull(SessionsManager::get('var-3 '));
        $this->assertNull(SessionsManager::get(' var-4 '));
        
        $active2->set('super-var', 'I m super.');
        $this->assertEquals('I m super.', SessionsManager::get('super-var'));
        
        $active2->close();
        $this->assertNull(SessionsManager::getActiveSession());
        $activeSesstion->start();
        $this->assertNotNull(SessionsManager::getActiveSession());
        $this->assertNull(SessionsManager::get('super-var'));
        $this->assertEquals(7200, SessionsManager::getActiveSession()->getDuration());
        $this->assertEquals(Session::STATUS_PAUSED, $active2->getStatus());
        
        SessionsManager::start('another-one');
        $this->assertEquals(Session::STATUS_PAUSED, $activeSesstion->getStatus());
        $this->assertEquals(Session::STATUS_RESUMED, $active2->getStatus());
        $this->assertEquals('I m super.', SessionsManager::get('super-var'));
        SessionsManager::destroy();
        $this->assertNull(SessionsManager::getActiveSession());
        $this->assertEquals(Session::STATUS_KILLED, $active2->getStatus());
        $active2->start();
        $this->assertEquals(Session::STATUS_NEW, $active2->getStatus());
        $this->assertNotNull(SessionsManager::getActiveSession());
        $this->assertEquals(300, SessionsManager::getActiveSession()->getDuration());
        $this->assertNull(SessionsManager::get('super-var'));
        SessionsManager::validateStorage();
    }
    /**
     * @test
     */
    public function testGetSessionIDFromRequest() {
        $this->assertFalse(SessionsManager::getSessionIDFromRequest('my-s'));
        $_POST['my-s'] = 'xyz';
        $this->assertEquals('xyz', SessionsManager::getSessionIDFromRequest('my-s'));
        unset($_POST['my-s']);
        $_GET['my-s'] = 'super';
        $this->assertEquals('super', SessionsManager::getSessionIDFromRequest('my-s'));
    }
    /**
     * @test
     */
    public function testClose00() {
        SessionsManager::pauseAll();
        $this->assertNull(SessionsManager::getActiveSession());
        SessionsManager::start('xyz');
        $this->assertNotNull(SessionsManager::getActiveSession());
        SessionsManager::close();
        $this->assertNull(SessionsManager::getActiveSession());
        SessionsManager::start('xyz');
        $this->assertEquals(Session::STATUS_RESUMED, SessionsManager::getActiveSession()->getStatus());
        $oldId = SessionsManager::getActiveSession()->getId();
        SessionsManager::newId();
        $this->assertNotEquals($oldId, SessionsManager::getActiveSession()->getId());
    }
    /**
     * @test
     * @depends testClose00
     */
    public function testCookiesHeaders() {
        $sessions = SessionsManager::getSessions();
        $this->assertEquals([
            'hello='.$sessions[0]->getId().'; expires='.date(DATE_COOKIE, $sessions[0]->getCookieParams()['expires']).'; path=/; Secure; HttpOnly; SameSite=Lax',
            'another-one='.$sessions[1]->getId().'; expires='.date(DATE_COOKIE, $sessions[1]->getCookieParams()['expires']).'; path=/; Secure; HttpOnly; SameSite=Lax',
            'xyz='.$sessions[2]->getId().'; expires='.date(DATE_COOKIE, $sessions[2]->getCookieParams()['expires']).'; path=/; Secure; HttpOnly; SameSite=Lax',
        ], SessionsManager::getCookiesHeaders());
    }
}
