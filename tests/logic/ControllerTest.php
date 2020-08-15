<?php
namespace webfiori\tests\logic;

use PHPUnit\Framework\TestCase;
use SimpleController;
use webfiori\entity\DBConnectionInfo;
use webfiori\logic\Controller;
use webfiori\tests\entity\TestQuery_1;
use webfiori\WebFiori;
/**
 * Description of FunctionsTest
 *
 * @author Ibrahim
 */
class ControllerTest extends TestCase {
    /**
     * @test
     */
    public function testExecuteQuery00() {
        $f = new Controller();
        $r = $f->excQ();
        $this->assertFalse($r);
        $errDetails = $f->getDBErrDetails();
        $this->assertEquals(Controller::NO_QUERY,$errDetails['error-code']);
        $this->assertEquals('No query object was set to execute.',$errDetails['error-message']);
    }
    /**
     * @test
     */
    public function testGetRows00() {
        $func = new Controller();
        $this->assertEquals(-1,$func->rows());
        $this->assertEmpty($func->getRows());
        $this->assertNull($func->getRow());
        $this->assertNull($func->nextRow());
    }
    /**
     * @test
     */
    public function testGetSession00() {
        $func = new Controller();
        $this->assertNotNull($func->getSession());
    }
    /**
     * @test
     */
    public function testGetSessionLang00() {
        $func = new Controller();
        $this->assertEquals('EN',$func->getSessionLang());
    }
    /**
     * @test
     */
    public function testGetSessionVar00() {
        $func = new Controller();
        $this->assertNull($func->getSessionVar(''));
    }
    /**
     * @test
     */
    public function testGetSessionVar01() {
        $func = new Controller();
        $this->assertNull($func->getSessionVar('random'));
    }
    /**
     * @test
     */
    public function testGetUserID00() {
        $func = new Controller();
        $this->assertEquals(-1,$func->getUserID());
    }
    /**
     * @test
     */
    public function testHasPrivilege00() {
        $func = new Controller();
        $this->assertFalse($func->hasPrivilege('HELLO'));
    }
    /**
     * @test
     */
    public function testSetConnection00() {
        $func = new Controller();
        $result = $func->setConnection('not_exist');
        $this->assertEquals(Controller::NO_SUCH_CONNECTION,$result);
    }
    /**
     * @test
     */
    public function testSetConnection01() {
        $connection = new DBConnectionInfo('root', '123456', 'testing_db');
        $connection->setConnectionName('test-connection');
        WebFiori::getConfig()->addDbConnection($connection);
        $func = new Controller();
        $result = $func->setConnection('test-connection');
        $this->assertTrue($result);
    }
    /**
     * @test
     */
    public function testSetQuery00() {
        $func = new Controller();
        $this->assertFalse($func->setQueryObject(null));
        $this->assertFalse($func->setQueryObject(44));
        $this->assertFalse($func->setQueryObject($func));
        $this->assertNull($func->getQueryObject());
    }
    /**
     * @test
     */
    public function testSetQuery01() {
        $func = new Controller();
        $q = new TestQuery_1();
        $this->assertTrue($func->setQueryObject($q));
        $this->assertTrue($func->getQueryObject() instanceof TestQuery_1);
    }
    /**
     * @test
     */
    public function testSetSessionVar00() {
        $func = new Controller();
        $this->assertFalse($func->setSessionVar(' ',null));
        $this->assertTrue($func->setSessionVar('hello',null));
    }
    /**
     * @test
     * @depends testUseDatabase02
     */
    public function testSimpleController00() {
        $c = new SimpleController();
        $users = $c->getUsers();
        $this->assertEquals('array', gettype($users));
        $this->assertEquals(3,count($users));

        for ($x = 0 ; $x < count($users) ; $x++) {
            $this->assertEquals('user-0'.$x,$users[$x]->getUserName());
            $this->assertEquals('pass-0'.$x,$users[$x]->getPassword());
        }
        $this->assertEquals(4,count($users[2]->getContactInfo()));
    }
    /**
     * @test
     */
    public function testUseDatabase00() {
        $func = new Controller();
        $result = $func->useDatabase('not-exist');
        $this->assertEquals(Controller::NO_SUCH_CONNECTION,$result);
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
        $func = new Controller();
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
        $connection = new DBConnectionInfo('root', '123456', 'testing_db');
        $connection->setConnectionName('test-connection');
        WebFiori::getConfig()->addDbConnection($connection);
        $func = new Controller();
        $this->assertTrue($func->setConnection('test-connection'));
        $result = $func->useDatabase();
        $this->assertTrue($result);

        return $func;
    }
    /**
     * @test
     */
    public function testUseDatabase03() {
        $func = new Controller();
        $result = $func->useDatabase();
        $this->assertFalse($result);
        $err = $func->getDBErrDetails();
        $this->assertEquals(-2,$err['error-code']);
        $this->assertEquals('No database connection was set.',$err['error-message']);
    }
    /**
     * @depends testUseDatabase02
     * @test
     * @param Controller $func 
     */
    public function testUseDatabase04($func) {
        $r = $func->useDatabase('test-connection');
        $this->assertTrue($r);

        return $func;
    }
    /**
     * @depends testUseDatabase04
     * @test
     * @param Controller $func 
     */
    public function testUseDatabase05($func) {
        $r = $func->useDatabase('test-connection-x');
        $this->assertEquals(Controller::NO_SUCH_CONNECTION,$r);
        $err = $func->getDBErrDetails();
        $this->assertEquals(-1,$err['error-code']);
        $this->assertEquals('No database connection was found which has the name \'test-connection-x\'.',$err['error-message']);

        return $func;
    }
    /**
     * @depends testUseDatabase05
     * @test
     * @param Controller $func 
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
        $func = new Controller();
        $this->assertTrue($func->setConnection('test-connection'));
        $result = $func->useDatabase();
        $this->assertTrue($result);
        
        $connection = new DBConnectionInfo('root', '123456', '');
        $connection->setConnectionName('test-connection-2');
        WebFiori::getConfig()->addDbConnection($connection);
        $result = $func->useDatabase('test-connection-2');
        $this->assertFalse($result);
        $errDetails = $func->getDBErrDetails();
        $this->assertEquals(1046,$errDetails['error-code']);
        $this->assertEquals("No database selected",$errDetails['error-message']);
    }
    /**
     * @test
     */
    public function testUseSession00() {
        $func = new Controller();
        $this->expectException('Exception');
        $r = $func->useSession([
            'name' => 'Invalid {'
        ]);
    }
    /**
     * @test
     */
    public function testUseSession01() {
        $func = new Controller();
        $r = $func->useSession(
            [
                'name' => 'test-session'
            ]
        );
        $this->assertFalse($r);
    }
}
