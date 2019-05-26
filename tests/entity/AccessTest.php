<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\Access;
/**
 * A test class for testing the class 'webfiori\entity\Access'.
 *
 * @author Ibrahim
 */
class AccessTest extends TestCase{
    
    public function test00() {
        $this->assertEquals(0, count(Access::groups()));
        $this->assertEquals(0, count(Access::privileges()));
    }
}
