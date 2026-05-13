<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\InitMigrationsCommand;
use WebFiori\Framework\Cli\Commands\RunMigrationsCommandNew;
use WebFiori\Framework\Cli\Commands\SkipMigrationsCommand;

/**
 * Test cases for SkipMigrationsCommand.
 */
class SkipMigrationsCommandTest extends CLITestCase {
    private ConnectionInfo $testConnection;

    /**
     * @test
     */
    public function testSkipAll() {
        $this->createTestMigration('SkipAll1');
        $this->createTestMigration('SkipAll2');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            SkipMigrationsCommand::class,
            '--all' => '',
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Skipped: App\\Database\\Migrations\\SkipAll1', $outputStr);
        $this->assertStringContainsString('Skipped: App\\Database\\Migrations\\SkipAll2', $outputStr);
        $this->assertStringContainsString('Total skipped: 2', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testSkipAllWhenNothingPending() {
        $this->createTestMigration('NoPending');
        $this->initMigrations();

        // Run it first
        $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection'
        ]);

        // Skip all - nothing left
        $output = $this->executeMultiCommand([
            SkipMigrationsCommand::class,
            '--all' => '',
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('No pending migrations to skip', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testSkipAlreadyApplied() {
        $this->createTestMigration('AlreadyDone');
        $this->initMigrations();

        // Run it first
        $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection'
        ]);

        // Try to skip it
        $output = $this->executeMultiCommand([
            SkipMigrationsCommand::class,
            '--name' => 'App\\Database\\Migrations\\AlreadyDone',
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Could not skip', $outputStr);
        $this->assertStringContainsString('not found or already applied', $outputStr);
        $this->assertEquals(1, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testSkippedMigrationWontRun() {
        $this->createTestMigration('WontRun');
        $this->initMigrations();

        // Skip it
        $this->executeMultiCommand([
            SkipMigrationsCommand::class,
            '--name' => 'App\\Database\\Migrations\\WontRun',
            '--connection' => 'test-connection'
        ]);

        // Now run migrations
        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Skipped: App\\Database\\Migrations\\WontRun', $outputStr);
        $this->assertStringNotContainsString('Applied: App\\Database\\Migrations\\WontRun', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testSkipSchemaTableMissing() {
        $this->createTestMigration('NoTable');
        // Do NOT call initMigrations()

        $output = $this->executeMultiCommand([
            SkipMigrationsCommand::class,
            '--all' => '',
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('schema_changes', $outputStr);
        $this->assertStringContainsString('migrations:ini', $outputStr);
        $this->assertEquals(1, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testSkipSingleByName() {
        $this->createTestMigration('SkipSingle1');
        $this->createTestMigration('SkipSingle2');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            SkipMigrationsCommand::class,
            '--name' => 'App\\Database\\Migrations\\SkipSingle1',
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Skipped: App\\Database\\Migrations\\SkipSingle1', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testSkipUpTo() {
        $this->createTestMigration('UpToMig');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            SkipMigrationsCommand::class,
            '--up-to' => 'UpToMig',
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Skipped: App\\Database\\Migrations\\UpToMig', $outputStr);
        $this->assertStringContainsString('Total skipped:', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testSkipWithInvalidConnection() {
        $output = $this->executeMultiCommand([
            SkipMigrationsCommand::class,
            '--all' => '',
            '--connection' => 'ghost'
        ]);

        $this->assertEquals([
            "Error: Connection 'ghost' not found.\n"
        ], $output);
        $this->assertEquals(1, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testSkipWithNoConnections() {
        App::getConfig()->removeAllDBConnections();

        $output = $this->executeMultiCommand([
            SkipMigrationsCommand::class,
            '--all' => ''
        ]);

        $this->assertEquals([
            "Info: No database connections configured.\n"
        ], $output);
        $this->assertEquals(1, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testSkipWithNoMigrations() {
        $output = $this->executeMultiCommand([
            SkipMigrationsCommand::class,
            '--all' => '',
            '--connection' => 'test-connection'
        ]);

        $this->assertEquals([
            "Info: No migrations found.\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testSkipWithNoModeFlag() {
        $this->createTestMigration('NoMode1');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            SkipMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Provide --name, --all, or --up-to', $outputStr);
        $this->assertEquals(1, $this->getExitCode());
    }

    private function cleanPhpFiles(string $dir): void {
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
            } elseif ($item->isDir() && count(scandir($item->getRealPath())) === 2) {
                rmdir($item->getRealPath());
            }
        }
    }

    private function cleanupMigrations(): void {
        $dir = APP_PATH.'Database'.DS.'Migrations';
        $this->cleanPhpFiles($dir);
    }

    private function cleanupSeeders(): void {
        $dir = APP_PATH.'Database'.DS.'Seeders';
        $this->cleanPhpFiles($dir);
    }

    private function createTestMigration(string $name): void {
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
    public function up(Database \$db): void {
        // Test migration
    }
    
    public function down(Database \$db): void {
        // Test rollback
    }
}
PHP;

        file_put_contents($dir.DS.$name.'.php', $content);
    }

    private function dropSchemaTable(): void {
        try {
            $conn = App::getConfig()->getDBConnection('test-connection');

            if ($conn !== null) {
                $db = new \WebFiori\Database\Database($conn);
                $db->raw("DROP TABLE IF EXISTS schema_changes")->execute();
                $db->close();
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    // --- Helpers ---

    private function initMigrations(string $env = 'dev'): void {
        $args = [
            InitMigrationsCommand::class,
            '--connection' => 'test-connection'
        ];

        if ($env !== 'dev') {
            $args['--env'] = $env;
        }

        $this->executeMultiCommand($args);
    }

    private function setupTestConnection(): void {
        $this->testConnection = new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', '127.0.0.1', 3306);
        $this->testConnection->setName('test-connection');
        App::getConfig()->addOrUpdateDBConnection($this->testConnection);
    }

    protected function setUp(): void {
        parent::setUp();
        $this->setupTestConnection();
        $this->dropSchemaTable();
        $this->cleanupMigrations();
    }

    protected function tearDown(): void {
        $this->cleanupMigrations();
        $this->cleanupSeeders();
        $this->dropSchemaTable();
        parent::tearDown();
    }
}
