<?php
namespace webfiori\framework\test\cli;

use webfiori\database\ConnectionInfo;
use webfiori\database\migration\MigrationsRunner;
use webfiori\framework\App;
use webfiori\framework\cli\CLITestCase;
use webfiori\framework\cli\commands\RunMigrationsCommand;
use webfiori\framework\writers\DatabaseMigrationWriter;
/**
 * @author Ibrahim
 */
class RunMigrationsCommantTest extends CLITestCase {
    /**
     * @test
     */
    public function testRunMigrations00() {
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: No migrations were found in the namespace '\app\database\migrations'.\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--connection' => 'ABC'
        ]));
        $this->assertEquals(0, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations01() {
        App::getConfig()->removeAllDBConnections();
        $clazz = $this->createMigration();
        $this->assertTrue(class_exists($clazz));
        
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Found 1 migration(s).\n",
            "Info: No connections were found in application configuration.\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--connection' => 'ABC'
        ]));
        $this->removeClass($clazz);
        $this->assertEquals(0, $this->getExitCode());
        App::getConfig()->removeAllDBConnections();
    }
    /**
     * @test
     */
    public function testRunMigrations02() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db');
        $conn->setName('default-conn');
        $clazz = $this->createMigration();
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Found 1 migration(s).\n",
            "Error: No connection was found which has the name 'ABC'.\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--connection' => 'ABC'
        ]));
        $this->removeClass($clazz);
        $this->assertEquals(-2, $this->getExitCode());
        App::getConfig()->removeAllDBConnections();
    }
    /**
     * @test
     */
    public function testRunMigrations03() {
        $conn = new ConnectionInfo('mysql', 'root', 'x123456', 'testing_db');
        $conn->setName('default-conn');
        $clazz = $this->createMigration();
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Found 1 migration(s).\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Error: Invalid answer.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Error: Unable to connect to database: 2002 - No such file or directory\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
        ], [
            '7',
            ''
        ]));
        $this->assertEquals(-1, $this->getExitCode());
        App::getConfig()->removeAllDBConnections();
    }
    /**
     * @test
     */
    public function testRunMigrations04() {
        $this->assertEquals([
            "Error: The argument --runner has invalid value: Class \"\app\database\migrations\" does not exist.\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations',
        ]));
        $this->assertEquals(-2, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations05() {
        $this->assertEquals([
            "Error: The argument --runner has invalid value: Exception: \"Call to private webfiori\\framework\App::__construct() from scope webfiori\\framework\cli\commands\RunMigrationsCommand\".\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\webfiori\\framework\\App',
        ]));
        $this->assertEquals(-2, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations06() {
        $this->assertEquals([
            "Info: No migrations where found in the namespace '\app\database\migrations\\emptyRunner'.\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\emptyRunner\XRunner',
        ]));
        $this->assertEquals(0, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations07() {
        $conn = new ConnectionInfo('mssql', 'sa', '1234567890@Eu', 'testing_dbx', SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $clazz = $this->createMigration();
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Found 1 migration(s).\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Error: Invalid answer.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Error: Unable to connect to database: 18456 - [Microsoft][ODBC Driver 18 for SQL Server][SQL Server]Login failed for user 'sa'.\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
        ], [
            '7',
            ''
        ]));
        $this->assertEquals(-1, $this->getExitCode());
        App::getConfig()->removeAllDBConnections();
    }
    /**
     * @test
     */
    public function testRunMigrations08() {
        $conn = new ConnectionInfo('mssql', 'sa', '1234567890@Eu', 'testing_db', SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $clazz = $this->createMigration();
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Found 1 migration(s).\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Error: Invalid answer.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Executing migration...\n",
            "Error: Failed to execute migrations due to following:\n",
            "208 - [Microsoft][ODBC Driver 18 for SQL Server][SQL Server]Invalid object name 'migrations'.\n",
            "Info: No migrations were executed.\n"
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
        ], [
            '7',
            ''
        ]));
        $this->assertEquals(-1, $this->getExitCode());
        App::getConfig()->removeAllDBConnections();
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function testRunMigrations09() {
        $conn = new ConnectionInfo('mssql', 'sa', '1234567890@Eu', 'testing_db', SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $clazz = $this->createMigration();
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->assertEquals([
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Error: Invalid answer.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Initializing migrations table...\n",
            "Success: Migrations table succesfully created.\n",
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Found 1 migration(s).\n",
            "Executing migration...\n",
            "Success: Migration 'Migration000' applied successfuly.\n",
            "Info: Number of applied migrations: 1\n",
            "Names of applied migrations:\n",
            "- Migration000\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--ini'
        ], [
            '7',
            ''
        ]));
        $this->assertEquals(0, $this->getExitCode());
        App::getConfig()->removeAllDBConnections();
        $this->removeMigTable($conn);
        $this->removeClass($clazz);
    }
    /**
     * @test
     */
    public function testRunMigrations10() {
        $conn = new ConnectionInfo('mssql', 'sa', '1234567890@Eu', 'testing_db', SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $clazz = $this->createMigration('Cool One');
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->assertEquals([
            "Initializing migrations table...\n",
            "Success: Migrations table succesfully created.\n",
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Found 1 migration(s).\n",
            "Executing migration...\n",
            "Success: Migration 'Cool One' applied successfuly.\n",
            "Info: Number of applied migrations: 1\n",
            "Names of applied migrations:\n",
            "- Cool One\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--ini',
            '--connection' => 'default-conn'
        ], [

        ]));
        $this->assertEquals(0, $this->getExitCode());
        App::getConfig()->removeAllDBConnections();
        $this->removeClass($clazz);
        $this->removeMigTable($conn);
    }
    /**
     * @test
     */
    public function testRunMigrations11() {
        $conn = new ConnectionInfo('mssql', 'sa', '1234567890@Eu', 'testing_db', SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $clazz = $this->createMigration('Cool One');
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->assertEquals([
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Initializing migrations table...\n",
            "Success: Migrations table succesfully created.\n",
            "Info: Using default namespace for migrations.\n",
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Found 1 migration(s).\n",
            "Executing migration...\n",
            "Success: Migration 'Cool One' applied successfuly.\n",
            "Info: Number of applied migrations: 1\n",
            "Names of applied migrations:\n",
            "- Cool One\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ini',
        ], [
            ''
        ]));
        $this->assertEquals(0, $this->getExitCode());
        App::getConfig()->removeAllDBConnections();
        $this->removeClass($clazz);
        $this->removeMigTable($conn);
    }
    /**
     * @test
     */
    public function testRunMigrations12() {
        $conn = new ConnectionInfo('mssql', 'sa', '1234567890@Eux', 'testing_db', SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->assertEquals([
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Initializing migrations table...\n",
            "Error: Unable to create migrations table due to following:\n",
            "Not connected to database.\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ini',
        ], [
            ''
        ]));
        $this->assertEquals(-1, $this->getExitCode());
        App::getConfig()->removeAllDBConnections();
    }
    /**
     * @test
     */
    public function testRunMigrations13() {
        $this->assertEquals([
            "Info: No connections were found in application configuration.\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ini',
        ], [
            ''
        ]));
        $this->assertEquals(0, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations14() {
        $this->assertEquals([
            "Initializing migrations table...\n",
            "Error: Unable to create migrations table due to following:\n",
            "Connection information not set.\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\emptyRunner\XRunner',
            '--ini'
        ]));
        $this->assertEquals(-1, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations15() {
        $this->assertEquals([
            "Initializing migrations table...\n",
            "Success: Migrations table succesfully created.\n",
            "Executing migration...\n",
            "Success: Migration 'First One' applied successfuly.\n",
            "Executing migration...\n",
            "Success: Migration 'Second one' applied successfuly.\n",
            "Executing migration...\n",
            "Success: Migration 'Third One' applied successfuly.\n",
            "Info: Number of applied migrations: 3\n",
            "Names of applied migrations:\n",
            "- First One\n",
            "- Second one\n",
            "- Third One\n"
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multi\MultiRunner',
            '--ini'
        ]));
        $this->assertEquals(0, $this->getExitCode());
    }
    /**
     * @test
     * @depends testRunMigrations15
     */
    public function testRunMigrations16() {
        $this->assertEquals([
            "Info: No migrations were executed.\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multi\MultiRunner',
        ]));
        $this->assertEquals(0, $this->getExitCode());
        $this->removeMigTable();
    }
    /**
     * @test
     */
    public function testRunMigrations17() {
        $this->assertEquals([
            "Error: The argument --runner has invalid value: \"\app\database\migrations\multi\Migration000\" is not an instance of \"MigrationsRunner\".\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multi\Migration000',
        ]));
        $this->assertEquals(-2, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations18() {
        $this->assertEquals([
            "Info: Using default namespace for migrations.\n",
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: No migrations were found in the namespace '\app\database\migrations'.\n"
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            
        ]));
        $this->assertEquals(0, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations19() {
        $this->assertEquals([
            "Initializing migrations table...\n",
            "Success: Migrations table succesfully created.\n",
            "Executing migration...\n",
            "Success: Migration 'First One' applied successfuly.\n",
            "Executing migration...\n",
            "Success: Migration 'Second one' applied successfuly.\n",
            "Executing migration...\n",
            "Error: Failed to execute migration due to following:\n",
            "Call to undefined method app\database\migrations\multiErr\MultiErrRunner::x()\n",
            "Warning: Execution stopped.\n",
            "Info: Number of applied migrations: 2\n",
            "Names of applied migrations:\n",
            "- First One\n",
            "- Second one\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multiErr\MultiErrRunner',
            '--ini'
        ]));
        $this->assertEquals(0, $this->getExitCode());
    }
    private function createMigration(?string $name = null) : string {
        $runner = new MigrationsRunner(APP_PATH.DS.'database'.DS.'migrations'.DS.'commands', '\\app\\database\\migrations\\commands', null);
        $writer = new DatabaseMigrationWriter($runner);
        if ($name !== null) {
            $writer->setMigrationName($name);
        }
        $writer->writeClass();
        return $writer->getName(true);
    }
    private function removeMigTable(?ConnectionInfo $conn = null) {
        if ($conn === null) {
            $conn = new ConnectionInfo('mssql', 'sa', '1234567890@Eu', 'testing_db', SQL_SERVER_HOST, 1433, [
                'TrustServerCertificate' => 'true'
            ]);
        }
        $runner = new MigrationsRunner(APP_PATH.DS.'database'.DS.'migrations'.DS.'commands', '\\app\\database\\migrations\\commands', $conn);
        $runner->dropMigrationsTable();
    }
}
