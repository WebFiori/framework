<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\Theme;
/**
 * Description of ThemeTest
 *
 * @author Ibrahim
 */
class ThemeTest extends TestCase{
    /**
     * @test
     */
    public function testUseTheme00() {
        $theme = Theme::usingTheme('WebFiori Theme');
        $this->assertTrue($theme instanceof Theme);
    }
}
