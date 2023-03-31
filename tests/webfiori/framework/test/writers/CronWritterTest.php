<?php
namespace webfiori\framework\test\writers;

use webfiori\framework\writers\SchedulerTaskClassWriter;
use PHPUnit\Framework\TestCase;
use webfiori\framework\scheduler\TaskArgument;
use webfiori\framework\scheduler\AbstractTask;
/**
 *
 * @author Ibrahim
 */
class CronWritterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $writter = new SchedulerTaskClassWriter();
        $this->assertEquals('NewTask', $writter->getName());
        $this->assertEquals('app\\tasks', $writter->getNamespace());
        $this->assertEquals('Task', $writter->getSuffix());
        $this->assertEquals([
            "webfiori\\framework\\scheduler\\AbstractTask",
            "webfiori\\framework\\scheduler\\TaskStatusEmail",
            "webfiori\\framework\\scheduler\\TasksManager",
        ], $writter->getUseStatements());
        $this->assertEquals('No Description', $writter->getTaskDescription());
        $this->assertEquals(0, count($writter->getTask()->getArguments()));
    }
    /**
     * @test
     */
    public function test01() {
        $writter = new SchedulerTaskClassWriter();
        $writter->setClassName('NewOk');
        $this->assertEquals('NewOkTask', $writter->getName());
        $this->assertEquals('app\\tasks', $writter->getNamespace());
        $this->assertEquals('Task', $writter->getSuffix());
        $this->assertEquals([
            "webfiori\\framework\\scheduler\\AbstractTask",
            "webfiori\\framework\\scheduler\\TaskStatusEmail",
            "webfiori\\framework\\scheduler\\TasksManager",
        ], $writter->getUseStatements());
        $this->assertEquals('No Description', $writter->getTaskDescription());
        $this->assertEquals(0, count($writter->getTask()->getArguments()));
        $writter->addArgument(new TaskArgument('test', 'A test Arg.'));
        $this->assertEquals(1, count($writter->getTask()->getArguments()));
        $writter->addArgument(new TaskArgument('test-2', 'Second test arg'));
        $writter->writeClass();
        $clazz = '\\'.$writter->getNamespace().'\\'.$writter->getName();
        $this->assertTrue(class_exists($clazz));
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof AbstractTask);
        $this->assertEquals(2, count($clazzObj->getArguments()));
        $writter->removeClass();
    }
}
