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
