<?php

namespace webfiori\framework\test\cli;
use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\Runner;
use webfiori\framework\cli\ArrayInputStream;
use \webfiori\framework\cli\ArrayOutputStream;
/**
 * Description of CommandTest
 *
 * @author Ibrahim
 */
class CommandTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        Runner::setInput([]);
        $command = new TestCommand('hello', [
            'name' => []
        ]);
        $this->assertEquals(0, Runner::runCommand($command, [
            'name' => 'Ibrahim'
        ]));
        $this->assertEquals([
            "Hello Ibrahim!\n",
            "Ok\n",
        ], Runner::getOutput());
    }
    /**
     * @test
     */
    public function test01() {
        Runner::setInput([]);
        $command = new TestCommand('hello', [
            'name' => []
        ]);
        $this->assertEquals(0, Runner::runCommand($command, [
            'name' => 'Hassan Hussain'
        ]));
        $this->assertEquals([
            "Hello Hassan Hussain!\n",
            "Ok\n",
        ], Runner::getOutput());
    }
}
