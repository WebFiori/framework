<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Router;

use Error;
use Exception;
use WebFiori\Cli\Runner;
use WebFiori\File\exceptions\FileException;
use WebFiori\File\File;
use WebFiori\Framework\App;
use WebFiori\Framework\Exceptions\RoutingException;
use WebFiori\Framework\Ui\HTTPCodeView;
use WebFiori\Framework\Ui\StarterPage;
use WebFiori\Framework\Ui\WebPage;
use WebFiori\Http\Request;
use WebFiori\Http\RequestMethod;
use WebFiori\Http\Response;
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
    }
    /**
     * Adds new route to a file inside the root folder.
     *
     * @param array $options An associative array of options.
     * The class 'RouteOption' can be used to access options. Available options
     * are:
     * <ul>
     * <li><b>path</b>: The path part of the URI. For example, if the
     * requested URI is 'http://www.example.com/user/ibrahim', the path
     * part of the URI is '/user/ibrahim'. It is possible to include parameters
     * in the path. To include a parameter in the path, its name must be enclosed
     * between {}. The value of the parameter will be stored in either the array
     * $_GET or $_POST after the requested URI is resolved. If we use the same
     * example above to get any user profile, We would add the following as
     * a path: 'user/{username}'. In this case, username will be available in
     * $_GET['username']. Note that its possible to get the value of the
     * parameter using the method <b>Router::<a href="#getParameterValue">getParameterValue()</a></b></li>
     * <li><b>route-to</b>: The path to the file that the route will point to.
     * It can be any file in the scope of the variable ROOT_PATH.</li>
     * <li><b>as-api</b>: If this parameter is set to true, the route will be
     * treated as if it was an API route. This means that the constant 'API_ROUTE'
     * will be initiated when a request is made to the route. Note that if the PHP file that
     * the route is pointing to represent an API, no need to add this option. Default is false.</li>
     * <li><b>case-sensitive</b>: Make the URL case-sensitive or not.
     * If this one is set to false, then if a request is made to the URL 'https://example.com/one/two',
     * It will be the same as requesting the URL 'https://example.com/OnE/tWO'. Default
     * is true.</li>
     * <li><b>languages</b>: An indexed array that contains the languages at
     * which the resource can have. Each language is represented as two
     * characters such as 'AR'.</li>
     * <li><b>vars-values</b>: An optional associative array which contains sub
     * indexed arrays that contains possible values for URI vars. This one is
     * used when building the sitemap.</li>
     * <li><b>middleware</b>: This can be a name of a middleware to assign to the
     * route. This also can be a middleware group. In addition to that, this can
     * be an array that holds middleware names or middleware groups names.</li>
     *  <li><b>in-sitemap</b>: If set to true, the given route will be added to
     * the basic site map which can be generated by the router class.</li>
     * <li><b>methods</b>: An optional array that can have a set of
     * allowed request methods for fetching the resource. This can be
     * also a single string such as 'GET' or 'POST'.</li>
     * <li><b>action</b>: If the class that the route is pointing to
     * represent a controller, this index can have the value of the
     * action that will be performed (the name of the class method).</li>
     * <li><b>routes</b> This option is used to have sub-routes in the same path. This
     * option is used to group multiple routes which share initial part of a path.</li>
     * </ul>
     *
     * @return bool The method will return true if the route was created.
     * If a route for the given path was already created, the method will return
     * false.
     *
     * @since 1.2
     */
    public static function addRoute(array $options) : bool {
        $options[RouteOption::TYPE] = Router::CUSTOMIZED;

        return Router::getInstance()->addRouteHelper1($options);
    }
    /**
     * Adds new route to a web services set.
     *
     * Note that the route which created using this method will be added to
     * 'global' and 'api' middleware groups.
     *
     * @param array $options An associative array that contains route
     * options. Available options are:
     * <ul>
     * <li><b>path</b>: The path part of the URI. For example, if the
     * requested URI is 'http://www.example.com/user/ibrahim', the path
     * part of the URI is '/user/ibrahim'. It is possible to include parameters
     * in the path. To include a parameter in the path, its name must be enclosed
     * between {}. The value of the parameter will be stored in either the array
     * $_GET or $_POST after the requested URI is resolved. If we use the same
     * example above to get any user profile, We would add the following as
     * a path: 'user/{username}'. In this case, username will be available in
     * $_GET['username']. Note that its possible to get the value of the
     * parameter using the method <b>Router::<a href="#getParameterValue">getParameterValue()</a></b>.
     * Note that for any route that points to a web services set, a parameter which
     * has the name 'service-name' must be added.</li>
     * <li><b>route-to</b>: The path to the API file. The root folder for
     * all APIs is '/apis'. If the API name is 'get-user-profile.php', then the
     * value of this parameter must be '/get-user-profile.php'. If the API is in a
     * subdirectory inside the APIs directory, then the name of the
     * directory must be included.</li>
     * <li><b>case-sensitive</b>: Make the URL case-sensitive or not.
     * If this one is set to false, then if a request is made to the URL 'https://example.com/one/two',
     * It will be the same as requesting the URL 'https://example.com/OnE/tWO'. Default
     * is true.</li>
     * <li><b>vars-values</b>: An optional associative array which contains sub
     * indexed arrays that contains possible values for URI vars. This one is
     * used when building the sitemap.</li>
     * <li><b>middleware</b>: This can be a name of a middleware to assign to the
     * route. This also can be a middleware group. In addition to that, this can
     * be an array that holds middleware names or middleware groups names.</li>
     * <li><b>methods</b>: An optional array that can have a set of
     * allowed request methods for fetching the resource. This can be
     * also a single string such as 'GET' or 'POST'.</li>
     * <li><b>routes</b> This option is used to have sub-routes in the same path. This
     * option is used to group multiple routes which share initial part of a path.</li>
     * </ul>
     *
     * @return bool The method will return true if the route was created.
     * If a route for the given path was already created, the method will return
     * false.
     *
     * @since 1.2
     */
    public static function api(array $options) : bool {
        $options[RouteOption::TYPE] = Router::API_ROUTE;
        self::addToMiddlewareGroup($options, 'api');

        return Router::getInstance()->addRouteHelper1($options);
    }
    /**
     * Returns the base URI which is used to create routes.
     *
     * @return string The base URL which is used to create routes. The returned
     * value is based on one of two values. Either the value that is returned
     * by the method 'Util::getBaseURL()' or the method 'SiteConfig::getBaseURL()'.
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
     * @param array $options An associative array that contains route
     * options. Available options are:
     * <ul>
     * <li><b>path</b>: The path part of the URI. For example, if the
     * requested URI is 'http://www.example.com/user/ibrahim', the path
     * part of the URI is '/user/ibrahim'. It is possible to include parameters
     * in the path. To include a parameter in the path, its name must be enclosed
     * between {}. The value of the parameter will be stored in either the array
     * $_GET or $_POST after the requested URI is resolved. If we use the same
     * example above to get any user profile, We would add the following as
     * a path: 'user/{username}'. In this case, username will be available in
     * $_GET['username']. Note that its possible to get the value of the
     * parameter using the method <b>Router::<a href="#getParameterValue">getParameterValue()</a></b></li>
     * <li><b>route-to</b>: A closure (A PHP function). </li>
     * <li><b>closure-params</b>: An array that contains values which
     * can be passed to the closure.</li>
     * <li><b>as-api</b>: If this parameter is set to true, the route will be
     * treated as if it was an API route. This means that the constant 'API_ROUTE'
     * will be initiated when a request is made to the route. Default is false.</li>
     * <li><b>case-sensitive</b>: Make the URL case-sensitive or not.
     * If this one is set to false, then if a request is made to the URL 'https://example.com/one/two',
     * It will be the same as requesting the URL 'https://example.com/OnE/tWO'. Default
     * is true.</li>
     * <li><b>languages</b>: An indexed array that contains the languages at
     * which the resource can have. Each language is represented as two
     * characters such as 'AR'.</li>
     * <li><b>vars-values</b>: An optional associative array which contains sub
     * indexed arrays that contains possible values for URI vars. This one is
     * used when building the sitemap.</li>
     *
     * <li><b>middleware</b>: This can be a name of a middleware to assign to the
     * route. This also can be a middleware group. In addition to that, this can
     * be an array that holds middleware names or middleware groups names.</li>
     *
     *  <li><b>in-sitemap</b>: If set to true, the given route will be added to
     * the basic site map which can be generated by the router class.</li>
     * <li><b>methods</b>: An optional array that can have a set of
     * allowed request methods for fetching the resource. This can be
     * also a single string such as 'GET' or 'POST'.</li>
     * <li><b>routes</b> This option is used to have sub-routes in the same path. This
     * option is used to group multiple routes which share initial part of a path.</li>
     * </ul>
     *
     *
     * @return bool The method will return true if the route was created.
     * If a route for the given path was already created, the method will return
     * false. Also, if <b>'route-to'</b> is not a function, the method will return false.
     *
     * @since 1.2
     */
    public static function closure(array $options) : bool {
        $options[RouteOption::TYPE] = Router::CLOSURE_ROUTE;
        self::addToMiddlewareGroup($options, 'closure');

        return Router::getInstance()->addRouteHelper1($options);
    }
    /**
     * Returns the value of the base URI which is appended to the path.
     *
     * This method is similar to calling the method <b>Router::<a href="#base">base()</a></b>.
     *
     * @return string
     *
     * @since 1.0
     */
    public static function getBase() : string {
        return self::getInstance()->baseUrl;
    }
    /**
     * Returns the value of a parameter which exist in the path part of the
     * URI.
     *
     * @param string $varName The name of the parameter. Note that it must
     * not include braces.
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
     * When the method Router::route() is called and a route is found, an
     * object of type 'RouterUri' is created which has route information.
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
        return self::getInstance()->getUriObjHelper($path);
    }
    /**
     * Returns an object of type 'RouterUri' which contains URL route information.
     *
     * @param string $url A string that represents a URL (such as 'https://example.com/my-resource').
     *
     * @return RouterUri|null If a resource was found which has the given route, an
     * object of type RouterUri is returned. Other than that, null is returned. Note
     * that if the URI is invalid, the method will return null. Also, if the library
     * 'http' is not loaded, the method will return null.
     *
     * @since 1.3.6
     */
    public static function getUriObjByURL(string $url) {
        try {
            self::getInstance()->resolveUrlHelper($url, false);

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
        $trimmed = self::getInstance()->fixUriPath($path);

        return isset($routesArr['static'][$trimmed]) || isset($routesArr['variable'][$trimmed]);
    }
    /**
     * Adds a route to a basic xml site map.
     *
     * If this method is called, a route in the form 'http://example.com/sitemam.xml'
     * and in the form 'http://example.com/sitemam' will be created.
     * The method will check all created routes objects and check if they
     * should be included in the site map. Note that if a URI has parameters, it
     * will be not included unless possible values are given for the parameter.
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
            $routes = Router::getInstance()->getRoutesHelper();

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
            RouteOption::CACHE_DURATION => 86400//1 day
        ]);
        self::closure([
            RouteOption::PATH => '/sitemap',
            RouteOption::TO => $sitemapFunc,
            RouteOption::SITEMAP => true,
            RouteOption::CACHE_DURATION => 86400//1 day
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
     * 'global' and 'web' middleware groups. Additionally, the routes will
     * be cached for one hour.
     *
     * @param array $options An associative array that contains route
     * options. Available options are:
     * <ul>
     * <li><b>path</b>: The path part of the URI. For example, if the
     * requested URI is 'http://www.example.com/user/ibrahim', the path
     * part of the URI is '/user/ibrahim'. It is possible to include parameters
     * in the path. To include a parameter in the path, its name must be enclosed
     * between {}. The value of the parameter will be stored in either the array
     * $_GET or $_POST after the requested URI is resolved. If we use the same
     * example above to get any user profile, We would add the following as
     * a path: 'user/{username}'. In this case, username will be available in
     * $_GET['username']. </li>
     * <li><b>route-to</b>: The path to the view file. The root folder for
     * all views is '/pages'. If the view name is 'view-user.php', then the
     * value of this parameter must be '/view-user.php'. If the view is in a
     * subdirectory inside pages directory, then the name of the
     * directory must be included.</li>
     * <li><b>case-sensitive</b>: Make the URL case-sensitive or not.
     * If this one is set to false, then if a request is made to the URL 'https://example.com/one/two',
     * It will be the same as requesting the URL 'https://example.com/OnE/tWO'. Default
     * is true.</li>
     * <li><b>languages</b>: An indexed array that contains the languages at
     * which the resource can have. Each language is represented as two
     * characters such as 'AR'.</li>
     * <li><b>vars-values</b>: An optional associative array which contains sub
     * indexed arrays that contains possible values for URI vars. This one is
     * used when building the sitemap.</li>
     * <li><b>middleware</b>: This can be a name of a middleware to assign to the
     * route. This also can be a middleware group. In addition to that, this can
     * be an array that holds middleware names or middleware groups names.</li>
     *  <li><b>in-sitemap</b>: If set to true, the given route will be added to
     * the basic site map which can be generated by the router class.</li>
     * <li><b>methods</b>: An optional array that can have a set of
     * allowed request methods for fetching the resource. This can be
     * also a single string such as 'GET' or 'POST'.</li>
     * <li><b>routes</b> This option is used to have sub-routes in the same path. This
     * option is used to group multiple routes which share initial part of a path.</li>
     * </ul>
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
     *
     * @param string $to A path or a URL at which the user will be sent to.
     *
     * @param int $code HTTP redirect code. Can have one of the following values:
     * 301, 302, 303, 307 and 308. Default is 301 (Permanent redirect).
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
        $pathFix = self::getInstance()->fixUriPath($path);
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
     * Redirect a URI to its route.
     *
     * @param string $uri The URI.
     *
     * @since 1.2
     */
    public static function route(string $uri) {
        Router::getInstance()->resolveUrlHelper($uri);
    }
    /**
     * Returns an associative array of all available routes.
     *
     * @return array An associative array of all available routes. The
     * keys will be requested URIs and the values are the routes.
     *
     * @since 1.2
     */
    public static function routes() : array {
        $routesArr = [];

        foreach (Router::getInstance()->getRoutesHelper()['static'] as $routeUri) {
            $routesArr[$routeUri->getUri()] = $routeUri->getRouteTo();
        }

        foreach (Router::getInstance()->getRoutesHelper()['variable'] as $routeUri) {
            $routesArr[$routeUri->getUri()] = $routeUri->getRouteTo();
        }

        return $routesArr;
    }
    /**
     * Returns an associative array that contains all routes.
     *
     * The returned array will have two indices, 'static' and 'variable'. The 'static'
     * index will contain routes to resources at which they don't contain parameters in
     * their path part. Each index of the two will have another sub associative array.
     * The indices of each sub array ill be URLs that represents the route and
     * the value at each index will be an object of type 'RouterUri'.
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
     * Sets a callback to call in case a given rout is not found.
     *
     * @param callable $func The function which will be called if
     * the rout is not found.
     *
     * @since 1.3.8
     */
    public static function setOnNotFound(callable $func) {
        self::getInstance()->setOnNotFoundHelper($func);
    }
    /**
     * Adds an object of type 'RouterUri' as new route.
     *
     * @param RouterUri $routerUri An object of type 'RouterUri'.
     *
     * @return bool If the object is added as new route, the method will
     * return true. If the given parameter is not an instance of 'RouterUri'
     * or a route is already added, The method will return false.
     *
     * @since 1.3.2
     */
    public static function uriObj(RouterUri $routerUri) : bool {
        if (!self::getInstance()->hasRouteHelper($routerUri->getPath())) {
            if ($routerUri->hasVars()) {
                self::getInstance()->routes['variable'] = $routerUri;
            } else {
                self::getInstance()->routes['static'] = $routerUri;
            }

            return true;
        }

        return false;
    }
    private function addRouteHelper0($options): bool {
        $routeTo = $options[RouteOption::TO];
        $caseSensitive = $options[RouteOption::CASE_SENSITIVE];
        $routeType = $options[RouteOption::TYPE];
        $incInSiteMap = $options[RouteOption::SITEMAP];
        $asApi = $options[RouteOption::API];
        $closureParams = $options[RouteOption::CLOSURE_PARAMS] ;
        $path = $options[RouteOption::PATH];
        $cache = $options[RouteOption::CACHE_DURATION];

        if ($routeType == self::CLOSURE_ROUTE && !is_callable($routeTo)) {
            return false;
        }
        $routeUri = new RouterUri($this->getBase().$path, $routeTo,$caseSensitive, $closureParams);
        $routeUri->setAction($options[RouteOption::ACTION]);
        $routeUri->setCacheDuration($cache);
        if (!$this->hasRouteHelper($routeUri)) {
            if ($asApi === true) {
                $routeUri->setType(self::API_ROUTE);
            } else {
                $routeUri->setType($routeType);
            }
            $routeUri->setIsInSiteMap($incInSiteMap);

            $routeUri->setRequestMethods($options[RouteOption::REQUEST_METHODS]);

            foreach ($options[RouteOption::LANGS] as $langCode) {
                $routeUri->addLanguage($langCode);
            }

            foreach ($options[RouteOption::VALUES] as $varName => $varValues) {
                $routeUri->addAllowedParameterValues($varName, $varValues);
            }
            $path = $routeUri->isCaseSensitive() ? $routeUri->getPath() : strtolower($routeUri->getPath());

            foreach ($options[RouteOption::MIDDLEWARE] as $mwName) {
                $routeUri->addMiddleware($mwName);
            }

            if ($routeUri->hasParameters()) {
                $this->routes['variable'][$path] = $routeUri;
            } else {
                $this->routes['static'][$path] = $routeUri;
            }

            return true;
        }

        return false;
    }
    /**
     * Adds new route to the router.
     *
     * @param array $options An associative array of route options. The
     * array can have the following indices:
     * <ul>
     * <li><b>path</b>: The path part of the URI (e.g. '/en/one/two'). If not
     * given, the route will represent home page of the website. It's possible
     * to add parameters to the path using this syntax: '/en/{var-one}/two/{var-two}'.
     * The value of the parameter can be accessed later through the
     * array $_GET or the array $_POST.</li>
     * <li><b>case-sensitive</b>: Make the URL case-sensitive or not.
     * If this one is set to false, then if a request is made to the URL 'https://example.com/one/two',
     * It will be the same as requesting the URL 'https://example.com/OnE/tWO'. Default
     * is true.</li>
     * <li><b>route-to</b>:  The location where the URI is going
     * to route to (The resource). It can be either a callable or a string which represents
     * the path to a PHP file.</li>
     * <li><b>methods</b>: An optional array that can have a set of
     * allowed request methods for fetching the resource. This can be
     * also a single string such as 'GET' or 'POST'.</li>
     * <li><b>type</b>: The type of the route. It can have one of 4
     * values:
     * <ul>
     * <li><b>Router::VIEW_ROUTE</b>: If the PHP file is inside the folder
     * '/pages' or in other subdirectory under the same folder.</li>
     * <li><b>Router::API_ROUTE</b> : If the PHP file is inside the folder '/apis'
     * or in other subdirectory under the same folder.</li>
     * <li><b>Router::CUSTOMIZED</b> : If the PHP file is inside the root folder or in
     * other subdirectory under the root.</li>
     * <li><b>Router::CLOSURE_ROUTE</b> If the route is a closure.</li>
     * </ul></li>
     * <li><b>is-api</b>: If this parameter is set to true, the route will be
     * treated as if it was an API route. This means that the constant 'API_ROUTE'
     * will be initiated when a request is made to the route. Default is false.</li>
     * <li><b>in-sitemap</b>: If set to true, the given route will be added to
     * the basic site map which can be generated by the router class.</li>
     * <li><b>closure-params</b>:If the route type is closure route,
     * it is possible to pass values to it using this array.
     * </li>
     * <li><b>routes</b> This option is used to have sub-routes in the same path. This
     * option is used to group multiple routes which share initial part of a path.</li>
     * </ul>
     *
     * @return bool If the route is added, the method will return true.
     * The method one return false only in two cases, either the route type
     * is not correct or a similar route was already added.
     *
     * @since 1.0
     */
    private function addRouteHelper1(array $options): bool {
        if (isset($options[RouteOption::SUB_ROUTES])) {
            $routesArr = $this->addRoutesGroupHelper($options);
            $added = true;

            foreach ($routesArr as $route) {
                $added = $added && $this->addRouteHelper1($route);
            }

            return $added;
        }

        if (!isset($options[RouteOption::TO])) {
            return false;
        } else {
            $options = $this->checkOptionsArr($options);
            $routeType = $options[RouteOption::TYPE];
        }

        if (strlen($this->getBase()) != 0 && ($routeType == self::API_ROUTE ||
            $routeType == self::VIEW_ROUTE ||
            $routeType == self::CUSTOMIZED ||
            $routeType == self::CLOSURE_ROUTE)) {
            return $this->addRouteHelper0($options);
        }

        return false;
    }
    private function addRoutesGroupHelper($options, &$routesToAddArr = []) {
        $subRoutes = isset($options[RouteOption::SUB_ROUTES]) && gettype($options[RouteOption::SUB_ROUTES]) == 'array' ? $options[RouteOption::SUB_ROUTES] : [];

        foreach ($subRoutes as $subRoute) {
            if (isset($subRoute[RouteOption::PATH])) {
                $this->copyOptionsToSub($options, $subRoute);
                $subRoute[RouteOption::PATH] = $options[RouteOption::PATH].'/'.$subRoute[RouteOption::PATH];

                if (isset($subRoute[RouteOption::SUB_ROUTES]) && gettype($subRoute[RouteOption::SUB_ROUTES]) == 'array') {
                    $this->addRoutesGroupHelper($subRoute, $routesToAddArr);
                } else {
                    $routesToAddArr[] = $subRoute;
                }
            }
        }

        if (isset($options[RouteOption::TO])) {
            $sub = [
                RouteOption::PATH => $options[RouteOption::PATH],
                RouteOption::TO => $options[RouteOption::TO]
            ];
            $this->copyOptionsToSub($options, $sub);
            $routesToAddArr[] = $sub;
        }

        return $routesToAddArr;
    }
    private static function addToMiddlewareGroup(&$options, $groupName) {
        if (isset($options[RouteOption::MIDDLEWARE])) {
            if (gettype($options[RouteOption::MIDDLEWARE]) == 'array') {
                $options[RouteOption::MIDDLEWARE][] = $groupName;
            } else {
                $options[RouteOption::MIDDLEWARE] = [$options[RouteOption::MIDDLEWARE], $groupName];
            }
        } else {
            $options[RouteOption::MIDDLEWARE] = $groupName;
        }
    }
    /**
     * Checks for provided options and set defaults for the ones which are
     * not provided.
     *
     * @param array $options
     *
     * @return array
     */
    private function checkOptionsArr(array $options): array {
        $routeTo = $options[RouteOption::TO];

        if (isset($options[RouteOption::CASE_SENSITIVE])) {
            $caseSensitive = $options[RouteOption::CASE_SENSITIVE] === true;
        } else {
            $caseSensitive = true;
        }
        
        if (isset($options[RouteOption::CACHE_DURATION])) {
            $cacheDuration = $options[RouteOption::CACHE_DURATION];
        } else {
            $cacheDuration = 0;
        }

        $routeType = $options[RouteOption::TYPE] ?? Router::CUSTOMIZED;

        $incInSiteMap = $options[RouteOption::SITEMAP] ?? false;

        if (isset($options[RouteOption::MIDDLEWARE])) {
            if (gettype($options[RouteOption::MIDDLEWARE]) == 'array') {
                $mdArr = $options[RouteOption::MIDDLEWARE];
            } else if (gettype($options[RouteOption::MIDDLEWARE]) == 'string') {
                $mdArr = [$options[RouteOption::MIDDLEWARE]];
            } else {
                $mdArr = [];
            }
        } else {
            $mdArr = [];
        }

        if (isset($options[RouteOption::API])) {
            $asApi = $options[RouteOption::API] === true;
        } else {
            $asApi = false;
        }
        $closureParams = isset($options[RouteOption::CLOSURE_PARAMS]) && gettype($options[RouteOption::CLOSURE_PARAMS]) == 'array' ?
                $options[RouteOption::CLOSURE_PARAMS] : [];
        $path = isset($options[RouteOption::PATH]) ? $this->fixUriPath($options[RouteOption::PATH]) : '';
        $languages = isset($options[RouteOption::LANGS]) && gettype($options[RouteOption::LANGS]) == 'array' ? $options[RouteOption::LANGS] : [];
        $varValues = isset($options[RouteOption::VALUES]) && gettype($options[RouteOption::VALUES]) == 'array' ? $options[RouteOption::VALUES] : [];

        $action = '';

        if (isset($options[RouteOption::ACTION])) {
            $trimmed = trim($options[RouteOption::ACTION]);

            if (strlen($trimmed) > 0) {
                $action = $trimmed;
            }
        }

        return [
            RouteOption::CASE_SENSITIVE => $caseSensitive,
            RouteOption::TYPE => $routeType,
            RouteOption::SITEMAP => $incInSiteMap,
            RouteOption::API => $asApi,
            RouteOption::PATH => $path,
            RouteOption::TO => $routeTo,
            RouteOption::CLOSURE_PARAMS => $closureParams,
            RouteOption::LANGS => $languages,
            RouteOption::VALUES => $varValues,
            RouteOption::MIDDLEWARE => $mdArr,
            RouteOption::REQUEST_METHODS => $this->getRequestMethodsHelper($options),
            RouteOption::ACTION => $action,
            RouteOption::CACHE_DURATION => $cacheDuration
        ];
    }
    private function copyOptionsToSub($options, &$subRoute) {
        if (!isset($subRoute[RouteOption::CASE_SENSITIVE])) {
            if (isset($options[RouteOption::CASE_SENSITIVE])) {
                $caseSensitive = $options[RouteOption::CASE_SENSITIVE] === true;
            } else {
                $caseSensitive = true;
            }
            $subRoute[RouteOption::CASE_SENSITIVE] = $caseSensitive;
        }

        $subRoute[RouteOption::TYPE] = $options[RouteOption::TYPE] ?? Router::CUSTOMIZED;

        if (!isset($subRoute[RouteOption::SITEMAP])) {
            $incInSiteMap = $options[RouteOption::SITEMAP] ?? false;
            $subRoute[RouteOption::SITEMAP] = $incInSiteMap;
        }

        if (isset($options[RouteOption::MIDDLEWARE])) {
            if (gettype($options[RouteOption::MIDDLEWARE]) == 'array') {
                $mdArr = $options[RouteOption::MIDDLEWARE];
            } else {
                if (gettype($options[RouteOption::MIDDLEWARE]) == 'string') {
                    $mdArr = [$options[RouteOption::MIDDLEWARE]];
                } else {
                    $mdArr = [];
                }
            }
        } else {
            $mdArr = [];
        }

        if (!isset($subRoute[RouteOption::MIDDLEWARE])) {
            $subRoute[RouteOption::MIDDLEWARE] = $mdArr;
        } else {
            if (gettype($subRoute[RouteOption::MIDDLEWARE]) == 'array') {
                foreach ($mdArr as $md) {
                    $subRoute[RouteOption::MIDDLEWARE][] = $md;
                }
            } else {
                if (gettype($subRoute[RouteOption::MIDDLEWARE]) == 'string') {
                    $newMd = [$subRoute[RouteOption::MIDDLEWARE]];

                    foreach ($mdArr as $md) {
                        $newMd[] = $md;
                    }
                    $subRoute[RouteOption::MIDDLEWARE] = $newMd;
                }
            }
        }
        $languages = isset($options[RouteOption::LANGS]) && gettype($options[RouteOption::LANGS]) == 'array' ? $options[RouteOption::LANGS] : [];

        if (isset($subRoute[RouteOption::LANGS]) && gettype($subRoute[RouteOption::LANGS]) == 'array') {
            foreach ($languages as $langCode) {
                if (!in_array($langCode, $subRoute[RouteOption::LANGS])) {
                    $subRoute[RouteOption::LANGS][] = $langCode;
                }
            }
        } else {
            $subRoute[RouteOption::LANGS] = $languages;
        }

        $reqMethArr = $this->getRequestMethodsHelper($options);

        if (isset($subRoute[RouteOption::REQUEST_METHODS])) {
            if (gettype($subRoute[RouteOption::REQUEST_METHODS]) != 'array') {
                $reqMethArr[] = $subRoute[RouteOption::REQUEST_METHODS];
                $subRoute[RouteOption::REQUEST_METHODS] = $reqMethArr;
            } else {
                foreach ($reqMethArr as $meth) {
                    $subRoute[RouteOption::REQUEST_METHODS][] = $meth;
                }
            }
        } else {
            $subRoute[RouteOption::REQUEST_METHODS] = $reqMethArr;
        }
    }
    private function fixFilePath($path) {
        if (strlen($path) != 0 && $path != '/') {
            $path00 = str_replace('/', DS, $path);
            $path01 = str_replace('\\', DS, $path00);

            if ($path01[strlen($path01) - 1] == DS || $path01[0] == DS) {
                while ($path01[0] == DS || $path01[strlen($path01) - 1] == DS) {
                    $path01 = trim($path01, DS);
                }
                $path01 = DS.$path01;
            }

            if ($path01[0] != DS) {
                $path01 = DS.$path01;
            }
            $path = $path01;
        } else {
            $path = DS;
        }

        return $path;
    }
    /**
     * Removes any extra forward slash in the beginning or the end.
     *
     * @param string $path Any string that represents the path part of a URI.
     *
     * @return string A string in the format '/nice/work/boy'.
     *
     * @since 1.1
     */
    private function fixUriPath(string $path): string {
        if (strlen($path) != 0 && $path != '/') {
            if ($path[strlen($path) - 1] == '/' || $path[0] == '/') {
                while ($path[0] == '/' || $path[strlen($path) - 1] == '/') {
                    $path = trim($path, '/');
                }
                $path = '/'.$path;
            }

            if ($path[0] != '/') {
                $path = '/'.$path;
            }
        } else {
            $path = '/';
        }

        return $path;
    }
    private function getFileDirAndName($absDir): array {
        $explode = explode(DS, $absDir);
        $fileName = $explode[count($explode) - 1];
        $dir = substr($absDir, 0, strlen($absDir) - strlen($fileName));

        return [
            'name' => $fileName,
            'dir' => $dir
        ];
    }
    /**
     * Creates and Returns a single instance of the router.
     *
     * @return Router
     *
     * @since 1.0
     */
    private static function getInstance(): Router {
        if (self::$router != null) {
            return self::$router;
        }
        self::$router = new Router();

        return self::$router;
    }
    /**
     * Returns an array that holds allowed request methods for fetching the
     * specified resource.
     *
     * @param array $options The array which used to hold route options.
     *
     * @return array If the route has no specific request methods, the
     * array will be empty. Other than that, the array will have request
     * methods as strings.
     */
    private function getRequestMethodsHelper(array $options): array {
        $requestMethodsArr = [];

        if (isset($options[RouteOption::REQUEST_METHODS])) {
            $methTypes = gettype($options[RouteOption::REQUEST_METHODS]);

            if ($methTypes == 'array') {
                foreach ($options[RouteOption::REQUEST_METHODS] as $reqMethod) {
                    $upper = strtoupper(trim($reqMethod));

                    if (in_array($upper, RequestMethod::getAll())) {
                        $requestMethodsArr[] = $upper;
                    }
                }
            } else {
                if ($methTypes == 'string') {
                    $upper = strtoupper(trim($options[RouteOption::REQUEST_METHODS]));

                    if (in_array($upper, RequestMethod::getAll())) {
                        $requestMethodsArr[] = $upper;
                    }
                }
            }
        }

        return $requestMethodsArr;
    }
    /**
     * Returns an array which contains all routes as RouteURI object.
     *
     * @return array An array which contains all routes as RouteURI object.
     *
     * @since 1.2
     */
    private function getRoutesHelper() : array {
        return $this->routes;
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
    private function getUriObjHelper(string $path) {
        if (isset($this->routes['static'][$path])) {
            return $this->routes['static'][$path];
        } else {
            if (isset($this->routes['variable'][$path])) {
                return $this->routes['variable'][$path];
            }
        }

        return null;
    }
    /**
     * Checks if a given path has a route or not.
     *
     * @param RouterUri $path The path which will be checked (such as '/path1/path2')
     *
     * @return bool The method will return true if the given path
     * has a route.
     *
     * @since 1.1
     */
    private function hasRouteHelper($uriObj): bool {
        $path = $uriObj->getPath();

        if (!$uriObj->isCaseSensitive()) {
            $path = strtolower($path);
        }

        if ($uriObj->hasParameters()) {
            return isset($this->routes['variable'][$path]);
        } else {
            return isset($this->routes['static'][$path]);
        }
    }
    /**
     * Checks if a directory name is a parameter or not.
     *
     * @param string $dir
     *
     * @return bool
     *
     * @since 1.1
     */
    private function isDirectoryAVar(string $dir): bool {
        return $dir[0] == '{' && $dir[strlen($dir) - 1] == '}';
    }
    /**
     *
     * @param RouterUri $route
     * @throws FileException
     */
    private function loadResourceHelper(RouterUri $route) {
        $file = $route->getRouteTo();
        $info = $this->getFileDirAndName($file);
        $fileObj = new File($info['name'], $info['dir']);
        $fileObj->read();

        if ($fileObj->getMIME() === 'text/plain') {
            $classNamespace = require_once $file;

            if (gettype($classNamespace) == 'string') {
                if (strlen($classNamespace) == 0) {
                    $constructor = '\\'.$route->getClassName();
                } else {
                    $constructor = '\\'.$classNamespace.'\\'.$route->getClassName();
                }

                if (class_exists($constructor)) {
                    $instance = new $constructor();

                    if ($instance instanceof WebServicesManager) {
                        if (!defined('API_CALL')) {
                            define('API_CALL', true);
                        }
                        $instance->process();
                    } else if ($instance instanceof WebPage) {
                        $instance->render();
                    } else if ($route->getAction() !== null) {
                        $toCall = $route->getAction();
                        $instance->$toCall();
                    }
                }
            }
        } else {
            $fileObj->view();
        }
    }
    /**
     * Send http 301 response code and redirect the request to non-www URI.
     *
     * @param RouterUri $uriObj
     */
    private function redirectToNonWWW(RouterUri $uriObj) {
        App::getResponse()->setCode(301);
        $path = '';

        $host = substr($uriObj->getHost(), strpos($uriObj->getHost(), '.'));

        for ($x = 1 ; $x < count($uriObj->getPathArray()) ; $x++) {
            $path .= '/'.$uriObj->getPathArray()[$x];
        }
        $queryString = '';

        if (strlen($uriObj->getQueryString()) > 0) {
            $queryString = '?'.$uriObj->getQueryString();
        }
        $fragment = '';

        if (strlen($uriObj->getFragment()) > 0) {
            $fragment = '#'.$uriObj->getFragment();
        }
        $port = '';

        if (strlen($uriObj->getPort()) > 0) {
            $port = ':'.$uriObj->getPort();
        }
        App::getResponse()->addHeader('location', $uriObj->getScheme().'://'.$host.$port.$path.$queryString.$fragment);
        App::getResponse()->send();
    }
    /**
     * Route a given URI to its specified resource.
     *
     * If the router has no routes, the router will send back a '418 - I'm A
     * Teapot' response. If the route is available but the file that the
     * router is routing to do not exist, a '500 - Server Error' Response
     * with the message 'The resource 'a_resource' was available but its route is not configured correctly.' is
     * sent back. If the route is not found, The router will call the function
     * that was set by the user in case a route is not found.
     *
     * @param string $uri A URI such as 'http://www.example.com/hello/ibrahim'
     *
     * @param bool $loadResource If set to true, the resource that represents the
     * route will be loaded. If false, the route will be only resolved. Default
     * is true.
     *
     * @since 1.0
     */
    private function resolveUrlHelper(string $uri, bool $loadResource = true) {
        $this->uriObj = null;

        if (self::routesCount() != 0) {
            $routeUri = new RouterUri($uri, '');

            if ($routeUri->hasWWW() && defined('NO_WWW') && NO_WWW === true) {
                $this->redirectToNonWWW($routeUri);
            }
            //first, search for the URI without checking parameters
            if ($this->searchRoute($routeUri, $uri, $loadResource)) {
                return;
            }
            //if no route found, try to replace parameters with values
            //note that query string vars are optional.
            if ($this->searchRoute($routeUri, $uri, $loadResource, true)) {
                return;
            }

            //if we reach this part, this means the route was not found
            if ($loadResource) {
                call_user_func($this->onNotFound);
            }
        } else {
            if ($loadResource === true) {
                $page = new StarterPage();

                $page->render();
            }
        }
    }

    /**
     *
     * @param RouterUri $route
     * @param bool $loadResource
     * @throws RoutingException
     */
    private function routeFound(RouterUri $route, bool $loadResource) {
        if ($route->isRequestMethodAllowed()) {
            $this->uriObj = $route;

            foreach ($route->getMiddleware() as $mw) {
                $mw->before(App::getRequest(), App::getResponse());
            }

            if ($route->getType() == self::API_ROUTE && !defined('API_CALL')) {
                define('API_CALL', true);
            }
            if (is_callable($route->getRouteTo())) {
                if ($loadResource === true) {
                    call_user_func_array($route->getRouteTo(),$route->getClosureParams());
                }
            } else {
                $file = $route->getRouteTo();
                // A route created using the syntax Class::class
                $xFile = '\\'.str_replace("/", "\\", $file);

                if (class_exists($xFile) && $loadResource) {
                    $class = new $xFile();

                    if ($class instanceof WebServicesManager) {
                        $class->process();
                    } else if ($class instanceof WebPage) {
                        $class->render();
                    } else if ($route->getAction() !== null) {
                        $toCall = $route->getAction();
                        $class->$toCall();
                    }
                } else {
                    $routeType = $route->getType();

                    if ($routeType == self::VIEW_ROUTE || $routeType == self::CUSTOMIZED || $routeType == self::API_ROUTE) {
                        $file = ROOT_PATH.$routeType.$this->fixFilePath($file);
                    } else {
                        $file = ROOT_PATH.$this->fixFilePath($file);
                    }

                    if (gettype($file) == 'string' && file_exists($file)) {
                        if ($loadResource === true) {
                            $route->setRoute($file);
                            $this->loadResourceHelper($route);
                        }
                    } else {
                        if ($loadResource === true) {
                            $message = 'The resource "'.App::getRequest()->getRequestedURI().'" was available. '
                            .'but its route is not configured correctly. '
                            .'The resource which the route is pointing to was not found.';

                            if (defined('WF_VERBOSE') && WF_VERBOSE) {
                                $message = 'The resource "'.App::getRequest()->getRequestedURI().'" was available. '
                                .'but its route is not configured correctly. '
                                .'The resource which the route is pointing to was not found ('.$file.').';
                            }
                            throw new RoutingException($message);
                        }
                    }
                }
            }
        } else {
            App::getResponse()->setCode(405);

            if (!defined('API_CALL')) {
                $notFoundView = new HTTPCodeView(405);
                $notFoundView->render();
            } else {
                $json = new Json([
                    'message' => 'Request method not allowed.',
                    'type' => 'error'
                ]);
                App::getResponse()->write($json);
            }
        }
    }

    /**
     *
     * @param RouterUri $routeUri
     * @param string $uri
     * @param bool $loadResource
     * @param bool $withVars
     * @return bool
     * @throws RoutingException
     */
    private function searchRoute(RouterUri $routeUri, string $uri, bool $loadResource, bool $withVars = false): bool {
        
        $pathArray = $routeUri->getPathArray();
        $requestMethod = App::getRequest()->getMethod();
        $indexToSearch = 'static';

        if ($withVars) {
            $indexToSearch = 'variable';
        }

        if ($indexToSearch == 'static') {
            $route = isset($this->routes[$indexToSearch][$routeUri->getPath()]) ?
                    $this->routes[$indexToSearch][$routeUri->getPath()] : null;

            if ($route instanceof RouterUri) {
                if (!$route->isCaseSensitive()) {
                    $isEqual = strtolower($route->getUri()) ==
                    strtolower($routeUri->getUri());
                } else {
                    $isEqual = $route->getUri() == $routeUri->getUri();
                }

                if ($isEqual) {
                    $route->setRequestedUri($uri);
                    $this->routeFound($route, $loadResource);

                    return true;
                }
            }
        } else {
            foreach ($this->routes['variable'] as $route) {
                $this->setUriVarsHelper($route, $pathArray, $requestMethod);

                if ($route->isAllParametersSet() && $route->setRequestedUri($uri)) {
                    $this->routeFound($route, $loadResource);

                    return true;
                }
            }
        }

        return false;
    }
    /**
     * Sets a callback to call in case a given rout is not found.
     *
     * @param callable $function The function which will be called if
     * the rout is not found.
     *
     * @since 1.0
     */
    private function setOnNotFoundHelper(callable $function) {
        $this->onNotFound = $function;
    }
    /**
     *
     * @param RouterUri $uriRouteObj One URI object taken from stored routes.
     *
     * @param array $requestedPathArr An array that contains requested URI path
     * part.
     *
     * @param string $requestMethod
     */
    private function setUriVarsHelper(RouterUri $uriRouteObj, array $requestedPathArr, string $requestMethod) {
        $routePathArray = $uriRouteObj->getPathArray();
        $pathVarsCount = count($routePathArray);
        $requestedPathPartsCount = count($requestedPathArr);

        for ($x = 0 ; $x < $pathVarsCount ; $x++) {
            if ($x == $requestedPathPartsCount) {
                break;
            }

            if ($this->isDirectoryAVar($routePathArray[$x])) {
                $varName = trim($routePathArray[$x], '{}');

                if ($varName[strlen($varName) - 1] == '?') {
                    $varName = trim($varName, '?');
                }
                $uriRouteObj->setParameterValue($varName, $requestedPathArr[$x]);

                if ($requestMethod == 'POST' || $requestMethod == 'PUT') {
                    $_POST[$varName] = filter_var(urldecode($requestedPathArr[$x]));
                } else if ($requestMethod == 'GET' || $requestMethod == 'DELETE' || Runner::isCLI()) {
                    //usually, in CLI there is no request method.
                    //but we store result in $_GET.
                    $_GET[$varName] = filter_var(urldecode($requestedPathArr[$x]));
                }
            } else if ((!$uriRouteObj->isCaseSensitive() && (strtolower($routePathArray[$x]) != strtolower($requestedPathArr[$x]))) || $routePathArray[$x] != $requestedPathArr[$x]) {
                break;
            }
        }
    }
    /**
     * Adds new route to a page.
     *
     * A page can be any file that is added inside the folder '/pages'.
     *
     * @param array $options An associative array that contains route
     * options. Available options are:
     * <ul>
     * <li><b>path</b>: The path part of the URI. For example, if the
     * requested URI is 'http://www.example.com/user/ibrahim', the path
     * part of the URI is '/user/ibrahim'. It is possible to include parameters
     * in the path. To include a parameter in the path, its name must be enclosed
     * between {}. The value of the parameter will be stored in either the array
     * $_GET or $_POST after the requested URI is resolved. If we use the same
     * example above to get any user profile, We would add the following as
     * a path: 'user/{username}'. In this case, username will be available in
     * $_GET['username']. </li>
     * <li><b>route-to</b>: The path to the view file. The root folder for
     * all views is '/pages'. If the view name is 'view-user.php', then the
     * value of this parameter must be '/view-user.php'. If the view is in a
     * subdirectory inside pages directory, then the name of the
     * directory must be included.</li>
     * <li><b>case-sensitive</b>: Make the URL case-sensitive or not.
     * If this one is set to false, then if a request is made to the URL 'https://example.com/one/two',
     * It will be the same as requesting the URL 'https://example.com/OnE/tWO'. Default
     * is true.</li>
     * <li><b>languages</b>: An indexed array that contains the languages at
     * which the resource can have. Each language is represented as two
     * characters such as 'AR'.</li>
     * <li><b>vars-values</b>: An optional associative array which contains sub
     * indexed arrays that contains possible values for URI vars. This one is
     * used when building the sitemap.</li>
     * <li><b>middleware</b>: This can be a name of a middleware to assign to the
     * route. This also can be a middleware group. In addition to that, this can
     * be an array that holds middleware names or middleware groups names.</li>
     *  <li><b>in-sitemap</b>: If set to true, the given route will be added to
     * the basic site map which can be generated by the router class.</li>
     * <li><b>methods</b>: An optional array that can have a set of
     * allowed request methods for fetching the resource. This can be
     * also a single string such as 'GET' or 'POST'.</li>
     * <li><b>routes</b> This option is used to have sub-routes in the same path. This
     * option is used to group multiple routes which share initial part of a path.</li>
     * </ul>
     *
     * @return bool The method will return true if the route was created.
     * If a route for the given path was already created, the method will return
     * false.
     *
     * @since 1.2
     *
     * @deprecated since version 1.3.12
     */
    private static function view(array $options): bool {
        if (gettype($options) == 'array') {
            $options[RouteOption::TYPE] = Router::VIEW_ROUTE;
            self::addToMiddlewareGroup($options, 'web');
            if (!isset($options[RouteOption::CACHE_DURATION])) {
                //Cache pages for 1 hour by default
                $options[RouteOption::CACHE_DURATION] = 3600;
            }
            return Router::getInstance()->addRouteHelper1($options);
        }

        return false;
    }
}
