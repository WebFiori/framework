<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 */
namespace WebFiori\Framework\Session;

/**
 * An in-memory session storage implementation backed by a static PHP array.
 *
 * Useful for:
 * - Application-level testing (no filesystem or database required).
 * - Single-process CLI commands that need session-like state.
 * - Unit tests that need predictable, fast session behavior.
 *
 * Because the store is static, all instances within the same process share
 * the same data. Call InMemorySessionStorage::reset() between tests to
 * isolate them.
 *
 * NOTE: Data is NOT persisted across requests or processes. This storage
 * does NOT provide cross-process visibility and is not suitable for
 * production multi-worker environments.
 *
 * @since 3.1.0
 */
class InMemorySessionStorage implements SessionStorage {
    /**
     * Shared in-process store keyed by [session_id][key] => [value, version].
     *
     * @var array<string, array<string, array{value: mixed, version: int}>>
     */
    private static array $store = [];

    /**
     * Destroys an entire session, removing all its keys.
     *
     * @param string $sessionId The session identifier.
     */
    public function destroy(string $sessionId): void {
        unset(self::$store[$sessionId]);
    }

    /**
     * No-op for in-memory storage (no TTL tracking).
     */
    public function gc(string $olderThan, int $maxCount = 0): void {
        // In-memory storage has no concept of expiry; GC is a no-op.
    }

    /**
     * Reads a single key from the session.
     *
     * @param string $sessionId The session identifier.
     * @param string $key The key to read.
     *
     * @return array{value: mixed, version: int}|null The stored entry, or null if not found.
     */
    public function read(string $sessionId, string $key): ?array {
        return self::$store[$sessionId][$key] ?? null;
    }

    /**
     * Reads all keys for a session.
     *
     * @param string $sessionId The session identifier.
     *
     * @return array<string, array{value: mixed, version: int}> All stored entries.
     */
    public function readAll(string $sessionId): array {
        return self::$store[$sessionId] ?? [];
    }

    /**
     * Removes a single key from the session.
     *
     * @param string $sessionId The session identifier.
     * @param string $key The key to remove.
     */
    public function remove(string $sessionId, string $key): void {
        unset(self::$store[$sessionId][$key]);
    }

    /**
     * Resets the entire in-memory store.
     *
     * Call this between test cases to prevent cross-test data leakage.
     */
    public static function reset(): void {
        self::$store = [];
    }

    /**
     * Writes a single key to the session.
     *
     * @param string $sessionId The session identifier.
     * @param string $key The key to write.
     * @param mixed $value The value to store.
     * @param string|int|null $expectedVersion Expected version for REJECT strategy.
     * @param ConflictStrategy $strategy How to handle concurrent modifications.
     *
     * @return int The new version number after the write.
     *
     * @throws SessionConflictException When strategy is REJECT and the stored
     *         version does not match expectedVersion.
     */
    public function write(
        string $sessionId,
        string $key,
        mixed $value,
        string|int|null $expectedVersion,
        ConflictStrategy $strategy
    ): int {
        $current = self::$store[$sessionId][$key] ?? null;
        $currentVersion = $current !== null ? (int) $current['version'] : 0;

        if ($strategy === ConflictStrategy::REJECT
            && $expectedVersion !== null
            && $currentVersion !== (int) $expectedVersion
        ) {
            throw new SessionConflictException($key, $expectedVersion, $currentVersion);
        }

        $newVersion = $currentVersion + 1;
        self::$store[$sessionId][$key] = [
            'value' => $value,
            'version' => $newVersion,
        ];

        return $newVersion;
    }
}
