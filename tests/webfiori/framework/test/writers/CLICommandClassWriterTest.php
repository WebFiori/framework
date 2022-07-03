<?php
namespace webfiori\framework\test\writers;

use PHPUnit\Framework\TestCase;
use webfiori\framework\writers\CLICommandClassWriter;;

class CLICommandClassWriterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $writer = new CLICommandClassWriter();
        $this->assertEquals('NewCommand', $writer->getName());
        $this->assertEquals(ROOT_DIR.DS.APP_DIR_NAME.DS.'commands'.DS.'NewCommand.php', $writer->getAbsolutePath());
        $this->assertEquals('app\\commands', $writer->getNamespace());
        $this->assertEquals('new-command', $writer->getCommandName());
        $this->assertEquals('', $writer->getDescription());
        $this->assertEquals([], $writer->getArgs());
        $this->assertEquals([
            'webfiori\cli\CLICommand'
        ], $writer->getUseStatements());
        $writer->writeClass();
        $this->assertTrue(class_exists($writer->getNamespace().'\\'.$writer->getName()));
        $writer->removeClass();
    }
    /**
     * @test
     */
    public function test01() {
        $writer = new CLICommandClassWriter();
        $this->assertFalse($writer->setCommandName('invalid name'));
        $this->assertFalse($writer->setCommandName('   '));
        $this->assertTrue($writer->setCommandName('Lets-Do-It'));
        $this->assertEquals('Lets-Do-It', $writer->getCommandName());
        $this->assertFalse($writer->setClassName('Invalid Name'));
        $this->assertFalse($writer->setClassName('   '));
        $this->assertTrue($writer->setClassName('DoItCommand'));
        $this->assertEquals('DoItCommand', $writer->getName());
        $this->assertEquals(ROOT_DIR.DS.APP_DIR_NAME.DS.'commands'.DS.'DoItCommand.php', $writer->getAbsolutePath());
        $this->assertEquals('app\\commands', $writer->getNamespace());
        $this->assertEquals('Lets-Do-It', $writer->getCommandName());
        $this->assertEquals('', $writer->getDescription());
        $this->assertEquals([], $writer->getArgs());
        $this->assertEquals([
            'webfiori\cli\CLICommand'
        ], $writer->getUseStatements());
        $writer->writeClass();
        $this->assertTrue(class_exists($writer->getNamespace().'\\'.$writer->getName()));
        $writer->removeClass();
    }
}
