<?php
/*
 * The MIT License
 *
 * Copyright 2020 Ibrahim, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace webfiori\entity\session;

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
    public function save($sessionId, $serializedSession);
    /**
     * Reads session state.
     * 
     * This method must be implemented in a way that it returns a string 
     * when session state is loaded. The string will be later 
     * unserialized by sessions manager.
     * 
     * @param string $sesstionId The unique identifier of the session.
     * 
     * @return string|null The method should return a string that represents the 
     * session. If no session was found which has the given ID, the method 
     * should return null.
     * 
     * @since 1.0
     */
    public function read($sesstionId);
    /**
     * Kill a session and remove its state from the storage.
     * 
     * @param string $sesstionId The unique identifier of the session.
     * 
     * @since 1.0
     */
    public function remove($sesstionId);
    /**
     * Removes all inactive sessions from the storage.
     * 
     * @since 1.0
     */
    public function gc();
}
