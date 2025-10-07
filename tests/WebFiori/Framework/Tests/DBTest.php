<?php
namespace WebFiori\Framework\Test;

use App\Database\TestTable;
use PHPUnit\Framework\TestCase;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\DatabaseException;
use WebFiori\Framework\App;
use WebFiori\Framework\DB;

/**
 * Description of DBTest
 *
 * @author Ibrahim
 */
class DBTest extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('No connection was found which has the name \'ok\'.');
        $db = new DB('ok');
    }
    /**
     * @test
     */
    public function test01() {
        $this->expectException(DatabaseException::class);

        $conn = new ConnectionInfo('mysql', 'root', '12345', 'testing_db', '127.0.0.1');
        $db = new DB($conn);
        $db->addTable(new TestTable());
        $db->table('test')->select()->execute();
    }
    /**
     * @test
     */
    public function test02() {
        $this->expectException(DatabaseException::class);

        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $db = new DB($conn);
        $db->addTable(new TestTable());

        $db->table('test')->select()->execute();
    }
    /**
     * @test
     */
    public function test03() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db');
        $db = new DB($conn);
        $this->assertEquals(0, count($db->getTables()));
        $db->register('database');
        $this->assertEquals(2, count($db->getTables()));
    }
    /**
     * @test
     */
    public function test04() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db');
        $conn->setName('default-conn');
        App::getConfig()->addOrUpdateDBConnection($conn);
        $db = new DB('default-conn');
        $this->assertEquals(0, count($db->getTables()));
        $db->register('database');
        $this->assertEquals(2, count($db->getTables()));
        App::getConfig()->removeAllDBConnections();
    }
}
