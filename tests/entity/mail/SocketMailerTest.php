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
}
