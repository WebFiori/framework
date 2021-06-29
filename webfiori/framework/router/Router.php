<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace webfiori\framework\router;

use Error;
use Exception;
use webfiori\framework\cli\CLI;
use webfiori\framework\exceptions\RoutingException;
use webfiori\framework\File;
use webfiori\framework\ui\HTTPCodeView;
use webfiori\framework\ui\WebPage;
use webfiori\framework\Util;
use webfiori\framework\WebFioriApp;
use webfiori\http\Request;
use webfiori\http\Response;
use webfiori\http\WebServicesManager;
use webfiori\json\Json;
use webfiori\ui\HTMLNode;
use webfiori\framework\ui\StarterPage;
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
 * &nbsp;&nbsp;&nbsp;&nbsp;'path'=>'/custom-route',<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;'route-to'=>'/my-files/my-view.html'<br/>
 * ]);
 * </pre> 
 * </p>
 * <p>
 * In addition to creating routes using files, it is possible to have routes which 
 * points to classes as follows:
 * <pre>
 * Router::addRoute([<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;'path'=>'/custom-route',<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;'route-to'=> MyClass::class<br/>
 * ]);
 * </pre> 
 * </p>
 * @author Ibrahim
 * @version 1.3.11
 */
class Router {
    /**
     * A constant that represents API route. It is simply the root directory where APIs 
     * should be created.
     * 
     * @since 1.0
     */
    const API_ROUTE = DS.APP_DIR_NAME.DS.'apis';
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
    const VIEW_ROUTE = DS.APP_DIR_NAME.DS.'pages';
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
        $this->pathAndResourceArr = [];
        $this->onNotFound = function ()
        {
            Response::setCode(404);

            if (!defined('API_CALL')) {
                $notFoundView = new HTTPCodeView(404);
                $notFoundView->render();
            } else {
                $json = new Json([
                    'message' => 'Requested resource was not found.',
                    'type' => 'error'
                ]);
                Response::write($json);
            }
        };

        if (class_exists('webfiori\conf\SiteConfig')) {
            $this->baseUrl = trim(WebFioriApp::getAppConfig()->getBaseURL(), '/');
        } else {
            $this->baseUrl = trim(Util::getBaseURL(), '/');
        }
    }
    /**
     * Adds new route to a file inside the root folder.
     * 
     * @param array $options An associative array of options. Available options 
     * are: 
     * <ul>
     * <li><b>path</b>: The path part of the URI. For example, if the 
     * requested URI is 'http://www.example.com/user/ibrahim', the path 
     * part of the URI is '/user/ibrahim'. It is possible to include variables 
     * in the path. To include a variable in the path, its name must be enclosed 
     * between {}. The value of the variable will be stored in either the array 
     * $_GET or $_POST after the requested URI is resolved. If we use the same 
     * example above to get any user profile, We would add the following as 
     * a path: 'user/{username}'. In this case, username will be available in 
     * $_GET['username']. Note that its possible to get the value of the 
     * variable using the method <b>Router::<a href="#getVarValue">getVarValue()</a></b></li>
     * <li><b>route-to</b>: The path to the file that the route will point to. 
     * It can be any file in the scope of the variable ROOT_DIR.</li>
     * <li><b>as-api</b>: If this parameter is set to true, the route will be 
     * treated as if it was an API route. This means that the constant 'API_ROUTE' 
     * will be initiated when a request is made to the route. Note that if the PHP file that 
     * the route is pointing to represents an API, no need to add this option. Default is false.</li>
     * <li><b>case-sensitive</b>: Make the URL case sensitive or not. 
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
     * represents a controller, this index can have the value of the 
     * action that will be performed (the name of the class method).</li>
     * </ul>
     * 
     * @return boolean The method will return true if the route was created. 
     * If a route for the given path was already created, the method will return 
     * false.
     * 
     * @since 1.2
     */
    public static function addRoute(array $options) {
        $options['type'] = Router::CUSTOMIZED;

        return Router::get()->_addRoute($options);
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
    public static function redirect($path, $to, $code = 301) {
        Router::closure([
            'path' => $path,
            'route-to' => function ($to, $httpCode) {
                $allowedCodes = [301, 302, 303, 307, 308];
                
                if (!in_array($httpCode, $allowedCodes)) {
                    $httpCode = 301;
                }
                Response::addHeader('location', $to);
                Response::setCode($httpCode);
                Response::send();
            },
            'closure-params' => [
                $to, $code
            ]
        ]);
    }
    /**
     * Adds new route to a web services set.
     * 
     * @param array $options An associative array that contains route 
     * options. Available options are:
     * <ul>
     * <li><b>path</b>: The path part of the URI. For example, if the 
     * requested URI is 'http://www.example.com/user/ibrahim', the path 
     * part of the URI is '/user/ibrahim'. It is possible to include variables 
     * in the path. To include a variable in the path, its name must be enclosed 
     * between {}. The value of the variable will be stored in either the array 
     * $_GET or $_POST after the requested URI is resolved. If we use the same 
     * example above to get any user profile, We would add the following as 
     * a path: 'user/{username}'. In this case, username will be available in 
     * $_GET['username']. Note that its possible to get the value of the 
     * variable using the method <b>Router::<a href="#getVarValue">getVarValue()</a></b>. 
     * Note that for any route that points to a web services set, a variable which 
     * has the name 'service-name' must be added.</li>
     * <li><b>route-to</b>: The path to the API file. The root folder for 
     * all APIs is '/apis'. If the API name is 'get-user-profile.php', then the 
     * value of this parameter must be '/get-user-profile.php'. If the API is in a 
     * sub-directory inside the APIs directory, then the name of the 
     * directory must be included.</li>
     * <li><b>case-sensitive</b>: Make the URL case sensitive or not. 
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
     * </ul>
     * 
     * @return boolean The method will return true if the route was created. 
     * If a route for the given path was already created, the method will return 
     * false.
     * 
     * @since 1.2
     */
    public static function api($options) {
        if (gettype($options) == 'array') {
            $options['type'] = Router::API_ROUTE;

            return Router::get()->_addRoute($options);
        }

        return false;
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
    public static function base() {
        return Router::get()->getBase();
    }

    /**
     * Adds new closure route.
     * 
     * @param array $options An associative array that contains route 
     * options. Available options are:
     * <ul>
     * <li><b>path</b>: The path part of the URI. For example, if the 
     * requested URI is 'http://www.example.com/user/ibrahim', the path 
     * part of the URI is '/user/ibrahim'. It is possible to include variables 
     * in the path. To include a variable in the path, its name must be enclosed 
     * between {}. The value of the variable will be stored in either the array 
     * $_GET or $_POST after the requested URI is resolved. If we use the same 
     * example above to get any user profile, We would add the following as 
     * a path: 'user/{username}'. In this case, username will be available in 
     * $_GET['username']. Note that its possible to get the value of the 
     * variable using the method <b>Router::<a href="#getVarValue">getVarValue()</a></b></li>
     * <li><b>route-to</b>: A closure (A PHP function). </li>
     * <li><b>closure-params</b>: An array that contains values which 
     * can be passed to the closure.</li>
     * <li><b>as-api</b>: If this parameter is set to true, the route will be 
     * treated as if it was an API route. This means that the constant 'API_ROUTE' 
     * will be initiated when a request is made to the route. Default is false.</li>
     * <li><b>case-sensitive</b>: Make the URL case sensitive or not. 
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
     * </ul>
     * 
     * 
     * @return boolean The method will return true if the route was created. 
     * If a route for the given path was already created, the method will return 
     * false. Also, if <b>'route-to'</b> is not a function, the method will return false.
     * 
     * @since 1.2
     */
    public static function closure($options) {
        if (gettype($options) == 'array') {
            $options['type'] = Router::CLOSURE_ROUTE;

            return Router::get()->_addRoute($options);
        }

        return false;
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
    public static function getBase() {
        return self::get()->baseUrl;
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
        return self::get()->uriObj;
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
    public static function getUriObj($path) {
        return self::get()->_getUriObj($path);
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
    public static function getUriObjByURL($url) {
        try {
            self::get()->_resolveUrl($url, false);

            return self::getRouteUri();
        } catch (Error $ex) {
            return null;
        } catch (Exception $ex) {
            return null;
        }
    }
    /**
     * Returns the value of a variable which exist in the path part of the 
     * URI.
     * 
     * @param string $varName The name of the variable. Note that it must 
     * not include braces.
     * 
     * @return string|null The method will return the value of the 
     * variable if it was set. If it is not set or routing is still not yet 
     * happend, the method will return null.
     * 
     * @since 1.3.9
     */
    public static function getVarValue($varName) {
        $routeUri = self::getRouteUri();

        if ($routeUri instanceof RouterUri) {
            return $routeUri->getUriVar($varName);
        }

        return null;
    }
    /**
     * Checks if a given path has a route or not.
     * 
     * @param string $path The path which will be checked (such as '/path1/path2')
     * 
     * @return boolean The method will return true if the given path 
     * has a route.
     * 
     * @since 1.3.8
     */
    public static function hasRoute($path) {
        $routesArr = self::get()->routes;
        $trimmed = self::get()->_fixUriPath($path);

        return isset($routesArr['static'][$trimmed]) || isset($routesArr['variable'][$trimmed]);
    }
    /**
     * Adds a route to a basic xml site map. 
     * 
     * If this method is called, a route in the form 'http://example.com/sitemam.xml'  
     * and in the form 'http://example.com/sitemam' will be created. 
     * The method will check all created routes objects and check if they 
     * should be included in the site map. Note that if a URI has variables, it 
     * will be not included unless possible values are given for the variable.
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
            $routes = Router::get()->_getRoutes();

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
            Response::write($retVal);
            Response::addHeader('content-type','text/xml');
        };
        self::closure([
            'path' => '/sitemap.xml', 
            'route-to' => $sitemapFunc,
            'in-sitemap' => true
        ]);
        self::closure([
            'path' => '/sitemap', 
            'route-to' => $sitemapFunc,
            'in-sitemap' => true
        ]);
    }
    /**
     * Call the closure which was set if a route is not found.
     * 
     * @since 1.3.10
     */
    public static function notFound() {
        call_user_func(self::get()->onNotFound);
    }
    /**
     * Display all routes details.
     * 
     * @since 1.3.8
     */
    public static function printRoutes() {
        self::get()->_printRoutes();
    }
    /**
     * Remove all routes which has been added to the array of routes.
     * 
     * @since 1.3.4
     */
    public static function removeAll() {
        self::get()->uriObj = null;
        self::get()->routes = [
            'static' => [],
            'variable' => []
        ];
    }
    /**
     * Removes a route given its path.
     * 
     * @param string $path The path part of route URI.
     * 
     * @return boolean If the route is removed, the method will return 
     * true. If not, The method will return false.
     * 
     * @since 1.3.7
     */
    public static function removeRoute($path) {
        $pathFix = self::base().self::get()->_fixUriPath($path);
        $retVal = false;

        if (isset(self::get()->routes['static'][$pathFix])) {
            unset(self::get()->routes['static'][$pathFix]);

            $retVal = true;
        } else {
            if (self::get()->routes['variable'][$pathFix]) {
                unset(self::get()->routes['variable'][$pathFix]);

                $retVal = true;
            }
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
    public static function route($uri) {
        Router::get()->_resolveUrl($uri);
    }
    /**
     * Returns an associative array of all available routes.
     * 
     * @return array An associative array of all available routes. The 
     * keys will be requested URIs and the values are the routes.
     * 
     * @since 1.2
     */
    public static function routes() {
        $routesArr = [];

        foreach (Router::get()->_getRoutes()['static'] as $routeUri) {
            $routesArr[$routeUri->getUri()] = $routeUri->getRouteTo();
        }

        foreach (Router::get()->_getRoutes()['variable'] as $routeUri) {
            $routesArr[$routeUri->getUri()] = $routeUri->getRouteTo();
        }

        return $routesArr;
    }
    /**
     * Returns an associative array that contains all routes.
     * 
     * The returned array will have two indices, 'static' and 'variable'. The 'static' 
     * index will contain routes to resources at which they don't contain variables in 
     * their path part. Each index of the two will have another sub associative array.
     * The indices of each sub array ill be URLs that represents the route and 
     * the value at each index will be an object of type 'RouterUri'. 
     * 
     * @return array An associative array that contains all routes.
     * 
     * @since 1.3.7
     */
    public static function routesAsRouterUri() {
        return self::get()->routes;
    }
    /**
     * Sets a callback to call in case a given rout is not found.
     * 
     * @param callable $func The function which will be called if 
     * the rout is not found.
     * 
     * @since 1.3.8
     */
    public static function setOnNotFound($func) {
        self::get()->_setOnNotFound($func);
    }
    /**
     * Adds an object of type 'RouterUri' as new route.
     * 
     * @param RouterUri $routerUri An object of type 'RouterUri'.
     * 
     * @return boolean If the object is added as new route, the method will 
     * return true. If the given parameter is not an instance of 'RouterUri' 
     * or a route is already added, The method will return false.
     * 
     * @since 1.3.2
     */
    public static function uriObj($routerUri) {
        if ($routerUri instanceof RouterUri && !self::get()->_hasRoute($routerUri->getPath())) {
            if ($routerUri->hasVars()) {
                self::get()->routes['variable'] = $routerUri;
            } else {
                self::get()->routes['static'] = $routerUri;
            }

            return true;
        }

        return false;
    }
    /**
     * Adds new route to a view file.
     * A view file can be any file that is added inside the folder '/pages'.
     * 
     * @param array $options An associative array that contains route 
     * options. Available options are:
     * <ul>
     * <li><b>path</b>: The path part of the URI. For example, if the 
     * requested URI is 'http://www.example.com/user/ibrahim', the path 
     * part of the URI is '/user/ibrahim'. It is possible to include variables 
     * in the path. To include a variable in the path, its name must be enclosed 
     * between {}. The value of the variable will be stored in either the array 
     * $_GET or $_POST after the requested URI is resolved. If we use the same 
     * example above to get any user profile, We would add the following as 
     * a path: 'user/{username}'. In this case, username will be available in 
     * $_GET['username']. </li>
     * <li><b>route-to</b>: The path to the view file. The root folder for 
     * all views is '/pages'. If the view name is 'view-user.php', then the 
     * value of this parameter must be '/view-user.php'. If the view is in a 
     * sub-directory inside the views directory, then the name of the 
     * directory must be included.</li>
     * <li><b>case-sensitive</b>: Make the URL case sensitive or not. 
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
     * </ul>
     * 
     * @return boolean The method will return true if the route was created. 
     * If a route for the given path was already created, the method will return 
     * false.
     * 
     * @since 1.2
     */
    public static function view($options) {
        if (gettype($options) == 'array') {
            $options['type'] = Router::VIEW_ROUTE;

            return Router::get()->_addRoute($options);
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
     * given, the route will represents home page of the website. Its possible 
     * to add variables to the path using this syntax: '/en/{var-one}/two/{var-two}'. 
     * The value of the variable can be accessed later through the 
     * array $_GET or the array $_POST.</li>
     * <li><b>case-sensitive</b>: Make the URL case sensitive or not. 
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
     * '/pages' or in other sub-directory under the same folder.</li>
     * <li><b>Router::API_ROUTE</b> : If the PHP file is inside the folder '/apis' 
     * or in other sub-directory under the same folder.</li>
     * <li><b>Router::CUSTOMIZED</b> : If the PHP file is inside the root folder or in 
     * other sub-directory under the root.</li>
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
     * </ul>
     * 
     * @return boolean If the route is added, the method will return true. 
     * The method one return false only in two cases, either the route type 
     * is not correct or a similar route was already added.
     * 
     * @since 1.0
     */
    private function _addRoute(array $options) {
        if (!isset($options['route-to'])) {
            return false;
        } else {
            $options = $this->_checkOptionsArr($options);
            $routeType = $options['type'];
        }

        if (strlen($this->getBase()) != 0 && ($routeType == self::API_ROUTE || 
            $routeType == self::VIEW_ROUTE || 
            $routeType == self::CUSTOMIZED || 
            $routeType == self::CLOSURE_ROUTE)) {
            return $this->_addRouteHelper($options);
        }

        return false;
    }
    private function _addRouteHelper($options) {
        $routeTo = $options['route-to'];
        $caseSensitive = $options['case-sensitive'];
        $routeType = $options['type'];
        $incInSiteMap = $options['in-sitemap'];
        $asApi = $options['as-api'];
        $closureParams = $options['closure-params'] ;
        $path = $options['path'];

        if ($routeType == self::CLOSURE_ROUTE && !is_callable($routeTo)) {
            return false;
        }
        $routeUri = new RouterUri($this->getBase().$path, $routeTo,$caseSensitive, $closureParams);
        $routeUri->setAction($options['action']);
        
        if (!$this->_hasRoute($routeUri)) {
            if ($asApi === true) {
                $routeUri->setType(self::API_ROUTE);
            } else {
                $routeUri->setType($routeType);
            }
            $routeUri->setIsInSiteMap($incInSiteMap);

            $routeUri->setRequestMethods($options['request-methods']);
            
            foreach ($options['languages'] as $langCode) {
                $routeUri->addLanguage($langCode);
            }

            foreach ($options['vars-values'] as $varName => $varValues) {
                $routeUri->addVarValues($varName, $varValues);
            }
            $path = $routeUri->isCaseSensitive() ? $routeUri->getPath() : strtolower($routeUri->getPath());

            foreach ($options['middleware'] as $mwName) {
                $routeUri->addMiddleware($mwName);
            }

            if ($routeUri->hasVars()) {
                $this->routes['variable'][$path] = $routeUri;
            } else {
                $this->routes['static'][$path] = $routeUri;
            }

            return true;
        }

        return false;
    }
    /**
     * Checks for provided options and set defaults for the ones which are 
     * not provided.
     * 
     * @param array $options
     * 
     * @return array
     */
    private function _checkOptionsArr($options) {
        $routeTo = $options['route-to'];

        if (isset($options['case-sensitive'])) {
            $caseSensitive = $options['case-sensitive'] === true;
        } else {
            $caseSensitive = true;
        }

        $routeType = isset($options['type']) ? $options['type'] : Router::CUSTOMIZED;

        if (isset($options['in-sitemap'])) {
            $incInSiteMap = $options['in-sitemap'];
        } else {
            $incInSiteMap = false;
        }

        if (isset($options['middleware'])) {
            if (gettype($options['middleware']) == 'array') {
                $mdArr = $options['middleware'];
            } else if (gettype($options['middleware']) == 'string') {
                $mdArr = [$options['middleware']];
            } else {
                $mdArr = [];
            }
        } else {
            $mdArr = [];
        }

        if (isset($options['as-api'])) {
            $asApi = $options['as-api'] === true;
        } else {
            $asApi = false;
        }
        $closureParams = isset($options['closure-params']) && gettype($options['closure-params']) == 'array' ? 
                $options['closure-params'] : [];
        $path = isset($options['path']) ? $this->_fixUriPath($options['path']) : '';
        $languages = isset($options['languages']) && gettype($options['languages']) == 'array' ? $options['languages'] : [];
        $varValues = isset($options['vars-values']) && gettype($options['languages']) == 'array' ? $options['vars-values'] : [];
        
        $action = '';
        
        if(isset($options['action'])) {
            $trimmed = trim($options['action']);
            if (strlen($trimmed) > 0) {
                $action = $trimmed;
            }
        }
        return [
            'case-sensitive' => $caseSensitive,
            'type' => $routeType,
            'in-sitemap' => $incInSiteMap,
            'as-api' => $asApi,
            'path' => $path,
            'route-to' => $routeTo,
            'closure-params' => $closureParams,
            'languages' => $languages,
            'vars-values' => $varValues,
            'middleware' => $mdArr,
            'request-methods' => $this->_getRequestMethods($options),
            'action' => $action
        ];
    }
    
    private function _getRequestMethods($options) {
        $requestMethodsArr = [];
        if (isset($options['methods'])) {
            $methTypes = gettype($options['methods']);
            
            if ($methTypes == 'array') {
                foreach ($options['methods'] as $reqMethod) {
                    $upper = strtoupper(trim($reqMethod));
                    
                    if (in_array($upper, Request::METHODS)) {
                        $requestMethodsArr[] = $upper;
                    }
                }
            } else if ($methTypes == 'string') {
                $upper = strtoupper(trim($options['methods']));
                
                if (in_array($upper, Request::METHODS)) {
                    $requestMethodsArr[] = $upper;
                }
            }
        }
        return $requestMethodsArr;
    }
    private function _fixFilePath($path) {
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
    private function _fixUriPath($path) {
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
    private function _getFileDirAndName($absDir) {
        $expl = explode(DS, $absDir);
        $fileName = $expl[count($expl) - 1];
        $dir = substr($absDir, 0, strlen($absDir) - strlen($fileName));

        return [
            'name' => $fileName,
            'dir' => $dir
        ];
    }
    /**
     * Returns an array which contains all routes as RouteURI object.
     * 
     * @return array An array which contains all routes as RouteURI object.
     * 
     * @since 1.2
     */
    private function _getRoutes() {
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
    private function _getUriObj($path) {
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
     * @return boolean The method will return true if the given path 
     * has a route.
     * 
     * @since 1.1
     */
    private function _hasRoute($uriObj) {
        $path = $uriObj->getPath();

        if (!$uriObj->isCaseSensitive()) {
            $path = strtolower($path);
        }

        if ($uriObj->hasVars()) {
            return isset($this->routes['variable'][$path]);
        } else {
            return isset($this->routes['static'][$path]);
        }
    }
    /**
     * Checks if a directory name is a variable or not.
     * 
     * @param type $dir
     * 
     * @return boolean
     * 
     * @since 1.1
     */
    private function _isDirectoryAVar($dir) {
        return $dir[0] == '{' && $dir[strlen($dir) - 1] == '}';
    }
    /**
     * 
     * @param RouterUri $route
     */
    private function _loadResource($route) {
        $file = $route->getRouteTo();
        $info = $this->_getFileDirAndName($file);
        $fileObj = new File($info['name'], $info['dir']);
        $fileObj->read();

        if ($fileObj->getFileMIMEType() === 'text/plain') {
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
     * Display all routes details.
     * 
     * @since 1.1
     */
    private function _printRoutes() {
        foreach ($this->routes['static'] as $route) {
            $route->printUri();
        }

        foreach ($this->routes['variable'] as $route) {
            $route->printUri();
        }
    }
    /**
     * Returns the number of routes at which the router has.
     * 
     * @return int Number of routes.
     * 
     * @since 1.3.11
     */
    public static function routesCount() {
        $routesArr = self::get()->routes;
        
        return count($routesArr['variable']) + count($routesArr['static']);
    }
    /**
     * Route a given URI to its specified resource.
     * 
     * If the router has no routes, the router will send back a '418 - I'm A 
     * Teapot' response. If the route is available but the file that the 
     * router is routing to does not exist, a '500 - Server Error' Response 
     * with the message 'The resource 'a_resource' was available but its route is not configured correctly.' is 
     * sent back. If the route is not found, The router will call the function 
     * that was set by the user in case a route is not found.
     * 
     * @param string $uri A URI such as 'http://www.example.com/hello/ibrahim'
     * 
     * @param boolean $loadResource If set to true, the resource that represents the 
     * route will be loaded. If false, the route will be only resolved. Default 
     * is true.
     * 
     * @since 1.0
     */
    private function _resolveUrl($uri, $loadResource = true) {
        $this->uriObj = null;
        
        if (self::routesCount() != 0) {
            $routeUri = new RouterUri($uri, '');

            if ($routeUri->hasWWW() && defined('NO_WWW') && NO_WWW === true) {
                $this->redirectToNonWWW($routeUri);
            }
            //first, search for the URI wuthout checking variables
            if ($this->_searchRoute($routeUri, $uri, $loadResource)) {
                return;
            }
            //if no route found, try to replace variables with values 
            //note that query string vars are optional.
            if ($this->_searchRoute($routeUri, $uri, $loadResource, true)) {
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
     * @param type $loadResource
     * @return type
     * @throws RoutingException
     */
    private function _routeFound($route, $loadResource) {
        if ($route->isRequestMethodAllowed()) {
            $this->uriObj = $route;
            $route->getMiddlewar()->insertionSort(false);

            foreach ($route->getMiddlewar() as $mw) {
                $mw->before();
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

                if (class_exists($xFile)) {
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
                        $file = ROOT_DIR.$routeType.$this->_fixFilePath($file);
                    } else {
                        $file = ROOT_DIR.$this->_fixFilePath($file);
                    }

                    if (gettype($file) == 'string' && file_exists($file)) {
                        if ($loadResource === true) {
                            $route->setRoute($file);
                            $this->_loadResource($route);
                        }
                    } else {
                        if ($loadResource === true) {
                            $message = 'The resource "'.Util::getRequestedURL().'" was availble. '
                            .'but its route is not configured correctly. '
                            .'The resource which the route is pointing to was not found.';

                            if (defined('WF_') && WF_VERBOSE) {
                                $message = 'The resource "'.Util::getRequestedURL().'" was availble. '
                                .'but its route is not configured correctly. '
                                .'The resource which the route is pointing to was not found ('.$file.').';
                            }
                            throw new RoutingException($message);
                        }
                    }
                }
            }
        } else {
            Response::setCode(405);

            if (!defined('API_CALL')) {
                $notFoundView = new HTTPCodeView(405);
                $notFoundView->render();
            } else {
                $json = new Json([
                    'message' => 'Request method not allowed.',
                    'type' => 'error'
                ]);
                Response::write($json);
            }
        }
    }
    /**
     * 
     * @param RouterUri $routeUri
     * @param type $uri
     * @param type $loadResource
     * @param type $withVars
     * @return boolean
     */
    private function _searchRoute($routeUri, $uri, $loadResource, $withVars = false) {
        $pathArray = $routeUri->getPathArray();
        $requestMethod = Request::getMethod();
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
                    $this->_routeFound($route, $loadResource);

                    return true;
                }
            }
        } else {
            foreach ($this->routes['variable'] as $route) {
                $this->_setUriVars($route, $pathArray, $requestMethod);

                if ($route->isAllVarsSet() && $route->setRequestedUri($uri)) {
                    $this->_routeFound($route, $loadResource);

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
    private function _setOnNotFound($function) {
        if (is_callable($function)) {
            $this->onNotFound = $function;
        }
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
    private function _setUriVars($uriRouteObj, $requestedPathArr, $requestMethod) {
        $routePathArray = $uriRouteObj->getPathArray();

        if (count($routePathArray) == count($requestedPathArr)) {
            $pathVarsCount = count($routePathArray);

            for ($x = 0 ; $x < $pathVarsCount ; $x++) {
                if ($this->_isDirectoryAVar($routePathArray[$x])) {
                    $varName = trim($routePathArray[$x], '{}');
                    $uriRouteObj->setUriVar($varName, $requestedPathArr[$x]);

                    if ($requestMethod == 'POST' || $requestMethod == 'PUT') {
                        $_POST[$varName] = filter_var(urldecode($requestedPathArr[$x]),FILTER_SANITIZE_STRING);
                    } else {
                        if ($requestMethod == 'GET' || $requestMethod == 'DELETE' || CLI::isCLI()) {
                            //usually, in CLI there is no request method. 
                            //but we store result in $_GET.
                            $_GET[$varName] = filter_var(urldecode($requestedPathArr[$x]),FILTER_SANITIZE_STRING);
                        }
                    }
                } else {
                    if ((!$uriRouteObj->isCaseSensitive() && (strtolower($routePathArray[$x]) != strtolower($requestedPathArr[$x]))) || $routePathArray[$x] != $requestedPathArr[$x]) {
                        break;
                    }
                }
            }
        }
    }
    /**
     * Creates and Returns a single instance of the router.
     * 
     * @return Router
     * 
     * @since 1.0
     */
    private static function get() {
        if (self::$router != null) {
            return self::$router;
        }
        self::$router = new Router();

        return self::$router;
    }
    /**
     * Send http 301 response code and redirect the request to non-www URI.
     * 
     * @param RouterUri $uriObj
     */
    private function redirectToNonWWW($uriObj) {
        Response::setCode(301);
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
        Response::addHeader('location', $uriObj->getScheme().'://'.$host.$port.$path.$queryString.$fragment);
        Response::send();
    }
}
