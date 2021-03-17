<?php
namespace webfiori\tests\entity;
use PHPUnit\Framework\TestCase;
use webfiori\framework\Page;
use webfiori\ui\HTMLNode;
use webfiori\framework\Theme;
use webfiori\framework\i18n\Language;
use webfiori\framework\WebFioriApp;
use webfiori\framework\ui\WebPage;
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
        $this->assertNull(Page::beforeRender('random'));
        $this->assertNull(Page::beforeRender());
        $this->assertEquals(1,Page::beforeRender(function(){}));
        $page = new WebPage();
        $this->assertNull(Page::beforeRender());
    }
    /**
     * @test
     */
    public function testDefaults00() {
        $page = new WebPage();
        $this->assertEquals('EN',$page->getLangCode());
        $this->assertNull($page->getDescription());
        $this->assertEquals('Hello World',$page->getTitle());
        $this->assertEquals('WebFiori',$page->getWebsiteName());
        $this->assertEquals(' | ',$page->getTitleSep());
        $this->assertTrue($page->hasHeader());
        $this->assertTrue($page->hasFooter());
        $this->assertTrue($page->hasAside());
        $this->assertEquals('ltr',$page->getWritingDir());
        $this->assertNotNull($page->getTranslation());
        $this->assertEquals('https://example.com/',$page->getCanonical());
    }
    /**
     * @test
     */
    public function testRender00() {
        $page = new WebPage();
        $doc = $page->render(false, true);
        $doc->removeChild($page->getChildByID('i18n'));
        $this->assertEquals('<!DOCTYPE html>'
                . '<html lang="EN">'
                . '<head>'
                . '<base href="https://example.com">'
                . '<title>Hello World | WebFiori</title>'
                . '<link rel="canonical" href="https://example.com/">'
                . '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">'
                . '</head>'
                . '<body itemscope itemtype="http://schema.org/WebPage">'
                . '<div id="page-header">'
                . '</div>'
                . '<div id="page-body">'
                . '<div id="side-content-area"></div>'
                . '<div id="main-content-area"></div>'
                . '</div><div id="page-footer">'
                . '</div>'
                . '</body>'
                . '</html>',$doc.'');
    }
    /**
     * @test
     */
    public function testRender01() {
        $page = new WebPage();
        $doc =$page->render(false, true);
        $doc->removeChild($page->getChildByID('i18n'));
        $this->assertEquals('<!DOCTYPE html>'
                . '<html lang="EN">'
                . '<head>'
                . '<base href="https://example.com">'
                . '<title>Hello World | WebFiori</title>'
                . '<link rel="canonical" href="https://example.com/">'
                . '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">'
                . '</head>'
                . '<body itemscope itemtype="http://schema.org/WebPage">'
                . '<div id="page-header">'
                . '</div>'
                . '<div id="page-body">'
                . '<div id="side-content-area"></div>'
                . '<div id="main-content-area"></div>'
                . '</div><div id="page-footer">'
                . '</div>'
                . '</body>'
                . '</html>',$doc.'');
    }
    /**
     * @test
     */
    public function testSetDescription00() {
        $page = new WebPage();
        $this->assertFalse($page->getDocument()->getHeadNode()->hasMeta('description'));
        $page->setDescription('Hello World Page.');
        $this->assertTrue($page->getDocument()->getHeadNode()->hasMeta('description'));
        $this->assertEquals('Hello World Page.',$page->getDescription());
        return $page;
    }
    /**
     * @test
     * @depends testSetDescription00
     */
    public function testSetDescription01(WebPage $page) {
        $this->assertEquals('Hello World Page.',$page->getDescription());
        $page->setDescription('');
        $this->assertNull($page->getDescription());
        $this->assertFalse($page->getDocument()->getHeadNode()->hasMeta('description'));
    }
    /**
     * @test
     */
    public function testReset00() {
        $page = new WebPage();
        $page->setTheme('WebFiori Theme');
        $page->setDescription('This is a test page.');
        $page->setLang('ar');
        $page->setTitle('Login');
        $page->setWebsiteName('Small ERP');
        $page->setTitleSep('-');
        $page->setHasHeader(false);
        $page->setHasFooter(false);
        $page->setHasAside(false);
        
        $this->assertEquals('AR',$page->getLangCode());
        $this->assertEquals('This is a test page.',$page->getDescription());
        $this->assertEquals('Login',$page->getTitle());
        $this->assertEquals('Small ERP',$page->getWebsiteName());
        $this->assertEquals(' - ',$page->getTitleSep());
        $this->assertFalse($page->hasHeader());
        $this->assertFalse($page->hasFooter());
        $this->assertFalse($page->hasAside());
        $this->assertNotNull($page->getTheme());
        $this->assertEquals('rtl',$page->getWritingDir());
        $this->assertNotNull($page->getTranslation());
        
        $page = new WebPage();
        $this->assertEquals('EN',$page->getLangCode());
        $this->assertNull($page->getDescription());
        $this->assertEquals('Hello World',$page->getTitle());
        $this->assertEquals('WebFiori',$page->getWebsiteName());
        $this->assertEquals(' | ',$page->getTitleSep());
        $this->assertTrue($page->hasAside());
        $this->assertTrue($page->hasFooter());
        $this->assertTrue($page->hasHeader());
        $this->assertEquals('ltr',$page->getWritingDir());
        $this->assertNotNull($page->getTranslation());
        $this->assertEquals('https://example.com/',$page->getCanonical());
    }
    /**
     * @test
     */
    public function testCanonical() {
        $page = new WebPage();
        $page->setCanonical('https://example.com/home');
        $c = $page->getCanonical();
        $this->assertEquals('https://example.com/home',$c);
        $this->assertEquals('https://example.com/home',$page->getDocument()->getHeadNode()->getCanonical());
    }
    /**
     * @test
     */
    public function testDirs00() {
        $page = new WebPage();
        $this->assertEquals('',$page->getThemeCSSDir());
        $this->assertEquals('',$page->getThemeJSDir());
        $this->assertEquals('',$page->getThemeImagesDir());
    }
    /**
     * @test
     * @depends testDirs00
     */
    public function testDirs01() {
        $page = new WebPage();
        $page->setTheme();
        $this->assertEquals('assets/webfiori-v1.0.8/css',$page->getThemeCSSDir());
        $this->assertEquals('assets/webfiori-v1.0.8/images',$page->getThemeImagesDir());
        $this->assertEquals('assets/webfiori-v1.0.8/js',$page->getThemeJSDir());
    }
    /**
     * @test
     */
    public function testTheme00() {
        $page = new WebPage();
        $page->setTheme();
        $theme = $page->getTheme();
        $this->assertTrue($theme instanceof Theme);
        $this->assertEquals(WebFioriApp::getAppConfig()->getBaseThemeName(), get_class($theme));
        $page->setTheme(get_class($theme));
        $theme2 = $page->getTheme();
        $this->assertTrue($theme2 === $theme);
        $page->setTheme('Template Theme');
        $theme3 = $page->getTheme();
        $this->assertFalse($theme3 === $theme2);
        $page->setTheme('Template Theme');
        $theme4 = $page->getTheme('Template Theme');
        $this->assertTrue($theme3 === $theme4);
    }
    /**
     * @test
     */
    public function testTheme01() {
        $page = new WebPage();
        $page->setTheme('');
        $this->assertNull($page->getTheme());
        $page->setTheme('      ');
        $this->assertNull($page->getTheme());
    }
    /**
     * @test
     */
    public function testTheme02() {
        $page = new WebPage();
        $page->setTheme('      Template Theme      ');
        $theme3 = $page->getTheme();
        $this->assertTrue($theme3 instanceof Theme);
    }
    /**
     * @test
     */
    public function testTheme03() {
        $firstThemeName = 'Template Theme';
        $secondThemeName = 'WebFiori Theme';
        $page = new WebPage();
        $page->setTheme($firstThemeName);
        $theme3 = $page->getTheme();
        $page->setTheme();
        $fTheme = $page->getTheme();
        $this->assertTrue($theme3 === $fTheme);
        $this->assertEquals($firstThemeName,$fTheme->getName());
        $page->setTheme($secondThemeName);
        $sTheme = $page->getTheme();
        $this->assertTrue($sTheme === Page::theme());
        $this->assertEquals($secondThemeName,$sTheme->getName());
        $page->setTheme($firstThemeName);
        $f2Theme = $page->getTheme();
        $this->assertTrue($f2Theme === $fTheme);
    }
    /**
     * @test
     */
    public function testInsert00() {
        $page = new WebPage();
        $node = new HTMLNode();
        $node->setID('new-node');
        $this->assertNotNull(Page::insert($node));
        $this->assertEquals(1,Page::document()->getChildByID('main-content-area')->childrenCount());
        $el = Page::document()->getChildByID('new-node');
        $this->assertTrue($el === $node);
    }
    /**
     * @test
     */
    public function testInsert01() {
        $page = new WebPage();
        $node = new HTMLNode();
        $node->setID('new-node');
        $this->assertNull(Page::insert($node,''));
        $this->assertEquals(0,Page::document()->getChildByID('main-content-area')->childrenCount());
        $el = Page::document()->getChildByID('new-node');
        $this->assertNull($el);
    }
    /**
     * @test
     */
    public function testUsingLang00() {
        $page = new WebPage();
        $null = Page::translation();
        $this->assertNotNull($null);
        $this->assertEquals('EN', $null->getCode());
    }
    /**
     * @test
     */
    public function testUsingLang01() {
        $page = new WebPage();
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
        $page = new WebPage();
        Page::lang('nm');
        Page::translation();
    }
    /**
     * @test
     */
    public function testUsingLang03() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The translation file was found. But no object of type \'Language\' is stored. Make sure that the parameter $addtoLoadedAfterCreate is set to true when creating the language object.');
        $page = new WebPage();
        $page->setLang('jp');
    }
    /**
     * @test
     */
    public function testHeader00() {
        $page = new WebPage();
        $this->assertTrue($page->hasHeader());
        $node = $page->getChildByID('page-header');
        $this->assertTrue($node instanceof HTMLNode);
        $this->assertEquals(3,$page->getDocument()->getBody()->childrenCount());
    }
    /**
     * @test
     */
    public function testHeader01() {
        $page = new WebPage();
        $page->setHasHeader(false);
        $this->assertFalse($page->hasHeader());
        $node = $page->getChildByID('page-header');
        $this->assertNull($node);
        $this->assertEquals(2,$page->getDocument()->getBody()->childrenCount());
    
        return $page;
    }
    /**
     * @test
     * @depends testHeader01
     */
    public function testHeader02(WebPage $page) {
        $this->assertFalse($page->hasHeader());
        $node = $page->getChildByID('page-header');
        $this->assertNull($node);
        $this->assertEquals(2,$page->getDocument()->getBody()->childrenCount());
        $page->setHasHeader(true);
        $this->assertTrue($page->hasHeader());
        $node2 = $page->getChildByID('page-header');
        $this->assertTrue($node2 instanceof HTMLNode);
        $this->assertEquals(3,$page->getDocument()->getBody()->childrenCount());
        $this->assertEquals('page-header',$page->getDocument()->getBody()->getChild(0)->getAttribute('id'));
    }
    /**
     * @test
     */
    public function testFooter00() {
        $page = new WebPage();
        $this->assertTrue($page->hasFooter());
        $node = $page->getChildByID('page-footer');
        $this->assertTrue($node instanceof HTMLNode);
        $this->assertEquals(3,$page->getDocument()->getBody()->childrenCount());
    }
    /**
     * @test
     */
    public function testFooter01() {
        $page = new WebPage();
        $page->setHasFooter(false);
        $this->assertFalse($page->hasFooter());
        $node = $page->getChildByID('page-footer');
        $this->assertNull($node);
        $this->assertEquals(2,$page->getDocument()->getBody()->childrenCount());
        return $page;
    }
    /**
     * @test
     * @depends testFooter01
     */
    public function testFooter02(WebPage $page) {
        $this->assertFalse($page->hasFooter());
        $node = $page->getChildByID('page-footer');
        $this->assertNull($node);
        $this->assertEquals(2,$page->getDocument()->getBody()->childrenCount());
        $page->setHasFooter(true);
        $this->assertTrue($page->hasFooter());
        $node2 = $page->getChildByID('page-footer');
        $this->assertTrue($node2 instanceof HTMLNode);
        $this->assertEquals(3,$page->getDocument()->getBody()->childrenCount());
        $this->assertEquals('page-footer',$page->getDocument()->getBody()->getChild(2)->getAttribute('id'));
    }
    /**
     * @test
     */
    public function testAside00() {
        $page = new WebPage();
        $this->assertTrue($page->hasAside());
        $node = $page->getChildByID('side-content-area');
        $this->assertTrue($node instanceof HTMLNode);
        $this->assertEquals(2,$page->getChildByID('page-body')->childrenCount());
    }
    /**
     * @test
     */
    public function testAside01() {
        $page = new WebPage();
        $page->setHasAside(false);
        $this->assertFalse($page->hasAside());
        $node = $page->getChildByID('side-content-area');
        $this->assertNull($node);
        $this->assertEquals(1,$page->getChildByID('page-body')->childrenCount());
    }
    /**
     * @test
     * @depends testFooter01
     */
    public function testAside02(WebPage $page) {
        $this->assertFalse($page->hasAside());
        $node = $page->getChildByID('side-content-area');
        $this->assertNull($node);
        $this->assertEquals(1,$page->getChildByID('page-body')->childrenCount());
        $page->setHasAside(true);
        $this->assertTrue($page->hasAside());
        $node2 = $page->getChildByID('side-content-area');
        $this->assertTrue($node2 instanceof HTMLNode);
        $this->assertEquals(2,$page->getChildByID('page-body')->childrenCount());
        $this->assertEquals('side-content-area',$page->getChildByID('page-body')->getChild(0)->getAttribute('id'));
    }
    /**
     * @test
     */
    public function testHead00() {
        $page = new WebPage();
        $head00 = $page->getDocument()->getHeadNode();
        $page->setTheme();
        $head01 = $page->getDocument()->getHeadNode();
        $this->assertFalse($head00 === $head01);
    }
    /**
     * @test
     */
    public function testHead01() {
        $page = new WebPage();
        $page->setDescription('This is for testing.');
        $head00 = $page->getDocument()->getHeadNode();
        $page->setTheme();
        $head01 = $page->getDocument()->getHeadNode();
        $this->assertFalse($head00 === $head01);
        $this->assertEquals($head00->getMeta('description')->getAttribute('content'),
                $head01->getMeta('description')->getAttribute('content'));
    }
}
