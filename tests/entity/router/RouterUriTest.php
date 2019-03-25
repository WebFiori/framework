<?php
declare(strict_types=1);
namespace webfiori\tests\entity\router;
use PHPUnit\Framework\TestCase;
use webfiori\entity\router\RouterUri;
/**
 * Description of TestRouterUri
 *
 * @author Eng.Ibrahim
 */
class RouterUriTest extends TestCase{
    /**
     * @test
     */
    public function test1(){
        $uri = RouterUri::splitURI('');
        print_r($uri);
        $this->assertEquals(true,TRUE);
    }
}
