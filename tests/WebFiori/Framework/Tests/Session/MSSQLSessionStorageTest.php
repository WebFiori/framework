<?php

namespace WebFiori\Framework\Test\Session;

use WebFiori\Database\ConnectionInfo;

/**
 * MSSQL-based database session storage tests.
 * Skipped if MSSQL container is not available.
 */
class MSSQLSessionStorageTest extends AbstractDatabaseSessionStorageTest {
    protected function setUp(): void {
        try {
            $dsn = 'sqlsrv:Server='.SQL_SERVER_HOST.',1433;Database='.SQL_SERVER_DB.';TrustServerCertificate=true';
            new \PDO($dsn, SQL_SERVER_USER, SQL_SERVER_PASS);
        } catch (\Throwable $e) {
            $this->markTestSkipped('MSSQL not available: '.$e->getMessage());
        }
        parent::setUp();
    }

    protected function getConnectionInfo(): ConnectionInfo {
        return new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
    }
}
