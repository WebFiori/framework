<?php
namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\ConflictStrategy;
use WebFiori\Framework\Session\DatabaseSessionStorage;

class MSSQLSessionStorageTest extends TestCase {
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
            $this->storage->write("expired-$i", "k", "data-$i", null, ConflictStrategy::LAST_WRITE_WINS);
            $this->storage->getController()->table('sessions')->update(['last-used' => '2020-01-01 00:00:00'])->where('s-id', "expired-$i")->execute();
        }
        $this->storage->gc('2021-01-01 00:00:00', 3);
        $remaining = $this->storage->getController()->table('sessions')->select()->execute()->getRowsCount();
        $this->assertEquals(7, $remaining);
    }
    /** @test */
    public function testLargeSessionDataChunked() {
        $largeData = str_repeat('A', 3000);
        $this->storage->write('large-session', 'k', $largeData, null, ConflictStrategy::LAST_WRITE_WINS);
        // Per-key storage handles large values without chunking.
        $entry = $this->storage->read('large-session', 'k');
        $this->assertNotNull($entry);
        $this->assertEquals($largeData, $entry['value']);
    }
    /** @test */
    public function testReadNonExistent() {
        $this->assertNull($this->storage->read('non-existent-id', 'k'));
    }
    /** @test */
    public function testReadSession() {
        $this->storage->write('test-id-002', 'k', 'my-session-data', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->assertEquals('my-session-data', $this->storage->read('test-id-002', 'k')['value']);
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
        $this->storage->write('test-id-001', 'k', 'serialized-data', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->assertTrue($this->storage->getController()->isSessionExist('test-id-001'));
    }
    /** @test */
    public function testSessionDataShrinks() {
        $this->storage->write('shrink-session', 'k', str_repeat('B', 3000), null, ConflictStrategy::LAST_WRITE_WINS);
        $this->storage->write('shrink-session', 'k', str_repeat('C', 100), null, ConflictStrategy::LAST_WRITE_WINS);
        $entry = $this->storage->read('shrink-session', 'k');
        $this->assertEquals(str_repeat('C', 100), $entry['value']);
    }
    /** @test */
    public function testUpdateSession() {
        $this->storage->write('test-id-003', 'k', 'original-data', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->storage->write('test-id-003', 'k', 'updated-data', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->assertEquals('updated-data', $this->storage->read('test-id-003', 'k')['value']);
    }

    private function createConnection(): ConnectionInfo {
        return new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
    }

    protected function setUp(): void {
        try {
            $conn = $this->createConnection();
            $conn->setName('test-sessions-conn');
            App::getConfig()->addOrUpdateDBConnection($conn);
            $this->storage = new DatabaseSessionStorage('test-sessions-conn');
            $this->storage->getController()->removeTables();
            $this->storage->getController()->createTables();
            $this->storage->write('mssql-probe', 'k', 'probe', null, ConflictStrategy::LAST_WRITE_WINS);
            $this->storage->destroy('mssql-probe');
        } catch (\Throwable $e) {
            $this->markTestSkipped('MSSQL not available: '.$e->getMessage());
        }
    }

    protected function tearDown(): void {
        try {
            $this->storage->getController()->removeTables();
        } catch (\Throwable $e) {
        }
    }
}
