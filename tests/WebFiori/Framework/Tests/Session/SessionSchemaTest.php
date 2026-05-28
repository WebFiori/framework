<?php

namespace WebFiori\Framework\Test\Session;

use PHPUnit\Framework\TestCase;
use WebFiori\Database\MsSql\MSSQLTable;
use WebFiori\Database\MySql\MySQLTable;
use WebFiori\Database\Sqlite\SQLiteTable;
use WebFiori\Framework\Session\SessionSchema;

/**
 * Tests for SessionSchema table creation.
 */
class SessionSchemaTest extends TestCase {
    /**
     * @test
     */
    public function testCreateSessionsTableMySQL() {
        $table = SessionSchema::createSessionsTable('mysql');
        $this->assertInstanceOf(MySQLTable::class, $table);
        $this->assertEquals('sessions', $table->getNormalName());
        $this->assertNotNull($table->getColByKey('s-id'));
        $this->assertNotNull($table->getColByKey('started-at'));
        $this->assertNotNull($table->getColByKey('last-used'));
    }
    /**
     * @test
     */
    public function testCreateSessionsTableMSSQL() {
        $table = SessionSchema::createSessionsTable('mssql');
        $this->assertInstanceOf(MSSQLTable::class, $table);
        $this->assertEquals('sessions', $table->getNormalName());
    }
    /**
     * @test
     */
    public function testCreateSessionsTableSQLite() {
        $table = SessionSchema::createSessionsTable('sqlite');
        $this->assertInstanceOf(SQLiteTable::class, $table);
        $this->assertEquals('sessions', $table->getNormalName());
    }
    /**
     * @test
     */
    public function testCreateSessionDataTableHasFK() {
        $table = SessionSchema::createSessionDataTable('mysql');
        $fks = $table->getForeignKeys();
        $this->assertCount(1, $fks);
    }
    /**
     * @test
     */
    public function testCreateSessionDataTableSQLite() {
        $table = SessionSchema::createSessionDataTable('sqlite');
        $this->assertInstanceOf(SQLiteTable::class, $table);
        $this->assertNotNull($table->getColByKey('s-id'));
        $this->assertNotNull($table->getColByKey('chunk-number'));
        $this->assertNotNull($table->getColByKey('data'));
    }
}
