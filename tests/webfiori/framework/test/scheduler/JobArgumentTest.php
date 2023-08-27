<?php
namespace webfiori\framework\test\scheduler;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use webfiori\framework\scheduler\TaskArgument;
/**
 *
 * @author Ibrahim
 */
class JobArgumentTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $arg = new TaskArgument('Super Arg');
        $this->assertEquals('Super Arg', $arg->getName());
        $this->assertEquals('NO DESCRIPTION', $arg->getDescription());
        $this->assertNull($arg->getValue());
        $this->assertNull($arg->getDefault());
        $this->assertEquals('{"name":"Super Arg","description":"NO DESCRIPTION","default":null}', $arg->toJSON().'');
        $arg->setValue('Cool');
        $arg->setDefault('Ok');
        $this->assertEquals('Cool',$arg->getValue());
        $this->assertEquals('Ok',$arg->getDefault());
        $this->assertEquals('{"name":"Super Arg","description":"NO DESCRIPTION","default":"Ok"}', $arg->toJSON().'');
    }
    /**
     * @test
     */
    public function test01() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid argument name: <empty string>");
        $arg = new TaskArgument('');
    }
    /**
     * @test
     */
    public function test02() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid argument name: Super#Arg");
        $arg = new TaskArgument('Super#Arg');
    }
    /**
     * @test
     */
    public function test03() {
        $arg = new TaskArgument('Ok');
        $arg->setName('New Name  ');
        $arg->setDescription('   ');
        $this->assertEquals('New Name', $arg->getName());
        $this->assertEquals('NO DESCRIPTION', $arg->getDescription());
        $this->assertEquals('{"name":"New Name","description":"NO DESCRIPTION","default":null}', $arg->toJSON().'');
        $arg->setDescription(' This arg is cool.  ');
        $this->assertEquals('This arg is cool.', $arg->getDescription());
        $this->assertEquals('{"name":"New Name","description":"This arg is cool.","default":null}', $arg->toJSON().'');
    }
}
