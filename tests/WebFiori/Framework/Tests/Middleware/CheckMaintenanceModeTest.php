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

class CheckMaintenanceModeTest extends TestCase {
    private string $maintenanceFile;

    protected function setUp(): void {
        $this->maintenanceFile = APP_PATH.'Storage'.DIRECTORY_SEPARATOR.'.maintenance';
    }

    protected function tearDown(): void {
        if (file_exists($this->maintenanceFile)) {
            unlink($this->maintenanceFile);
        }
    }
    /** @test */
    public function testNoFilePassesThrough() {
        $mw = new CheckMaintenanceMode();
        $request = new Request();
        $response = new Response();

        $mw->before($request, $response);

        $this->assertEquals(200, $response->getCode());
    }
    /** @test */
    public function testFileExistsReturns503() {
        file_put_contents($this->maintenanceFile, json_encode([
            'message' => 'Down for maintenance',
            'retry_after' => 60,
            'allowed' => [],
            'api_prefix' => '/api',
        ]));

        $mw = new CheckMaintenanceMode();
        $request = new Request();
        $response = new Response();

        $mw->before($request, $response);

        $this->assertEquals(503, $response->getCode());
    }
    /** @test */
    public function testAllowedIpBypasses() {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        file_put_contents($this->maintenanceFile, json_encode([
            'message' => 'Down',
            'allowed' => ['192.168.1.100'],
            'api_prefix' => '/api',
        ]));

        $mw = new CheckMaintenanceMode();
        $request = new Request();
        $response = new Response();

        $mw->before($request, $response);

        $this->assertEquals(200, $response->getCode());
        unset($_SERVER['REMOTE_ADDR']);
    }
    /** @test */
    public function testApiResponseIsJson() {
        file_put_contents($this->maintenanceFile, json_encode([
            'message' => 'Maintenance',
            'retry_after' => 60,
            'allowed' => [],
            'api_prefix' => '/api',
        ]));

        // Use Accept header to trigger JSON response
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $mw = new CheckMaintenanceMode();
        $request = \WebFiori\Http\Request::createFromGlobals();
        $response = new \WebFiori\Http\Response();

        $mw->before($request, $response);

        $this->assertEquals(503, $response->getCode());
        $this->assertStringContainsString('Maintenance', $response->getBody());
        unset($_SERVER['HTTP_ACCEPT']);
    }
    /** @test */
    public function testAfterAndAfterSendDoNothing() {
        $mw = new CheckMaintenanceMode();
        $request = new \WebFiori\Http\Request();
        $response = new \WebFiori\Http\Response();
        $mw->after($request, $response);
        $mw->afterSend($request, $response);
        $this->assertTrue(true);
    }
    /** @test */
    public function testHtmlResponseWhenNotApi() {
        file_put_contents($this->maintenanceFile, json_encode([
            'message' => 'We are updating',
            'retry_after' => 120,
            'allowed' => [],
            'api_prefix' => '/api',
        ]));

        unset($_SERVER['HTTP_ACCEPT']);
        $mw = new CheckMaintenanceMode();
        $request = new \WebFiori\Http\Request();
        $response = new \WebFiori\Http\Response();

        $mw->before($request, $response);

        $this->assertEquals(503, $response->getCode());
        $this->assertStringContainsString('Under Maintenance', $response->getBody());
        $this->assertStringContainsString('We are updating', $response->getBody());
    }
    /** @test */
    public function testCustomMaintenancePage() {
        $customPage = APP_PATH.'Storage'.DIRECTORY_SEPARATOR.'maintenance.html';
        file_put_contents($customPage, '<html><body>Custom maintenance</body></html>');
        file_put_contents($this->maintenanceFile, json_encode([
            'message' => 'Down',
            'allowed' => [],
            'api_prefix' => '/api',
        ]));

        unset($_SERVER['HTTP_ACCEPT']);
        $mw = new CheckMaintenanceMode();
        $request = new \WebFiori\Http\Request();
        $response = new \WebFiori\Http\Response();

        $mw->before($request, $response);

        $this->assertEquals(503, $response->getCode());
        $this->assertStringContainsString('Custom maintenance', $response->getBody());
        unlink($customPage);
    }
}
