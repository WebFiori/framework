<?php
namespace webfiori\framework\test\writers;

use PHPUnit\Framework\TestCase;
use webfiori\framework\Lang;
use webfiori\framework\writers\LangClassWriter;
/**
 *
 * @author Ibrahim
 */
class LangWritterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $writter = new LangClassWriter('EN', 'ltr');
        $this->assertEquals('LangEN', $writter->getName());
        $this->assertEquals('app\\langs', $writter->getNamespace());
        $this->assertEquals('', $writter->getSuffix());
        $this->assertEquals([
                Lang::class
        ], $writter->getUseStatements());
    }
}
