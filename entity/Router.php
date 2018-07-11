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
 * @version 1.0
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
     * An associative array that contains all defined routes.
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
//        $this->addRoute('/', 'default.html', self::VIEW_ROUTE);
//        $this->addRoute('/index', 'default.html', self::VIEW_ROUTE);
    }
    /**
     * 
     * @param string $rquestedUri
     * @param type $routeTo
     * @param type $routeType
     * @return boolean
     * @since 1.0
     */
    public function addRoute($rquestedUri,$routeTo,$routeType) {
        if($routeType == self::API_ROUTE || 
           $routeType == self::VIEW_ROUTE || 
           $routeType == self::CUSTOMIZED || $routeType == self::FUNCTION_ROUTE){
            $requestedUriBoken = Router::splitURI($rquestedUri);
            if($requestedUriBoken['protocol'] == ''){
                $rquestedUri = trim(SiteConfig::get()->getBaseURL(),'/').$rquestedUri;
                $requestedUriBoken = Router::splitURI($rquestedUri);
            }
            if($routeType != self::FUNCTION_ROUTE){
                $routeBroken = Router::splitURI($routeTo);
                $routeFile = ROOT_DIR.$routeType.'/'.$routeBroken['uri-without-query-string'];
                if(file_exists($routeFile)){
                    $this->routes[$requestedUriBoken['uri-without-query-string']] = array(
                        'route-type'=>$routeType,
                        'requested-uri-format'=>$requestedUriBoken['uri'],
                        'route-to'=>$routeFile,
                        'variables'=>array()
                    );
                    foreach ($requestedUriBoken['uri-broken'] as $val){
                        $len = strlen($val);
                        if($val[0] == '{' && $val[$len - 1] == '}'){
                            array_push($this->routes[$requestedUriBoken['uri-without-query-string']]['variables'], $val);
                        }
                    }
                    return TRUE;
                }
            }
            else{
                $this->routes[$requestedUriBoken['uri-without-query-string']] = array(
                    'route-type'=>$routeType,
                    'requested-uri-format'=>$requestedUriBoken['uri'],
                    'route-to'=>$routeTo,
                    'variables'=>array()
                );
                foreach ($requestedUriBoken['uri-broken'] as $val){
                    $len = strlen($val);
                    if($val[0] == '{' && $val[$len - 1] == '}'){
                        array_push($this->routes[$requestedUriBoken['uri-without-query-string']]['variables'], $val);
                    }
                }
                return TRUE;
            }
        }
        return FALSE;
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
     * Breaks a URI into its basic components. This function can break 
     * URIs which uses HTTP or HTTPS protocols. If a URI has different protocol, 
     * the result will be unexpected.
     * @param string $uri The URI that will be broken.
     * @return array The function will return an associative array that 
     * contains the components of the URI. The array will have the 
     * following indices:
     * <ul>
     * <li><b>uri</b>: The original URI.</li>
     * <li><b>domain</b>: Only set if the URI has a protocol (http or https).</li>
     * <li><b>protocol</b>: http or https.</li>
     * <li><b>uri-without-query-string</b>: The URI without query string.</li>
     * <li><b>query-string</b>: Query string if the URI has any.</li>
     * <li><b>uri-broken</b>: The URI after separating each component.</li>
     * <li><b>query-string-breaked</b>: The query string broken into an arrays of keys and values.</li>
     * </ul>
     * @since 1.0
     */
    public static function splitURI($uri) {
        $retVal = array(
            'uri'=>$uri,
            'domain'=>'',
            'protocol'=>'',
            'uri-without-query-string'=>'',
            'query-string'=>'',
            'uri-broken'=>array(),
            'query-string-breaked'=>array()
        );
        //first, split query string from the URI
        $split = explode('?', $uri);
        //Query string will be in $split[1] if any
        $retVal['query-string'] = isset($split[1]) ? $split[1] : '';
        //$split[0] will contain the URI without query string.
        $retVal['uri-without-query-string'] = trim($split[0], '/');
        //after that, we need to check the start of the URI if 
        //it contains one of the following: 'http://', 'https://', 
        $splitx = explode('://', $retVal['uri-without-query-string']);
        $retVal['protocol'] = isset($splitx[1]) ? $splitx[0] : '';
        
        return $retVal;
    }
    /**
     * 
     * @param type $uri
     * @since 1.0
     */
    public function route($uri) {
        if(count($this->routes) != 0){
            $uriSplit = Router::splitURI($uri);
            if($uriSplit['protocol'] == ''){
                $uri = trim(SiteConfig::get()->getBaseURL(),'/').$uri;
                $uriSplit = Router::splitURI($uri);
            }
            $requestMethod = filter_var(getenv('REQUEST_METHOD'));
            foreach ($this->routes as $route){
                
                $this->routes[$route['requested-uri-format']]['var-values'] = $this->extractVarsValue($uri, $route);
            }
            
            foreach ($this->routes as $route){
                if(isset($route['var-values']) && count($route['var-values']) != 0){
                    foreach ($route['var-values'] as $key => $value){
                        $keyTrim = trim($key, '{');
                        if($requestMethod == 'GET' || $requestMethod == 'DELETE'){
                            $_GET[trim($keyTrim,'}')] = $value;
                        }
                        else if($requestMethod == 'POST' || $requestMethod == 'PUT'){
                            $_POST[trim($keyTrim,'}')] = $value;
                        }
                        $uri = str_replace($value, $key, $uri);
                    }
                }
                if(isset($this->routes[$uri])){
                    break;
                }
            }
            if(isset($this->routes[$uri])){
                if($this->routes[$uri]['route-type'] != self::FUNCTION_ROUTE){
                    require_once $this->routes[$uri]['route-to'];
                }
                else{
                    call_user_func($this->routes[$uri]['route-to']);
                }
                return TRUE;
            }
            else{
                call_user_func($this->onNotFound);
                return FALSE;
            }
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
