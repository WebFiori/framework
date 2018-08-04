<?php

/*
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
/**
 * The basic class that is used to route user requests to the correct 
 * location.
 *
 * @author Ibrahim
 * @version 1.3.1
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
     * @var Function 
     * @since 1.0
     */
    private $onNotFound;
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
        if(class_exists('SiteConfig')){
            $this->baseUrl = trim(SiteConfig::get()->getBaseURL(), '/');
        }
        else{
            $this->baseUrl = trim(Util::getBaseURL(), '/');
        }
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
     * @param string|Function $routeTo The location where the URI is going 
     * to route to. It can be either a function or a string which represents 
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
     * <li><b>Router::CLOSURE_ROUTE</b> If the route is a closure function.</li>
     * </ul>
     * @param array $closureParams [Optinal] If the route type is closure route, 
     * it is possible to pass values to it using this array.
     * @return boolean If the route is added, the function will return <b>TRUE</b>. 
     * The function one return <b>FALSE</b> only in two cases, either the route type 
     * is not correct or a similar route was already added.
     * @since 1.0
     */
    public function addRoute($path,$routeTo,$routeType,$closureParams=array()) {
        if($routeType == self::API_ROUTE || 
           $routeType == self::VIEW_ROUTE || 
           $routeType == self::CUSTOMIZED || 
           $routeType == self::CLOSURE_ROUTE){
            if($routeType != self::CLOSURE_ROUTE){
                $path = $this->fixPath($path);
                $routeTo = ROOT_DIR.$this->fixPath($routeType.$routeTo);
            }
            else{
                if(!($routeTo instanceof Closure)){
                    return FALSE;
                }
            }
            if(!$this->hasRoute($path)){
                $routeUri = new RouterUri($this->getBase().$path, $routeTo, $closureParams);
                $routeUri->setType($routeType);
                $this->routes[] = $routeUri;
                return TRUE;
            }
        }
        return FALSE;
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
     * @return boolean The function will return TRUE if the route was created. 
     * If a route for the given path was already created, the function will return 
     * FALSE. Also if the given view file was not found, the function will not 
     * create any route and return FALSE.
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
     * @return boolean The function will return TRUE if the route was created. 
     * If a route for the given path was already created, the function will return 
     * FALSE. Also if the given view file was not found, the function will not 
     * create any route and return FALSE.
     * @since 1.2
     */
    public static function api($path,$apiFile) {
        return Router::get()->addRoute($path, $apiFile, Router::API_ROUTE);
    }
    /**
     * Returns the base URL which is used to create routes.
     * @return string The base URL which is used to create routes. The returned 
     * value is based on one of two values. Either the value that is returned 
     * by the function 'Util::getBaseURL()' or the function 'SiteConfig::getBaseURL()'.
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
     * @param string $closure A closure. It is simply a function.
     * @return boolean The function will return TRUE if the route was created. 
     * If a route for the given path was already created, the function will return 
     * FALSE. Also if the given view file was not found, the function will not 
     * create any route and return FALSE.
     * @param array $closureParams [Optinal] If the route type is closure route, 
     * it is possible to pass values to it using this array.
     * @since 1.2
     */
    public static function closure($path,$closure,$closureParams=array()) {
        return Router::get()->addRoute($path, $closure, Router::CLOSURE_ROUTE,$closureParams);
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
     * @return boolean The function will return TRUE if the route was created. 
     * If a route for the given path was already created, the function will return 
     * FALSE. Also if the given view file was not found, the function will not 
     * create any route and return FALSE.
     * @since 1.2
     */
    public static function other($path,$route) {
        return Router::get()->addRoute($path, $route, Router::CUSTOMIZED);
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
     * Removes any extra forward slash in the begening or the end.
     * @param string $path
     * @return string
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
     * @return boolean The function will return <b>TRUE</b> if the given path 
     * has a route.
     * @since 1.1
     */
    public function hasRoute($path) {
        $hasRoute = FALSE;
        $routeURI = new RouterUri($this->getBase().$path, '');
        foreach ($this->routes as $route){
            $hasRoute = $hasRoute || $routeURI->equals($route);
        }
        return $hasRoute;
    }
    /**
     * Sets a function to call in case a given rout is not found.
     * @param Function $function The function which will be called if 
     * the rout is not found.
     * @since 1.0
     */
    public function setOnNotFound($function) {
        if(is_callable($function)){
            $this->onNotFound = $function;
        }
    }
    /**
     * Route a given URI to its specified route.
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
                            return;
                        }
                        else{
                            $file = $route->getRouteTo();
                            if(file_exists($file)){
                                require_once $file;
                            }
                            else{
                                header("HTTP/1.1 500 Server Error");
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
                                . 'The resource <b>'.Util::getRequestedURL().'</b> was availble. '
                                        . 'But the admin did not configure its route correctly.'
                                . '</p>'
                                . '</body>'
                                . '</html>');
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
                            call_user_func($route->getRouteTo(),$route->getClosureParams());
                            return;
                        }
                        else{
                            if($route->getType() == self::API_ROUTE){
                                define('API_CALL', TRUE);
                            }
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
     * @since 1.0
     */
    public function clear() {
        $this->routes = array();
    }
}
