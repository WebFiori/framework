<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Router;

use Error;
use Exception;
use WebFiori\Cli\Runner;
use WebFiori\File\File;
use WebFiori\Framework\App;
use WebFiori\Framework\Ui\HTTPCodeView;
use WebFiori\Http\Uri;
use WebFiori\Http\WebServicesManager;
use WebFiori\Json\Json;
use WebFiori\Ui\HTMLNode;
/**
 * The basic class that is used to route user requests to the correct
 * location.
 *
 * The developer can use this class to create a readable URIs to system resources.
 * In general, there are 4 types of routes:
 * <ul>
 * <li>View route.</li>
 * <li>API route.</li>
 * <li>Closure route.</li>
 * <li>Custom route.</li>
 * </ul>
 * <p>
 * A view route is a route that points to a file which exist inside the directory
 * 'app/pages'. They are simply web pages (HTML or PHP).
 * </p>
 * <p>An API route is a route that points to a file which exist inside the
 * directory 'app/apis'. This folder usually contains PHP files which extends
 * the class 'ExtendedWebServicesManager' or the class 'WebServicesManager'.
 * </p>
 * <p>
 * A closure route is simply a function that will be executed when the
 * user visits the URL.
 * </p>
 * <p>
 * A customized route is a route that can point to any file which exist inside
 * the framework scope. For example, The developer might create a folder
 * 'my-files' and inside it, he might add 'my-view.html'. Then he can add a route
 * to it as follows:
 * <pre>
 * Router::addRoute([<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;RouteOption::PATH => '/custom-route',<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;RouteOption::TO => '/my-files/my-view.html'<br/>
 * ]);
 * </pre>
 * </p>
 * <p>
 * In addition to creating routes using files, it is possible to have routes which
 * points to classes as follows:
 * <pre>
 * Router::addRoute([<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;RouteOption::PATH => '/custom-route',<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;RouteOption::TO => MyClass::class<br/>
 * ]);
 * </pre>
 * </p>
 * @author Ibrahim
 */
class Router {
    /**
     * A constant that represents API route. It is simply the root directory where APIs
     * should be created.
     *
     * @since 1.0
     */
    const API_ROUTE = DS.APP_DIR.DS.'apis';
    /**
     * A constant that represents closure route. The value of the
     * constant is 'func'.
     *
     * @since 1.0
     */
    const CLOSURE_ROUTE = 'func';
    /**
     * A constant for custom directory route.
     *
     * @since 1.0
     */
    const CUSTOMIZED = DS;
    /**
     * A constant that represents view route. It is simply the root directory where web
     * pages should be created.
     *
     * @since 1.0
     */
    const VIEW_ROUTE = DS.APP_DIR.DS.'pages';
    /**
     *
     * @var type
     *
     * @since 1.1
     */
    private $baseUrl;
    /**
     * @var RouteBuilder
     */
    private $builder;
    /**
     * @var RouteDispatcher
     */
    private $dispatcher;
    /**
     * @var RouteMatcher
     */
    private $matcher;
    /**
     * A callback function to call in case if a rout is
     * not found.
     *
     * @var callable
     *
     * @since 1.0
     */
    private $onNotFound;
    /**
     * A single instance of the router.
     *
     * @var Router
     *
     * @since 1.0
     */
    private static $router;
    /**
     * An array which contains an objects of type 'RouteUri'.
     *
     * @var array
     *
     * @since 1.0
     */
    private $routes;
    /**
     * An object of type 'RouterUri' which represents the route that the
     * user was sent to.
     *
     * @var RouterUri
     *
     * @since 1.3.5
     */
    private $uriObj;
    /**
     * Creates new instance of 'Router'
     *
     * @since 1.0
     */
    private function __construct() {
        $this->routes = [
            'static' => [],
            'variable' => []
        ];
        !defined('DS') ? define('DS', DIRECTORY_SEPARATOR) : '';
        $this->onNotFound = function ()
        {
            App::getResponse()->setCode(404);

            if (!defined('API_CALL')) {
                $notFoundView = new HTTPCodeView(404);
                $notFoundView->render();
            } else {
                $json = new Json([
                    'message' => 'Requested resource was not found.',
                    'type' => 'error'
                ]);
                App::getResponse()->write($json);
            }
        };

        $this->baseUrl = trim(Uri::getBaseURL(), '/');
        $this->builder = new RouteBuilder($this);
        $this->matcher = new RouteMatcher($this);
        $this->dispatcher = new RouteDispatcher($this);
    }

    /**
     * Returns a reference to the routes array.
     *
     * @return array
     */
    public function &getRoutesRef(): array {
        return $this->routes;
    }

    /**
     * Adds new route to a file inside the root folder.
     *
     * @param array $options An associative array of options.
     *
     * @return bool The method will return true if the route was created.
     * If a route for the given path was already created, the method will return
     * false.
     *
     * @since 1.2
     */
    public static function addRoute(array $options) : bool {
        $options[RouteOption::TYPE] = Router::CUSTOMIZED;

        return Router::getInstance()->getBuilder()->addRoute($options);
    }
    /**
     * Adds new route to a web services set.
     *
     * Note that the route which created using this method will be added to
     * 'global' and 'api' middleware groups.
     *
     * @param array $options An associative array that contains route options.
     *
     * @return bool The method will return true if the route was created.
     * If a route for the given path was already created, the method will return
     * false.
     *
     * @since 1.2
     */
    public static function api(array $options) : bool {
        $options[RouteOption::TYPE] = Router::API_ROUTE;
        RouteBuilder::addToMiddlewareGroup($options, 'api');

        return Router::getInstance()->getBuilder()->addRoute($options);
    }
    /**
     * Returns the base URI which is used to create routes.
     *
     * @return string The base URL which is used to create routes.
     *
     * @since 1.3.1
     */
    public static function base() : string {
        return Router::getInstance()->getBase();
    }

    /**
     * Adds new closure route.
     *
     * Note that the route which created using this method will be added to
     * 'global' and 'closure' middleware groups.
     *
     * @param array $options An associative array that contains route options.
     *
     * @return bool The method will return true if the route was created.
     * If a route for the given path was already created, the method will return
     * false. Also, if <b>'route-to'</b> is not a function, the method will return false.
     *
     * @since 1.2
     */
    public static function closure(array $options) : bool {
        $options[RouteOption::TYPE] = Router::CLOSURE_ROUTE;
        RouteBuilder::addToMiddlewareGroup($options, 'closure');

        return Router::getInstance()->getBuilder()->addRoute($options);
    }
    /**
     * Returns the value of the base URI which is appended to the path.
     *
     * @return string
     *
     * @since 1.0
     */
    public static function getBase() : string {
        return self::getInstance()->baseUrl;
    }

    /**
     * Returns the RouteBuilder instance.
     *
     * @return RouteBuilder
     */
    public function getBuilder(): RouteBuilder {
        return $this->builder;
    }

    /**
     * Returns the RouteDispatcher instance.
     *
     * @return RouteDispatcher
     */
    public function getDispatcher(): RouteDispatcher {
        return $this->dispatcher;
    }

    /**
     * Creates and Returns a single instance of the router.
     *
     * @return Router
     *
     * @since 1.0
     */
    public static function getInstance(): Router {
        if (self::$router != null) {
            return self::$router;
        }
        self::$router = new Router();

        return self::$router;
    }

    /**
     * Returns the RouteMatcher instance.
     *
     * @return RouteMatcher
     */
    public function getMatcher(): RouteMatcher {
        return $this->matcher;
    }

    /**
     * Returns the on-not-found callback.
     *
     * @return callable
     */
    public function getOnNotFound(): callable {
        return $this->onNotFound;
    }
    /**
     * Returns the value of a parameter which exist in the path part of the
     * URI.
     *
     * @param string $varName The name of the parameter.
     *
     * @return string|null The method will return the value of the
     * parameter if it was set. If it is not set or routing is still not yet
     * happened, the method will return null.
     *
     * @since 1.3.9
     */
    public static function getParameterValue(string $varName) {
        $routeUri = self::getRouteUri();

        if ($routeUri instanceof RouterUri) {
            return $routeUri->getParameterValue($varName);
        }

        return null;
    }
    /**
     * Returns an object of type 'RouterUri' which contains route information.
     *
     * @return RouterUri|null An object which has route information. If the
     * method 'Router::route()' is not yet called or no route was found,
     * the method will return null.
     *
     * @since 1.3.5
     */
    public static function getRouteUri() {
        return self::getInstance()->uriObj;
    }
    /**
     * Returns an object of type 'RouterUri' that represents route URI.
     *
     * @param string $path The path part of the URI.
     *
     * @return RouterUri|null If a route was found which has the given path,
     * an object of type 'RouterUri' is returned. If no route is found, null
     * is returned.
     *
     * @since 1.3.3
     */
    public static function getUriObj(string $path) {
        return self::getInstance()->getMatcher()->getUriObj($path);
    }
    /**
     * Returns an object of type 'RouterUri' which contains URL route information.
     *
     * @param string $url A string that represents a URL.
     *
     * @return RouterUri|null If a resource was found which has the given route, an
     * object of type RouterUri is returned. Other than that, null is returned.
     *
     * @since 1.3.6
     */
    public static function getUriObjByURL(string $url) {
        try {
            self::getInstance()->getMatcher()->resolveUrl($url, false);

            return self::getRouteUri();
        } catch (Error $ex) {
            return null;
        } catch (Exception $ex) {
            return null;
        }
    }
    /**
     * Checks if a given path has a route or not.
     *
     * @param string $path The path which will be checked (such as '/path1/path2')
     *
     * @return bool The method will return true if the given path
     * has a route.
     *
     * @since 1.3.8
     */
    public static function hasRoute(string $path): bool {
        $routesArr = self::getInstance()->routes;
        $trimmed = self::getInstance()->getBuilder()->fixUriPath($path);

        return isset($routesArr['static'][$trimmed]) || isset($routesArr['variable'][$trimmed]);
    }
    /**
     * Adds a route to a basic xml site map.
     *
     * @since 1.3.2
     */
    public static function incSiteMapRoute() {
        $sitemapFunc = function()
        {
            $urlSet = new HTMLNode('urlset');
            $urlSet->setIsQuotedAttribute(true);
            $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9')
                ->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
            $routes = Router::getInstance()->getRoutesRef();

            foreach ($routes['static'] as $route) {
                if ($route->isInSiteMap()) {
                    $nodes = $route->getSitemapNodes();

                    foreach ($nodes as $node) {
                        $urlSet->addChild($node);
                    }
                }
            }

            foreach ($routes['variable'] as $route) {
                if ($route->isInSiteMap()) {
                    $nodes = $route->getSitemapNodes();

                    foreach ($nodes as $node) {
                        $urlSet->addChild($node);
                    }
                }
            }
            $retVal = '<?xml version="1.0" encoding="UTF-8"?>';
            $retVal .= $urlSet->toHTML();
            App::getResponse()->write($retVal);
            App::getResponse()->addHeader('content-type','text/xml');
        };
        self::closure([
            RouteOption::PATH => '/sitemap.xml',
            RouteOption::TO => $sitemapFunc,
            RouteOption::SITEMAP => true,
            RouteOption::CACHE_DURATION => 86400
        ]);
        self::closure([
            RouteOption::PATH => '/sitemap',
            RouteOption::TO => $sitemapFunc,
            RouteOption::SITEMAP => true,
            RouteOption::CACHE_DURATION => 86400
        ]);
    }
    /**
     * Call the closure which was set if a route is not found.
     *
     * @since 1.3.10
     */
    public static function notFound() {
        call_user_func(self::getInstance()->onNotFound);
    }
    /**
     * Adds new route to a web page.
     *
     * Note that the route which created using this method will be added to
     * 'global' and 'web' middleware groups.
     *
     * @param array $options An associative array that contains route options.
     *
     * @return bool The method will return true if the route was created.
     * If a route for the given path was already created, the method will return
     * false.
     *
     * @since 1.3.12
     */
    public static function page(array $options) : bool {
        return self::view($options);
    }
    /**
     * Adds a redirect route.
     *
     * @param string $path The path at which when the user visits will be redirected.
     * @param string $to A path or a URL at which the user will be sent to.
     * @param int $code HTTP redirect code. Default is 301.
     *
     * @since 1.3.11
     */
    public static function redirect(string $path, string $to, int $code = 301) {
        self::removeRoute($path);
        Router::closure([
            'path' => $path,
            'route-to' => function ($to, $httpCode)
            {
                $allowedCodes = [301, 302, 303, 307, 308];

                if (!in_array($httpCode, $allowedCodes)) {
                    $httpCode = 301;
                }
                $requestedUri = Router::getRouteUri()->getRequestedUri();

                if ($requestedUri !== null && strlen($requestedUri->getQueryString()) > 0) {
                    $to .= '?'.$requestedUri->getQueryString();
                }
                App::getResponse()->addHeader('location', $to);
                App::getResponse()->setCode($httpCode);

                if (!Runner::isCLI()) {
                    App::getResponse()->send();
                }
            },
            'closure-params' => [
                $to, $code
            ]
        ]);
    }
    /**
     * Remove all routes which has been added to the array of routes.
     *
     * @since 1.3.4
     */
    public static function removeAll() {
        self::getInstance()->uriObj = null;
        self::getInstance()->routes = [
            'static' => [],
            'variable' => []
        ];
    }
    /**
     * Removes a route given its path.
     *
     * @param string $path The path part of route URI.
     *
     * @return bool If the route is removed, the method will return
     * true. If not, The method will return false.
     *
     * @since 1.3.7
     */
    public static function removeRoute(string $path) : bool {
        $pathFix = self::getInstance()->getBuilder()->fixUriPath($path);
        $retVal = false;
        $routes = &self::getInstance()->routes;

        if (isset($routes['static'][$pathFix])) {
            unset($routes['static'][$pathFix]);

            $retVal = true;
        } else if (isset($routes['variable'][$pathFix])) {
            unset($routes['variable'][$pathFix]);

            $retVal = true;
        }

        return $retVal;
    }
    /**
     * Destroys the default Router instance. Next call creates a fresh one.
     */
    public static function resetInstance(): void {
        self::$router = null;
    }
    /**
     * Redirect a URI to its route.
     *
     * @param string $uri The URI.
     *
     * @since 1.2
     */
    public static function route(string $uri) {
        Router::getInstance()->getMatcher()->resolveUrl($uri);
    }
    /**
     * Returns an associative array of all available routes.
     *
     * @return array An associative array of all available routes.
     *
     * @since 1.2
     */
    public static function routes() : array {
        $routesArr = [];

        foreach (Router::getInstance()->routes['static'] as $routeUri) {
            $routesArr[$routeUri->getUri()] = $routeUri->getRouteTo();
        }

        foreach (Router::getInstance()->routes['variable'] as $routeUri) {
            $routesArr[$routeUri->getUri()] = $routeUri->getRouteTo();
        }

        return $routesArr;
    }
    /**
     * Returns an associative array that contains all routes.
     *
     * @return array An associative array that contains all routes.
     *
     * @since 1.3.7
     */
    public static function routesAsRouterUri() : array {
        return self::getInstance()->routes;
    }
    /**
     * Returns the number of routes at which the router has.
     *
     * @return int Number of routes.
     *
     * @since 1.3.11
     */
    public static function routesCount() : int {
        $routesArr = self::getInstance()->routes;

        return count($routesArr['variable']) + count($routesArr['static']);
    }
    /**
     * Replaces the default Router instance.
     *
     * @param Router $router The router instance to use.
     */
    public static function setInstance(Router $router): void {
        self::$router = $router;
    }
    /**
     * Sets a callback to call in case a given rout is not found.
     *
     * @param callable $func The function which will be called if
     * the rout is not found.
     *
     * @since 1.3.8
     */
    public static function setOnNotFound(callable $func) {
        self::getInstance()->onNotFound = $func;
    }

    /**
     * Sets the current route URI object.
     *
     * @param RouterUri|null $uri
     */
    public function setRouteUri(?RouterUri $uri): void {
        $this->uriObj = $uri;
    }
    /**
     * Adds an object of type 'RouterUri' as new route.
     *
     * @param RouterUri $routerUri An object of type 'RouterUri'.
     *
     * @return bool If the object is added as new route, the method will
     * return true. If a route is already added, The method will return false.
     *
     * @since 1.3.2
     */
    public static function uriObj(RouterUri $routerUri) : bool {
        if (!self::getInstance()->getMatcher()->hasRoute($routerUri)) {
            if ($routerUri->hasVars()) {
                self::getInstance()->routes['variable'] = $routerUri;
            } else {
                self::getInstance()->routes['static'] = $routerUri;
            }

            return true;
        }

        return false;
    }

    /**
     * Adds new route to a page.
     *
     * @param array $options An associative array that contains route options.
     *
     * @return bool The method will return true if the route was created.
     *
     * @since 1.2
     *
     * @deprecated since version 1.3.12
     */
    private static function view(array $options): bool {
        if (gettype($options) == 'array') {
            $options[RouteOption::TYPE] = Router::VIEW_ROUTE;
            RouteBuilder::addToMiddlewareGroup($options, 'web');

            if (!isset($options[RouteOption::CACHE_DURATION])) {
                $options[RouteOption::CACHE_DURATION] = 3600;
            }

            return Router::getInstance()->getBuilder()->addRoute($options);
        }

        return false;
    }
}
