<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\Theme;
use webfiori\conf\SiteConfig;
/**
 * Description of ThemeTest
 *
 * @author Ibrahim
 */
class ThemeTest extends TestCase{
    public function testAvailableThemes00() {
        $themes = Theme::getAvailableThemes();
        $this->assertEquals(4,count($themes));
    }
    /**
     * @test
     */
    public function testUseTheme00() {
        $themeName = 'WebFiori Theme';
        //$this->assertFalse(Theme::isThemeLoaded($themeName));
        $theme = Theme::usingTheme($themeName);
        $this->assertTrue($theme instanceof Theme);
        $this->assertTrue(Theme::isThemeLoaded($themeName));
        $this->assertEquals('1.0.1',$theme->getVersion());
        $this->assertEquals('The main theme for WebFiori Framework.',$theme->getDescription());
        $this->assertEquals('Ibrahim Ali',$theme->getAuthor());
        $this->assertEquals('https://opensource.org/licenses/MIT',$theme->getLicenseUrl());
        $this->assertEquals('MIT License',$theme->getLicenseName());
        $this->assertEquals(1,count(Theme::getLoadedThemes()));
    }
    /**
     * @test
     */
    public function testUseTheme01() {
        $theme = Theme::usingTheme();
        $this->assertTrue($theme instanceof Theme);
        $this->assertEquals('WebFiori Theme',$theme->getName());
    }
    /**
     * @test
     */
    public function testUseTheme02() {
        $themeName = 'Not Exist';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No such theme: \''.$themeName.'\'.');
        Theme::usingTheme('Not Exist');
    }
}
