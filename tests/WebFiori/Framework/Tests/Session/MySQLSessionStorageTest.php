<?php
namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\ConflictStrategy;
use WebFiori\Framework\Session\DatabaseSessionStorage;

class MySQLSessionStorageTest extends TestCase {
    protected DatabaseSessionStorage $storage;

    /** @test */
    public function testConcurrentSessions() {
        $this->storage->write('session-a', 'k', 'data-a', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->storage->write('session-b', 'k', 'data-b', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->storage->write('session-c', 'k', 'data-c', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->assertEquals('data-a', $this->storage->read('session-a', 'k')['value']);
        $this->assertEquals('data-b', $this->storage->read('session-b', 'k')['value']);
        $this->assertEquals('data-c', $this->storage->read('session-c', 'k')['value']);
    }

    /** @test */
    public function testGCEmptyTable() {
        $this->storage->gc('2021-01-01 00:00:00');
        $this->assertTrue(true);
    }

    /** @test */
    public function testGCKeepsActiveSessions() {
        $this->storage->write('active-1', 'k', 'data1', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->storage->gc('2020-01-01 00:00:00');
        $this->assertTrue($this->storage->getController()->isSessionExist('active-1'));
    }

    /** @test */
    public function testGCRemovesExpiredSessions() {
        $this->storage->write('expired-1', 'k', 'data1', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->storage->write('expired-2', 'k', 'data2', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->storage->getController()->table('sessions')->update(['last-used' => '2020-01-01 00:00:00'])->where('s-id', 'expired-1')->execute();
        $this->storage->getController()->table('sessions')->update(['last-used' => '2020-01-01 00:00:00'])->where('s-id', 'expired-2')->execute();
        $this->storage->gc('2021-01-01 00:00:00');
        $this->assertFalse($this->storage->getController()->isSessionExist('expired-1'));
        $this->assertFalse($this->storage->getController()->isSessionExist('expired-2'));
    }

    /** @test */
    public function testGCRespectsBatchLimit() {
        for ($i = 0; $i < 10; $i++) {
            $this->storage->write("expired-$i", 'k', "data-$i", null, ConflictStrategy::LAST_WRITE_WINS);
            $this->storage->getController()->table('sessions')->update(['last-used' => '2020-01-01 00:00:00'])->where('s-id', "expired-$i")->execute();
        }
        $this->storage->gc('2021-01-01 00:00:00', 3);
        $remaining = $this->storage->getController()->table('sessions')->select()->execute()->getRowsCount();
        $this->assertEquals(7, $remaining);
    }

    /** @test */
    public function testReadNonExistent() {
        $this->assertNull($this->storage->read('non-existent-id', 'anykey'));
    }

    /** @test */
    public function testReadSession() {
        $this->storage->write('test-id-002', 'k', 'my-value', null, ConflictStrategy::LAST_WRITE_WINS);
        $entry = $this->storage->read('test-id-002', 'k');
        $this->assertNotNull($entry);
        $this->assertEquals('my-value', $entry['value']);
    }

    /** @test */
    public function testRemoveNonExistent() {
        $this->storage->destroy('non-existent-id');
        $this->assertFalse($this->storage->getController()->isSessionExist('non-existent-id'));
    }

    /** @test */
    public function testRemoveSession() {
        $this->storage->write('test-id-004', 'k', 'data', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->storage->destroy('test-id-004');
        $this->assertFalse($this->storage->getController()->isSessionExist('test-id-004'));
    }

    /** @test */
    public function testSaveNewSession() {
        $this->storage->write('test-id-001', 'mykey', 'value', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->assertTrue($this->storage->getController()->isSessionExist('test-id-001'));
    }

    /** @test */
    public function testUpdateSession() {
        $this->storage->write('test-id-003', 'k', 'original', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->storage->write('test-id-003', 'k', 'updated', null, ConflictStrategy::LAST_WRITE_WINS);
        $entry = $this->storage->read('test-id-003', 'k');
        $this->assertEquals('updated', $entry['value']);
    }

    private function createConnection(): ConnectionInfo {
        $port = getenv('MYSQL_PORT') !== false ? intval(getenv('MYSQL_PORT')) : 3306;

        return new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', '127.0.0.1', $port);
    }

    protected function setUp(): void {
        try {
            $conn = $this->createConnection();
            $db = new Database($conn);
            $db->getConnection();
        } catch (\Throwable $e) {
            $this->markTestSkipped('MySQL not available: '.$e->getMessage());
        }

        $conn = $this->createConnection();
        $conn->setName('test-sessions-conn');
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->storage = new DatabaseSessionStorage('test-sessions-conn');
        $this->storage->getController()->removeTables();
        $this->storage->getController()->createTables();
    }

    protected function tearDown(): void {
        try {
            $this->storage->getController()->removeTables();
        } catch (\Throwable $e) {
        }
    }
}
