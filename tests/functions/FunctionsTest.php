<?php
namespace webfiori\tests\functions;
use PHPUnit\Framework\TestCase;
use webfiori\functions\Functions;
use webfiori\entity\DBConnectionInfo;
use webfiori\WebFiori;
use webfiori\tests\entity\TestQuery_1;
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
        $connection->setConnectionName('test-connection');
        WebFiori::getConfig()->addDbConnection($connection);
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
        $connection->setConnectionName('test-connection');
        WebFiori::getConfig()->addDbConnection($connection);
        $func = new Functions();
        $result = $func->useDatabase('test-connection');
        $this->assertFalse($result);
        $errDetails = $func->getDBErrDetails();
        $this->assertEquals(1045,$errDetails['error-code']);
        $this->assertEquals("Access denied for user 'root'@'localhost' (using password: YES)",$errDetails['error-message']);
    }
    /**
     * @test
     */
    public function testUseDatabase02() {
        $connection = new DBConnectionInfo('root', '123456', 'test_db');
        $connection->setConnectionName('test-connection');
        WebFiori::getConfig()->addDbConnection($connection);
        $func = new Functions();
        $this->assertTrue($func->setConnection('test-connection'));
        $result = $func->useDatabase();
        if($result === false){
            \webfiori\entity\Util::print_r($func->getDBErrDetails());
        }
        $this->assertTrue($result);
    }
    /**
     * @test
     */
    public function testSetQuery00() {
        $func = new Functions();
        $this->assertFalse($func->setQueryObject(null));
        $this->assertFalse($func->setQueryObject(44));
        $this->assertFalse($func->setQueryObject($func));
        $this->assertNull($func->getQueryObject());
    }
    /**
     * @test
     */
    public function testSetQuery01() {
        $func = new Functions();
        $q = new TestQuery_1();
        $this->assertTrue($func->setQueryObject($q));
        $this->assertTrue($func->getQueryObject() instanceof TestQuery_1);
    }
}
