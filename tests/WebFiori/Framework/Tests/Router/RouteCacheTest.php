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
use WebFiori\Cache\Cache;
use WebFiori\Cache\FileStorage;
use WebFiori\Framework\Router\RouteCache;
use WebFiori\Framework\Router\RouteOption;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Router\ServiceRouter;

class RouteCacheTest extends TestCase {
    private string $cacheDir;
    private Cache $cache;

    protected function setUp(): void {
        parent::setUp();
        $this->cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wf-route-cache-test';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        $this->cache = new Cache(new FileStorage($this->cacheDir));
        ServiceRouter::reset();
    }

    protected function tearDown(): void {
        // Clean up cache files
        $files = glob($this->cacheDir . DIRECTORY_SEPARATOR . '*');

        foreach ($files ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        @rmdir($this->cacheDir);
        ServiceRouter::reset();
        parent::tearDown();
    }

    /** @test */
    public function testConstructorDefaults() {
        $rc = new RouteCache($this->cache);
        $this->assertFalse($rc->isEnabled());
        $this->assertEquals('wf_routes_cache', $rc->getCacheKey());
    }

    /** @test */
    public function testConstructorCustomValues() {
        $rc = new RouteCache($this->cache, true, 'custom_key');
        $this->assertTrue($rc->isEnabled());
        $this->assertEquals('custom_key', $rc->getCacheKey());
    }

    /** @test */
    public function testSetEnabled() {
        $rc = new RouteCache($this->cache);
        $rc->setEnabled(true);
        $this->assertTrue($rc->isEnabled());
        $rc->setEnabled(false);
        $this->assertFalse($rc->isEnabled());
    }

    /** @test */
    public function testLoadReturnsFalseWhenDisabled() {
        $rc = new RouteCache($this->cache, false);
        $this->assertFalse($rc->load());
    }

    /** @test */
    public function testLoadReturnsFalseWhenNoCache() {
        $rc = new RouteCache($this->cache, true);
        $this->assertFalse($rc->load());
    }

    /** @test */
    public function testIsCachedReturnsFalseInitially() {
        $rc = new RouteCache($this->cache, true);
        $this->assertFalse($rc->isCached());
    }

    /** @test */
    public function testBuildCachesRoutes() {
        $fixturesDir = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'ServiceRouterFixtures';
        ServiceRouter::discover('WebFiori\\Tests\\ServiceRouterFixtures', '/cache-test', [], $fixturesDir);

        $rc = new RouteCache($this->cache, true);
        $count = $rc->build([
            ['namespace' => 'WebFiori\\Tests\\ServiceRouterFixtures', 'basePath' => '/cache-test', 'options' => [], 'directory' => $fixturesDir, 'recursive' => false]
        ]);

        $this->assertGreaterThan(0, $count);
        $this->assertTrue($rc->isCached());
    }

    /** @test */
    public function testBuildAndLoadRestoresRoutes() {
        $fixturesDir = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'ServiceRouterFixtures';
        ServiceRouter::discover('WebFiori\\Tests\\ServiceRouterFixtures', '/restore-test', [], $fixturesDir);

        $rc = new RouteCache($this->cache, true);
        $rc->build([
            ['namespace' => 'WebFiori\\Tests\\ServiceRouterFixtures', 'basePath' => '/restore-test', 'options' => [], 'directory' => $fixturesDir, 'recursive' => false]
        ]);

        ServiceRouter::reset();
        $this->assertEmpty(ServiceRouter::getDiscovered());

        $result = $rc->load();
        $this->assertTrue($result);
        $this->assertNotEmpty(ServiceRouter::getDiscovered());
    }

    /** @test */
    public function testClearRemovesCache() {
        $fixturesDir = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'ServiceRouterFixtures';
        ServiceRouter::discover('WebFiori\\Tests\\ServiceRouterFixtures', '/clear-test', [], $fixturesDir);

        $rc = new RouteCache($this->cache, true);
        $rc->build([]);
        $this->assertTrue($rc->isCached());

        $rc->clear();
        $this->assertFalse($rc->isCached());
    }

    /** @test */
    public function testBuildWithNoDiscoveredServices() {
        $rc = new RouteCache($this->cache, true);
        $count = $rc->build([]);
        $this->assertEquals(0, $count);
        $this->assertTrue($rc->isCached());
    }

    /** @test */
    public function testBuildIncludesDiscoveredServices() {
        $fixturesDir = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'ServiceRouterFixtures';
        ServiceRouter::discover('WebFiori\\Tests\\ServiceRouterFixtures', '/cached-apis', [], $fixturesDir);

        $rc = new RouteCache($this->cache, true);
        $rc->build([
            ['namespace' => 'WebFiori\\Tests\\ServiceRouterFixtures', 'basePath' => '/cached-apis', 'options' => [], 'directory' => $fixturesDir, 'recursive' => false]
        ]);

        // Clear discovered and reload from cache
        ServiceRouter::reset();
        $this->assertEmpty(ServiceRouter::getDiscovered());

        $rc->load();
        $this->assertNotEmpty(ServiceRouter::getDiscovered());
        $this->assertArrayHasKey('orders', ServiceRouter::getDiscovered());
    }

    /** @test */
    public function testLoadWithEmptyRoutesArray() {
        // Manually set cache with empty routes
        $this->cache->set('wf_routes_cache', [
            'routes' => [],
            'discovered' => [],
            'built_at' => date('c'),
            'total' => 0,
            'skipped' => 0,
        ], 86400);

        $rc = new RouteCache($this->cache, true);
        $result = $rc->load();
        $this->assertTrue($result);
    }
}
