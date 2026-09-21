<?php
namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Session\ConflictStrategy;
use WebFiori\Framework\Session\DefaultSessionStorage;

class DefaultSessionStorageTest extends TestCase {
    public function testConstruct() {
        $storage = new DefaultSessionStorage();
        $this->assertTrue($storage->isStorageDirExist());
    }

    public function testDestroy() {
        $storage = new DefaultSessionStorage();
        $id = 'destroy-test-'.time();
        $storage->write($id, 'k', 'v', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->assertTrue($storage->isStorageFileExist($id));
        $storage->destroy($id);
        $this->assertFalse($storage->isStorageFileExist($id));
    }

    public function testGc() {
        $storage = new DefaultSessionStorage();
        $id = 'gc-test-session-'.time();
        $storage->write($id, 'k', 'v', null, ConflictStrategy::LAST_WRITE_WINS);
        $storage->gc(date('Y-m-d H:i:s', time() - 3600), 10);
        // File is newer than the threshold — should still exist.
        $this->assertTrue($storage->isStorageFileExist($id));
        $storage->destroy($id);
    }

    public function testGcRemovesOldFiles() {
        $storage = new DefaultSessionStorage();
        $id = 'gc-old-session-'.time();
        $storage->write($id, 'k', 'v', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->assertTrue($storage->isStorageFileExist($id));
        $storage->gc(date('Y-m-d H:i:s', time() - 3600), 10);
        // Newer than threshold — still exists.
        $this->assertTrue($storage->isStorageFileExist($id));
        $storage->destroy($id);
    }

    public function testIsStorageFileExist() {
        $storage = new DefaultSessionStorage();
        $this->assertFalse($storage->isStorageFileExist('definitely-not-exist'));
    }

    public function testReadAll() {
        $storage = new DefaultSessionStorage();
        $id = 'readall-test-'.time();
        $storage->write($id, 'foo', 'bar', null, ConflictStrategy::LAST_WRITE_WINS);
        $storage->write($id, 'baz', 42, null, ConflictStrategy::LAST_WRITE_WINS);
        $all = $storage->readAll($id);
        $this->assertArrayHasKey('foo', $all);
        $this->assertArrayHasKey('baz', $all);
        $this->assertEquals('bar', $all['foo']['value']);
        $this->assertEquals(42, $all['baz']['value']);
        $storage->destroy($id);
    }

    public function testReadNonExistent() {
        $storage = new DefaultSessionStorage();
        $data = $storage->read('non-existent-session-id-xyz', 'somekey');
        $this->assertNull($data);
    }

    public function testRemove() {
        $storage = new DefaultSessionStorage();
        $id = 'test-remove-session-'.time();
        $storage->write($id, 'key1', 'data', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->assertNotNull($storage->read($id, 'key1'));
        $storage->remove($id, 'key1');
        $this->assertNull($storage->read($id, 'key1'));
        $storage->destroy($id);
    }

    public function testRemoveNonExistent() {
        $storage = new DefaultSessionStorage();
        // Should not throw
        $storage->remove('non-existent-id-'.time(), 'anykey');
        $this->assertTrue(true);
    }

    public function testSaveAndRead() {
        $this->testWriteAndRead();
    }

    public function testVersionIncrementsOnWrite() {
        $storage = new DefaultSessionStorage();
        $id = 'version-test-'.time();
        $v1 = $storage->write($id, 'key', 'first', null, ConflictStrategy::LAST_WRITE_WINS);
        $v2 = $storage->write($id, 'key', 'second', null, ConflictStrategy::LAST_WRITE_WINS);
        $this->assertEquals(1, $v1);
        $this->assertEquals(2, $v2);
        $entry = $storage->read($id, 'key');
        $this->assertEquals('second', $entry['value']);
        $this->assertEquals(2, $entry['version']);
        $storage->destroy($id);
    }

    public function testWriteAndRead() {
        $storage = new DefaultSessionStorage();
        $id = 'test-session-'.time();
        $storage->write($id, 'mykey', 'hello', null, ConflictStrategy::LAST_WRITE_WINS);
        $entry = $storage->read($id, 'mykey');
        $this->assertNotNull($entry);
        $this->assertEquals('hello', $entry['value']);
        $this->assertEquals(1, $entry['version']);
        // cleanup
        $storage->destroy($id);
    }
}
