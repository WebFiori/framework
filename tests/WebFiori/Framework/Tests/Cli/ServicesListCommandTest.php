<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\ServicesListCommand;
use WebFiori\Framework\Router\ServiceRouter;

class ServicesListCommandTest extends CLITestCase {
    protected function tearDown(): void {
        ServiceRouter::reset();
        parent::tearDown();
    }

    /** @test */
    public function testNoServicesDiscovered() {
        ServiceRouter::reset();
        $output = $this->executeMultiCommand([ServicesListCommand::class]);
        $outputStr = implode('', $output);

        $this->assertStringContainsString('No services discovered', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /** @test */
    public function testWithDiscoveredServices() {
        $dir = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'ServiceRouterFixtures';
        ServiceRouter::discover('WebFiori\\Tests\\ServiceRouterFixtures', '/apis', [], $dir);

        // Verify getDiscovered() has data (this is what the command reads)
        $discovered = ServiceRouter::getDiscovered();
        $this->assertArrayHasKey('orders', $discovered);
        $this->assertGreaterThanOrEqual(3, count($discovered));
    }
}
