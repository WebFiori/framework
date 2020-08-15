<?php
namespace webfiori\tests\entity\router;

use PHPUnit\Framework\TestCase;
use webfiori\entity\router\Router;
use webfiori\entity\router\RouterUri;
use webfiori\entity\Util;
/**
 * Description of RouterTest
 *
 * @author Eng.Ibrahim
 */
class RouterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        Router::removeAll();
        $this->assertEquals(0,count(Router::routes()));
    }
    /**
     * @test
     */
    public function testAddAPIRoute00() {
        $this->assertTrue(Router::api([
            'path' => '/call-api-00', 
            'route-to' => '/my-api.php']));
        $this->assertFalse(Router::view([
            'path' => '/call-api-00', 
            'route-to' => '/my-other-api.php']));
        $this->assertTrue(Router::view([
            'path' => '/call-api-01', 
            'route-to' => '/my-api.php']));
    }
    /**
     * @test
     */
    public function testAddClosureRoute00() {
        $c1 = function()
        {
        };
        $c2 = function()
        {
        };
        $this->assertTrue(Router::closure([
            'path' => '/call', 
            'route-to' => $c1
        ]));
        $this->assertFalse(Router::closure([
            'path' => '/call', 
            'route-to' => $c2
        ]));
        $this->assertTrue(Router::closure([
            'path' => '/call-2', 
            'route-to' => $c1
        ]));
        $this->assertFalse(Router::closure([
            'path' => '/call', 
            'route-to' => 'Not Func'
        ]));
    }
    /**
     * @test
     */
    public function testAddViewRoute00() {
        $this->assertTrue(Router::view([
            'path' => '/view-something',
            'route-to' => 'my-view.php']));
        $this->assertFalse(Router::view([
            'path' => '/view-something', 
            'route-to' => '/my-other-view.php']));
        $this->assertTrue(Router::view([
            'path' => '/view-something-2', 
            'route-to' => '/my-view.php']));
    }
    /**
     * @test
     */
    public function testRoute00() {
        Router::removeAll();
        Router::setOnNotFound(function()
        {
        });
        Router::closure([
            'path' => '{var-1}/{var-2}',
            'route-to' => function()
            {
            }
        ]);
        $obj = Router::getRouteUri();
        $this->assertNull($obj);
        Router::route(Util::getBaseURL().'/hello/world');
        $obj = Router::getRouteUri();
        $this->assertTrue($obj instanceof RouterUri);
        $this->assertEquals('hello',$obj->getUriVar('var-1'));
        $this->assertEquals('world',$obj->getUriVar('var-2'));
        $this->assertTrue(Router::getVarValue('var-2') == $obj->getUriVar('var-2'));
    }
    /**
     * @test
     */
    public function testRoute01() {
        Router::removeAll();
        Router::setOnNotFound(function()
        {
        });
        Router::closure([
            'path' => '{var-1}/{var-2}/{var-1}',
            'route-to' => function()
            {
            }
        ]);
        Router::route(Util::getBaseURL().'/hello/world/boy');
        $obj = Router::getRouteUri();
        $this->assertTrue($obj instanceof RouterUri);
        $this->assertEquals('boy',$obj->getUriVar('var-1'));
        $this->assertEquals('world',$obj->getUriVar('var-2'));
    }
}
