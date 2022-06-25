<?php
namespace webfiori\framework\test\cli;
use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\Runner;

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
    /**
     * @test
     */
    public function testAddArg04() {
        $command = new TestCommand('new');
        $this->assertTrue($command->addArg('default-options', [
            'optional' => true
        ]));
        $this->assertFalse($command->addArg('default-options'));
    }
    /**
     * @test
     */
    public function testClear00() {
        Runner::setInput([]);
        $command = new TestCommand('hello', [
            'name' => [
                
            ]
        ]);
        Runner::runCommand($command, [
            'name' => 'Ibrahim'
        ]);
        $command->clearConsole();
        $this->assertEquals([
            "Hello Ibrahim!\n",
            "Ok\n",
            "\ec"
        ], Runner::getOutput());
    }
    /**
     * @test
     */
    public function testClear01() {
        Runner::setInput([]);
        $command = new TestCommand('hello', [
            'name' => [
                
            ]
        ]);
        Runner::runCommand($command, [
            'name' => 'Ibrahim'
        ]);
        $command->clearLine();
        $this->assertEquals([
            "Hello Ibrahim!\n",
            "Ok\n",
            "\e[2K\r"
        ], Runner::getOutput());
    }
    /**
     * @test
     */
    public function testClear02() {
        Runner::setInput([]);
        $command = new TestCommand('hello', [
            'name' => [
                
            ]
        ]);
        Runner::runCommand($command, [
            'name' => 'Ibrahim'
        ]);
        $command->clear(1);
        $this->assertEquals([
            "Hello Ibrahim!\n",
            "Ok\n",
            "\e[1D \e[1D\e[1C"
        ], Runner::getOutput());
    }
    /**
     * @test
     */
    public function testClear03() {
        Runner::setInput([]);
        $command = new TestCommand('hello', [
            'name' => [
                
            ]
        ]);
        Runner::runCommand($command, [
            'name' => 'Ibrahim'
        ]);
        $command->clear(2);
        $this->assertEquals([
            "Hello Ibrahim!\n",
            "Ok\n",
            "\e[1D \e[1D\e[1D \e[1D\e[2C"
        ], Runner::getOutput());
    }
    /**
     * @test
     */
    public function testClear05() {
        Runner::setInput([]);
        $command = new TestCommand('hello', [
            'name' => [
                
            ]
        ]);
        Runner::runCommand($command, [
            'name' => 'Ibrahim'
        ]);
        $command->clear(1, false);
        $this->assertEquals([
            "Hello Ibrahim!\n",
            "Ok\n",
            "\e[1C \e[2D"
        ], Runner::getOutput());
    }
    /**
     * @test
     */
    public function testClear06() {
        Runner::setInput([]);
        $command = new TestCommand('hello', [
            'name' => [
                
            ]
        ]);
        Runner::runCommand($command, [
            'name' => 'Ibrahim'
        ]);
        $command->clear(2, false);
        $this->assertEquals([
            "Hello Ibrahim!\n",
            "Ok\n",
            "\e[1C  \e[3D"
        ], Runner::getOutput());
    }
    /**
     * @test
     */
    public function testMove00() {
        Runner::setInput([]);
        $command = new TestCommand('hello', [
            'name' => [
                
            ]
        ]);
        Runner::runCommand($command, [
            'name' => 'Ibrahim'
        ]);
        $command->moveCursorDown(3);
        $command->moveCursorDown(6);
        $command->moveCursorLeft(88);
        $command->moveCursorRight(4);
        $this->assertEquals([
            "Hello Ibrahim!\n",
            "Ok\n",
            "\e[3B\e[6B\e[88D\e[4C"
        ], Runner::getOutput());
    }
    /**
     * @test
     */
    public function testMove01() {
        Runner::setInput([]);
        $command = new TestCommand('hello', [
            'name' => [
                
            ]
        ]);
        Runner::runCommand($command, [
            'name' => 'Ibrahim'
        ]);
        $command->moveCursorDown(3);
        $command->moveCursorDown(6);
        $command->moveCursorLeft(88);
        $command->moveCursorRight(4);
        $this->assertEquals([
            "Hello Ibrahim!\n",
            "Ok\n",
            "\e[3B\e[6B\e[88D\e[4C"
        ], Runner::getOutput());
    }
    /**
     * @test
     */
    public function testPrintList00() {
        Runner::setInput([]);
        $command = new TestCommand('hello');
        Runner::runCommand($command);
        $command->printList([
            'one',
            'two',
            'three'
        ]);
        $this->assertEquals([
            "Hello !\n",
            "Ok\n",
            "- one\n",
            "- two\n",
            "- three\n"
        ], Runner::getOutput());
    }
    /**
     * @test
     */
    public function testPrintList01() {
        Runner::setInput([]);
        $command = new TestCommand('hello');
        Runner::runCommand($command, [
            '--ansi'
        ]);
        $command->printList([
            'one',
            'two',
            'three'
        ]);
        $this->assertEquals([
            "\e[31mHello !\e[0m\n",
            "Ok\n",
            "\e[32m- \e[0mone\n",
            "\e[32m- \e[0mtwo\n",
            "\e[32m- \e[0mthree\n"
        ], Runner::getOutput());
    }
}
