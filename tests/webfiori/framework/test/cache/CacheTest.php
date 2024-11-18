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
        $data = Cache::get('test', function () {
            return 'This is a test.';
        });
        $this->assertEquals('This is a test.', $data);
        $this->assertEquals('This is a test.', Cache::get('test'));
        $this->assertNull(Cache::get('not_cached'));
    }
    /**
     * @test
     */
    public function test01() {
        $data = Cache::get('test2', function () {
            return 'This is a test.';
        }, 1);
        $this->assertEquals('This is a test.', $data);
        sleep(2);
        $this->assertNull(Cache::get('test2'));
    }
    /**
     * @test
     */
    public function test03() {
        $this->assertFalse(Cache::has('test3'));
        $data = Cache::get('test3', function () {
            return 'This is a test.';
        }, 600);
        $this->assertEquals('This is a test.', $data);
        $this->assertTrue(Cache::has('test3'));
        Cache::delete('test3');
        $this->assertFalse(Cache::has('test3'));
        $this->assertNull(Cache::get('test3'));
    }
    /**
     * @test
     */
    public function test04() {
        $this->assertFalse(Cache::has('test3'));
        $data = Cache::get('test4', function () {
            return 'This is a test.';
        }, 600);
        $this->assertEquals('This is a test.', $data);
        $item = Cache::getItem('test4');
        $this->assertEquals(600, $item->getTTL());
        Cache::setTTL('test4', 1000);
        $item = Cache::getItem('test4');
        $this->assertEquals(1000, $item->getTTL());
    }
}
