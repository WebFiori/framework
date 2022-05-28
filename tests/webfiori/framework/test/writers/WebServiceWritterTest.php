<?php
namespace webfiori\framework\test\writers;

use webfiori\framework\cli\writers\WebServiceWriter;
use PHPUnit\Framework\TestCase;
use webfiori\http\AbstractWebService;
/**
 * Description of CronWritterTest
 *
 * @author Ibrahim
 */
class WebServiceWritterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $writter = new WebServiceWriter();
        $this->assertEquals('NewWebService', $writter->getName());
        $this->assertEquals('app\\apis', $writter->getNamespace());
        $this->assertEquals('Service', $writter->getSuffix());
        $this->assertEquals([
            "webfiori\\framework\\EAbstractWebService",
        ], $writter->getUseStatements());
    }
    /**
     * @test
     */
    public function test01() {
        $writter = new WebServiceWriter();
        $writter->setClassName('SuperService');
        $this->assertEquals('SuperService', $writter->getName());
        $this->assertEquals('app\\apis', $writter->getNamespace());
        $this->assertEquals('Service', $writter->getSuffix());
        $this->assertEquals([
            "webfiori\\framework\\EAbstractWebService",
        ], $writter->getUseStatements());
        $writter->addRequestParam([
            'name' => 'param-1',
            'type' => 'boolean'
        ]);
        $writter->addRequestMethod('get');
        $writter->writeClass();
        $clazz = '\\'.$writter->getNamespace().'\\'.$writter->getName();
        $this->assertTrue(class_exists($clazz));
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof AbstractWebService);
        $this->assertEquals(1, count($clazzObj->getParameters()));
        $writter->removeClass();
    }
}
