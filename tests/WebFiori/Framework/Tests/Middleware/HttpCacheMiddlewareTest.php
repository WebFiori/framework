<?php

namespace WebFiori\Framework\Test\Middleware;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Middleware\CorsMiddleware;
use WebFiori\Framework\Middleware\HttpCacheMiddleware;
use WebFiori\Framework\Middleware\RateLimitMiddleware;
use WebFiori\Framework\Middleware\VerifyCsrfToken;
use WebFiori\Framework\Middleware\CheckMaintenanceMode;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

class HttpCacheMiddlewareTest extends TestCase {
    /** @test */
    public function testEtagGenerated() {
        $mw = new HttpCacheMiddleware();
        $request = new Request();
        $request->setRequestMethod('GET');
        $response = new Response();
        $response->write('Hello World');

        $mw->after($request, $response);

        $etag = $response->getHeader('ETag');
        $this->assertNotEmpty($etag);
    }
    /** @test */
    public function testNonGetSkipped() {
        $mw = new HttpCacheMiddleware();
        $request = new Request();
        $request->setRequestMethod('POST');
        $response = new Response();
        $response->write('data');

        $mw->after($request, $response);

        $this->assertEmpty($response->getHeader('ETag'));
    }
    /** @test */
    public function testCacheControlHeader() {
        $mw = new HttpCacheMiddleware(['max-age' => 3600, 'public' => true]);
        $request = new Request();
        $request->setRequestMethod('GET');
        $response = new Response();
        $response->write('content');

        $mw->after($request, $response);

        $cc = $response->getHeader('Cache-Control');
        $this->assertNotEmpty($cc);
    }
    /** @test */
    public function testSettersGetters() {
        $mw = new HttpCacheMiddleware();
        $mw->setMaxAge(600);
        $mw->setPublic(true);
        $this->assertEquals(600, $mw->getMaxAge());
        $this->assertTrue($mw->isPublic());
    }
}
