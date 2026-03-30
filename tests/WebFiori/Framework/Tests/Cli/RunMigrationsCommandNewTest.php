<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\RunMigrationsCommandNew;

/**
 * Test cases for RunMigrationsCommandNew.
 *
 * @author Ibrahim
 */
class RunMigrationsCommandNewTest extends CLITestCase {
    private ConnectionInfo $testConnection;

    /**
     * @test
     */
    public function testRunWithNoConnections() {
        App::getConfig()->removeAllDBConnections();

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class
        ]);

        $this->assertEquals([
            "Info: No database connections configured.\n"
        ], $output);
        $this->assertEquals(1, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testRunWithInvalidConnection() {
        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'invalid-connection'
        ]);

        $this->assertEquals([
            "Error: Connection 'invalid-connection' not found.\n"
        ], $output);
        $this->assertEquals(1, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testRunWithNoMigrations() {
        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
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
    public function testRunWithPendingMigrations() {
        $this->createTestMigration('RunTest1');
        $this->createTestMigration('RunTest2');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Running migrations...', $outputStr);
        $this->assertStringContainsString('Applied: App\\Database\\Migrations\\RunTest1', $outputStr);
        $this->assertStringContainsString('Applied: App\\Database\\Migrations\\RunTest2', $outputStr);
        $this->assertStringContainsString('Info: Applied: 2 migration(s)', $outputStr);
        $this->assertStringContainsString('Info: Time:', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testRunWithAlreadyAppliedMigrations() {
        $this->createTestMigration('AlreadyApplied');
        $this->initMigrations();
        
        // Run migrations first time
        $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection'
        ]);

        // Run again - should skip
        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Running migrations...', $outputStr);
        $this->assertStringContainsString('Info: Applied: 0 migrations', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testRunWithCustomEnv() {
        $this->createTestMigration('EnvTest');
        $this->initMigrations('staging');

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection',
            '--env' => 'staging'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Running migrations...', $outputStr);
        $this->assertStringContainsString('Applied: App\\Database\\Migrations\\EnvTest', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    private function initMigrations(string $env = 'dev'): void {
        $args = [
            'WebFiori\\Framework\\Cli\\Commands\\InitMigrationsCommand',
            '--connection' => 'test-connection'
        ];
        
        if ($env !== 'dev') {
            $args['--env'] = $env;
        }
        
        $this->executeMultiCommand($args);
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

    private function cleanupMigrations(): void {
        $dir = APP_PATH.'Database'.DS.'Migrations';

        if (is_dir($dir)) {
            foreach (glob($dir.DS.'*.php') as $file) {
                if (basename($file) !== '.gitkeep') {
                    unlink($file);
                }
            }
        }
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
        App::getConfig()->removeAllDBConnections();
        parent::tearDown();
    }

    private function dropSchemaTable(): void {
        try {
            $connection = App::getConfig()->getDBConnection('test-connection');
            if ($connection !== null) {
                $runner = new \WebFiori\Database\Schema\SchemaRunner($connection);
                $runner->dropSchemaTable();
            }
        } catch (\Throwable $e) {
            // Ignore errors during cleanup
        }
    }


    /**
     * @test
     * Covers: getConnection() interactive path (no --connection arg, select from list)
     */
    public function testRunWithInteractiveConnectionSelection() {
        $output = $this->executeSingleCommand(new RunMigrationsCommandNew(), [], [
            'test-connection', // select connection interactively
        ]);

        $this->assertContains("Info: No migrations found.
", $output);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     * Covers: catch block — schema_changes table missing (code 1146) → warning branch
     */
    public function testRunWithMissingSchemaTable() {
        $this->createTestMigration('SchemaTableMissing');
        // Do NOT call initMigrations() so schema_changes table doesn't exist

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection',
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Table "schema_changes" does not exist', $outputStr);
        $this->assertStringContainsString('Run "migrations:ini" to create the table.', $outputStr);
        $this->assertEquals(1, $this->getExitCode());
    }

    /**
     * @test
     * Covers: runMigrations() $skipped non-empty — environment mismatch
     */
    public function testRunWithSkippedMigrations() {
        $this->createMigrationWithEnvironment('SkippedEnvMigration', 'production');
        $this->initMigrations('dev');

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection',
            '--env'        => 'dev',
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Skipped: App\Database\Migrations\SkippedEnvMigration', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     * Covers: runMigrations() $failed non-empty — migration up() throws
     */
    public function testRunWithFailedMigration() {
        $this->createFailingMigration('FailingMigration');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection',
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Failed: App\Database\Migrations\FailingMigration', $outputStr);
        $this->assertEquals(1, $this->getExitCode());
    }

    /**
     * @test
     * Covers: runMigrations() $seedersCount > 0 — seeder applied
     */
    public function testRunWithSeeder() {
        $this->createTestSeeder('RunTestSeeder');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            RunMigrationsCommandNew::class,
            '--connection' => 'test-connection',
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Applied: App\Database\Seeders\RunTestSeeder', $outputStr);
        $this->assertStringContainsString('Info: Applied: 1 seeder(s)', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    private function createMigrationWithEnvironment(string $name, string $env): void {
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
    public function getEnvironments(): array { return ['$env']; }
    public function up(Database \$db): void {}
    public function down(Database \$db): void {}
}
PHP;
        file_put_contents($dir.DS.$name.'.php', $content);
    }

    private function createFailingMigration(string $name): void {
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
        throw new \RuntimeException('Intentional failure for testing.');
    }
    public function down(Database \$db): void {}
}
PHP;
        file_put_contents($dir.DS.$name.'.php', $content);
    }

    private function createTestSeeder(string $name): void {
        $dir = APP_PATH.'Database'.DS.'Seeders';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $content = <<<PHP
<?php
namespace App\Database\Seeders;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractSeeder;

class $name extends AbstractSeeder {
    public function run(Database \$db): void {}
    public function rollback(Database \$db): void {}
}
PHP;
        file_put_contents($dir.DS.$name.'.php', $content);
    }

    private function cleanupSeeders(): void {
        $dir = APP_PATH.'Database'.DS.'Seeders';
        if (is_dir($dir)) {
            foreach (glob($dir.DS.'*.php') as $file) {
                unlink($file);
            }
        }
    }
}
