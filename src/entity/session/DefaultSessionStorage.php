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

use webfiori\entity\File;
use webfiori\entity\Util;
use webfiori\entity\cli\CLI;
/**
 * The default sessions storage engine.
 *
 * This storage engine will store session state as a file in the folder 
 * 'app/storage/sessions'. The name of the file that contains session state 
 * will be the ID of the session.
 * 
 * @author Ibrahim
 * 
 * @since 1.1.0
 * 
 * @version 1.0
 */
class DefaultSessionStorage implements SessionStorage {
    private $storeLoc;
    /**
     * Creates new instance of the class.
     * 
     * @since 1.0
     */
    public function __construct() {
        $this->storeLoc = ROOT_DIR.DS.'app'.DS.'storage'.DS.'sessions';
        Util::isDirectory($this->storeLoc, true);
    }
    /**
     * Removes all inactive sessions.
     * 
     * This method will check if the constant 'SESSION_GC' is exist and its value 
     * is valid. If exist and valid, it will be used as reference for removing 
     * old sessions. If it does not exist, the method will remove any inactive 
     * session which is older than 30 days.
     * 
     * @since 1.0
     */
    public function gc() {
        $sessionsFiles = array_diff(scandir($this->storeLoc), ['.','..']);

        if (defined('SESSION_GC') && SESSION_GC > 0) {
            $olderThan = time() - SESSION_GC;
        } else {
            //Clear any sesstion which is older than 30 days
            $olderThan = time() - 60 * 60 * 24 * 30;
        }

        foreach ($sessionsFiles as $file) {
            $fileObj = new File($this->storeLoc.DS.$file);

            if ($fileObj->getLastModified() < $olderThan) {
                $fileObj->remove();
            }
        }
    }
    /**
     * Reads a session from session file.
     * 
     * @param string $sessionId The ID of the session.
     * 
     * @return string|null If the method successfully accessed session state, 
     * the method will return a string that represents the session. Other than that, 
     * the method will return null.
     * 
     * @since 1.0
     */
    public function read($sessionId) {
        $file = new File($sessionId, $this->storeLoc);

        if ($file->isExist()) {
            $file->read();

            return $file->getRawData();
        }
    }
    /**
     * Removes session file.
     * 
     * @param string $sessionId The ID of the session.
     * 
     * @since 1.0
     */
    public function remove($sessionId) {
        unlink($this->storeLoc.DS.$sessionId);
    }
    /**
     * Stores session state to a file.
     * 
     * @param Session $sessionId The session that will be stored.
     * 
     * @param string $session The session that will be stored.
     * 
     * @since 1.0
     */
    public function save($sessionId, $session) {
        //if (!CLI::isCLI()) {
            $file = new File($sessionId, $this->storeLoc);
            $file->setRawData($session);
            $file->write(false, true);
        //}
    }
}
