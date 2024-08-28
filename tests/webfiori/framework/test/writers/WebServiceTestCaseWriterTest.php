<?php
namespace webfiori\framework\test\writers;

use PHPUnit\Framework\TestCase;
use webfiori\framework\scheduler\webServices\TasksLogoutService;
use webfiori\framework\scheduler\webServices\TasksServicesManager;
use webfiori\framework\writers\APITestCaseWriter;
/**
 *
 * @author Ibrahim
 */
class WebServiceTestCaseWriterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $w = new APITestCaseWriter(new TasksServicesManager(), \webfiori\framework\scheduler\webServices\ForceTaskExecutionService::class);
        $w->writeClass();
        $this->assertTrue(true);
    }
}
