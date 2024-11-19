<?php

namespace webfiori\framework\cache;

/**
 */
interface Storage {
    public function has(string $key) : bool;
    public function cache(Item $item);
    public function delete(string $key);
    public function read(string $key);
    public function readItem(string $key);
    public function flush();
}
