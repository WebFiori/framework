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
 * An interface that has the method which must be implemented by any cache engine.
 */
interface Storage {
    /**
     * Store an item into the cache.
     *
     * This method must be implemented in a way that it keeps all needed information
     * about cached item. Primarily, it must store the following:
     * <ul>
     * <li>key</li>
     * <li>data</li>
     * <li>time to live</li>
     * <li>creation time</li>
     * </ul>
     *
     * @param Item $item An item that will be added to the cache.
     */
    public function cache(Item $item);
    /**
     * Removes an item from the cache.
     *
     * @param string $key The key of the item.
     */
    public function delete(string $key);
    /**
     * Removes all cached items.
     *
     * This method must be implemented in a way that it removes all cache items
     * regardless of expiry time.
     */
    public function flush();
    /**
     * Checks if an item exist in the cache.
     *
     * This method must be implemented in a way that it returns true if given
     * key exist in the cache and not yet expired.
     *
     * @param string $key The value of item key.
     *
     * @return bool Returns true if given
     * key exist in the cache and not yet expired.
     */
    public function has(string $key) : bool;
    /**
     * Reads and returns the data stored in cache item given its key.
     *
     * This method should be implemented in a way that it reads cache item
     * as an object of type 'Item'. Then it should do a check if the cached
     * item is expired or not. If not expired, its data is returned. Other than
     * that, null should be returned.
     *
     * @param string $key The key of the item.
     *
     * @return mixed|null If cache item is not expired, its data is returned. Other than
     * that, null is returned.
     */
    public function read(string $key);
    /**
     * Reads cache item as an object given its key.
     *
     * @param string $key The unique identifier of the item.
     *
     * @return Item|null If cache item exist and is not expired,
     * an object of type 'Item' should be returned. Other than
     * that, null is returned.
     */
    public function readItem(string $key);
}
