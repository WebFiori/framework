<?php
namespace webfiori\framework\test\writers;

use PHPUnit\Framework\TestCase;
use WebFiori\Cli\CLICommand;
use webfiori\framework\writers\CLICommandClassWriter;

class CLICommandClassWriterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $writer = new CLICommandClassWriter();
        $this->assertEquals('NewCommand', $writer->getName());
        $this->assertEquals(ROOT_PATH.DS.APP_DIR.DS.'commands'.DS.'NewCommand.php', $writer->getAbsolutePath());
        $this->assertEquals('app\\commands', $writer->getNamespace());
        $this->assertEquals('new-command', $writer->getCommandName());
        $this->assertEquals('', $writer->getDescription());
        $this->assertEquals([], $writer->getArgs());
        $this->assertEquals([
            'WebFiori\Cli\CLICommand'
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
        $this->assertTrue($writer->setClassName('DoItXCommand'));
        $this->assertEquals('DoItXCommand', $writer->getName());
        $this->assertEquals(ROOT_PATH.DS.APP_DIR.DS.'commands'.DS.'DoItXCommand.php', $writer->getAbsolutePath());
        $this->assertEquals('app\\commands', $writer->getNamespace());
        $this->assertEquals('Lets-Do-It', $writer->getCommandName());
        $this->assertEquals('', $writer->getDescription());
        $this->assertEquals([], $writer->getArgs());
        $writer->setArgs([
            new \WebFiori\Cli\Argument('--do', 'A do arg', true)
        ]);
        $this->assertEquals('', $writer->getDescription());
        $writer->setCommandDescription('Random desc');
        $this->assertEquals('Random desc', $writer->getDescription());
        $this->assertEquals([
            'WebFiori\Cli\CLICommand'
        ], $writer->getUseStatements());
        $writer->writeClass();
        $clazz = $writer->getNamespace().'\\'.$writer->getName();
        $this->assertTrue(class_exists($clazz));
        $writer->removeClass();
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof CLICommand);
        $this->assertEquals('Lets-Do-It', $clazzObj->getName());
        $this->assertEquals('Random desc', $clazzObj->getDescription());
        $this->assertEquals([
            '--do'
        ], $clazzObj->getArgsNames());
        $arg = $clazzObj->getArg('--do');
        $this->assertTrue($arg instanceof \WebFiori\Cli\Argument);
        $this->assertEquals('A do arg', $arg->getDescription());
        $this->assertTrue($arg->isOptional());
    }
    /**
     * @test
     */
    public function test02() {
        $writer = new CLICommandClassWriter();
        $this->assertFalse($writer->setCommandName('invalid name'));
        $this->assertFalse($writer->setCommandName('   '));
        $this->assertTrue($writer->setCommandName('Lets-Do-It'));
        $this->assertEquals('Lets-Do-It', $writer->getCommandName());
        $this->assertFalse($writer->setClassName('Invalid Name'));
        $this->assertFalse($writer->setClassName('   '));
        $this->assertTrue($writer->setClassName('DoItX2Command'));
        $this->assertEquals('DoItX2Command', $writer->getName());
        $this->assertEquals(ROOT_PATH.DS.APP_DIR.DS.'commands'.DS.'DoItX2Command.php', $writer->getAbsolutePath());
        $this->assertEquals('app\\commands', $writer->getNamespace());
        $this->assertEquals('Lets-Do-It', $writer->getCommandName());
        $this->assertEquals('', $writer->getDescription());
        $this->assertEquals([], $writer->getArgs());
        $writer->setArgs([
            new \WebFiori\Cli\Argument('--do', 'A do arg', true)
        ]);
        $this->assertEquals('', $writer->getDescription());
        $writer->setCommandDescription(' ');
        $this->assertEquals('', $writer->getDescription());
        $this->assertEquals([
            'WebFiori\Cli\CLICommand'
        ], $writer->getUseStatements());
        $writer->writeClass();
        $clazz = $writer->getNamespace().'\\'.$writer->getName();
        $this->assertTrue(class_exists($clazz));
        $writer->removeClass();
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof CLICommand);
        $this->assertEquals('Lets-Do-It', $clazzObj->getName());
        $this->assertEquals('<NO DESCRIPTION>', $clazzObj->getDescription());
        $this->assertEquals([
            '--do'
        ], $clazzObj->getArgsNames());
        $arg = $clazzObj->getArg('--do');
        $this->assertTrue($arg instanceof \WebFiori\Cli\Argument);
        $this->assertEquals('A do arg', $arg->getDescription());
        $this->assertTrue($arg->isOptional());
    }
}
