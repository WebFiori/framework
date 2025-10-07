<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework\session;

/**
 * An interface which can be used to implement different types of sessions storage.
 *
 * @author Ibrahim
 *
 * @since 1.1.0
 *
 * @version 1.0
 */
interface SessionStorage {
    /**
     * Removes all inactive sessions from the storage.
     *
     * @since 1.0
     */
    public function gc();
    /**
     * Reads session state.
     *
     * This method must be implemented in a way that it returns a string
     * when session state is loaded. The string will be later
     * unserialized by sessions manager.
     *
     * @param string $sessionId The unique identifier of the session.
     *
     * @return string|null The method should return a string that represents the
     * session. If no session was found which has the given ID, the method
     * should return null.
     *
     * @since 1.0
     */
    public function read(string $sessionId);
    /**
     * Kill a session and remove its state from the storage.
     *
     * @param string $sessionId The unique identifier of the session.
     *
     * @since 1.0
     */
    public function remove(string $sessionId);
    /**
     * Store session state.
     *
     * This method must store serialized session. The developer can use the method
     * <code>Session::serialize()</code> to serialize any session.
     *
     * @param string $sessionId The ID of the session that will be stored.
     *
     * @param string $serializedSession A string that represents the session.
     *
     * @since 1.0
     */
    public function save(string $sessionId, string $serializedSession);
}
