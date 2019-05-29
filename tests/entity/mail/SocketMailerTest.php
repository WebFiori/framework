<?php
namespace webfiori\tests\entity\mail;
use PHPUnit\Framework\TestCase;
use webfiori\entity\mail\SMTPAccount;
use webfiori\entity\mail\SocketMailer;
/**
 * A test class for testing the class 'webfiori\entity\mail\SocketMailer'.
 *
 * @author Ibrahim
 */
class SocketMailerTest extends TestCase{
    /**
     * @test
     */
    public function testConstructor00() {
        $sm = new SocketMailer();
        $this->assertEquals('',$sm->getLastLogMessage());
        $this->assertSame(0,$sm->getLastResponseCode());
        $this->assertSame(0,$sm->getPriority());
        $this->assertSame(5,$sm->getTimeout());
    }
    /**
     * @test
     */
    public function testSetPriority00() {
        $sm = new SocketMailer();
        $sm->setPriority(-2);
        $this->assertSame(-1,$sm->getPriority());
        $sm->setPriority(100);
        $this->assertSame(1,$sm->getPriority());
        $sm->setPriority("hello");
        $this->assertSame(0,$sm->getPriority());
        $sm->setPriority("-26544");
        $this->assertSame(-1,$sm->getPriority());
        $sm->setPriority("26544");
        $this->assertSame(1,$sm->getPriority());
        $sm->setPriority(0);
        $this->assertSame(0,$sm->getPriority());
    }
    /**
     * @test
     */
    public function testAddReciver00() {
        $sm = new SocketMailer();
        $this->assertFalse($sm->addReceiver('', ''));
        $this->assertFalse($sm->addReceiver('Hello', ''));
        $this->assertFalse($sm->addReceiver('', 'hello@web.com'));
    }
    /**
     * @test
     */
    public function testAddReciver01() {
        $sm = new SocketMailer();
        $this->assertTrue($sm->addReceiver('  <Hello  ', '  hello@>hello.com'));
        $this->assertEquals('Hello <hello@hello.com>',$sm->getReceiversStr());
        $this->assertEquals('Hello',$sm->getReceivers()['hello@hello.com']);
        $this->assertTrue($sm->addReceiver('  <Hello2  ', '  hello@>hello.com'));
        $this->assertEquals('Hello2 <hello@hello.com>',$sm->getReceiversStr());
        $this->assertTrue($sm->addReceiver('Hel>lo-9  ', '  hello-9@>hello.com'));
        $this->assertEquals('Hello2 <hello@hello.com>,Hello-9 <hello-9@hello.com>',$sm->getReceiversStr());
    }
    /**
     * @test
     */
    public function testAddReciver02() {
        $sm = new SocketMailer();
        $this->assertTrue($sm->addReceiver('  <Hello  ', '  hello@>hello.com',true));
        $this->assertEquals('Hello <hello@hello.com>',$sm->getCCStr());
        $this->assertEquals('Hello',$sm->getCC()['hello@hello.com']);
        $this->assertTrue($sm->addReceiver('  <Hello2  ', '  hello@>hello.com',true));
        $this->assertEquals('Hello2 <hello@hello.com>',$sm->getCCStr());
        $this->assertTrue($sm->addReceiver('Hel>lo-9  ', '  hello-9@>hello.com',true));
        $this->assertEquals('Hello2 <hello@hello.com>,Hello-9 <hello-9@hello.com>',$sm->getCCStr());
    }
    /**
     * @test
     */
    public function testAddReciver03() {
        $sm = new SocketMailer();
        $this->assertTrue($sm->addReceiver('  <Hello  ', '  hello@>hello.com',true,true));
        $this->assertEquals('Hello <hello@hello.com>',$sm->getBCCStr());
        $this->assertEquals('Hello',$sm->getBCC()['hello@hello.com']);
        $this->assertTrue($sm->addReceiver('  <Hello2  ', '  hello@>hello.com',true,true));
        $this->assertEquals('Hello2 <hello@hello.com>',$sm->getBCCStr());
        $this->assertTrue($sm->addReceiver('Hel>lo-9  ', '  hello-9@>hello.com',true,true));
        $this->assertEquals('Hello2 <hello@hello.com>,Hello-9 <hello-9@hello.com>',$sm->getBCCStr());
    }
}
