<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\RollbackMigrationsCommand;

/**
 * Test cases for RollbackMigrationsCommand.
 *
 * @author Ibrahim
 */
class RollbackMigrationsCommandTest extends CLITestCase {
    private ConnectionInfo $testConnection;

    /**
     * @test
     */
    public function testRollbackWithNoConnections() {
        App::getConfig()->removeAllDBConnections();

        $output = $this->executeMultiCommand([
            RollbackMigrationsCommand::class
        ]);

        $this->assertEquals([
            "Info: No database connections configured.\n"
        ], $output);
        $this->assertEquals(1, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testRollbackWithInvalidConnection() {
        $output = $this->executeMultiCommand([
            RollbackMigrationsCommand::class,
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
    public function testRollbackWithNoMigrations() {
        $output = $this->executeMultiCommand([
            RollbackMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $this->assertEquals([
            "Rolling back last batch...\n",
            "Info: No migrations to rollback.\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testRollbackAllWithNoMigrations() {
        $output = $this->executeMultiCommand([
            RollbackMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--all'
        ]);

        $this->assertEquals([
            "Rolling back all migrations...\n",
            "Info: No migrations to rollback.\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testRollbackBatchWithNoMigrations() {
        $output = $this->executeMultiCommand([
            RollbackMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--batch' => '1'
        ]);

        $this->assertEquals([
            "Rolling back batch 1...\n",
            "Info: No migrations to rollback.\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testRollbackLastBatchWithMigrations() {
        $this->createTestMigration('RollbackTest1');
        $this->initAndRunMigrations();

        $output = $this->executeMultiCommand([
            RollbackMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Rolling back last batch...', $outputStr);
        $this->assertStringContainsString('Rolled back: App\\Database\\Migrations\\RollbackTest1', $outputStr);
        $this->assertStringContainsString('Info: Total rolled back: 1', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testRollbackSpecificBatchWithMigrations() {
        $this->createTestMigration('Batch1Migration');
        $this->initAndRunMigrations();

        $output = $this->executeMultiCommand([
            RollbackMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Rolling back last batch...', $outputStr);
        $this->assertStringContainsString('Rolled back: App\\Database\\Migrations\\Batch1Migration', $outputStr);
        $this->assertStringContainsString('Info: Total rolled back: 1', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testRollbackAllWithMigrations() {
        $this->createTestMigration('Migration1');
        $this->createTestMigration('Migration2');
        $this->initAndRunMigrations();

        $output = $this->executeMultiCommand([
            RollbackMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--all'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Rolling back all migrations...', $outputStr);
        $this->assertStringContainsString('Rolled back: App\\Database\\Migrations\\Migration1', $outputStr);
        $this->assertStringContainsString('Rolled back: App\\Database\\Migrations\\Migration2', $outputStr);
        $this->assertStringContainsString('Info: Total rolled back: 2', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testRollbackWithCustomEnv() {
        $this->createTestMigration('EnvTest1');
        $this->initAndRunMigrations('staging');

        $output = $this->executeMultiCommand([
            RollbackMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--env' => 'staging'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Rolling back last batch...', $outputStr);
        $this->assertStringContainsString('Rolled back: App\\Database\\Migrations\\EnvTest1', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    private function initAndRunMigrations(string $env = 'dev'): void {
        $args = [
            'WebFiori\\Framework\\Cli\\Commands\\InitMigrationsCommand',
            '--connection' => 'test-connection'
        ];
        
        if ($env !== 'dev') {
            $args['--env'] = $env;
        }
        
        $this->executeMultiCommand($args);
        
        $args = [
            'WebFiori\\Framework\\Cli\\Commands\\RunMigrationsCommandNew',
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
        $this->cleanupMigrations();
    }

    protected function tearDown(): void {
        $this->cleanupMigrations();
        App::getConfig()->removeAllDBConnections();
        parent::tearDown();
    }
}
