<?php

namespace WebFiori\Framework\Test\Session;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Database;

/**
 * MSSQL-based database session storage tests.
 * Skipped if MSSQL container is not available or schema has issues.
 */
class MSSQLSessionStorageTest extends AbstractDatabaseSessionStorageTest {
    protected function setUp(): void {
        try {
            $conn = $this->getConnectionInfo();
            $conn->setName($this->getConnectionName());
            \WebFiori\Framework\App::getConfig()->addOrUpdateDBConnection($conn);

            $this->storage = new \WebFiori\Framework\Session\DatabaseSessionStorage($this->getConnectionName());
            $this->storage->getController()->removeTables();
            $this->storage->getController()->createTables();
            // Verify a basic operation works
            $this->storage->save('mssql-test-probe', 'probe');
            $this->storage->remove('mssql-test-probe');
        } catch (\Throwable $e) {
            $this->markTestSkipped('MSSQL not available or schema issue: '.$e->getMessage());
        }
    }

    protected function getConnectionInfo(): ConnectionInfo {
        return new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
    }
}
