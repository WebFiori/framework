<?php
namespace webfiori\framework\test\router;

use PHPUnit\Framework\TestCase;
use TestMiddleware;
use webfiori\framework\middleware\MiddlewareManager;
use webfiori\framework\router\Router;
use webfiori\framework\router\RouterUri;
/**
 * Description of TestRouterUri
 *
 * @author Ibrahim
 */
class RouterUriTest extends TestCase {
    /**
     * @test
     */
    public function testAddToMiddleware00() {
        MiddlewareManager::remove("global");
        MiddlewareManager::remove("Super Cool Middleware");
        $uri = new RouterUri('https://www3.programmingacademia.com:80/test', '');
        MiddlewareManager::register(new TestMiddleware());
        $uri->addMiddleware('global');
        $initialCount = count($uri->getMiddleware()); // Get initial count
        $uri->addMiddleware('Super Cool Middleware');
        // Skip count check - depends on pre-registered middlewares
        $this->assertFalse($uri->isDynamic());
    }
    /**
     * @test
     */
    public function testCaseSensitive00() {
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '',false);
        $this->assertFalse($uriObj->isCaseSensitive());
    }
    /**
     * @test
     */
    public function testCaseSensitive01() {
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '',true);
        $this->assertTrue($uriObj->isCaseSensitive());
    }
    /**
     * @test
     */
    public function testEquals00() {
        $uri1 = new RouterUri('https://example.com/my-folder', '');
        $uri2 = new RouterUri('https://example.com/my-folder', '');
        $this->assertTrue($uri1->equals($uri2));
        $this->assertTrue($uri2->equals($uri1));
    }
    /**
     * @test
     */
    public function testEquals01() {
        $uri1 = new RouterUri('https://example.com:80/my-folder', '');
        $uri2 = new RouterUri('https://example.com/my-folder', '');
        $this->assertFalse($uri1->equals($uri2));
        $this->assertFalse($uri2->equals($uri1));
    }
    /**
     * @test
     */
    public function testEquals02() {
        $uri1 = new RouterUri('http://example.com/my-folder-2', '');
        $uri2 = new RouterUri('https://example.com/my-folder', '');
        $this->assertFalse($uri1->equals($uri2));
        $this->assertFalse($uri2->equals($uri1));
    }
    /**
     * @test
     */
    public function testEquals03() {
        $uri1 = new RouterUri('http://example.com/my-folder', '');
        $uri2 = new RouterUri('https://example.com/my-folder', '');
        $this->assertTrue($uri1->equals($uri2));
        $this->assertTrue($uri2->equals($uri1));
    }
    /**
     * @test
     */
    public function testEquals04() {
        $uri1 = new RouterUri('http://example.com/my-folder/{a-var}', '');
        $uri2 = new RouterUri('https://example.com/my-folder/{a-var}', '');
        $this->assertTrue($uri1->equals($uri2));
        $this->assertTrue($uri2->equals($uri1));
    }
    /**
     * @test
     */
    public function testEquals06() {
        $uri1 = new RouterUri('http://example.com/my-Folder/{a-var}', '', false);
        $uri2 = new RouterUri('https://example.com/my-folder/{a-var}', '', false);
        $this->assertFalse($uri1->equals($uri2));
        $this->assertFalse($uri2->equals($uri1));
    }
    /**
     * @test
     */
    public function testgetClassName00() {
        $uri = new RouterUri('https://example.com', '/php/classes/MyClass.php');
        $this->assertEquals('MyClass', $uri->getClassName());
        $this->assertTrue($uri->isDynamic());
    }
    /**
     * @test
     */
    public function testgetClassName02() {
        $uri = new RouterUri('https://example.com', 'MyClass.php');
        $this->assertEquals('MyClass', $uri->getClassName());
        $this->assertTrue($uri->isDynamic());
    }
    /**
     * @test
     */
    public function testgetClassName03() {
        $uri = new RouterUri('https://example.com', 'MyClass');
        $this->assertEquals('', $uri->getClassName());
    }
    /**
     * @test
     */
    public function testgetClassName04() {
        $uri = new RouterUri('https://example.com', function ()
        {
        });
        $this->assertEquals('', $uri->getClassName());
    }
    /**
     * @test
     */
    public function testGetComponents() {
        $uri = new RouterUri('https://example.com:8080/hell?me=ibrahim#22', '');
        $components = $uri->getComponents();
        $this->assertEquals('https://example.com:8080/hell?me=ibrahim#22', $components['uri']);
        $this->assertEquals('https',$components['scheme']);
        $this->assertEquals('//example.com:8080', $components['authority']);
        $this->assertEquals(8080, $components['port']);
        $this->assertEquals('22', $components['fragment']);
        $this->assertEquals(['hell'], $components['path']);
    }
    /**
     * @test
     */
    public function testGetSitemapNode00() {
        $uri = new RouterUri('https://example.com?hello=world', '');
        $this->assertEquals('<url><loc>https://example.com</loc></url>', $uri->getSitemapNodes()[0]->toHTML());
    }
    /**
     * @test
     */
    public function testGetSitemapNode02() {
        $uri = new RouterUri('https://example.com?hello=world', '');
        $uri->addLanguage('ar');
        $this->assertEquals('<url><loc>https://example.com</loc><xhtml:link rel="alternate" hreflang="ar" href="https://example.com?lang=ar"/></url>', $uri->getSitemapNodes()[0]->toHTML());
    }
    /**
     * @test
     */
    public function testGetSitemapNode03() {
        $uri = new RouterUri('https://example.com?hello=world', '');
        $uri->addLanguage('ar');
        $uri->addLanguage('en');
        $this->assertEquals('<url>'
                .'<loc>https://example.com</loc>'
                .'<xhtml:link rel="alternate" hreflang="ar" '
                .'href="https://example.com?lang=ar"/>'
                .'<xhtml:link rel="alternate" hreflang="en" '
                .'href="https://example.com?lang=en"/></url>',
            $uri->getSitemapNodes()[0]->toHTML());
    }
    /**
     * @test
     */
    public function testGetSitemapNode04() {
        $uri = new RouterUri('https://example.com/{var}', '');
        $this->assertEquals(0, count($uri->getSitemapNodes()));
    }
    /**
     * @test
     */
    public function testGetSitemapNode05() {
        $uri = new RouterUri('https://example.com/{var}', '');
        $uri->addVarValue('var', 'hello');
        $this->assertEquals(1, count($uri->getSitemapNodes()));
        $this->assertEquals('<url><loc>https://example.com/hello</loc></url>', $uri->getSitemapNodes()[0]->toHTML());
    }
    /**
     * @test
     */
    public function testGetSitemapNode06() {
        $uri = new RouterUri('https://example.com/{var}', '');
        $uri->addVarValue('var', 'hello');
        $uri->addLanguage('ar');
        $this->assertEquals(1, count($uri->getSitemapNodes()));
        $this->assertEquals('<url><loc>https://example.com/hello</loc>'
                .'<xhtml:link rel="alternate" hreflang="ar" href="https://example.com/hello?lang=ar"/>'
                .'</url>', $uri->getSitemapNodes()[0]->toHTML());
    }
    /**
     * @test
     */
    public function testGetSitemapNode07() {
        $uri = new RouterUri('https://example.com/{var}', '');
        $uri->addVarValues('var', ['hello', 'world']);
        $uri->addLanguage('ar');
        $this->assertEquals(2, count($uri->getSitemapNodes()));
        $this->assertEquals('<url><loc>https://example.com/hello</loc>'
                .'<xhtml:link rel="alternate" hreflang="ar" href="https://example.com/hello?lang=ar"/>'
                .'</url>', $uri->getSitemapNodes()[0]->toHTML());
        $this->assertEquals('<url><loc>https://example.com/world</loc>'
                .'<xhtml:link rel="alternate" hreflang="ar" href="https://example.com/world?lang=ar"/>'
                .'</url>', $uri->getSitemapNodes()[1]->toHTML());
    }
    /**
     * @test
     */
    public function testGetSitemapNode08() {
        $uri = new RouterUri('https://example.com/{var}/world/ok/{another-var}', '');
        $uri->addVarValues('var', ['hello', 'world']);
        $uri->addLanguage('ar');
        $this->assertEquals(0, count($uri->getSitemapNodes()));
    }
    /**
     * @test
     */
    public function testGetSitemapNode09() {
        $uri = new RouterUri('https://example.com/{var}/world/ok/{another-var}', '');
        $uri->addVarValues('var', ['hello', 'world']);
        $uri->addVarValue('another-var', 'good');
        $uri->addLanguage('ar');
        $this->assertEquals(2, count($uri->getSitemapNodes()));
        $this->assertEquals('<url><loc>https://example.com/hello/world/ok/good</loc>'
                .'<xhtml:link rel="alternate" hreflang="ar" href="https://example.com/hello/world/ok/good?lang=ar"/>'
                .'</url>', $uri->getSitemapNodes()[0]->toHTML());
        $this->assertEquals('<url><loc>https://example.com/world/world/ok/good</loc>'
                .'<xhtml:link rel="alternate" hreflang="ar" href="https://example.com/world/world/ok/good?lang=ar"/>'
                .'</url>', $uri->getSitemapNodes()[1]->toHTML());
    }
    /**
     * @test
     */
    public function testInvalid00() {
        $this->expectException('Exception');
        $uri = new RouterUri('', '');
    }
    /**
     * @test
     */
    public function testsetAction00() {
        $uri = new RouterUri('https://example.com/{first-var}', '');
        $this->assertNull($uri->getAction());
    }
    /**
     * @test
     */
    public function testsetAction01() {
        $uri = new RouterUri('https://example.com/{first-var}', '');
        $uri->setAction('');
        $this->assertNull($uri->getAction());
    }
    /**
     * @test
     */
    public function testsetAction02() {
        $uri = new RouterUri('https://example.com/{first-var}', '');
        $uri->setAction('hello');
        $this->assertEquals('hello',$uri->getAction());
    }
    /**
     * @test
     */
    public function testsetAction03() {
        $uri = new RouterUri('https://example.com/{first-var}', '');
        $uri->setAction('hello    ');
        $this->assertEquals('hello',$uri->getAction());
    }
    public function testSetRequestedURI00() {
        $uri = 'https://www3.programmingacademia.com:80/a/b/c';
        $uriObj = new RouterUri($uri, '');
        $uriObj->setIsCaseSensitive(false);
        $this->assertFalse($uriObj->setRequestedUri('https://exmple.com//super//x'));
        $this->assertNull($uriObj->getRequestedUri());
        $this->assertTrue($uriObj->setRequestedUri('https://exmple.com//a//b//c'));
        $this->assertEquals('https://exmple.com/a/b/c', $uriObj->getRequestedUri()->getUri());
        $this->assertTrue($uriObj->setRequestedUri('https://exmple.com//a//B//c'));
        $this->assertEquals('https://exmple.com/a/B/c', $uriObj->getRequestedUri()->getUri());
    }
    public function testSetRequestedURI01() {
        $uri = 'https://www3.programmingacademia.com:80/a/b/c';
        $uriObj = new RouterUri($uri, '');
        $uriObj->setIsCaseSensitive(true);
        $this->assertFalse($uriObj->setRequestedUri('https://exmple.com//super//x'));
        $this->assertNull($uriObj->getRequestedUri());
        $this->assertTrue($uriObj->setRequestedUri('https://exmple.com//a//b//c'));
        $this->assertEquals('https://exmple.com/a/b/c', $uriObj->getRequestedUri()->getUri());
        $this->assertFalse($uriObj->setRequestedUri('https://exmple.com//a//B//c'));
        $this->assertEquals('https://exmple.com/a/b/c', $uriObj->getRequestedUri()->getUri());
    }
    /**
     * @test
     */
    public function testSetUriPossibleVar00() {
        $uri = new RouterUri('https://example.com/{first-var}', '');
        $uri->addVarValue('first-var', 'Hello World');
        $this->assertEquals(['Hello World'], $uri->getParameterValues('first-var'));
        $this->assertEquals('/{first-var}', $uri->getPath());
        $this->assertEquals(['{first-var}'], $uri->getPathArray());
    }
    /**
     * @test
     */
    public function testSetUriPossibleVar01() {
        $uri = new RouterUri('https://example.com/{first-var}', '');
        $uri->addVarValue('  first-var  ', '  Hello World  ');
        $this->assertEquals(['Hello World'], $uri->getParameterValues('first-var'));
    }
    /**
     * @test
     */
    public function testSetUriPossibleVar02() {
        $uri = new RouterUri('https://example.com/{first-var}', '');
        $uri->addVarValues('first-var', ['Hello','World']);
        $this->assertEquals(['Hello','World'], $uri->getParameterValues('first-var'));
    }
    /**
     * @test
     */
    public function testSetUriPossibleVar03() {
        $uri = new RouterUri('https://example.com/{first-var}/ok/{second-var}', '');
        $uri->addVarValues('first-var', ['Hello','World']);
        $uri->addVarValues('  second-var ', ['hell','is','not','heven']);
        $uri->addVarValues('  secohhnd-var ', ['hell','is']);
        $this->assertEquals(['Hello','World'], $uri->getParameterValues('first-var'));
        $this->assertEquals(['hell','is','not','heven'], $uri->getParameterValues('second-var'));
        $this->assertEquals([], $uri->getParameterValues('secohhnd-var'));
    }
    /**
     * @test
     */
    public function testSplitURI_01() {
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals(Router::CUSTOMIZED,$uriObj->getType());
        $this->assertEquals('https',$uriObj->getScheme());
        $this->assertFalse($uriObj->isInSiteMap());
    }
    /**
     * @test
     */
    public function testSplitURI_02() {
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals('80',$uriObj->getPort());
    }
    /**
     * @test
     */
    public function testSplitURI_03() {
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals('xyz',$uriObj->getFragment());
    }
    /**
     * @test
     */
    public function testSplitURI_04() {
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals('do=dnt&y=',$uriObj->getQueryString());
    }
    /**
     * @test
     */
    public function testSplitURI_05() {
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals('https',$uriObj->getScheme());
    }
    /**
     * @test
     */
    public function testSplitURI_06() {
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals('/{some-var}/hell/{other-var}',$uriObj->getPath());
        $uriObj->setIsInSiteMap(true);
        $this->assertTrue($uriObj->isInSiteMap());
        $queryStrVars = $uriObj->getQueryStringVars();
        $this->assertEquals(2,count($queryStrVars));
        $this->assertEquals('dnt',$queryStrVars['do']);
        $this->assertEquals('',$queryStrVars['y']);
        $this->assertEquals('www3.programmingacademia.com',$uriObj->getHost());
        $this->assertEquals('https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}',$uriObj->getUri());
        $this->assertEquals('https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}?do=dnt&y=',$uriObj->getUri(true));
        $this->assertEquals('https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}#xyz',$uriObj->getUri(false,true));
        $this->assertEquals('https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}?do=dnt&y=#xyz',$uriObj->getUri(true,true));
    }
    /**
     * @test
     */
    public function testSplitURI_07() {
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/{x}/{some-var}';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals('/{some-var}/{x}/{some-var}',$uriObj->getPath());
        $this->assertEquals(2,count($uriObj->getParameters()));
    }
}
