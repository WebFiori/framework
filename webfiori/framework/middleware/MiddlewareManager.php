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
namespace webfiori\framework\middleware;

use webfiori\collections\LinkedList;
/**
 * This class is used to manage the operations which are related to middleware.
 *
 * @author Ibrahim
 * 
 * @since 1.0
 * 
 * @since 2.0.0
 */
class MiddlewareManager {
    private static $inst;
    /**
     *
     * @var LinkedList 
     */
    private $middlewareList;
    private function __construct() {
        $this->middlewareList = new LinkedList();
    }
    /**
     * Returns a set of meddalewares that belongs to a specific group.
     * 
     * @param string $groupName The name of the group.
     * 
     * @return LinkedList The method will return a linked list with all 
     * middleware in the group. If no group which has the given name exist, the 
     * list will be empty.
     * 
     * @since 1.0
     */
    public static function getGroup($groupName) {
        $list = new LinkedList();
        $mdList = self::get()->middlewareList;

        foreach ($mdList as $mw) {
            if (in_array($groupName, $mw->getGroups())) {
                $list->add($mw);
            }
        }

        return $list;
    }
    /**
     * Returns a registered middleware given its name.
     * 
     * @param strin $name The name of the middleware.
     * 
     * @return AbstractMiddleware|null If a middleware with the given name is 
     * found, the method will return it. Other than that, the method will return 
     * null.
     * 
     * @since 1.0
     */
    public static function getMiddleware($name) {
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
     * @param AbstractMiddleware $middleware The middleware that will be registered.
     * 
     * @since 1.0
     */
    public static function register(AbstractMiddleware $middleware) {
        self::get()->middlewareList->add($middleware);
    }
    /**
     * Removes a middleware given its name.
     * 
     * @param string $name The name of the middleware.
     * 
     * @since 1.0
     */
    public static function remove($name) {
        $manager = self::get();
        $mw = $manager->getMiddleware($name);

        if ($mw instanceof AbstractMiddleware) {
            $manager->middlewareList->remove($manager->middlewareList->indexOf($mw));
        }
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
