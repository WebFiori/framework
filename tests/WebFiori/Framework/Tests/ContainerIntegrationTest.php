<?php

namespace WebFiori\Framework\Test;

use PHPUnit\Framework\TestCase;
use WebFiori\Container\Container;
use WebFiori\Framework\App;
use WebFiori\Framework\Middleware\MiddlewareRegistry;
use WebFiori\Framework\Router\Router;
use WebFiori\Framework\Scheduler\TasksManager;
use WebFiori\Framework\Session\SessionManager;

class ContainerIntegrationTest extends TestCase {
    /**
     * @test
     */
    public function testContainerReturnsInstance() {
        $container = App::container();
        $this->assertInstanceOf(Container::class, $container);
    }
    /**
     * @test
     */
    public function testSessionManagerRegistered() {
        $manager = App::container()->make(SessionManager::class);
        $this->assertInstanceOf(SessionManager::class, $manager);
    }
    /**
     * @test
     */
    public function testMiddlewareRegistryRegistered() {
        $registry = App::container()->make(MiddlewareRegistry::class);
        $this->assertInstanceOf(MiddlewareRegistry::class, $registry);
    }
    /**
     * @test
     */
    public function testRouterRegistered() {
        $router = App::container()->make(Router::class);
        $this->assertInstanceOf(Router::class, $router);
    }
    /**
     * @test
     */
    public function testTasksManagerRegistered() {
        $tasks = App::container()->make(TasksManager::class);
        $this->assertInstanceOf(TasksManager::class, $tasks);
    }
    /**
     * @test
     */
    public function testCustomBinding() {
        App::container()->bind('test-key', function () {
            return new \stdClass();
        });
        $obj = App::container()->make('test-key');
        $this->assertInstanceOf(\stdClass::class, $obj);
    }
}
