<?php
namespace WebFiori\Framework\Test;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\App;
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
        $this->assertFalse(App::getResponse()->hasHeader('content-type'));
        $this->assertTrue(App::getResponse()->addHeader('content-type', 'application/json'));
        $this->assertTrue(App::getResponse()->hasHeader('content-type', 'application/json'));
        $this->assertFalse(App::getResponse()->hasHeader('content-type', 'text/js'));
    }
    /**
     * @test
     */
    public function testAddHeader01() {
        $this->assertFalse(App::getResponse()->hasHeader('Set-Cookie'));
        $this->assertTrue(App::getResponse()->addHeader('Set-Cookie', 'name=ok'));
        $this->assertTrue(App::getResponse()->hasHeader('Set-Cookie'));
        $this->assertTrue(App::getResponse()->hasHeader('Set-Cookie','name=ok'));

        $this->assertTrue(App::getResponse()->addHeader('Set-Cookie', 'name=good'));
        $this->assertTrue(App::getResponse()->hasHeader('Set-cookie','name=good'));

        $this->assertTrue(App::getResponse()->addHeader('Set-Cookie', 'name=no'));
        $this->assertTrue(App::getResponse()->hasHeader('Set-Cookie','name=no'));
    }
    /**
     * @test
     */
    public function testClearBody() {
        App::getResponse()->write('Hello World!');
        $this->assertEquals('Hello World!', App::getResponse()->getBody());
        App::getResponse()->clearBody();
        $this->assertEquals('', App::getResponse()->getBody());
    }
    /**
     * @test
     * @depends testAddHeader00
     */
    public function testRemoveHeader00() {
        $this->assertTrue(App::getResponse()->hasHeader('content-type'));
        App::getResponse()->removeHeader('content-type');
        $this->assertFalse(App::getResponse()->hasHeader('content-type'));
    }
    /**
     * @test
     * @depends testAddHeader01
     */
    public function testRemoveHeader01() {
        $this->assertTrue(App::getResponse()->hasHeader('Set-Cookie'));
        $this->assertTrue(App::getResponse()->removeHeader('Set-cookie', 'name=good'));
        $this->assertFalse(App::getResponse()->hasHeader('Set-cookie','name=good'));
        $this->assertTrue(App::getResponse()->hasHeader('Set-Cookie','name=no'));
        $this->assertTrue(App::getResponse()->hasHeader('Set-Cookie','name=ok'));
        App::getResponse()->removeHeader('Set-cookie');
        $this->assertFalse(App::getResponse()->hasHeader('Set-Cookie'));
    }
    /**
     * @test
     */
    public function testRemoveHeaders() {
        App::getResponse()->addHeader('content-type', 'application/json');
        $this->assertTrue(App::getResponse()->hasHeader('content-type'));
        $this->assertFalse(App::getResponse()->hasHeader('content-type','text/plain'));
        App::getResponse()->clearHeaders();
        $this->assertEquals(0, count(App::getResponse()->getHeaders()));
    }
    /**
     * @test
     */
    public function testSetResponseCode() {
        $this->assertEquals(200, App::getResponse()->getCode());
        App::getResponse()->setCode(99);
        $this->assertEquals(200, App::getResponse()->getCode());
        App::getResponse()->setCode(100);
        $this->assertEquals(100, App::getResponse()->getCode());
        App::getResponse()->setCode(599);
        $this->assertEquals(599, App::getResponse()->getCode());
        App::getResponse()->setCode(600);
        $this->assertEquals(599, App::getResponse()->getCode());
    }
}
