<?php
namespace webfiori\framework\test;

use PHPUnit\Framework\TestCase;
use webfiori\framework\Util;
/**
 * A test class for testing the class 'webfiori\framework\Util'.
 *
 * @author Ibrahim
 */
class UtilTest extends TestCase {
    /**
     * @test
     */
    public function testGetClientIP00() {
        $this->assertEquals('127.0.0.1', Util::getClientIP());
    }
    /**
     * @test
     */
    public function testGetRequestHeaders00() {
        $this->assertEquals([
            'host' => '127.0.0.1'
        ], Util::getRequestHeaders());
    }
    /**
     * @test
     */
    public function testGetWeekDayNum00() {
        $this->assertEquals(7, Util::getGWeekday('2019-09-08'));
        $this->assertEquals(1, Util::getGWeekday('2019-09-09'));
    }
}
