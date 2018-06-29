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

/**
 * Description of Router
 *
 * @author Ibrahim
 */
class Router {
    const VIEW_ROUTE = '/pages';
    const API_ROUTE = '/apis';
    private static $router;
    /**
     * Returns a single instance of the router.
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
        $this->addRoute('/SysAPIs', 'SysAPIs.php', self::API_ROUTE);
        $this->addRoute('/AuthAPI', 'AuthAPI.php', self::API_ROUTE);
        $this->addRoute('/FileAPIs', 'FileAPIs.php', self::API_ROUTE);
        $this->addRoute('/UserAPIs', 'UserAPIs.php', self::API_ROUTE);
        $this->addRoute('/NumsAPIs', 'NumsAPIs.php', self::API_ROUTE);
        $this->addRoute('/WebsiteAPIs', 'WebsiteAPIs.php', self::API_ROUTE);
        $this->addRoute('/PasswordAPIs', 'PasswordAPIs.php', self::API_ROUTE);
        $this->addRoute('/apis/{example}', 'ExampleAPI.php', self::API_ROUTE);
        $this->addRoute('/views/{example}', 'example-page.php', self::VIEW_ROUTE);
        $this->addRoute('/', 'default.html', self::VIEW_ROUTE);
        $this->addRoute('/index', 'login.php', self::VIEW_ROUTE);
        $this->addRoute('/setup/welcome', 'setup/welcome.php', self::VIEW_ROUTE);
        $this->addRoute('/setup/admin-account', 'setup/admin-account.php', self::VIEW_ROUTE);
        $this->addRoute('/setup/database', 'setup/database-setup.php', self::VIEW_ROUTE);
        $this->addRoute('/setup/email', 'setup/email-account.php', self::VIEW_ROUTE);
        $this->addRoute('/setup/website', 'setup/website-config.php', self::VIEW_ROUTE);
        $this->addRoute('/home', 'home.php', self::VIEW_ROUTE);
        $this->addRoute('/login', 'login.php', self::VIEW_ROUTE);
        $this->addRoute('/logout', 'logout.php', self::VIEW_ROUTE);
        $this->addRoute('/new-password', 'new-password.php', self::VIEW_ROUTE);
        $this->addRoute('/activate-account', 'activate-account.php', self::VIEW_ROUTE);
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
        if($routeType == self::API_ROUTE || $routeType == self::VIEW_ROUTE){
            $requestedUriBoken = Router::splitURI($rquestedUri);
            $routeBroken = Router::splitURI($routeTo);
            if($requestedUriBoken['protocol'] == ''){
                $rquestedUri = trim(SiteConfig::get()->getBaseURL(),'/').$rquestedUri;
                $requestedUriBoken = Router::splitURI($rquestedUri);
            }
            $routeFile = ROOT_DIR.$routeType.'/'.$routeBroken['uri-without-query-string'];
            if(file_exists($routeFile)){
                $this->routes[$requestedUriBoken['uri-without-query-string']] = array(
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
        return FALSE;
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
        $split = explode('?', $uri);
        $retVal['query-string'] = isset($split[1]) ? $split[1] : '';
        $retVal['uri-without-query-string'] = trim($split[0], '/');
        $uriSplit = explode('/', $retVal['uri-without-query-string']);
        $uriSplitCount = count($uriSplit);
        for ($x = 0 ; $x < $uriSplitCount ; $x++){
            if($uriSplit[$x] != ''){
                if($uriSplit[$x] == 'http:' || $uriSplit[$x] == 'https:'){
                    $retVal['protocol'] = trim($uriSplit[$x], ':');
                }
                else{
                    if($x == 2 && ($retVal['protocol'] == 'http' || $retVal == 'https')){
                        $retVal['domain'] = trim($uriSplit[$x], 'www.');
                    }
                    else{
                        array_push($retVal['uri-broken'], $uriSplit[$x]);
                    }
                }
            }
        }
        if($retVal['query-string'] != ''){
            $queryStringSplit = explode('&', $retVal['query-string']);
            foreach ($queryStringSplit as $val){
                $qSplit = explode('=', $val);
                $arr = array('key'=>$qSplit[0],'value'=>$qSplit[1]);
                array_push($retVal['query-string-breaked'], $arr);
            }
        }
        return $retVal;
    }
    /**
     * 
     * @param type $uri
     * @since 1.0
     */
    public function route($uri) {
        $uriSplit = Router::splitURI($uri);
        if($uriSplit['protocol'] == ''){
            $uri = trim(SiteConfig::get()->getBaseURL(),'/').$uri;
            $uriSplit = Router::splitURI($uri);
        }
        $origUri = $uri;
        foreach ($this->routes as $route){
            $this->routes[$route['requested-uri-format']]['var-values'] = $this->extractVarsValue($uri, $route);
        }
        foreach ($this->routes as $route){
            if(isset($route['var-values']) && count($route['var-values']) != 0){
                foreach ($route['var-values'] as $key => $value){
                    $keyTrim = trim($key, '{');
                    $_GET[trim($keyTrim,'}')] = $value;
                    $uri = str_replace($value, $key, $uri);
                }
            }
        }
        if(isset($this->routes[$uri])){
            require_once $this->routes[$uri]['route-to'];
        }
        else{
            http_response_code(404);
            echo 'The resource at <b>'.$origUri.'</b> was Not Found';
        }
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
