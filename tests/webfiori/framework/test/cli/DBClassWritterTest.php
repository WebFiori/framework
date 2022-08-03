<?php
namespace webfiori\framework\test\cli;

use tables\EmployeeInfoTable;
use tables\PositionInfoTable;
use tables\UserInfoTable;
use webfiori\framework\writers\DBClassWriter;

/**
 * Description of DBClassWritterTest
 *
 * @author Ibrahim
 */
class DBClassWritterTest extends CreateTestCase {
    /**
     * @test
     */
    public function test00() {
        $table = new UserInfoTable();
        $mapper = $table->getEntityMapper();
        $mapper->setEntityName('CoolUser');
        $mapper->setNamespace('webfiori\\entity');
        $writter = new DBClassWriter('UserDBClass', 'webfiori\\db', $table);
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
        $mapper->setNamespace('webfiori\\entity');
        $writter = new DBClassWriter('EmployeeDB', 'webfiori\\db', $table);
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
        $mapper->setNamespace('webfiori\\entity');
        $writter = new DBClassWriter('PositionDB', 'webfiori\\db', $table);
        $writter->writeClass();
        $this->assertTrue(class_exists($writter->getName(true)));
        $this->removeClass($writter->getName(true));
    }
    /**
     * @test
     */
    public function test03() {
        $table = new PositionInfoTable();
        $writter = new DBClassWriter('PositionDB2', 'webfiori\\db', $table);
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
