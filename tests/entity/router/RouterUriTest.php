<?php
namespace webfiori\tests\entity\router;

use PHPUnit\Framework\TestCase;
use webfiori\entity\router\Router;
use webfiori\entity\router\RouterUri;
/**
 * Description of TestRouterUri
 *
 * @author Eng.Ibrahim
 */
class RouterUriTest extends TestCase {
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
    public function testgetClassName00() {
        $uri = new RouterUri('', '/php/classes/MyClass.php');
        $this->assertEquals('MyClass', $uri->getClassName());
    }
    /**
     * @test
     */
    public function testgetClassName02() {
        $uri = new RouterUri('', 'MyClass.php');
        $this->assertEquals('MyClass', $uri->getClassName());
    }
    /**
     * @test
     */
    public function testgetClassName03() {
        $uri = new RouterUri('', 'MyClass');
        $this->assertEquals('', $uri->getClassName());
    }
    /**
     * @test
     */
    public function testgetClassName04() {
        $uri = new RouterUri('', function () {});
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
    public function testEquals06() {
        $uri1 = new RouterUri('http://example.com/my-Folder/{a-var}', '', false);
        $uri2 = new RouterUri('https://example.com/my-folder/{a-var}', '', false);
        $this->assertFalse($uri1->equals($uri2));
        $this->assertFalse($uri2->equals($uri1));
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
        $this->assertEquals(2,count($uriObj->getUriVars()));
    }
}
