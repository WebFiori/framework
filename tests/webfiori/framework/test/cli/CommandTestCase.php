<?php

namespace webfiori\framework\test\cli;
use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\CommandTest;
/**
 * Description of CommandTest
 *
 * @author Ibrahim
 */
class CommandTestCase extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $test = new CommandTest(TESTS_PATH.DIRECTORY_SEPARATOR.'input.txt', TESTS_PATH.DIRECTORY_SEPARATOR.'output.txt');
        $test->runCommand(new TestCommand('hello', [
            'name' => []
        ]), [
            'name' => 'Ibrahim'
        ]);
        $this->assertTrue($test->assertExitStatusEquals(0));
        $this->assertTrue($test->isOutputEquals([
            "Hello Ibrahim!",
            "Ok",
            ""
        ]));
    }
    /**
     * @test
     */
    public function test01() {
        $test = new CommandTest(TESTS_PATH.DIRECTORY_SEPARATOR.'input.txt', TESTS_PATH.DIRECTORY_SEPARATOR.'output.txt');
        $test->runCommand(new TestCommand('hello', [
            'name' => []
        ]), [
            'name' => 'Hassan Hussain'
        ]);
        $this->assertTrue($test->assertExitStatusEquals(0));
        $this->assertTrue($test->isOutputEquals([
            "Hello Hassan Hussain!",
            "Ok",
            ""
        ]));
    }
}
