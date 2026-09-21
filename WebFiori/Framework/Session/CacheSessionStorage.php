<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2026-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 */
namespace WebFiori\Framework\Session;

use WebFiori\Cache\Item;
use WebFiori\Cache\SecurityConfig;
use WebFiori\Cache\Storage;

/**
 * A session storage implementation backed by the cache library.
 *
 * Stores each session key as a separate cache entry:
 * - Per-key entry: "wf_session:{session_id}:{key}" => ['value'=>...,'version'=>int]
 * - Key index:     "wf_session:{session_id}:_keys" => [key1, key2, ...]
 *
 * The key index is used to support readAll() and destroy(). Note that
 * cache backends without atomic read-modify-write may have a small race
 * window when updating the index; for strict per-key conflict detection
 * prefer DatabaseSessionStorage.
 *
 * @author Ibrahim
 * @since 3.1.0 (per-key interface)
 */
class CacheSessionStorage implements SessionStorage {
    private string $prefix;
    private Storage $storage;
    private int $ttl;

    public function __construct(Storage $cacheStorage, string $prefix = 'wf_session:', int $ttl = 7200) {
        $this->storage = $cacheStorage;
        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $sessionId): void {
        $keys = $this->readIndex($sessionId);

        foreach ($keys as $key) {
            $this->storage->delete($this->keyFor($sessionId, $key));
        }

        $this->storage->delete($this->indexKey($sessionId));
    }

    /**
     * {@inheritdoc}
     */
    public function gc(string $olderThan, int $maxCount = 0): void {
        $this->storage->purgeExpired();
    }

    public function getPrefix(): string {
        return $this->prefix;
    }

    public function getStorage(): Storage {
        return $this->storage;
    }

    public function getTTL(): int {
        return $this->ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $sessionId, string $key): ?array {
        $cacheKey = $this->keyFor($sessionId, $key);
        $data = $this->storage->read($cacheKey, null);

        if (!is_array($data) || !array_key_exists('value', $data)) {
            return null;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function readAll(string $sessionId): array {
        $keys = $this->readIndex($sessionId);
        $result = [];

        foreach ($keys as $key) {
            $entry = $this->read($sessionId, $key);

            if ($entry !== null) {
                $result[$key] = $entry;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $sessionId, string $key): void {
        $this->storage->delete($this->keyFor($sessionId, $key));
        $this->removeFromIndex($sessionId, $key);
    }

    public function setTTL(int $ttl): void {
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function write(
        string $sessionId,
        string $key,
        mixed $value,
        string|int|null $expectedVersion,
        ConflictStrategy $strategy
    ): int {
        $current = $this->read($sessionId, $key);
        $currentVersion = $current !== null ? (int) $current['version'] : 0;

        if ($strategy === ConflictStrategy::REJECT
            && $expectedVersion !== null
            && $currentVersion !== (int) $expectedVersion
        ) {
            throw new SessionConflictException($key, $expectedVersion, $currentVersion);
        }

        $newVersion = $currentVersion + 1;
        $entry = ['value' => $value, 'version' => $newVersion];
        $cacheKey = $this->keyFor($sessionId, $key);

        $item = new Item($cacheKey, $entry, $this->ttl);
        $secConfig = new SecurityConfig();
        $secConfig->setEncryptionEnabled(false);
        $item->setSecurityConfig($secConfig);
        $this->storage->store($item);

        // Update the key index.
        $this->addToIndex($sessionId, $key);

        return $newVersion;
    }

    private function addToIndex(string $sessionId, string $key): void {
        $keys = $this->readIndex($sessionId);

        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
            $item = new Item($this->indexKey($sessionId), $keys, $this->ttl);
            $secConfig = new SecurityConfig();
            $secConfig->setEncryptionEnabled(false);
            $item->setSecurityConfig($secConfig);
            $this->storage->store($item);
        }
    }

    private function indexKey(string $sessionId): string {
        return $this->prefix.$sessionId.':_keys';
    }

    private function keyFor(string $sessionId, string $key): string {
        return $this->prefix.$sessionId.':'.$key;
    }

    /** @return string[] */
    private function readIndex(string $sessionId): array {
        $idx = $this->storage->read($this->indexKey($sessionId), null);

        return is_array($idx) ? $idx : [];
    }

    private function removeFromIndex(string $sessionId, string $key): void {
        $keys = $this->readIndex($sessionId);
        $keys = array_values(array_filter($keys, fn ($k) => $k !== $key));
        $item = new Item($this->indexKey($sessionId), $keys, $this->ttl);
        $secConfig = new SecurityConfig();
        $secConfig->setEncryptionEnabled(false);
        $item->setSecurityConfig($secConfig);
        $this->storage->store($item);
    }
}
