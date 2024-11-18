<?php


namespace webfiori\framework\cache;

/**
 */
class Cache {
    /**
     * 
     * @var AbstractCacheStore
     */
    private $driver;
    private static $inst;
    private static function getInst() : Cache {
        if (self::$inst === null) {
            self::$inst = new Cache();
            self::setDriver(FileCacheStore::class);
        }
        return self::$inst;
    }
    public static function setDriver(string $clazz) {
        if (!class_exists($clazz)) {
            return false;
        }
        $class = new $clazz();
        
        if (($clazz instanceof AbstractCacheStore)) {
            return false;
        }
        self::getInst()->driver = $class;
    }
    public static function getDriver() : AbstractCacheStore {
        return self::getInst()->driver;
    }
    public static function delete(string $key) {
        $item = self::getDriver();
        $item->setKey($key);
        $item->delete();
    }
    public static function has(string $key) : bool {
        $item = self::getDriver();
        $item->setKey($key);
        return $item->isExist();
    }
    public static function set(string $key, $data, int $ttl = 60, bool $override = false) : bool {
        if (!self::has($key) || $override === true) {
            $item = self::getDriver();
            $item->setKey($key);
            $item->setSecret('ok');
            $item->setData($data);
            $item->setTTL($ttl);
            $item->delete();
            $item->cache();
        }
        return false;
    }
    /**
     * 
     * @param string $key
     * @return AbstractCacheStore
     */
    public static function getItem(string $key) {
        if (self::has($key)) {
            self::get($key);
            return self::getDriver();
        }
    }
    public static function setTTL(string $key, $ttl) {
        $item = self::getItem($key);
        if ($item === null) {
            return false;
        }
        $item->setTTL($ttl);
        $item->cache();
        return true;
    }
    public static function get(string $key, callable $generator = null, int $ttl = 60, array $params = []) {
        $item = self::getDriver();
        $item->setKey($key);
        $item->setSecret('ok');
        $data = $item->read();
        
        if ($data !== null && $data !== false) {
            return $data;
        }
        if (!is_callable($generator)) {
            return null;
        }
        $newData = call_user_func_array($generator, $params);
        $item->setData($newData);
        $item->setTTL($ttl);
        
        $item->cache();
        return $newData;
    }
}
