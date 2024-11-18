<?php

namespace webfiori\framework\cache;

/**
 *
 */
abstract class AbstractCacheStore {
    private $timeToLive;
    private $data;
    private $key;
    private $secretKey;
    private $createdAt;
    public function __construct(string $key = 'item', $data = '', int $ttl = 60, string $secretKey = '') {
        $this->setKey($key);
        $this->setTTL($ttl);
        $this->setData($data);
        $this->setSecret($secretKey);
        $this->setCreatedAt(time());
    }
    public function getCreatedAt() : int {
        return $this->createdAt;
    }
    public function setCreatedAt(int $time) {
        $this->createdAt = $time;
    }
    public abstract function isExist();
    public abstract function cache();
    public abstract function delete();
    public abstract function read();
    public function getTTL() : int {
        return $this->timeToLive;
    }
    public function setTTL(int $ttl) {
        $this->timeToLive = $ttl;
    }
    public function setKey(string $key) {
        $this->key = $key;
    }
    public function getKey() {
        return $this->key;
    }
    public function setData($data) {
        $this->data = $data;
    }
    public function getData() {
        return $this->data;
    }
    public function getDataEncrypted() {
        return $this->encrypt($this->getData());
    }
    public function getDataDecrypted() {
        return $this->decrypt($this->getData());
    }
    public function setSecret(string $secret) {
        $this->secretKey = $secret;
    }
    public function getSecret() : string {
        return $this->secretKey;
    }
    public function getExpiryTime() : int {
        return $this->getCreatedAt() + $this->getTTL();
    }
    private function encrypt($data) {
        // iv > encrypt > append iv  > encode
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $this->getSecret(), 0, $iv);
        $encoded = base64_encode($iv . $encryptedData);
        return $encoded;
    }

    
    private function decrypt($data) {
        // decode > extract iv > decrypt d9xCt
        $decodedData = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($decodedData, 0, $ivLength);
        $encryptedData = substr($decodedData, $ivLength);
        $decrypted = openssl_decrypt($encryptedData, 'aes-256-cbc', $this->getSecret(), 0, $iv);
        return $decrypted;
    }
}
