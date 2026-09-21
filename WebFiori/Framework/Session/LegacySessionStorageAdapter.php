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
 * Wraps a legacy read()/save() storage implementation in the new per-key
 * SessionStorage interface, providing backward compatibility for custom
 * storage backends written against the old contract.
 *
 * ⚠️  IMPORTANT LIMITATIONS — READ BEFORE USING:
 *
 * This adapter provides NO per-key conflict detection. Each write operation
 * reads the whole session blob, updates the target key, and saves the whole
 * blob back. Under concurrent writes from multiple processes, the last writer
 * will still clobber changes made by earlier writers (last-writer-wins).
 *
 * Additionally, read() still returns a value from the whole-blob read, not a
 * truly independent per-key storage entry, so the adapter does NOT provide
 * the real-time cross-process visibility that a native SessionStorage
 * implementation delivers.
 *
 * Use this adapter ONLY as a temporary bridge while migrating a custom storage
 * implementation to the new SessionStorage interface. Once migrated, cross-
 * process visibility and conflict resolution will work correctly.
 *
 * @since 3.1.0
 */
class LegacySessionStorageAdapter implements SessionStorage {
    private LegacySessionStorageInterface $legacy;

    /**
     * @param LegacySessionStorageInterface $legacy The legacy storage to wrap.
     */
    public function __construct(LegacySessionStorageInterface $legacy) {
        $this->legacy = $legacy;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $sessionId): void {
        $this->legacy->remove($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function gc(string $olderThan, int $maxCount = 0): void {
        $this->legacy->gc($olderThan, $maxCount);
    }

    /**
     * {@inheritdoc}
     *
     * NOTE: Reads the whole session blob and extracts the key. Does not provide
     * true per-key storage reads; cross-process visibility is not guaranteed.
     */
    public function read(string $sessionId, string $key): ?array {
        $all = $this->readAll($sessionId);

        return $all[$key] ?? null;
    }

    /**
     * {@inheritdoc}
     *
     * Deserializes the whole session blob and wraps each value with version 0
     * (no version tracking is available in legacy storage).
     */
    public function readAll(string $sessionId): array {
        $blob = $this->legacy->read($sessionId);

        if ($blob === null) {
            return [];
        }

        $data = @unserialize($blob);

        if (!is_array($data)) {
            return [];
        }

        $result = [];

        foreach ($data as $k => $v) {
            $result[$k] = ['value' => $v, 'version' => 0];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $sessionId, string $key): void {
        $blob = $this->legacy->read($sessionId);

        if ($blob === null) {
            return;
        }

        $data = @unserialize($blob);

        if (!is_array($data)) {
            return;
        }

        unset($data[$key]);
        $this->legacy->save($sessionId, serialize($data));
    }

    /**
     * {@inheritdoc}
     *
     * NOTE: Implements a read-modify-write cycle on the whole session blob.
     * No conflict detection is performed regardless of the strategy parameter;
     * LAST_WRITE_WINS semantics always apply.
     */
    public function write(
        string $sessionId,
        string $key,
        mixed $value,
        string|int|null $expectedVersion,
        ConflictStrategy $strategy
    ): int {
        $blob = $this->legacy->read($sessionId);
        $data = ($blob !== null)
            ? (($d = @unserialize($blob)) !== false && is_array($d) ? $d : [])
            : [];

        $data[$key] = $value;
        $this->legacy->save($sessionId, serialize($data));

        // Version tracking is not available in legacy storage.
        return 1;
    }
}
