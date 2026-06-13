<?php
namespace WebFiori\Framework\Test\Middleware;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Middleware\StartSessionMiddleware;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

class StartSessionMiddlewareTest extends TestCase {
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
        $mw = new StartSessionMiddleware();
        $this->assertEquals('start-session', $mw->getName());
        $this->assertEquals(PHP_INT_MAX, $mw->getPriority());
        $this->assertEquals('wf-session', $mw->getSessionName());
        $this->assertEquals([], $mw->getSessionOptions());
        $this->assertContains('web', $mw->getGroups());
    }

    /** @test */
    public function testSetSessionName() {
        $mw = new StartSessionMiddleware();
        $mw->setSessionName('my-session');
        $this->assertEquals('my-session', $mw->getSessionName());
    }

    /** @test */
    public function testSetSessionOptions() {
        $mw = new StartSessionMiddleware();
        $mw->setSessionOptions(['duration' => 3600]);
        $this->assertEquals(['duration' => 3600], $mw->getSessionOptions());
    }

    /** @test */
    public function testBeforeStartsSession() {
        $mw = new StartSessionMiddleware();
        $request = new Request();
        $response = new Response();

        $this->assertNull(SessionsManager::getActiveSession());
        $mw->before($request, $response);
        $active = SessionsManager::getActiveSession();
        $this->assertNotNull($active);
        $this->assertEquals('wf-session', $active->getName());
        $this->assertTrue($active->isRunning());
    }

    /** @test */
    public function testBeforeWithCustomName() {
        $mw = new StartSessionMiddleware();
        $mw->setSessionName('custom-session');
        $request = new Request();
        $response = new Response();

        $mw->before($request, $response);
        $active = SessionsManager::getActiveSession();
        $this->assertNotNull($active);
        $this->assertEquals('custom-session', $active->getName());
    }

    /** @test */
    public function testAfterAddsCookieHeaders() {
        $mw = new StartSessionMiddleware();
        $request = new Request();
        $response = new Response();

        $mw->before($request, $response);
        $mw->after($request, $response);

        $headers = $response->getHeaders();
        $hasCookie = false;

        foreach ($headers as $h) {
            if (strtolower($h->getName()) === 'set-cookie') {
                $hasCookie = true;
                break;
            }
        }

        $this->assertTrue($hasCookie, 'Response should have set-cookie header after middleware after()');
    }

    /** @test */
    public function testAfterSendValidatesStorage() {
        $mw = new StartSessionMiddleware();
        $request = new Request();
        $response = new Response();

        $mw->before($request, $response);
        // Should not throw
        $mw->afterSend($request, $response);
        $this->assertTrue(true);
    }

    /** @test */
    public function testGetManager() {
        $mw = new StartSessionMiddleware();
        $this->assertNotNull($mw->getManager());
    }
}
