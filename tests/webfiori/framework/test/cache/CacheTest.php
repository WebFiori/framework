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
        $this->assertTrue(Cache::isEnabled());
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
    /**
     * @test
     */
    public function test06() {
        $key = 'bbuu';
        $this->assertTrue(Cache::isEnabled());
        Cache::setEnabled(false);
        $data = Cache::get($key, function () {
            return 'This is a test.';
        });
        $this->assertEquals('This is a test.', $data);
        $this->assertNull(Cache::get($key));
        $this->assertFalse(Cache::isEnabled());
    }
    /**
     * @test
     */
    public function testSet00() {
        $key = 'new_cool_key';
        Cache::setEnabled(true);
        $this->assertTrue(Cache::isEnabled());
        $this->assertTrue(Cache::set($key, 'This is a test.', 60, false));
        $this->assertEquals('This is a test.', Cache::get($key));
        $item = Cache::getItem($key);
        $this->assertEquals(60, $item->getTTL());
        
        $this->assertFalse(Cache::set($key, 'This is a test.', 60, false));
        $this->assertEquals('This is a test.', Cache::get($key));
        $item = Cache::getItem($key);
        $this->assertEquals(60, $item->getTTL());
        
        $this->assertTrue(Cache::set($key, 'This is a test 2.', 660, true));
        $this->assertEquals('This is a test 2.', Cache::get($key));
        $item = Cache::getItem($key);
        $this->assertEquals(660, $item->getTTL());
    }
    /**
     * @test
     */
    public function testSetTTL00() {
        $key = 'new_cool_key2';
        Cache::setEnabled(true);
        $this->assertTrue(Cache::isEnabled());
        $this->assertTrue(Cache::set($key, 'This is a test.', 60, false));
        $item = Cache::getItem($key);
        $this->assertEquals(60, $item->getTTL());
        Cache::setTTL($key, 700);
        
        $item = Cache::getItem($key);
        $this->assertEquals(700, $item->getTTL());
    }
    /**
     * @test
     */
    public function testSetTTL01() {
        $key = 'not exist cool';
        $this->assertFalse(Cache::setTTL($key, 700));
    }
}
