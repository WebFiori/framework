<?php
namespace webfiori\framework\test\mail;

use PHPUnit\Framework\TestCase;
use webfiori\framework\mail\SMTPAccount;
/**
 * A test class for testing the class 'webfiori\framework\mail\SMTPAccount'.
 *
 * @author Ibrahim
 */
class SMTPAccountTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $acc = new SMTPAccount();
        $this->assertSame(465,$acc->getPort());
        $this->assertEquals('',$acc->getAddress());
        $this->assertEquals('',$acc->getSenderName());
        $this->assertEquals('',$acc->getPassword());
        $this->assertEquals('',$acc->getServerAddress());
        $this->assertEquals('',$acc->getUsername());
    }
    /**
     * @test
     */
    public function test01() {
        $acc = new SMTPAccount([
            'user' => 'my-mail@example.com',
            'pass' => '123456',
            'port' => 25,
            'server-address' => 'mail.examplex.com',
            'sender-name' => 'Example Sender',
            'sender-address' => 'no-reply@example.com',
            'account-name' => 'no-reply'
        ]);
        $this->assertSame(25,$acc->getPort());
        $this->assertEquals('no-reply@example.com',$acc->getAddress());
        $this->assertEquals('Example Sender',$acc->getSenderName());
        $this->assertEquals('123456',$acc->getPassword());
        $this->assertEquals('mail.examplex.com',$acc->getServerAddress());
        $this->assertEquals('my-mail@example.com',$acc->getUsername());
        $this->assertEquals('no-reply',$acc->getAccountName());
    }
    /**
     * @test
     */
    public function testSetAddress() {
        $acc = new SMTPAccount();
        $acc->setAddress('ix@hhh.com');
        $this->assertEquals('ix@hhh.com',$acc->getAddress());
        $acc->setAddress('    hhgix@hhh.com    ');
        $this->assertEquals('hhgix@hhh.com',$acc->getAddress());
    }
    /**
     * @test
     */
    public function testSetPassword() {
        $acc = new SMTPAccount();
        $acc->setPassword(' 55664 $wwe ');
        $this->assertEquals(' 55664 $wwe ',$acc->getPassword());
    }
    /**
     * @test
     */
    public function testSetPort00() {
        $acc = new SMTPAccount();
        $acc->setPort('88');
        $this->assertEquals(88,$acc->getPort());
        $acc->setPort(0);
        $this->assertEquals(0,$acc->getPort());
        $acc->setPort(1);
        $this->assertEquals(1,$acc->getPort());
    }
    /**
     * @test
     */
    public function testSetServerAddress() {
        $acc = new SMTPAccount();
        $acc->setServerAddress('smtp.hhh.com');
        $this->assertEquals('smtp.hhh.com',$acc->getServerAddress());
        $acc->setAddress('    smtp.xhx.com    ');
        $this->assertEquals('smtp.xhx.com',$acc->getAddress());
    }
    /**
     * @test
     */
    public function testSetUsername() {
        $acc = new SMTPAccount();
        $acc->setUsername('webfiori@hello.com');
        $this->assertEquals('webfiori@hello.com',$acc->getUsername());
        $acc->setUsername('    webfiori@hello-00.com    ');
        $this->assertEquals('webfiori@hello-00.com',$acc->getUsername());
    }
}
