<?php

namespace WebFiori\Framework\Test\Health;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Health\HealthCheck;
use WebFiori\Framework\Health\HealthCheckInterface;
use WebFiori\Framework\Health\HealthCheckResult;

class PassingCheck implements HealthCheckInterface {
    public function getName(): string {
        return 'passing';
    }

    public function check(): HealthCheckResult {
        return HealthCheckResult::ok(['latency_ms' => 5]);
    }
}

class FailingCheck implements HealthCheckInterface {
    public function getName(): string {
        return 'failing';
    }

    public function check(): HealthCheckResult {
        return HealthCheckResult::fail('Connection refused');
    }
}

class HealthCheckTest extends TestCase {
    protected function setUp(): void {
        HealthCheck::reset();
    }
    /**
     * @test
     */
    public function testRunAllWithNoChecks() {
        $result = HealthCheck::runAll();
        $this->assertEquals('ok', $result['status']);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertEmpty($result['checks']);
    }
    /**
     * @test
     */
    public function testRegisterClassBased() {
        HealthCheck::register(new PassingCheck());
        $this->assertEquals(1, HealthCheck::getCheckCount());
    }
    /**
     * @test
     */
    public function testRegisterCallable() {
        HealthCheck::register('disk', function () {
            return ['status' => 'ok', 'free_gb' => 50];
        });
        $this->assertEquals(1, HealthCheck::getCheckCount());
    }
    /**
     * @test
     */
    public function testAllPassingReturnsOk() {
        HealthCheck::register(new PassingCheck());
        HealthCheck::register('custom', function () {
            return ['status' => 'ok'];
        });

        $result = HealthCheck::runAll();
        $this->assertEquals('ok', $result['status']);
        $this->assertCount(2, $result['checks']);
        $this->assertEquals('ok', $result['checks']['passing']['status']);
    }
    /**
     * @test
     */
    public function testFailingCheckReturnsFail() {
        HealthCheck::register(new PassingCheck());
        HealthCheck::register(new FailingCheck());

        $result = HealthCheck::runAll();
        $this->assertEquals('fail', $result['status']);
        $this->assertEquals('ok', $result['checks']['passing']['status']);
        $this->assertEquals('fail', $result['checks']['failing']['status']);
        $this->assertEquals('Connection refused', $result['checks']['failing']['reason']);
    }
    /**
     * @test
     */
    public function testCallableReturningResult() {
        HealthCheck::register('result-check', function () {
            return HealthCheckResult::ok(['version' => '2.0']);
        });

        $result = HealthCheck::runAll();
        $this->assertEquals('ok', $result['checks']['result-check']['status']);
        $this->assertEquals('2.0', $result['checks']['result-check']['version']);
    }
    /**
     * @test
     */
    public function testReset() {
        HealthCheck::register(new PassingCheck());
        HealthCheck::reset();
        $this->assertEquals(0, HealthCheck::getCheckCount());
    }
    /**
     * @test
     */
    public function testResultOk() {
        $r = HealthCheckResult::ok(['latency' => 10]);
        $this->assertEquals('ok', $r->getStatus());
        $this->assertNull($r->getReason());
        $this->assertEquals(['latency' => 10], $r->getMeta());
    }
    /**
     * @test
     */
    public function testResultFail() {
        $r = HealthCheckResult::fail('timeout', ['ms' => 5000]);
        $this->assertEquals('fail', $r->getStatus());
        $this->assertEquals('timeout', $r->getReason());
        $this->assertEquals(['ms' => 5000], $r->getMeta());
    }
    /**
     * @test
     */
    public function testResultToArray() {
        $r = HealthCheckResult::fail('down');
        $arr = $r->toArray();
        $this->assertEquals('fail', $arr['status']);
        $this->assertEquals('down', $arr['reason']);
    }
}
