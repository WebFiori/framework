<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace webfiori\tests\entity\mail;
use PHPUnit\Framework\TestCase;
use PHPMailer\PHPMailer;
/**
 * Description of TestSMTPServer
 *
 * @author Ibrahim
 */
class TestSMTPServer extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $server = new SMTPServer('smtp.gmail.com', 465);
        $this->assertEquals('smtp.gmail.com', $server->getHost());
        $this->assertEquals(465, $server->getPort());
        
        $this->assertTrue($server->connect());
    }
    /**
     * @test
     */
    public function test01() {
        $server = new SMTPServer('smtp.outlook.com', 587);
        $this->assertEquals('smtp.outlook.com', $server->getHost());
        $this->assertEquals(587, $server->getPort());
        
        $this->assertTrue($server->connect());
    }
}
