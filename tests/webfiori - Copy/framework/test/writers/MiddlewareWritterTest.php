<?php
namespace webfiori\framework\test\writers;

use webfiori\framework\cli\CLITestCase;
use webfiori\framework\middleware\AbstractMiddleware;
use webfiori\framework\writers\MiddlewareClassWriter;
/**
 *
 * @author Ibrahim
 */
class MiddlewareWritterTest extends CLITestCase {
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
            "webfiori\\framework\\session\\SessionsManager",
            "WebFiori\\Http\\Request",
            "WebFiori\\Http\\Response",
        ], $writter->getUseStatements());
        $this->assertEquals('New Middleware', $writter->getMiddlewareName());
        $this->assertEquals(0, $writter->getMiddlewarePriority());
        $this->assertEquals([], $writter->getGroups());
    }
    /**
     * @test
     */
    public function test01() {
        $writter = new MiddlewareClassWriter();
        $writter->setClassName('XMd');
        $writter->setMiddlewareName('Super Middleware');
        $writter->setMiddlewarePriority(100);
        $writter->addGroup('one-group');
        $writter->addGroup('two-group');
        $writter->addGroup('global');
        $this->assertEquals('XMdMiddleware', $writter->getName());
        $this->assertEquals('app\\middleware', $writter->getNamespace());
        $this->assertEquals('Middleware', $writter->getSuffix());
        $this->assertEquals([
            "webfiori\\framework\\middleware\\AbstractMiddleware",
            "webfiori\\framework\\session\\SessionsManager",
            "WebFiori\\Http\\Request",
            "WebFiori\\Http\\Response",
        ], $writter->getUseStatements());
        $this->assertEquals('Super Middleware', $writter->getMiddlewareName());
        $this->assertEquals(100, $writter->getMiddlewarePriority());
        $this->assertEquals([
            'one-group', 'two-group', 'global'
        ], $writter->getGroups());

        $writter->writeClass();

        $clazz = $writter->getName(true);
        $this->assertTrue(class_exists($clazz));
        $writter->removeClass();
        $md = new $clazz();
        $this->assertTrue($md instanceof AbstractMiddleware);

        $this->assertEquals('Super Middleware', $md->getName());
        $this->assertEquals(100, $md->getPriority());
        $this->assertEquals([
            'one-group', 'two-group', 'global'
        ], $md->getGroups());
    }
}
