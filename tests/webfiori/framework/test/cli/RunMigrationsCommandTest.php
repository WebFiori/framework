<?php
namespace webfiori\framework\test\cli;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\Schema\SchemaRunner;
use webfiori\framework\App;
use webfiori\framework\cli\CLITestCase;
use webfiori\framework\cli\commands\RunMigrationsCommand;

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
    }
    
    protected function tearDown(): void {
        App::getConfig()->removeAllDBConnections();
        parent::tearDown();
    }
    
    private function setupTestConnection(): void {
        $this->testConnection = new ConnectionInfo('mysql', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', 'localhost', 3306);
        $this->testConnection->setName('test-connection');
        App::getConfig()->addOrUpdateDBConnection($this->testConnection);
    }
    
    /**
     * @test
     */
    public function testExecWithNoConnections(): void {
        App::getConfig()->removeAllDBConnections();
        
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class
        ]);
        
        $this->assertContains("Info: No connections were found in application configuration.\n", $output);
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
        
        $this->assertContains("Error: No connection was found which has the name 'invalid-connection'.\n", $output);
        $this->assertEquals(1, $this->getExitCode());
    }
    
    /**
     * @test
     */
    public function testExecWithInvalidRunnerClass(): void {
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--runner' => 'NonExistentClass'
        ]);
        
        $this->assertContains("Error: The argument --runner has invalid value: Class \"NonExistentClass\" does not exist.\n", $output);
        $this->assertEquals(1, $this->getExitCode());
    }
    
    /**
     * @test
     */
    public function testExecWithInvalidRunnerType(): void {
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--runner' => 'stdClass'
        ]);
        
        $this->assertContains("Error: The argument --runner has invalid value: \"stdClass\" is not an instance of \"SchemaRunner\".\n", $output);
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
        
        $this->assertContains("Initializing migrations table...\n", $output);
        $this->assertEquals(0, $this->getExitCode());
        
        // Verify table was actually created using mysqli
        $mysqli = new \mysqli('localhost', 'root', MYSQL_ROOT_PASSWORD, 'testing_db', 3306);
        $result = $mysqli->query("SHOW TABLES LIKE 'schema_changes'");
        $this->assertEquals(1, $result->num_rows, 'Migrations table should be created');
        $mysqli->close();
    }
    
    /**
     * @test
     */
    public function testExecuteMigrationsWithNoMigrations(): void {
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
    public function testRollbackWithNoMigrations(): void {
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--rollback'
        ]);
        
        $this->assertContains("Info: No migrations found.\n", $output);
        $this->assertEquals(0, $this->getExitCode());
    }
    
    /**
     * @test
     */
    public function testRollbackAllWithNoMigrations(): void {
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--rollback',
            '--all'
        ]);
        
        $this->assertContains("Info: No migrations found.\n", $output);
        $this->assertEquals(0, $this->getExitCode());
    }
    
    /**
     * @test
     */
    public function testExecuteMigrationsWithValidRunner(): void {
        $this->createTestMigrationRunner();
        
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--runner' => 'TestMigrationRunner'
        ]);
        
        // The test runner has no migrations, so it should report no migrations found
        $this->assertContains("Info: No migrations found.\n", $output);
        $this->assertEquals(0, $this->getExitCode());
        
        $this->cleanupTestMigrationRunner();
    }
    
    /**
     * @test
     */
    public function testExceptionHandling(): void {
        $this->createFaultyMigrationRunner();
        
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--connection' => 'test-connection',
            '--runner' => 'FaultyMigrationRunner'
        ]);
        
        // The exception is caught during runner creation
        $this->assertContains("Error: The argument --runner has invalid value: Exception: \"Test exception\".\n", $output);
        $this->assertEquals(1, $this->getExitCode());
        
        $this->cleanupFaultyMigrationRunner();
    }
    
    private function createTestMigrationRunner(): void {
        $code = '<?php
class TestMigrationRunner extends \WebFiori\Database\Schema\SchemaRunner {
    public function __construct() {
        parent::__construct(null);
    }
}';
        file_put_contents(APP_PATH . 'TestMigrationRunner.php', $code);
        require_once APP_PATH . 'TestMigrationRunner.php';
    }
    
    private function cleanupTestMigrationRunner(): void {
        if (file_exists(APP_PATH . 'TestMigrationRunner.php')) {
            unlink(APP_PATH . 'TestMigrationRunner.php');
        }
    }
    
    private function createFaultyMigrationRunner(): void {
        $code = '<?php
class FaultyMigrationRunner extends \WebFiori\Database\Schema\SchemaRunner {
    public function __construct() {
        parent::__construct(null);
        throw new \Exception("Test exception");
    }
}';
        file_put_contents(APP_PATH . 'FaultyMigrationRunner.php', $code);
        require_once APP_PATH . 'FaultyMigrationRunner.php';
    }
    
    private function cleanupFaultyMigrationRunner(): void {
        if (file_exists(APP_PATH . 'FaultyMigrationRunner.php')) {
            unlink(APP_PATH . 'FaultyMigrationRunner.php');
        }
    }
}
