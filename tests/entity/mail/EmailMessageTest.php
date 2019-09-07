<?php
namespace webfiori\tests\entity\mail;
use PHPUnit\Framework\TestCase;
use webfiori\entity\mail\EmailMessage;
use webfiori\entity\mail\SMTPAccount;
use webfiori\conf\MailConfig;
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
    /**
     * @test
     */
    public function test01() {
        $smtp = new SMTPAccount();
        //$smtp->setServerAddress('mail.invalid.com');
        MailConfig::get()->addSMTPAccount('smtp-acc-00', $smtp);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The account "smtp-acc-00" has invalid host or port number. Port: 465, Host: .');
        $message = EmailMessage::createInstance('smtp-acc-00');
    }
    /**
     * @test
     */
    public function test02() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The account "smtp-acc-00" has invalid host or port number. Port: 255, Host: mail.programmingacademia.com.');
        $smtp = new SMTPAccount();
        $smtp->setPassword('iz1Iimu#z');
        $smtp->setAddress('test@programmingacademia.com');
        $smtp->setUsername('test@programmingacademia.com');
        $smtp->setServerAddress('mail.programmingacademia.com ');
        $smtp->setPort(255);
        MailConfig::get()->addSMTPAccount('smtp-acc-00', $smtp);
        $message = EmailMessage::createInstance('smtp-acc-00');
    }
    /**
     * @test
     */
    public function test03() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The account "smtp-acc-00" has invalid host or port number. Port: 25, Host: mail.programmingacademia.com.');
        $smtp = new SMTPAccount();
        $smtp->setPassword('izimu#z');
        $smtp->setAddress('test@programmingacademia.com');
        $smtp->setUsername('test@programmingacademia.com');
        $smtp->setServerAddress('mail.programmingacademia.com ');
        $smtp->setPort(25);
        MailConfig::get()->addSMTPAccount('smtp-acc-00', $smtp);
        $message = EmailMessage::createInstance('smtp-acc-00');
        $this->assertTrue($message instanceof EmailMessage);
    }
    /**
     * @test
     */
//    public function test04() {
//        $smtp = new SMTPAccount();
//        $smtp->setPassword('iz2)X1Iimu#z');
//        $smtp->setAddress('test@programmingacademia.com');
//        $smtp->setUsername('test@programmingacademia.com');
//        $smtp->setServerAddress('mail.programmingacademia.com  ');
//        $smtp->setPort(25);
//        MailConfig::get()->addSMTPAccount('smtp-acc-00', $smtp);
//        $message = EmailMessage::createInstance('smtp-acc-00');
//        $this->assertTrue($message instanceof EmailMessage);
//    }
}
