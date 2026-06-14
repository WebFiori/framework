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

class MwA extends AbstractMiddleware {
    public function __construct() { parent::__construct('mw-a'); }
    public function before(Request $r, Response $res) {}
    public function after(Request $r, Response $res) {}
    public function afterSend(Request $r, Response $res) {}
}

class MwB extends AbstractMiddleware {
    public function __construct() { parent::__construct('mw-b'); }
    public function getDependencies(): array { return ['mw-a']; }
    public function before(Request $r, Response $res) {}
    public function after(Request $r, Response $res) {}
    public function afterSend(Request $r, Response $res) {}
}

class MwC extends AbstractMiddleware {
    public function __construct() { parent::__construct('mw-c'); }
    public function getDependencies(): array { return ['mw-b']; }
    public function before(Request $r, Response $res) {}
    public function after(Request $r, Response $res) {}
    public function afterSend(Request $r, Response $res) {}
}

class MwOrphan extends AbstractMiddleware {
    public function __construct() { parent::__construct('mw-orphan'); }
    public function getDependencies(): array { return ['mw-nonexistent']; }
    public function before(Request $r, Response $res) {}
    public function after(Request $r, Response $res) {}
    public function afterSend(Request $r, Response $res) {}
}

class MiddlewareTransitiveDepsTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        MiddlewareManager::register(new MwA());
        MiddlewareManager::register(new MwB());
        MiddlewareManager::register(new MwC());
    }

    protected function tearDown(): void {
        MiddlewareManager::reset();
        parent::tearDown();
    }

    /** @test */
    public function testSingleDependencyAutoResolved() {
        $uri = new RouterUri('https://example.com/test', '');
        $uri->addMiddleware(new MwB()); // depends on mw-a

        $middleware = $uri->getMiddleware();
        $names = array_map(fn($mw) => $mw->getName(), $middleware);

        $this->assertContains('mw-a', $names);
        $this->assertContains('mw-b', $names);
        $this->assertCount(2, $middleware);
    }

    /** @test */
    public function testTransitiveChainResolved() {
        $uri = new RouterUri('https://example.com/test', '');
        $uri->addMiddleware(new MwC()); // C depends on B, B depends on A

        $middleware = $uri->getMiddleware();
        $names = array_map(fn($mw) => $mw->getName(), $middleware);

        $this->assertContains('mw-a', $names);
        $this->assertContains('mw-b', $names);
        $this->assertContains('mw-c', $names);
        $this->assertCount(3, $middleware);
    }

    /** @test */
    public function testExecutionOrderCorrect() {
        $uri = new RouterUri('https://example.com/test', '');
        $uri->addMiddleware(new MwC());

        $middleware = $uri->getMiddleware();
        $names = array_map(fn($mw) => $mw->getName(), $middleware);

        // A must come before B, B before C
        $this->assertLessThan(
            array_search('mw-b', $names),
            array_search('mw-a', $names)
        );
        $this->assertLessThan(
            array_search('mw-c', $names),
            array_search('mw-b', $names)
        );
    }

    /** @test */
    public function testNoDuplicateWhenDependencyAlreadyAssigned() {
        $uri = new RouterUri('https://example.com/test', '');
        $uri->addMiddleware(new MwA());
        $uri->addMiddleware(new MwB());

        $middleware = $uri->getMiddleware();
        $names = array_map(fn($mw) => $mw->getName(), $middleware);

        $this->assertCount(2, $middleware);
        $this->assertEquals(1, count(array_keys($names, 'mw-a')));
    }

    /** @test */
    public function testMissingDependencySkippedSilently() {
        $uri = new RouterUri('https://example.com/test', '');
        $uri->addMiddleware(new MwOrphan()); // depends on mw-nonexistent

        $middleware = $uri->getMiddleware();
        $names = array_map(fn($mw) => $mw->getName(), $middleware);

        $this->assertContains('mw-orphan', $names);
        $this->assertNotContains('mw-nonexistent', $names);
        $this->assertCount(1, $middleware);
    }

    /** @test */
    public function testNoDependenciesUnchanged() {
        $uri = new RouterUri('https://example.com/test', '');
        $uri->addMiddleware(new MwA()); // no dependencies

        $middleware = $uri->getMiddleware();
        $this->assertCount(1, $middleware);
        $this->assertEquals('mw-a', $middleware[0]->getName());
    }
}
