<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Cli\Commands;

use WebFiori\Cache\Cache;
use WebFiori\Cache\FileStorage;
use WebFiori\Cli\Command;
use WebFiori\Framework\Router\RouteCache;
use WebFiori\Framework\Router\Router;

/**
 * CLI command to build the route cache.
 *
 * @author Ibrahim
 */
class RoutesCacheCommand extends Command {
    public function __construct() {
        parent::__construct('routes:cache', [], 'Build the route cache for production.');
    }

    public function exec(): int {
        $cache = $this->createRouteCache();
        $cache->setEnabled(true);
        $count = $cache->build();
        $this->success("Route cache built: $count route(s) cached.");

        return 0;
    }

    private function createRouteCache(): RouteCache {
        $storagePath = APP_PATH . 'Storage';

        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        return new RouteCache(new Cache(new FileStorage($storagePath)), true);
    }
}
