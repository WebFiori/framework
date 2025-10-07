<?php
namespace WebFiori\Framework\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use Themes\FioriTheme\NewFTestTheme;
use WebFiori\Framework\Access;
use WebFiori\Framework\App;
use WebFiori\Framework\Lang;
use WebFiori\Framework\Session\SessionsManager;
use WebFiori\Framework\ThemeManager;
use WebFiori\Framework\UI\WebPage;
use WebFiori\Framework\User;
use WebFiori\UI\HTMLNode;
/**
 * Description of PageTest
 *
 * @author Ibrahim
 */
class PageTest extends TestCase {
    
    protected function setUp(): void {
        parent::setUp();
        
        // Register test themes to avoid directory creation errors
        $registeredThemes = ThemeManager::getRegisteredThemes();
        $themeNames = array_keys($registeredThemes);
        
        if (!in_array('New Theme 2', $themeNames)) {
            ThemeManager::register(new \Themes\FioriTheme2\NewTestTheme2());
        }
        
        if (!in_array('New Super Theme', $themeNames)) {
            ThemeManager::register(new \themes\fioriTheme\NewFTestTheme());
        }
    }
    
    protected function tearDown(): void {
        // Clean up theme assets from public directory
        $assetsPath = ROOT_PATH . DS . PUBLIC_FOLDER . DS . 'assets';
        if (is_dir($assetsPath)) {
            // Only remove theme directories, not the entire assets folder
            $themeDirs = ['FioriTheme', 'FioriTheme2', 'NewTestTheme2', 'NewFTestTheme'];
            foreach ($themeDirs as $themeDir) {
                $themePath = $assetsPath . DS . $themeDir;
                if (is_dir($themePath)) {
                    $this->removeDirectory($themePath);
                }
            }
        }
        parent::tearDown();
    }
    
    private function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DS . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
    
    /**
     * @test
     */
    public function testAddCss00() {
        $page = new WebPage();
        $page->addCSS('assets/css/theme.css');
        $this->assertEquals(1, count($page->getDocument()->getHeadNode()->getCSSNodes()));
    }
    /**
     * @test
     */
    public function testAddCss01() {
        $page = new WebPage();
        $page->addCSS('assets/css/theme.css', [
            'id' => 'my-css'
        ]);
        $this->assertEquals(1, count($page->getDocument()->getHeadNode()->getCSSNodes()));
        $cssNode = $page->getChildByID('my-css');
        $this->assertEquals('assets/css/theme.css?cv=1.0', $cssNode->getAttributeValue('href'));
    }
    /**
     * @test
     */
    public function testAddCss02() {
        $page = new WebPage();
        $page->addCSS('assets/css/theme.css', [
            'id' => 'my-css',
            'revision' => false
        ]);
        $this->assertEquals(1, count($page->getDocument()->getHeadNode()->getCSSNodes()));
        $cssNode = $page->getChildByID('my-css');
        $this->assertEquals('assets/css/theme.css', $cssNode->getAttributeValue('href'));
    }
    /**
     * @test
     */
    public function testAddJs00() {
        $page = new WebPage();
        $page->addJS('assets/js/theme.js');
        $this->assertEquals(1, count($page->getDocument()->getHeadNode()->getJSNodes()));
    }
    /**
     * @test
     */
    public function testAddJs01() {
        $page = new WebPage();
        $page->addJS('assets/js/theme.js', [
            'id' => 'my-js'
        ]);
        $this->assertEquals(1, count($page->getDocument()->getHeadNode()->getJSNodes()));
        $cssNode = $page->getChildByID('my-js');
        $this->assertEquals('assets/js/theme.js?jv=1.0', $cssNode->getAttributeValue('src'));
    }
    /**
     * @test
     */
    public function testAddJs02() {
        $page = new WebPage();
        $page->addJS('assets/js/theme.js', [
            'id' => 'my-js',
            'revision' => false
        ]);
        $this->assertEquals(1, count($page->getDocument()->getHeadNode()->getJSNodes()));
        $cssNode = $page->getChildByID('my-js');
        $this->assertEquals('assets/js/theme.js', $cssNode->getAttributeValue('src'));
    }
    /**
     * @test
     */
    public function testAddMeta00() {
        $page = new WebPage();
        $page->addMeta('robots', 'index, follow');
        $this->assertEquals(2, count($page->getDocument()->getHeadNode()->getMetaNodes()));
        $this->assertEquals('index, follow', $page->getMetaVal('robots'));
    }
    /**
     * @test
     */
    public function testAddMeta01() {
        $page = new WebPage();
        $this->assertEquals('', $page->getMetaVal('robots'));
        $page->addMeta('robots', 'index, follow');
        $page->addMeta('robots', 'no-index');
        $this->assertEquals('index, follow', $page->getMetaVal('robots'));
        $page->addMeta('robots', 'no-index', true);
        $this->assertEquals('no-index', $page->getMetaVal('robots'));
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

        return $page;
    }
    /**
     * @test
     * @depends testAside01
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
    public function testBeforeRender00() {
        $page = new WebPage();
        $c = $page->addBeforeRender(function (WebPage $p, TestCase $c)
        {
            $c->assertTrue($p->getDocument()->hasChild('super-el'));
        },0,  [$this]);
        $this->assertNotNull($c);
        $this->assertEquals(0, $c->getPriority());

        $c2 = $page->addBeforeRender(function(WebPage $p, TestCase $c)
        {
            $ch = $p->insert('div');
            $ch->setID('super-el');
        }, 3, [$this]);
        $this->assertEquals(3, $c2->getPriority());
    }
    /**
     * @test
     */
    public function testBeforeRender01() {
        $page = new WebPage();
        $c = $page->addBeforeRender(function (WebPage $p, TestCase $c)
        {
            $c->assertTrue($p->getDocument()->getChildByID('super-el') !== null);
        },0,  [$this]);

        $c2 = $page->addBeforeRender(function(WebPage $p, TestCase $c)
        {
            $ch = $p->insert('div');
            $ch->setID('super-el');
        }, 3, [$this]);
        $this->assertNull($page->getDocument()->getChildByID('super-el'));
        $page->beforeRender();
        $this->assertNotNull($page->getDocument()->getChildByID('super-el'));
    }
    /**
     * @test
     */
    public function testBeforeRender02() {
        $page = new WebPage();
        $c = $page->addBeforeRender(function (WebPage $p, TestCase $c)
        {
            $c->assertTrue($p->getDocument()->getChildByID('super-el') === null);
        },0,  [$this]);

        $c2 = $page->addBeforeRender(function(WebPage $p, TestCase $c)
        {
            $ch = $p->insert('div');
            $ch->setID('super-el');
        }, 3, [$this]);
        $page->removeBeforeRender($c2->getID());
        $page->beforeRender();
        $this->assertNull($page->getDocument()->getChildByID('super-el'));
    }
    /**
     * @test
     */
    public function testBeforeRender03() {
        $page = new WebPage();
        $c = $page->addBeforeRender(function (WebPage $p, TestCase $c)
        {
            
            $p->addBeforeRender(function (WebPage $p, TestCase $c) {
                $c->assertTrue($p->getDocument()->getChildByID('super-el') === null);
            }, 4, [$this]);
            $p->addBeforeRender(function(WebPage $p, TestCase $c)
            {
                $ch = $p->insert('div');
                $ch->setID('super-el');
            }, 3, [$this]);
            $p->addBeforeRender(function (WebPage $p, TestCase $c) {
                $c->assertTrue($p->getDocument()->getChildByID('super-el') !== null);
            }, 2, [$this]);
            
        },0,  [$this]);

        $page->beforeRender();
        $this->assertNotNull($page->getDocument()->getChildByID('super-el'));
    }
    /**
     * @test
     */
    public function testBeforeRender04() {
        $page = new WebPage();

        $c2 = $page->addBeforeRender(function(WebPage $p, TestCase $c)
        {
            $ch = $p->insert('div');
            $ch->setID('super-el');
        }, 3, [$this]);
        
        $c2->setCallback(function(WebPage $p, TestCase $c)
        {
            $ch = $p->insert('div');
            $ch->setID('super-cool');
        }, [$this]);
        
        $page->beforeRender();
        $this->assertNull($page->getDocument()->getChildByID('super-el'));
        $this->assertNotNull($page->getDocument()->getChildByID('super-cool'));
    }
    /**
     * @test
     */
    public function testCanonical() {
        $page = new WebPage();
        $page->setCanonical('https://127.0.0.1/home');
        $c = $page->getCanonical();
        $this->assertEquals('https://127.0.0.1/home',$c);
        $this->assertEquals('https://127.0.0.1/home',$page->getDocument()->getHeadNode()->getCanonical());
    }
    /**
     * @test
     */
    public function testCreateHtmlNode00() {
        $page = new WebPage();
        $node = $page->createHTMLNode();
        $this->assertEquals('div', $node->getNodeName());
    }
    /**
     * @test
     */
    public function testCreateHtmlNode01() {
        $page = new WebPage();
        $node = $page->createHTMLNode([
            'name' => 'input',
            'attributes' => [
                'type' => 'text'
            ]
        ]);
        $this->assertEquals('input', $node->getNodeName());
        $this->assertEquals('text', $node->getAttribute('type'));
    }
    /**
     * @test
     */
    public function testCreateHtmlNode02() {
        $page = new WebPage();
        $page->setTheme(NewFTestTheme::class);
        $node = $page->createHTMLNode([
            'type' => 'section',
            'element-id' => 'super-sec'
        ]);
        $this->assertEquals('section', $node->getNodeName());
        $this->assertEquals('super-sec', $node->getChild(0)->getID());
        $el = $page->getChildByID('my-div');
        $this->assertEquals('My Name Is Super Hero', $el->getChild(0)->getText());
    }
    /**
     * @test
     */
    public function testCreateHtmlNode03() {
        $page = new WebPage();
        $node = $page->createHTMLNode();
        $node->setID('new-node');
        $this->assertEquals('div', $node->getNodeName());
        $this->assertEquals(0,$page->getChildByID('main-content-area')->childrenCount());
        $el = $page->getChildByID('new-node');
        $this->assertNull($el);
    }
    /**
     * @test
     */
    public function testHasPrivilege00() {
        $page = new WebPage();
        $this->assertFalse($page->hasPrivilege('A_TEST'));
    }
    /**
     * @test
     */
    public function testHasPrivilege01() {
        $page = new WebPage();
        SessionsManager::start('new');
        $this->assertFalse($page->hasPrivilege('A_TEST'));
    }
    /**
     * @test
     */
    public function testHasPrivilege02() {
        $page = new WebPage();
        SessionsManager::start('new');
        $user = new User();
        $user->addPrivilege('A_TEST');
        SessionsManager::getActiveSession()->setUser($user);
        $this->assertFalse($page->hasPrivilege('A_TEST'));
        Access::newGroup('Test');
        Access::newPrivilege('Test', 'A_TEST');
        $user->addPrivilege('A_TEST');
        $this->assertTrue($page->hasPrivilege('A_TEST'));
    }
    /**
     * @test
     */
    public function testGetSession00() {
        SessionsManager::destroy();
        $page = new WebPage();
        $this->assertNull($page->getActiveSession());
    }
    /**
     * @test
     */
    public function testGetSession01() {
        SessionsManager::start('test-session-1');
        $page = new WebPage();
        $session = $page->getActiveSession();
        $this->assertNotNull($session);
        $this->assertEquals('test-session-1', $session->getName());
        SessionsManager::destroy();
        $this->assertNull($page->getActiveSession());
    }
    /**
     * @test
     */
    public function testGetSession02() {
        SessionsManager::start('test-session-1');
        SessionsManager::start('test-session-2');
        $page = new WebPage();
        $session = $page->getActiveSession();
        $this->assertNotNull($session);
        $this->assertEquals('test-session-2', $session->getName());
        SessionsManager::destroy();
        $this->assertNull($page->getActiveSession());
    }
    /**
     * @test
     */
    public function testGetLabel00() {
        $page = new WebPage();
        $this->assertEquals('a/b/c', $page->get('a/b/c'));
    }
    /**
     * @test
     */
    public function testGetLabel01() {
        $page = new WebPage();
        $this->assertEquals('2', $page->get('hello/two'));
        $this->assertEquals([
            'cool' => 'Cool'
        ], $page->get('hello/one'));
    }
    /**
     * @test
     */
    public function testDefaults00() {
        $page = new WebPage();
        $this->assertEquals('EN',$page->getLangCode());
        $this->assertNull($page->getDescription());
        $this->assertEquals('Default',$page->getTitle());
        $this->assertEquals('Application',$page->getWebsiteName());
        $this->assertEquals(' | ',$page->getTitleSep());
        $this->assertTrue($page->hasHeader());
        $this->assertTrue($page->hasFooter());
        $this->assertTrue($page->hasAside());
        $this->assertEquals('ltr',$page->getWritingDir());
        $this->assertNotNull($page->getTranslation());
        $this->assertEquals('https://127.0.0.1/',$page->getCanonical());
        $this->assertEquals('https://127.0.0.1',$page->getBase());
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
        App::getConfig()->setTheme('');
        $page->setTheme();
        $this->assertEquals('',$page->getThemeCSSDir());
        $this->assertEquals('',$page->getThemeImagesDir());
        $this->assertEquals('',$page->getThemeJSDir());
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
    public function testHead00() {
        $page = new WebPage();
        $head00 = $page->getDocument()->getHeadNode();
        $page->setTheme('New Theme 2');
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
        $page->setTheme('New Theme 2');
        $head01 = $page->getDocument()->getHeadNode();
        $this->assertFalse($head00 === $head01);
        $this->assertEquals($head00->getMeta('description')->getAttribute('content'),
            $head01->getMeta('description')->getAttribute('content'));
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
    public function testInsert00() {
        $page = new WebPage();
        $node = new HTMLNode();
        $node->setID('new-node');
        $this->assertNotNull($page->insert($node));
        $this->assertEquals(1,$page->getChildByID('main-content-area')->childrenCount());
        $el = $page->getChildByID('new-node');
        $this->assertTrue($el === $node);
    }
    /**
     * @test
     */
    public function testInsert01() {
        $page = new WebPage();
        $node = new HTMLNode();
        $node->setID('new-node');
        $this->assertNull($page->insert($node,''));
        $this->assertEquals(0,$page->getChildByID('main-content-area')->childrenCount());
        $el = $page->getChildByID('new-node');
        $this->assertNull($el);
    }
    /**
     * @test
     */
    public function testInsert02() {
        $page = new WebPage();
        $node = new HTMLNode();
        $node->setID('new-node');
        $this->assertNull($page->insert($node,''));
        $this->assertEquals(0,$page->getChildByID('main-content-area')->childrenCount());
        $el = $page->getChildByID('new-node');
        $this->assertNull($el);
    }
    /**
     * @test
     */
    public function testRender00() {
        $_SERVER['HTTPS'] = null;
        $page = new WebPage();
        $doc = $page->render(false, true);
        $doc->removeChild($page->getChildByID('i18n'));
        $this->assertEquals('<!DOCTYPE html>'
                .'<html lang=EN>'
                .'<head>'
                .'<base href="http://127.0.0.1">'
                .'<title>Default | Application</title>'
                .'<link rel=canonical href="http://127.0.0.1/">'
                .'<meta name=viewport content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">'
                .'</head>'
                .'<body itemscope itemtype="http://schema.org/WebPage">'
                .'<div id="page-header">'
                .'</div>'
                .'<div id="page-body">'
                .'<div id="side-content-area"></div>'
                .'<div id="main-content-area"></div>'
                .'</div><div id="page-footer">'
                .'</div>'
                .'</body>'
                .'</html>',$doc.'');
    }
    /**
     * @test
     */
    public function testRender01() {
        $_SERVER['HTTPS'] = null;
        $page = new WebPage();
        $doc = $page->render(false, true);
        $doc->removeChild($page->getChildByID('i18n'));
        $this->assertEquals('<!DOCTYPE html>'
                .'<html lang=EN>'
                .'<head>'
                .'<base href="http://127.0.0.1">'
                .'<title>Default | Application</title>'
                .'<link rel=canonical href="http://127.0.0.1/">'
                .'<meta name=viewport content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">'
                .'</head>'
                .'<body itemscope itemtype="http://schema.org/WebPage">'
                .'<div id="page-header">'
                .'</div>'
                .'<div id="page-body">'
                .'<div id="side-content-area"></div>'
                .'<div id="main-content-area"></div>'
                .'</div><div id="page-footer">'
                .'</div>'
                .'</body>'
                .'</html>',$doc.'');
    }
    /**
     * @test
     */
    public function testReset00() {
        $_SERVER['HTTPS'] = 'yes';
        $page = new WebPage();
        $page->setTheme('New Super Theme');
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
        $this->assertEquals('Default',$page->getTitle());
        $this->assertEquals('Application',$page->getWebsiteName());
        $this->assertEquals(' | ',$page->getTitleSep());
        $this->assertTrue($page->hasAside());
        $this->assertTrue($page->hasFooter());
        $this->assertTrue($page->hasHeader());
        $this->assertEquals('ltr',$page->getWritingDir());
        $this->assertNotNull($page->getTranslation());
        $this->assertEquals('https://127.0.0.1/',$page->getCanonical());
    }
    /**
     * @test
     */
    public function testSetDescription00() {
        $page = new WebPage();
        $this->assertFalse($page->getDocument()->getHeadNode()->hasMeta('description'));
        $page->setDescription('Hello World Page.');
        $this->assertEquals('Hello World Page.',$page->getDescription());
        $this->assertTrue($page->getDocument()->getHeadNode()->hasMeta('description'));

        return $page;
    }
    /**
     * @test
     * @depends testSetDescription00
     */
    public function testSetDescription01(WebPage $page) {
        $this->assertEquals('Hello World Page.',$page->getDescription());
        $page->setDescription('  ');
        $this->assertNull($page->getDescription());
        $this->assertFalse($page->getDocument()->getHeadNode()->hasMeta('description'));
    }
    /**
     * @test
     */
    public function testTheme00() {
        App::getConfig()->setTheme('');
        $page = new WebPage();
        $page->setTheme();
        $theme = $page->getTheme();
        $this->assertNull($theme);

        App::getConfig()->setTheme('New Theme 2');

        $page->setTheme();
        $theme2 = $page->getTheme();
        $this->assertSame($theme2->getPage(), $page);
        $this->assertNotNull($page->getChildByID('theme-after-loaded-el'));
        $this->assertNotNull($theme2);
        $page->setTheme('New Theme 2');
        $theme3 = $page->getTheme();
        $this->assertTrue($theme3 === $theme2);
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
        $page->setTheme('      New Super Theme      ');
        $theme3 = $page->getTheme();
        
        $this->assertTrue($theme3 instanceof \WebFiori\framework\Theme);
    }
    /**
     * @test
     */
    public function testTheme03() {
        $firstThemeName = 'New Theme 2';
        $secondThemeName = 'New Super Theme';
        $page = new WebPage();

        $page->setTheme($firstThemeName);
        $theme3 = $page->getTheme();

        $page->setTheme($secondThemeName);
        $fTheme = $page->getTheme();
        
        $this->assertEquals($firstThemeName, $theme3->getName());
        $this->assertEquals($secondThemeName, $fTheme->getName());
        
        $this->assertFalse($theme3 === $fTheme);
        $this->assertNotEquals($firstThemeName,$fTheme->getName());
        $page->setTheme($secondThemeName);
        $sTheme = $page->getTheme();
        $this->assertTrue($sTheme === $page->getTheme());
        $this->assertEquals($secondThemeName,$sTheme->getName());
    }
    /**
     * @test
     */
    public function testUIFunctions00() {
        $page = new WebPage();
        $this->assertEquals($page->getTitle(), title());
        $page->setTitle('New One');
        $this->assertEquals($page->getTitle(), title());
    }
    /**
     * @test
     */
    public function testUsingLang00() {
        $page = new WebPage();
        $null = $page->getTranslation();
        $this->assertNotNull($null);
        $this->assertEquals('EN', $null->getCode());
    }
    /**
     * @test
     */
    public function testUsingLang01() {
        $page = new WebPage();
        $page->setLang('en');
        $lang = $page->getTranslation();
        $this->assertTrue($lang instanceof Lang);
        $lang2 = $page->getTranslation();
        $this->assertTrue($lang2 instanceof Lang);
        $this->assertTrue($lang === $lang2);
        $page->setLang('ar');
        $lang3 = $page->getTranslation();
        $this->assertTrue($lang3 instanceof Lang);
        $this->assertFalse($lang3 === $lang2);
    }
    /**
     * @test
     */
    public function testUsingLang02() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No language class was found for the language \'NM\'.');
        $page = new WebPage();
        $page->setLang('nm');
    }
    /**
     * @test
     */
    public function testUsingLang03() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The translation file was found. But no object of type \''.Lang::class.'\' is stored. Make sure that the parameter $addtoLoadedAfterCreate is set to true when creating the language object.');
        $page = new WebPage();
        $page->setLang('jp');
    }
}
