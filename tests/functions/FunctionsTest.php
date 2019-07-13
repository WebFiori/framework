<?php
namespace webfiori\tests\functions;
use PHPUnit\Framework\TestCase;
use webfiori\functions\Functions;
use webfiori\entity\DBConnectionInfo;
use webfiori\WebFiori;
use webfiori\tests\entity\TestQuery_1;
use webfiori\entity\SessionManager;
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
//    public function testUseDatabase01() {
//        $connection = new DBConnectionInfo('root', '12345', 'testing_db');
//        $connection->setConnectionName('test-connection');
//        WebFiori::getConfig()->addDbConnection($connection);
//        $func = new Functions();
//        $result = $func->useDatabase('test-connection');
//        $this->assertFalse($result);
//        $errDetails = $func->getDBErrDetails();
//        $this->assertEquals(1045,$errDetails['error-code']);
//        $this->assertEquals("Access denied for user 'root'@'localhost' (using password: YES)",$errDetails['error-message']);
//    }
    /**
     * @test
     */
    public function testUseDatabase02() {
        $connection = new DBConnectionInfo('root', '123456', 'testing_db');
        $connection->setConnectionName('test-connection');
        WebFiori::getConfig()->addDbConnection($connection);
        $func = new Functions();
        $this->assertTrue($func->setConnection('test-connection'));
        $result = $func->useDatabase();
        $this->assertTrue($result);
        return $func;
    }
    /**
     * @test
     */
    public function testUseDatabase03() {
        $func = new Functions();
        $result = $func->useDatabase();
        $this->assertFalse($result);
        $err = $func->getDBErrDetails();
        $this->assertEquals(-2,$err['error-code']);
        $this->assertEquals('No database connection was set.',$err['error-message']);
    }
    /**
     * @depends testUseDatabase02
     * @test
     * @param Functions $func 
     */
    public function testUseDatabase04($func) {
        $r = $func->useDatabase('test-connection');
        $this->assertTrue($r);
        return $func;
    }
    /**
     * @depends testUseDatabase04
     * @test
     * @param Functions $func 
     */
    public function testUseDatabase05($func) {
        $r = $func->useDatabase('test-connection-x');
        $this->assertEquals(Functions::NO_SUCH_CONNECTION,$r);
        $err = $func->getDBErrDetails();
        $this->assertEquals(-1,$err['error-code']);
        $this->assertEquals('No database connection was found which has the name \'test-connection-x\'.',$err['error-message']);
        return $func;
    }
    /**
     * @depends testUseDatabase05
     * @test
     * @param Functions $func 
     */
    public function testUseDatabase06($func) {
        $r = $func->useDatabase('test-connection');
        $this->assertTrue($r);
    }
    /**
     * @test
     */
    public function testUseDatabase07() {
        $connection = new DBConnectionInfo('root', '123456', 'testing_db');
        $connection->setConnectionName('test-connection');
        WebFiori::getConfig()->addDbConnection($connection);
        $func = new Functions();
        $this->assertTrue($func->setConnection('test-connection'));
        $result = $func->useDatabase();
        $this->assertTrue($result);
        $connection = new DBConnectionInfo('root', '123456', '');
        $connection->setConnectionName('test-connection');
        WebFiori::getConfig()->addDbConnection($connection);
        $result = $func->useDatabase('test-connection');
        $this->assertFalse($result);
        $errDetails = $func->getDBErrDetails();
        $this->assertEquals(1046,$errDetails['error-code']);
        $this->assertEquals("No database selected",$errDetails['error-message']);
    }
    /**
     * @test
     */
    public function testUseSession00() {
        $func = new Functions();
        $r = $func->useSession();
        $this->assertFalse($r);
    }
    /**
     * @test
     */
    public function testUseSession01() {
        $func = new Functions();
        $r = $func->useSession(
            array(
                'name'=>'test-session'
            )
        );
        $this->assertFalse($r);
    }
    /**
     * @test
     */
    public function testExecuteQuery00() {
        $f = new Functions();
        $r = $f->excQ();
        $this->assertFalse($r);
        $errDetails = $f->getDBErrDetails();
        $this->assertEquals(Functions::NO_QUERY,$errDetails['error-code']);
        $this->assertEquals('No query object was set to execute.',$errDetails['error-message']);
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
