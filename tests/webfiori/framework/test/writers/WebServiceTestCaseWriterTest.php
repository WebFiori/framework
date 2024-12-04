<?php
namespace webfiori\framework\test\writers;

use webfiori\framework\scheduler\webServices\ForceTaskExecutionService;
use webfiori\framework\scheduler\webServices\TasksServicesManager;
use webfiori\framework\test\cli\CreateTestCase;
use webfiori\framework\writers\APITestCaseWriter;
/**
 *
 * @author Ibrahim
 */
class WebServiceTestCaseWriterTest extends CreateTestCase {
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
