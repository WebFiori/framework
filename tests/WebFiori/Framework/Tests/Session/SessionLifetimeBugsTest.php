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

/**
 * Reproduction tests for the session lifetime and garbage-collection issues
 * #431 to #435. Each test asserts the EXPECTED behavior, so it fails while the
 * reported bug exists and passes once it is fixed.
 *
 * Drivers that need an external service (MySQL, MSSQL) are skipped when the
 * service is not reachable.
 */
class SessionLifetimeBugsTest extends TestCase {
    /**
     * Issue #431: setDuration() and setIsRefresh() on a running session must
     * reach storage without an explicit close(), as in a normal request where
     * StartSessionMiddleware::afterSend() only calls validateStorage().
     *
     * @test
     */
    #[DataProvider('storageProvider')]
    public function testIssue431DurationAndRefreshArePersisted(string $driver) {
        $storage = $this->createStorage($driver);
        SessionsManager::reset();
        SessionsManager::setStorage($storage);
        $name = 'i431-'.$driver;

        SessionsManager::start($name);
        $s = SessionsManager::getActiveSession();
        $sid = $s->getId();
        $s->setDuration(10080);
        $s->setIsRefresh(true);
        SessionsManager::validateStorage();

        $meta = $storage->read($sid, '_meta')['value'];
        $this->assertEquals(10080, $meta['lifeTime'], "Issue #431: lifeTime was not persisted ($driver).");
        $this->assertTrue($meta['isRef'], "Issue #431: isRef was not persisted ($driver).");

        $next = new Session(['name' => $name, 'session-id' => $sid]);
        $next->start();
        $this->assertEquals(604800, $next->getDuration(), "Issue #431: resumed session lost its duration ($driver).");
        $this->assertTrue($next->isRefresh(), "Issue #431: resumed session lost its refresh flag ($driver).");
    }

    /**
     * Issue #432: GC must not delete a session that is idle for longer than
     * the default threshold (4 hours) but whose own lifetime (7 days) has not
     * expired.
     *
     * @test
     */
    #[DataProvider('timestampStorageProvider')]
    public function testIssue432GcRespectsPerSessionLifetime(string $driver) {
        $storage = $this->createStorage($driver);
        SessionsManager::setStorage($storage);

        $s = new Session(['name' => 'i432-'.$driver, 'duration' => 10080, 'refresh' => true]);
        $s->start();
        $s->set('k', 'v');
        $sid = $s->getId();
        $s->close();

        $this->ageSession($storage, $sid, 5 * 3600);

        $resumed = new Session(['name' => 'i432-'.$driver, 'session-id' => $sid]);
        $resumed->start();
        $this->assertGreaterThan(0, $resumed->getRemainingTime(), 'Precondition: session lifetime has not expired.');

        // Age it again: resuming may legitimately update the last-use time (#433).
        $this->ageSession($storage, $sid, 5 * 3600);
        $storage->gc(SessionsManager::getGCTime());

        $this->assertTrue(
            $this->sessionExistsInStorage($storage, $sid),
            "Issue #432: GC deleted a session whose 7 day lifetime has not expired ($driver)."
        );
    }

    /**
     * Issue #433: an active session with refresh enabled must not be treated
     * as idle. Resuming it must extend its stored expiry, so garbage
     * collection keeps it.
     *
     * The original report was about the last-use timestamp. With
     * ExpiringSessionStorage (ADR-0061) garbage collection no longer depends
     * on that timestamp for sessions that have a recorded expiry, so the test
     * checks the expiry that GC actually uses.
     *
     * @test
     */
    #[DataProvider('storageProvider')]
    public function testIssue433ResumeExtendsExpiryOfRefreshSession(string $driver) {
        $storage = $this->createStorage($driver);
        SessionsManager::setStorage($storage);
        $name = 'i433-'.$driver;

        $s = new Session(['name' => $name, 'duration' => 10080, 'refresh' => true]);
        $s->start();
        $s->set('k', 'v');
        $sid = $s->getId();

        // Simulate a session that was last refreshed almost a full lifetime
        // ago: only 30 seconds of it are left.
        $storage->setExpiry($sid, time() + 30);

        $resumed = new Session(['name' => $name, 'session-id' => $sid]);
        $resumed->start();
        $this->assertEquals(SessionStatus::RESUMED, $resumed->getStatus());
        $this->assertEquals('v', $resumed->get('k'));

        $this->assertGreaterThan(
            time() + 10080 * 60 - 300,
            $storage->getExpiry($sid),
            "Issue #433: resuming an active refresh session did not extend its expiry ($driver)."
        );
    }

    /**
     * Issue #434: SESSION_GC must be read as a duration in seconds, not as an
     * absolute Unix timestamp.
     *
     * @test
     */
    public function testIssue434SessionGcIsADuration() {
        $previous = getenv('SESSION_GC');
        putenv('SESSION_GC=604800');

        try {
            $threshold = strtotime(SessionsManager::getGCTime());
        } finally {
            if ($previous === false) {
                putenv('SESSION_GC');
            } else {
                putenv('SESSION_GC='.$previous);
            }
        }

        $this->assertEqualsWithDelta(
            time() - 604800,
            $threshold,
            5,
            'Issue #434: SESSION_GC=604800 should mean "older than one week", not the timestamp 604800.'
        );
    }

    /**
     * Issue #435: when '_meta' has no lifeTime, a resumed session must fall
     * back to the default duration (120 minutes), not 2 minutes.
     *
     * @test
     */
    #[DataProvider('storageProvider')]
    public function testIssue435MissingLifeTimeFallsBackToDefault(string $driver) {
        $storage = $this->createStorage($driver);
        SessionsManager::reset();
        SessionsManager::setStorage($storage);
        $name = 'i435-'.$driver;

        SessionsManager::start($name);
        $sid = SessionsManager::getActiveSession()->getId();
        $storage->write(
            $sid,
            '_meta',
            ['startedAt' => time(), 'isRef' => false, 'langCode' => 'EN'],
            null,
            ConflictStrategy::LAST_WRITE_WINS
        );

        $r = new Session(['name' => $name, 'session-id' => $sid]);
        $r->start();

        $this->assertEquals(
            Session::DEFAULT_SESSION_DURATION * 60,
            $r->getDuration(),
            "Issue #435: missing lifeTime fell back to {$r->getDuration()} seconds ($driver)."
        );
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
