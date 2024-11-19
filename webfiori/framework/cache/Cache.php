<?php


namespace webfiori\framework\cache;

/**
 */
class Cache {
    /**
     * 
     * @var string
     */
    private $driver;
    private static $inst;
    private static function getInst() : Cache {
        if (self::$inst === null) {
            self::$inst = new Cache();
            self::setDriver(new FileStorage());
        }
        return self::$inst;
    }
    public static function setDriver(Storage $driver) {
        self::getInst()->driver = $driver;
    }
    public static function getDriver() : Storage {
        return self::getInst()->driver;
    }
    public static function delete(string $key) {
        self::getDriver()->delete($key);
    }
    public static function has(string $key) : bool {
        return self::getDriver()->has($key);
    }
    public static function flush() {
        self::getDriver()->flush();
    }
    public static function set(string $key, $data, int $ttl = 60, bool $override = false) : bool {
        if (!self::has($key) || $override === true) {
            $item = new Item($key, $data, $ttl, defined('CACHE_SECRET') ? CACHE_SECRET : '');
            self::getDriver()->cache($item);
        }
        return false;
    }
    /**
     * 
     * @param string $key
     * @return Item|null
     */
    public static function getItem(string $key) {
        return self::getDriver()->readItem($key);
    }
    public static function setTTL(string $key, $ttl) {
        $item = self::getItem($key);
        if ($item === null) {
            return false;
        }
        $item->setTTL($ttl);
        self::getDriver()->cache($item);
        return true;
    }
    public static function get(string $key, callable $generator = null, int $ttl = 60, array $params = []) {
        $data = self::getDriver()->read($key);
        
        if ($data !== null && $data !== false) {
            return $data;
        }

        if (!is_callable($generator)) {
            return null;
        }
        $newData = call_user_func_array($generator, $params);
        $item = new Item($key, $newData, $ttl, defined('CACHE_SECRET') ? CACHE_SECRET : '');
        self::getDriver()->cache($item);
        return $newData;
    }
}
