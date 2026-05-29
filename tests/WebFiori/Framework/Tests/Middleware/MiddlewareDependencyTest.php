<?php

namespace WebFiori\Framework\Test\Middleware;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Framework\Middleware\MiddlewareManager;
use WebFiori\Framework\Router\RouterUri;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

class MiddlewareA extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('mw-a');
        $this->setPriority(10);
    }

    public function getDependencies(): array {
        return [];
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
    }
}

class MiddlewareB extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('mw-b');
        $this->setPriority(100);
    }

    public function getDependencies(): array {
        return ['mw-a']; // must run after A
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
    }
}

class MiddlewareC extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('mw-c');
        $this->setPriority(50);
    }

    public function getDependencies(): array {
        return ['mw-b']; // must run after B (which runs after A)
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
    }
}

class MiddlewareX extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('mw-x');
        $this->setPriority(200);
    }

    public function getDependencies(): array {
        return ['mw-y']; // circular: X depends on Y
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
    }
}

class MiddlewareY extends AbstractMiddleware {
    public function __construct() {
        parent::__construct('mw-y');
        $this->setPriority(200);
    }

    public function getDependencies(): array {
        return ['mw-x']; // circular: Y depends on X
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
    }
}

class MiddlewareDependencyTest extends TestCase {
    /**
     * @test
     */
    public function testDependencyOrderIsRespected() {
        $uri = new RouterUri('https://example.com/dep', '');
        // Add in wrong priority order: B has higher priority but depends on A
        $b = new MiddlewareB();
        $a = new MiddlewareA();
        MiddlewareManager::register($b);
        MiddlewareManager::register($a);
        $uri->addMiddleware('mw-b');
        $uri->addMiddleware('mw-a');

        $list = $uri->getMiddleware();
        $names = array_map(fn ($m) => $m->getName(), $list);

        // A must come before B regardless of priority
        $this->assertEquals(['mw-a', 'mw-b'], $names);
    }
    /**
     * @test
     */
    public function testChainedDependencies() {
        $uri = new RouterUri('https://example.com/chain', '');
        // C depends on B, B depends on A. Add in reverse.
        MiddlewareManager::register(new MiddlewareC());
        MiddlewareManager::register(new MiddlewareB());
        MiddlewareManager::register(new MiddlewareA());
        $uri->addMiddleware('mw-c');
        $uri->addMiddleware('mw-b');
        $uri->addMiddleware('mw-a');

        $list = $uri->getMiddleware();
        $names = array_map(fn ($m) => $m->getName(), $list);

        $this->assertEquals(['mw-a', 'mw-b', 'mw-c'], $names);
    }
    /**
     * @test
     */
    public function testPriorityUsedAsTiebreaker() {
        $uri = new RouterUri('https://example.com/tie', '');
        // A (priority 10) and C (priority 50) have no dependency on each other
        // but both are independent. C should come first due to higher priority.
        $a = new MiddlewareA(); // priority 10, no deps
        $c = new MiddlewareC(); // priority 50, depends on mw-b
        // Without B present, C's dependency on mw-b is unresolvable (ignored)
        // So A and C are independent — sorted by priority
        MiddlewareManager::register($a);
        $uri->addMiddleware('mw-a');

        // Create a standalone middleware with priority 50 and no deps
        $standalone = new class() extends AbstractMiddleware {
            public function __construct() {
                parent::__construct('mw-standalone');
                $this->setPriority(50);
            }

            public function after(Request $request, Response $response) {
            }

            public function afterSend(Request $request, Response $response) {
            }

            public function before(Request $request, Response $response) {
            }
        };
        MiddlewareManager::register($standalone);
        $uri->addMiddleware('mw-standalone');

        $list = $uri->getMiddleware();
        $names = array_map(fn ($m) => $m->getName(), $list);

        // Higher priority first when no dependency relationship
        $this->assertEquals(['mw-standalone', 'mw-a'], $names);
    }
    /**
     * @test
     */
    public function testCircularDependencyThrows() {
        $this->expectException(\WebFiori\Framework\Exceptions\RoutingException::class);
        $this->expectExceptionMessage('Circular middleware dependency');

        $uri = new RouterUri('https://example.com/circular', '');
        MiddlewareManager::register(new MiddlewareX());
        MiddlewareManager::register(new MiddlewareY());
        $uri->addMiddleware('mw-x');
        $uri->addMiddleware('mw-y');

        $uri->getMiddleware(); // triggers sort
    }
    /**
     * @test
     */
    public function testNoDependenciesFallsBackToPriority() {
        $uri = new RouterUri('https://example.com/prio', '');
        $a = new MiddlewareA(); // priority 10
        $b = new MiddlewareB(); // priority 100, depends on A

        // Only add A — no dependencies, just priority
        $standalone50 = new class() extends AbstractMiddleware {
            public function __construct() {
                parent::__construct('mw-p50');
                $this->setPriority(50);
            }

            public function after(Request $request, Response $response) {
            }

            public function afterSend(Request $request, Response $response) {
            }

            public function before(Request $request, Response $response) {
            }
        };

        MiddlewareManager::register($a);
        MiddlewareManager::register($standalone50);
        $uri->addMiddleware('mw-a');
        $uri->addMiddleware('mw-p50');

        $list = $uri->getMiddleware();
        $names = array_map(fn ($m) => $m->getName(), $list);

        // p50 has higher priority, no deps → comes first
        $this->assertEquals(['mw-p50', 'mw-a'], $names);
    }
    /**
     * @test
     */
    public function testEmptyMiddlewareList() {
        $uri = new RouterUri('https://example.com/empty', '');
        $list = $uri->getMiddleware();
        $this->assertEmpty($list);
    }
}
