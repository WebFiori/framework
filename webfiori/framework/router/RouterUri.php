<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework\router;

use Closure;
use InvalidArgumentException;
use webfiori\framework\middleware\MiddlewareManager;
use WebFiori\Http\Uri;
use WebFiori\UI\HTMLNode;
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
    private $sortedMiddleeareList;
    private $cacheDuration;
    /**
     *
     * @var array
     * @since 1.2
     */
    private $closureParams = [];
    /**
     * A boolean value that is set to true if the URI will be included in
     * generated site map.
     *
     * @var bool
     *
     * @since 1.3
     */
    private $incInSiteMap;
    /**
     * A boolean which is set to true if URI is case-sensitive.
     *
     * @var bool
     *
     * @since 1.0
     */
    private $isCS;

    /**
     * Set to true if the resource that the route points to is dynamic (PHP file or code).
     *
     * @var bool
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
     *
     * @var Uri|null
     */
    private $requestedUri;
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
     * @param string|callable $routeTo The file that the route will take the user
     * to. This can be an absolute path to a file, a closure or class
     * name.
     *
     * @param bool $caseSensitive A boolean. If the URI is case-sensitive,
     * then this value must be set to true. False if not. Default is true.
     *
     * @param array $closureParams If the closure needs to use parameters,
     * it is possible to supply them using this array.
     *
     * @throws InvalidArgumentException The method will throw this exception if the
     * given URI is invalid.
     */
    public function __construct(string $requestedUri, $routeTo, bool $caseSensitive = true, array $closureParams = []) {
        parent::__construct($requestedUri);
        $this->isCS = $caseSensitive;
        $this->setType(Router::CUSTOMIZED);
        $this->setRoute($routeTo);
        $this->assignedMiddlewareList = [];
        $this->sortedMiddleeareList = [];

        $this->setClosureParams($closureParams);
        $this->incInSiteMap = false;
        $this->languages = [];
        $this->addMiddleware('global');
        $this->setCacheDuration(0);
    }
    /**
     * Returns the duration of URI cache.
     * 
     * @return int The duration of URI cache. Default value is zero which indicates
     * that no caching will happen.
     */
    public function getCacheDuration() : int {
        return $this->cacheDuration;
    }
    /**
     * Sets the duration of URI cache.
     * 
     * @param int $val A positive value that represent cache duration in seconds.
     * If 0 is given, it indicates that no caching will happen.
     */
    public function setCacheDuration(int $val) {
        if ($val >= 0) {
            $this->cacheDuration = $val;
        }
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
    public function addLanguage(string $langCode) {
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
    public function addMiddleware(string $name) {
        $mw = MiddlewareManager::getMiddleware($name);

        if ($mw === null) {
            $group = MiddlewareManager::getGroup($name);

            foreach ($group as $mw) {
                $this->assignedMiddlewareList[] = $mw;
            }

            return;
        }
        $this->assignedMiddlewareList[] = $mw;
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
    public function getClassName() : string {
        if ($this->getType() != Router::CLOSURE_ROUTE) {
            $path = $this->getRouteTo();
            $pathExplode = explode(DS, $path);

            if (count($pathExplode) >= 1) {
                $fileNameExplode = explode('.', $pathExplode[count($pathExplode) - 1]);

                if (count($fileNameExplode) == 2 && $fileNameExplode[1] == 'php') {
                    return $fileNameExplode[0];
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
    public function getClosureParams() : array {
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
    public function getLanguages() : array {
        return $this->languages;
    }
    /**
     * Returns a list that holds objects for the middleware.
     *
     * @return array
     *
     */
    public function getMiddleware() : array {
        if (count($this->assignedMiddlewareList) != count($this->sortedMiddleeareList)) {
            $compareFunc = function ($a, $b) {
                return $a->compare($b);
            };
            $this->sortedMiddleeareList = $this->assignedMiddlewareList;
            
            usort($this->sortedMiddleeareList, $compareFunc);
        }
        return $this->sortedMiddleeareList;
    }

    /**
     * Returns an array that contains requested URI information.
     *
     * @return Uri|null If the requested URI is set, the method will return
     * its information contained in an object. Other than that, null is returned.
     *
     * @since 1.3.4
     */
    public function getRequestedUri() {
        return $this->requestedUri;
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
    public function getSitemapNodes() : array {
        $retVal = [];

        if (!$this->hasParameters()) {
            $retVal[] = $this->buildSitemapNode($this->getUri());

            return $retVal;
        }

        $this->_($this->getUri(), $this->getParametersNames(), 0, $retVal);

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
    public function getType() : string {
        return $this->type;
    }

    /**
     * Checks if the URI has WWW in the host part or not.
     *
     * @return bool If the URI has WWW in the host, the method will return
     * true. Other than that, it will return false.
     *
     * @since 1.3.4
     */
    public function hasWWW() : bool {
        $host = $this->getHost();
        $www = substr($host, 0, 3);

        return $www == 'www';
    }
    /**
     * Returns the value of the property that tells if the URI is case-sensitive
     * or not.
     *
     * @return bool  True if URI case-sensitive. False if not. Default is false.
     *
     * @since 1.0
     */
    public function isCaseSensitive() : bool {
        return $this->isCS;
    }
    /**
     * Checks if the resource that the URI is pointing to is dynamic.
     *
     * A resource is considered as dynamic if it is a PHP code or a PHP file.
     *
     * @return bool If the resource is dynamic, the method will return true.
     * other than that, the method will return false.
     *
     * @since 1.3.7
     */
    public function isDynamic() : bool {
        return $this->isDynamic;
    }
    /**
     * Checks if the URI will be included in auto-generated site map or not.
     *
     * @return bool If the URI will be included, the method will return
     * true. Default is false.
     *
     * @since 1.3
     */
    public function isInSiteMap() : bool {
        return $this->incInSiteMap;
    }
    /**
     * Sets the name of the action that will be called in the controller.
     *
     * @param string $action The name of the controller method.
     *
     * @since 1.5.0
     */
    public function setAction(string $action) {
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
    public function setClosureParams(array $arr) {
        $this->closureParams = $arr;
    }
    /**
     * Make the URI case-sensitive or not.
     *
     * This is mainly used in case the developer would like to use the
     * URI in routing.
     *
     * @param bool $caseSensitive True to make it case-sensitive. False to
     * not.
     *
     * @since 1.0
     */
    public function setIsCaseSensitive(bool $caseSensitive) {
        $this->isCS = $caseSensitive === true;
    }
    /**
     * Sets the value of the property '$incInSiteMap'.
     *
     * @param bool $bool If true is given, the URI will be included
     * in site map.
     *
     * @since 1.3
     */
    public function setIsInSiteMap(bool $bool) {
        $this->incInSiteMap = $bool === true;
    }
    /**
     * Sets the requested URI.
     *
     * @param string $uri A string that represents requested URI.
     *
     * @return bool If the requested URI is a match with the original URI which
     * is stored in the object, it will be set and the method will return true.
     * Other than that, the method will return false.
     *
     * @since 1.0
     */
    public function setRequestedUri(string $uri): bool {
        $requested = new Uri($uri);

        if ($this->compareUriPathHelper($requested)) {
            $this->requestedUri = $requested;

            return true;
        }

        return false;
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
        $xRouteTo = null;
        $type = gettype($routeTo);

        if (!is_callable($routeTo) && $type != 'object' && !class_exists($routeTo)) {
            $cleaned = str_replace('\\', DS, $routeTo);
            $xRouteTo = str_replace('/', DS, $cleaned);
            $explode = explode('.', $routeTo);
            $extension = $explode[count($explode) - 1];

            if ($extension != 'php') {
                $this->isDynamic = false;
            }
        } else if (is_callable($routeTo)) {
            $this->setType(Router::CLOSURE_ROUTE);
            $xRouteTo = $routeTo;
        } else if ($type == 'object') {
            $xRouteTo = get_class($routeTo);
        } else if (class_exists($routeTo)) {
            $xRouteTo = $routeTo;
        }

        $this->routeTo = $xRouteTo;
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
    public function setType(string $type) {
        if ($type == Router::API_ROUTE || $type == Router::CLOSURE_ROUTE ||
                $type == Router::CUSTOMIZED || $type == Router::VIEW_ROUTE) {
            $this->type = $type;
        }
    }

    private function _($originalUriWithVars, $uriVars, $varIndex, &$nodesArr) {
        $varName = $uriVars[$varIndex];
        $varValues = $this->getParameterValues($varName);

        foreach ($varValues as $varValue) {
            $uriWithVarsReplaced = str_replace('{'.$varName.'}', $varValue, $originalUriWithVars);

            if ($varIndex + 1 != count($uriVars)) {
                $this->_($uriWithVarsReplaced, $uriVars, $varIndex + 1, $nodesArr);
                continue;
            }
            $nodesArr[] = $this->buildSitemapNode($uriWithVarsReplaced);
        }
    }
    private function buildSitemapNode($uri): HTMLNode {
        $node = new HTMLNode('url');
        $node->addChild('loc')->text($uri, false);

        foreach ($this->getLanguages() as $langCode) {
            $node->text('<xhtml:link rel="alternate" hreflang="'.$langCode.'" href="'.$uri.'?lang='.$langCode.'"/>', false);
        }

        return $node;
    }
    private function comparePathHelper($originalPath, $requestedPath): bool {
        $count = count($originalPath);
        $requestedCount = count($requestedPath);

        for ($x = 0 ; $x < $count ; $x++) {
            if ($x == $requestedCount) {
                break;
            }
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

    /**
     * Validate the path part of original URI and the requested one.
     *
     * @param Uri $requestedUri
     * @return bool
     *
     * @since 1.0
     */
    private function compareUriPathHelper(Uri $requestedUri): bool {
        $requestedArr = $requestedUri->getComponents();

        $originalPath = $this->getPathArray();
        $requestedPath = $requestedArr['path'];

        return $this->comparePathHelper($originalPath, $requestedPath);
    }
}
