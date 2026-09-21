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
 * Defines how a Session reads values from shared storage.
 *
 * The default (REALTIME) guarantees cross-process visibility and is the
 * correct setting for multi-worker environments such as IIS/FastCGI or
 * PHP-FPM with more than one worker process.
 *
 * @since 3.1.0
 */
enum ReadStrategy {
    /**
     * Session data is loaded once at start(); no automatic re-reads.
     *
     * get() always returns the locally-cached snapshot value. To pick up
     * writes from other processes, the developer must call Session::refresh()
     * explicitly. Suitable for long-running requests (e.g. SSE streaming)
     * that define their own sync boundaries.
     */
    case MANUAL_SYNC;
    /**
     * Every get() fetches the current value directly from storage.
     *
     * Guarantees that one process immediately sees writes committed by
     * another process. Correct for IIS/FastCGI multi-worker deployments.
     * Higher I/O than snapshot modes; each get() is one storage read.
     *
     * This is the default.
     */
    case REALTIME;

    /**
     * Session data is loaded into a local snapshot at start().
     *
     * On get(), the local snapshot is checked first. If the key is absent
     * (cache miss), the current value is fetched from storage and cached
     * locally. Keys present in the snapshot at start() are NOT refreshed
     * mid-request; a write by another process to an existing key will not
     * be seen until a new request starts.
     *
     * Use when you need fewer storage reads and can tolerate partial
     * staleness for keys that existed at request start.
     */
    case SNAPSHOT_WITH_MISS;
}
