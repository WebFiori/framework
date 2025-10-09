<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2024 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Session;

/**
 * A class which is used to hold the options names that are supported during
 * new session initialization or session update.
 *
 * @author Ibrahim
 */
class SessionOption {
    /**
     * An option which is used to set session duration (minutes)
     */
    const DURATION = 'duration';
    /**
     * An option which is used to set the name of the session. The name will be
     * same as session cookie.
     */
    const NAME = 'name';
    /**
     * An option which is used to set if session timeout will be refreshed on
     * each request or not (bool)
     */
    const REFRESH = 'refresh';
    /**
     * An option which is used to set the ID of the session.
     */
    const SESSION_ID = 'session-id';
}
