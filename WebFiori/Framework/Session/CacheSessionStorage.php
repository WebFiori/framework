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
namespace WebFiori\Framework\Session;

use WebFiori\Cache\Item;
use WebFiori\Cache\SecurityConfig;
use WebFiori\Cache\Storage;

/**
 * A session storage implementation backed by the cache library.
 *
 * This allows sessions to be stored using any cache backend (Redis, file, etc.)
 * by delegating to the cache Storage interface.
 *
 * @author Ibrahim
 */
class CacheSessionStorage implements SessionStorage {
    /**
     * @var Storage The cache storage backend.
     */
    private Storage $storage;
    /**
     * @var string Prefix for session cache keys.
     */
    private string $prefix;
    /**
     * @var int Session TTL in seconds.
     */
    private int $ttl;

    /**
     * Creates new instance.
     *
     * @param Storage $cacheStorage The cache storage backend to use.
     * @param string $prefix Key prefix to namespace session entries.
     * @param int $ttl Time-to-live for session entries in seconds. Default: 7200 (2 hours).
     */
    public function __construct(Storage $cacheStorage, string $prefix = 'wf_session:', int $ttl = 7200) {
        $this->storage = $cacheStorage;
        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    /**
     * Returns the cache storage backend.
     *
     * @return Storage
     */
    public function getStorage(): Storage {
        return $this->storage;
    }

    /**
     * Returns the key prefix.
     *
     * @return string
     */
    public function getPrefix(): string {
        return $this->prefix;
    }

    /**
     * Returns the TTL in seconds.
     *
     * @return int
     */
    public function getTTL(): int {
        return $this->ttl;
    }

    /**
     * Sets the TTL for session entries.
     *
     * @param int $ttl Time-to-live in seconds.
     */
    public function setTTL(int $ttl): void {
        $this->ttl = $ttl;
    }

    /**
     * {@inheritDoc}
     */
    public function gc(string $olderThan, int $maxCount = 0) {
        $this->storage->purgeExpired();
    }

    /**
     * {@inheritDoc}
     */
    public function read(string $sessionId) {
        $data = $this->storage->read($this->prefix . $sessionId, null);

        if ($data === null) {
            return null;
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $sessionId) {
        $this->storage->delete($this->prefix . $sessionId);
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $sessionId, string $serializedSession) {
        $item = new Item($this->prefix . $sessionId, $serializedSession, $this->ttl);
        $secConfig = new SecurityConfig();
        $secConfig->setEncryptionEnabled(false);
        $item->setSecurityConfig($secConfig);
        $this->storage->store($item);
    }
}
