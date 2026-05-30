<?php
namespace WebFiori\Framework\Test\Middleware;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Middleware\AuthorizeMiddleware;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

class AuthorizeMiddlewareTest extends TestCase {
    public function testConstruct() {
        $mw = new AuthorizeMiddleware('edit-post');
        $this->assertEquals('authorize', $mw->getName());
        $this->assertEquals('edit-post', $mw->getPermission());
        $this->assertEquals(30000, $mw->getPriority());
    }
    public function testGetDependencies() {
        $mw = new AuthorizeMiddleware('view-users');
        $deps = $mw->getDependencies();
        $this->assertContains('start-session', $deps);
    }
    public function testBeforeNoUser() {
        $mw = new AuthorizeMiddleware('admin');
        $request = Request::createFromGlobals();
        $response = new Response();

        $mw->before($request, $response);
        $this->assertEquals(401, $response->getCode());
    }
    public function testAfterDoesNothing() {
        $mw = new AuthorizeMiddleware('admin');
        $request = Request::createFromGlobals();
        $response = new Response();

        $mw->after($request, $response);
        $this->assertEquals(200, $response->getCode());
    }
    public function testAfterSendDoesNothing() {
        $mw = new AuthorizeMiddleware('admin');
        $request = Request::createFromGlobals();
        $response = new Response();

        $mw->afterSend($request, $response);
        $this->assertEquals(200, $response->getCode());
    }
}
