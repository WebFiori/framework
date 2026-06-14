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

/**
 * CLI command to clear the route cache.
 *
 * @author Ibrahim
 */
class RoutesClearCommand extends Command {
    public function __construct() {
        parent::__construct('routes:clear', [], 'Clear the route cache.');
    }

    public function exec(): int {
        $cache = $this->createRouteCache();
        $cache->clear();
        $this->success('Route cache cleared.');

        return 0;
    }

    private function createRouteCache(): RouteCache {
        $storagePath = APP_PATH . 'Storage';

        return new RouteCache(new Cache(new FileStorage($storagePath)), true);
    }
}
