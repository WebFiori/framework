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
use webfiori\collections\LinkedList;
use webfiori\framework\middleware\MiddlewareManager;
use webfiori\framework\Util;
use webfiori\http\Uri;
use webfiori\ui\HTMLNode;
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
 * @version 1.5.0
 */
class RouterUri extends Uri {
    /**
     * The action (class method) that will be performed.
     * 
     * @var string|null
     * 
     * @since 1.5.0
     */
    private $action;
    private $assignedMiddlewareList;
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
     * Set to true if the resource that the route points to is dynamic (PHP file or code).
     * 
     * @var boolean
     * 
     * @since 1.3.7 
     */
    private $isDynamic;
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
        parent::__construct($requestedUri);
        $this->setType(Router::CUSTOMIZED);
        $this->setRoute($routeTo);
        $this->setIsCaseSensitive($caseSensitive);
        $this->assignedMiddlewareList = new LinkedList();

        $this->setClosureParams($closureParams);
        $this->incInSiteMap = false;
        $this->languages = [];
        $this->addMiddleware('global');
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
     * Adds the URI to middleware or to middleware group.
     * 
     * @param string $name The name of the middleware or the group.
     * 
     * @since 1.4
     */
    public function addMiddleware($name) {
        $mw = MiddlewareManager::getMiddleware($name);

        if ($mw !== null) {
            $this->assignedMiddlewareList->add($mw);
        } else {
            $group = MiddlewareManager::getGroup($name);

            foreach ($group as $mw) {
                $this->assignedMiddlewareList->add($mw);
            }
        }
    }
    /**
     * Returns the name of the action that will be called in the controller.
     * 
     * @return string|null The name of the controller method.
     * 
     * @since 1.5.0
     */
    public function getAction() {
        return $this->action;
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
     * Returns a list that holds objects for the middleware.
     * 
     * @return LinkedList
     * 
     * @since 1.4.0
     */
    public function getMiddlewar() {
        return $this->assignedMiddlewareList;
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
        return isset($this->getComponents()['requested-uri']) ? $this->getComponents()['requested-uri'] : null;
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
        Util::print_r($this->getComponents(),false);
    }
    /**
     * Sets the name of the action that will be called in the controller.
     * 
     * @param string $action The name of the controller method.
     * 
     * @since 1.5.0
     */
    public function setAction($action) {
        $trimmed = trim($action);

        if (strlen($trimmed) != 0) {
            $this->action = $trimmed;
        }
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
}
