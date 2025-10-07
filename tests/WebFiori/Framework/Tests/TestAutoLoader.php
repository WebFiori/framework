<?php
namespace WebFiori\Framework\Test;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Autoload\ClassLoader;
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
        $this->assertEquals(ROOT_PATH, ClassLoader::root());
    }
    /**
     * @test
     */
    public function test01() {
        $cArr = explode('\\', 'WebFiori\\Framework\\Autoload\\ClassLoader');
        $className = $cArr[count($cArr) - 1];
        $classNs = implode('\\', array_slice($cArr, 0, count($cArr) - 1));

        $isLoaded = ClassLoader::isLoaded($className, $classNs);
        $this->assertTrue($isLoaded);
    }
    /**
     * @test
     */
    public function test02() {
        $isLoaded = ClassLoader::isLoaded('ClassLoader');
        $this->assertTrue($isLoaded);
    }
}
