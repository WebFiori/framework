<?php
namespace WebFiori\Framework\Test;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\App;
use WebFiori\Http\AbstractWebService;

class AutoRegisterTest extends TestCase {
    /**
     * @test
     * Test that attribute-based services (using #[RestController]) are
     * correctly discovered and registered by autoRegister().
     * This is the fix for issue #313.
     */
    public function testAutoRegisterAttributedService() {
        $registered = [];
        App::autoRegister('Apis/AttributedTest', function (AbstractWebService $ws) use (&$registered)
        {
            $registered[] = $ws;
        }, 'Service');

        $this->assertCount(1, $registered, 'Expected one attributed service to be registered.');
        $this->assertEquals('attributed-service', $registered[0]->getName());
        $this->assertEquals('A service registered via attribute', $registered[0]->getDescription());
    }

    /**
     * @test
     * Reproduces the exact bug from issue #313: registerServices() passes
     * [$this] (a manager object) as constructor params. For attribute-based
     * services, the constructor expects string, not an object. The fix should
     * detect the type mismatch and instantiate with no args instead.
     */
    public function testAutoRegisterAttributedServiceWithIncompatibleConstructorArgs() {
        $managerMock = new \stdClass();
        $registered = [];

        App::autoRegister('Apis/AttributedTest2', function (AbstractWebService $ws) use (&$registered)
        {
            $registered[] = $ws;
        }, 'Service', [$managerMock]);

        $this->assertCount(1, $registered, 'Attributed service should be registered even when incompatible constructor args are passed.');
        $this->assertEquals('attributed-service-2', $registered[0]->getName());
    }

    /**
     * @test
     * Test that constructor-based services still work when suffix matches.
     */
    public function testAutoRegisterConstructorBasedServiceWithSuffix() {
        $registered = [];
        App::autoRegister('tests/Apis/Multiple', function (AbstractWebService $ws) use (&$registered)
        {
            $registered[] = $ws;
        }, 'Service');

        $this->assertCount(0, $registered);
    }
}
