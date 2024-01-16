<?php
namespace webfiori\framework\test\router;

use PHPUnit\Framework\TestCase;
use webfiori\framework\router\RouteOption;
use webfiori\framework\router\Router;
use webfiori\framework\router\RouterUri;
use webfiori\framework\Util;
use webfiori\http\RequestMethod;
/**
 * Description of RouterTest
 *
 * @author Ibrahim
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
            RouteOption::PATH => '/call-api-00',
            RouteOption::TO => '/my-api.php']));
        $this->assertFalse(Router::page([
            RouteOption::PATH => '/call-api-00',
            RouteOption::TO => '/my-other-api.php']));
        $this->assertTrue(Router::page([
            RouteOption::PATH => '/call-api-01',
            RouteOption::TO => '/my-api.php']));
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
            RouteOption::PATH => '/call',
            RouteOption::TO => $c1
        ]));
        $this->assertFalse(Router::closure([
            RouteOption::PATH => '/call',
            RouteOption::TO => $c2
        ]));
        $this->assertTrue(Router::closure([
            RouteOption::PATH => '/call-2',
            RouteOption::TO => $c1
        ]));
        $this->assertFalse(Router::closure([
            RouteOption::PATH => '/call',
            RouteOption::TO => 'Not Func'
        ]));
    }
    /**
     * @test
     */
    public function testAddViewRoute00() {
        $this->assertTrue(Router::page([
            RouteOption::PATH => '/view-something',
            RouteOption::TO => 'my-view.php']));
        $this->assertFalse(Router::page([
            RouteOption::PATH => '/view-something',
            RouteOption::TO => '/my-other-view.php']));
        $this->assertTrue(Router::page([
            RouteOption::PATH => '/view-something-2',
            RouteOption::TO => '/my-view.php']));
    }
    /**
     * @test
     */
    public function testOptionalParam00() {
        Router::removeAll();
        Router::setOnNotFound(function()
        {
        });
        Router::closure([
            RouteOption::PATH => '{var-1}/{var-2?}',
            RouteOption::TO => function()
            {
            },
            RouteOption::VALUES => [
                'var-1' => [
                    'hello'
                ]
            ]
        ]);
        $obj = Router::getUriObj('/{var-1}/{var-2?}');
        $this->assertNotNull($obj);

        $this->assertEquals(2, count($obj->getParameters()));
        Router::route(Util::getBaseURL().'/hello/world');

        $this->assertEquals('hello',$obj->getParameterValue('var-1'));
        $this->assertEquals('world',$obj->getParameterValue('var-2'));
    }
    /**
     * @test
     */
    public function testOptionalParam01() {
        Router::removeAll();

        Router::closure([
            RouteOption::PATH => '{var-1}/{var-2?}',
            RouteOption::TO => function()
            {
            }
        ]);

        Router::route(Util::getBaseURL().'/hello');
        $obj = Router::getRouteUri();
        $this->assertNotNull($obj);
        $this->assertEquals('hello',$obj->getParameterValue('var-1'));
        $this->assertEquals('hello', Router::getParameterValue('var-1'));
        $this->assertNull($obj->getParameterValue('var-2'));
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
            RouteOption::PATH => '{var-1}/{var-2}',
            RouteOption::TO => function()
            {
            }
        ]);
        $obj = Router::getRouteUri();
        $this->assertNull($obj);
        Router::route(Util::getBaseURL().'/hello/world');
        $obj = Router::getRouteUri();
        $this->assertTrue($obj instanceof RouterUri);
        $this->assertEquals('hello',$obj->getParameterValue('var-1'));
        $this->assertEquals('world',$obj->getParameterValue('var-2'));
        $this->assertTrue(Router::getParameterValue('var-2') == $obj->getParameterValue('var-2'));
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
            RouteOption::PATH => '{var-1}/{var-2}/{var-1}',
            RouteOption::TO => function()
            {
            }
        ]);
        Router::route(Util::getBaseURL().'/hello/world/boy');
        $obj = Router::getRouteUri();
        $this->assertTrue($obj instanceof RouterUri);
        $this->assertEquals('boy',$obj->getParameterValue('var-1'));
        $this->assertEquals('world',$obj->getParameterValue('var-2'));
    }
    /**
     * @test
     */
    public function testRoutesGroup00() {
        Router::removeAll();
        Router::page([
            RouteOption::PATH => 'users',
            RouteOption::CASE_SENSITIVE => false,
            RouteOption::MIDDLEWARE => 'M1',
            RouteOption::LANGS => ['EN'],
            RouteOption::REQUEST_METHODS => RequestMethod::POST,
            RouteOption::SUB_ROUTES => [
                [
                    RouteOption::PATH => 'view-user/{user-id}',
                    RouteOption::TO => 'ViewUserPage.php',
                    RouteOption::LANGS => ['AR']
                ],
                [
                    RouteOption::PATH => 'get-users',
                    RouteOption::LANGS => ['AR'],
                    RouteOption::CASE_SENSITIVE => true,
                    RouteOption::SUB_ROUTES => [
                        [
                            RouteOption::PATH => 'by-name',
                            RouteOption::TO => 'GetUserByName.php',
                            RouteOption::LANGS => ['FR'],
                            RouteOption::CASE_SENSITIVE => false,
                        ],
                        [
                            RouteOption::PATH => 'by-email',
                            RouteOption::TO => 'GetUserByEmail.php'
                        ]
                    ],
                ],
                [
                    RouteOption::PATH => '/',
                    RouteOption::TO => 'ListUsers.php',
                    RouteOption::CASE_SENSITIVE => true,
                    RouteOption::REQUEST_METHODS => [RequestMethod::OPTIONS, RequestMethod::GET]
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
}
