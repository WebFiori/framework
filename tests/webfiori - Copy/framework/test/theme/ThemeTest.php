<?php
namespace webfiori\framework\test\theme;

use const DS;
use Exception;
use PHPUnit\Framework\TestCase;
use themes\fioriTheme\NewFTestTheme;
use webfiori\framework\App;
use webfiori\framework\Theme;
use webfiori\framework\ThemeManager;
/**
 * Description of ThemeTest
 *
 * @author Ibrahim
 */
class ThemeTest extends TestCase {
    public function testAvailableThemes00() {
        $themes = ThemeManager::getRegisteredThemes();
        $this->assertEquals(2, count($themes));
    }
    /**
     * @test
     */
    public function testCreateHTMLNode00() {
        App::getConfig()->setTheme('New Super Theme');

        $theme = ThemeManager::usingTheme();
        $this->assertTrue($theme instanceof Theme);
        $this->assertEquals('New Super Theme', $theme->getName());
        $node = $theme->createHTMLNode();
        $this->assertEquals('div', $node->getNodeName());

        $xNode = $theme->createHTMLNode([
            'name' => 'input',
            'attributes' => [
                'type' => 'text'
            ]
        ]);
        $this->assertEquals('input', $xNode->getNodeName());
        $this->assertEquals([
            'type' => 'text'
        ], $xNode->getAttributes());
    }
    /**
     * @test
     */
    public function testToJson00() {
        App::getConfig()->setTheme('');
        $theme = ThemeManager::usingTheme();
        $this->assertNull($theme);
        $theme = ThemeManager::usingTheme('New Super Theme');

        $j = $theme->toJSON();
        $j->setPropsStyle('camel');
        $this->assertEquals('{"name":"New Super Theme","url":"","license":"","licenseUrl":"","version":"1.0.0","author":"","authorUrl":""}',$j.'');
    }
    /**
     * @test
     */
    public function testUseTheme00() {
        $themeName = 'New Theme 2';
        ThemeManager::resetRegistered();
        ThemeManager::register(new \themes\fioriTheme2\NewTestTheme2());
        //$this->assertFalse(Theme::isThemeLoaded($themeName));
        $theme = ThemeManager::usingTheme($themeName);
        $this->assertTrue($theme instanceof Theme);
        $this->assertTrue(ThemeManager::isThemeRegistered($themeName));
        $this->assertEquals('1.0',$theme->getVersion());
        $this->assertEquals('This theme is in before loaded.',$theme->getDescription());
        $this->assertEquals('Ibrahim Ali',$theme->getAuthor());
        $this->assertEquals('https://opensource.org/licenses/MIT',$theme->getLicenseUrl());
        $this->assertEquals('MIT',$theme->getLicenseName());
        $this->assertEquals(1,count(ThemeManager::getRegisteredThemes()));
        $this->assertEquals('fioriTheme2', $theme->getDirectoryName());
        $this->assertEquals('https://my-theme-side.com', $theme->getUrl());
        $this->assertEquals(ROOT_PATH.DS.'themes'.DS.'fioriTheme2'.DS, $theme->getAbsolutePath());
        $this->assertEquals('css', $theme->getCssDirName());
        $this->assertEquals('js', $theme->getJsDirName());
        $this->assertEquals('images', $theme->getImagesDirName());
    }
    /**
     * @test
     */
    public function testUseTheme01() {
        App::getConfig()->setTheme('');
        $theme = ThemeManager::usingTheme();
        $this->assertNull($theme);
        ThemeManager::register(new NewFTestTheme());
        $theme = ThemeManager::usingTheme(NewFTestTheme::class);
        $this->assertTrue($theme instanceof Theme);
        $this->assertEquals('New Super Theme', $theme->getName());
        $this->assertEquals(App::getConfig()->getBaseURL(),$theme->getBaseURL());
        $theme->setBaseURL('https://example.com/x');
        $this->assertEquals('https://example.com/x',$theme->getBaseURL());
    }
    /**
     * @test
     */
    public function testUseTheme02() {
        $themeName = 'Not Exist';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No such theme: \''.$themeName.'\'.');
        ThemeManager::usingTheme('Not Exist');
    }
}
