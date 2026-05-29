<?php

namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Session\Session;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\Session\SessionStatus;

/**
 * Tests for session encryption/decryption.
 *
 * Note: SESSION_KEY is defined once in the test bootstrap. Tests that verify
 * behavior without a key or with a different key use process isolation.
 */
class SessionEncryptionTest extends TestCase {
    protected function setUp(): void {
        SessionsManager::reset();

        if (!defined('SESSION_KEY')) {
            define('SESSION_KEY', 'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2');
        }
    }

    /**
     * @test
     */
    public function testSerializeWithKey() {
        $session = new Session(['name' => 'enc-test']);
        $session->start();
        $session->set('secret', 'my-value');

        $serialized = $session->serialize();

        $this->assertStringStartsWith('ENC:', $serialized);
        // Ensure plaintext data is not visible in the output
        $this->assertStringNotContainsString('my-value', $serialized);
        $this->assertStringNotContainsString('secret', $serialized);
    }
    /**
     * @test
     */
    public function testDeserializeEncryptedRoundTrip() {
        $session = new Session(['name' => 'roundtrip']);
        $session->start();
        $session->set('user', 'ibrahim');
        $session->set('role', 'admin');

        $serialized = $session->serialize();

        $session2 = new Session(['name' => 'x', 'session-id' => $session->getId()]);
        $result = $session2->deserialize($serialized);

        $this->assertTrue($result);
        $this->assertEquals(SessionStatus::RESUMED, $session2->getStatus());
    }
    /**
     * @test
     */
    public function testTamperedCiphertextFails() {
        $session = new Session(['name' => 'tamper-test']);
        $session->start();
        $session->set('important', 'data');

        $serialized = $session->serialize();

        // Flip a byte in the encoded payload
        $encoded = substr($serialized, 4); // remove 'ENC:'
        $decoded = base64_decode($encoded);
        // Tamper with ciphertext area (after iv+tag = 28 bytes)
        if (strlen($decoded) > 30) {
            $decoded[30] = chr(ord($decoded[30]) ^ 0xFF);
        }
        $tampered = 'ENC:'.base64_encode($decoded);

        $session2 = new Session(['name' => 'x', 'session-id' => $session->getId()]);
        $result = $session2->deserialize($tampered);

        $this->assertFalse($result);
    }
    /**
     * @test
     */
    public function testDifferentSessionIdFails() {
        $session = new Session(['name' => 'id-test']);
        $session->start();
        $session->set('data', 'value');

        $serialized = $session->serialize();

        // Try to deserialize with a different session ID (different derived key)
        $session2 = new Session(['name' => 'x', 'session-id' => 'completely-different-id']);
        $result = $session2->deserialize($serialized);

        $this->assertFalse($result);
    }
    /**
     * @test
     */
    public function testLegacyUnencryptedFormatMigration() {
        // Simulate legacy unencrypted format: len_base64data
        $session = new Session(['name' => 'legacy-test']);
        $session->start();
        $session->set('old-data', 'from-v1');

        $plaintext = base64_encode(serialize($session));
        $legacy = strlen($plaintext).'_'.$plaintext;

        $session2 = new Session(['name' => 'x', 'session-id' => $session->getId()]);
        $result = $session2->deserialize($legacy);

        $this->assertTrue($result);
        $this->assertEquals(SessionStatus::RESUMED, $session2->getStatus());
    }
    /**
     * @test
     */
    public function testRandomIVProducesDifferentCiphertext() {
        $session = new Session(['name' => 'iv-test']);
        $session->start();
        $session->set('data', 'same-value');

        $serialized1 = $session->serialize();
        $serialized2 = $session->serialize();

        // Same session, same data, but different random IV each time
        $this->assertNotEquals($serialized1, $serialized2);

        // Both should still decrypt correctly
        $s1 = new Session(['name' => 'x', 'session-id' => $session->getId()]);
        $this->assertTrue($s1->deserialize($serialized1));

        $s2 = new Session(['name' => 'x', 'session-id' => $session->getId()]);
        $this->assertTrue($s2->deserialize($serialized2));
    }
    /**
     * @test
     */
    public function testTruncatedCiphertextFails() {
        $session = new Session(['name' => 'trunc-test']);
        $session->start();

        $serialized = $session->serialize();
        // Truncate the payload
        $truncated = 'ENC:'.base64_encode('short');

        $session2 = new Session(['name' => 'x', 'session-id' => $session->getId()]);
        $result = $session2->deserialize($truncated);

        $this->assertFalse($result);
    }
    /**
     * @test
     */
    public function testInvalidBase64Fails() {
        $session = new Session(['name' => 'x', 'session-id' => 'test-id']);
        $result = $session->deserialize('ENC:not-valid-base64!!!');

        $this->assertFalse($result);
    }
    /**
     * @test
     */
    public function testRawFormatDeserializes() {
        // Manually create RAW format
        $session = new Session(['name' => 'raw-source']);
        $session->start();
        $session->set('hello', 'world');

        $plaintext = base64_encode(serialize($session));
        $raw = 'RAW:'.$plaintext;

        $session2 = new Session(['name' => 'x', 'session-id' => $session->getId()]);
        $result = $session2->deserialize($raw);

        $this->assertTrue($result);
        $this->assertEquals(SessionStatus::RESUMED, $session2->getStatus());
    }
    /**
     * @test
     */
    public function testCorruptedRawFormatFails() {
        $session = new Session(['name' => 'x', 'session-id' => 'test-id']);
        $result = $session->deserialize('RAW:not-valid-base64-serialized-data');

        $this->assertFalse($result);
    }
}
