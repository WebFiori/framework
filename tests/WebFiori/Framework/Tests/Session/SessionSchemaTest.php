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
    /**
     * @test
     *
     * On MSSQL the per-key value column must be NVARCHAR(MAX) (size -1), not
     * the auto-mapped nvarchar(4000) which would cap a key value at 4000 chars.
     */
    public function testKvDataSvalueIsNvarcharMaxOnMSSQL() {
        $table = SessionSchema::createSessionKvDataTable('mssql');
        $col = $table->getColByKey('svalue');
        $this->assertNotNull($col);
        $this->assertEquals('nvarchar', $col->getDatatype());
        $this->assertEquals(-1, $col->getSize());
    }
    /**
     * @test
     *
     * MySQL keeps mediumtext (~16 MB) for the per-key value column.
     */
    public function testKvDataSvalueIsMediumTextOnMySQL() {
        $table = SessionSchema::createSessionKvDataTable('mysql');
        $col = $table->getColByKey('svalue');
        $this->assertNotNull($col);
        $this->assertEquals('mediumtext', $col->getDatatype());
    }
    /**
     * @test
     *
     * The per-key table's session-ID foreign key must match the referenced
     * sessions primary key type/size on every engine.
     */
    public function testKvDataSessionIdMatchesSessionsPk() {
        foreach (['mysql', 'mssql', 'sqlite'] as $dbType) {
            $sessions = SessionSchema::createSessionsTable($dbType);
            $kv = SessionSchema::createSessionKvDataTable($dbType);

            $pk = $sessions->getColByKey('s-id');
            $fk = $kv->getColByKey('s-id');

            $this->assertNotNull($pk, "$dbType: sessions.s_id missing");
            $this->assertNotNull($fk, "$dbType: session_kv_data.s_id missing");
            $this->assertEquals(
                $pk->getDatatype(),
                $fk->getDatatype(),
                "$dbType: s_id type mismatch between sessions and session_kv_data"
            );
            $this->assertEquals(
                $pk->getSize(),
                $fk->getSize(),
                "$dbType: s_id size mismatch between sessions and session_kv_data"
            );
        }
    }
}
