<?php
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
        $uri = new RouterUri('/hello', 'x.php');
        $this->assertEquals(true,TRUE);
    }
}
