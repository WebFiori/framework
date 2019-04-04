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
 * A class that is used to split URIs and get their parameters.
 * The main aim of this class is to extract URI parameters including:
 * <ul>
 * <li>Host</li>
 * <li>Authority</li>
 * <li>Fragment (if any)</li>
 * <li>Path</li>
 * <li>Port (if any)</li>
 * <li>Query string (if any)</li>
 * <li>Scheme</li>
 * </ul>
 * The class is also used for routing.
 * For more information on URI structure, visit <a target="_blank" href="https://en.wikipedia.org/wiki/Uniform_Resource_Identifier#Examples">Wikipedia</a>.
 * @author Ibrahim
 * @version 1.3
 */
class RouterUri {
    /**
     * The route which this URI will be routing to.
     * @var mixed This route can be a file or a function.
     * @since 1.0 
     */
    private $routeTo;
    /**
     * The URI broken into its sub-components (scheme, authority ...) as an associative 
     * array.
     * @var array 
     * @since 1.0
     */
    private $uriBroken;
    /**
     * The type of the route.
     * @var string
     * @since 1.1 
     */
    private $type;
    /**
     * 
     * @var type 
     * @since 1.2
     */
    private $closureParams = array();
    /**
     * A boolean value that is set to TRUE if the URI will be included in 
     * generated site map.
     * @var boolean 
     * @since 1.3
     */
    private $incInSiteMap;
    /**
     * Creates new instance.
     * @param string $requestedUri The URI such as 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz'
     * @param string $routeTo The file that the route will take the user to ar a closure.
     * @param array $closureParams If the closure needs to use parameters, 
     * it is possible to supply them using this array.
     */
    public function __construct($requestedUri,$routeTo,$closureParams=array()) {
        $this->setRoute($routeTo);
        $this->uriBroken = self::splitURI($requestedUri);
        $this->setClosureParams($closureParams);
        $this->incInSiteMap = FALSE;
    }
    /**
     * Checks if the URI will be included in auto-generated site map or not.
     * @return boolean If the URI will be included, the method will return 
     * TRUE. Default is FALSE.
     * @since 1.3
     */
    public function isInSiteMap(){
        return $this->incInSiteMap;
    }
    /**
     * Sets the value of the property '$incInSiteMap'.
     * @param boolean $bool If TRUE is given, the URI will be included 
     * in site map.
     * @since 1.3
     */
    public function setIsInSiteMap($bool) {
        $this->incInSiteMap = $bool === TRUE ? TRUE : FALSE;
    }
    /**
     * Returns the type of element that the URI will route to.
     * The type of the element can be 1 of 4 values:
     * <ul>
     * <li>Router::API_ROUTE</li>
     * <li>Router::VIEW_ROUTE</li>
     * <li>Router::CLOSURE_ROUTE</li>
     * <li>Router::CUSTOMIZED</li>
     * </ul>
     * @return string The type of element that the URI will route to.
     * @since 1.1
     */
    public function getType() {
        return $this->type;
    }
    /**
     * Sets the type of element that the URI will route to.
     * The type of the element can be 1 of 4 values:
     * <ul>
     * <li>Router::API_ROUTE</li>
     * <li>Router::VIEW_ROUTE</li>
     * <li>Router::CLOSURE_ROUTE</li>
     * <li>Router::CUSTOMIZED</li>
     * </ul>
     * @param string $type The type of element that the URI will route to.
     * @since 1.1
     */
    public function setType($type) {
        $this->type = $type;
    }
    /**
     * Sets the array of closure parameters.
     * @param array $arr An array that contains all the values that will be 
     * passed to the closure.
     * @since 1.2
     */
    public function setClosureParams($arr){
        if(gettype($arr) == 'array'){
            $this->closureParams = $arr;
        }
    }
    /**
     * Returns an array that contains the variables which will be passed to 
     * the closure.
     * @return array
     * @since 1.2
     */
    public function getClosureParams() {
        return $this->closureParams;
    }
    /**
     * Checks if all URI variables has values or not.
     * @return boolean The function will return TRUE if all URI 
     * variables have a value other than NULL.
     * @since 1.0
     */
    public function isAllVarsSet() {
        $canRoute = TRUE;
        foreach ($this->getUriVars() as $key => $val){
            $canRoute = $canRoute && $val != NULL;
        }
        return $canRoute;
    }
    /**
     * Print the details of the generated URI.
     * This method will use the method 'Util::print_r()' to print the array 
     * that contains URI details.
     * @since 1.0
     */
    public function printUri() {
        Util::print_r($this->uriBroken);
    }
    /**
     * Returns the location where the URI will route to.
     * @return string|callable Usually, the route can be either a callable 
     * or a path to a file. The file can be of any type.
     * @since 1.0
     */
    public function getRouteTo() {
        return $this->routeTo;
    }
    /**
     * Sets the route which the URI will take to.
     * @param string|callable $routeTo Usually, the route can be either a 
     * file or it can be a callable. The file can be of any type.
     * @since 1.0
     */
    public function setRoute($routeTo) {
        $this->routeTo = $routeTo;
    }
    /**
     * Returns the query string that was appended to the URI.
     * @return string The query string that was appended to the URI. 
     * If the URI has no query string, the function will return empty 
     * string.
     * @since 1.0
     */
    public function getQueryString() {
        return $this->uriBroken['query-string'];
    }
    /**
     * Returns an associative array which contains query string parameters.
     * @return array An associative array which contains query string parameters. 
     * the keys will be acting as the names of the parameters and the values 
     * of each parameter will be in its key.
     * @since 1.0
     */
    public function getQueryStringVars(){
        return $this->uriBroken['query-string-vars'];
    }
    /**
     * Returns fragment part of the URI.
     * @return string Fragment part of the URI. The fragment in the URI is 
     * any string that comes after the character '#'.
     * @since 1.0
     */
    public function getFragment() {
        return $this->uriBroken['fragment'];
    }
    /**
     * Returns port number of the authority part of the URI.
     * @return string Port number of the authority part of the URI. If 
     * port number was not specified, the function will return empty string.
     * @since 1.0
     */
    public function getPort() {
        return $this->uriBroken['port'];
    }
    /**
     * Returns authority part of the URI.
     * @return string The authority part of the URI. Usually, 
     * it is a string in the form '//www.example.com:80'.
     * @since 1.0
     */
    public function getAuthority() {
        return $this->uriBroken['authority'];
    }
    /**
     * Returns the scheme part of the URI.
     * @return string The scheme part of the URI. Usually, it is called protocol 
     * (like http, ftp).
     * @since 1.0
     */
    public function getScheme() {
        return $this->uriBroken['scheme'];
    }
    /**
     * Returns an array which contains the names of URI directories.
     * @return array An array which contains the names of URI directories. 
     * For example, if the path part of the URI is '/path1/path2', the 
     * array will contain the value 'path1' at index 0 and 'path2' at index 1.
     * @since 1.0
     */
    public function getPathArray() {
        return $this->uriBroken['path'];
    }
    /**
     * Returns the path part of the URI.
     * @return string A string such as '/path1/path2/path3'.
     * @since 1.0
     */
    public function getPath() {
        $retVal = '';
        foreach ($this->uriBroken['path'] as $dir){
            $retVal .= '/'.$dir;
        }
        return $retVal;
    }
    /**
     * Returns host name from the host part of the URI.
     * @return string The host name such as 'www.programmingacademia.com'.
     * @since 1.0
     */
    public function geHost() {
        return $this->uriBroken['host'];
    }
    /**
     * Returns the original requested URI.
     * @param boolean $incQueryStr If set to TRUE, the query string part 
     * will be included in the URL.
     * @return string The original requested URI.
     * @since 1.0
     */
    public function getUri($incQueryStr=false) {
        if($incQueryStr === TRUE){
            return $this->uriBroken['uri'];
        }
        else{
            return $this->getScheme().':'.$this->getAuthority().$this->getPath();
        }
    }
    /**
     * Checks if the URI has a variable or not given its name.
     * A variable is a string which is defined while creating the route. 
     * it is name is included between '{}'.
     * @param string $varName The name of the variable.
     * @return boolean If the given variable name is exist, the function will 
     * return TRUE. Other than that, the function will return FALSE.
     * @since 1.0
     */
    public function hasUriVar($varName) {
        return array_key_exists($varName, $this->uriBroken['uri-vars']);
    }
    /**
     * Sets the value of a URI variable.
     * A variable is a string which is defined while creating the route. 
     * it is name is included between '{}'.
     * @param string $varName The name of the variable.
     * @param string $value The value of the variable.
     * @return boolean The function will return TRUE if the variable 
     * was set. If the variable does not exist, the function will return FALSE.
     * @since 1.0
     */
    public function setUriVar($varName,$value) {
        if($this->hasUriVar($varName)){
            $this->uriBroken['uri-vars'][$varName] = $value;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Returns the value of URI variable given its name.
     * A variable is a string which is defined while creating the route. 
     * it is name is included between '{}'.
     * @param string $varName The name of the variable.
     * @return string|NULL The function will return the value of the 
     * variable if found. If the variable is not set or the variable 
     * does not exist, the function will return NULL.
     * @since 1.0
     */
    public function getUriVar($varName) {
        if($this->hasUriVar($varName)){
            return $this->uriBroken['uri-vars'][$varName];
        }
        return NULL;
    }
    /**
     * Checks if the URI has any variables or not.
     * A variable is a string which is defined while creating the route. 
     * it is name is included between '{}'.
     * @return boolean If the URI has any variables, the function will 
     * return TRUE.
     * @since 1.0
     */
    public function hasVars() {
        return count($this->getUriVars()) != 0;
    }
    /**
     * Returns an associative array which contains all URI parts.
     * @return array The function will return an associative array that 
     * contains the components of the URI. The array will have the 
     * following indices:
     * <ul>
     * <li><b>uri</b>: The original URI.</li>
     * <li><b>port</b>: The port number taken from the authority part.</li>
     * <li><b>host</b>: Will be always empty string.</li>
     * <li><b>authority</b>: Authority part of the URI.</li>
     * <li><b>scheme</b>: Scheme part of the URI (e.g. http or https).</li>
     * <li><b>query-string</b>: Query string if the URI has any.</li>
     * <li><b>fragment</b>: Any string that comes after the character '#' in the URI.</li>
     * <li><b>path</b>: An array that contains the names of path directories</li>
     * <li><b>query-string-vars</b>: An array that contains query string parameter and values.</li>
     * <li><b>uri-vars</b>: An array that contains URI path variable and values.</li>
     * </ul>
     * @since 1.0
     */
    public function getComponents() {
        return $this->uriBroken;
    }
    /**
     * Returns an associative array which contains URI parameters.
     * @return array An associative array which contains URI parameters. The 
     * keys will be the names of the variables and the value of each variable will 
     * be in its index.
     * @since 1.0
     */
    public function getUriVars() {
        return $this->uriBroken['uri-vars'];
    }
    /**
     * Breaks a URI into its basic components.
     * @param string $uri The URI that will be broken.
     * @return array|boolean If the given URI is not valid, 
     * the Method will return FALSE. Other than that, The function will return an associative array that 
     * contains the components of the URI. The array will have the 
     * following indices:
     * <ul>
     * <li><b>uri</b>: The original URI.</li>
     * <li><b>port</b>: The port number taken from the authority part.</li>
     * <li><b>host</b>: Will be always empty string.</li>
     * <li><b>authority</b>: Authority part of the URI.</li>
     * <li><b>scheme</b>: Scheme part of the URI (e.g. http or https).</li>
     * <li><b>query-string</b>: Query string if the URI has any.</li>
     * <li><b>fragment</b>: Any string that comes after the character '#' in the URI.</li>
     * <li><b>path</b>: An array that contains the names of path directories</li>
     * <li><b>query-string-vars</b>: An array that contains query string parameter and values.</li>
     * <li><b>uri-vars</b>: An array that contains URI path variable and values.</li>
     * </ul>
     * @since 1.0
     */
    public static function splitURI($uri) {
        $validate = filter_var($uri,FILTER_VALIDATE_URL);
        if($validate === FALSE){
            return FALSE;
        }
        $retVal = array(
            'uri'=>$uri,
            'authority'=>'',
            'host'=>'',
            'port'=>'',
            'scheme'=>'',
            'query-string'=>'',
            'fragment'=>'',
            'path'=>array(),
            'query-string-vars'=>array(
                
            ),
            'uri-vars'=>array(
                
            ),
        );
        //First step, extract the fragment
        $split1 = explode('#', $uri);
        $retVal['fragment'] = isset($split1[1]) ? $split1[1] : '';
        
        //after that, extract the query string
        $split2 = explode('?', $split1[0]);
        $retVal['query-string'] = isset($split2[1]) ? $split2[1] : '';
        
        //next comes the scheme
        $split3 = explode(':', $split2[0]);
        $retVal['scheme'] = $split3[0];
        if(count($split3) == 3){
            //if 3, this means port number was specifyed in the URI
            $split3[1] = $split3[1].':'.$split3[2];
        }
        //now, break the remaining using / as a delemiter
        //the authority will be located at index 2 if the URI
        //follows the standatd
        \webfiori\entity\Util::print_r($split3);
        $split4 = explode('/', $split3[1]);
        $retVal['authority'] = '//'.$split4[2];
        
        //after that, we create the path from the remaining parts
        //also we check if the path has variables or not
        //a variable is a value in the path which is enclosed between {}
        for($x = 3 ; $x < count($split4) ; $x++){
            $dirName = $split4[$x];
            if($dirName != ''){
                $retVal['path'][] = utf8_decode(urldecode($dirName));
                if($dirName[0] == '{' && $dirName[strlen($dirName) - 1] == '}'){
                    $retVal['uri-vars'][trim($split4[$x], '{}')] = NULL;
                }
            }
        }
        //now extract port number from the authority (if any)
        $split5 = explode(':', $retVal['authority']);
        $retVal['port'] = isset($split5[1]) ? $split5[1] : '';
        
        //finaly, split query string and extract vars
        $split6 = explode('&', $retVal['query-string']);
        foreach ($split6 as $param){
            $split7 = explode('=', $param);
            $retVal['query-string-vars'][$split7[0]] = isset($split7[1]) ? $split7[1] : '';
//            $var = $retVal['query-string-vars'][$split7[0]];
//            if(strlen($var) > 0){
//                if($var[0] == '{' && $var[strlen($var) - 1] == '}'){
//                    $retVal['uri-vars'][trim($var, '{}')] = NULL;
//                }
//            }
        }
        return $retVal;
    }
    /**
     * Checks if two URIs are equal or not.
     * Two URIs are considered equal if they have the same authority and the 
     * same path name.
     * @param RouterUri $otherUri The URI which 'this' URI will be checked against. 
     * @return boolean The function will return TRUE if the URIs are 
     * equal.
     * @since 1.0
     */
    public function equals($otherUri) {
        if($otherUri instanceof RouterUri){
            $isEqual = TRUE;
            if($this->getAuthority() == $otherUri->getAuthority()){
                $thisPathNames = $this->getPathArray();
                $otherPathNames = $otherUri->getPathArray();
                $boolsArr = array();
                foreach ($thisPathNames as $path1){
                    $boolsArr[] = in_array($path1, $otherPathNames);
                }
                foreach ($otherPathNames as $path){
                    $boolsArr[] = in_array($path, $thisPathNames);
                }
                foreach ($boolsArr as $bool){
                    $isEqual = $isEqual && $bool;
                }
                return $isEqual;
            }
        }
        return FALSE;
    }
}
