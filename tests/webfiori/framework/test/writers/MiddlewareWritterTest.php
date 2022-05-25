<?php
namespace webfiori\framework\test\writers;

use webfiori\framework\cli\writers\MiddlewareClassWriter;
use PHPUnit\Framework\TestCase;
/**
 * Description of CronWritterTest
 *
 * @author Ibrahim
 */
class MiddlewareWritterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $writter = new MiddlewareClassWriter();
        $this->assertEquals('NewMiddleware', $writter->getName());
        $this->assertEquals('app\\middleware', $writter->getNamespace());
        $this->assertEquals('Middleware', $writter->getSuffix());
        $this->assertEquals([
            "webfiori\\framework\\middleware\\AbstractMiddleware",
            "webfiori\\framework\\SessionsManager",
            "webfiori\\http\\Request",
            "webfiori\\http\\Response",
        ], $writter->getUseStatements());
        $this->assertEquals('New Middleware', $writter->getMiddlewareName());
        $this->assertEquals(0, $writter->getMiddlewarePriority());
        $this->assertEquals([], $writter->getGroups());
    }
}
