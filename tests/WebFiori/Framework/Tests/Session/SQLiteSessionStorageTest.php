<?php

namespace WebFiori\Framework\Test\Session;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\DatabaseSessionStorage;

/**
 * SQLite-based database session storage tests.
 * Always runs (no external container needed).
 */
class SQLiteSessionStorageTest extends AbstractDatabaseSessionStorageTest {
    protected function getConnectionInfo(): ConnectionInfo {
        $dbPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_session_test_'.getmypid().'.db';

        return new ConnectionInfo('sqlite', '', '', $dbPath, '');
    }

    protected function setUp(): void {
        $conn = $this->getConnectionInfo();
        $conn->setName($this->getConnectionName());

        $this->storage = new DatabaseSessionStorage($conn);
        $this->storage->getController()->removeTables();
        $this->storage->getController()->createTables();
    }

    protected function tearDown(): void {
        parent::tearDown();
        $dbPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_session_test_'.getmypid().'.db';

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
    }
}
