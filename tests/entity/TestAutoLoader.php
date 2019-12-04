<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\AutoLoader;
/**
 * Description of TestAutoLoader
 *
 * @author Ibrahim
 */
class TestAutoLoader extends TestCase{
    /**
     * @test
     */
    public function test00() {
        $this->assertEquals(ROOT_DIR, AutoLoader::root());
    }
    /**
     * @test
     */
    public function test01() {
        $isLoaded = AutoLoader::isLoaded('webfiori\\entity\\AutoLoader');
        $this->assertTrue($isLoaded);
    }
}
