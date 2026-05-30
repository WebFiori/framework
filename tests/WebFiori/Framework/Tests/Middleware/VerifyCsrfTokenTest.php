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

class VerifyCsrfTokenTest extends TestCase {
    protected function setUp(): void {
        SessionsManager::reset();
        SessionsManager::start('csrf-test');
    }
    /** @test */
    public function testGetRequestPassesWithoutValidation() {
        $csrf = new VerifyCsrfToken();
        $request = new Request();
        $request->setRequestMethod('GET');
        $response = new Response();

        $csrf->before($request, $response);

        $this->assertEquals(200, $response->getCode());
    }
    /** @test */
    public function testPostWithoutTokenRejects() {
        $csrf = new VerifyCsrfToken();
        $request = new Request();
        $request->setRequestMethod('POST');
        $response = new Response();

        $csrf->before($request, $response);

        $this->assertEquals(403, $response->getCode());
    }
    /** @test */
    public function testPostWithValidTokenPasses() {
        $csrf = new VerifyCsrfToken();
        $request = new Request();
        $request->setRequestMethod('GET');
        $response = new Response();

        // Generate token
        $csrf->before($request, $response);
        $token = VerifyCsrfToken::getToken();
        $this->assertNotNull($token);

        // Now POST with token - set header before creating request
        $_SERVER['HTTP_X_CSRF_TOKEN'] = $token;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request2 = Request::createFromGlobals();
        $response2 = new Response();

        $csrf->before($request2, $response2);

        $this->assertEquals(200, $response2->getCode());
        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }
    /** @test */
    public function testExcludedPathSkipsValidation() {
        $csrf = new VerifyCsrfToken();
        $csrf->setExcludedPaths(['/webhook']);
        $_SERVER['REQUEST_URI'] = '/webhook/stripe';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = Request::createFromGlobals();
        $response = new Response();

        $csrf->before($request, $response);

        $this->assertEquals(200, $response->getCode());
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }
    /** @test */
    public function testMetaTagInjected() {
        $csrf = new VerifyCsrfToken();
        $request = new Request();
        $request->setRequestMethod('GET');
        $response = new Response();

        $csrf->before($request, $response);
        $response->write('<html><head></head><body></body></html>');
        $csrf->after($request, $response);

        $body = $response->getBody();
        $this->assertStringContainsString('csrf-token', $body);
    }
}
