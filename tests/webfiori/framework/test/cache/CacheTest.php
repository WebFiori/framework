<?php
namespace webfiori\framework\test\cache;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cache\Cache;
/**
 */
class CacheTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $key = 'first';
        $data = Cache::get($key, function () {
            return 'This is a test.';
        });
        $this->assertEquals('This is a test.', $data);
        $this->assertEquals('This is a test.', Cache::get($key));
        $this->assertNull(Cache::get('not_cached'));
    }
    /**
     * @test
     */
    public function test01() {
        $key = 'test_2';
        $this->assertFalse(Cache::has($key));
        $data = Cache::get($key, function () {
            return 'This is a test.';
        }, 5);
        $this->assertEquals('This is a test.', $data);
        $this->assertTrue(Cache::has($key));
        sleep(6);
        $this->assertFalse(Cache::has($key));
        $this->assertNull(Cache::get($key));
    }
    /**
     * @test
     */
    public function test03() {
        $key = 'ok_test';
        $this->assertFalse(Cache::has($key));
        $data = Cache::get($key, function () {
            return 'This is a test.';
        }, 600);
        $this->assertEquals('This is a test.', $data);
        $this->assertTrue(Cache::has($key));
        Cache::delete($key);
        $this->assertFalse(Cache::has($key));
        $this->assertNull(Cache::get($key));
    }
    /**
     * @test
     */
    public function test04() {
        $key = 'test_3';
        $this->assertFalse(Cache::has($key));
        $data = Cache::get($key, function () {
            return 'This is a test.';
        }, 600);
        $this->assertEquals('This is a test.', $data);
        $item = Cache::getItem($key);
        $this->assertNotNull($item);
        $this->assertEquals(600, $item->getTTL());
        Cache::setTTL($key, 1000);
        $item = Cache::getItem($key);
        $this->assertEquals(1000, $item->getTTL());
        Cache::delete($key);
        $this->assertNull(Cache::getItem($key));
    }
    public function test05() {
        $keys = [];
        for ($x = 0 ; $x < 10 ; $x++) {
            $key = 'item_'.$x;
            Cache::get($key, function () {
                return 'This is a test.';
            }, 600);
            $keys[] = $key;
        }
        foreach ($keys as $key) {
            $this->assertTrue(Cache::has($key));
        }
        Cache::flush();
        foreach ($keys as $key) {
            $this->assertFalse(Cache::has($key));
        }
    }
}
