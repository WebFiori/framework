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
namespace WebFiori\Framework\Middleware;

use Exception;

/**
 * This class is used to manage the operations which are related to middleware.
 *
 * @author Ibrahim
 *
 */
class MiddlewareManager {
    private static $inst;
    /**
     *
     * @var array
     */
    private $middlewareList;
    private function __construct() {
        $this->middlewareList = [];
    }
    /**
     * Returns a set of middleware that belongs to a specific group.
     *
     * @param string $groupName The name of the group.
     *
     * @return array The method will return a linked list with all
     * middleware in the group. If no group which has the given name exist, the
     * list will be empty.
     */
    public static function getGroup(string $groupName) : array {
        $list = [];
        $mdList = self::get()->middlewareList;

        foreach ($mdList as $mw) {
            if (in_array($groupName, $mw->getGroups())) {
                $list[] = $mw;
            }
        }

        return $list;
    }
    /**
     * Returns a registered middleware given its name.
     *
     * @param string $name The name of the middleware.
     *
     * @return AbstractMiddleware|null If a middleware with the given name is
     * found, the method will return it. Other than that, the method will return
     * null.
     */
    public static function getMiddleware(string $name) {
        $mdList = self::get()->middlewareList;

        foreach ($mdList as $mw) {
            if ($mw->getName() == $name) {
                return $mw;
            }
        }
    }
    /**
     * Register a new middleware.
     *
     * @param AbstractMiddleware|string $middleware The middleware that will be registered.
     */
    public static function register($middleware) : bool {
        if (gettype($middleware) == 'string') {
            try {
                $middleware = new $middleware();
            } catch (Exception $exc) {
                return false;
            }
        }
        if ($middleware instanceof AbstractMiddleware) {
            self::get()->middlewareList[] = $middleware;
            return true;
        }
        return false;
    }
    /**
     * Removes a middleware given its name.
     *
     * @param string $name The name of the middleware.
     */
    public static function remove(string $name) {
        $manager = self::get();
        $newList = [];
        
        foreach ($manager->middlewareList as $md) {
            if ($md->getName() != $name) {
                $newList[] = $md;
            }
        }
        $manager->middlewareList = $newList;
    }
    /**
     *
     * @return MiddlewareManager
     */
    private static function get() {
        if (self::$inst === null) {
            self::$inst = new MiddlewareManager();
        }

        return self::$inst;
    }
}
