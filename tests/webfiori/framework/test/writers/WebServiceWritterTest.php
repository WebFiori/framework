<?php
namespace webfiori\framework\test\writers;

use PHPUnit\Framework\TestCase;
use webfiori\framework\writers\WebServiceWriter;
use WebFiori\Http\AbstractWebService;
/**
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
            "WebFiori\Http\AbstractWebService",
            "WebFiori\Http\ParamType",
            "WebFiori\Http\ParamOption",
            "webfiori\\http\\RequestMethod"
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
            "WebFiori\Http\AbstractWebService",
            "WebFiori\Http\ParamType",
            "WebFiori\Http\ParamOption",
            "webfiori\\http\\RequestMethod"
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
    /**
     * @test
     */
    public function test02() {
        $writter = new WebServiceWriter();
        $writter->setClassName('Super2Service');
        $this->assertEquals('Super2Service', $writter->getName());
        $this->assertEquals('app\\apis', $writter->getNamespace());
        $this->assertEquals('Service', $writter->getSuffix());
        $this->assertEquals([
            "WebFiori\Http\AbstractWebService",
            "WebFiori\Http\ParamType",
            "WebFiori\Http\ParamOption",
            "webfiori\\http\\RequestMethod"
        ], $writter->getUseStatements());
        $writter->addRequestParam([
            'name' => 'param-1',
            'type' => 'boolean'
        ]);
        $writter->addRequestParam([
            'name' => 'param-2',
            'type' => 'boolean',
            'default' => false,
            'description' => 'A bool.'
        ]);
        $writter->addRequestParam([
            'name' => 'param-3',
            'type' => 'string',
            'optional' => true,
            'default' => 'Ok',
            'allow-empty' => true
        ]);
        $writter->addRequestParam([
            'name' => 'param-4',
            'type' => 'string',
            'allow-empty' => true
        ]);
        $writter->addRequestParam([
            'name' => 'param-5',
            'type' => 'boolean',
            'default' => true,
            'description' => 'A second bool.'
        ]);
        $writter->addRequestParam([
            'name' => 'param-6',
            'type' => 'integer',
            'default' => 66,
            'description' => 'A number.',
            'optional' => true
        ]);
        $writter->addRequestMethod('get');
        $writter->writeClass();
        $clazz = $writter->getName(true);
        $this->assertTrue(class_exists($clazz));
        $clazzObj = new $clazz();
        $writter->removeClass();
        $this->assertTrue($clazzObj instanceof AbstractWebService);
        $this->assertEquals(6, count($clazzObj->getParameters()));

        $param1 = $clazzObj->getParameterByName('param-1');

        $this->assertEquals('boolean', $param1->getType());
        $this->assertNull($param1->getDefault());
        $this->assertNull($param1->getDescription());
        $this->assertFalse($param1->isOptional());
        $this->assertFalse($param1->isEmptyStringAllowed());

        $param2 = $clazzObj->getParameterByName('param-2');
        $this->assertEquals('boolean', $param2->getType());
        $this->assertFalse($param2->getDefault());
        $this->assertEquals('A bool.', $param2->getDescription());
        $this->assertFalse($param2->isOptional());
        $this->assertFalse($param2->isEmptyStringAllowed());

        $param3 = $clazzObj->getParameterByName('param-3');
        $this->assertEquals('string', $param3->getType());
        $this->assertEquals('Ok', $param3->getDefault());
        $this->assertNull($param3->getDescription());
        $this->assertTrue($param3->isOptional());
        $this->assertTrue($param3->isEmptyStringAllowed());

        $param3 = $clazzObj->getParameterByName('param-6');
        $this->assertEquals('integer', $param3->getType());
        $this->assertEquals(66, $param3->getDefault());
        $this->assertEquals('A number.', $param3->getDescription());
        $this->assertTrue($param3->isOptional());
    }
}
