<?php
namespace WebFiori\Framework\Test\Middleware;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Middleware\CacheMiddleware;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

class CacheMiddlewareTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        SessionsManager::reset();
    }

    protected function tearDown(): void {
        SessionsManager::reset();
        parent::tearDown();
    }

    /** @test */
    public function testDefaults() {
        $mw = new CacheMiddleware();
        $this->assertEquals('cache', $mw->getName());
        $this->assertEquals(50, $mw->getPriority());
        $this->assertContains('web', $mw->getGroups());
    }

    /** @test */
    public function testGetKeyWithoutSession() {
        $mw = new CacheMiddleware();
        $key = $mw->getKey();
        $this->assertNotEmpty($key);
        $this->assertIsString($key);
    }

    /** @test */
    public function testGetKeyWithSession() {
        SessionsManager::start('wf-session');
        $mw = new CacheMiddleware();
        $key = $mw->getKey();
        $session = SessionsManager::getActiveSession();
        $this->assertStringContainsString($session->getId(), $key);
    }

    /** @test */
    public function testBeforeWithNoCache() {
        $mw = new CacheMiddleware();
        $request = new Request();
        $response = new Response();

        $mw->before($request, $response);
        $this->assertEquals(200, $response->getCode());
        $this->assertEmpty($response->getBody());
    }

    /** @test */
    public function testAfterSendDoesNothing() {
        $mw = new CacheMiddleware();
        $request = new Request();
        $response = new Response();

        $mw->afterSend($request, $response);
        $this->assertTrue(true);
    }
}
