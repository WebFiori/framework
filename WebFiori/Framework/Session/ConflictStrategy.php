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
 * Defines how concurrent write conflicts are resolved when two processes
 * attempt to modify the same session key.
 *
 * @since 3.1.0
 */
enum ConflictStrategy {
    /**
     * Overwrite the stored value regardless of concurrent modifications.
     *
     * Never throws. Behaves identically to the legacy whole-blob save
     * (last write wins). Backward compatible with existing code.
     *
     * This is the default.
     */
    case LAST_WRITE_WINS;

    /**
     * Throw SessionConflictException when another process has modified
     * the key since it was last read.
     *
     * The caller must catch SessionConflictException and decide how to
     * proceed (retry, discard, merge). Use when the caller must handle
     * conflicts explicitly — e.g. financial aggregates where losing a
     * write is unacceptable.
     */
    case REJECT;

    /**
     * Re-read the current value from storage and call a user-supplied
     * callback to compute the new value, retrying until the write succeeds
     * or a retry limit is reached.
     *
     * The callback receives the current stored value and must return the
     * desired new value: fn(mixed $currentValue): mixed
     *
     * Suitable for counters, aggregations, and append-style operations
     * where the new value depends on what another process may have written.
     */
    case RETRY_WITH_CALLBACK;
}
