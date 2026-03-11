<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\FreshMigrationsCommand;

/**
 * Test cases for FreshMigrationsCommand.
 *
 * @author Ibrahim
 */
class FreshMigrationsCommandTest extends CLITestCase {
    private ConnectionInfo $testConnection;

    /**
     * @test
     */
    public function testFreshWithNoConnections() {
        App::getConfig()->removeAllDBConnections();

        $output = $this->executeMultiCommand([
            FreshMigrationsCommand::class
        ]);

        $this->assertEquals([
            "Info: No database connections configured.\n"
        ], $output);
        $this->assertEquals(1, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testFreshWithInvalidConnection() {
        $output = $this->executeMultiCommand([
            FreshMigrationsCommand::class,
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
    public function testFreshWithNoMigrations() {
        $output = $this->executeMultiCommand([
            FreshMigrationsCommand::class,
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
    public function testFreshWithMigrations() {
        $this->createTestMigration('FreshTest1');
        $this->createTestMigration('FreshTest2');
        $this->initAndRunMigrations();

        $output = $this->executeMultiCommand([
            FreshMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Rolling back all migrations...', $outputStr);
        $this->assertStringContainsString('Rolled back: App\\Database\\Migrations\\FreshTest1', $outputStr);
        $this->assertStringContainsString('Rolled back: App\\Database\\Migrations\\FreshTest2', $outputStr);
        $this->assertStringContainsString('Running database changes...', $outputStr);
        $this->assertStringContainsString('Applied: App\\Database\\Migrations\\FreshTest1', $outputStr);
        $this->assertStringContainsString('Applied: App\\Database\\Migrations\\FreshTest2', $outputStr);
        $this->assertStringContainsString('Info: Applied: 2 migration(s)', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testFreshWithNoAppliedMigrations() {
        $this->createTestMigration('FreshTest3');
        $this->initMigrations();

        $output = $this->executeMultiCommand([
            FreshMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Rolling back all migrations...', $outputStr);
        $this->assertStringContainsString('Info: No migrations were rolled back.', $outputStr);
        $this->assertStringContainsString('Running database changes...', $outputStr);
        $this->assertStringContainsString('Applied: App\\Database\\Migrations\\FreshTest3', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testFreshWithCustomEnv() {
        $this->createTestMigration('EnvFreshTest');
        $this->initAndRunMigrations('staging');

        $output = $this->executeMultiCommand([
            FreshMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--env' => 'staging'
        ]);

        $outputStr = implode('', $output);
        $this->assertStringContainsString('Rolling back all migrations...', $outputStr);
        $this->assertStringContainsString('Running database changes...', $outputStr);
        $this->assertStringContainsString('Applied: App\\Database\\Migrations\\EnvFreshTest', $outputStr);
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

    private function initAndRunMigrations(string $env = 'dev'): void {
        $this->initMigrations($env);
        
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
        $this->dropSchemaTable();
        App::getConfig()->removeAllDBConnections();
        parent::tearDown();
    }
}
