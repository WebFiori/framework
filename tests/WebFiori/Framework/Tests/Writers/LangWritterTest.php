<?php
namespace WebFiori\Framework\Test\Writers;

use PHPUnit\Framework\TestCase;
use WebFiori\Framework\Lang;
use WebFiori\Framework\Writers\LangClassWriter;
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
        $this->assertEquals('App\\Langs', $writter->getNamespace());
        $this->assertEquals('', $writter->getSuffix());
        $this->assertEquals([
                Lang::class
        ], $writter->getUseStatements());
    }
}
