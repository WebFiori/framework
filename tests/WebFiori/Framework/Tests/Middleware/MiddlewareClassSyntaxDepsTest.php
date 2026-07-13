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
namespace WebFiori\Framework\Test\Middleware;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Framework\Middleware\MiddlewareManager;
use WebFiori\Framework\Router\RouterUri;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

/**
 * Middleware with no dependencies, used as a dependency target.
 */
class SessionMw extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('start-session');
    }

    public function before(Request $r, Response $res) {}
    public function after(Request $r, Response $res) {}
    public function afterSend(Request $r, Response $res) {}
}

/**
 * Middleware that declares dependency using ::class syntax.
 */
class AuditLogMw extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('audit-log');
    }

    public function getDependencies(): array {
        return [SessionMw::class];
    }

    public function before(Request $r, Response $res) {}
    public function after(Request $r, Response $res) {}
    public function afterSend(Request $r, Response $res) {}
}

/**
 * Middleware that mixes ::class syntax and string name dependencies.
 */
class MixedDepsMw extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('mixed-deps');
    }

    public function getDependencies(): array {
        return [SessionMw::class, 'audit-log'];
    }

    public function before(Request $r, Response $res) {}
    public function after(Request $r, Response $res) {}
    public function afterSend(Request $r, Response $res) {}
}

/**
 * Middleware that depends on a class that is not registered.
 */
class UnregisteredClassDepMw extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('unregistered-class-dep');
    }

    public function getDependencies(): array {
        return ['\Some\Nonexistent\Middleware'];
    }

    public function before(Request $r, Response $res) {}
    public function after(Request $r, Response $res) {}
    public function afterSend(Request $r, Response $res) {}
}

/**
 * Tests for issue #405: Support ::class syntax in getDependencies().
 *
 * @see https://github.com/webfiori/framework/issues/405
 */
class MiddlewareClassSyntaxDepsTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        MiddlewareManager::reset();
    }

    protected function tearDown(): void {
        MiddlewareManager::reset();
        parent::tearDown();
    }

    /**
     * @test
     * A middleware declaring a dependency via ::class should resolve to
     * the registered instance of that class.
     */
    public function testClassSyntaxDependencyResolved() {
        MiddlewareManager::register(new SessionMw());
        MiddlewareManager::register(new AuditLogMw());

        $uri = new RouterUri('https://example.com/class-dep', '');
        $uri->addMiddleware('audit-log');

        $middleware = $uri->getMiddleware();
        $names = array_map(fn ($mw) => $mw->getName(), $middleware);

        $this->assertContains('start-session', $names);
        $this->assertContains('audit-log', $names);
        $this->assertCount(2, $middleware);
    }

    /**
     * @test
     * When using ::class syntax, the dependency must be executed before
     * the dependent middleware.
     */
    public function testClassSyntaxDependencyOrder() {
        MiddlewareManager::register(new SessionMw());
        MiddlewareManager::register(new AuditLogMw());

        $uri = new RouterUri('https://example.com/class-order', '');
        $uri->addMiddleware('audit-log');

        $middleware = $uri->getMiddleware();
        $names = array_map(fn ($mw) => $mw->getName(), $middleware);

        // SessionMw must come before AuditLogMw
        $this->assertLessThan(
            array_search('audit-log', $names),
            array_search('start-session', $names)
        );
    }

    /**
     * @test
     * A middleware can mix ::class and string name dependencies.
     * Both should be resolved correctly.
     */
    public function testMixedClassAndStringDependencies() {
        MiddlewareManager::register(new SessionMw());
        MiddlewareManager::register(new AuditLogMw());
        MiddlewareManager::register(new MixedDepsMw());

        $uri = new RouterUri('https://example.com/mixed', '');
        $uri->addMiddleware('mixed-deps');

        $middleware = $uri->getMiddleware();
        $names = array_map(fn ($mw) => $mw->getName(), $middleware);

        $this->assertContains('start-session', $names);
        $this->assertContains('audit-log', $names);
        $this->assertContains('mixed-deps', $names);
        $this->assertCount(3, $middleware);
    }

    /**
     * @test
     * Execution order with mixed deps: session before audit-log before mixed-deps.
     */
    public function testMixedDependenciesOrder() {
        MiddlewareManager::register(new SessionMw());
        MiddlewareManager::register(new AuditLogMw());
        MiddlewareManager::register(new MixedDepsMw());

        $uri = new RouterUri('https://example.com/mixed-order', '');
        $uri->addMiddleware('mixed-deps');

        $middleware = $uri->getMiddleware();
        $names = array_map(fn ($mw) => $mw->getName(), $middleware);

        // session before audit-log
        $this->assertLessThan(
            array_search('audit-log', $names),
            array_search('start-session', $names)
        );
        // audit-log before mixed-deps
        $this->assertLessThan(
            array_search('mixed-deps', $names),
            array_search('audit-log', $names)
        );
    }

    /**
     * @test
     * If a ::class dependency references a class not registered in the manager,
     * it should be skipped silently (same as unresolvable string names).
     */
    public function testUnregisteredClassDependencySkipped() {
        MiddlewareManager::register(new UnregisteredClassDepMw());

        $uri = new RouterUri('https://example.com/unregistered', '');
        $uri->addMiddleware('unregistered-class-dep');

        $middleware = $uri->getMiddleware();
        $names = array_map(fn ($mw) => $mw->getName(), $middleware);

        $this->assertContains('unregistered-class-dep', $names);
        $this->assertCount(1, $middleware);
    }

    /**
     * @test
     * No duplicates when the dependency resolved via ::class is already
     * explicitly assigned to the route.
     */
    public function testNoDuplicateWhenClassDepAlreadyAssigned() {
        MiddlewareManager::register(new SessionMw());
        MiddlewareManager::register(new AuditLogMw());

        $uri = new RouterUri('https://example.com/no-dup', '');
        $uri->addMiddleware('start-session');
        $uri->addMiddleware('audit-log');

        $middleware = $uri->getMiddleware();
        $names = array_map(fn ($mw) => $mw->getName(), $middleware);

        $this->assertCount(2, $middleware);
        $this->assertEquals(1, count(array_keys($names, 'start-session')));
    }
}
