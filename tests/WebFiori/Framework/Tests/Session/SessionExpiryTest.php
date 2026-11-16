<?php
namespace WebFiori\Framework\Test\Session;

require_once __DIR__.'/SessionDriversTrait.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Session\ConflictStrategy;
use WebFiori\Framework\Session\InMemorySessionStorage;
use WebFiori\Framework\Session\Session;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\Session\SessionStatus;
use WebFiori\Framework\Session\SessionStorage;

/**
 * Tests how Session computes the expiry it hands to ExpiringSessionStorage
 * (ADR-0061), against every built-in driver.
 *
 * Drivers that need an external service (MySQL, MSSQL) are skipped when the
 * service is not reachable.
 */
class SessionExpiryTest extends TestCase {
    /**
     * A session with refresh expires one full duration after its latest write
     * of metadata, not after it started.
     *
     * @test
     */
    #[DataProvider('storageProvider')]
    public function testNewRefreshSessionExpiresOneDurationFromNow(string $driver) {
        $storage = $this->createStorage($driver);
        SessionsManager::setStorage($storage);

        $s = new Session(['name' => 'exp-refresh-'.$driver, 'duration' => 30, 'refresh' => true]);
        $s->start();

        $this->assertEqualsWithDelta(time() + 1800, $storage->getExpiry($s->getId()), 5);
    }

    /**
     * A session without refresh expires one duration after it started.
     *
     * @test
     */
    #[DataProvider('storageProvider')]
    public function testNewSessionExpiresOneDurationAfterStart(string $driver) {
        $storage = $this->createStorage($driver);
        SessionsManager::setStorage($storage);

        $s = new Session(['name' => 'exp-new-'.$driver, 'duration' => 30]);
        $s->start();

        $this->assertEqualsWithDelta($s->getStartedAt() + 1800, $storage->getExpiry($s->getId()), 2);
    }

    /**
     * A session with duration 0 has no lifetime to enforce, so no expiry is
     * recorded and it stays subject to the idle threshold.
     *
     * @test
     */
    #[DataProvider('storageProvider')]
    public function testNonPersistentSessionHasNoExpiry(string $driver) {
        $storage = $this->createStorage($driver);
        SessionsManager::setStorage($storage);

        $s = new Session(['name' => 'exp-none-'.$driver, 'duration' => 30]);
        $s->start();
        $this->assertNotNull($storage->getExpiry($s->getId()));

        $s->setDuration(0);

        $this->assertNull($storage->getExpiry($s->getId()));
    }

    /**
     * A session without refresh keeps its original expiry when resumed.
     *
     * @test
     */
    #[DataProvider('storageProvider')]
    public function testResumeKeepsExpiryOfNonRefreshSession(string $driver) {
        $storage = $this->createStorage($driver);
        SessionsManager::setStorage($storage);
        $name = 'exp-keep-'.$driver;

        $s = new Session(['name' => $name, 'duration' => 30]);
        $s->start();
        $sid = $s->getId();
        $before = $storage->getExpiry($sid);

        (new Session(['name' => $name, 'session-id' => $sid]))->start();

        $this->assertSame($before, $storage->getExpiry($sid));
    }

    /**
     * A session created before expiry tracking existed has no recorded
     * expiry. Resuming it records one.
     *
     * @test
     */
    #[DataProvider('storageProvider')]
    public function testResumeRecordsMissingExpiry(string $driver) {
        $storage = $this->createStorage($driver);
        SessionsManager::setStorage($storage);

        $s = new Session(['name' => 'exp-legacy-'.$driver, 'duration' => 30]);
        $s->start();
        $sid = $s->getId();
        $storage->setExpiry($sid, null);

        $r = new Session(['name' => 'exp-legacy-'.$driver, 'session-id' => $sid]);
        $r->start();

        $this->assertEqualsWithDelta($r->getStartedAt() + 1800, $storage->getExpiry($sid), 2);
    }

    /**
     * Resuming a session with refresh enabled moves its expiry forward, but
     * only when it drifted by more than a minute, to avoid a write on every
     * request.
     *
     * @test
     */
    #[DataProvider('storageProvider')]
    public function testResumeRefreshesExpiryOnlyWhenDriftExceedsOneMinute(string $driver) {
        $storage = $this->createStorage($driver);
        SessionsManager::setStorage($storage);
        $name = 'exp-throttle-'.$driver;

        $s = new Session(['name' => $name, 'duration' => 30, 'refresh' => true]);
        $s->start();
        $sid = $s->getId();

        // 20 seconds of drift: below the threshold, the stored value is kept.
        $recent = time() + 1800 - 20;
        $storage->setExpiry($sid, $recent);
        (new Session(['name' => $name, 'session-id' => $sid]))->start();
        $this->assertSame($recent, $storage->getExpiry($sid));

        // 10 minutes of drift: the stored value moves forward.
        $storage->setExpiry($sid, time() + 1800 - 600);
        (new Session(['name' => $name, 'session-id' => $sid]))->start();
        $this->assertEqualsWithDelta(time() + 1800, $storage->getExpiry($sid), 5);
    }

    /**
     * setDuration() and setIsRefresh() on a running session update the
     * recorded expiry (issue #431).
     *
     * @test
     */
    #[DataProvider('storageProvider')]
    public function testSetDurationAndRefreshUpdateRecordedExpiry(string $driver) {
        $storage = $this->createStorage($driver);
        SessionsManager::setStorage($storage);

        $s = new Session(['name' => 'exp-set-'.$driver, 'duration' => 30]);
        $s->start();
        $sid = $s->getId();

        $s->setDuration(60);
        $this->assertEqualsWithDelta($s->getStartedAt() + 3600, $storage->getExpiry($sid), 2);

        $s->setIsRefresh(true);
        $this->assertEqualsWithDelta(time() + 3600, $storage->getExpiry($sid), 5);
    }

    /**
     * A session whose storage does not implement ExpiringSessionStorage must
     * keep working: nothing calls the expiry methods.
     *
     * @test
     */
    public function testStorageWithoutExpirySupportStillWorks() {
        $inner = new InMemorySessionStorage();
        $plain = new class($inner) implements SessionStorage
        {
            public function __construct(private SessionStorage $inner) {
            }
            public function destroy(string $sessionId): void {
                $this->inner->destroy($sessionId);
            }
            public function gc(string $olderThan, int $maxCount = 0): void {
                $this->inner->gc($olderThan, $maxCount);
            }
            public function read(string $sessionId, string $key): ?array {
                return $this->inner->read($sessionId, $key);
            }
            public function readAll(string $sessionId): array {
                return $this->inner->readAll($sessionId);
            }
            public function remove(string $sessionId, string $key): void {
                $this->inner->remove($sessionId, $key);
            }
            public function write(string $sessionId, string $key, mixed $value, string|int|null $expectedVersion, ConflictStrategy $strategy): string|int {
                return $this->inner->write($sessionId, $key, $value, $expectedVersion, $strategy);
            }
        };
        SessionsManager::setStorage($plain);

        $s = new Session(['name' => 'plain-storage', 'duration' => 60, 'refresh' => true]);
        $s->start();
        $s->set('k', 'v');
        $s->setDuration(120);
        $s->close();

        $r = new Session(['name' => 'plain-storage', 'session-id' => $s->getId()]);
        $r->start();
        $this->assertEquals(SessionStatus::RESUMED, $r->getStatus());
        $this->assertEquals('v', $r->get('k'));
        $this->assertEquals(7200, $r->getDuration());
    }
    use SessionDriversTrait;

    protected function setUp(): void {
        parent::setUp();
        InMemorySessionStorage::reset();
        SessionsManager::reset();
    }

    protected function tearDown(): void {
        $this->cleanupDrivers();
        SessionsManager::reset();
        SessionsManager::setStorage(new InMemorySessionStorage());
        parent::tearDown();
    }
}
