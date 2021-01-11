<?php
namespace webfiori\ini;

use webfiori\examples\SampleMiddleware;
use webfiori\framework\middleware\MiddlewareManager;
/**
 * Register middleware which are created outside the folder 'app/middleware'.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 * 
 * @since 2.0.0
 */
class InitMiddleware {
    /**
     * Register middleware.
     * 
     * The main aim of this method is to give the developer a way to register 
     * the middleware which are created outside the folder 'app/pages'. To register 
     * any middleware, use the method MiddlewareManager::register().
     * 
     * @since 1.0
     */
    public static function init() {
        MiddlewareManager::register(new SampleMiddleware());
    }
}
