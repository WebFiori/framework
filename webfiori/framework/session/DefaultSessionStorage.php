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

use WebFiori\Cli\Runner;
use WebFiori\File\exceptions\FileException;
use WebFiori\File\File;
use webfiori\framework\exceptions\SessionException;
/**
 * The default sessions storage engine.
 *
 * This storage engine will store session state as a file in the folder
 * 'app/sto/sessions'. The name of the file that contains session state
 * will be the ID of the session.
 *
 * @author Ibrahim
 *
 */
class DefaultSessionStorage implements SessionStorage {
    private $storeLoc;
    /**
     * Creates new instance of the class.
     *
     */
    public function __construct() {
        $sessionsDirName = 'sessions';
        $sessionsStoragePath = APP_PATH.'sto';
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
     * Removes all inactive sessions.
     *
     * This method will check if the constant 'SESSION_GC' is existed and its value
     * is valid. If exist and valid, it will be used as reference for removing
     * old sessions. If it does not exist, the method will remove any inactive
     * session which is older than 30 days.
     *
     */
    public function gc() {
        if (!$this->isStorageDirExist()) {
            return;
        }
        $sessionsFiles = array_diff(scandir($this->storeLoc), ['.','..']);

        $olderThan = SessionsManager::getGCTime();

        foreach ($sessionsFiles as $file) {
            $fileObj = new File($this->storeLoc.DS.$file);

            if ($fileObj->getLastModified() < $olderThan) {
                $fileObj->remove();
            }
        }
    }
    /**
     * Checks if sessions storage location is existed and writable.
     *
     * @return bool If sessions storage location exist and is writable,
     * the method will return true.
     *
     */
    public function isStorageDirExist(): bool {
        return file_exists($this->storeLoc) && is_writable($this->storeLoc);
    }
    /**
     * Checks if session storage file exist or not.
     *
     * Note that this method will first check for existence of storage
     * directory by calling the method DefaultSessionStorage::isStorageDirExist().
     *
     * @return bool If sessions storage file exist and is writable,
     * the method will return true.
     *
     */
    public function isStorageFileExist(string $sId): bool {
        if ($this->isStorageDirExist()) {
            return file_exists($this->storeLoc.DS.$sId);
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
     * @throws FileException
     */
    public function read(string $sessionId) {
        if (!$this->isStorageDirExist()) {
            return null;
        }
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
     */
    public function remove(string $sessionId) {
        if ($this->isStorageFileExist($sessionId)) {
            unlink($this->storeLoc.DS.$sessionId);
        }
    }

    /**
     * Stores session state to a file.
     *
     * @param string $sessionId The session that will be stored.
     *
     * @param string $serializedSession The session that will be stored.
     *
     * @throws FileException
     */
    public function save(string $sessionId, string $serializedSession) {
        if ((!Runner::isCLI() || defined('__PHPUNIT_PHAR__')) && $this->isStorageDirExist()) {
            //Session storage should be only allowed in testing env or http
            $file = new File($sessionId, $this->storeLoc);
            $file->setRawData($serializedSession);
            $file->write(false, true);
        }
    }
}
