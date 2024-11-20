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

/**
 * A class which is used to hold route options as constants.
 *
 * @author Ibrahim
 */
class RouteOption {
    /**
     * An option which is used to set the name of controller action that will be invoked (MVC).
     */
    const ACTION = 'action';
    /**
     * An option which is used to treat the route as an API call.
     */
    const API = 'as-api';
    /**
     * An option which is used to indicate if path is case sensitive or not.
     */
    const CASE_SENSITIVE = 'case-sensitive';
    /**
     * An option which is used to set the duration at which route result will be cached in seconds.
     */
    const CACHE_DURATION = 'cache-duration';
    /**
     * An option which is used to set an array as closure parameters (applies to routes of type closure only)
     */
    const CLOSURE_PARAMS = 'closure-params';
    /**
     * An option which is used to set the languages at which the route will be available at (used in building sitemap).
     */
    const LANGS = 'languages';
    /**
     * An option which is used to set the middleware that will be applied to the route.
     */
    const MIDDLEWARE = 'middleware';
    /**
     * An option that represents the path part of the URI.
     */
    const PATH = 'path';
    /**
     * An option which is used to set an array of allowed request methods.
     */
    const REQUEST_METHODS = 'methods';
    /**
     * An option which is used to tell if the route should be part of auto-generated sitemap or not.
     */
    const SITEMAP = 'in-sitemap';
    /**
     * An option which is used to set sub-routes.
     */
    const SUB_ROUTES = 'routes';
    /**
     * An option which is used to set the resource at which the route will point to.
     */
    const TO = 'route-to';
    /**
     * An option which is used to set route type.
     */
    const TYPE = 'type';
    /**
     * An option which is used to set an array of allowed values to route parameters.
     */
    const VALUES = 'vars-values';
}
