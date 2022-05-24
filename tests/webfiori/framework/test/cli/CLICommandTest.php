<?php
namespace webfiori\framework\test\cli;
use PHPUnit\Framework\TestCase;

class CLICommandTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $command = new TestCommand('new-command');
        $this->assertEquals($command->getName(), 'new-command');
        $this->assertEquals('<NO DESCRIPTION>', $command->getDescription());
        $this->assertEquals(0, count($command->getArgs()));
    }
    /**
     * @test
     */
    public function test01() {
        $command = new TestCommand('new-command');
        $command->println('%30s', 'ok');
        $this->assertTrue(true);
    }
    /**
     * @test
     */
    public function test03() {
        $command = new TestCommand('with space');
        $this->assertEquals('new-command', $command->getName());
        $this->assertEquals('<NO DESCRIPTION>', $command->getDescription());
    }
    /**
     * @test
     */
    public function testAddArg00() {
        $command = new TestCommand('new-command');
        $this->assertFalse($command->addArg(''));
        $this->assertFalse($command->addArg('with space'));
        $this->assertFalse($command->addArg('       '));
        $this->assertFalse($command->addArg('invalid name'));
        $this->assertTrue($command->addArg('valid'));
        $this->assertTrue($command->addArg('--valid-name'));
        $this->assertTrue($command->addArg('0invalid'));
        $this->assertTrue($command->addArg('valid-1'));
    }
    /**
     * @test
     */
    public function testAddArg01() {
        $command = new TestCommand('new-command');
        $this->assertTrue($command->addArg('default-options'));
        $argDetails = $command->getArg('default-options');
        $this->assertEquals('<NO DESCRIPTION>', $argDetails->getDescription());
        $this->assertFalse($argDetails->isOptional());
        $this->assertEquals([], $argDetails->getAllowedValues());
    }
    /**
     * @test
     */
    public function testAddArg02() {
        $command = new TestCommand('new-command');
        $this->assertTrue($command->addArg('default-options', [
            'optional' => true
        ]));
        $argDetails = $command->getArg('default-options');
        $this->assertEquals('<NO DESCRIPTION>', $argDetails->getDescription());
        $this->assertTrue($argDetails->isOptional());
        $this->assertEquals([], $argDetails->getAllowedValues());
    }
    /**
     * @test
     */
    public function testAddArg03() {
        $command = new TestCommand('new');
        $this->assertTrue($command->addArg('default-options', [
            'optional' => true
        ]));
        $argDetails = $command->getArg('default-options');
        $this->assertEquals('<NO DESCRIPTION>', $argDetails->getDescription());
        $this->assertTrue($argDetails->isOptional());
        $this->assertEquals([], $argDetails->getAllowedValues());
    }
}
