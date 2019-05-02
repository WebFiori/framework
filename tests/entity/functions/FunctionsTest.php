<?php
namespace webfiori\tests\functions;
use PHPUnit\Framework\TestCase;
use webfiori\functions\Functions;
/**
 * Description of FunctionsTest
 *
 * @author Ibrahim
 */
class FunctionsTest extends TestCase{
    /**
     * @test
     */
    public function testSetConnection00() {
        $func = new Functions();
        $result = $func->setConnection('not_exist');
        $this->assertEquals(Functions::NO_SUCH_CONNECTION,$result);
    }
}
