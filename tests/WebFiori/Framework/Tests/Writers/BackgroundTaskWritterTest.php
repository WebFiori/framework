<?php
namespace WebFiori\Framework\Test\Writers;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Scheduler\AbstractTask;
use WebFiori\Framework\Scheduler\TaskArgument;
use WebFiori\Framework\Writers\SchedulerTaskClassWriter;
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
        $this->assertEquals('App\\Tasks', $writter->getNamespace());
        $this->assertEquals('Task', $writter->getSuffix());
        $this->assertEquals([
            "WebFiori\\Framework\\Scheduler\\AbstractTask",
            "WebFiori\\Framework\\Scheduler\\TaskStatusEmail",
            "WebFiori\\Framework\\Scheduler\\TasksManager",
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
        $this->assertEquals('App\\Tasks', $writter->getNamespace());
        $this->assertEquals('Task', $writter->getSuffix());
        $this->assertEquals([
            "WebFiori\\Framework\\Scheduler\\AbstractTask",
            "WebFiori\\Framework\\Scheduler\\TaskStatusEmail",
            "WebFiori\\Framework\\Scheduler\\TasksManager",
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
        
        $this->assertEquals('App\\Tasks', $writter->getNamespace());
        $this->assertEquals('Task', $writter->getSuffix());
        $this->assertEquals([
            "WebFiori\\Framework\\Scheduler\\AbstractTask",
            "WebFiori\\Framework\\Scheduler\\TaskStatusEmail",
            "WebFiori\\Framework\\Scheduler\\TasksManager",
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
