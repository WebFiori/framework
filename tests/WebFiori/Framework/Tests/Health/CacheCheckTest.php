<?php
namespace WebFiori\Framework\Test\Health;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Health\Checks\CacheCheck;
use WebFiori\Framework\Health\HealthCheckResult;

class CacheCheckTest extends TestCase {
    public function testGetName() {
        $check = new CacheCheck();
        $this->assertEquals('cache', $check->getName());
    }
    public function testCheck() {
        $check = new CacheCheck();
        $result = $check->check();
        $this->assertInstanceOf(HealthCheckResult::class, $result);
        // Either ok or fail depending on cache availability
        $this->assertContains($result->getStatus(), ['ok', 'fail']);
    }
}
