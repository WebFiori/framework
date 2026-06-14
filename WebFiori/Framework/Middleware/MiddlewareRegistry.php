<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2019-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Middleware;


/**
 * Concrete class that holds registered middleware and groups.
 *
 * This is the injectable, testable implementation. For static access,
 * use the MiddlewareManager facade.
 *
 * @author Ibrahim
 */
class MiddlewareRegistry {
    /**
     * @var array Registered middleware instances.
     */
    private array $middlewareList = [];
    /**
     * Returns a set of middleware that belongs to a specific group.
     *
     * @param string $groupName The name of the group.
     *
     * @return array Array of middleware in the group.
     */
    public function getGroup(string $groupName): array {
        $list = [];

        foreach ($this->middlewareList as $mw) {
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
     * @return AbstractMiddleware|null
     */
    public function getMiddleware(string $name): ?AbstractMiddleware {
        foreach ($this->middlewareList as $mw) {
            if ($mw->getName() == $name) {
                return $mw;
            }
        }

        return null;
    }
    /**
     * Register a new middleware.
     *
     * @param AbstractMiddleware|string $middleware The middleware instance or class name.
     *
     * @return bool True if registered successfully.
     */
    public function register($middleware): bool {
        if (gettype($middleware) == 'string') {
            try {
                $middleware = new $middleware();
            } catch (\Throwable $exc) {
                return false;
            }
        }

        if ($middleware instanceof AbstractMiddleware) {
            $this->middlewareList[] = $middleware;

            return true;
        }

        return false;
    }
    /**
     * Removes a middleware given its name.
     *
     * @param string $name The name of the middleware.
     */
    public function remove(string $name): void {
        $newList = [];

        foreach ($this->middlewareList as $md) {
            if ($md->getName() != $name) {
                $newList[] = $md;
            }
        }
        $this->middlewareList = $newList;
    }
    /**
     * Remove all registered middleware.
     */
    public function reset(): void {
        $this->middlewareList = [];
    }
    /**
     * Returns all registered middleware.
     *
     * @return array
     */
    public function getAll(): array {
        return $this->middlewareList;
    }
}
