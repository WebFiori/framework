<?php
namespace webfiori\tests\entity\router;

use PHPUnit\Framework\TestCase;
use webfiori\framework\router\Router;
use webfiori\framework\router\RouterUri;
use webfiori\framework\Util;
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
    public function testRoutesGroup00() {
        Router::removeAll();
        Router::page([
            'path' => 'users',
            'case-sensitive' => false,
            'middleware' => 'M1',
            'languages' => ['EN'],
            'methods' => 'post',
            'routes' => [
                [
                    'path' => 'view-user/{user-id}',
                    'route-to' => 'ViewUserPage.php',
                    'languages' => ['AR']
                ],
                [
                    'path' => 'get-users',
                    'languages' => ['AR'],
                    'case-sensitive' => true,
                    'routes' => [
                        [
                            'path' => 'by-name',
                            'route-to' => 'GetUserByName.php',
                            'languages' => ['FR'],
                            'case-sensitive' => false,
                        ],
                        [
                            'path' => 'by-email',
                            'route-to' => 'GetUserByEmail.php'
                        ]
                    ],
                ],
                [
                    'path' => '/',
                    'route-to' => 'ListUsers.php',
                    'case-sensitive' => true,
                    'methods' => ['options', 'get']
                ]
            ]
        ]);
        $this->assertTrue(Router::hasRoute('users'));
        $this->assertTrue(Router::hasRoute('users/view-user/{user-id}'));
        
        $route2 = Router::getUriObj('/users/view-user/{user-id}');
        $this->assertEquals('ViewUserPage.php', $route2->getRouteTo());
        $this->assertFalse($route2->isCaseSensitive());
        $this->assertEquals(['ar', 'en'], $route2->getLanguages());
        $this->assertEquals(['POST'], $route2->getRequestMethods());
        
        $route = Router::getUriObj('/users');
        $this->assertEquals('ListUsers.php', $route->getRouteTo());
        $this->assertEquals(['en'], $route->getLanguages());
        $this->assertEquals(['OPTIONS','GET', 'POST'], $route->getRequestMethods());
        $this->assertTrue($route->isCaseSensitive());
        
        $route3 = Router::getUriObj('/users/get-users/by-name');
        $this->assertEquals('GetUserByName.php', $route3->getRouteTo());
        
        $this->assertEquals(['fr','ar','en'], $route3->getLanguages());
        $this->assertFalse($route3->isCaseSensitive());
        
        
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
