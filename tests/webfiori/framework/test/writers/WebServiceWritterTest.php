<?php
namespace webfiori\framework\test\writers;

use webfiori\framework\cli\writers\WebServiceWriter;
use PHPUnit\Framework\TestCase;
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
}
