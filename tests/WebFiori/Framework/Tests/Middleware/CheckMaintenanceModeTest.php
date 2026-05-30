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
}
