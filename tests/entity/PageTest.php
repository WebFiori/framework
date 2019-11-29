<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\Page;
/**
 * Description of PageTest
 *
 * @author Ibrahim
 */
class PageTest extends TestCase{
    /**
     * @test
     */
    public function testDefaults00() {
        $this->assertNull(Page::lang());
        $this->assertNull(Page::description());
        $this->assertEquals('Hello World',Page::title());
        $this->assertEquals('Hello Website',Page::siteName());
        $this->assertEquals(' | ',Page::separator());
        $this->assertTrue(Page::header());
        $this->assertTrue(Page::footer());
        $this->assertTrue(Page::aside());
        $this->assertNull(Page::theme());
        $this->assertEquals('ltr',Page::dir());
        $this->assertNull(Page::translation());
        $this->assertEquals('https://127.0.0.1/',Page::canonical());
    }
    /**
     * @test
     */
    public function testReset00() {
        Page::theme('Webfiori Theme');
        Page::lang('ar');
        Page::description('This is a test page.');
        Page::title('Login');
        Page::siteName('Small ERP');
        Page::separator('-');
        Page::header(false);
        Page::footer(false);
        Page::aside(false);
        Page::translation();
        
        $this->assertEquals('AR',Page::lang());
        $this->assertEquals('This is a test page.',Page::description());
        $this->assertEquals('Login',Page::title());
        $this->assertEquals('Small ERP',Page::siteName());
        $this->assertEquals(' - ',Page::separator());
        $this->assertFalse(Page::header());
        $this->assertFalse(Page::footer());
        $this->assertFalse(Page::aside());
        $this->assertNotNull(Page::theme());
        $this->assertEquals('rtl',Page::dir());
        $this->assertNotNull(Page::translation());
        
        Page::reset();
        $this->assertNull(Page::lang());
        $this->assertNull(Page::description());
        $this->assertEquals('Hello World',Page::title());
        $this->assertEquals('Hello Website',Page::siteName());
        $this->assertEquals(' | ',Page::separator());
        $this->assertTrue(Page::header());
        $this->assertTrue(Page::footer());
        $this->assertTrue(Page::aside());
        $this->assertNull(Page::theme());
        $this->assertEquals('ltr',Page::dir());
        $this->assertNull(Page::translation());
        $this->assertEquals('https://127.0.0.1/',Page::canonical());
    }
}
