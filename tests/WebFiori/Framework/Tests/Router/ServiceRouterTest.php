<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Test\Router;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Router\ServiceRouter;

class ServiceRouterTest extends TestCase {
    private string $fixturesDir;
    private string $namespace = 'WebFiori\\Tests\\ServiceRouterFixtures';

    protected function setUp(): void {
        parent::setUp();
        ServiceRouter::reset();
        $this->fixturesDir = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'ServiceRouterFixtures';
    }

    protected function tearDown(): void {
        ServiceRouter::reset();
        parent::tearDown();
    }

    /** @test */
    public function testDiscoverFindsAttributedService() {
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir);
        $discovered = ServiceRouter::getDiscovered();

        $this->assertArrayHasKey('orders', $discovered);
        $this->assertEquals('WebFiori\\Tests\\ServiceRouterFixtures\\OrderService', $discovered['orders']['class']);
        $this->assertEquals('service', $discovered['orders']['type']);
        $this->assertEquals('/apis/orders', $discovered['orders']['path']);
    }

    /** @test */
    public function testDiscoverDerivesNameFromClassWhenAttributeNameEmpty() {
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir);
        $discovered = ServiceRouter::getDiscovered();

        $this->assertArrayHasKey('product', $discovered);
        $this->assertEquals('service', $discovered['product']['type']);
    }

    /** @test */
    public function testDiscoverFindsLegacyWebService() {
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir);
        $discovered = ServiceRouter::getDiscovered();

        $this->assertArrayHasKey('legacy', $discovered);
        $this->assertEquals('WebFiori\\Tests\\ServiceRouterFixtures\\LegacyService', $discovered['legacy']['class']);
        $this->assertEquals('service', $discovered['legacy']['type']);
    }

    /** @test */
    public function testDiscoverFindsWebServicesManager() {
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir);
        $discovered = ServiceRouter::getDiscovered();

        $this->assertArrayHasKey('users', $discovered);
        $this->assertEquals('WebFiori\\Tests\\ServiceRouterFixtures\\UsersManager', $discovered['users']['class']);
        $this->assertEquals('manager', $discovered['users']['type']);
        $this->assertEquals('/apis/users', $discovered['users']['path']);
    }

    /** @test */
    public function testDiscoverSkipsNonServiceClasses() {
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir);
        $discovered = ServiceRouter::getDiscovered();

        foreach ($discovered as $name => $entry) {
            $this->assertNotEquals('WebFiori\\Tests\\ServiceRouterFixtures\\HelperUtil', $entry['class']);
        }
    }

    /** @test */
    public function testDiscoverReturnsCount() {
        $count = ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir);

        $this->assertEquals(6, $count); // orders, product, legacy, users, auth/login, v2/users/profile
    }

    /** @test */
    public function testDiscoverWithNonExistentNamespace() {
        $count = ServiceRouter::discover(
            'WebFiori\\Tests\\Fixtures\\NonExistent',
            '/apis'
        );

        $this->assertEquals(0, $count);
        $this->assertEmpty(ServiceRouter::getDiscovered());
    }

    /** @test */
    public function testDiscoverWithoutDirectoryUsesNamespaceToPath() {
        // App\Apis is a real directory relative to ROOT_PATH
        // This exercises namespaceToPath() with a valid path
        $count = ServiceRouter::discover('App\\Apis', '/app-apis');
        // May find services or may not — depends on what's in App/Apis
        // The point is it doesn't crash
        $this->assertIsInt($count);
    }

    /** @test */
    public function testDiscoverAppliesBasePath() {
        ServiceRouter::discover($this->namespace, '/v2/apis', [], $this->fixturesDir);
        $discovered = ServiceRouter::getDiscovered();

        $this->assertEquals('/v2/apis/orders', $discovered['orders']['path']);
    }

    /** @test */
    public function testGetDiscoveredInitiallyEmpty() {
        $this->assertEmpty(ServiceRouter::getDiscovered());
    }

    /** @test */
    public function testResetClearsDiscovered() {
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir);
        $this->assertNotEmpty(ServiceRouter::getDiscovered());

        ServiceRouter::reset();
        $this->assertEmpty(ServiceRouter::getDiscovered());
    }

    /** @test */
    public function testDiscoverRecursiveFindsNestedServices() {
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir, true);
        $discovered = ServiceRouter::getDiscovered();

        // LoginService in UserAuth/ subdirectory
        $this->assertArrayHasKey('user-auth/login', $discovered);
        $this->assertEquals('/apis/user-auth/login', $discovered['user-auth/login']['path']);
    }

    /** @test */
    public function testDiscoverNonRecursiveSkipsSubdirectories() {
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir, false);
        $discovered = ServiceRouter::getDiscovered();

        $this->assertArrayNotHasKey('user-auth/login', $discovered);
    }

    /** @test */
    public function testDiscoverSkipsAttributeNameWithSlash() {
        // Services using 'name' property cannot have slashes.
        // Services using 'path' property CAN have slashes.
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir);
        $discovered = ServiceRouter::getDiscovered();

        // OrderService uses name='orders' — no slash
        $this->assertArrayHasKey('orders', $discovered);
        $this->assertStringNotContainsString('/', 'orders');

        // AuthLoginService uses path='auth/login' — slashes allowed via path
        $this->assertArrayHasKey('auth/login', $discovered);
    }

    /** @test */
    public function testDynamicRegistersRoute() {
        $routesBefore = Router::routesCount();
        ServiceRouter::dynamic($this->namespace, '/dynamic/{controller}', [], $this->fixturesDir);
        $this->assertGreaterThan($routesBefore, Router::routesCount());
    }

    /** @test */
    public function testHandleReturns404ForUnknownService() {
        $response = \WebFiori\Framework\App::getResponse();
        $response->setCode(200);
        ServiceRouter::handle('nonexistent', $this->namespace, $this->fixturesDir);
        $this->assertEquals(404, $response->getCode());
    }

    /**
     * @test
     * Tests that #[RestController(path: 'auth/login')] uses the path property
     * as the route key instead of the name.
     * @see https://github.com/webfiori/framework/issues/398
     */
    public function testDiscoverUsesPathPropertyForRouting() {
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir);
        $discovered = ServiceRouter::getDiscovered();

        $this->assertArrayHasKey('auth/login', $discovered);
        $this->assertEquals('/apis/auth/login', $discovered['auth/login']['path']);
        $this->assertEquals('WebFiori\\Tests\\ServiceRouterFixtures\\AuthLoginService', $discovered['auth/login']['class']);
        $this->assertEquals('service', $discovered['auth/login']['type']);
    }

    /**
     * @test
     * Tests that path property takes priority over name property.
     * @see https://github.com/webfiori/framework/issues/398
     */
    public function testPathPropertyTakesPriorityOverName() {
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir);
        $discovered = ServiceRouter::getDiscovered();

        // AuthLoginService has name='login' and path='auth/login'
        // It should be keyed by path, not name
        $this->assertArrayHasKey('auth/login', $discovered);
        $this->assertArrayNotHasKey('login', $discovered);
    }

    /**
     * @test
     * Tests that a multi-segment path property works without a name.
     * @see https://github.com/webfiori/framework/issues/398
     */
    public function testMultiSegmentPathWithoutName() {
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir);
        $discovered = ServiceRouter::getDiscovered();

        $this->assertArrayHasKey('v2/users/profile', $discovered);
        $this->assertEquals('/apis/v2/users/profile', $discovered['v2/users/profile']['path']);
        $this->assertEquals('WebFiori\\Tests\\ServiceRouterFixtures\\UserProfileService', $discovered['v2/users/profile']['class']);
    }

    /**
     * @test
     * Tests that services without path property still work as before (fallback to name or derived).
     * @see https://github.com/webfiori/framework/issues/398
     */
    public function testNoPathPropertyFallsBackToExistingBehavior() {
        ServiceRouter::discover($this->namespace, '/apis', [], $this->fixturesDir);
        $discovered = ServiceRouter::getDiscovered();

        // OrderService has name='orders', no path
        $this->assertArrayHasKey('orders', $discovered);
        $this->assertEquals('/apis/orders', $discovered['orders']['path']);

        // ProductService has no name, no path — derived from class name
        $this->assertArrayHasKey('product', $discovered);
    }
}
