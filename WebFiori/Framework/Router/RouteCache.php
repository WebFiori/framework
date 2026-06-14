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
namespace WebFiori\Framework\Router;

use WebFiori\Cache\Cache;

/**
 * Caches route definitions to avoid discovery/registration overhead in production.
 *
 * @author Ibrahim
 */
class RouteCache {
    /**
     * @var Cache The cache instance.
     */
    private Cache $cache;
    /**
     * @var string Cache key for route data.
     */
    private string $cacheKey;
    /**
     * @var bool Whether caching is enabled.
     */
    private bool $enabled;

    /**
     * Creates new instance.
     *
     * @param Cache $cache The cache instance to use.
     * @param bool $enabled Whether route caching is enabled.
     * @param string $cacheKey The cache key for route data.
     */
    public function __construct(Cache $cache, bool $enabled = false, string $cacheKey = 'wf_routes_cache') {
        $this->cache = $cache;
        $this->enabled = $enabled;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Returns whether route caching is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool {
        return $this->enabled;
    }

    /**
     * Sets whether route caching is enabled.
     *
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void {
        $this->enabled = $enabled;
    }

    /**
     * Checks if a route cache exists.
     *
     * @return bool
     */
    public function isCached(): bool {
        return $this->cache->get($this->cacheKey) !== null;
    }

    /**
     * Load cached routes into the Router.
     *
     * @return bool True if cache was loaded, false if no cache exists.
     */
    public function load(): bool {
        if (!$this->enabled) {
            return false;
        }

        $data = $this->cache->get($this->cacheKey);

        if ($data === null) {
            return false;
        }

        $discovered = $data['discovered'] ?? [];

        if (!empty($discovered)) {
            ServiceRouter::setDiscovered($discovered);
        }

        // Re-register routes from the cached service map
        $configs = $data['configs'] ?? [];

        foreach ($configs as $config) {
            ServiceRouter::discover(
                $config['namespace'],
                $config['basePath'],
                $config['options'] ?? [],
                $config['directory'] ?? null,
                $config['recursive'] ?? false
            );
        }

        return true;
    }

    /**
     * Build route cache from current ServiceRouter state.
     *
     * @param array $discoverConfigs Array of discover() call configs to replay on load.
     *
     * @return int Number of discovered services cached.
     */
    public function build(array $discoverConfigs = []): int {
        $discovered = ServiceRouter::getDiscovered();

        $data = [
            'discovered' => $discovered,
            'configs' => $discoverConfigs,
            'built_at' => date('c'),
            'total' => count($discovered),
        ];

        $this->cache->set($this->cacheKey, $data, 31536000, true);

        return count($discovered);
    }

    /**
     * Clear the route cache.
     */
    public function clear(): void {
        $this->cache->delete($this->cacheKey);
    }

    /**
     * Returns the cache key.
     *
     * @return string
     */
    public function getCacheKey(): string {
        return $this->cacheKey;
    }
}
