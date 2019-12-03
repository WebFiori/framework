<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\Page;
use phpStructs\html\HTMLNode;
use webfiori\entity\Theme;
use webfiori\entity\langs\Language;
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
    public function testRender00() {
        $doc = Page::render(true);
        $this->assertEquals('<!DOCTYPE html>'
                . '<html>'
                . '<head>'
                . '<base href="https://127.0.0.1/">'
                . '<title>Hello World | Hello Website</title>'
                . '<link rel="canonical" href="https://127.0.0.1/">'
                . '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">'
                . '</head>'
                . '<body itemscope="" itemtype="http://schema.org/WebPage">'
                . '<div id="page-header">'
                . '</div>'
                . '<div id="page-body">'
                . '<div id="side-content-area"></div>'
                . '<div id="main-content-area"></div>'
                . '</div><div id="page-footer">'
                . '</div>'
                . '</body>'
                . '</html>',$doc);
    }
    /**
     * @test
     */
    public function testReset00() {
        Page::theme('WebFiori Theme');
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
    /**
     * @test
     */
    public function testCanonical() {
        $c = Page::canonical('https://example.com/home');
        $this->assertEquals('https://example.com/home',$c);
        $this->assertEquals('https://example.com/home',Page::document()->getHeadNode()->getCanonical());
    }
    /**
     * @test
     */
    public function testInsert00() {
        Page::reset();
        $node = new HTMLNode();
        $node->setID('new-node');
        $this->assertTrue(Page::insert($node));
        $this->assertEquals(1,Page::document()->getChildByID('main-content-area')->childrenCount());
        $el = Page::document()->getChildByID('new-node');
        $this->assertTrue($el === $node);
    }
    /**
     * @test
     */
    public function testInsert01() {
        Page::reset();
        $node = new HTMLNode();
        $node->setID('new-node');
        $this->assertFalse(Page::insert($node,''));
        $this->assertEquals(0,Page::document()->getChildByID('main-content-area')->childrenCount());
        $el = Page::document()->getChildByID('new-node');
        $this->assertNull($el);
    }
    /**
     * @test
     */
    public function testUsingLang00() {
        Page::reset();
        $null = Page::translation();
        $this->assertNull($null);
    }
    /**
     * @test
     */
    public function testUsingLang01() {
        Page::reset();
        Page::lang('en');
        $lang = Page::translation();
        $this->assertTrue($lang instanceof Language);
        $lang2 = Page::translation();
        $this->assertTrue($lang2 instanceof Language);
        $this->assertTrue($lang === $lang2);
        Page::lang('ar');
        $lang3 = Page::translation();
        $this->assertTrue($lang3 instanceof Language);
        $this->assertFalse($lang3 === $lang2);
    }
    /**
     * @test
     */
    public function testUsingLang02() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No language class was found for the language \'NM\'.');
        Page::reset();
        Page::lang('nm');
        Page::translation();
    }
    /**
     * @test
     */
    public function testUsingLang03() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The translation file was found. But no object of type \'Language\' is stored. Make sure that the parameter $addtoLoadedAfterCreate is set to true when creating the language object.');
        Page::reset();
        Page::lang('jp');
        Page::translation();
    }
    
}
