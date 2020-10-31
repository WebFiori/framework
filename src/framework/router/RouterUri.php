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

use Closure;
use InvalidArgumentException;
use webfiori\ui\HTMLNode;
use webfiori\framework\Util;
/**
 * A class that is used to split URIs and get their parameters.
 * 
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
 * 
 * @author Ibrahim
 * 
 * @version 1.3.7
 */
class RouterUri {
    /**
     * 
     * @var type 
     * @since 1.2
     */
    private $closureParams = [];
    /**
     * A boolean value that is set to true if the URI will be included in 
     * generated site map.
     * 
     * @var boolean 
     * 
     * @since 1.3
     */
    private $incInSiteMap;
    /**
     * A boolean which is set to true if URI is case sensitive.
     * 
     * @var boolean 
     * 
     * @since 1.3.1
     */
    private $isCS;
    /**
     * An array that contains all languages that the resource the URI is pointing 
     * to can have.
     * 
     * @var array
     * 
     * @since 1.3.5 
     */
    private $languages;
    /**
     * The route which this URI will be routing to.
     * 
     * @var mixed This route can be a file or a method.
     * 
     * @since 1.0 
     */
    private $routeTo;
    /**
     * The type of the route.
     * 
     * @var string
     * 
     * @since 1.1 
     */
    private $type;
    /**
     * The URI broken into its sub-components (scheme, authority ...) as an associative 
     * array.
     * 
     * @var array 
     * 
     * @since 1.0
     */
    private $uriBroken;
    /**
     * @since 1.3.3
     */
    private static $UV = 'uri-vars';
    /**
     * Set to true if the resource that the route points to is dynamic (PHP file or code).
     * 
     * @var boolean
     * 
     * @since 1.3.7 
     */
    private $isDynamic;
    /**
     * Creates new instance.
     * 
     * @param string $requestedUri The URI such as 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz'
     * 
     * @param string|Closure $routeTo The file that the route will take the user to ar a closure.
     * 
     * @param boolean $caseSensitive A boolean. If the URI is case sensitive, 
     * then this value must be set to true. False if not. Default is true.
     * 
     * @param array $closureParams If the closure needs to use parameters, 
     * it is possible to supply them using this array.
     * 
     * @throws InvalidArgumentException The method will throw this exception if the 
     * given URI is invalid.
     */
    public function __construct($requestedUri,$routeTo,$caseSensitive = true,$closureParams = []) {
        $this->setType(Router::CUSTOMIZED);
        $this->setRoute($routeTo);
        $this->isCS = $caseSensitive === true;
        $broken = self::splitURI($requestedUri);

        if (gettype($broken) != 'array') {
            throw new InvalidArgumentException('Invalid URI given.');
        }
        $this->uriBroken = $broken;
        $this->uriBroken['vars-possible-values'] = [];

        foreach (array_keys($this->getUriVars()) as $varName) {
            $this->uriBroken['vars-possible-values'][$varName] = [];
        }
        $this->setClosureParams($closureParams);
        $this->incInSiteMap = false;
        $this->languages = [];
    }
    /**
     * Adds a language to the set of languages at which the resource that the URI 
     * points to.
     * 
     * @param string $langCode A two characters string such as 'AR' that represents 
     * language code.
     * 
     * @since 1.3.5
     */
    public function addLanguage($langCode) {
        $lower = strtolower(trim($langCode));

        if (strlen($lower) == 2 && $lower[0] >= 'a' && $lower[0] <= 'z' && $lower[1] >= 'a' && $lower[1] <= 'z' && !in_array($lower, $this->languages)) {
            $this->languages[] = $lower;
        }
    }
    /**
     * Adds a possible value for a URI variable.
     * 
     * This is used in constructing the sitemap node of the URI. If a value is 
     * provided, then it will be part of the URI that will appear in the sitemap.
     * 
     * @param string $varName The name of the variable. It must be exist as 
     * the path part in the URI.
     * 
     * @param string $varValue The value of the variable. Note that any extra spaces 
     * in the value will be trimmed.
     * 
     * @since 1.3.6
     */
    public function addVarValue($varName, $varValue) {
        $trimmed = trim($varName);
        $trimmedVal = trim($varValue);

        if (strlen($trimmedVal) != 0 
                && isset($this->uriBroken['vars-possible-values'][$trimmed]) 
                && !in_array($trimmedVal, $this->uriBroken['vars-possible-values'][$trimmed])) {
            $this->uriBroken['vars-possible-values'][$trimmed][] = $trimmedVal;
        }
    }
    /**
     * Adds multiple values to URI variable.
     * 
     * @param string $varName The name of he variable.
     * 
     * @param array $arrayOfVals An array that contains all possible values for 
     * the variable.
     * 
     * @since 1.3.6
     */
    public function addVarValues($varName, $arrayOfVals) {
        if (gettype($arrayOfVals) == 'array') {
            foreach ($arrayOfVals as $val) {
                $this->addVarValue($varName, $val);
            }
        }
    }
    /**
     * Checks if two URIs are equal or not.
     * 
     * Two URIs are considered equal if they have the same authority and the 
     * same path name (same characters case). 
     * 
     * @param RouterUri $otherUri The URI which 'this' URI will be checked against. 
     * 
     * @return boolean The method will return true if the URIs are 
     * equal.
     * 
     * @since 1.0
     */
    public function equals($otherUri) {
        if ($otherUri instanceof RouterUri) {

            if ($this->getAuthority() == $otherUri->getAuthority()) {
                $thisPathNames = $this->getPath();
                $otherPathNames = $otherUri->getPath();
                
                return $thisPathNames == $otherPathNames;
            }
        }

        return false;
    }
    /**
     * Returns authority part of the URI.
     * 
     * @return string The authority part of the URI. Usually, 
     * it is a string in the form '//www.example.com:80'.
     * 
     * @since 1.0
     */
    public function getAuthority() {
        return $this->uriBroken['authority'];
    }
    /**
     * Returns class name based on the file which the route will point to.
     * 
     * The method will try to extract class name from the file which the 
     * route is pointing to.
     * This only applies to routes which points to PHP classes only.
     * 
     * @return string Class name taken from file name. If route type is not 
     * API o not view, the method will return empty string.
     * 
     * @since 1.3.2
     */
    public function getClassName() {
        if ($this->getType() != Router::CLOSURE_ROUTE) {
            $path = $this->getRouteTo();
            $pathExpl = explode(DS, $path);

            if (count($pathExpl) >= 1) {
                $expld = explode('.', $pathExpl[count($pathExpl) - 1]);

                if (count($expld) == 2 && $expld[1] == 'php') {
                    return $expld[0];
                }
            }
        }

        return '';
    }
    /**
     * Returns an array that contains the variables which will be passed to 
     * the closure.
     * 
     * @return array
     * 
     * @since 1.2
     */
    public function getClosureParams() {
        return $this->closureParams;
    }
    /**
     * Returns an associative array which contains all URI parts.
     * 
     * @return array The method will return an associative array that 
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
     * <li><b>full-path</b>: A string that represent the whole path of the URI.</li>
     * <li><b>path</b>: An array that contains the names of path directories</li>
     * <li><b>query-string-vars</b>: An array that contains query string parameter and values.</li>
     * <li><b>uri-vars</b>: An array that contains URI path variable and values.</li>
     * </ul>
     * 
     * @since 1.0
     */
    public function getComponents() {
        return $this->uriBroken;
    }
    /**
     * Returns fragment part of the URI.
     * 
     * @return string Fragment part of the URI. The fragment in the URI is 
     * any string that comes after the character '#'.
     * 
     * @since 1.0
     */
    public function getFragment() {
        return $this->uriBroken['fragment'];
    }
    /**
     * Returns host name from the host part of the URI.
     * 
     * @return string The host name such as 'www.programmingacademia.com'.
     * 
     * @since 1.0
     */
    public function getHost() {
        return $this->uriBroken['host'];
    }
    /**
     * Returns an array that contains a set of languages at which the resource that the URI 
     * points to can have.
     * 
     * @return array An array that contains language codes.
     * 
     * @since 1.3.5
     */
    public function getLanguages() {
        return $this->languages;
    }
    /**
     * Returns the path part of the URI.
     * 
     * @return string A string such as '/path1/path2/path3'.
     * 
     * @since 1.0
     */
    public function getPath() {
        return $this->uriBroken['full-path'];
    }
    /**
     * Returns an array which contains the names of URI directories.
     * 
     * @return array An array which contains the names of URI directories. 
     * For example, if the path part of the URI is '/path1/path2', the 
     * array will contain the value 'path1' at index 0 and 'path2' at index 1.
     * 
     * @since 1.0
     */
    public function getPathArray() {
        return $this->uriBroken['path'];
    }
    /**
     * Returns port number of the authority part of the URI.
     * 
     * @return string Port number of the authority part of the URI. If 
     * port number was not specified, the method will return empty string.
     * 
     * @since 1.0
     */
    public function getPort() {
        return $this->uriBroken['port'];
    }
    /**
     * Returns the query string that was appended to the URI.
     * 
     * @return string The query string that was appended to the URI. 
     * If the URI has no query string, the method will return empty 
     * string.
     * 
     * @since 1.0
     */
    public function getQueryString() {
        return $this->uriBroken['query-string'];
    }
    /**
     * Returns an associative array which contains query string parameters.
     * 
     * @return array An associative array which contains query string parameters. 
     * the keys will be acting as the names of the parameters and the values 
     * of each parameter will be in its key.
     * 
     * @since 1.0
     */
    public function getQueryStringVars() {
        return $this->uriBroken['query-string-vars'];
    }
    /**
     * Returns an array that contains requested URI information.
     * 
     * @return array The method will return an associative array that 
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
     * <li><b>full-path</b>: A string which is similar to '/path/to/resource' that represents path part of 
     * a URL.</li>
     * <li><b>path</b>: An array that contains the names of path directories</li>
     * <li><b>query-string-vars</b>: An array that contains query string parameter and values.</li>
     * <li><b>uri-vars</b>: An array that contains URI path variable and values.</li>
     * </ul>
     * 
     * @since 1.3.4
     */
    public function getRequestedUri() {
        return isset($this->uriBroken['requested-uri']) ? $this->uriBroken['requested-uri'] : null;
    }
    /**
     * Returns the location where the URI will route to.
     * 
     * @return string|callable Usually, the route can be either a callable 
     * or a path to a file. The file can be of any type.
     * 
     * @since 1.0
     */
    public function getRouteTo() {
        return $this->routeTo;
    }
    /**
     * Returns the scheme part of the URI.
     * 
     * @return string The scheme part of the URI. Usually, it is called protocol 
     * (like http, ftp).
     * 
     * @since 1.0
     */
    public function getScheme() {
        return $this->uriBroken['scheme'];
    }
    /**
     * Returns an object of type 'HTMLNode' that contains URI information which 
     * can be used to construct XML sitemap.
     * 
     * @return array The method will return an array that contains objects 
     * of type 'HTMLNode' that contains URI information which 
     * can be used to construct XML sitemap.
     * 
     * @since 1.3.5
     * 
     */
    public function getSitemapNodes() {
        $retVal = [];

        if ($this->hasVars()) {
            $this->_($this->getUri(), array_keys($this->getUriVars()), 0, $retVal);
        } else {
            $retVal[] = $this->_buildSitemapNode($this->getUri());
        }

        return $retVal;
    }
    /**
     * Returns the type of element that the URI will route to.
     * 
     * The type of the element can be 1 of 4 values:
     * <ul>
     * <li>Router::API_ROUTE</li>
     * <li>Router::VIEW_ROUTE</li>
     * <li>Router::CLOSURE_ROUTE</li>
     * <li>Router::CUSTOMIZED</li>
     * </ul>
     * 
     * @return string The type of element that the URI will route to. Default 
     * return value is Router::CUSTOMIZED.
     * 
     * @since 1.1
     */
    public function getType() {
        return $this->type;
    }
    /**
     * Returns the original requested URI.
     * 
     * @param boolean $incQueryStr If set to true, the query string part 
     * will be included in the URL. Default is false.
     * 
     * @param boolean $incFragment If set to true, the fragment part 
     * will be included in the URL. Default is false.
     * 
     * @return string The original requested URI.
     * 
     * @since 1.0
     */
    public function getUri($incQueryStr = false,$incFragment = false) {
        $retVal = $this->getScheme().':'.$this->getAuthority().$this->getPath();

        if ($incQueryStr && $incFragment) {
            $queryStr = $this->getQueryString();

            if (strlen($queryStr) != 0) {
                $retVal .= '?'.$queryStr;
            }
            $fragment = $this->getFragment();

            if (strlen($fragment) != 0) {
                $retVal .= '#'.$fragment;
            }
        } else {
            if ($incQueryStr && !$incFragment) {
                $queryStr = $this->getQueryString();

                if (strlen($queryStr) != 0) {
                    $retVal .= '?'.$queryStr;
                }
            } else if (!$incQueryStr && $incFragment) {
                $fragment = $this->getFragment();

                if (strlen($fragment) != 0) {
                    $retVal .= '#'.$fragment;
                }
            }
        }

        return $retVal;
    }
    /**
     * Returns the value of URI variable given its name.
     * 
     * A variable is a string which is defined while creating the route. 
     * it is name is included between '{}'.
     * 
     * @param string $varName The name of the variable. Note that this value 
     * must not include braces.
     * 
     * @return string|null The method will return the value of the 
     * variable if found. If the variable is not set or the variable 
     * does not exist, the method will return null.
     * 
     * @since 1.0
     */
    public function getUriVar($varName) {
        if ($this->hasUriVar($varName)) {
            return $this->uriBroken[self::$UV][$varName];
        }

        return null;
    }
    /**
     * Returns an associative array which contains URI parameters.
     * 
     * @return array An associative array which contains URI parameters. The 
     * keys will be the names of the variables and the value of each variable will 
     * be in its index.
     * 
     * @since 1.0
     */
    public function getUriVars() {
        return $this->uriBroken[self::$UV];
    }
    /**
     * Returns an array that contains possible values for a URI variable.
     * 
     * @param string $varName The name of the variable.
     * 
     * @return array The method will return an array that contains all possible 
     * values for the variable which was added using the method Router::addUriVarValue(). 
     * If the variable does not exist, the array will be empty.
     * 
     * @since 1.3.6
     */
    public function getVarValues($varName) {
        $trimmed = trim($varName);

        if (isset($this->uriBroken['vars-possible-values'][$trimmed])) {
            return $this->uriBroken['vars-possible-values'][$trimmed];
        }

        return [];
    }
    /**
     * Checks if the URI has a variable or not given its name.
     * 
     * A variable is a string which is defined while creating the route. 
     * it is name is included between '{}'.
     * 
     * @param string $varName The name of the variable.
     * 
     * @return boolean If the given variable name is exist, the method will 
     * return true. Other than that, the method will return false.
     * 
     * @since 1.0
     */
    public function hasUriVar($varName) {
        return array_key_exists($varName, $this->uriBroken[self::$UV]);
    }
    /**
     * Checks if the URI has any variables or not.
     * 
     * A variable is a string which is defined while creating the route. 
     * it is name is included between '{}'.
     * 
     * @return boolean If the URI has any variables, the method will 
     * return true.
     * 
     * @since 1.0
     */
    public function hasVars() {
        return count($this->getUriVars()) != 0;
    }
    /**
     * Checks if the URI has WWW in the host part or not.
     * 
     * @return boolean If the URI has WWW in the host, the method will return 
     * true. Other than that, it will return false.
     * 
     * @since 1.3.4
     */
    public function hasWWW() {
        $host = $this->getHost();
        $www = substr($host, 0, 3);

        return $www == 'www';
    }
    /**
     * Checks if all URI variables has values or not.
     * 
     * @return boolean The method will return true if all URI 
     * variables have a value other than null.
     * 
     * @since 1.0
     */
    public function isAllVarsSet() {
        $canRoute = true;

        foreach ($this->getUriVars() as  $val) {
            $canRoute = $canRoute && $val !== null;
        }

        return $canRoute;
    }
    /**
     * Returns the value of the property that tells if the URI is case sensitive 
     * or not.
     * 
     * @return boolean  True if URI case sensitive. False if not. Default is false.
     * 
     * @since 1.3.1
     */
    public function isCaseSensitive() {
        return $this->isCS;
    }
    /**
     * Checks if the resource that the URI is pointing to is dynamic.
     * 
     * A resource is considered as dynamic if it is a PHP code or a PHP file.
     * 
     * @return boolean If the resource is dynamic, the method will return true. 
     * other than that, the method will return false.
     * 
     * @since 1.3.7
     */
    public function isDynamic() {
        return $this->isDynamic;
    }
    /**
     * Checks if the URI will be included in auto-generated site map or not.
     * 
     * @return boolean If the URI will be included, the method will return 
     * true. Default is false.
     * 
     * @since 1.3
     */
    public function isInSiteMap() {
        return $this->incInSiteMap;
    }
    /**
     * Print the details of the generated URI.
     * 
     * This method will use the method 'Util::print_r()' to print the array 
     * that contains URI details.
     * 
     * @since 1.0
     */
    public function printUri() {
        Util::print_r($this->uriBroken,false);
    }
    /**
     * Sets the array of closure parameters.
     * 
     * @param array $arr An array that contains all the values that will be 
     * passed to the closure.
     * 
     * @since 1.2
     */
    public function setClosureParams($arr) {
        if (gettype($arr) == 'array') {
            $this->closureParams = $arr;
        }
    }
    /**
     * Sets the value of the property '$incInSiteMap'.
     * 
     * @param boolean $bool If true is given, the URI will be included 
     * in site map.
     * 
     * @since 1.3
     */
    public function setIsInSiteMap($bool) {
        $this->incInSiteMap = $bool === true;
    }
    /**
     * Sets the requested URI.
     * 
     * @param string $uri A string that represents requested URI.
     * 
     * @return boolean If the requested URI is a match with the original URI which 
     * is stored in the object, it will be set and the method will return true. 
     * Other than that, the method will return false.
     * 
     * @since 1.3.4 
     */
    public function setRequestedUri($uri) {
        $this->uriBroken['requested-uri'] = self::splitURI($uri);

        if (!$this->_comparePath()) {
            unset($this->uriBroken['requested-uri']);

            return false;
        }

        return true;
    }
    
    /**
     * Sets the route which the URI will take to.
     * 
     * @param string|Closure $routeTo Usually, the route can be either a 
     * file or it can be a callable. The file can be of any type.
     * 
     * @since 1.0
     */
    public function setRoute($routeTo) {
        $this->isDynamic = true;
        
        if ($routeTo instanceof Closure) {
            $this->setType(Router::CLOSURE_ROUTE);
        } else {
            $cleaned = str_replace('\\', DS, $routeTo);
            $routeTo = str_replace('/', DS, $cleaned);
            $expl = explode('.', $routeTo);
            $extension = $expl[count($expl) - 1];
            if ($extension != 'php') {
                $this->isDynamic = false;
            }
        }
        $this->routeTo = $routeTo;
    }
    /**
     * Sets the type of element that the URI will route to.
     * 
     * The type of the element can be 1 of 4 values:
     * <ul>
     * <li>Router::API_ROUTE</li>
     * <li>Router::VIEW_ROUTE</li>
     * <li>Router::CLOSURE_ROUTE</li>
     * <li>Router::CUSTOMIZED</li>
     * </ul>
     * If any thing else is given, it won't update.
     * 
     * @param string $type The type of element that the URI will route to.
     * 
     * @since 1.1
     */
    public function setType($type) {
        if ($type == Router::API_ROUTE || $type == Router::CLOSURE_ROUTE || 
                $type == Router::CUSTOMIZED || $type == Router::VIEW_ROUTE) {
            $this->type = $type;
        }
    }
    /**
     * Sets the value of a URI variable.
     * 
     * A variable is a string which is defined while creating the route. 
     * it is name is included between '{}'.
     * 
     * @param string $varName The name of the variable.
     * 
     * @param string $value The value of the variable.
     * 
     * @return boolean The method will return true if the variable 
     * was set. If the variable does not exist, the method will return false.
     * 
     * @since 1.0
     */
    public function setUriVar($varName,$value) {
        if ($this->hasUriVar($varName)) {
            $this->uriBroken[self::$UV][$varName] = $value;

            return true;
        }

        return false;
    }
    /**
     * Breaks a URI into its basic components.
     * 
     * @param string $uri The URI that will be broken.
     * 
     * @return array|boolean If the given URI is not valid, 
     * the Method will return false. Other than that, The method will return an associative array that 
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
     * <li><b>full-path</b>: A string that represents whole path parts.</li>
     * <li><b>query-string-vars</b>: An array that contains query string parameter and values.</li>
     * <li><b>uri-vars</b>: An array that contains URI path variables and values.</li>
     * </ul>
     * 
     * @since 1.0
     */
    public static function splitURI($uri) {
        $validate = filter_var($uri,FILTER_VALIDATE_URL);

        if ($validate === false) {
            return false;
        }
        $retVal = [
            'uri' => $uri,
            'authority' => '',
            'host' => '',
            'port' => '',
            'scheme' => '',
            'query-string' => '',
            'fragment' => '',
            'full-path' => '',
            'path' => [],
            'query-string-vars' => [

            ],
            self::$UV => [

            ]
        ];
        //First step, extract the fragment
        $split1 = explode('#', $uri);
        $retVal['fragment'] = isset($split1[1]) ? $split1[1] : '';

        //after that, extract the query string
        $split2 = explode('?', $split1[0]);
        $retVal['query-string'] = isset($split2[1]) ? $split2[1] : '';

        //next comes the scheme
        $split3 = explode(':', $split2[0]);
        $retVal['scheme'] = $split3[0];

        if (count($split3) == 3) {
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
        for ($x = 3 ; $x < count($split4) ; $x++) {
            $dirName = $split4[$x];

            if ($dirName != '') {
                $decoded = utf8_decode(urldecode($dirName));
                $retVal['path'][] = $decoded;
                $retVal['full-path'] .= '/'.$decoded;
                if ($dirName[0] == '{' && $dirName[strlen($dirName) - 1] == '}') {
                    $retVal[self::$UV][trim($split4[$x], '{}')] = null;
                }
            }
        }
        //now extract port number from the authority (if any)
        $split5 = explode(':', $retVal['authority']);
        $retVal['port'] = isset($split5[1]) ? $split5[1] : '';
        //Also, host can be extracted at this step.
        $retVal['host'] = trim($split5[0],'//');
        //finaly, split query string and extract vars
        $split6 = explode('&', $retVal['query-string']);

        foreach ($split6 as $param) {
            $split7 = explode('=', $param);
            $retVal['query-string-vars'][$split7[0]] = isset($split7[1]) ? $split7[1] : '';
        }

        return $retVal;
    }
    private function _($originalUriWithVars, $uriVars, $varIndex, &$nodesArr) {
        $varName = $uriVars[$varIndex];
        $varValues = $this->getVarValues($varName);

        foreach ($varValues as $varValue) {
            $uriWithVarsReplaced = str_replace('{'.$varName.'}', $varValue, $originalUriWithVars);

            if ($varIndex + 1 == count($uriVars)) {
                $nodesArr[] = $this->_buildSitemapNode($uriWithVarsReplaced);
            } else {
                $this->_($uriWithVarsReplaced, $uriVars, $varIndex + 1, $nodesArr);
            }
        }
    }
    private function _buildSitemapNode($uri) {
        $node = new HTMLNode('url');
        $node->addChild('loc', [], false)->text($uri);

        foreach ($this->getLanguages() as $langCode) {
            $node->text('<xhtml:link rel="alternate" hreflang="'.$langCode.'" href="'.$uri.'?lang='.$langCode.'"/>', false);
        }

        return $node;
    }
    /**
     * Validate the path part of original URI and the requested one.
     * 
     * @return boolean
     * 
     * @since 1.3.4
     */
    private function _comparePath() {
        $requestedArr = $this->getRequestedUri();

        if ($requestedArr !== null) {
            $originalPath = $this->getPathArray();
            $requestedPath = $requestedArr['path'];

            if (count($originalPath) == count($requestedPath)) {
                return $this->_comparePathHelper($originalPath, $requestedPath);
            }
        }

        return false;
    }
    private function _comparePathHelper($originalPath, $requestedPath) {
        $count = count($originalPath);

        for ($x = 0 ; $x < $count ; $x++) {
            $original = $originalPath[$x];

            if (!($original[0] == '{' && $original[strlen($original) - 1] == '}')) {
                $requested = $requestedPath[$x];

                if (!$this->isCaseSensitive()) {
                    $requested = strtolower($requested);
                    $original = strtolower($original);
                }

                if ($requested != $original) {
                    return false;
                }
            }
        }

        return true;
    }
}
