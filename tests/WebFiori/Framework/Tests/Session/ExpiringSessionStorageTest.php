<?php
namespace WebFiori\Framework\Test\Session;

require_once __DIR__.'/SessionDriversTrait.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Session\ConflictStrategy;
use WebFiori\Framework\Session\ExpiringSessionStorage;
use WebFiori\Framework\Session\InMemorySessionStorage;
use WebFiori\Framework\Session\SessionSchemaMigration;

/**
 * Contract tests for ExpiringSessionStorage, run against every built-in
 * driver. See ADR-0061.
 *
 * Drivers that need an external service (MySQL, MSSQL) are skipped when the
 * service is not reachable.
 */
class ExpiringSessionStorageTest extends TestCase {
    /**
     * @test
     * @dataProvider storageProvider
     */
    #[DataProvider('storageProvider')]
    public function testBuiltInDriversAreExpiring(string $driver) {
        $this->assertInstanceOf(ExpiringSessionStorage::class, $this->createStorage($driver));
    }

    /**
     * @test
     * @dataProvider storageProvider
     */
    #[DataProvider('storageProvider')]
    public function testDestroyClearsExpiry(string $driver) {
        $storage = $this->createStorage($driver);
        $this->write($storage, 'destroy-me');
        $storage->setExpiry('destroy-me', time() + 3600);

        $storage->destroy('destroy-me');

        $this->assertNull($storage->getExpiry('destroy-me'));
    }

    /**
     * @test
     * @dataProvider storageProvider
     */
    #[DataProvider('storageProvider')]
    public function testExpiryRoundTrip(string $driver) {
        $storage = $this->createStorage($driver);
        $this->write($storage, 'round-trip');
        $this->assertNull($storage->getExpiry('round-trip'), 'A new session has no recorded expiry.');

        $at = time() + 3600;
        $storage->setExpiry('round-trip', $at);
        $this->assertSame($at, $storage->getExpiry('round-trip'));

        $storage->setExpiry('round-trip', $at + 100);
        $this->assertSame($at + 100, $storage->getExpiry('round-trip'), 'A later call replaces the expiry.');

        $storage->setExpiry('round-trip', null);
        $this->assertNull($storage->getExpiry('round-trip'), 'Null clears the expiry.');
    }

    /**
     * Writing to a session after its expiry was recorded must not lose the
     * expiry or the data.
     *
     * @test
     * @dataProvider storageProvider
     */
    #[DataProvider('storageProvider')]
    public function testExpirySurvivesLaterWrites(string $driver) {
        $storage = $this->createStorage($driver);
        $this->write($storage, 'later-writes');
        $at = time() + 3600;
        $storage->setExpiry('later-writes', $at);

        $storage->write('later-writes', 'more', 'data', null, ConflictStrategy::LAST_WRITE_WINS);

        $this->assertSame($at, $storage->getExpiry('later-writes'));
        $this->assertEquals('data', $storage->read('later-writes', 'more')['value']);
        $this->assertEquals('v', $storage->read('later-writes', 'k')['value']);
    }

    /**
     * Sessions without a recorded expiry (created before upgrade, or with a
     * non-persistent lifetime) keep being removed by the idle threshold.
     *
     * @test
     * @dataProvider timestampStorageProvider
     */
    #[DataProvider('timestampStorageProvider')]
    public function testGcFallsBackToIdleThresholdWithoutExpiry(string $driver) {
        $storage = $this->createStorage($driver);
        $this->write($storage, 'idle');
        $this->write($storage, 'fresh');
        $this->ageSession($storage, 'idle', 5 * 3600);

        $storage->gc(date('Y-m-d H:i:s', time() - 4 * 3600));

        $this->assertFalse($this->sessionExistsInStorage($storage, 'idle'));
        $this->assertTrue($this->sessionExistsInStorage($storage, 'fresh'));
    }

    /**
     * @test
     * @dataProvider storageProvider
     */
    #[DataProvider('storageProvider')]
    public function testGcRespectsMaxCount(string $driver) {
        if ($driver === 'cache') {
            $this->markTestSkipped('The cache driver delegates expiry to cache TTLs; gc() has no count to limit.');
        }
        $storage = $this->createStorage($driver);

        foreach (['a', 'b', 'c'] as $id) {
            $this->write($storage, 'max-'.$id);
            $this->expireSession($driver, $storage, 'max-'.$id);
        }

        $storage->gc(date('Y-m-d H:i:s', time() - 86400), 2);

        $left = 0;

        foreach (['a', 'b', 'c'] as $id) {
            $left += $this->sessionExistsInStorage($storage, 'max-'.$id) ? 1 : 0;
        }
        $this->assertSame(1, $left);
    }

    /**
     * Sessions that have an expiry are removed by gc() only once it passed,
     * whatever the idle threshold says.
     *
     * @test
     * @dataProvider storageProvider
     */
    #[DataProvider('storageProvider')]
    public function testGcUsesRecordedExpiry(string $driver) {
        $storage = $this->createStorage($driver);
        $this->write($storage, 'expired');
        $this->write($storage, 'alive');
        $this->expireSession($driver, $storage, 'expired');
        $storage->setExpiry('alive', time() + 3600);

        // An idle threshold in the far future would delete every session
        // that has no expiry. 'alive' has one, so it must stay.
        $storage->gc(date('Y-m-d H:i:s', time() + 86400));

        $this->assertFalse($this->sessionExistsInStorage($storage, 'expired'), 'Expired session should be removed.');
        $this->assertTrue($this->sessionExistsInStorage($storage, 'alive'), 'Unexpired session should be kept.');
    }

    /**
     * A database whose 'sessions' table has no 'expires_at' column yet (not
     * migrated) must keep working with idle-threshold GC, and the migration
     * must add the column.
     *
     * @test
     * @dataProvider databaseStorageProvider
     */
    #[DataProvider('databaseStorageProvider')]
    public function testUnmigratedDatabaseDegradesAndMigrationAddsColumn(string $driver) {
        $storage = $this->createStorage($driver);

        // Turn the table into the pre-upgrade shape.
        $db = $storage->getController();
        $dropIndex = $driver === 'sqlite'
            ? 'DROP INDEX IF EXISTS idx_sessions_expires_at'
            : 'DROP INDEX idx_sessions_expires_at ON sessions';
        $db->raw($dropIndex)->execute();
        $db->raw('ALTER TABLE sessions DROP COLUMN expires_at')->execute();

        $this->write($storage, 'old-idle');
        $this->write($storage, 'old-fresh');
        $this->ageSession($storage, 'old-idle', 5 * 3600);

        $storage->setExpiry('old-fresh', time() + 3600);
        $this->assertNull($storage->getExpiry('old-fresh'), 'Without the column no expiry can be recorded.');

        $storage->gc(date('Y-m-d H:i:s', time() - 4 * 3600));
        $this->assertFalse($this->sessionExistsInStorage($storage, 'old-idle'));
        $this->assertTrue($this->sessionExistsInStorage($storage, 'old-fresh'));

        SessionSchemaMigration::run($this->dbConnection);

        $at = time() + 3600;
        $storage->setExpiry('old-fresh', $at);
        $this->assertSame($at, $storage->getExpiry('old-fresh'), 'After the migration expiry can be recorded.');

        // Running the migration twice is harmless.
        SessionSchemaMigration::run($this->dbConnection);
        $this->assertSame($at, $storage->getExpiry('old-fresh'));
    }

    /**
     * Makes a session's recorded expiry a time in the past. The cache driver
     * enforces expiry through TTLs, so the test waits for the 1 second TTL to
     * lapse.
     */
    private function expireSession(string $driver, ExpiringSessionStorage $storage, string $sessionId): void {
        $storage->setExpiry($sessionId, time() - 10);

        if ($driver === 'cache') {
            sleep(2);
        }
    }

    private function write(ExpiringSessionStorage $storage, string $sessionId): void {
        $storage->write($sessionId, 'k', 'v', null, ConflictStrategy::LAST_WRITE_WINS);
    }
    use SessionDriversTrait;

    protected function setUp(): void {
        parent::setUp();
        InMemorySessionStorage::reset();
    }

    protected function tearDown(): void {
        $this->cleanupDrivers();
        parent::tearDown();
    }
}
