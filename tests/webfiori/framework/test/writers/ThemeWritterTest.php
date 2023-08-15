<?php
namespace webfiori\framework\test\writers;

use PHPUnit\Framework\TestCase;
use webfiori\framework\Theme;
use webfiori\framework\writers\ThemeClassWriter;
/**
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
    /**
     * @test
     */
    public function test01() {
        $writter = new ThemeClassWriter();
        $writter->setClassName('SuperHotNew');
        $writter->setThemeName('Cool Theme');
        $this->assertEquals('SuperHotNewTheme', $writter->getName());
        $writter->setNamespace('app\\themes\\cool');
        $writter->setPath(ROOT_PATH.DS.APP_DIR.DS.'themes'.DS.'cool');
        $this->assertEquals('app\\themes\\cool', $writter->getNamespace());
        $this->assertEquals('Theme', $writter->getSuffix());
        $this->assertEquals([
        ], $writter->getUseStatements());
        $this->assertEquals('Cool Theme', $writter->getThemeName());
        $writter->writeClass();
        $clazz = $writter->getName(true);
        $this->assertTrue(class_exists($clazz));
        $writter->removeClass();
        $clazzObj = new $clazz();
        $this->assertTrue($clazzObj instanceof Theme);
        $this->assertEquals('Cool Theme', $clazzObj->getName());
        $writter->removeClass();
        $writter->removeComponents();
    }
}
