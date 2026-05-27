<?php
namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Session\DefaultSessionStorage;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\Session\SessionStorage;

/**
 * Tests for session garbage collection.
 */
class GCTest extends TestCase {
    private $tempDir;
    /**
     * @test
     */
    public function testDefaultStorageGCDeletesOldFiles() {
        $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_gc_test_'.uniqid();
        mkdir($this->tempDir);

        // Create old files (2 hours ago)
        for ($i = 0; $i < 5; $i++) {
            $path = $this->tempDir.DIRECTORY_SEPARATOR.'old_session_'.$i;
            file_put_contents($path, 'data');
            touch($path, time() - 7200);
        }

        // Create new files (just now)
        for ($i = 0; $i < 3; $i++) {
            $path = $this->tempDir.DIRECTORY_SEPARATOR.'new_session_'.$i;
            file_put_contents($path, 'data');
        }

        // Use reflection to set storage location
        $storage = new DefaultSessionStorage();
        $ref = new \ReflectionClass($storage);
        $prop = $ref->getProperty('storeLoc');
        $prop->setAccessible(true);
        $prop->setValue($storage, $this->tempDir);

        // GC with threshold of 1 hour ago
        $threshold = date('Y-m-d H:i:s', time() - 3600);
        $storage->gc($threshold);

        // Old files should be deleted, new files should remain
        $remaining = array_diff(scandir($this->tempDir), ['.', '..']);
        $this->assertCount(3, $remaining);

        foreach ($remaining as $file) {
            $this->assertStringStartsWith('new_session_', $file);
        }
    }
    /**
     * @test
     */
    public function testDefaultStorageGCEmptyDir() {
        $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_gc_test_'.uniqid();
        mkdir($this->tempDir);

        $storage = new DefaultSessionStorage();
        $ref = new \ReflectionClass($storage);
        $prop = $ref->getProperty('storeLoc');
        $prop->setAccessible(true);
        $prop->setValue($storage, $this->tempDir);

        // Should not throw
        $threshold = date('Y-m-d H:i:s', time() - 3600);
        $storage->gc($threshold, 100);

        $remaining = array_diff(scandir($this->tempDir), ['.', '..']);
        $this->assertCount(0, $remaining);
    }
    /**
     * @test
     */
    public function testDefaultStorageGCNoLimitDeletesAll() {
        $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_gc_test_'.uniqid();
        mkdir($this->tempDir);

        // Create 20 old files
        for ($i = 0; $i < 20; $i++) {
            $path = $this->tempDir.DIRECTORY_SEPARATOR.'session_'.$i;
            file_put_contents($path, 'data');
            touch($path, time() - 7200);
        }

        $storage = new DefaultSessionStorage();
        $ref = new \ReflectionClass($storage);
        $prop = $ref->getProperty('storeLoc');
        $prop->setAccessible(true);
        $prop->setValue($storage, $this->tempDir);

        // GC with no limit (0)
        $threshold = date('Y-m-d H:i:s', time() - 3600);
        $storage->gc($threshold, 0);

        $remaining = array_diff(scandir($this->tempDir), ['.', '..']);
        $this->assertCount(0, $remaining);
    }
    /**
     * @test
     */
    public function testDefaultStorageGCRespectsBatchLimit() {
        $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_gc_test_'.uniqid();
        mkdir($this->tempDir);

        // Create 20 old files
        for ($i = 0; $i < 20; $i++) {
            $path = $this->tempDir.DIRECTORY_SEPARATOR.'session_'.$i;
            file_put_contents($path, 'data');
            touch($path, time() - 7200);
        }

        $storage = new DefaultSessionStorage();
        $ref = new \ReflectionClass($storage);
        $prop = $ref->getProperty('storeLoc');
        $prop->setAccessible(true);
        $prop->setValue($storage, $this->tempDir);

        // GC with batch limit of 5
        $threshold = date('Y-m-d H:i:s', time() - 3600);
        $storage->gc($threshold, 5);

        $remaining = array_diff(scandir($this->tempDir), ['.', '..']);
        $this->assertCount(15, $remaining);
    }
    /**
     * @test
     */
    public function testGCAlwaysRunsWhenProbabilityEqualsDivisor() {
        $spy = new SpySessionStorage();
        SessionsManager::setStorage($spy);
        SessionsManager::setGCProbability(1, 1);

        SessionsManager::start('test-session');
        SessionsManager::validateStorage();

        $this->assertCount(1, $spy->gcCalls);
        $this->assertEquals(100, $spy->gcCalls[0]['maxCount']);
    }
    /**
     * @test
     */
    public function testGCDefaultBatchSize() {
        $this->assertEquals(100, SessionsManager::getGCBatchSize());
    }
    /**
     * @test
     */
    public function testGCDefaultProbability() {
        $this->assertEquals(1, SessionsManager::getGCProbability());
        $this->assertEquals(100, SessionsManager::getGCDivisor());
    }
    /**
     * @test
     */
    public function testGCDisabledWhenDivisorZero() {
        $spy = new SpySessionStorage();
        SessionsManager::setStorage($spy);
        SessionsManager::setGCProbability(1, 0);

        SessionsManager::start('test-session');
        SessionsManager::validateStorage();

        $this->assertCount(0, $spy->gcCalls);
    }
    /**
     * @test
     */
    public function testGCDisabledWhenProbabilityZero() {
        $spy = new SpySessionStorage();
        SessionsManager::setStorage($spy);
        SessionsManager::setGCProbability(0, 100);

        SessionsManager::start('test-session');
        SessionsManager::validateStorage();

        $this->assertCount(0, $spy->gcCalls);
    }
    /**
     * @test
     */
    public function testGCPassesCorrectBatchSize() {
        $spy = new SpySessionStorage();
        SessionsManager::setStorage($spy);
        SessionsManager::setGCProbability(1, 1);
        SessionsManager::setGCBatchSize(250);

        SessionsManager::start('test-session');
        SessionsManager::validateStorage();

        $this->assertCount(1, $spy->gcCalls);
        $this->assertEquals(250, $spy->gcCalls[0]['maxCount']);
    }
    /**
     * @test
     */
    public function testGCPassesCorrectThreshold() {
        $spy = new SpySessionStorage();
        SessionsManager::setStorage($spy);
        SessionsManager::setGCProbability(1, 1);

        SessionsManager::start('test-session');
        SessionsManager::validateStorage();

        $this->assertCount(1, $spy->gcCalls);
        // Threshold should be approximately now - (DEFAULT_SESSION_DURATION * 2) minutes
        $expectedTime = time() - (120 * 60 * 2);
        $actualTime = strtotime($spy->gcCalls[0]['olderThan']);
        // Allow 5 seconds tolerance
        $this->assertEqualsWithDelta($expectedTime, $actualTime, 5);
    }
    /**
     * @test
     */
    public function testNoopDriverWorks() {
        $noop = new NoopSessionStorage();
        SessionsManager::setStorage($noop);
        SessionsManager::setGCProbability(1, 1);

        SessionsManager::start('noop-test');
        // Should not throw any errors
        SessionsManager::validateStorage();
        $this->assertTrue(true);
    }
    /**
     * @test
     */
    public function testResetRestoresGCDefaults() {
        SessionsManager::setGCProbability(5, 50);
        SessionsManager::setGCBatchSize(999);

        SessionsManager::reset();

        $this->assertEquals(1, SessionsManager::getGCProbability());
        $this->assertEquals(100, SessionsManager::getGCDivisor());
        $this->assertEquals(100, SessionsManager::getGCBatchSize());
    }
    /**
     * @test
     */
    public function testSetGCBatchSize() {
        SessionsManager::setGCBatchSize(50);
        $this->assertEquals(50, SessionsManager::getGCBatchSize());
    }
    /**
     * @test
     */
    public function testSetGCBatchSizeNegativeIgnored() {
        SessionsManager::setGCBatchSize(50);
        SessionsManager::setGCBatchSize(-1);
        $this->assertEquals(50, SessionsManager::getGCBatchSize());
    }
    /**
     * @test
     */
    public function testSetGCProbability() {
        SessionsManager::setGCProbability(5, 200);
        $this->assertEquals(5, SessionsManager::getGCProbability());
        $this->assertEquals(200, SessionsManager::getGCDivisor());
    }
    /**
     * @test
     */
    public function testSetGCProbabilityNegativeIgnored() {
        SessionsManager::setGCProbability(5, 200);
        SessionsManager::setGCProbability(-1, -1);
        $this->assertEquals(5, SessionsManager::getGCProbability());
        $this->assertEquals(200, SessionsManager::getGCDivisor());
    }

    protected function setUp(): void {
        SessionsManager::reset();
        $this->tempDir = null;
    }

    protected function tearDown(): void {
        if ($this->tempDir !== null && is_dir($this->tempDir)) {
            $files = array_diff(scandir($this->tempDir), ['.', '..']);

            foreach ($files as $file) {
                unlink($this->tempDir.DIRECTORY_SEPARATOR.$file);
            }
            rmdir($this->tempDir);
        }
    }
}

/**
 * A spy storage that records gc() calls.
 */
class SpySessionStorage implements SessionStorage {
    public array $gcCalls = [];
    public array $savedSessions = [];

    public function gc(string $olderThan, int $maxCount = 0) {
        $this->gcCalls[] = ['olderThan' => $olderThan, 'maxCount' => $maxCount];
    }

    public function read(string $sessionId) {
        return $this->savedSessions[$sessionId] ?? null;
    }

    public function remove(string $sessionId) {
        unset($this->savedSessions[$sessionId]);
    }

    public function save(string $sessionId, string $serializedSession) {
        $this->savedSessions[$sessionId] = $serializedSession;
    }
}

/**
 * A no-op storage for testing that the framework works with drivers that don't do GC.
 */
class NoopSessionStorage implements SessionStorage {
    public function gc(string $olderThan, int $maxCount = 0) {
        // No-op: e.g., Redis with TTL handles expiry natively
    }

    public function read(string $sessionId) {
        return null;
    }

    public function remove(string $sessionId) {
    }

    public function save(string $sessionId, string $serializedSession) {
    }
}
