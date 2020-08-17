<?php

namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\Response;

/**
 * Description of RequestTest
 *
 * @author Ibrahim
 */

class RequestTest extends TestCase {
    /**
     * @test
     */
    public function testAddHeader00() {
        $this->assertFalse(Response::hasHeader('content-type'));
        $this->assertTrue(Response::addHeader('content-type', 'application/json'));
        $this->assertTrue(Response::hasHeader('content-type', 'application/json'));
        $this->assertFalse(Response::hasHeader('content-type', 'text/js'));
    }
    /**
     * @test
     */
    public function testAddHeader01() {
        $this->assertFalse(Response::hasHeader('Set-Cookie'));
        $this->assertTrue(Response::addHeader('Set-Cookie', 'name=ok'));
        $this->assertTrue(Response::hasHeader('Set-Cookie'));
        $this->assertTrue(Response::hasHeader('Set-Cookie','name=ok'));
        
        $this->assertTrue(Response::addHeader('Set-Cookie', 'name=good'));
        $this->assertTrue(Response::hasHeader('Set-cookie','name=good'));
        
        $this->assertTrue(Response::addHeader('Set-Cookie', 'name=no'));
        $this->assertTrue(Response::hasHeader('Set-Cookie','name=no'));
    }
    /**
     * @test
     * @depends testAddHeader00
     */
    public function testRemoveHeader00() {
        $this->assertTrue(Response::hasHeader('content-type'));
        Response::removeHeader('content-type');
        $this->assertFalse(Response::hasHeader('content-type'));
    }
    /**
     * @test
     * @depends testAddHeader01
     */
    public function testRemoveHeader01() { 
        $this->assertTrue(Response::hasHeader('Set-Cookie'));
        $this->assertTrue(Response::removeHeader('Set-cookie', 'name=good'));
        $this->assertFalse(Response::hasHeader('Set-cookie','name=good'));
        $this->assertTrue(Response::hasHeader('Set-Cookie','name=no'));
        $this->assertTrue(Response::hasHeader('Set-Cookie','name=ok'));
        Response::removeHeader('Set-cookie');
        $this->assertFalse(Response::hasHeader('Set-Cookie'));
    }
}
