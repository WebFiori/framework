<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2024 Ibrahim BinAlshikh and Contributors
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework\cache;

/**
 * File based cache storage engine.
 */
class FileStorage implements Storage {
    private $cacheDir;
    private $data;
    /**
     * Creates new instance of the class.
     * 
     * The default location for cache files will be in [APP_PATH]/sto/cache.
     * To use a custom path, the developer can define the constant CACHE_PATH. 
     */
    public function __construct() {
        $default = APP_PATH.DS.'sto'.DS.'cache'.DS;
        $path = defined('CACHE_PATH') ? CACHE_PATH : $default;
        $this->setPath($path);
    }
    /**
     * Store an item into the cache.
     * 
     * @param Item $item An item that will be added to the cache.
     */
    public function cache(Item $item) {
        $filePath = $this->getPath() .DS. md5($item->getKey()) . '.cache';
        $encryptedData = $item->getDataEncrypted();
        
        if (!is_dir($this->getPath())) {
            mkdir($this->getPath(), 0755, true);
        }
        file_put_contents($filePath, serialize([
            'data' => $encryptedData, 
            'created_at' => time(), 
            'ttl' => $item->getTTL(), 
            'expires' => $item->getExpiryTime(),
            'key' => $item->getKey()
        ]));
    }
    private function initData(string $key) {
        $filePath = $this->cacheDir . md5($key) . '.cache';

        if (!file_exists($filePath)) {
            $this->data = [
                'expires' => 0,
                'ttl' => 0,
                'data' => null,
                'created_at' => 0,
                'key' => ''
            ];
            return ;
        }

        $this->data = unserialize(file_get_contents($filePath));
    }
    /**
     * Removes an item from the cache.
     * 
     * @param string $key The key of the item.
     */
    public function delete(string $key) {
        $filePath = $this->getPath() . md5($key) . '.cache';
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    /**
     * Removes all cached items.
     * 
     */
    public function flush() {
        $files = glob($this->cacheDir . '*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
    }
    /**
     * Checks if an item exist in the cache.
     * @param string $key The value of item key.
     * 
     * @return bool Returns true if given
     * key exist in the cache and not yet expired.
     */
    public function has(string $key): bool {
        return $this->read($key) !== null;
    }
    /**
     * Reads and returns the data stored in cache item given its key.
     * 
     * @param string $key The key of the item.
     * 
     * @return mixed|null If cache item is not expired, its data is returned. Other than
     * that, null is returned.
     */
    public function read(string $key) {
        $item = $this->readItem($key);
        
        if ($item !== null) {
            return $item->getDataDecrypted();
        }
        
        return null;
    }
    /**
     * Returns a string that represents the path to the folder which is used to
     * create cache files.
     * 
     * @return string A string that represents the path to the folder which is used to
     * create cache files.
     */
    public function getPath() : string {
        return $this->cacheDir;
    }
    /**
     * Sets the path to the folder which is used to create cache files.
     * 
     * @param string $path
     */
    public function setPath(string $path) {
        $this->cacheDir = $path;
    }
    /**
     * Reads cache item as an object given its key. 
     * 
     * @param string $key The unique identifier of the item.
     * 
     * @return Item|null If cache item exist and is not expired, 
     * an object of type 'Item' is returned. Other than
     * that, null is returned.
     */
    public function readItem(string $key) {
        $this->initData($key);
        $now = time();
        if ($now > $this->data['expires']) {
            $this->delete($key);
            return null;
        }
        $item = new Item($key, $this->data['data'], $this->data['ttl'], defined('CACHE_SECRET') ? CACHE_SECRET : '');
        $item->setCreatedAt($this->data['created_at']);
        
        return $item;
    }
}
