<?php
namespace webfiori\framework\test\writers;

use webfiori\framework\cli\writers\TableClassWriter;
use PHPUnit\Framework\TestCase;
/**
 * Description of CronWritterTest
 *
 * @author Ibrahim
 */
class TablewWritterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $writter = new TableClassWriter();
        $this->assertEquals('NewTable', $writter->getName());
        $this->assertEquals('app\\database', $writter->getNamespace());
        $this->assertEquals('Table', $writter->getSuffix());
        $this->assertEquals([
            
        ], $writter->getUseStatements());
    }
}
