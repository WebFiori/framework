<?php
namespace webfiori\framework\test;

use PHPUnit\Framework\TestCase;
use webfiori\framework\AutoLoader;
/**
 * Description of TestAutoLoader
 *
 * @author Ibrahim
 */
class TestAutoLoader extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $this->assertEquals(ROOT_PATH, AutoLoader::root());
    }
    /**
     * @test
     */
    public function test01() {
        $cArr = explode('\\', 'webfiori\\entity\\AutoLoader');
        $className = $cArr[count($cArr) - 1];
        $classNs = implode('\\', array_slice($cArr, 0, count($cArr) - 1));
        
        $isLoaded = AutoLoader::isLoaded($className, $classNs);
        $this->assertTrue($isLoaded);
    }
    /**
     * @test
     */
    public function test02() {
        $isLoaded = AutoLoader::isLoaded('AutoLoader');
        $this->assertTrue($isLoaded);
    }
}
