<?php

namespace WebFiori\Framework\Test\Session;

use WebFiori\Database\ConnectionInfo;

/**
 * MySQL-based database session storage tests.
 * Skipped if MySQL container is not available.
 */
class MySQLSessionStorageTest extends AbstractDatabaseSessionStorageTest {
    protected function setUp(): void {
        try {
            new \PDO(
                'mysql:host=localhost;port=3307;dbname=testing_db',
                'root',
                MYSQL_ROOT_PASSWORD
            );
        } catch (\Throwable $e) {
            $this->markTestSkipped('MySQL not available: '.$e->getMessage());
        }
        parent::setUp();
    }

    protected function getConnectionInfo(): ConnectionInfo {
        return new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', 'localhost', 3307);
    }
}
