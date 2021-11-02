<?php
namespace webfiori\tests\entity;

use PHPUnit\Framework\TestCase;
use webfiori\framework\WebFioriApp;
use webfiori\framework\Theme;
use webfiori\framework\ThemeLoader;
/**
 * Description of ThemeTest
 *
 * @author Ibrahim
 */
class ThemeTest extends TestCase {
    public function testAvailableThemes00() {
        $themes = ThemeLoader::getAvailableThemes();
        $this->assertEquals(9,count($themes));
    }
    /**
     * @test
     */
    public function testToJson00() {
        $theme = ThemeLoader::usingTheme();
        $j = $theme->toJSON();
        $j->setPropsStyle('camel');
        $this->assertEquals('{"themesPath":"'. \webfiori\json\Json::escapeJSONSpecialChars(THEMES_PATH).'", "name":"New Fiori", "url":"", "license":"MIT", "licenseUrl":"", "version":"1.0", "author":"", "authorUrl":"", "imagesDirName":"images", "themeDirName":"newFiori", "cssDirName":"css", "jsDirName":"js"}',$j.'');
    }
    /**
     * @test
     */
    public function testUseTheme00() {
        $themeName = 'WebFiori Theme';
        ThemeLoader::resetLoaded();
        //$this->assertFalse(Theme::isThemeLoaded($themeName));
        $theme = ThemeLoader::usingTheme($themeName);
        $this->assertTrue($theme instanceof Theme);
        $this->assertTrue(ThemeLoader::isThemeLoaded($themeName));
        $this->assertEquals('1.0.1',$theme->getVersion());
        $this->assertEquals('The main theme for WebFiori Framework.',$theme->getDescription());
        $this->assertEquals('Ibrahim Ali',$theme->getAuthor());
        $this->assertEquals('https://opensource.org/licenses/MIT',$theme->getLicenseUrl());
        $this->assertEquals('MIT License',$theme->getLicenseName());
        $this->assertEquals(1,count(ThemeLoader::getLoadedThemes()));
    }
    /**
     * @test
     */
    public function testUseTheme01() {
        $theme = ThemeLoader::usingTheme();
        $this->assertTrue($theme instanceof Theme);
        $this->assertEquals('New Fiori',$theme->getName());
        $this->assertEquals(WebFioriApp::getAppConfig()->getBaseURL(),$theme->getBaseURL());
        $theme->setBaseURL('https://example.com/x');
        $this->assertEquals('https://example.com/x',$theme->getBaseURL());
    }
    /**
     * @test
     */
    public function testUseTheme02() {
        $themeName = 'Not Exist';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No such theme: \''.$themeName.'\'.');
        ThemeLoader::usingTheme('Not Exist');
    }
}
