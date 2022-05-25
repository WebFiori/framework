<?php
namespace webfiori\framework\test\writers;

use webfiori\framework\cli\writers\LangClassWriter;
use PHPUnit\Framework\TestCase;
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
            "webfiori\\framework\\i18n\\Language",
        ], $writter->getUseStatements());
    }
}
