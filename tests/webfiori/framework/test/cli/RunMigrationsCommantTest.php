<?php
namespace webfiori\framework\test\cli;

use app\database\migrations\multiErr\MultiErrRunner;
use webfiori\database\ConnectionInfo;
use webfiori\database\DatabaseException;
use webfiori\database\migration\MigrationsRunner;
use webfiori\framework\App;
use webfiori\framework\cli\CLITestCase;
use webfiori\framework\cli\commands\RunMigrationsCommand;
use webfiori\framework\writers\DatabaseMigrationWriter;
use const APP_PATH;
use const DS;
use const SQL_SERVER_DB;
use const SQL_SERVER_HOST;
use const SQL_SERVER_PASS;
use const SQL_SERVER_USER;
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
            "Info: No migrations found in the namespace '\app\database\migrations'.\n",
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
        
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--connection' => 'ABC'
        ]);
        $this->removeClass($clazz);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Info: No connections were found in application configuration.\n",
        ], $output);
        
        $this->assertEquals(-1, $this->getExitCode());
        
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
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--connection' => 'ABC'
        ]);
        $this->removeClass($clazz);
        App::getConfig()->removeAllDBConnections();
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Error: No connection was found which has the name 'ABC'.\n",
        ], $output);
        
        $this->assertEquals(-1, $this->getExitCode());
        
    }
    /**
     * @test
     */
    public function testRunMigrations03() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, 'x123456', SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $clazz = $this->createMigration('PO', 'MyPOMigration');
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
        ], [
            '7',
            ''
        ]);
        App::getConfig()->removeAllDBConnections();
        $this->removeClass($clazz);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Error: Invalid answer.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Error: Unable to connect to database: 18456 - [Microsoft][ODBC Driver ".ODBC_VERSION." for SQL Server][SQL Server]Login failed for user 'sa'.\n",
        ], $output);
        $this->assertEquals(-1, $this->getExitCode());
        
        
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
        $this->assertEquals(-1, $this->getExitCode());
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
        $this->assertEquals(-1, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations06() {
        $this->assertEquals([
            "Checking namespace '\app\database\migrations\\emptyRunner' for migrations...\n",
            "Info: No migrations found in the namespace '\app\database\migrations\\emptyRunner'.\n",
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
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, 'testing_dbx', SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $clazz = $this->createMigration('Cool', 'CoolOne');
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
        ], [
            '7',
            ''
        ]);
        $this->removeClass($clazz);
        App::getConfig()->removeAllDBConnections();
        $err = ODBC_VERSION == 17 ? "Error: Unable to connect to database: 4060 - [Microsoft][ODBC Driver ".ODBC_VERSION." for SQL Server][SQL Server]Cannot open database \"testing_dbx\" requested by the login. The login failed.\n"
                : "Error: Unable to connect to database: 18456 - [Microsoft][ODBC Driver ".ODBC_VERSION." for SQL Server][SQL Server]Login failed for user 'sa'.\n";
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Error: Invalid answer.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            $err,
        ], $output);
        $this->assertEquals(-1, $this->getExitCode());
        
    }
    /**
     * @test
     */
    public function testRunMigrations08() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $this->removeMigTable($conn);
        $clazz = $this->createMigration('Super', 'SuperMigration');
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
        ], [
            '7',
            ''
        ]);
        App::getConfig()->removeAllDBConnections();
        $this->removeClass($clazz);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Error: Invalid answer.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Starting to execute migrations...\n",
            "Error: Failed to execute migration due to following:\n",
            "208 - [Microsoft][ODBC Driver ".ODBC_VERSION." for SQL Server][SQL Server]Invalid object name 'migrations'. (Line 361)\n",
            "Warning: Execution stopped.\n",
            "Info: No migrations were executed.\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        
    }
    /**
     * @test
     */
    public function testRunMigrations09() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $clazz = $this->createMigration();
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--ini'
        ], [
            '7',
            ''
        ]);
        App::getConfig()->removeAllDBConnections();
        $this->removeMigTable($conn);
        $this->removeClass($clazz);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Error: Invalid answer.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Initializing migrations table...\n",
            "Success: Migrations table succesfully created.\n",
            "Starting to execute migrations...\n",
            "Success: Migration 'Migration000' applied successfuly.\n",
            "Info: Number of applied migrations: 1\n",
            "Names of applied migrations:\n",
            "- Migration000\n",
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        
    }
    /**
     * @test
     */
    public function testRunMigrations10() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $clazz = $this->createMigration('Cool One', 'CLSOne');
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--ini',
            '--connection' => 'default-conn'
        ], [

        ]);
        App::getConfig()->removeAllDBConnections();
        $this->removeClass($clazz);
        $this->removeMigTable($conn);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Initializing migrations table...\n",
            "Success: Migrations table succesfully created.\n",
            "Starting to execute migrations...\n",
            "Success: Migration 'Cool One' applied successfuly.\n",
            "Info: Number of applied migrations: 1\n",
            "Names of applied migrations:\n",
            "- Cool One\n",
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        
    }
    /**
     * @test
     */
    public function testRunMigrations11() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $clazz = $this->createMigration('Cool One', 'ColOne');
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ini',
        ], [
            ''
        ]);
        App::getConfig()->removeAllDBConnections();
        $this->removeClass($clazz);
        $this->removeMigTable($conn);
        $this->assertEquals([
            "Info: Using default namespace for migrations.\n",
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Initializing migrations table...\n",
            "Success: Migrations table succesfully created.\n",
            "Starting to execute migrations...\n",
            "Success: Migration 'Cool One' applied successfuly.\n",
            "Info: Number of applied migrations: 1\n",
            "Names of applied migrations:\n",
            "- Cool One\n",
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        
    }
    /**
     * @test
     */
    public function testRunMigrations12() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, '1234567890@Eux', SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $clazz = $this->createMigration('Oh S', 'OhSuperMg');
        App::getConfig()->addOrUpdateDBConnection($conn);
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ini',
        ], [
            ''
        ]);
        App::getConfig()->removeAllDBConnections();
        $this->removeClass($clazz);
        $this->assertEquals([
            "Info: Using default namespace for migrations.\n",
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Select database connection:\n",
            "0: default-conn <--\n",
            "Initializing migrations table...\n",
            "Error: Unable to create migrations table due to following:\n",
            "Unable to connect to database: 18456 - [Microsoft][ODBC Driver ".ODBC_VERSION." for SQL Server][SQL Server]Login failed for user 'sa'.\n",
        ], $output);
        $this->assertEquals(-1, $this->getExitCode());
        
    }
    /**
     * @test
     */
    public function testRunMigrations13() {
        $clazz = $this->createMigration();
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ini',
        ], [
            ''
        ]);
        $this->removeClass($clazz);
        $this->assertEquals([
            "Info: Using default namespace for migrations.\n",
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Info: No connections were found in application configuration.\n",
        ], $output);
        $this->assertEquals(-1, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations14() {
        //$clazz = $this->createMigration();
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\noConn\XRunner',
            '--ini'
        ]);
        //$this->removeClass($clazz);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations\\noConn' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations\\noConn'.\n",
            "Info: No connections were found in application configuration.\n",
        ], $output);
        $this->assertEquals(-1, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations15() {
        $this->assertEquals([
            "Checking namespace '\app\database\migrations\multi' for migrations...\n",
            "Info: Found 3 migration(s) in the namespace '\app\database\migrations\multi'.\n",
            "Initializing migrations table...\n",
            "Success: Migrations table succesfully created.\n",
            "Starting to execute migrations...\n",
            "Success: Migration 'First One' applied successfuly.\n",
            "Success: Migration 'Second one' applied successfuly.\n",
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
            "Checking namespace '\app\database\migrations\multi' for migrations...\n",
            "Info: Found 3 migration(s) in the namespace '\app\database\migrations\multi'.\n",
            "Starting to execute migrations...\n",
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
        $this->assertEquals(-1, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations18() {
        $this->assertEquals([
            "Info: Using default namespace for migrations.\n",
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: No migrations found in the namespace '\app\database\migrations'.\n"
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            
        ]));
        $this->assertEquals(0, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRunMigrations19() {
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multiErr\MultiErrRunner',
            '--ini'
        ]);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations\multiErr' for migrations...\n",
            "Info: Found 3 migration(s) in the namespace '\app\database\migrations\multiErr'.\n",
            "Initializing migrations table...\n",
            "Success: Migrations table succesfully created.\n",
            "Starting to execute migrations...\n",
            "Success: Migration 'First One' applied successfuly.\n",
            "Success: Migration 'Second one' applied successfuly.\n",
            "Error: Failed to execute migration due to following:\n",
            "Call to undefined method app\database\migrations\multiErr\Migration000::x() (Line 22)\n",
            "Warning: Execution stopped.\n",
            "Info: Number of applied migrations: 2\n",
            "Names of applied migrations:\n",
            "- First One\n",
            "- Second one\n",
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $r = new MultiErrRunner();
        $this->removeMigTable($r->getConnectionInfo());
    }
    /**
     * @test
     */
    public function testRollback00() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        $clazz = $this->createMigration('ABC', 'ABC');
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->addOrUpdateDBConnection($conn);
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--rollback',
            '--connection' => 'default-conn',
            '--ini'
        ], [
            ''
        ]);
        App::getConfig()->removeAllDBConnections();
        $this->removeMigTable($conn);
        $this->removeClass($clazz);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Initializing migrations table...\n",
            "Success: Migrations table succesfully created.\n",
            "Rolling back last executed migration...\n",
            "Info: No migration rolled back.\n",
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRollback01() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        
        $ns = '\\app\\database\\migrations';
        $clazz = $this->createAndRunMigration($conn, $ns, 'ABCD Cool', 'ABCCool');
        App::getConfig()->addOrUpdateDBConnection($conn);
        
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--rollback',
            '--connection' => 'default-conn',
        ]);
        App::getConfig()->removeAllDBConnections();
        $this->removeMigTable($conn);
        $this->removeClass($clazz);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Rolling back last executed migration...\n",
            "Success: Migration 'ABCD Cool' was successfully rolled back.\n",
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRollback02() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');
        
        $ns = '\\app\\database\\migrations';
        $clazz = $this->createAndRunMigration($conn, $ns, 'ABCD Cool', 'ABCCool');
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--rollback',
            '--connection' => 'default-conn',
        ]);
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--rollback',
            '--connection' => 'default-conn',
        ]);
        App::getConfig()->removeAllDBConnections();
        $this->removeMigTable($conn);
        $this->removeClass($clazz);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations' for migrations...\n",
            "Info: Found 1 migration(s) in the namespace '\app\database\migrations'.\n",
            "Rolling back last executed migration...\n",
            "Info: No migration rolled back.\n",
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testRollback03() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');

        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multi\MultiRunner',
            '--ini'
        ]);
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multi\\MultiRunner',
            '--rollback',
        ]);

        App::getConfig()->removeAllDBConnections();
        
        
        $this->assertEquals([
            "Checking namespace '\app\database\migrations\multi' for migrations...\n",
            "Info: Found 3 migration(s) in the namespace '\app\database\migrations\multi'.\n",
            "Rolling back last executed migration...\n",
            "Success: Migration 'Third One' was successfully rolled back.\n",
        ], $output);
        
        $this->assertEquals(0, $this->getExitCode());
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multi\\MultiRunner',
            '--rollback',
        ]);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations\multi' for migrations...\n",
            "Info: Found 3 migration(s) in the namespace '\app\database\migrations\multi'.\n",
            "Rolling back last executed migration...\n",
            "Success: Migration 'Second one' was successfully rolled back.\n",
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multi\\MultiRunner',
            '--rollback',
        ]);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations\multi' for migrations...\n",
            "Info: Found 3 migration(s) in the namespace '\app\database\migrations\multi'.\n",
            "Rolling back last executed migration...\n",
            "Success: Migration 'First One' was successfully rolled back.\n",
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multi\\MultiRunner',
            '--rollback',
        ]);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations\multi' for migrations...\n",
            "Info: Found 3 migration(s) in the namespace '\app\database\migrations\multi'.\n",
            "Rolling back last executed migration...\n",
            "Info: No migration rolled back.\n",
        ], $output);
        $this->removeMigTable($conn);
    }
    /**
     * @test
     */
    public function testRollback04() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');

        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multi\MultiRunner',
            '--ini'
        ]);
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multi\\MultiRunner',
            '--rollback',
            '--all'
        ]);
        
        $this->assertEquals([
            "Checking namespace '\app\database\migrations\multi' for migrations...\n",
            "Info: Found 3 migration(s) in the namespace '\app\database\migrations\multi'.\n",
            "Rolling back migrations...\n",
            "Success: Migration 'Third One' was successfully rolled back.\n",
            "Success: Migration 'Second one' was successfully rolled back.\n",
            "Success: Migration 'First One' was successfully rolled back.\n",
        ], $output);
        
        $this->assertEquals(0, $this->getExitCode());
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multi\\MultiRunner',
            '--rollback',
            '--all'
        ]);
        $this->assertEquals([
            "Checking namespace '\app\database\migrations\multi' for migrations...\n",
            "Info: Found 3 migration(s) in the namespace '\app\database\migrations\multi'.\n",
            "Rolling back migrations...\n",
            "Info: No migration rolled back.\n",
        ], $output);
        $this->removeMigTable($conn);
    }
    /**
     * @test
     */
    public function testRollback05() {
        $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
            'TrustServerCertificate' => 'true'
        ]);
        $conn->setName('default-conn');

        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multiDownErr\MultiErrRunner',
            '--ini'
        ]);
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multiDownErr\MultiErrRunner',
            '--rollback',
            '--all'
        ]);
        
        $this->assertEquals([
            "Checking namespace '\app\database\migrations\multiDownErr' for migrations...\n",
            "Info: Found 3 migration(s) in the namespace '\app\database\migrations\multiDownErr'.\n",
            "Rolling back migrations...\n",
            "Success: Migration 'Third One' was successfully rolled back.\n",
            "Error: Failed to execute migration due to following:\n",
            "Call to undefined method webfiori\database\migration\MigrationsRunner::do() (Line 30)\n",
            "Warning: Execution stopped.\n",
        ], $output);
        
        $this->assertEquals(-1, $this->getExitCode());
        $output = $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\app\\database\\migrations\\multiDownErr\MultiErrRunner',
            '--rollback',
            '--all'
        ]);
        
        $this->assertEquals([
            "Checking namespace '\app\database\migrations\multiDownErr' for migrations...\n",
            "Info: Found 3 migration(s) in the namespace '\app\database\migrations\multiDownErr'.\n",
            "Rolling back migrations...\n",
            "Error: Failed to execute migration due to following:\n",
            "Call to undefined method webfiori\database\migration\MigrationsRunner::do() (Line 30)\n",
            "Warning: Execution stopped.\n",
        ], $output);
        $this->removeMigTable($conn);
    }
    private function createAndRunMigration(ConnectionInfo $connection, string $ns, ?string $name = null, ?string $className = null) : string {
        $clazz = $this->createMigration($name, $className);
        App::getConfig()->addOrUpdateDBConnection($connection);
        $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => $ns,
            '--connection' => $connection->getName(),
            '--ini'
        ]);
        $this->assertTrue(class_exists($clazz));
        App::getConfig()->removeDBConnection($connection->getName());
        return $clazz;
    }
    private function createMigration(?string $name = null, ?string $className = null) : string {
        $runner = new MigrationsRunner(APP_PATH.DS.'database'.DS.'migrations'.DS.'commands', '\\app\\database\\migrations\\commands', null);
        $writer = new DatabaseMigrationWriter($runner);
        if ($name !== null) {
            $writer->setMigrationName($name);
        }
        if ($className !== null) {
            $writer->setClassName($className);
        }
        $writer->writeClass();
        return $writer->getName(true);
    }
    private function removeMigTable(?ConnectionInfo $conn = null) {
        if ($conn === null) {
            $conn = new ConnectionInfo('mssql', SQL_SERVER_USER, SQL_SERVER_PASS, SQL_SERVER_DB, SQL_SERVER_HOST, 1433, [
                'TrustServerCertificate' => 'true'
            ]);
        }
        $runner = new MigrationsRunner(APP_PATH.DS.'database'.DS.'migrations'.DS.'commands', '\\app\\database\\migrations\\commands', $conn);
        try{
            $runner->dropMigrationsTable();
        } catch (DatabaseException $ex) {

        }
    }
}
