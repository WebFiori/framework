<?php
namespace webfiori\framework\test\session;

use PHPUnit\Framework\TestCase;
use webfiori\database\ConnectionInfo;
use webfiori\database\DatabaseException;
use webfiori\framework\App;
use webfiori\framework\exceptions\SessionException;
use webfiori\framework\session\DatabaseSessionStorage;
use webfiori\framework\session\SessionsManager;
use webfiori\framework\session\SessionStatus;

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
        SessionsManager::reset();
        $this->assertEquals(0, count(SessionsManager::getSessions()));
        $this->assertNull(SessionsManager::getActiveSession());
        $this->assertNull(SessionsManager::get('xyz'));
        $this->assertFalse(SessionsManager::remove('xyz'));
        $this->assertFalse(SessionsManager::set('xyz','hello'));
        $this->assertNull(SessionsManager::pull('xyz'));
        
        $this->assertFalse(SessionsManager::hasCookie());
        SessionsManager::start('hello');
        $this->assertFalse(SessionsManager::hasCookie());
        $this->assertEquals(1, count(SessionsManager::getSessions()));
        $activeSesstion = SessionsManager::getActiveSession();
        $this->assertFalse($activeSesstion->isRefresh());
        $this->assertTrue($activeSesstion->isRunning());

        $this->assertNotNull($activeSesstion);
        $this->assertEquals('hello', $activeSesstion->getName());
        $this->assertEquals(7200, $activeSesstion->getDuration());
        $this->assertEquals(SessionStatus::NEW, $activeSesstion->getStatus());

        SessionsManager::set('var-1', 'Good');
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
        $this->assertEquals(SessionStatus::PAUSED, $activeSesstion->getStatus());

        $activeSesstion->start();
        $this->assertTrue($activeSesstion->isRunning());

        $this->assertNotNull(SessionsManager::getActiveSession());
        $this->assertEquals(SessionStatus::RESUMED, $activeSesstion->getStatus());
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
        $this->assertEquals(SessionStatus::PAUSED, $activeSesstion->getStatus());
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
        $this->assertEquals(SessionStatus::PAUSED, $active2->getStatus());

        SessionsManager::start('another-one');
        $this->assertEquals(SessionStatus::PAUSED, $activeSesstion->getStatus());
        $this->assertEquals(SessionStatus::RESUMED, $active2->getStatus());
        $this->assertEquals('I m super.', SessionsManager::get('super-var'));
        SessionsManager::destroy();
        $this->assertNull(SessionsManager::getActiveSession());
        $this->assertEquals(SessionStatus::KILLED, $active2->getStatus());
        SessionsManager::validateStorage();
        $active2->start();
        $this->assertEquals(SessionStatus::NEW, $active2->getStatus());
        $this->assertNotNull(SessionsManager::getActiveSession());
        $this->assertEquals(300, SessionsManager::getActiveSession()->getDuration());
        $this->assertNull(SessionsManager::get('super-var'));
        SessionsManager::validateStorage();
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
        $this->assertEquals(SessionStatus::RESUMED, SessionsManager::getActiveSession()->getStatus());
        $oldId = SessionsManager::getActiveSession()->getId();
        $newId = SessionsManager::newId();
        $this->assertNotEquals($oldId, $newId);
        $this->assertNotEquals($oldId, SessionsManager::getActiveSession()->getId());
        $this->assertEquals($newId, SessionsManager::getActiveSession()->getId());
    }
    /**
     * @test
     * @depends testClose00
     */
    public function testCookiesHeaders() {
        SessionsManager::reset();
        SessionsManager::start('hello');
        $sessions = SessionsManager::getSessions();
        $this->assertEquals([
            'hello='.$sessions[0]->getId().'; expires='.$sessions[0]->getCookie()->getLifetime().'; domain=127.0.0.1; path=/; Secure; HttpOnly; SameSite=Lax'
            ], SessionsManager::getCookiesHeaders());
    }
    /**
     * @test
     */
    public function testDatabaseSession00() {
        $this->expectException(SessionException::class);
        $this->expectExceptionMessage("Connection 'sessions-connection' was not found in application configuration.");
        SessionsManager::reset();
        SessionsManager::setStorage(new DatabaseSessionStorage());
    }
    /**
     * @test
     */
    public function testDatabaseSession01() {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("208 - [Microsoft][ODBC Driver 17 for SQL Server][SQL Server]Invalid object name 'session_data'.");
        $conn = new ConnectionInfo('mssql', 'sa', '1234567890@Eu', 'testing_db', 'localhost');
        $conn->setName('sessions-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);
        SessionsManager::setStorage(new DatabaseSessionStorage());
        SessionsManager::getStorage()->getController()->removeTables();
        SessionsManager::start('hello');
    }
    /**
     * @test
     * @depends testInitSessionsDb
     */
    public function testDatabaseSession02() {
        $conn = new ConnectionInfo('mssql', 'sa', '1234567890@Eu', 'testing_db', 'localhost');
        $conn->setName('sessions-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);
        SessionsManager::reset();
        $sto = new DatabaseSessionStorage();
        SessionsManager::setStorage($sto);

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
        $this->assertEquals(SessionStatus::NEW, $activeSesstion->getStatus());

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
        $this->assertEquals(SessionStatus::PAUSED, $activeSesstion->getStatus());

        $activeSesstion->start();
        $this->assertTrue($activeSesstion->isRunning());

        $this->assertNotNull(SessionsManager::getActiveSession());
        $this->assertEquals(SessionStatus::RESUMED, $activeSesstion->getStatus());
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
        $this->assertEquals(SessionStatus::PAUSED, $activeSesstion->getStatus());
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
        $this->assertEquals(SessionStatus::PAUSED, $active2->getStatus());

        SessionsManager::start('another-one');
        $this->assertEquals(SessionStatus::PAUSED, $activeSesstion->getStatus());
        $this->assertEquals(SessionStatus::RESUMED, $active2->getStatus());
        $this->assertEquals('I m super.', SessionsManager::get('super-var'));
        SessionsManager::destroy();
        $this->assertNull(SessionsManager::getActiveSession());
        $this->assertEquals(SessionStatus::KILLED, $active2->getStatus());
        $active2->start();
        $this->assertEquals(SessionStatus::NEW, $active2->getStatus());
        $this->assertNotNull(SessionsManager::getActiveSession());
        $this->assertEquals(300, SessionsManager::getActiveSession()->getDuration());
        $this->assertNull(SessionsManager::get('super-var'));
        SessionsManager::validateStorage();
    }
    /**
     * @test
     * @depends testDatabaseSession02
     * @depends testCloseDb00
     */
    public function testDropDbTables00() {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage("208 - [Microsoft][ODBC Driver 17 for SQL Server][SQL Server]Invalid object name 'session_data'.");
        $conn = new ConnectionInfo('mssql', 'sa', '1234567890@Eu', 'testing_db', 'localhost');
        $conn->setName('sessions-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);
        SessionsManager::reset();
        $sto = new DatabaseSessionStorage();
        $sto->dropTables();
        SessionsManager::setStorage($sto);
        SessionsManager::start('hello');
    }
    /**
     * @test
     */
    public function testGetSessionIDFromRequest() {
        $this->assertFalse(SessionsManager::getSessionIDFromRequest('my-s'));
        putenv('REQUEST_METHOD=GET');
        $_GET['my-s'] = 'super';
        $this->assertEquals('super', SessionsManager::getSessionIDFromRequest('my-s'));

        $_POST['my-s'] = 'xyz';
        putenv('REQUEST_METHOD=POST');
        $this->assertEquals('xyz', SessionsManager::getSessionIDFromRequest('my-s'));
    }
    /**
     * @test
     * @depends testDatabaseSession01
     */
    public function testInitSessionsDb() {
        $conn = new ConnectionInfo('mssql', 'sa', '1234567890@Eu', 'testing_db', 'localhost');
        $conn->setName('sessions-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);
        SessionsManager::reset();
        $sto = new DatabaseSessionStorage();
        $sto->getController()->createTables()->execute();
        $sto->getController()->clear();
        $sto->getController()->table('session_data')->selectCount()->execute();
        $sto->getController()->table('sessions')->selectCount()->execute();
        $this->assertTrue(true);
    }
    /**
     * @test
     * @depends testInitSessionsDb
     */
    public function testDbSessions00() {
        SessionsManager::start('hello-x', [
            
        ]);
        $activeSesstion = SessionsManager::getActiveSession();
        
        $this->assertFalse($activeSesstion->isRefresh());
        $this->assertTrue($activeSesstion->isRunning());

        $this->assertNotNull($activeSesstion);
        $this->assertEquals('hello-x', $activeSesstion->getName());
        $this->assertEquals(7200, $activeSesstion->getDuration());
        $this->assertEquals(SessionStatus::NEW, $activeSesstion->getStatus());

        SessionsManager::set('var-1', 'Good');
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
        $this->assertEquals(SessionStatus::PAUSED, $activeSesstion->getStatus());

        $activeSesstion->start();
        $this->assertTrue($activeSesstion->isRunning());

        $this->assertNotNull(SessionsManager::getActiveSession());
        $this->assertEquals(SessionStatus::RESUMED, $activeSesstion->getStatus());
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
        $this->assertEquals(SessionStatus::PAUSED, $activeSesstion->getStatus());
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
        $this->assertEquals(SessionStatus::PAUSED, $active2->getStatus());

        SessionsManager::start('another-one');
        $this->assertEquals(SessionStatus::PAUSED, $activeSesstion->getStatus());
        $this->assertEquals(SessionStatus::RESUMED, $active2->getStatus());
        $this->assertEquals('I m super.', SessionsManager::get('super-var'));
        SessionsManager::destroy();
        $this->assertNull(SessionsManager::getActiveSession());
        $this->assertEquals(SessionStatus::KILLED, $active2->getStatus());
        SessionsManager::validateStorage();
        $active2->start();
        $this->assertEquals(SessionStatus::NEW, $active2->getStatus());
        $this->assertNotNull(SessionsManager::getActiveSession());
        $this->assertEquals(300, SessionsManager::getActiveSession()->getDuration());
        $this->assertNull(SessionsManager::get('super-var'));
        SessionsManager::validateStorage();
    }
    /**
     * @test
     * @depends testInitSessionsDb
     */
    public function testCloseDb00() {
        SessionsManager::pauseAll();
        $this->assertNull(SessionsManager::getActiveSession());
        SessionsManager::start('xyz');
        $this->assertNotNull(SessionsManager::getActiveSession());
        SessionsManager::close();
        $this->assertNull(SessionsManager::getActiveSession());
        SessionsManager::start('xyz');
        $this->assertEquals(SessionStatus::RESUMED, SessionsManager::getActiveSession()->getStatus());
        $oldId = SessionsManager::getActiveSession()->getId();
        $newId = SessionsManager::newId();
        $this->assertNotEquals($oldId, $newId);
        $this->assertNotEquals($oldId, SessionsManager::getActiveSession()->getId());
        $this->assertEquals($newId, SessionsManager::getActiveSession()->getId());
    }
}





