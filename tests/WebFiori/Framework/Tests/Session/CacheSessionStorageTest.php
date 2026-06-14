<?php
namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Cache\FileStorage;
use WebFiori\Framework\Session\CacheSessionStorage;

class CacheSessionStorageTest extends TestCase {
    private string $cacheDir;

    protected function setUp(): void {
        parent::setUp();
        $this->cacheDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf-session-cache-test';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    protected function tearDown(): void {
        // Clean up cache files
        $files = glob($this->cacheDir.DIRECTORY_SEPARATOR.'*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        if (is_dir($this->cacheDir)) {
            rmdir($this->cacheDir);
        }

        parent::tearDown();
    }

    /** @test */
    public function testConstructorDefaults() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $this->assertSame($fileStorage, $storage->getStorage());
        $this->assertEquals('wf_session:', $storage->getPrefix());
        $this->assertEquals(7200, $storage->getTTL());
    }

    /** @test */
    public function testConstructorCustomValues() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage, 'custom:', 3600);

        $this->assertEquals('custom:', $storage->getPrefix());
        $this->assertEquals(3600, $storage->getTTL());
    }

    /** @test */
    public function testSetTTL() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);
        $storage->setTTL(1800);

        $this->assertEquals(1800, $storage->getTTL());
    }

    /** @test */
    public function testSaveAndRead() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $sessionId = 'test-session-123';
        $data = 'serialized_session_data_here';

        $storage->save($sessionId, $data);
        $result = $storage->read($sessionId);

        $this->assertEquals($data, $result);
    }

    /** @test */
    public function testReadNonExistent() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $result = $storage->read('non-existent-id');
        $this->assertNull($result);
    }

    /** @test */
    public function testRemove() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $sessionId = 'remove-test-456';
        $storage->save($sessionId, 'some_data');

        // Verify it exists
        $this->assertNotNull($storage->read($sessionId));

        // Remove and verify
        $storage->remove($sessionId);
        $this->assertNull($storage->read($sessionId));
    }

    /** @test */
    public function testSaveOverwrite() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $sessionId = 'overwrite-test';
        $storage->save($sessionId, 'first_data');
        $storage->save($sessionId, 'second_data');

        $this->assertEquals('second_data', $storage->read($sessionId));
    }

    /** @test */
    public function testGcDoesNotThrow() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $storage->save('gc-test-1', 'data1');
        $storage->gc('2020-01-01 00:00:00', 10);

        // Should not throw
        $this->assertTrue(true);
    }

    /** @test */
    public function testPrefixIsolation() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storageA = new CacheSessionStorage($fileStorage, 'a:');
        $storageB = new CacheSessionStorage($fileStorage, 'b:');

        $storageA->save('session1', 'data_a');
        $storageB->save('session1', 'data_b');

        $this->assertEquals('data_a', $storageA->read('session1'));
        $this->assertEquals('data_b', $storageB->read('session1'));
    }

    /** @test */
    public function testRemoveDoesNotAffectOtherSessions() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $storage->save('keep-me', 'keep_data');
        $storage->save('delete-me', 'delete_data');

        $storage->remove('delete-me');

        $this->assertEquals('keep_data', $storage->read('keep-me'));
        $this->assertNull($storage->read('delete-me'));
    }

    /** @test */
    public function testIntegrationWithSessionManager() {
        $fileStorage = new FileStorage($this->cacheDir);
        $cacheStorage = new CacheSessionStorage($fileStorage);

        // Simulate what SessionManager does
        $sessionId = 'integration-test-789';
        $serialized = 'O:8:"stdClass":1:{s:4:"user";s:5:"admin";}';

        $cacheStorage->save($sessionId, $serialized);
        $loaded = $cacheStorage->read($sessionId);

        $this->assertEquals($serialized, $loaded);

        $cacheStorage->remove($sessionId);
        $this->assertNull($cacheStorage->read($sessionId));
    }
}
