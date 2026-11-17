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

/**
 * An optional extension of {@see SessionStorage} for backends that keep the
 * expiry time of each session as first-class data.
 *
 * {@see Session} is the only component that knows a session's lifetime and
 * refresh policy. It computes the absolute expiry time and hands it to the
 * storage with {@see ExpiringSessionStorage::setExpiry()}. The storage then
 * uses its native mechanism to enforce it (an indexed column, a cache TTL,
 * a sidecar file, ...), so garbage collection can remove exactly the sessions
 * that have expired instead of guessing from a global idle threshold.
 *
 * Storages that do not implement this interface keep working: the session
 * system falls back to the idle-time threshold passed to
 * {@see SessionStorage::gc()}.
 *
 * Behavior of gc() for implementors:
 * - A session with a recorded expiry is removed when that expiry has passed,
 *   whatever the idle threshold says.
 * - A session without a recorded expiry (created before upgrade, or with a
 *   non-persistent lifetime) is removed using the idle threshold, as before.
 *
 * See ADR-0061 in the webfiori/docs repository. A later major version is
 * expected to fold these methods into {@see SessionStorage} itself.
 *
 * @since 3.2.0
 */
interface ExpiringSessionStorage extends SessionStorage {
    /**
     * Returns the recorded expiry time of a session.
     *
     * @param string $sessionId The session identifier.
     *
     * @return int|null The expiry as a Unix timestamp, or null if no expiry
     *         is recorded (or the session does not exist).
     */
    public function getExpiry(string $sessionId): ?int;

    /**
     * Records the time at which a session expires.
     *
     * @param string $sessionId The session identifier.
     * @param int|null $expiresAt The expiry as a Unix timestamp. Null clears
     *        the recorded expiry, which makes the session subject to the idle
     *        threshold again.
     */
    public function setExpiry(string $sessionId, ?int $expiresAt): void;
}
