<?php
namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Session\InMemorySessionStorage;
use WebFiori\Framework\Session\Session;
use WebFiori\Framework\Session\SessionsManager;

/**
 * Tests for Session encryption-at-rest via encryptValue()/decryptValue().
 *
 * When SESSION_KEY is defined, Session encrypts every value before writing
 * to storage and decrypts transparently on read. Storage backends stay
 * storage-only — they never see or know about plaintext values.
 *
 * @since 3.1.0
 */
class SessionEncryptionTest extends TestCase {
    /**
     * @test
     * Session::get() transparently returns the original value regardless
     * of whether SESSION_KEY is set.
     */
    public function testGetReturnsDecryptedValue(): void {
        $session = new Session(['name' => 'enc-get-test']);
        $session->start();
        $session->set('product', ['id' => 1, 'name' => 'Widget', 'price' => 9.99]);
        $session->set('count', 42);
        $session->set('flag', true);

        $this->assertSame(['id' => 1, 'name' => 'Widget', 'price' => 9.99], $session->get('product'));
        $this->assertSame(42, $session->get('count'));
        $this->assertTrue($session->get('flag'));
    }

    /**
     * @test
     * Session::getVars() decrypts all values.
     */
    public function testGetVarsDecryptsAll(): void {
        $session = new Session(['name' => 'vars-test']);
        $session->start();
        $session->set('a', 'alpha');
        $session->set('b', 'beta');

        $vars = $session->getVars();

        $this->assertSame('alpha', $vars['a']);
        $this->assertSame('beta', $vars['b']);
    }

    /**
     * @test
     * Session::remove() works correctly regardless of encryption state.
     */
    public function testRemoveWorksWithEncryption(): void {
        $session = new Session(['name' => 'rm-test']);
        $session->start();
        $session->set('keep', 'yes');
        $session->set('drop', 'no');

        $session->remove('drop');

        $this->assertSame('yes', $session->get('keep'));
        $this->assertNull($session->get('drop'));
    }

    /**
     * @test
     * Values survive a close() + start() round-trip (second Session instance
     * reads and decrypts what the first one wrote).
     */
    public function testRoundTripAcrossSessionInstances(): void {
        $options = ['name' => 'rt-test'];

        $s1 = new Session($options);
        $s1->start();
        $sid = $s1->getId();
        $s1->set('cart', ['apple', 'orange']);
        $s1->close();

        // Second instance with same session ID.
        $s2 = new Session(array_merge($options, ['session-id' => $sid]));
        $s2->start();

        $this->assertSame(['apple', 'orange'], $s2->get('cart'));
    }

    /**
     * @test
     * When SESSION_KEY is defined, stored values must not be plaintext.
     */
    public function testValuesAreEncryptedAtRestWhenKeyDefined(): void {
        if (!defined('SESSION_KEY') || SESSION_KEY === '') {
            $this->markTestSkipped('SESSION_KEY not defined in this test environment.');
        }

        $session = new Session(['name' => 'enc-test']);
        $session->start();
        $session->set('credit_card', '4111-1111-1111-1111');
        $session->set('user_token', 'bearer-xyz');

        $sid = $session->getId();
        $rawAll = $this->getInnerStorage()->readAll($sid);

        foreach ($rawAll as $key => $entry) {
            if ($key === '_meta') {
                continue;
            }
            $this->assertStringStartsWith(
                'ENC:',
                (string) $entry['value'],
                "Key '$key' should be AES-encrypted at rest but was stored as plaintext."
            );
            $this->assertStringNotContainsString(
                '4111-1111-1111-1111',
                (string) $entry['value'],
                'Plaintext credit card number must not appear in storage.'
            );
        }
    }

    private function getInnerStorage(): InMemorySessionStorage {
        return SessionsManager::getStorage();
    }
    protected function setUp(): void {
        InMemorySessionStorage::reset();
        SessionsManager::setStorage(new InMemorySessionStorage());
    }

    protected function tearDown(): void {
        InMemorySessionStorage::reset();
    }
}
