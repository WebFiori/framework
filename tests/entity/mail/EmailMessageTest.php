<?php
namespace webfiori\tests\entity\mail;
use PHPUnit\Framework\TestCase;
use webfiori\entity\mail\EmailMessage;
/**
 * A test class for testing the class 'webfiori\entity\mail\EmailMessage'.
 *
 * @author Ibrahim
 */
class EmailMessageTest extends TestCase{
    /**
     * @test
     */
    public function test00() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No SMTP account was found which has the name "not exist".');
        $message = EmailMessage::createInstance('not exist');
    }
}
