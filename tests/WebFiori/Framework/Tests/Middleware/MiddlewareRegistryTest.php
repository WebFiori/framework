<?php

namespace WebFiori\Framework\Test\Middleware;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Middleware\MiddlewareRegistry;
use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Http\Request;
use WebFiori\Http\Response;

class DummyMiddleware extends AbstractMiddleware {
    public function __construct(string $name = 'dummy') {
        parent::__construct($name);
    }

    public function after(Request $request, Response $response) {
    }

    public function afterSend(Request $request, Response $response) {
    }

    public function before(Request $request, Response $response) {
    }
}

class MiddlewareRegistryTest extends TestCase {
    /**
     * @test
     */
    public function testRegisterAndGet() {
        $registry = new MiddlewareRegistry();
        $mw = new DummyMiddleware('test-mw');
        $registry->register($mw);

        $found = $registry->getMiddleware('test-mw');
        $this->assertSame($mw, $found);
    }
    /**
     * @test
     */
    public function testGetMiddlewareNotFound() {
        $registry = new MiddlewareRegistry();
        $this->assertNull($registry->getMiddleware('nonexistent'));
    }
    /**
     * @test
     */
    public function testRegisterByClassName() {
        $registry = new MiddlewareRegistry();
        $result = $registry->register(DummyMiddleware::class);
        $this->assertTrue($result);
        $this->assertNotNull($registry->getMiddleware('dummy'));
    }
    /**
     * @test
     */
    public function testRegisterInvalidReturnsFalse() {
        $registry = new MiddlewareRegistry();
        $result = $registry->register('NonExistentClass');
        $this->assertFalse($result);
    }
    /**
     * @test
     */
    public function testGetGroup() {
        $registry = new MiddlewareRegistry();
        $mw1 = new DummyMiddleware('mw1');
        $mw1->addToGroup('api');
        $mw2 = new DummyMiddleware('mw2');
        $mw2->addToGroup('api');
        $mw3 = new DummyMiddleware('mw3');
        $mw3->addToGroup('web');

        $registry->register($mw1);
        $registry->register($mw2);
        $registry->register($mw3);

        $apiGroup = $registry->getGroup('api');
        $this->assertCount(2, $apiGroup);

        $webGroup = $registry->getGroup('web');
        $this->assertCount(1, $webGroup);

        $emptyGroup = $registry->getGroup('nonexistent');
        $this->assertEmpty($emptyGroup);
    }
    /**
     * @test
     */
    public function testRemove() {
        $registry = new MiddlewareRegistry();
        $registry->register(new DummyMiddleware('to-remove'));
        $registry->remove('to-remove');
        $this->assertNull($registry->getMiddleware('to-remove'));
    }
    /**
     * @test
     */
    public function testReset() {
        $registry = new MiddlewareRegistry();
        $registry->register(new DummyMiddleware('a'));
        $registry->register(new DummyMiddleware('b'));
        $registry->reset();
        $this->assertEmpty($registry->getAll());
    }
    /**
     * @test
     */
    public function testGetAll() {
        $registry = new MiddlewareRegistry();
        $registry->register(new DummyMiddleware('x'));
        $registry->register(new DummyMiddleware('y'));
        $this->assertCount(2, $registry->getAll());
    }
}
