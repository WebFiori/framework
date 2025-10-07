<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2023 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework\session;

/**
 * A class which is used to hold constants which represents different statuses
 * of a session.
 */
class SessionStatus {
    /**
     * A constant that indicates the session was expired.
     *
     */
    const EXPIRED = 'expired';
    /**
     * A constant that indicates the session was initialized but not started or
     * resumed.
     *
     */
    const INACTIVE = 'none';
    /**
     * A constant that indicates the session has been killed by calling the
     * method 'Session::kill()'.
     *
     */
    const KILLED = 'killed';
    /**
     * A constant that indicates the session was just created.
     *
     */
    const NEW = 'new';
    /**
     * A constant that indicates the session was paused.
     *
     */
    const PAUSED = 'paused';
    /**
     * A constant that indicates the session has been resumed.
     *
     */
    const RESUMED = 'resumed';
}
