<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace webfiori\framework\test;

use app\database\TestTable;
use PHPUnit\Framework\TestCase;
use webfiori\database\ConnectionInfo;
use webfiori\database\DatabaseException;
use webfiori\framework\ConfigController;
use webfiori\framework\DB;
use webfiori\framework\WebFioriApp;

/**
 * Description of DBTest
 *
 * @author Ibrah
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
        WebFioriApp::getAppConfig()->addDbConnection($conn);
        $db = new DB('default-conn');
        $this->assertEquals(0, count($db->getTables()));
        $db->register('database');
        $this->assertEquals(2, count($db->getTables()));
        WebFioriApp::getAppConfig()->removeDBConnections();
    }
}
