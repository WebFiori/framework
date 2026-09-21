<?php
namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Session\InMemorySessionStorage;
use WebFiori\Framework\Session\Session;
use WebFiori\Framework\Session\SessionOption;
use WebFiori\Framework\Session\SessionsManager;

/**
 * Tests that concurrent writes from two processes do not lose each other's
 * changes — the last-writer-wins clobber problem (issue #410).
 *
 * These tests MUST FAIL against the current whole-blob snapshot-isolation
 * implementation. They become the regression target for the per-key write system.
 */
class SessionLostWriteTest extends TestCase {
    /**
     * @test
     *
     * Process A sets 'foo', saves. Process B (which started before A saved,
     * holding a stale snapshot) then sets 'theme' and saves. Under whole-blob
     * save-at-end, B's save overwrites A's 'foo'.
     *
     * CURRENT BEHAVIOR: only 'theme' survives — 'foo' is LOST.
     * EXPECTED AFTER FIX: both 'foo' and 'theme' survive.
     */
    public function testConcurrentWritesDoNotLoseKeys(): void {
        $sid = 'lost-write-session';
        $name = 'lost-write-test';

        // Both processes start (read same initial state — empty session).
        $processA = new Session(['name' => $name, SessionOption::SESSION_ID => $sid]);
        $processA->start();

        $processB = new Session(['name' => $name, SessionOption::SESSION_ID => $sid]);
        $processB->start();

        // Process A sets its key and closes first.
        $processA->set('foo', 'bar');
        $processA->close();

        // Process B sets a different key and closes after A.
        // Under whole-blob save, B's close() saves {theme:'dark'} without 'foo'.
        $processB->set('theme', 'dark');
        $processB->close();

        // Start a new session C to read the final state.
        $processC = new Session(['name' => $name, SessionOption::SESSION_ID => $sid]);
        $processC->start();

        // Both keys must exist — per-key writes prevent clobbering.
        $this->assertSame(
            'bar',
            $processC->get('foo'),
            "'foo' was lost because Process B's whole-blob save clobbered Process A's write."
        );
        $this->assertSame(
            'dark',
            $processC->get('theme'),
            "'theme' must also survive."
        );
    }

    /**
     * @test
     *
     * Three concurrent processes each write a distinct key.
     * All three must survive regardless of close() order.
     *
     * CURRENT BEHAVIOR: only the last-closing process's keys survive.
     * EXPECTED AFTER FIX: all three keys survive.
     */
    public function testThreeConcurrentWritesAllSurvive(): void {
        $sid = 'three-worker-session';
        $name = 'three-worker-test';

        $a = new Session(['name' => $name, SessionOption::SESSION_ID => $sid]);
        $a->start();
        $b = new Session(['name' => $name, SessionOption::SESSION_ID => $sid]);
        $b->start();
        $c = new Session(['name' => $name, SessionOption::SESSION_ID => $sid]);
        $c->start();

        $a->set('keyA', 'valueA');
        $b->set('keyB', 'valueB');
        $c->set('keyC', 'valueC');

        // Close in A→B→C order; C saves last.
        $a->close();
        $b->close();
        $c->close();

        $reader = new Session(['name' => $name, SessionOption::SESSION_ID => $sid]);
        $reader->start();

        $this->assertSame('valueA', $reader->get('keyA'), 'keyA lost in multi-worker clobber.');
        $this->assertSame('valueB', $reader->get('keyB'), 'keyB lost in multi-worker clobber.');
        $this->assertSame('valueC', $reader->get('keyC'), 'keyC lost in multi-worker clobber.');
    }

    protected function setUp(): void {
        InMemorySessionStorage::reset();
        SessionsManager::setStorage(new InMemorySessionStorage());
    }
}
