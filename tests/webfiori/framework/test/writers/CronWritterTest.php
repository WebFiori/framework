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
        $this->assertEquals('NewJob', $writter->getName());
        $this->assertEquals('app\\jobs', $writter->getNamespace());
        $this->assertEquals('Job', $writter->getSuffix());
        $this->assertEquals([
            "webfiori\\framework\\scheduler\\AbstractTask",
            "webfiori\\framework\\scheduler\\TaskStatusEmail",
            "webfiori\\framework\\scheduler\\TasksManager",
        ], $writter->getUseStatements());
        $this->assertEquals('No Description', $writter->getJobDescription());
        $this->assertEquals(0, count($writter->getJob()->getArguments()));
    }
    /**
     * @test
     */
    public function test01() {
        $writter = new SchedulerTaskClassWriter();
        $writter->setClassName('NewOk');
        $this->assertEquals('NewOkJob', $writter->getName());
        $this->assertEquals('app\\jobs', $writter->getNamespace());
        $this->assertEquals('Job', $writter->getSuffix());
        $this->assertEquals([
            "webfiori\\framework\\scheduler\\AbstractTask",
            "webfiori\\framework\\scheduler\\TaskStatusEmail",
            "webfiori\\framework\\scheduler\\TasksManager",
        ], $writter->getUseStatements());
        $this->assertEquals('No Description', $writter->getJobDescription());
        $this->assertEquals(0, count($writter->getJob()->getArguments()));
        $writter->addArgument(new TaskArgument('test', 'A test Arg.'));
        $this->assertEquals(1, count($writter->getJob()->getArguments()));
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
