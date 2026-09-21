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
 * Interface for per-key session storage backends.
 *
 * Each operation targets a single key within a session, enabling real-time
 * cross-process visibility (reads fetch current storage state) and preventing
 * the last-writer-wins clobber caused by whole-blob save-at-end.
 *
 * All built-in drivers (DefaultSessionStorage, DatabaseSessionStorage,
 * CacheSessionStorage, InMemorySessionStorage) implement this interface.
 *
 * Custom implementations written against the old read()/save() contract
 * should be wrapped in a LegacySessionStorageAdapter until they can be
 * migrated to this interface.
 *
 * @since 3.1.0
 */
interface SessionStorage {
    /**
     * Destroys an entire session, removing all its keys.
     *
     * @param string $sessionId The session identifier.
     */
    public function destroy(string $sessionId): void;

    /**
     * Removes sessions that are older than the given time.
     *
     * @param string $olderThan A date string in the format 'Y-m-d H:i:s'.
     * @param int $maxCount Maximum number of sessions to remove. 0 = no limit.
     */
    public function gc(string $olderThan, int $maxCount = 0): void;
    /**
     * Reads a single key from the session.
     *
     * Returns the current stored value and its version number. The version
     * is used for optimistic concurrency control under ConflictStrategy::REJECT.
     *
     * @param string $sessionId The session identifier.
     * @param string $key The key to read.
     *
     * @return array{value: mixed, version: int}|null The stored entry,
     *         or null if the key does not exist in this session.
     */
    public function read(string $sessionId, string $key): ?array;

    /**
     * Reads all keys for a session.
     *
     * @param string $sessionId The session identifier.
     *
     * @return array<string, array{value: mixed, version: int}> All stored
     *         entries keyed by their key names.
     */
    public function readAll(string $sessionId): array;

    /**
     * Removes a single key from the session.
     *
     * @param string $sessionId The session identifier.
     * @param string $key The key to remove.
     */
    public function remove(string $sessionId, string $key): void;

    /**
     * Writes a single key to the session and returns the new version.
     *
     * @param string $sessionId The session identifier.
     * @param string $key The key to write.
     * @param mixed $value The value to store.
     * @param string|int|null $expectedVersion The version the caller last read,
     *        used when strategy is REJECT to detect concurrent modifications.
     *        Pass null to skip version checking.
     * @param ConflictStrategy $strategy How to handle concurrent modifications.
     *
     * @return string|int The new version number after the write.
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
    ): string|int;
}
