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

        foreach (self::get()->middlewareList as $mw) {
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
        foreach (self::get()->middlewareList as $mw) {
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
        $mw = self::getMiddleware($name);

        if ($mw instanceof AbstractMiddleware) {
            self::get()->middlewareList->remove(self::get()->middlewareList->indexOf($mw));
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
