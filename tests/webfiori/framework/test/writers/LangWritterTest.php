<?php
namespace webfiori\framework\test\writers;

use webfiori\framework\cli\writers\LangClassWriter;
use PHPUnit\Framework\TestCase;
use webfiori\framework\Language;
/**
 * Description of CronWritterTest
 *
 * @author Ibrahim
 */
class LangWritterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $writter = new LangClassWriter('EN', 'ltr');
        $this->assertEquals('LanguageEN', $writter->getName());
        $this->assertEquals('app\\langs', $writter->getNamespace());
        $this->assertEquals('', $writter->getSuffix());
        $this->assertEquals([
                Language::class
        ], $writter->getUseStatements());
    }
}
