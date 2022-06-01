<?php

namespace webfiori\framework\test\cli;
use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\CommandArgument;
/**
 * Description of CommandArgumentTest
 *
 * @author Ibrahim
 */
class CommandArgumentTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $arg = new CommandArgument();
        $this->assertNull($arg->getValue());
        $this->assertEquals('', $arg->getDefault());
        $this->assertEquals('', $arg->getDescription());
        $arg->setDescription(' Cool Arg ');
        $this->assertEquals('Cool Arg', $arg->getDescription());
        $arg->setDescription(' ');
        $this->assertEquals('', $arg->getDescription());
        $this->assertEquals('arg', $arg->getName());
        $this->assertFalse($arg->isOptional());
        $arg->setIsOptional(true);
        $this->assertTrue($arg->isOptional());
        $arg->setIsOptional(false);
        $this->assertFalse($arg->isOptional());
        $this->assertEquals([], $arg->getAllowedValues());
        $this->assertNull($arg->getValue());
    }
    /**
     * @test
     */
    public function test01() {
        $arg = new CommandArgument('');
        $this->assertEquals('arg', $arg->getName());
    }
    /**
     * @test
     */
    public function test02() {
        $arg = new CommandArgument('--config');
        $this->assertNull($arg->getValue());
        $this->assertEquals('', $arg->getDefault());
        $this->assertEquals('', $arg->getDescription());
        $this->assertEquals('--config', $arg->getName());
        $this->assertFalse($arg->isOptional());
        $this->assertEquals([], $arg->getAllowedValues());
        $this->assertNull($arg->getValue());
    }
    /**
     * @test
     */
    public function testSetName() {
        $arg = new CommandArgument('    ');
        $this->assertEquals('arg', $arg->getName());
        $this->assertTrue($arg->setName('my-val'));
        $this->assertEquals('my-val', $arg->getName());
        $this->assertFalse($arg->setName('with space'));
        $this->assertEquals('my-val', $arg->getName());
        $this->assertTrue($arg->setName('   --arg1    '));
        $this->assertEquals('--arg1', $arg->getName());
    }
    /**
     * @test
     */
    public function testSetValue00() {
        $arg = new CommandArgument();
        $this->assertNull($arg->getValue());
        $arg->setValue('');
        $this->assertEquals('', $arg->getValue());
        $arg->setValue('    Super Lengthy String      ');
        $this->assertEquals('Super Lengthy String', $arg->getValue());
    }
    /**
     * @test
     */
    public function testSetValue01() {
        $arg = new CommandArgument();
        $this->assertNull($arg->getValue());
        $arg->addAllowedValue('Super');
        $this->assertFalse($arg->setValue(''));
        $this->assertNull($arg->getValue());
        $this->assertTrue($arg->setValue('Super'));
        $this->assertEquals('Super', $arg->getValue());
    }
}
