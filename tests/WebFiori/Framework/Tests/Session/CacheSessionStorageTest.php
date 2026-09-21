<?php
namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Cache\FileStorage;
use WebFiori\Framework\Session\CacheSessionStorage;
use WebFiori\Framework\Session\ConflictStrategy;

class CacheSessionStorageTest extends TestCase {
    private string $cacheDir;

    /** @test */
    public function testConstructorCustomValues() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage, 'custom:', 3600);

        $this->assertEquals('custom:', $storage->getPrefix());
        $this->assertEquals(3600, $storage->getTTL());
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
    public function testGcDoesNotThrow() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $storage->write('gc-test-1', 'k', 'data1', null, ConflictStrategy::LAST_WRITE_WINS);
        $storage->gc('2020-01-01 00:00:00', 10);

        $this->assertTrue(true);
    }

    /** @test */
    public function testIntegrationWithSessionManager() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $sessionId = 'integration-test-789';
        $storage->write($sessionId, 'user', 'admin', null, ConflictStrategy::LAST_WRITE_WINS);
        $entry = $storage->read($sessionId, 'user');

        $this->assertEquals('admin', $entry['value']);

        $storage->destroy($sessionId);
        $this->assertNull($storage->read($sessionId, 'user'));
    }

    /** @test */
    public function testPrefixIsolation() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storageA = new CacheSessionStorage($fileStorage, 'a:');
        $storageB = new CacheSessionStorage($fileStorage, 'b:');

        $storageA->write('session1', 'k', 'data_a', null, ConflictStrategy::LAST_WRITE_WINS);
        $storageB->write('session1', 'k', 'data_b', null, ConflictStrategy::LAST_WRITE_WINS);

        $this->assertEquals('data_a', $storageA->read('session1', 'k')['value']);
        $this->assertEquals('data_b', $storageB->read('session1', 'k')['value']);
    }

    /** @test */
    public function testReadNonExistent() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $result = $storage->read('non-existent-id', 'anykey');
        $this->assertNull($result);
    }

    /** @test */
    public function testRemove() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $sessionId = 'remove-test-456';
        $storage->write($sessionId, 'k', 'data', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->assertNotNull($storage->read($sessionId, 'k'));
        $storage->remove($sessionId, 'k');
        $this->assertNull($storage->read($sessionId, 'k'));
    }

    /** @test */
    public function testRemoveDoesNotAffectOtherSessions() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $storage->write('keep-me', 'k', 'keep', null, ConflictStrategy::LAST_WRITE_WINS);
        $storage->write('delete-me', 'k', 'delete', null, ConflictStrategy::LAST_WRITE_WINS);
        $storage->destroy('delete-me');

        $this->assertEquals('keep', $storage->read('keep-me', 'k')['value']);
        $this->assertNull($storage->read('delete-me', 'k'));
    }

    /** @test */
    public function testSaveAndRead() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $sessionId = 'test-session-123';
        $storage->write($sessionId, 'mykey', 'hello', null, ConflictStrategy::LAST_WRITE_WINS);
        $entry = $storage->read($sessionId, 'mykey');

        $this->assertNotNull($entry);
        $this->assertEquals('hello', $entry['value']);
    }

    /** @test */
    public function testSaveOverwrite() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);

        $sessionId = 'overwrite-test';
        $storage->write($sessionId, 'k', 'first', null, ConflictStrategy::LAST_WRITE_WINS);
        $storage->write($sessionId, 'k', 'second', null, ConflictStrategy::LAST_WRITE_WINS);
        $entry = $storage->read($sessionId, 'k');
        $this->assertEquals('second', $entry['value']);
    }

    /** @test */
    public function testSetTTL() {
        $fileStorage = new FileStorage($this->cacheDir);
        $storage = new CacheSessionStorage($fileStorage);
        $storage->setTTL(1800);

        $this->assertEquals(1800, $storage->getTTL());
    }

    protected function setUp(): void {
        parent::setUp();
        $this->cacheDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf-session-cache-test';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    protected function tearDown(): void {
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
}
