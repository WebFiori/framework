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
 * A class which represent a cache item.
 */
class Item {
    private $createdAt;
    private $data;
    private $key;
    private $secretKey;
    private $timeToLive;
    /**
     * Creates new instance of the class.
     *
     * @param string $key The unique key which is used to identify cache item.
     * Its used in storing, update and deletion of cache item.
     *
     * @param mixed $data The data that will be cached.
     *
     * @param int $ttl The time at which the item will be kept in the cache in seconds.
     *
     * @param string $secretKey A secret key which is used during encryption
     * and decryption phases of cache storage and retrieval.
     */
    public function __construct(string $key = 'item', $data = '', int $ttl = 60, string $secretKey = '') {
        $this->setKey($key);
        $this->setTTL($ttl);
        $this->setData($data);
        $this->setSecret($secretKey);
        $this->setCreatedAt(time());
    }
    /**
     * Generates a cryptographic secure key.
     *
     * The generated key can be used to encrypt sensitive data.
     *
     * @return string
     */
    public static function generateKey() : string {
        return bin2hex(random_bytes(32));
    }
    /**
     * Returns the time at which the item was created at.
     *
     * The value returned by the method is Unix timestamp.
     *
     * @return int An integer that represents Unix timestamp in seconds.
     */
    public function getCreatedAt() : int {
        return $this->createdAt;
    }
    /**
     * Returns the data of cache item.
     *
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }
    /**
     * Returns cache item data after performing decryption on it.
     *
     * @return mixed
     */
    public function getDataDecrypted() {
        return unserialize($this->decrypt($this->getData()));
    }
    /**
     * Returns cache data after performing encryption on it.
     *
     * Note that the raw data must be
     *
     * @return string
     */
    public function getDataEncrypted() : string {
        return $this->encrypt(serialize($this->getData()));
    }
    /**
     * Returns the time at which cache item will expire as Unix timestamp.
     *
     * The method will add the time at which the item was created at to TTL and
     * return the value.
     *
     * @return int The time at which cache item will expire as Unix timestamp.
     */
    public function getExpiryTime() : int {
        return $this->getCreatedAt() + $this->getTTL();
    }
    /**
     * Gets the key of the item.
     *
     * The key acts as a unique identifier for cache items.
     *
     * @return string A string that represents the key.
     */
    public function getKey() : string {
        return $this->key;
    }
    /**
     * Returns the value of the key which is used in encrypting cache data.
     *
     * @return string The value of the key which is used in encrypting cache data.
     * Default return value is empty string.
     */
    public function getSecret() : string {
        return $this->secretKey;
    }
    /**
     * Returns the duration at which the item will be kept in cache in seconds.
     *
     * @return int The duration at which the item will be kept in cache in seconds.
     */
    public function getTTL() : int {
        return $this->timeToLive;
    }
    /**
     * Sets the time at which the item was created at.
     *
     * @param int $time An integer that represents Unix timestamp in seconds.
     * Must be a positive value.
     */
    public function setCreatedAt(int $time) {
        if ($time > 0) {
            $this->createdAt = $time;
        }
    }
    /**
     * Sets the data of the item.
     *
     * This represents the data that will be stored or retrieved.
     *
     * @param mixed $data
     */
    public function setData($data) {
        $this->data = $data;
    }
    /**
     * Sets the key of the item.
     *
     * The key acts as a unique identifier for cache items.
     *
     * @param string $key A string that represents the key.
     */
    public function setKey(string $key) {
        $this->key = $key;
    }
    /**
     * Sets the value of the key which is used in encrypting cache data.
     *
     * @param string $secret A cryptographic key which is used to encrypt
     * cache data. To generate one, the method Item::generateKey() can be used.
     */
    public function setSecret(string $secret) {
        $this->secretKey = $secret;
    }
    /**
     * Sets the duration at which the item will be kept in cache in seconds.
     *
     * @param int $ttl Time-to-live of the item in cache.
     */
    public function setTTL(int $ttl) {
        if ($ttl >= 0) {
            $this->timeToLive = $ttl;
        }
    }


    private function decrypt($data) {
        // decode > extract iv > decrypt
        $decodedData = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($decodedData, 0, $ivLength);
        $encryptedData = substr($decodedData, $ivLength);
        $decrypted = openssl_decrypt($encryptedData, 'aes-256-cbc', $this->getSecret(), 0, $iv);

        return $decrypted;
    }
    private function encrypt($data) {
        // iv > encrypt > append iv  > encode
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $this->getSecret(), 0, $iv);
        $encoded = base64_encode($iv.$encryptedData);

        return $encoded;
    }
}
