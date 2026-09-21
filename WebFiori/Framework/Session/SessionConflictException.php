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

use WebFiori\Framework\Exceptions\SessionException;

/**
 * Thrown by session storage when a write conflict is detected under
 * ConflictStrategy::REJECT and the stored version of a key has changed
 * since it was last read by the current process.
 *
 * @since 3.1.0
 */
class SessionConflictException extends SessionException {
    private string|int $actualVersion;
    private string $conflictKey;
    private string|int|null $expectedVersion;

    /**
     * @param string $key The session key where the conflict was detected.
     * @param string|int|null $expectedVersion The version the caller expected.
     * @param string|int $actualVersion The version currently in storage.
     */
    public function __construct(string $key, string|int|null $expectedVersion, string|int $actualVersion) {
        $this->conflictKey = $key;
        $this->expectedVersion = $expectedVersion;
        $this->actualVersion = $actualVersion;

        parent::__construct(
            "Session conflict on key '$key': expected version "
            .($expectedVersion ?? 'null').", got $actualVersion. "
            ."Another process has modified this key concurrently."
        );
    }

    /**
     * Returns the version currently stored (written by another process).
     */
    public function getActualVersion(): string|int {
        return $this->actualVersion;
    }

    /**
     * Returns the session key where the conflict was detected.
     */
    public function getConflictKey(): string {
        return $this->conflictKey;
    }

    /**
     * Returns the version the caller expected when writing.
     */
    public function getExpectedVersion(): string|int|null {
        return $this->expectedVersion;
    }
}
