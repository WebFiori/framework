<?php
namespace webfiori\framework\test\writers;

use webfiori\framework\cli\writers\ThemeClassWriter;
use PHPUnit\Framework\TestCase;
/**
 * Description of CronWritterTest
 *
 * @author Ibrahim
 */
class ThemeWritterTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $writter = new ThemeClassWriter();
        $this->assertEquals('NewTheme', $writter->getName());
        $this->assertEquals('app\\themes\\new', $writter->getNamespace());
        $this->assertEquals('Theme', $writter->getSuffix());
        $this->assertEquals([
        ], $writter->getUseStatements());
        $this->assertEquals('New Theme', $writter->getThemeName());
    }
}
