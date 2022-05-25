<?php
namespace webfiori\framework\test\cli;

use webfiori\framework\cli\OutputFormatter;
use PHPUnit\Framework\TestCase;
/**
 * Description of OutputFormatterTest
 *
 * @author Ibrahim
 */
class OutputFormatterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $this->assertEquals('Hello', OutputFormatter::formatOutput('Hello'));
    }
    /**
     * @test
     */
    public function test01() {
        $this->assertEquals("\e[31mHello\e[0m", OutputFormatter::formatOutput('Hello', [
            'color' => 'red'
        ]));
    }
    /**
     * @test
     */
    public function test02() {
        $this->assertEquals("\e[1mHello\e[0m", OutputFormatter::formatOutput('Hello', [
            'bold' => true
        ]));
    }
    /**
     * @test
     */
    public function test03() {
        $this->assertEquals("\e[4mHello\e[0m", OutputFormatter::formatOutput('Hello', [
            'underline' => true
        ]));
    }
    /**
     * @test
     */
    public function test04() {
        $this->assertEquals("\e[1;4mHello\e[0m", OutputFormatter::formatOutput('Hello', [
            'underline' => true,
            'bold' => true
        ]));
    }
    /**
     * @test
     */
    public function test05() {
        $this->assertEquals("\e[7mHello\e[0m", OutputFormatter::formatOutput('Hello', [
            'reverse' => true
        ]));
    }
    /**
     * @test
     */
    public function test06() {
        $this->assertEquals("\e[1;4;7mHello\e[0m", OutputFormatter::formatOutput('Hello', [
            'reverse' => true,
            'bold' => true,
            'underline' => true
        ]));
    }
    /**
     * @test
     */
    public function test07() {
        $this->assertEquals("\e[1;4;7;93mHello\e[0m", OutputFormatter::formatOutput('Hello', [
            'reverse' => true,
            'bold' => true,
            'underline' => true,
            'color' => 'light-yellow'
        ]));
    }
    /**
     * @test
     */
    public function test08() {
        $this->assertEquals("\e[1;4;7mHello\e[0m", OutputFormatter::formatOutput('Hello', [
            'reverse' => true,
            'bold' => true,
            'underline' => true,
            'color' => 'not supported'
        ]));
    }
    /**
     * @test
     */
    public function test09() {
        $this->assertEquals("\e[1;4;7;40mHello\e[0m", OutputFormatter::formatOutput('Hello', [
            'reverse' => true,
            'bold' => true,
            'underline' => true,
            'bg-color' => 'black'
        ]));
    }
    /**
     * @test
     */
    public function test10() {
        $this->assertEquals("\e[1;4;7mHello\e[0m", OutputFormatter::formatOutput('Hello', [
            'reverse' => true,
            'bold' => true,
            'underline' => true,
            'bg-color' => 'ggg'
        ]));
    }
    /**
     * @test
     */
    public function test11() {
        $this->assertEquals("\e[1;4;5;7;33;43mHello\e[0m", OutputFormatter::formatOutput('Hello', [
            'reverse' => true,
            'bold' => true,
            'underline' => true,
            'bg-color' => 'yellow',
            'color' => 'yellow',
            'blink' => true
        ]));
    }
    /**
     * @test
     */
    public function test12() {
        $_SERVER['NO_COLOR'] = 1;
        $this->assertEquals("\e[1;4;5;7mHello\e[0m", OutputFormatter::formatOutput('Hello', [
            'reverse' => true,
            'bold' => true,
            'underline' => true,
            'bg-color' => 'yellow',
            'color' => 'yellow',
            'blink' => true
        ]));
    }
    /**
     * @test
     */
    public function test13() {
        $_SERVER['NO_COLOR'] = 1;
        $this->assertEquals("Hello", OutputFormatter::formatOutput('Hello', [
            'reverse' => true,
            'bold' => true,
            'underline' => true,
            'bg-color' => 'yellow',
            'color' => 'yellow',
            'blink' => true,
            'ansi' => false
        ]));
    }
}
