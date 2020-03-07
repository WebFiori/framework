<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\entity\Page;
use phpStructs\html\HTMLNode;
use webfiori\entity\Theme;
use webfiori\entity\langs\Language;
use webfiori\conf\SiteConfig;
/**
 * Description of PageTest
 *
 * @author Ibrahim
 */
class PageTest extends TestCase{
    /**
     * @test
     */
    public function testBeforeRender00() {
        $this->assertNull(Page::beforeRender());
        Page::beforeRender('random');
        $this->assertNull(Page::beforeRender());
        Page::beforeRender(function(){});
        $this->assertTrue(is_callable(Page::beforeRender()));
        Page::reset();
        $this->assertNull(Page::beforeRender());
    }
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
        $this->assertEquals('ltr',Page::dir());
        $this->assertNull(Page::translation());
        $this->assertEquals('https://127.0.0.1/',Page::canonical());
    }
    /**
     * @test
     */
    public function testRender00() {
        Page::reset();
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
    public function testRender01() {
        Page::reset();
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
    public function testSetDescription00() {
        Page::reset();
        $this->assertFalse(Page::document()->getHeadNode()->hasMeta('description'));
        Page::description('Hello World Page.');
        $this->assertTrue(Page::document()->getHeadNode()->hasMeta('description'));
        $this->assertEquals('Hello World Page.',Page::description());
    }
    /**
     * @test
     * @depends testSetDescription00
     */
    public function testSetDescription01() {
        Page::description();
        $this->assertEquals('Hello World Page.',Page::description());
        Page::description('');
        $this->assertNull(Page::description());
        $this->assertFalse(Page::document()->getHeadNode()->hasMeta('description'));
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
    public function testDirs00() {
        Page::reset();
        $this->assertEquals('',Page::cssDir());
        $this->assertEquals('',Page::imagesDir());
        $this->assertEquals('',Page::jsDir());
    }
    /**
     * @test
     * @depends testDirs00
     */
    public function testDirs01() {
        Page::theme();
        $this->assertEquals('themes/webfiori/css',Page::cssDir());
        $this->assertEquals('themes/webfiori/images',Page::imagesDir());
        $this->assertEquals('themes/webfiori/js',Page::jsDir());
    }
    /**
     * @test
     */
    public function testTheme00() {
        Page::reset();
        $theme = Page::theme();
        $this->assertTrue($theme instanceof Theme);
        $this->assertEquals(SiteConfig::getBaseThemeName(),$theme->getName());
        $theme2 = Page::theme();
        $this->assertTrue($theme2 === $theme);
        $theme3 = Page::theme('Template Theme');
        $this->assertFalse($theme3 === $theme2);
        $theme4 = Page::theme('Template Theme');
        $this->assertTrue($theme3 === $theme4);
    }
    /**
     * @test
     */
    public function testTheme01() {
        Page::reset();
        $this->assertNull(Page::theme(''));
        $this->assertNull(Page::theme('    '));
    }
    /**
     * @test
     */
    public function testTheme02() {
        Page::reset();
        $theme3 = Page::theme('      Template Theme      ');
        $this->assertTrue($theme3 instanceof Theme);
    }
    /**
     * @test
     */
    public function testTheme03() {
        $firstThemeName = 'Template Theme';
        $secondThemeName = 'WebFiori Theme';
        Page::reset();
        $theme3 = Page::theme($firstThemeName);
        $fTheme = Page::theme();
        $this->assertTrue($theme3 === $fTheme);
        $this->assertEquals($firstThemeName,$fTheme->getName());
        $sTheme = Page::theme($secondThemeName);
        $this->assertTrue($sTheme === Page::theme());
        $this->assertEquals($secondThemeName,$sTheme->getName());
        $f2Theme = Page::theme($firstThemeName);
        $this->assertTrue($f2Theme === $fTheme);
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
    /**
     * @test
     */
    public function testHeader00() {
        Page::reset();
        $this->assertTrue(Page::header());
        $node = Page::document()->getChildByID('page-header');
        $this->assertTrue($node instanceof HTMLNode);
        $this->assertEquals(3,Page::document()->getBody()->childrenCount());
    }
    /**
     * @test
     */
    public function testHeader01() {
        Page::reset();
        $this->assertFalse(Page::header(false));
        $node = Page::document()->getChildByID('page-header');
        $this->assertNull($node);
        $this->assertEquals(2,Page::document()->getBody()->childrenCount());
    }
    /**
     * @test
     * @depends testHeader01
     */
    public function testHeader02() {
        $this->assertFalse(Page::header());
        $node = Page::document()->getChildByID('page-header');
        $this->assertNull($node);
        $this->assertEquals(2,Page::document()->getBody()->childrenCount());
        $this->assertTrue(Page::header(true));
        $node2 = Page::document()->getChildByID('page-header');
        $this->assertTrue($node2 instanceof HTMLNode);
        $this->assertEquals(3,Page::document()->getBody()->childrenCount());
        $this->assertEquals('page-header',Page::document()->getBody()->getChild(0)->getAttribute('id'));
    }
    /**
     * @test
     */
    public function testFooter00() {
        Page::reset();
        $this->assertTrue(Page::footer());
        $node = Page::document()->getChildByID('page-footer');
        $this->assertTrue($node instanceof HTMLNode);
        $this->assertEquals(3,Page::document()->getBody()->childrenCount());
    }
    /**
     * @test
     */
    public function testFooter01() {
        Page::reset();
        $this->assertFalse(Page::footer(false));
        $node = Page::document()->getChildByID('page-footer');
        $this->assertNull($node);
        $this->assertEquals(2,Page::document()->getBody()->childrenCount());
    }
    /**
     * @test
     * @depends testFooter01
     */
    public function testFooter02() {
        $this->assertFalse(Page::footer());
        $node = Page::document()->getChildByID('page-footer');
        $this->assertNull($node);
        $this->assertEquals(2,Page::document()->getBody()->childrenCount());
        $this->assertTrue(Page::footer(true));
        $node2 = Page::document()->getChildByID('page-footer');
        $this->assertTrue($node2 instanceof HTMLNode);
        $this->assertEquals(3,Page::document()->getBody()->childrenCount());
        $this->assertEquals('page-footer',Page::document()->getBody()->getChild(2)->getAttribute('id'));
    }
    /**
     * @test
     */
    public function testAside00() {
        Page::reset();
        $this->assertTrue(Page::aside());
        $node = Page::document()->getChildByID('side-content-area');
        $this->assertTrue($node instanceof HTMLNode);
        $this->assertEquals(2,Page::document()->getChildByID('page-body')->childrenCount());
    }
    /**
     * @test
     */
    public function testAside01() {
        Page::reset();
        $this->assertFalse(Page::aside(false));
        $node = Page::document()->getChildByID('side-content-area');
        $this->assertNull($node);
        $this->assertEquals(1,Page::document()->getChildByID('page-body')->childrenCount());
    }
    /**
     * @test
     * @depends testFooter01
     */
    public function testAside02() {
        $this->assertFalse(Page::aside());
        $node = Page::document()->getChildByID('side-content-area');
        $this->assertNull($node);
        $this->assertEquals(1,Page::document()->getChildByID('page-body')->childrenCount());
        $this->assertTrue(Page::aside(true));
        $node2 = Page::document()->getChildByID('side-content-area');
        $this->assertTrue($node2 instanceof HTMLNode);
        $this->assertEquals(2,Page::document()->getChildByID('page-body')->childrenCount());
        $this->assertEquals('side-content-area',Page::document()->getChildByID('page-body')->getChild(0)->getAttribute('id'));
    }
    /**
     * @test
     */
    public function testHead00() {
        Page::reset();
        $head00 = Page::document()->getHeadNode();
        Page::theme();
        $head01 = Page::document()->getHeadNode();
        $this->assertFalse($head00 === $head01);
    }
    /**
     * @test
     */
    public function testHead01() {
        Page::reset();
        Page::description('This is for testing.');
        $head00 = Page::document()->getHeadNode();
        Page::theme();
        $head01 = Page::document()->getHeadNode();
        $this->assertFalse($head00 === $head01);
        $this->assertEquals($head00->getMeta('description')->getAttribute('content'),
                $head01->getMeta('description')->getAttribute('content'));
    }
}
