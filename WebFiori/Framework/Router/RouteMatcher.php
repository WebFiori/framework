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
namespace WebFiori\Framework\Router;

use WebFiori\Cli\Runner;
use WebFiori\Framework\App;
use WebFiori\Framework\Ui\StarterPage;

/**
 * A class responsible for matching incoming URIs against registered routes.
 *
 * @author Ibrahim
 */
class RouteMatcher {
    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    /**
     * Returns an object of type 'RouterUri' that represents route URI.
     *
     * @param string $path The path part of the URI.
     *
     * @return RouterUri|null If a route was found, an object of type 'RouterUri'
     * is returned. If no route is found, null is returned.
     */
    public function getUriObj(string $path): ?RouterUri {
        $routes = $this->router->getRoutesRef();

        if (isset($routes['static'][$path])) {
            return $routes['static'][$path];
        } else if (isset($routes['variable'][$path])) {
            return $routes['variable'][$path];
        }

        return null;
    }

    /**
     * Checks if a given RouterUri object has a registered route.
     *
     * @param RouterUri $uriObj The URI object to check.
     *
     * @return bool True if the route exists.
     */
    public function hasRoute(RouterUri $uriObj): bool {
        $path = $uriObj->getPath();

        if (!$uriObj->isCaseSensitive()) {
            $path = strtolower($path);
        }

        $routes = $this->router->getRoutesRef();

        if ($uriObj->hasParameters()) {
            return isset($routes['variable'][$path]);
        } else {
            return isset($routes['static'][$path]);
        }
    }

    /**
     * Route a given URI to its specified resource.
     *
     * @param string $uri A URI such as 'http://www.example.com/hello/ibrahim'
     * @param bool $loadResource If set to true, the resource will be loaded.
     */
    public function resolveUrl(string $uri, bool $loadResource = true): void {
        $this->router->setRouteUri(null);

        if (Router::routesCount() != 0) {
            $routeUri = new RouterUri($uri, '');

            if ($routeUri->hasWWW() && defined('NO_WWW') && NO_WWW === true) {
                $this->router->getDispatcher()->redirectToNonWWW($routeUri);
            }

            //first, search for the URI without checking parameters
            if ($this->searchRoute($routeUri, $uri, $loadResource)) {
                return;
            }

            //if no route found, try to replace parameters with values
            if ($this->searchRoute($routeUri, $uri, $loadResource, true)) {
                return;
            }

            //if we reach this part, this means the route was not found
            if ($loadResource) {
                call_user_func($this->router->getOnNotFound());
            }
        } else {
            if ($loadResource === true) {
                $page = new StarterPage();
                $page->render();
            }
        }
    }

    /**
     * Checks if a directory name is a parameter or not.
     */
    private function isDirectoryAVar(string $dir): bool {
        return $dir[0] == '{' && $dir[strlen($dir) - 1] == '}';
    }

    /**
     * Searches for a matching route.
     *
     * @param RouterUri $routeUri The parsed request URI.
     * @param string $uri The raw URI string.
     * @param bool $loadResource Whether to load the resource.
     * @param bool $withVars Whether to check variable routes.
     *
     * @return bool True if a route was found.
     */
    private function searchRoute(RouterUri $routeUri, string $uri, bool $loadResource, bool $withVars = false): bool {
        $pathArray = $routeUri->getPathArray();
        $requestMethod = App::getRequest()->getMethod();
        $indexToSearch = 'static';

        if ($withVars) {
            $indexToSearch = 'variable';
        }

        $routes = $this->router->getRoutesRef();

        if ($indexToSearch == 'static') {
            $route = isset($routes[$indexToSearch][$routeUri->getPath()]) ?
                    $routes[$indexToSearch][$routeUri->getPath()] : null;

            if ($route instanceof RouterUri) {
                if (!$route->isCaseSensitive()) {
                    $isEqual = strtolower($route->getUri()) ==
                    strtolower($routeUri->getUri());
                } else {
                    $isEqual = $route->getUri() == $routeUri->getUri();
                }

                if ($isEqual) {
                    $route->setRequestedUri($uri);
                    $this->router->getDispatcher()->dispatch($route, $loadResource);

                    return true;
                }
            }
        } else {
            foreach ($routes['variable'] as $route) {
                $this->setUriVarsHelper($route, $pathArray, $requestMethod);

                if ($route->isAllParametersSet() && $route->setRequestedUri($uri)) {
                    $this->router->getDispatcher()->dispatch($route, $loadResource);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Sets URI parameter values from the requested path.
     */
    private function setUriVarsHelper(RouterUri $uriRouteObj, array $requestedPathArr, string $requestMethod): void {
        $routePathArray = $uriRouteObj->getPathArray();
        $pathVarsCount = count($routePathArray);
        $requestedPathPartsCount = count($requestedPathArr);

        for ($x = 0 ; $x < $pathVarsCount ; $x++) {
            if ($x == $requestedPathPartsCount) {
                break;
            }

            if ($this->isDirectoryAVar($routePathArray[$x])) {
                $varName = trim($routePathArray[$x], '{}');

                if ($varName[strlen($varName) - 1] == '?') {
                    $varName = trim($varName, '?');
                }
                $uriRouteObj->setParameterValue($varName, $requestedPathArr[$x]);

                if ($requestMethod == 'POST' || $requestMethod == 'PUT') {
                    $_POST[$varName] = filter_var(urldecode($requestedPathArr[$x]));
                } else if ($requestMethod == 'GET' || $requestMethod == 'DELETE' || Runner::isCLI()) {
                    $_GET[$varName] = filter_var(urldecode($requestedPathArr[$x]));
                }
            } else if ((!$uriRouteObj->isCaseSensitive() && (strtolower($routePathArray[$x]) != strtolower($requestedPathArr[$x]))) || $routePathArray[$x] != $requestedPathArr[$x]) {
                break;
            }
        }
    }
}
