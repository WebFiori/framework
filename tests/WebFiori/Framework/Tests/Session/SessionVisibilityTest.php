<?php
namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Session\InMemorySessionStorage;
use WebFiori\Framework\Session\Session;
use WebFiori\Framework\Session\SessionOption;
use WebFiori\Framework\Session\SessionsManager;

/**
 * Tests that a session variable written by one process (worker) is visible
 * to another process that is already mid-flight — the IIS/FastCGI multi-worker
 * race condition (issue #410).
 *
 * These tests MUST FAIL against the current snapshot-isolation implementation.
 * They become the regression target for the real-time session system.
 */
class SessionVisibilityTest extends TestCase {
    private string $sessionId;

    /**
     * @test
     *
     * Process 1 writes a key to storage after Process 2 has already started.
     * Process 2 must see the key when it reads it — real-time visibility.
     *
     * CURRENT BEHAVIOR: Process 2 returns null (stale snapshot from start).
     * EXPECTED AFTER FIX: Process 2 returns 'you-have-mail'.
     */
    public function testProcess2SeesProcess1LiveWrite(): void {
        $sessionName = 'visibility-test';

        // Process 1 starts.
        $processA = new Session(['name' => $sessionName, SessionOption::SESSION_ID => $this->sessionId]);
        $processA->start();

        // Process 2 starts (concurrent — reads the same session state as A).
        $processB = new Session(['name' => $sessionName, SessionOption::SESSION_ID => $this->sessionId]);
        $processB->start();

        // Process 1 writes a new key mid-flight.
        $processA->set('notification', 'you-have-mail');
        // Process 1 closes (saves).
        $processA->close();

        // Process 2 reads the key — must see Process 1's committed write.
        // Under current snapshot isolation this returns null → TEST FAILS.
        $this->assertSame(
            'you-have-mail',
            $processB->get('notification'),
            'Process 2 must see Process 1\'s write via real-time storage reads (not a stale snapshot).'
        );
    }

    /**
     * @test
     *
     * A key set by Process 1 and saved must be immediately readable by
     * a freshly-constructed Process 2 with real-time reads enabled.
     *
     * CURRENT BEHAVIOR: returns null until a full deserialization round-trip.
     * EXPECTED AFTER FIX: returns 'bar' directly from storage.
     */
    public function testRealtimeReadAfterExternalWrite(): void {
        $sessionName = 'realtime-read-test';
        $sid = 'test-realtime-'.$this->sessionId;

        // Process 1 starts, sets a key, closes.
        $processA = new Session(['name' => $sessionName, SessionOption::SESSION_ID => $sid]);
        $processA->start();
        $processA->set('foo', 'bar');
        $processA->close();

        // Process 2 starts (after Process 1 has saved).
        $processB = new Session(['name' => $sessionName, SessionOption::SESSION_ID => $sid]);
        $processB->start();

        // Process 2 must read 'foo' live from storage without it being in its
        // initial in-memory snapshot. Under current code, get() only checks
        // $this->sessionVariables which was populated at start() from the blob.
        $this->assertSame(
            'bar',
            $processB->get('foo'),
            'After real-time reads, get() must fetch current value from storage.'
        );
    }

    protected function setUp(): void {
        $this->sessionId = 'test-visibility-session';
        InMemorySessionStorage::reset();
        SessionsManager::setStorage(new InMemorySessionStorage());
    }
}
