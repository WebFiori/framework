<?php
namespace webfiori\tests\entity\cli;
use PHPUnit\Framework\TestCase;

class TestCLICommand extends TestCase{
    /**
     * @test
     */
    public function test00() {
        $command = new TestCommand('new-command');
        $this->assertEquals($command->getName(), 'new-command');
        $this->assertEquals('<NO DESCRIPTION>', $command->getDescription());
        $this->assertEquals(0, count($command->getArgs()));
    }
}
