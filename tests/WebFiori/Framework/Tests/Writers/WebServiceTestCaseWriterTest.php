<?php
namespace WebFiori\Framework\Test\Writers;

use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Scheduler\WebServices\ForceTaskExecutionService;
use WebFiori\Framework\Scheduler\WebServices\TasksServicesManager;
use WebFiori\Framework\Writers\APITestCaseWriter;
/**
 *
 * @author Ibrahim
 */
class WebServiceTestCaseWriterTest extends CLITestCase {
    /**
     * @test
     */
    public function test00() {
        $w = new APITestCaseWriter(new TasksServicesManager(), ForceTaskExecutionService::class);
        $this->assertEquals('tests\\apis\\WebServiceTest', $w->getName(true));
        $this->assertEquals(9, $w->getPhpUnitVersion());
        $this->assertEquals(ROOT_PATH.DS.'tests'.DS.'apis'.DS.'WebServiceTest.php', $w->getAbsolutePath());
        $w->writeClass();
        $this->assertTrue(class_exists('\\'.$w->getName(true)));
        unlink($w->getAbsolutePath());
    }
    /**
     * @test
     */
    public function test01() {
        $w = new APITestCaseWriter(new TasksServicesManager(), new ForceTaskExecutionService());
        $w->setClassName('Cool');
        $w->setNamespace('\\tests\\cool');
        $w->setPath(ROOT_PATH.DS.'tests'.DS.'cool');
        $this->assertEquals('tests\\cool\\CoolTest', $w->getName(true));
        $w->writeClass();
        $this->assertEquals(ROOT_PATH.DS.'tests'.DS.'cool'.DS.'CoolTest.php', $w->getAbsolutePath());
        $this->assertTrue(file_exists($w->getAbsolutePath()));
        require_once $w->getAbsolutePath();
        $this->assertTrue(class_exists('\\'.$w->getName(true)));
        unlink($w->getAbsolutePath());
    }
}
