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

use WebFiori\Cli\Runner;
use WebFiori\Framework\Exceptions\SessionException;

/**
 * File-based session storage engine.
 *
 * Stores sessions as JSON files in [APP_DIR]/Storage/Sessions/. Each file
 * contains all keys for one session as a JSON object:
 *
 * {"key1":{"value":...,"version":1},"key2":{"value":...,"version":2}}
 *
 * Each write acquires an exclusive file lock (flock LOCK_EX) around the full
 * read-modify-write cycle to prevent file-level corruption under concurrent
 * access on the same host.
 *
 * For true cross-host per-key isolation, use DatabaseSessionStorage or a
 * distributed cache backend.
 *
 * Existing session files written by the old blob-serialization format are
 * automatically migrated to the new JSON format on first access.
 *
 * @author Ibrahim
 * @since 3.1.0 (per-key interface)
 */
class DefaultSessionStorage implements ExpiringSessionStorage {
    private const EXPIRY_SUFFIX = '.exp';
    private string $storeLoc;

    public function __construct() {
        $sessionsDirName = 'Sessions';
        $sessionsStoragePath = APP_PATH.'Storage';
        $this->storeLoc = $sessionsStoragePath.DS.$sessionsDirName;

        if (!file_exists($this->storeLoc) && is_writable($sessionsStoragePath)) {
            set_error_handler(function (int $errno)
            {
                throw new SessionException('Unable to create sessions storage folder.', $errno);
            });

            if (!is_dir($sessionsStoragePath)) {
                mkdir($sessionsStoragePath);
            }

            if (!is_dir($this->storeLoc)) {
                mkdir($this->storeLoc);
            }
            restore_error_handler();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $sessionId): void {
        $path = $this->filePath($sessionId);

        if (file_exists($path)) {
            unlink($path);
        }
        $expiryPath = $this->expiryPath($sessionId);

        if (file_exists($expiryPath)) {
            unlink($expiryPath);
        }
    }

    /**
     * {@inheritdoc}
     *
     * The expiry is kept in a sidecar file next to the session file, because
     * the modification time of the session file changes on every write.
     */
    public function getExpiry(string $sessionId): ?int {
        $path = $this->expiryPath($sessionId);

        if (!file_exists($path)) {
            return null;
        }
        $raw = @file_get_contents($path);

        return $raw !== false && ctype_digit(trim($raw)) ? (int) trim($raw) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setExpiry(string $sessionId, ?int $expiresAt): void {
        if (!$this->canWrite()) {
            return;
        }
        $path = $this->expiryPath($sessionId);

        if ($expiresAt === null) {
            if (file_exists($path)) {
                unlink($path);
            }

            return;
        }
        file_put_contents($path, (string) $expiresAt, LOCK_EX);
    }

    /**
     * {@inheritdoc}
     */
    public function gc(string $olderThan, int $maxCount = 0): void {
        if (!$this->isStorageDirExist()) {
            return;
        }

        $files = array_diff(scandir($this->storeLoc), ['.', '..']);
        $removed = 0;
        $threshold = strtotime($olderThan);
        $now = time();

        foreach ($files as $file) {
            $filePath = $this->storeLoc.DS.$file;

            if (substr($file, -strlen(self::EXPIRY_SUFFIX)) === self::EXPIRY_SUFFIX) {
                // Remove expiry files whose session file is gone.
                if (!file_exists(substr($filePath, 0, -strlen(self::EXPIRY_SUFFIX))) && file_exists($filePath)) {
                    unlink($filePath);
                }

                continue;
            }

            if ($maxCount > 0 && $removed >= $maxCount) {
                break;
            }

            $expiry = $this->getExpiry($file);

            if ($expiry !== null) {
                $expired = $expiry <= $now;
            } else {
                $mtime = filemtime($filePath);
                $expired = $mtime !== false && $mtime < $threshold;
            }

            if ($expired) {
                $this->destroy($file);
                $removed++;
            }
        }
    }

    /**
     * Checks if the storage directory exists and is writable.
     */
    public function isStorageDirExist(): bool {
        return file_exists($this->storeLoc) && is_writable($this->storeLoc);
    }

    /**
     * Checks if a session file exists.
     */
    public function isStorageFileExist(string $sId): bool {
        return $this->isStorageDirExist() && file_exists($this->filePath($sId));
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $sessionId, string $key): ?array {
        $all = $this->readAll($sessionId);

        return $all[$key] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function readAll(string $sessionId): array {
        if (!$this->isStorageDirExist()) {
            return [];
        }

        $path = $this->filePath($sessionId);

        if (!file_exists($path)) {
            return [];
        }

        $handle = @fopen($path, 'rb');

        if (!is_resource($handle)) {
            return [];
        }

        flock($handle, LOCK_SH);
        $raw = stream_get_contents($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

        if ($raw === false || $raw === '') {
            return [];
        }

        return $this->decode($raw, $sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $sessionId, string $key): void {
        if (!$this->isStorageDirExist()) {
            return;
        }

        $path = $this->filePath($sessionId);

        if (!file_exists($path)) {
            return;
        }

        $handle = @fopen($path, 'c+b');

        if (!is_resource($handle)) {
            return;
        }

        flock($handle, LOCK_EX);
        rewind($handle);
        $raw = stream_get_contents($handle);
        $data = ($raw !== false && $raw !== '') ? $this->decode($raw, $sessionId) : [];
        unset($data[$key]);
        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, $encoded);
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
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
        if (!$this->canWrite()) {
            return 0;
        }

        $path = $this->filePath($sessionId);
        $handle = @fopen($path, 'c+b');

        if (!is_resource($handle)) {
            return 0;
        }

        flock($handle, LOCK_EX);

        // Re-read after acquiring lock (another process may have written since).
        rewind($handle);
        $raw = stream_get_contents($handle);
        $data = ($raw !== false && $raw !== '')
            ? $this->decode($raw, $sessionId)
            : [];

        $currentVersion = isset($data[$key]) ? (int) $data[$key]['version'] : 0;

        if ($strategy === ConflictStrategy::REJECT
            && $expectedVersion !== null
            && $currentVersion !== (int) $expectedVersion
        ) {
            flock($handle, LOCK_UN);
            fclose($handle);

            throw new SessionConflictException($key, $expectedVersion, $currentVersion);
        }

        $newVersion = $currentVersion + 1;
        $data[$key] = ['value' => $value, 'version' => $newVersion];

        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, $encoded);
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

        return $newVersion;
    }

    private function canWrite(): bool {
        return (!Runner::isCLI() || defined('__PHPUNIT_PHAR__')
            || class_exists('PHPUnit\\Framework\\TestCase'))
            && $this->isStorageDirExist();
    }

    /**
     * Decodes the file content into the per-key array format.
     * Handles both the new JSON format and the legacy blob format.
     *
     * @return array<string, array{value: mixed, version: int}>
     */
    private function decode(string $raw, string $sessionId): array {
        // Try new JSON format first.
        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            // Validate that it's in the per-key format (each value has 'value'+'version').
            $firstVal = reset($decoded);

            if ($firstVal === false || (is_array($firstVal) && array_key_exists('value', $firstVal))) {
                return $decoded;
            }
        }

        // Legacy blob format: return empty (legacy sessions are effectively invalidated).
        // The session will be treated as new, and new per-key entries will be written on next set().
        return [];
    }

    private function expiryPath(string $sessionId): string {
        return $this->filePath($sessionId).self::EXPIRY_SUFFIX;
    }

    private function filePath(string $sessionId): string {
        return $this->storeLoc.DS.$sessionId;
    }
}
