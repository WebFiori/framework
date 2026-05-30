<?php

namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Framework\Session\DatabaseSessionStorage;

class SQLiteSessionStorageTest extends TestCase {
    protected DatabaseSessionStorage $storage;

    protected function setUp(): void {
        $dbPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_session_test_'.getmypid().'.db';
        $conn = new ConnectionInfo('sqlite', '', '', $dbPath, '');
        $conn->setName('test-sessions-conn');

        $this->storage = new DatabaseSessionStorage($conn);
        $this->storage->getController()->removeTables();
        $this->storage->getController()->createTables();
    }

    protected function tearDown(): void {
        try {
            $this->storage->getController()->removeTables();
        } catch (\Throwable $e) {
        }
        $dbPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_session_test_'.getmypid().'.db';

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
    }
    /** @test */
    public function testSaveNewSession() {
        $this->storage->save('test-id-001', 'serialized-data');
        $this->assertTrue($this->storage->getController()->isSessionExist('test-id-001'));
    }
    /** @test */
    public function testReadSession() {
        $this->storage->save('test-id-002', 'my-session-data');
        $this->assertEquals('my-session-data', $this->storage->read('test-id-002'));
    }
    /** @test */
    public function testUpdateSession() {
        $this->storage->save('test-id-003', 'original-data');
        $this->storage->save('test-id-003', 'updated-data');
        $this->assertEquals('updated-data', $this->storage->read('test-id-003'));
    }
    /** @test */
    public function testRemoveSession() {
        $this->storage->save('test-id-004', 'data');
        $this->storage->remove('test-id-004');
        $this->assertFalse($this->storage->getController()->isSessionExist('test-id-004'));
    }
    /** @test */
    public function testRemoveNonExistent() {
        $this->storage->remove('non-existent-id');
        $this->assertFalse($this->storage->getController()->isSessionExist('non-existent-id'));
    }
    /** @test */
    public function testReadNonExistent() {
        $this->assertNull($this->storage->read('non-existent-id'));
    }
    /** @test */
    public function testGCRemovesExpiredSessions() {
        $this->storage->save('expired-1', 'data1');
        $this->storage->save('expired-2', 'data2');
        $this->storage->getController()->table('sessions')->update(['last-used' => '2020-01-01 00:00:00'])->where('s-id', 'expired-1')->execute();
        $this->storage->getController()->table('sessions')->update(['last-used' => '2020-01-01 00:00:00'])->where('s-id', 'expired-2')->execute();
        $this->storage->gc('2021-01-01 00:00:00');
        $this->assertFalse($this->storage->getController()->isSessionExist('expired-1'));
        $this->assertFalse($this->storage->getController()->isSessionExist('expired-2'));
    }
    /** @test */
    public function testGCKeepsActiveSessions() {
        $this->storage->save('active-1', 'data1');
        $this->storage->gc('2020-01-01 00:00:00');
        $this->assertTrue($this->storage->getController()->isSessionExist('active-1'));
    }
    /** @test */
    public function testGCRespectsBatchLimit() {
        for ($i = 0; $i < 10; $i++) {
            $this->storage->save("expired-$i", "data-$i");
            $this->storage->getController()->table('sessions')->update(['last-used' => '2020-01-01 00:00:00'])->where('s-id', "expired-$i")->execute();
        }
        $this->storage->gc('2021-01-01 00:00:00', 3);
        $remaining = $this->storage->getController()->table('sessions')->select()->execute()->getRowsCount();
        $this->assertEquals(7, $remaining);
    }
    /** @test */
    public function testGCEmptyTable() {
        $this->storage->gc('2021-01-01 00:00:00');
        $this->assertTrue(true);
    }
    /** @test */
    public function testLargeSessionDataChunked() {
        $largeData = str_repeat('A', 3000);
        $this->storage->save('large-session', $largeData);
        $this->assertGreaterThan(1, $this->storage->getController()->getChunksCount('large-session'));
        $this->assertEquals($largeData, $this->storage->read('large-session'));
    }
    /** @test */
    public function testSessionDataShrinks() {
        $this->storage->save('shrink-session', str_repeat('B', 3000));
        $initialChunks = $this->storage->getController()->getChunksCount('shrink-session');
        $this->storage->save('shrink-session', str_repeat('C', 100));
        $this->assertLessThan($initialChunks, $this->storage->getController()->getChunksCount('shrink-session'));
        $this->assertEquals(str_repeat('C', 100), $this->storage->read('shrink-session'));
    }
    /** @test */
    public function testConcurrentSessions() {
        $this->storage->save('session-a', 'data-a');
        $this->storage->save('session-b', 'data-b');
        $this->storage->save('session-c', 'data-c');
        $this->assertEquals('data-a', $this->storage->read('session-a'));
        $this->assertEquals('data-b', $this->storage->read('session-b'));
        $this->assertEquals('data-c', $this->storage->read('session-c'));
    }
}
