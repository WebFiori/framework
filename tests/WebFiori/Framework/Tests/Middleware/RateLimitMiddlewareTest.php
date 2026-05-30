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
    /** @test */
    public function testExceedsLimitReturns429() {
        $key = 'test-exceed-'.time();
        $mw = new RateLimitMiddleware(1, 60, fn($r) => $key);
        $request = new Request();
        $response = new Response();

        // First request passes
        $mw->before($request, $response);
        $this->assertEquals(200, $response->getCode());

        // Second request exceeds
        $response2 = new Response();
        $mw->before($request, $response2);
        $this->assertEquals(429, $response2->getCode());
        $this->assertNotEmpty($response2->getHeader('Retry-After'));
    }
    /** @test */
    public function testCustomKeyResolver() {
        $mw = new RateLimitMiddleware(60, 60, function($req) {
            return 'custom-key';
        });
        $request = new Request();
        $response = new Response();

        $mw->before($request, $response);
        $this->assertEquals(200, $response->getCode());
        $this->assertNotEmpty($response->getHeader('X-RateLimit-Limit'));
    }
    /** @test */
    public function testAfterAndAfterSendDoNothing() {
        $mw = new RateLimitMiddleware();
        $request = new Request();
        $response = new Response();
        $mw->after($request, $response);
        $mw->afterSend($request, $response);
        $this->assertTrue(true);
    }
    /** @test */
    public function testGetDependencies() {
        $mw = new RateLimitMiddleware();
        $this->assertEquals(['start-session'], $mw->getDependencies());
    }
}
