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
namespace WebFiori\Framework\Session;

use WebFiori\Cli\Runner;
use WebFiori\File\File;
use WebFiori\Framework\Exceptions\SessionException;
/**
 * The default sessions storage engine.
 *
 * This storage engine will store session state as a file in the folder
 * '[APP_DIR]/Storage/Sessions'. The name of the file that contains session state
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
        $sessionsDirName = 'Sessions';
        $sessionsStoragePath = APP_PATH.'Storage';
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
     * Removes sessions that are older than the given time.
     *
     * @param string $olderThan A date string in the format 'Y-m-d H:i:s'.
     * Sessions not modified since this time should be removed.
     *
     * @param int $maxCount Maximum number of sessions to remove in this run.
     * 0 means no limit.
     */
    public function gc(string $olderThan, int $maxCount = 0) {
        if (!$this->isStorageDirExist()) {
            return;
        }

        $sessionsFiles = array_diff(scandir($this->storeLoc), ['.', '..']);
        $removed = 0;
        $olderThanTimestamp = strtotime($olderThan);

        foreach ($sessionsFiles as $file) {
            if ($maxCount > 0 && $removed >= $maxCount) {
                break;
            }

            $filePath = $this->storeLoc.DS.$file;
            $mtime = filemtime($filePath);

            if ($mtime !== false && $mtime < $olderThanTimestamp) {
                unlink($filePath);
                $removed++;
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

        return false;
    }
    /**
     * Reads a session from session file.
     *
     * @param string $sessionId The ID of the session.
     *
     * @return string|null If the method successfully accessed session state,
     * the method will return a string that represents the session. Other than that,
     * the method will return null.
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

        return null;
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
     */
    public function save(string $sessionId, string $serializedSession) {
        if ((!Runner::isCLI() || defined('__PHPUNIT_PHAR__') || class_exists('PHPUnit\\Framework\\TestCase')) && $this->isStorageDirExist()) {
            //Session storage should be only allowed in testing env or http
            $file = new File($sessionId, $this->storeLoc);
            $file->setRawData($serializedSession);
            $file->write(false, true);
        }
    }
}
