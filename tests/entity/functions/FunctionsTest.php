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
        $connection = new DBConnectionInfo('root', '123456', 'testing_db');
        WebFiori::getConfig()->addDbConnection('test-connection', $connection);
        $func = new Functions();
        $result = $func->setConnection('test-connection');
        $this->assertTrue($result);
    }
    /**
     * @test
     */
    public function testUseDatabase00() {
        $func = new Functions();
        $result = $func->useDatabase('not-exist');
        $this->assertEquals(Functions::NO_SUCH_CONNECTION,$result);
        $errDetails = $func->getDBErrDetails();
        $this->assertEquals(-1,$errDetails['error-code']);
        $this->assertEquals('No database connection was found which has the name \'not-exist\'.',$errDetails['error-message']);
    }
    /**
     * @test
     */
    public function testUseDatabase01() {
        $connection = new DBConnectionInfo('root', '12345', 'testing_db');
        WebFiori::getConfig()->addDbConnection('test-connection', $connection);
        $func = new Functions();
        $result = $func->useDatabase('test-connection');
        $this->assertFalse($result);
        $errDetails = $func->getDBErrDetails();
        $this->assertEquals(1045,$errDetails['error-code']);
        $this->assertEquals("Access denied for user 'root'@'localhost' (using password: YES)",$errDetails['error-message']);
    }
}
