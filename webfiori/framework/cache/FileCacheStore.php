<?php

namespace webfiori\framework\cache;

/**
 */
class FileCacheStore extends AbstractCacheStore {
    private $cacheDir;
    
    public function __construct(string $key = 'item', string $data = '', int $ttl = 60, string $secretKey = '') {
        parent::__construct($key, $data, $ttl, $secretKey);
        $this->setPath(APP_PATH.DS.'sto'.DS.'cache'.DS);
    }
    public function isExist() {
        $filePath = $this->getPath() . md5($this->getKey()) . '.cache';
        return file_exists($filePath);
    }
    public function cache() {
        $filePath = $this->getPath() . md5($this->getKey()) . '.cache';
        $encryptedData = $this->getDataEncrypted();
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->getPath(), 0755, true);
        }
        file_put_contents($filePath, serialize(['data' => $encryptedData, 'created_at' => time(), 'ttl' => $this->getTTL(), 'expires' => $this->getExpiryTime()]));
    }
    public function read() {
        $filePath = $this->cacheDir . md5($this->getKey()) . '.cache';

        if (!file_exists($filePath)) {
            return null;
        }

        $cacheData = unserialize(file_get_contents($filePath));


        if (time() > $cacheData['expires']) {
            unlink($filePath);
            return null;
        }
        
        $this->setData($cacheData['data']);
        $this->setTTL($cacheData['ttl']);
        $this->setCreatedAt($cacheData['created_at']);
        return $this->getDataDecrypted();
    }
    public function delete() {
        $filePath = $this->getPath() . md5($this->getKey()) . '.cache';
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    public function getPath() : string {
        return $this->cacheDir;
    }
    public function setPath(string $path) {
        $this->cacheDir = $path;
    }
}
