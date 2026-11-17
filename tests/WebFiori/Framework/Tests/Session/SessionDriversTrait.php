<?php
namespace WebFiori\Framework\Test\Session;

use WebFiori\Cache\FileStorage;
use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\ConnectionPool;
use WebFiori\Framework\App;
use WebFiori\Framework\Session\CacheSessionStorage;
use WebFiori\Framework\Session\ConflictStrategy;
use WebFiori\Framework\Session\DatabaseSessionStorage;
use WebFiori\Framework\Session\DefaultSessionStorage;
use WebFiori\Framework\Session\InMemorySessionStorage;
use WebFiori\Framework\Session\SessionStorage;

/**
 * Skip reasons of database drivers that failed to connect, shared by every
 * test class in the run. An unreachable server then costs one login timeout
 * per run instead of one per test. Kept outside the trait because each class
 * using a trait gets its own copy of the trait's static properties.
 */
final class SessionDriversAvailability {
    /** @var array<string, string> */
    public static array $unavailable = [];
}

/**
 * Creates and cleans up every session storage driver for driver-parametrized
 * tests. Drivers that need an external service (MySQL, MSSQL) skip the test
 * when the service is not reachable.
 *
 * Environment overrides: MYSQL_PORT, SQL_SERVER_HOST (for MSSQL a port can be
 * given as 'host,port').
 */
trait SessionDriversTrait {
    private string $cacheDir = '';
    /** @var string|ConnectionInfo|null Connection used by the last database driver created. */
    private $dbConnection = null;
    private ?DatabaseSessionStorage $dbStorage = null;
    private string $sqlitePath = '';

    public static function databaseStorageProvider(): array {
        return [
            'sqlite' => ['sqlite'],
            'mysql' => ['mysql'],
            'mssql' => ['mssql'],
        ];
    }

    public static function storageProvider(): array {
        return [
            'default (file)' => ['default'],
            'in-memory' => ['memory'],
            'cache' => ['cache'],
            'sqlite' => ['sqlite'],
            'mysql' => ['mysql'],
            'mssql' => ['mssql'],
        ];
    }

    /**
     * Drivers whose garbage collection is driven by a last-use timestamp
     * (file mtime or the 'last-used' column).
     */
    public static function timestampStorageProvider(): array {
        return [
            'default (file)' => ['default'],
            'sqlite' => ['sqlite'],
            'mysql' => ['mysql'],
            'mssql' => ['mssql'],
        ];
    }

    /**
     * Moves the last-use time of a session into the past.
     */
    private function ageSession(SessionStorage $storage, string $sessionId, int $seconds): void {
        $time = time() - $seconds;

        if ($storage instanceof DefaultSessionStorage) {
            $m = new \ReflectionMethod($storage, 'filePath');
            $m->setAccessible(true);
            touch($m->invoke($storage, $sessionId), $time);

            return;
        }
        $storage->getController()->table('sessions')
            ->update(['last-used' => date('Y-m-d H:i:s', $time)])
            ->where('s-id', $sessionId)->execute();
    }

    private function cleanupDrivers(): void {
        if ($this->dbStorage !== null) {
            try {
                $this->dbStorage->getController()->removeTables();
            } catch (\Throwable $e) {
            }
            // Close every pooled connection. Leaving them open exhausts the
            // pool (max 100) mid-suite, and returning them as idle lets a later
            // test reuse a SQLite handle whose database file was deleted.
            $this->dbStorage->getController()->close();
            $this->dbStorage = null;
            ConnectionPool::reset();
        }

        if ($this->sqlitePath !== '' && file_exists($this->sqlitePath)) {
            unlink($this->sqlitePath);
        }

        if ($this->cacheDir !== '' && is_dir($this->cacheDir)) {
            foreach (glob($this->cacheDir.DIRECTORY_SEPARATOR.'*') as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->cacheDir);
        }
        $this->cacheDir = '';
        $this->sqlitePath = '';
    }

    private function createDbStorage(ConnectionInfo $conn, string $label, bool $register = true): SessionStorage {
        if (isset(SessionDriversAvailability::$unavailable[$label])) {
            $this->markTestSkipped(SessionDriversAvailability::$unavailable[$label]);
        }

        try {
            $conn->setName('test-sessions-conn');

            if ($register) {
                App::getConfig()->addOrUpdateDBConnection($conn);
            }
            $storage = new DatabaseSessionStorage($register ? 'test-sessions-conn' : $conn);
            $storage->getController()->removeTables();
            $storage->getController()->createTables();
            $storage->write('probe', 'k', 'probe', null, ConflictStrategy::LAST_WRITE_WINS);
            $storage->destroy('probe');
        } catch (\Throwable $e) {
            SessionDriversAvailability::$unavailable[$label] = "$label not available: ".$e->getMessage();
            $this->markTestSkipped(SessionDriversAvailability::$unavailable[$label]);
        }
        $this->dbStorage = $storage;
        $this->dbConnection = $register ? 'test-sessions-conn' : $conn;

        return $storage;
    }

    private function createStorage(string $driver): SessionStorage {
        switch ($driver) {
            case 'default':
                return new DefaultSessionStorage();
            case 'memory':
                return new InMemorySessionStorage();
            case 'cache':
                $this->cacheDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf-session-drv-test-'.uniqid();
                mkdir($this->cacheDir, 0755, true);

                return new CacheSessionStorage(new FileStorage($this->cacheDir));
            case 'sqlite':
                $this->sqlitePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wf_session_drv_'.getmypid().'.db';

                return $this->createDbStorage(new ConnectionInfo('sqlite', '', '', $this->sqlitePath, ''), 'SQLite', false);
            case 'mysql':
                $port = getenv('MYSQL_PORT') !== false ? intval(getenv('MYSQL_PORT')) : 3306;

                return $this->createDbStorage(
                    new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', '127.0.0.1', $port),
                    'MySQL'
                );
            case 'mssql':
                return $this->createDbStorage(
                    new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
                        'TrustServerCertificate' => 'true',
                    ]),
                    'MSSQL'
                );
        }
        $this->fail("Unknown driver '$driver'.");
    }

    /**
     * Returns the last-use time of a session as a Unix timestamp.
     */
    private function lastUsed(SessionStorage $storage, string $sessionId): int {
        if ($storage instanceof DefaultSessionStorage) {
            $m = new \ReflectionMethod($storage, 'filePath');
            $m->setAccessible(true);
            clearstatcache();

            return filemtime($m->invoke($storage, $sessionId));
        }
        $rows = $storage->getController()->table('sessions')->select()
            ->where('s-id', $sessionId)->execute()->getRows();

        return strtotime($rows[0]['last-used'] ?? $rows[0]['last_used']);
    }

    private function sessionExistsInStorage(SessionStorage $storage, string $sessionId): bool {
        if ($storage instanceof DefaultSessionStorage) {
            return $storage->isStorageFileExist($sessionId);
        }

        if ($storage instanceof DatabaseSessionStorage) {
            return $storage->getController()->isSessionExist($sessionId);
        }

        return $storage->readAll($sessionId) !== [];
    }
}
