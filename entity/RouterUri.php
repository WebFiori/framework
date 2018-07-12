<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if(!defined('ROOT_DIR')){
    http_response_code(403);
    die('{"message":"Forbidden"}');
}
/**
 * A class that is used to split URIs and get their parameters and others
 *
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
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
     */
    private $uriBroken;
    /**
     * Creates new instance.
     * @param string $requestedUri The URI such as 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz'
     * @param type $routeTo The 
     */
    public function __construct($requestedUri,$routeTo) {
        $this->setRoute($routeTo);
        $this->uriBroken = self::splitURI($requestedUri);
    }
    /**
     * Checks if all URI variables has values or not.
     * @return boolean The function will return <b>TRUE</b> if all URI 
     * variables have a value other than <b>NULL</b>.
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
     * Sets the route which the URI will take to.
     * @param string|function $routeTo Usually, the route can be either a 
     * PHP file or it can be a function.
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
     * Returns host name from the authority part of the URI.
     * @return string The host name such as 'www.programmingacademia.com'.
     * @since 1.0
     */
    public function geHost() {
        return $this->uriBroken['host'];
    }
    /**
     * Returns the original requested URI.
     * @return string The original requested URI.
     * @since 1.0
     */
    public function getRequestedUri() {
        return $this->uriBroken['uri'];
    }
    /**
     * Checks if the URI has a variable or not given its name.
     * @param string $varName The name of the variable.
     * @return boolean If the given variable name is exist, the function will 
     * return <b>TRUE</b>. Other than that, the function will return <b>FALSE</b>.
     * @since 1.0
     */
    public function hasUriVar($varName) {
        return array_key_exists($varName, $this->uriBroken['uri-vars']);
    }
    /**
     * Sets the value of a URI variable.
     * @param string $varName The name of the variable.
     * @param string $value The value of the variable.
     * @return boolean The function will return <b>TRUE</b> if the variable 
     * was set. If the variable does not exist, the function will return <b>FALSE</b>.
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
     * @param string $varName The name of the variable.
     * @return string|NULL The function will return the value of the 
     * variable if found. If the variable is not set or the variable 
     * does not exist, the function will return <b>NULL</b>.
     * @since 1.0
     */
    public function getUriVar($varName) {
        if($this->hasUriVar($varName)){
            return $this->uriBroken['uri-vars'][$varName];
        }
        return NULL;
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
    public static function splitURI($uri) {
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
                
            )
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
        $split4 = explode('/', $split3[1]);
        $retVal['authority'] = '//'.$split4[2];
        
        //after that, we create the path from the remaining parts
        //also we check if the path has variables or not
        //a variable is a value in the path which is enclosed between {}
        for($x = 3 ; $x < count($split4) ; $x++){
            $dirName = $split4[$x];
            if($dirName != ''){
                $retVal['path'][] = $dirName;
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
        }
        return $retVal;
    }
    /**
     * Checks if two URIs are equal or not
     * @param RouterUri $otherUri The URI which 'this' URI will be checked against. 
     * @return boolean The function will return <b>TRUE</b> if the URIs are 
     * equal. Two URIs are considered equal if they have the same authority and the 
     * same variables names.
     * @since 1.0
     */
    public function equals($otherUri) {
        if($otherUri instanceof RouterUri){
            $isEqual = TRUE;
            if($this->getAuthority() == $otherUri->getAuthority()){
                $thisKeys = array_keys($this->getUriVars());
                $boolsArr = array();
                foreach ($thisKeys as $key){
                    $boolsArr[] = array_key_exists($key, $otherUri->getUriVars());
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
