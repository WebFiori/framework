<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\InitMigrationsCommand;
use WebFiori\Framework\Cli\Commands\RunMigrationsCommandNew;

/**
 * Test cases for connection-targeted migrations CLI support.
 */
class ConnectionTargetedMigrationsTest extends CLITestCase {
    private ConnectionInfo $mssqlConnection;
    private ConnectionInfo $mysqlConnection;

    /**
     * @test
     */
    public function testAllConnectionsAndConnectionMutuallyExclusive() {
        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--all-connections' => '',
            '--connection' => 'mysql-db'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Cannot use --all-connections and --connection together', $outputStr);
        $this->assertEquals(1, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testAllConnectionsMixedTargeting() {
        // Create migrations: one for mysql, one for mssql (simulated as second mysql conn), one for all
        $this->createTargetedMigration('MysqlOnly', "['mysql-db']");
        $this->createTargetedMigration('SecondOnly', "['second-db']");
        $this->createTargetedMigration('Universal', '[]');

        // Add a second mysql connection with different name (same physical DB for testing)
        $secondConn = new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', '127.0.0.1', 3306);
        $secondConn->setName('second-db');
        App::getConfig()->addOrUpdateDBConnection($secondConn);

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--all-connections' => ''
        ]);

        $outputStr = implode('', $output);

        // mysql-db section should apply MysqlOnly + Universal, skip SecondOnly
        $this->assertStringContainsString('=== Connection: mysql-db ===', $outputStr);
        $this->assertStringContainsString('=== Connection: second-db ===', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testAllConnectionsNoConnections() {
        App::getConfig()->removeAllDBConnections();

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--all-connections' => ''
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('No database connections configured', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testAllConnectionsRunsOnBoth() {
        $this->createTargetedMigration('ForMysql', "['mysql-db']");
        $this->createTargetedMigration('ForAll', '[]');

        $this->initMigrationsFor('mysql-db');

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--all-connections' => ''
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('=== Connection: mysql-db ===', $outputStr);
        $this->assertStringContainsString('Applied: App\\Database\\Migrations\\ForAll', $outputStr);
        $this->assertStringContainsString('Applied: App\\Database\\Migrations\\ForMysql', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test - Integration: targeted migration on MSSQL connection
     */
    public function testMssqlTargetedMigration() {
        try {
            $this->setupMssqlConnection();
        } catch (\Throwable $e) {
            $this->markTestSkipped('MSSQL connection not available: '.$e->getMessage());

            return;
        }

        $this->createTargetedMigration('MssqlMig', "['mssql-db']");

        // Run against mysql - should skip
        $this->initMigrationsFor('mysql-db');
        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'mysql-db'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Skipped: App\\Database\\Migrations\\MssqlMig', $outputStr);

        // Run against mssql - should apply
        $this->initMigrationsFor('mssql-db');
        $output2 = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'mssql-db'
        ]);

        $outputStr2 = implode('', $output2);
        $this->assertStringContainsString('Applied: App\\Database\\Migrations\\MssqlMig', $outputStr2);
    }

    /**
     * @test
     */
    public function testMultiTargetMigrationRunsOnEither() {
        $this->createTargetedMigration('MultiTarget', "['mysql-db', 'mssql-db']");
        $this->initMigrationsFor('mysql-db');

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'mysql-db'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Applied: App\\Database\\Migrations\\MultiTarget', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testTargetedMigrationRunsOnCorrectConnection() {
        $this->createTargetedMigration('OnlyMysql', "['mysql-db']");
        $this->initMigrationsFor('mysql-db');

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'mysql-db'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Applied: App\\Database\\Migrations\\OnlyMysql', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testTargetedMigrationSkippedOnWrongConnection() {
        $this->createTargetedMigration('OnlyMssql', "['mssql-db']");
        $this->initMigrationsFor('mysql-db');

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'mysql-db'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Skipped: App\\Database\\Migrations\\OnlyMssql', $outputStr);
        $this->assertStringContainsString('Connection mismatch', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testValidationWarnsDefaultConnectionName() {
        // Add a connection with default name
        $defaultConn = new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', '127.0.0.1', 3306);
        // Don't set name — it stays as 'New_Connection'
        App::getConfig()->addOrUpdateDBConnection($defaultConn);

        $this->createTargetedMigration('SomeTargeted', "['mysql-db']");

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'New_Connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('default name "New_Connection"', $outputStr);
    }

    /**
     * @test
     */
    public function testValidationWarnsUnknownTargetConnection() {
        $this->createTargetedMigration('BadTarget', "['nonexistent-db']");
        $this->initMigrationsFor('mysql-db');

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'mysql-db'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('targets unknown connection: nonexistent-db', $outputStr);
    }

    private function cleanupMigrations(): void {
        $dir = APP_PATH.'Database'.DS.'Migrations';

        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isFile() && $item->getExtension() === 'php') {
                unlink($item->getRealPath());
            }
        }
    }

    // --- Helpers ---

    private function createTargetedMigration(string $name, string $targets): void {
        $dir = APP_PATH.'Database'.DS.'Migrations';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $content = <<<PHP
<?php
namespace App\Database\Migrations;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class $name extends AbstractMigration {
    public function getTargetConnections(): array {
        return $targets;
    }

    public function up(Database \$db): void {
        // Test migration - no-op
    }
    
    public function down(Database \$db): void {
    }
}
PHP;

        file_put_contents($dir.DS.$name.'.php', $content);
    }

    private function dropSchemaTable(string $connectionName = 'mysql-db'): void {
        try {
            $conn = App::getConfig()->getDBConnection($connectionName);

            if ($conn !== null) {
                $db = new \WebFiori\Database\Database($conn);
                $db->raw("DROP TABLE IF EXISTS schema_changes")->execute();
                $db->close();
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    private function initMigrationsFor(string $connectionName): void {
        $this->executeMultiCommand([
            InitMigrationsCommand::class,
            '--connection' => $connectionName
        ]);
    }

    private function setupMssqlConnection(): void {
        $password = getenv('SA_SQL_SERVER_PASSWORD') ?: '1234567890@Eu';
        $this->mssqlConnection = new ConnectionInfo('mssql', 'sa', $password, 'testing_db', 'localhost', 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $this->mssqlConnection->setName('mssql-db');
        App::getConfig()->addOrUpdateDBConnection($this->mssqlConnection);

        // Test connection works
        $db = new \WebFiori\Database\Database($this->mssqlConnection);
        $db->raw("SELECT 1 AS test")->execute();
        $db->close();
    }

    private function setupMysqlConnection(): void {
        $this->mysqlConnection = new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', '127.0.0.1', 3306);
        $this->mysqlConnection->setName('mysql-db');
        App::getConfig()->addOrUpdateDBConnection($this->mysqlConnection);
    }

    protected function setUp(): void {
        parent::setUp();
        App::getConfig()->removeAllDBConnections();
        $this->setupMysqlConnection();
        $this->dropSchemaTable('mysql-db');
        $this->cleanupMigrations();
    }

    protected function tearDown(): void {
        $this->cleanupMigrations();
        $this->dropSchemaTable('mysql-db');
        $this->dropSchemaTable('mssql-db');
        parent::tearDown();
    }
}
