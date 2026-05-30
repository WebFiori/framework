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

class RateLimitMiddlewareTest extends TestCase {
    /** @test */
    public function testHeadersAdded() {
        $mw = new RateLimitMiddleware(10, 60);
        $request = new Request();
        $response = new Response();

        $mw->before($request, $response);

        $this->assertNotEmpty($response->getHeader('X-RateLimit-Limit'));
        $this->assertNotEmpty($response->getHeader('X-RateLimit-Remaining'));
        $this->assertNotEmpty($response->getHeader('X-RateLimit-Reset'));
    }
    /** @test */
    public function testTrustedIpBypasses() {
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $mw = new RateLimitMiddleware(1, 60, null, ['10.0.0.1']);
        $request = new Request();
        $response = new Response();

        $mw->before($request, $response);

        $this->assertEquals(200, $response->getCode());
        $this->assertEmpty($response->getHeader('X-RateLimit-Limit'));
        unset($_SERVER['REMOTE_ADDR']);
    }
}
