<?php
namespace webfiori\tests\entity\router;
use webfiori\entity\router\Router;
use PHPUnit\Framework\TestCase;
/**
 * Description of RouterTest
 *
 * @author Eng.Ibrahim
 */
class RouterTest extends TestCase{
    /**
     * @test
     */
    public function test00(){
        Router::removeAll();
        $this->assertEquals(0,count(Router::routes()));
    }
    /**
     * @test
     */
    public function testAddViewRoute00() {
        $this->assertTrue(Router::view('/view-something', '/my-view.php'));
        $this->assertFalse(Router::view('/view-something', '/my-other-view.php'));
        $this->assertTrue(Router::view('/view-something-2', '/my-view.php'));
    }
    /**
     * @test
     */
    public function testAddAPIRoute00() {
        $this->assertTrue(Router::api('/call-api-00', '/my-api.php'));
        $this->assertFalse(Router::view('/call-api-00', '/my-other-api.php'));
        $this->assertTrue(Router::view('/call-api-01', '/my-api.php'));
    }
    /**
     * @test
     */
    public function testAddClosureRoute00() {
        $c1 = function(){
            
        };
        $c2 = function(){
            
        };
        $this->assertTrue(Router::closure('/call', $c1));
        $this->assertFalse(Router::closure('/call', $c2));
        $this->assertTrue(Router::closure('/call-2', $c1));
        $this->assertFalse(Router::closure('/call', 'Not Func'));
    }
}
