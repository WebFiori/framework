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
    http_response_code(403);
    die('{"message":"Forbidden"}');
}
/**
 * Description of Router
 *
 * @author Ibrahim
 * @version 1.1
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
    const FUNCTION_ROUTE = 'func';
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
     * An array which contains an objects of type <b>RouteUri</b>.
     * @var array
     * @since 1.0 
     */
    private $routes;
    /**
     * Creates new instance of <b>Router</b>
     * @since 1.0
     */
    private function __construct() {
        $this->routes = array();
        $this->onNotFound = function (){};
        $this->baseUrl = trim(SiteConfig::get()->getBaseURL(), '/');
    }
    public function getBase() {
        return $this->baseUrl;
    }
    /**
     * 
     * @param string $path
     * @param type $routeTo
     * @param type $routeType
     * @return boolean
     * @since 1.0
     */
    public function addRoute($path,$routeTo,$routeType) {
        if($routeType == self::API_ROUTE || 
           $routeType == self::VIEW_ROUTE || 
           $routeType == self::CUSTOMIZED || 
           $routeType == self::FUNCTION_ROUTE){
            if(!$this->hasRoute($path)){
                $routeUri = new RouterUri($this->getBase().$path, $routeTo);
                $this->routes[] = $routeUri;
                return TRUE;
            }
        }
        return FALSE;
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
     * 
     * @param type $uri
     * @since 1.0
     */
    public function route($uri) {
        if(count($this->routes) != 0){
            
        }
        else{
            die('No routes are available.');
        }
    }
    /**
     * Removes all added routes.
     * @since 1.0
     */
    public function clear() {
        $this->routes = array();
    }
    
    public function notFound($origUri) {
        http_response_code(404);
        die('The resource at <b>'.$origUri.'</b> was Not Found');
    }
    
    private function extractVarsValue($requestedUri,$routeArr) {
        $vars = $routeArr['variables'];
        $varsCount = count($vars);
        $varsArr = array();
        if($varsCount != 0){
            $uriFormatSplit = Router::splitURI($routeArr['requested-uri-format']);
            $requestedSplit = Router::splitURI($requestedUri);
            if(count($requestedSplit['uri-broken']) == count($uriFormatSplit['uri-broken'] )){
                $index = 0;
                foreach ($uriFormatSplit['uri-broken'] as $val){
                    if(in_array($val, $vars)){
                        $varsArr[$val] = $requestedSplit['uri-broken'][$index];
                    }
                    $index++;
                }
            }
        }
        return $varsArr;
    }
}
