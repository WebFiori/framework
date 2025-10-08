<?php
namespace WebFiori\Framework\Test\Cli;

use Tables\EmployeeInfoTable;
use Tables\PositionInfoTable;
use Tables\UserInfoTable;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Writers\DBClassWriter;

/**
 * Description of DBClassWritterTest
 *
 * @author Ibrahim
 */
class DBClassWritterTest extends CLITestCase {
    /**
     * @test
     */
    public function test00() {
        $table = new UserInfoTable();
        $mapper = $table->getEntityMapper();
        $mapper->setEntityName('CoolUser');
        $mapper->setNamespace('WebFiori\\Entity');
        $writter = new DBClassWriter('UserDBClass', 'WebFiori\\Db', $table);
        $writter->writeClass();
        $this->assertTrue(class_exists($writter->getName(true)));
        $this->removeClass($writter->getName(true));
    }
    
    /**
     * @test
     */
    public function test01() {
        $table = new EmployeeInfoTable();
        $mapper = $table->getEntityMapper();
        $mapper->setEntityName('Employee');
        $mapper->setNamespace('WebFiori\\Entity');
        $writter = new DBClassWriter('EmployeeDB', 'WebFiori\\Db', $table);
        $writter->writeClass();
        $this->assertTrue(class_exists($writter->getName(true)));
        $this->removeClass($writter->getName(true));
    }
    
    /**
     * @test
     */
    public function test02() {
        $table = new PositionInfoTable();
        $mapper = $table->getEntityMapper();
        $mapper->setEntityName('Position');
        $mapper->setNamespace('WebFiori\\Entity');
        $writter = new DBClassWriter('PositionDB', 'WebFiori\\Db', $table);
        $writter->writeClass();
        $this->assertTrue(class_exists($writter->getName(true)));
        $this->removeClass($writter->getName(true));
    }
    
    /**
     * @test
     */
    public function test03() {
        $table = new PositionInfoTable();
        $writter = new DBClassWriter('PositionDB2', 'WebFiori\\Db', $table);
        $writter->setConnection('  ');
        $this->assertNull($writter->getConnectionName());
        $writter->setConnection('ok-connection');
        $this->assertEquals('ok-connection', $writter->getConnectionName());
        $writter->includeColumnsUpdate();
        $writter->writeClass();
        $this->assertTrue(class_exists($writter->getName(true)));
        $this->removeClass($writter->getName(true));
    }
}
