<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\InitMigrationsCommand;

/**
 * Test cases for InitMigrationsCommand.
 *
 * @author Ibrahim
 */
class InitMigrationsCommandTest extends CLITestCase {
    private ConnectionInfo $testConnection;

    /**
     * @test
     */
    public function testInitWithNoConnections() {
        App::getConfig()->removeAllDBConnections();

        $output = $this->executeMultiCommand([
            InitMigrationsCommand::class
        ]);

        $this->assertEquals([
            "Info: No database connections configured.\n"
        ], $output);
        $this->assertEquals(1, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testInitWithInvalidConnection() {
        $output = $this->executeMultiCommand([
            InitMigrationsCommand::class,
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
    public function testInitMigrationsTable() {
        $output = $this->executeMultiCommand([
            InitMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $this->assertEquals([
            "Creating migrations tracking table...\n",
            "Success: Migrations table created successfully.\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testInitWithCustomEnv() {
        $output = $this->executeMultiCommand([
            InitMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--env' => 'staging'
        ]);

        $this->assertEquals([
            "Creating migrations tracking table...\n",
            "Success: Migrations table created successfully.\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
    }

    /**
     * @test
     */
    public function testInitTableAlreadyExists() {
        // Create table first
        $this->executeMultiCommand([
            InitMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        // Try to create again
        $output = $this->executeMultiCommand([
            InitMigrationsCommand::class,
            '--connection' => 'test-connection'
        ]);

        $this->assertEquals([
            "Creating migrations tracking table...\n",
            "Success: Migrations table created successfully.\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
    }

    private function setupTestConnection(): void {
        $this->testConnection = new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', '127.0.0.1', 3306);
        $this->testConnection->setName('test-connection');
        App::getConfig()->addOrUpdateDBConnection($this->testConnection);
    }

    protected function setUp(): void {
        parent::setUp();
        $this->setupTestConnection();
    }

    protected function tearDown(): void {
        App::getConfig()->removeAllDBConnections();
        parent::tearDown();
    }
}
