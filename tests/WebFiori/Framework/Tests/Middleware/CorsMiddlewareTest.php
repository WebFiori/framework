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

class CorsMiddlewareTest extends TestCase {
    /** @test */
    public function testPreflightReturns204() {
        $_SERVER['HTTP_ORIGIN'] = 'https://app.example.com';
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        putenv('REQUEST_METHOD=OPTIONS');
        $cors = new CorsMiddleware(['origins' => ['https://app.example.com']]);
        $request = Request::createFromGlobals();
        $response = new Response();

        $cors->before($request, $response);

        $this->assertEquals(204, $response->getCode());
        unset($_SERVER['HTTP_ORIGIN']);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        putenv('REQUEST_METHOD=GET');
    }
    /** @test */
    public function testDisallowedOriginNoHeaders() {
        $_SERVER['HTTP_ORIGIN'] = 'https://evil.com';
        $cors = new CorsMiddleware(['origins' => ['https://allowed.com']]);
        $request = Request::createFromGlobals();
        $response = new Response();

        $cors->before($request, $response);
        $cors->after($request, $response);

        $this->assertEmpty($response->getHeader('Access-Control-Allow-Origin'));
        unset($_SERVER['HTTP_ORIGIN']);
    }
    /** @test */
    public function testWildcardOrigin() {
        $_SERVER['HTTP_ORIGIN'] = 'https://anything.com';
        $cors = new CorsMiddleware(['origins' => ['*']]);
        $request = Request::createFromGlobals();
        $response = new Response();

        $cors->after($request, $response);

        $headers = $response->getHeader('Access-Control-Allow-Origin');
        $this->assertNotEmpty($headers);
        unset($_SERVER['HTTP_ORIGIN']);
    }
    /** @test */
    public function testGetters() {
        $cors = new CorsMiddleware([
            'origins' => ['https://a.com'],
            'methods' => ['GET', 'POST'],
            'headers' => ['X-Custom'],
            'max-age' => 3600,
            'credentials' => true,
        ]);
        $this->assertEquals(['https://a.com'], $cors->getOrigins());
        $this->assertEquals(['GET', 'POST'], $cors->getMethods());
        $this->assertEquals(['X-Custom'], $cors->getHeaders());
        $this->assertEquals(3600, $cors->getMaxAge());
        $this->assertTrue($cors->isCredentialsAllowed());
    }
}
