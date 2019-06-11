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
namespace webfiori\entity\router;
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 404 Not Found");
    die('<!DOCTYPE html><html><head><title>Not Found</title></head><body>'
    . '<h1>404 - Not Found</h1><hr><p>The requested resource was not found on the server.</p></body></html>');
}
use webfiori\conf\SiteConfig;
use webfiori\entity\Util;
use jsonx\JsonX;
/**
 * The basic class that is used to route user requests to the correct 
 * location.
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
 * '/pages'. They are simply web pages (HTML or PHP).
 * </p>
 * <p>An API route is a route that points to a file which exist inside the 
 * directory '/apis'. This folder usually contains PHP files which extends 
 * the class 'ExtendedWebAPI' or the class 'WebAPI'.
 * </p>
 * <b>
 * A closure route is simply a function that will be executed when the 
 * user visits the URL.
 * </b>
 * <p>
 * A customized route is a route that can point to any file which exist inside 
 * the framework scope. For example, The developer might create a folder 
 * 'my-files' and inside it, he might add 'my-view.html'. Then he can add a route 
 * to it as follows:
 * <pre>
 * Router::other('/test-custom','/my-files/my-view.html');
 * </pre> 
 * </p>
 * @author Ibrahim
 * @version 1.3.5
 */
class Router {
    /**
     * A constant for the route of views. It is simply the root directory where web 
     * pages should be created.
     * @since 1.0
     */
    const VIEW_ROUTE = '/pages';
    /**
     * A constant for the route of APIs. It is simply the root directory where APIs 
     * should be created.
     * @since 1.0
     */
    const API_ROUTE = '/apis';
    /**
     * A constant for the case when the route is a function call.
     * @since 1.0
     */
    const CLOSURE_ROUTE = 'func';
    /**
     * A constant for custom directory route.
     * @since 1.0
     */
    const CUSTOMIZED = '/';
    /**
     * A callback function to call in case if a rout is 
     * not found.
     * @var callable 
     * @since 1.0
     */
    private $onNotFound;
    /**
     * An object of type 'RouterUri' which represents the route that the 
     * user was sent to.
     * @var RouterUri 
     * @since 1.3.5
     */
    private $uriObj;
    /**
     * A single instance of the router.
     * @var Router
     * @since 1.0 
     */
    private static $router;
    /**
     *
     * @var type 
     * @since 1.1
     */
    private $baseUrl;
    /**
     * Creates and Returns a single instance of the router.
     * @return Router
     * @since 1.0
     */
    public static function get(){
        if(self::$router != NULL){
            return self::$router;
        }
        self::$router = new Router();
        return self::$router;
    }
    /**
     * An array which contains an objects of type 'RouteUri'.
     * @var array
     * @since 1.0 
     */
    private $routes;
    /**
     * Creates new instance of 'Router'
     * @since 1.0
     */
    private function __construct() {
        $this->routes = array();
        $this->onNotFound = function (){
            header("HTTP/1.1 404 Not found");
            die(''
                    . '<!DOCTYPE html>'
                    . '<html>'
                    . '<head>'
                    . '<title>Not Found</title>'
                    . '</head>'
                    . '<body>'
                    . '<h1>404 - Not Found</h1>'
                    . '<hr>'
                    . '<p>'
                    . 'The resource <b>'.Util::getRequestedURL().'</b> was not found on the server.'
                    . '</p>'
                    . '</body>'
                    . '</html>');
        };
        if(class_exists('webfiori\conf\SiteConfig')){
            $this->baseUrl = trim(SiteConfig::getBaseURL(), '/');
        }
        else{
            $this->baseUrl = trim(Util::getBaseURL(), '/');
        }
    }
    /**
     * Adds an object of type 'RouterUri' as new route.
     * @param RouterUri $routerUri An object of type 'RouterUri'.
     * @return boolean If the object is added as new route, the method will 
     * return true. If the given parameter is not an instance of 'RouterUri' 
     * or a route is already added, The method will return false.
     * @since 1.3.2
     */
    public static function uriObj($routerUri){
        if($routerUri instanceof RouterUri){
            if(!self::get()->hasRoute($routerUri->getPath())){
                self::get()->routes[] = $routerUri;
                return true;
            }
        }
        return false;
    }
    /**
     * Returns an object of type 'RouterUri' that represents route URI.
     * @param string $path The path part of the URI.
     * @return RouterUri|NULL If a route was found which has the given path, 
     * an object of type 'RouterUri' is returned. If no route is found, NULL 
     * is returned.
     * @since 1.3.3
     */
    private function &_getUriObj($path) {
        $routeURI = new RouterUri($this->getBase().$this->fixPath($path), '');
        foreach ($this->routes as $route){
            if($routeURI->equals($route)){
                return $route;
            }
        }
        $null = NULL;
        return $null;
    }
    /**
     * Returns an object of type 'RouterUri' that represents route URI.
     * @param string $path The path part of the URI.
     * @return RouterUri|NULL If a route was found which has the given path, 
     * an object of type 'RouterUri' is returned. If no route is found, NULL 
     * is returned.
     * @since 1.3.3
     */
    public static function getUriObj($path) {
        $uri = self::get()->_getUriObj($path);
        return $uri;
    }
    /**
     * Adds a route to a basic xml site map. 
     * If this method is called, a route in the form 'http://example.com/sitemam.xml' 
     * The method will check all created RouterUri objects and check if they 
     * should be included in the site map.
     * @since 1.3.2
     */
    public static function incSiteMapRoute(){
        self::closure('/sitemap.xml', function(){
            $urlSet = new HTMLNode('urlset');
            $urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            $urlSet->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
            $routes = Router::get()->getRoutes();
            foreach ($routes as $route){
                if($route->isInSiteMap()){
                    $url = new HTMLNode('url');
                    $loc = new HTMLNode('loc');
                    $loc->addChild(HTMLNode::createTextNode($route->getUri()));
                    $url->addChild($loc);
                    $urlSet->addChild($url);
                }
            }
            $retVal = '<?xml version="1.0" encoding="UTF-8"?>';
            $retVal .= $urlSet->toHTML();
            header('content-type:text/xml');
            echo $retVal;
        });
    }
    /**
     * Redirect a URI to its route.
     * @param string $uri The URI.
     * @since 1.2
     */
    public static function route($uri) {
        Router::get()->sendToRoute($uri);
    }
    /**
     * Returns the value of the base URL which is appended to the path.
     * @return string
     * @since 1.0
     */
    public function getBase() {
        return $this->baseUrl;
    }
    /**
     * Adds new route to the router.
     * @param string $path The path part of the URI (e.g. '/en/one/two').
     * @param string|callable $routeTo The location where the URI is going 
     * to route to. It can be either a callable or a string which represents 
     * the path to a PHP file.
     * @param string $routeType The type of the route. It can have one of 4 
     * values:
     * <ul>
     * <li><b>Router::VIEW_ROUTE</b>: If the PHP file is inside the folder 
     * '/pages' or in other sub-directory under the same folder.</li>
     * <li><b>Router::API_ROUTE</b> : If the PHP file is inside the folder '/apis' 
     * or in other sub-directory under the same folder.</li>
     * <li><b>Router::CUSTOMIZED</b> : If the PHP file is inside the root folder or in 
     * other sub-directory under the root.</li>
     * <li><b>Router::CLOSURE_ROUTE</b> If the route is a closure.</li>
     * </ul>
     * @param array $closureParams If the route type is closure route, 
     * it is possible to pass values to it using this array.
     * @param boolean $incInSiteMap If set to true, the given route will be added to 
     * the basic site map which can be generated by the router class.
     * @param boolean $asApi If this parameter is set to true, the route will be 
     * treated as if it was an API route. This means that the constant 'API_ROUTE' 
     * will be initiated when a request is made to the route. Default is false.
     * @return boolean If the route is added, the method will return true. 
     * The method one return false only in two cases, either the route type 
     * is not correct or a similar route was already added.
     * @since 1.0
     */
    public function addRoute($path,$routeTo,$routeType,$closureParams=array(),$incInSiteMap=false,$asApi=false) {
        if(strlen($this->getBase()) != 0){
            if($routeType == self::API_ROUTE || 
                $routeType == self::VIEW_ROUTE || 
                $routeType == self::CUSTOMIZED || 
                $routeType == self::CLOSURE_ROUTE){
                if($routeType != self::CLOSURE_ROUTE){
                    $path = $this->fixPath($path);
                    $routeTo = ROOT_DIR.$this->fixPath($routeType.$routeTo);
                }
                else{
                    if(!is_callable($routeTo)){
                        return false;
                    }
                }
                if(!$this->hasRoute($path)){
                    $routeUri = new RouterUri($this->getBase().$path, $routeTo, $closureParams);
                    if($asApi === true){
                        $routeUri->setType(self::API_ROUTE);
                    }
                    else{
                        $routeUri->setType($routeType);
                    }
                    $routeUri->setIsInSiteMap($incInSiteMap);
                    $this->routes[] = $routeUri;
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * Returns an associative array of all available routes.
     * @return array An associative array of all available routes. The 
     * keys will be requested URIs and the values are the routes.
     * @since 1.2
     */
    public static function routes() {
        $routesArr = array();
        foreach (Router::get()->getRoutes() as $routeUri){
            $routesArr[$routeUri->getUri()] = $routeUri->getRouteTo();
        }
        return $routesArr;
    }
    /**
     * Returns an array which contains all routes as RouteURI object.
     * @return array An array which contains all routes as RouteURI object.
     * @since 1.2
     */
    public function getRoutes() {
        return $this->routes;
    }
    /**
     * Adds new route to a view file.
     * A view file can be any file that is added inside the folder '/pages'.
     * @param string $path The path part of the URI. For example, if the 
     * requested URI is 'http://www.example.com/user/ibrahim', the path 
     * part of the URI is '/user/ibrahim'. It is possible to include variables 
     * in the path. To include a variable in the path, its name must be enclosed 
     * between {}. The value of the variable will be stored in either the array 
     * $_GET or $_POST after the requested URI is resolved. If we use the same 
     * example above to get any user profile, We would add the following as 
     * a path: 'user/{username}'. In this case, username will be available in 
     * $_GET['username']. 
     * @param string $viewFile The path to the view file. The root folder for 
     * all views is '/pages'. If the view name is 'view-user.php', then the 
     * value of this parameter must be '/view-user.php'. If the view is in a 
     * sub-directory inside the views directory, then the name of the 
     * directory must be included.
     * @return boolean The method will return true if the route was created. 
     * If a route for the given path was already created, the method will return 
     * false.
     * @since 1.2
     */
    public static function view($path,$viewFile) {
        return Router::get()->addRoute($path, $viewFile, Router::VIEW_ROUTE);
    }
    /**
     * Adds new route to an API file.
     * @param string $path The path part of the URI. For example, if the 
     * requested URI is 'http://www.example.com/user/ibrahim', the path 
     * part of the URI is '/user/ibrahim'. It is possible to include variables 
     * in the path. To include a variable in the path, its name must be enclosed 
     * between {}. The value of the variable will be stored in either the array 
     * $_GET or $_POST after the requested URI is resolved. If we use the same 
     * example above to get any user profile, We would add the following as 
     * a path: 'user/{username}'. In this case, username will be available in 
     * $_GET['username']. 
     * @param string $apiFile The path to the API file. The root folder for 
     * all APIs is '/apis'. If the API name is 'get-user-profile.php', then the 
     * value of this parameter must be '/get-user-profile.php'. If the API is in a 
     * sub-directory inside the APIs directory, then the name of the 
     * directory must be included.
     * @return boolean The method will return true if the route was created. 
     * If a route for the given path was already created, the method will return 
     * false. Also if the given view file was not found, the method will not 
     * create any route and return false.
     * @since 1.2
     */
    public static function api($path,$apiFile) {
        return Router::get()->addRoute($path, $apiFile, Router::API_ROUTE);
    }
    /**
     * Returns the base URL which is used to create routes.
     * @return string The base URL which is used to create routes. The returned 
     * value is based on one of two values. Either the value that is returned 
     * by the method 'Util::getBaseURL()' or the method 'SiteConfig::getBaseURL()'.
     * @since 1.3.1
     */
    public static function base(){
        return Router::get()->getBase();
    }

    /**
     * Adds new closure route.
     * @param string $path The path part of the URI. For example, if the 
     * requested URI is 'http://www.example.com/user/ibrahim', the path 
     * part of the URI is '/user/ibrahim'. It is possible to include variables 
     * in the path. To include a variable in the path, its name must be enclosed 
     * between {}. The value of the variable will be stored in either the array 
     * $_GET or $_POST after the requested URI is resolved. If we use the same 
     * example above to get any user profile, We would add the following as 
     * a path: 'user/{username}'. In this case, username will be available in 
     * $_GET['username']. 
     * @param callable $closure A closure.
     * @return boolean The method will return true if the route was created. 
     * If a route for the given path was already created, the method will return 
     * false. Also if the given view file was not found, the method will not 
     * create any route and return false.
     * @param array $closureParams If the route type is closure route, 
     * it is possible to pass values to it using this array.
     * @param boolean $asApi If this parameter is set to true, the route will be 
     * treated as if it was an API route. This means that the constant 'API_ROUTE' 
     * will be initiated when a request is made to the route. Default is false.
     * @since 1.2
     */
    public static function closure($path,$closure,$closureParams=array(),$asApi=false) {
        return Router::get()->addRoute($path, $closure, Router::CLOSURE_ROUTE,$closureParams,false,$asApi);
    }
    /**
     * Adds new route to a file inside the root folder.
     * @param string $path The path part of the URI. For example, if the 
     * requested URI is 'http://www.example.com/user/ibrahim', the path 
     * part of the URI is '/user/ibrahim'. It is possible to include variables 
     * in the path. To include a variable in the path, its name must be enclosed 
     * between {}. The value of the variable will be stored in either the array 
     * $_GET or $_POST after the requested URI is resolved. If we use the same 
     * example above to get any user profile, We would add the following as 
     * a path: 'user/{username}'. In this case, username will be available in 
     * $_GET['username']. 
     * @param string $route The path to the file. It can be any file in the scope 
     * of the variable ROOT_DIR.
     * @param boolean $asApi If this parameter is set to true, the route will be 
     * treated as if it was an API route. This means that the constant 'API_ROUTE' 
     * will be initiated when a request is made to the route. Default is false.
     * @return boolean The method will return true if the route was created. 
     * If a route for the given path was already created, the method will return 
     * false. Also if the given view file was not found, the method will not 
     * create any route and return false.
     * @since 1.2
     */
    public static function other($path,$route, $asApi=false) {
        return Router::get()->addRoute($path, $route, Router::CUSTOMIZED,null,false,$asApi);
    }
    /**
     * Display all routes details.
     * @since 1.1
     */
    public function printRoutes() {
        foreach ($this->routes as $route){
            $route->printUri();
        }
    }
    /**
     * Removes any extra forward slash in the beginning or the end.
     * @param string $path Any string that represents the path part of a URI.
     * @return string A string in the format '/nice/work/boy'.
     * @since 1.1
     */
    private function fixPath($path) {
        if($path != '/'){
            if($path[strlen($path) - 1] == '/' || $path[0] == '/'){
                while($path[0] == '/' || $path[strlen($path) - 1] == '/'){
                    $path = trim($path, '/');
                }
                $path = '/'.$path;
            }
            if($path[0] != '/'){
                $path = '/'.$path;
            }
        }
        return $path;
    }
    /**
     * Checks if a given path has a route or not.
     * @param string $path The path which will be checked (such as '/path1/path2')
     * @return boolean The method will return true if the given path 
     * has a route.
     * @since 1.1
     */
    public function hasRoute($path) {
        $hasRoute = false;
        $routeURI = new RouterUri($this->getBase().$this->fixPath($path), '');
        foreach ($this->routes as $route){
            $hasRoute = $hasRoute || $routeURI->equals($route);
        }
        return $hasRoute;
    }
    /**
     * Sets a callback to call in case a given rout is not found.
     * @param callable $function The function which will be called if 
     * the rout is not found.
     * @since 1.0
     */
    public function setOnNotFound($function) {
        if(is_callable($function)){
            $this->onNotFound = $function;
        }
    }
    /**
     * Returns an object of type 'RouterUri' which contains route information.
     * When the method Router::route() is called and a route is found, an 
     * object of type 'RouterUri' is created which has route information. 
     * @return RouterUri|null An object which has route information. If the 
     * method 'Router::route()' is not yet called or no route was found, 
     * the method will return null.
     * @since 1.3.5
     */
    public static function getRouteUri() {
        return self::get()->uriObj;
    }
    /**
     * Route a given URI to its specified route.
     * If the router has no routes, the router will send back a '418 - I'm A 
     * Teapot' response. If the route is available but the file that the 
     * router is routing to does not exist, a '500 - Server Error' Response 
     * with the message 'The resource 'a_resource' was available but its route is not configured correctly.' is 
     * sent back. If the route is not found, The router will call the function 
     * that was set by the user in case a route is not found.
     * @param string $uri A URI such as 'http://www.example.com/hello/ibrahim'
     * @since 1.0
     */
    public function sendToRoute($uri) {
        if(count($this->routes) != 0){
            $routeUri = new RouterUri($uri, '');
            //first, search for the URI wuthout checking variables
            foreach ($this->routes as $route){
                if(!$route->hasVars()){
                    if($route->getUri() == $routeUri->getUri()){
                        if(is_callable($route->getRouteTo())){
                            call_user_func($route->getRouteTo(),$route->getClosureParams());
                            $this->uriObj = $route;
                            return;
                        }
                        else{
                            $file = $route->getRouteTo();
                            if(file_exists($file)){
                                $this->uriObj = $route;
                                require_once $file;
                            }
                            else{
                                header("HTTP/1.1 500 Server Error");
                                if($route->getType() == self::API_ROUTE){
                                    $j = new JsonX();
                                    $j->add('message', 'The resource \''.Util::getRequestedURL().'\' was availble but its route is not configured correctly.');
                                    $j->add('type', 'error');
                                    die($j.'');
                                }
                                else{
                                    die(''
                                    . '<!DOCTYPE html>'
                                    . '<html>'
                                    . '<head>'
                                    . '<title>Server Error</title>'
                                    . '</head>'
                                    . '<body>'
                                    . '<h1>500 - Server Error</h1>'
                                    . '<hr>'
                                    . '<p>'
                                    . 'The resource <b>'.Util::getRequestedURL().'</b> was availble. but its route is not configured correctly.'
                                    . '</p>'
                                    . '</body>'
                                    . '</html>');
                                }
                            }
                            return;
                        }
                    }
                }
            }
            //if no route found, try to replace variables with values 
            //note that query string vars are optional.
            $pathArray = $routeUri->getPathArray();
            $requestMethod = filter_var(getenv('REQUEST_METHOD'));
            foreach ($this->routes as $route){
                if($route->hasVars()){
                    $routePathArray = $route->getPathArray();
                    if(count($routePathArray) == count($pathArray)){
                        for($x = 0 ; $x < count($routePathArray) ; $x++){
                            if($this->isDirectoryAVar($routePathArray[$x])){
                                $varName = trim($routePathArray[$x], '{}');
                                $route->setUriVar($varName, $pathArray[$x]);
                                if($requestMethod == 'POST' || $requestMethod == 'PUT'){
                                    $_POST[$varName] = $pathArray[$x];
                                }
                                else if($requestMethod == 'GET' || $requestMethod == 'DELETE'){
                                    $_GET[$varName] = $pathArray[$x];
                                }
                            }
                            else if($routePathArray[$x] != $pathArray[$x]){
                                break;
                            }
                        }
                    }
                    //if all variables are set, then we found our route.
                    if($route->isAllVarsSet()){
                        if(is_callable($route->getRouteTo())){
                            $this->uriObj = $route;
                            call_user_func($route->getRouteTo(),$route->getClosureParams());
                            return;
                        }
                        else{
                            if($route->getType() == self::API_ROUTE){
                                define('API_CALL', true);
                            }
                            $this->uriObj = $route;
                            require_once $route->getRouteTo();
                            return;
                        }
                    }
                }
            }
            //if we reach this part, this means the route was not found
            call_user_func($this->onNotFound);
        }
        else{
            header("HTTP/1.1 418 I'm a teapot");
            die(''
                    . '<!DOCTYPE html>'
                    . '<html>'
                    . '<head>'
                    . '<title>I\'m a teapot</title>'
                    . '</head>'
                    . '<body>'
                    . '<h1>418 - I\'m a teabot</h1>'
                    . '<hr>'
                    . '<p>'
                    . 'Acctually, I\'m an empty teapot since I don\'t have routes yet.'
                    . '</p>'
                    . '</body>'
                    . '</html>');
        }
    }
    /**
     * Remove all routes which has been added to the array of routes.
     * This method is similar to calling the method Router::clear()
     * @since 1.3.4
     */
    public static function removeAll() {
        self::get()->clear();
    }
    /**
     * Checks if a directory name is a variable or not.
     * @param type $dir
     * @return boolean
     * @since 1.1
     */
    private function isDirectoryAVar($dir){
        return $dir[0] == '{' && $dir[strlen($dir) - 1] == '}';
    }
    /**
     * Removes all added routes.
     * This method will simply re-initialize the array that contains all 
     * routes.
     * @since 1.0
     * @deprecated since version 1.3.4
     */
    public function clear() {
        $this->routes = array();
    }
}
