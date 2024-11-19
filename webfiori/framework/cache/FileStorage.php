<?php



namespace webfiori\framework\cache;

/**
 */
class FileStorage implements Storage {
    private $cacheDir;
    private $data;
    public function __construct() {
        $default = APP_PATH.DS.'sto'.DS.'cache'.DS;
        $path = defined('CACHE_PATH') ? CACHE_PATH : $default;
        $this->setPath($path);
    }
    //put your code here
    public function cache(Item $item) {
        $filePath = $this->getPath() . md5($item->getKey()) . '.cache';
        $encryptedData = $item->getDataEncrypted();
        
        if (!is_dir($this->cacheDir)) {
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
    public function delete(string $key) {
        $filePath = $this->getPath() . md5($key) . '.cache';
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function flush() {
        $files = glob($this->cacheDir . '*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
    }

    public function has(string $key): bool {
        return $this->read($key) !== null;
    }

    public function read(string $key) {
        $item = $this->readItem($key);
        
        if ($item !== null) {
            return $item->getDataDecrypted();
        }
        
        return null;
    }
    public function getPath() : string {
        return $this->cacheDir;
    }
    public function setPath(string $path) {
        $this->cacheDir = $path;
    }

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
