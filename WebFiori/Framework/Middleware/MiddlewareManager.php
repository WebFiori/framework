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

/**
 * A static facade for the MiddlewareRegistry class.
 *
 * Provides a convenient static API that delegates to a default MiddlewareRegistry instance.
 * For dependency injection or testing, use MiddlewareRegistry directly.
 *
 * @author Ibrahim
 */
class MiddlewareManager {
    /**
     * @var MiddlewareRegistry|null
     */
    private static ?MiddlewareRegistry $inst = null;
    /**
     * Returns the default MiddlewareRegistry instance.
     *
     * @return MiddlewareRegistry
     */
    public static function getInstance(): MiddlewareRegistry {
        if (self::$inst === null) {
            self::$inst = new MiddlewareRegistry();
        }

        return self::$inst;
    }
    /**
     * Replaces the default MiddlewareRegistry instance.
     *
     * @param MiddlewareRegistry $registry The registry to use as default.
     */
    public static function setInstance(MiddlewareRegistry $registry): void {
        self::$inst = $registry;
    }
    /**
     * Destroys the default instance. Next call creates a fresh one.
     */
    public static function reset(): void {
        self::$inst = null;
    }
    /**
     * @see MiddlewareRegistry::getGroup()
     */
    public static function getGroup(string $groupName): array {
        return self::getInstance()->getGroup($groupName);
    }
    /**
     * @see MiddlewareRegistry::getMiddleware()
     */
    public static function getMiddleware(string $name): ?AbstractMiddleware {
        return self::getInstance()->getMiddleware($name);
    }
    /**
     * @see MiddlewareRegistry::register()
     */
    public static function register($middleware): bool {
        return self::getInstance()->register($middleware);
    }
    /**
     * @see MiddlewareRegistry::remove()
     */
    public static function remove(string $name): void {
        self::getInstance()->remove($name);
    }
}
