<?php
namespace webfiori\tests\entity\router;
use PHPUnit\Framework\TestCase;
use webfiori\entity\router\RouterUri;
use webfiori\entity\router\Router;
/**
 * Description of TestRouterUri
 *
 * @author Eng.Ibrahim
 */
class RouterUriTest extends TestCase{
    /**
     * @test
     */
    public function testSplitURI_01(){
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals(Router::CUSTOMIZED.$uriObj->getType());
        $this->assertEquals('https',$uriObj->getScheme());
        $this->assertFalse($uriObj->isInSiteMap());
    }
    /**
     * @test
     */
    public function testSplitURI_02(){
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals('80',$uriObj->getPort());
    }
    /**
     * @test
     */
    public function testSplitURI_03(){
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals('xyz',$uriObj->getFragment());
    }
    /**
     * @test
     */
    public function testSplitURI_04(){
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals('do=dnt&y=',$uriObj->getQueryString());
    }
    /**
     * @test
     */
    public function testSplitURI_05(){
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals('https',$uriObj->getScheme());
    }
    /**
     * @test
     */
    public function testSplitURI_06(){
        $uri = 'https://www3.programmingacademia.com:80/{some-var}/hell/{other-var}/?do=dnt&y=#xyz';
        $uriObj = new RouterUri($uri, '');
        $this->assertEquals('/{some-var}/hell/{other-var}',$uriObj->getPath());
        $uriObj->setIsInSiteMap(true);
        $this->assertTrue($uriObj->isInSiteMap());
    }
}
