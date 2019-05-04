<?php
namespace webfiori\tests\functions;
use PHPUnit\Framework\TestCase;
use webfiori\functions\Functions;
use webfiori\entity\DBConnectionInfo;
use webfiori\WebFiori;
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
    /**
     * @test
     */
    public function testSetConnection01() {
        $connection = new DBConnectionInfo('root', '123456', 'test_db');
        WebFiori::getConfig()->addDbConnection('test-connection', $connection);
        $func = new Functions();
        $result = $func->setConnection('test-connection');
        $this->assertTrue($result);
    }
}
