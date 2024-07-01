<?php
namespace webfiori\framework\test\writers;

use PHPUnit\Framework\TestCase;
use webfiori\framework\scheduler\AbstractTask;
use webfiori\framework\scheduler\TaskArgument;
use webfiori\framework\writers\SchedulerTaskClassWriter;
/**
 *
 * @author Ibrahim
 */
class BackgroundTaskWritterTest extends TestCase {
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
    /**
     * @test
     */
    public function test02() {
        $writter = new SchedulerTaskClassWriter('NewOk2B', 'Super Test Task', 'A test Task', [
            new TaskArgument('name', 'The name of something.')
        ]);

        $this->assertEquals('NewOk2BTask', $writter->getName());
        
        $this->assertEquals('app\\tasks', $writter->getNamespace());
        $this->assertEquals('Task', $writter->getSuffix());
        $this->assertEquals([
            "webfiori\\framework\\scheduler\\AbstractTask",
            "webfiori\\framework\\scheduler\\TaskStatusEmail",
            "webfiori\\framework\\scheduler\\TasksManager",
        ], $writter->getUseStatements());
        $this->assertEquals('A test Task', $writter->getTaskDescription());
        $this->assertEquals(1, count($writter->getTask()->getArguments()));
        $writter->addArgument(new TaskArgument('test', 'A test Arg.'));
        $this->assertEquals(2, count($writter->getTask()->getArguments()));
        $writter->addArgument(new TaskArgument('test-2', 'Second test arg'));
        $writter->writeClass();
        $clazz = '\\'.$writter->getNamespace().'\\'.$writter->getName();
        $this->assertTrue(class_exists($clazz));
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof AbstractTask);
        $this->assertEquals('A test Task', $clazzObj->getDescription());
        $this->assertEquals(3, count($clazzObj->getArguments()));
        $this->assertEquals('name', $clazzObj->getArguments()[0]->getName());
        $this->assertEquals('The name of something.', $clazzObj->getArguments()[0]->getDescription());
        $this->assertEquals('test', $clazzObj->getArguments()[1]->getName());
        $this->assertEquals('A test Arg.', $clazzObj->getArguments()[1]->getDescription());
        $this->assertEquals('test-2', $clazzObj->getArguments()[2]->getName());
        $this->assertEquals('Second test arg', $clazzObj->getArguments()[2]->getDescription());
        $writter->removeClass();
    }
}
