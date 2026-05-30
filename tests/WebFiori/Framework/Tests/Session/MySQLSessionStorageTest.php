<?php

namespace WebFiori\Framework\Test\Session;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Database;

/**
 * MySQL-based database session storage tests.
 * Skipped if MySQL container is not available.
 */
class MySQLSessionStorageTest extends AbstractDatabaseSessionStorageTest {
    protected function setUp(): void {
        try {
            $conn = $this->getConnectionInfo();
            $db = new Database($conn);
            $db->getConnection();
        } catch (\Throwable $e) {
            $this->markTestSkipped('MySQL not available: '.$e->getMessage());
        }
        parent::setUp();
    }

    protected function getConnectionInfo(): ConnectionInfo {
        return new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', '127.0.0.1', getenv('MYSQL_PORT') !== false ? intval(getenv('MYSQL_PORT')) : 3306);
    }
}
