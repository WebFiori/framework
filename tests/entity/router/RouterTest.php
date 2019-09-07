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
        $this->assertTrue(Router::view([
            'path'=>'/view-something',
            'route-to'=>'my-view.php']));
        $this->assertFalse(Router::view([
            'path'=>'/view-something', 
            'route-to'=>'/my-other-view.php']));
        $this->assertTrue(Router::view([
            'path'=>'/view-something-2', 
            'route-to'=>'/my-view.php']));
    }
    /**
     * @test
     */
    public function testAddAPIRoute00() {
        $this->assertTrue(Router::api([
            'path'=>'/call-api-00', 
            'route-to'=>'/my-api.php']));
        $this->assertFalse(Router::view([
            'path'=>'/call-api-00', 
            'route-to'=>'/my-other-api.php']));
        $this->assertTrue(Router::view([
            'path'=>'/call-api-01', 
            'route-to'=>'/my-api.php']));
    }
    /**
     * @test
     */
    public function testAddClosureRoute00() {
        $c1 = function(){
            
        };
        $c2 = function(){
            
        };
        $this->assertTrue(Router::closure([
            'path'=>'/call', 
            'route-to'=>$c1
        ]));
        $this->assertFalse(Router::closure([
            'path'=>'/call', 
            'route-to'=>$c2
        ]));
        $this->assertTrue(Router::closure([
            'path'=>'/call-2', 
            'route-to'=>$c1
        ]));
        $this->assertFalse(Router::closure([
            'path'=>'/call', 
            'route-to'=>'Not Func'
        ]));
    }
}
