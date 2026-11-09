<?php
namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\DatabaseException;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\ConflictStrategy;
use WebFiori\Framework\Session\DatabaseSessionStorage;
use WebFiori\Framework\Session\SessionDB;

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
    /**
     * Regression test for bug #428: non-atomic check-then-insert race in
     * SessionDB. Two concurrent requests carrying the same new session id both
     * pass isSessionExist() and both reach the INSERT, causing a PRIMARY KEY
     * (PK_sessions) violation — SQL Server error 2627.
     *
     * The race is reproduced deterministically with a SessionDB whose existence
     * check is stale: it reports "no row" on first look even though another
     * concurrent request has already created the row. This drives SessionDB's
     * own create branch ("if (!isSessionExist()) INSERT") straight into the
     * duplicate INSERT — exactly the losing-request scenario from the issue.
     *
     * Asserts the EXPECTED (correct) behavior: first-write session creation
     * must be idempotent / race-safe, so the collision must be swallowed and
     * NOT raise. This FAILS while the bug is present (the create throws 2627)
     * and PASSES once SessionDB catches the duplicate-key and falls through to
     * an update.
     *
     * @test
     */
    public function testConcurrentCreateIsRaceSafe() {
        $sId = 'race-session-'.uniqid();

        // Request A wins the race and creates the session row first.
        $requestA = new SessionDB('test-sessions-conn');
        $requestA->writeSessionKey($sId, 'k', 'value-a', null, ConflictStrategy::LAST_WRITE_WINS);

        // Request B has a STALE existence view: its isSessionExist() returned
        // false before A inserted, so B still believes the row is absent and
        // proceeds to the create branch. This is the losing request in the race.
        $requestB = new StaleSessionDB('test-sessions-conn');

        try {
            $requestB->writeSessionKey($sId, 'k2', 'value-b', null, ConflictStrategy::LAST_WRITE_WINS);
        } catch (DatabaseException $e) {
            $this->fail(
                'Bug #428: concurrent session creation raised a duplicate-key error '.
                '('.$e->getCode().' - '.$e->getMessage().'). '.
                'First-write session creation should be idempotent / race-safe.'
            );
        }

        // Both concurrent first-writes must survive under the single session row.
        $this->assertTrue($this->storage->getController()->isSessionExist($sId));
        $this->assertEquals('value-a', $this->storage->read($sId, 'k')['value']);
        $this->assertEquals('value-b', $this->storage->read($sId, 'k2')['value']);
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
        // Remove the connection from app config so other tests that expect
        // 'sessions-connection' to be absent are not affected by this class.
        try {
            App::getConfig()->removeDBConnection('test-sessions-conn');
        } catch (\Throwable $e) {
        }
    }
}

/**
 * Test double that simulates a losing request in the bug #428 race: its very
 * first isSessionExist() check returns a STALE "false" (as if read before a
 * concurrent request inserted the row), forcing SessionDB's create branch to
 * run against an already-existing row. All subsequent checks delegate to the
 * real implementation.
 */
class StaleSessionDB extends SessionDB {
    private bool $staleReadConsumed = false;

    public function isSessionExist(string $sId): bool {
        if (!$this->staleReadConsumed) {
            $this->staleReadConsumed = true;

            // Pretend the row does not exist yet (stale read).
            return false;
        }

        return parent::isSessionExist($sId);
    }
}
