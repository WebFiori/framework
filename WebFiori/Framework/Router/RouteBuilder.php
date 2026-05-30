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

use WebFiori\Framework\Middleware\AbstractMiddleware;
use WebFiori\Http\RequestMethod;

/**
 * A class responsible for building and registering routes.
 *
 * @author Ibrahim
 */
class RouteBuilder {
    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    /**
     * Adds new route to the router.
     *
     * @param array $options An associative array of route options.
     *
     * @return bool If the route is added, the method will return true.
     */
    public function addRoute(array $options): bool {
        if (isset($options[RouteOption::SUB_ROUTES])) {
            $routesArr = $this->addRoutesGroupHelper($options);
            $added = true;

            foreach ($routesArr as $route) {
                $added = $added && $this->addRoute($route);
            }

            return $added;
        }

        if (!isset($options[RouteOption::TO])) {
            return false;
        } else {
            $options = $this->checkOptionsArr($options);
            $routeType = $options[RouteOption::TYPE];
        }

        if (strlen($this->router->getBase()) != 0 && ($routeType == Router::API_ROUTE ||
            $routeType == Router::VIEW_ROUTE ||
            $routeType == Router::CUSTOMIZED ||
            $routeType == Router::CLOSURE_ROUTE)) {
            return $this->buildAndRegister($options);
        }

        return false;
    }

    /**
     * Adds middleware group name to options array.
     */
    public static function addToMiddlewareGroup(array &$options, string $groupName): void {
        if (isset($options[RouteOption::MIDDLEWARE])) {
            if (gettype($options[RouteOption::MIDDLEWARE]) == 'array') {
                $options[RouteOption::MIDDLEWARE][] = $groupName;
            } else {
                $options[RouteOption::MIDDLEWARE] = [$options[RouteOption::MIDDLEWARE], $groupName];
            }
        } else {
            $options[RouteOption::MIDDLEWARE] = $groupName;
        }
    }

    /**
     * Removes any extra forward slash in the beginning or the end.
     *
     * @param string $path Any string that represents the path part of a URI.
     *
     * @return string A string in the format '/nice/work/boy'.
     */
    public function fixUriPath(string $path): string {
        if (strlen($path) != 0 && $path != '/') {
            if ($path[strlen($path) - 1] == '/' || $path[0] == '/') {
                while (strlen($path) > 0 && ($path[0] == '/' || $path[strlen($path) - 1] == '/')) {
                    $path = trim($path, '/');
                }
                $path = '/'.$path;
            }

            if ($path[0] != '/') {
                $path = '/'.$path;
            }
        } else {
            $path = '/';
        }

        return $path;
    }

    private function addRoutesGroupHelper(array $options, array &$routesToAddArr = []): array {
        $subRoutes = isset($options[RouteOption::SUB_ROUTES]) && gettype($options[RouteOption::SUB_ROUTES]) == 'array' ? $options[RouteOption::SUB_ROUTES] : [];

        foreach ($subRoutes as $subRoute) {
            if (isset($subRoute[RouteOption::PATH])) {
                $this->copyOptionsToSub($options, $subRoute);
                $subRoute[RouteOption::PATH] = $options[RouteOption::PATH].'/'.$subRoute[RouteOption::PATH];

                if (isset($subRoute[RouteOption::SUB_ROUTES]) && gettype($subRoute[RouteOption::SUB_ROUTES]) == 'array') {
                    $this->addRoutesGroupHelper($subRoute, $routesToAddArr);
                } else {
                    $routesToAddArr[] = $subRoute;
                }
            }
        }

        if (isset($options[RouteOption::TO])) {
            $sub = [
                RouteOption::PATH => $options[RouteOption::PATH],
                RouteOption::TO => $options[RouteOption::TO]
            ];
            $this->copyOptionsToSub($options, $sub);
            $routesToAddArr[] = $sub;
        }

        return $routesToAddArr;
    }

    private function buildAndRegister(array $options): bool {
        $routeTo = $options[RouteOption::TO];
        $caseSensitive = $options[RouteOption::CASE_SENSITIVE];
        $routeType = $options[RouteOption::TYPE];
        $incInSiteMap = $options[RouteOption::SITEMAP];
        $asApi = $options[RouteOption::API];
        $closureParams = $options[RouteOption::CLOSURE_PARAMS];
        $path = $options[RouteOption::PATH];
        $cache = $options[RouteOption::CACHE_DURATION];

        if ($routeType == Router::CLOSURE_ROUTE && !is_callable($routeTo)) {
            return false;
        }
        $routeUri = new RouterUri($this->router->getBase().$path, $routeTo, $caseSensitive, $closureParams);
        $routeUri->setAction($options[RouteOption::ACTION]);
        $routeUri->setCacheDuration($cache);

        if (!$this->router->getMatcher()->hasRoute($routeUri)) {
            if ($asApi === true) {
                $routeUri->setType(Router::API_ROUTE);
            } else {
                $routeUri->setType($routeType);
            }
            $routeUri->setIsInSiteMap($incInSiteMap);
            $routeUri->setRequestMethods($options[RouteOption::REQUEST_METHODS]);

            foreach ($options[RouteOption::LANGS] as $langCode) {
                $routeUri->addLanguage($langCode);
            }

            foreach ($options[RouteOption::VALUES] as $varName => $varValues) {
                $routeUri->addAllowedParameterValues($varName, $varValues);
            }
            $path = $routeUri->isCaseSensitive() ? $routeUri->getPath() : strtolower($routeUri->getPath());

            foreach ($options[RouteOption::MIDDLEWARE] as $mwName) {
                $routeUri->addMiddleware($mwName);
            }

            $routes = &$this->router->getRoutesRef();

            if ($routeUri->hasParameters()) {
                $routes['variable'][$path] = $routeUri;
            } else {
                $routes['static'][$path] = $routeUri;
            }

            return true;
        }

        return false;
    }

    /**
     * Checks for provided options and set defaults for the ones which are
     * not provided.
     */
    private function checkOptionsArr(array $options): array {
        $routeTo = $options[RouteOption::TO];

        if (isset($options[RouteOption::CASE_SENSITIVE])) {
            $caseSensitive = $options[RouteOption::CASE_SENSITIVE] === true;
        } else {
            $caseSensitive = true;
        }

        if (isset($options[RouteOption::CACHE_DURATION])) {
            $cacheDuration = $options[RouteOption::CACHE_DURATION];
        } else {
            $cacheDuration = 0;
        }

        $routeType = $options[RouteOption::TYPE] ?? Router::CUSTOMIZED;
        $incInSiteMap = $options[RouteOption::SITEMAP] ?? false;

        if (isset($options[RouteOption::MIDDLEWARE])) {
            $raw = $options[RouteOption::MIDDLEWARE];

            if (is_array($raw)) {
                $mdArr = $raw;
            } else if (is_string($raw) || $raw instanceof AbstractMiddleware) {
                $mdArr = [$raw];
            } else {
                $mdArr = [];
            }
        } else {
            $mdArr = [];
        }

        if (isset($options[RouteOption::API])) {
            $asApi = $options[RouteOption::API] === true;
        } else {
            $asApi = false;
        }
        $closureParams = isset($options[RouteOption::CLOSURE_PARAMS]) && gettype($options[RouteOption::CLOSURE_PARAMS]) == 'array' ?
                $options[RouteOption::CLOSURE_PARAMS] : [];
        $path = isset($options[RouteOption::PATH]) ? $this->fixUriPath($options[RouteOption::PATH]) : '';
        $languages = isset($options[RouteOption::LANGS]) && gettype($options[RouteOption::LANGS]) == 'array' ? $options[RouteOption::LANGS] : [];
        $varValues = isset($options[RouteOption::VALUES]) && gettype($options[RouteOption::VALUES]) == 'array' ? $options[RouteOption::VALUES] : [];

        $action = '';

        if (isset($options[RouteOption::ACTION])) {
            $trimmed = trim($options[RouteOption::ACTION]);

            if (strlen($trimmed) > 0) {
                $action = $trimmed;
            }
        }

        return [
            RouteOption::CASE_SENSITIVE => $caseSensitive,
            RouteOption::TYPE => $routeType,
            RouteOption::SITEMAP => $incInSiteMap,
            RouteOption::API => $asApi,
            RouteOption::PATH => $path,
            RouteOption::TO => $routeTo,
            RouteOption::CLOSURE_PARAMS => $closureParams,
            RouteOption::LANGS => $languages,
            RouteOption::VALUES => $varValues,
            RouteOption::MIDDLEWARE => $mdArr,
            RouteOption::REQUEST_METHODS => $this->getRequestMethodsHelper($options),
            RouteOption::ACTION => $action,
            RouteOption::CACHE_DURATION => $cacheDuration
        ];
    }

    private function copyOptionsToSub(array $options, array &$subRoute): void {
        if (!isset($subRoute[RouteOption::CASE_SENSITIVE])) {
            if (isset($options[RouteOption::CASE_SENSITIVE])) {
                $caseSensitive = $options[RouteOption::CASE_SENSITIVE] === true;
            } else {
                $caseSensitive = true;
            }
            $subRoute[RouteOption::CASE_SENSITIVE] = $caseSensitive;
        }

        $subRoute[RouteOption::TYPE] = $options[RouteOption::TYPE] ?? Router::CUSTOMIZED;

        if (!isset($subRoute[RouteOption::SITEMAP])) {
            $incInSiteMap = $options[RouteOption::SITEMAP] ?? false;
            $subRoute[RouteOption::SITEMAP] = $incInSiteMap;
        }

        if (isset($options[RouteOption::MIDDLEWARE])) {
            if (gettype($options[RouteOption::MIDDLEWARE]) == 'array') {
                $mdArr = $options[RouteOption::MIDDLEWARE];
            } else {
                if (gettype($options[RouteOption::MIDDLEWARE]) == 'string') {
                    $mdArr = [$options[RouteOption::MIDDLEWARE]];
                } else {
                    $mdArr = [];
                }
            }
        } else {
            $mdArr = [];
        }

        if (!isset($subRoute[RouteOption::MIDDLEWARE])) {
            $subRoute[RouteOption::MIDDLEWARE] = $mdArr;
        } else {
            if (gettype($subRoute[RouteOption::MIDDLEWARE]) == 'array') {
                foreach ($mdArr as $md) {
                    $subRoute[RouteOption::MIDDLEWARE][] = $md;
                }
            } else {
                if (gettype($subRoute[RouteOption::MIDDLEWARE]) == 'string') {
                    $newMd = [$subRoute[RouteOption::MIDDLEWARE]];

                    foreach ($mdArr as $md) {
                        $newMd[] = $md;
                    }
                    $subRoute[RouteOption::MIDDLEWARE] = $newMd;
                }
            }
        }
        $languages = isset($options[RouteOption::LANGS]) && gettype($options[RouteOption::LANGS]) == 'array' ? $options[RouteOption::LANGS] : [];

        if (isset($subRoute[RouteOption::LANGS]) && gettype($subRoute[RouteOption::LANGS]) == 'array') {
            foreach ($languages as $langCode) {
                if (!in_array($langCode, $subRoute[RouteOption::LANGS])) {
                    $subRoute[RouteOption::LANGS][] = $langCode;
                }
            }
        } else {
            $subRoute[RouteOption::LANGS] = $languages;
        }

        $reqMethArr = $this->getRequestMethodsHelper($options);

        if (isset($subRoute[RouteOption::REQUEST_METHODS])) {
            if (gettype($subRoute[RouteOption::REQUEST_METHODS]) != 'array') {
                $reqMethArr[] = $subRoute[RouteOption::REQUEST_METHODS];
                $subRoute[RouteOption::REQUEST_METHODS] = $reqMethArr;
            } else {
                foreach ($reqMethArr as $meth) {
                    $subRoute[RouteOption::REQUEST_METHODS][] = $meth;
                }
            }
        } else {
            $subRoute[RouteOption::REQUEST_METHODS] = $reqMethArr;
        }
    }

    private function getRequestMethodsHelper(array $options): array {
        $requestMethodsArr = [];

        if (isset($options[RouteOption::REQUEST_METHODS])) {
            $methTypes = gettype($options[RouteOption::REQUEST_METHODS]);

            if ($methTypes == 'array') {
                foreach ($options[RouteOption::REQUEST_METHODS] as $reqMethod) {
                    $upper = strtoupper(trim($reqMethod));

                    if (in_array($upper, RequestMethod::getAll())) {
                        $requestMethodsArr[] = $upper;
                    }
                }
            } else {
                if ($methTypes == 'string') {
                    $upper = strtoupper(trim($options[RouteOption::REQUEST_METHODS]));

                    if (in_array($upper, RequestMethod::getAll())) {
                        $requestMethodsArr[] = $upper;
                    }
                }
            }
        }

        return $requestMethodsArr;
    }
}
