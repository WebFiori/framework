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
        $this->assertNull(SessionsManager::getActiveSesstion());
        $this->assertNull(SessionsManager::get('xyz'));
        $this->assertFalse(SessionsManager::remove('xyz'));
        $this->assertNull(SessionsManager::pull('xyz'));
        
        SessionsManager::start('hello');
        
        $activeSesstion = SessionsManager::getActiveSesstion();
        $this->assertFalse($activeSesstion->isRefresh());
        $this->assertTrue($activeSesstion->isRunning());
        
        $this->assertNotNull($activeSesstion);
        $this->assertEquals('hello', $activeSesstion->getName());
        $this->assertEquals(120, $activeSesstion->getDuration());
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
        
        $this->assertNull(SessionsManager::getActiveSesstion());
        $this->assertEquals(Session::STATUS_PAUSED, $activeSesstion->getStatus());
        
        $activeSesstion->start();
        $this->assertTrue($activeSesstion->isRunning());
        
        $this->assertNotNull(SessionsManager::getActiveSesstion());
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
        $active2 = SessionsManager::getActiveSesstion();
        $this->assertEquals(5, $active2->getDuration());
        $this->assertTrue($active2->isRefresh());
        $this->assertTrue($active2->isRunning());
        $this->assertNull(SessionsManager::get('var-3 '));
        $this->assertNull(SessionsManager::get(' var-4 '));
        
        $active2->set('super-var', 'I m super.');
        $this->assertEquals('I m super.', SessionsManager::get('super-var'));
        
        $active2->close();
        $this->assertNull(SessionsManager::getActiveSesstion());
        $activeSesstion->start();
        $this->assertNotNull(SessionsManager::getActiveSesstion());
        $this->assertNull(SessionsManager::get('super-var'));
        $this->assertEquals(120, SessionsManager::getActiveSesstion()->getDuration());
        $this->assertEquals(Session::STATUS_PAUSED, $active2->getStatus());
        
        SessionsManager::start('another-one');
        $this->assertEquals(Session::STATUS_PAUSED, $activeSesstion->getStatus());
        $this->assertEquals(Session::STATUS_RESUMED, $active2->getStatus());
        $this->assertEquals('I m super.', SessionsManager::get('super-var'));
        SessionsManager::destroy();
        $this->assertNull(SessionsManager::getActiveSesstion());
        $this->assertEquals(Session::STATUS_KILLED, $active2->getStatus());
        $active2->start();
        $this->assertEquals(Session::STATUS_NEW, $active2->getStatus());
        $this->assertNotNull(SessionsManager::getActiveSesstion());
        $this->assertEquals(5, SessionsManager::getActiveSesstion()->getDuration());
        $this->assertNull(SessionsManager::get('super-var'));
        SessionsManager::validateStorage();
    }
    
}
