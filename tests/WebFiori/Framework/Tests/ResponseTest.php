<?php
namespace webfiori\framework\test;

use PHPUnit\Framework\TestCase;
use WebFiori\Http\Response;

/**
 * Description of RequestTest
 *
 * @author Ibrahim
 */
class ResponseTest extends TestCase {
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
     */
    public function testClearBody() {
        Response::write('Hello World!');
        $this->assertEquals('Hello World!', Response::getBody());
        Response::clearBody();
        $this->assertEquals('', Response::getBody());
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
    /**
     * @test
     */
    public function testRemoveHeaders() {
        Response::addHeader('content-type', 'application/json');
        $this->assertTrue(Response::hasHeader('content-type'));
        $this->assertFalse(Response::hasHeader('content-type','text/plain'));
        Response::clearHeaders();
        $this->assertEquals(0, count(Response::getHeaders()));
    }
    /**
     * @test
     */
    public function testSetResponseCode() {
        $this->assertEquals(200, Response::getCode());
        Response::setCode(99);
        $this->assertEquals(200, Response::getCode());
        Response::setCode(100);
        $this->assertEquals(100, Response::getCode());
        Response::setCode(599);
        $this->assertEquals(599, Response::getCode());
        Response::setCode(600);
        $this->assertEquals(599, Response::getCode());
    }
}
