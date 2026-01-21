<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\RunMigrationsCommand;

/**
 * Test cases for RunMigrationsCommand.
 * 
 * @author Ibrahim
 */
class RunMigrationsCommandTest extends CLITestCase {
    
    private ConnectionInfo $testConnection;
    
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
    
    private function setupTestConnection(): void {
        $this->testConnection = new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', '127.0.0.1', 3306);
        $this->testConnection->setName('test-connection');
        App::getConfig()->addOrUpdateDBConnection($this->testConnection);
    }
    
    private function cleanupMigrations(): void {
        $dir = APP_PATH . 'Database' . DS . 'Migrations';
        if (is_dir($dir)) {
            foreach (glob($dir . DS . '*.php') as $file) {
                if (basename($file) !== '.gitkeep') {
                    unlink($file);
                }
            }
        }
    }
    
    /**
     * @test
     */
    public function testExecWithNoConnections(): void {
        App::getConfig()->removeAllDBConnections();
        
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class
        ]);
        
        $this->assertContains("Info: No database connections configured.\n", $output);
        $this->assertEquals(1, $this->getExitCode());
    }
    
    /**
     * @test
     */
    public function testExecWithInvalidConnection(): void {
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--connection' => 'invalid-connection'
        ]);
        
        $this->assertContains("Error: Connection 'invalid-connection' not found.\n", $output);
        $this->assertEquals(1, $this->getExitCode());
    }
    
    /**
     * @test
     */
    public function testInitializeMigrationsTable(): void {
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--init'
        ]);
        
        $this->assertContains("Creating migrations tracking table...\n", $output);
        $this->assertContains("Success: Migrations table created successfully.\n", $output);
        $this->assertEquals(0, $this->getExitCode());
    }
    
    /**
     * @test
     */
    public function testRunWithNoMigrations(): void {
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);
        
        $this->assertContains("Info: No migrations found.\n", $output);
        $this->assertEquals(0, $this->getExitCode());
    }
    
    /**
     * @test
     */
    public function testDryRun(): void {
        // Create a test migration
        $this->createTestMigration('TestMigration');
        
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--dry-run'
        ]);
        
        // Check if output contains expected text
        $outputStr = implode('', $output);
        $this->assertStringContainsString('Pending migrations:', $outputStr);
        $this->assertStringContainsString('TestMigration', $outputStr);
        $this->assertEquals(0, $this->getExitCode());
    }
    
    private function createTestMigration(string $name): void {
        $dir = APP_PATH . 'Database' . DS . 'Migrations';
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
        
        file_put_contents($dir . DS . $name . '.php', $content);
    }
}
