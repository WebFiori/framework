<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Schema\SchemaRunner;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\StepMigrationsCommand;

class StepMigrationsCommandTest extends CLITestCase {
    private ConnectionInfo $testConnection;

    protected function setUp(): void {
        parent::setUp();
        $this->setupTestConnection();
        $this->dropSchemaTable();
        $this->cleanupMigrations();
    }

    protected function tearDown(): void {
        $this->cleanupMigrations();
        $this->dropSchemaTable();
        App::getConfig()->removeAllDBConnections();
        parent::tearDown();
    }

    /** @test */
    public function testNoConnectionsConfigured() {
        App::getConfig()->removeAllDBConnections();

        $output = $this->executeMultiCommand([
            StepMigrationsCommand::class
        ]);

        $this->assertEquals([
            "Info: No database connections configured.\n"
        ], $output);
        $this->assertEquals(1, $this->getExitCode());
    }

    /** @test */
    public function testNoPendingMigrations() {
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            StepMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('No pending migrations.', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /** @test */
    public function testApplyOneMigration() {
        $this->createTestMigration('StepApply1');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            StepMigrationsCommand::class,
            '--connection' => 'test-connection'
        ], [
            '0' // Select "Apply"
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Applied:', $outputStr);
        $this->assertStringContainsString('1 applied, 0 skipped', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /** @test */
    public function testSkipOneMigration() {
        $this->createTestMigration('StepSkip1');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            StepMigrationsCommand::class,
            '--connection' => 'test-connection'
        ], [
            '1' // Select "Skip"
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Skipped:', $outputStr);
        $this->assertStringContainsString('0 applied, 1 skipped', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /** @test */
    public function testQuitBeforeProcessingAll() {
        $this->createTestMigration('StepQuit1');
        $this->createTestMigration('StepQuit2');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            StepMigrationsCommand::class,
            '--connection' => 'test-connection'
        ], [
            '0', // Apply first
            '2'  // Quit on second
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Applied:', $outputStr);
        $this->assertStringContainsString('1 applied, 0 skipped', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /** @test */
    public function testMixedApplyAndSkip() {
        $this->createTestMigration('StepMix1');
        $this->createTestMigration('StepMix2');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            StepMigrationsCommand::class,
            '--connection' => 'test-connection'
        ], [
            '0', // Apply first
            '1'  // Skip second
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('1 applied, 1 skipped', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    private function initMigrations(): void {
        $this->executeMultiCommand([
            'WebFiori\\Framework\\Cli\\Commands\\InitMigrationsCommand',
            '--connection' => 'test-connection'
        ]);
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

        if (!is_dir($dir)) {
            return;
        }

        foreach (glob($dir.DS.'*.php') as $file) {
            unlink($file);
        }
    }

    private function setupTestConnection(): void {
        $this->testConnection = new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', '127.0.0.1', 3306);
        $this->testConnection->setName('test-connection');
        App::getConfig()->addOrUpdateDBConnection($this->testConnection);
    }

    private function dropSchemaTable(): void {
        try {
            $connection = App::getConfig()->getDBConnection('test-connection');

            if ($connection !== null) {
                $runner = new SchemaRunner($connection);
                $runner->dropSchemaTable();
            }
        } catch (\Throwable $e) {
            // Ignore errors during cleanup
        }
    }
}
