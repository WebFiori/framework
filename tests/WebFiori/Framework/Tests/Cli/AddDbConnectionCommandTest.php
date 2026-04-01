<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\AddDbConnectionCommand;

/**
 * Test cases for AddDbConnectionCommand
 *
 * @author Ibrahim
 */
class AddDbConnectionCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testAddDBConnection00() {
        $output = $this->executeSingleCommand(new AddDbConnectionCommand(), [], [
            '0',
            '127.0.0.1',
            "\n", // Hit Enter to pick default value (port 3306)
            'root',
            '123456',
            'testing_db',
            "\n" // Hit Enter to pick default value (connection name)
        ]);

        $count = count(App::getConfig()->getDBConnections());
        $connName = 'db-connection-'.$count;
        $this->assertEquals([
            "Select database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Database host: Enter = '127.0.0.1'\n",
            "Port number: Enter = '3306'\n",
            "Username:\n",
            "Password:\n",
            "******\n",
            "Database name:\n",
            "Give your connection a friendly name: Enter = '$connName'\n",
            "Trying to connect to the database...\n",
            "Success: Connected. Adding the connection...\n",
            "Success: Connection information was stored in application configuration.\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testAddDBConnection01() {
        $connName = 'db-connection-'.(count(App::getConfig()->getDBConnections()) + 1);

        $output = $this->executeSingleCommand(new AddDbConnectionCommand(), [
            'WebFiori',
            'add:db-connection'
        ], [
            '0',
            '127.0.0.1',
            "\n", // Hit Enter to pick default value (port 3306)
            'root',
            'not_correct',
            'testing_db',
            "\n", // Hit Enter to pick default value (connection name)
            'y'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $output = $this->getOutput();

        $this->assertEquals("Select database type:\n", $output[0]);
        $this->assertEquals("0: mysql\n", $output[1]);
        $this->assertEquals("1: mssql\n", $output[2]);
        $this->assertEquals("Database host: Enter = '127.0.0.1'\n", $output[3]);
        $this->assertEquals("Port number: Enter = '3306'\n", $output[4]);
        $this->assertEquals("Username:\n", $output[5]);
        $this->assertEquals("Password:\n", $output[6]);
        $this->assertEquals("***********\n", $output[7]);
        $this->assertEquals("Database name:\n", $output[8]);
        $this->assertEquals("Give your connection a friendly name: Enter = '$connName'\n", $output[9]);
        $this->assertEquals("Trying to connect to the database...\n", $output[10]);
        $this->assertEquals("Trying with 'localhost'...\n", $output[11]);
        $this->assertEquals("Error: Unable to connect to the database.\n", $output[12]);
        $this->assertStringContainsString("Error: Unable to connect to database: 1045 - Access denied for user", $output[13]);
        $this->assertEquals("Would you like to store connection information anyway?(y/N)\n", $output[14]);
        $this->assertEquals("Success: Connection information was stored in application configuration.\n", $output[15]);
    }
    /**
     * @test
     */
    public function testAddDBConnection02() {
        $count = count(App::getConfig()->getDBConnections());
        $connName = 'db-connection-'.($count + 1);

        $output = $this->executeSingleCommand(new AddDbConnectionCommand(), [
            'WebFiori',
            'add:db-connection'
        ], [
            '0',
            '127.0.0.1',
            "\n", // Hit Enter to pick default value (port 3306)
            'root',
            'not_correct',
            'testing_db',
            "\n", // Hit Enter to pick default value (connection name)
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $output = $this->getOutput();

        $this->assertEquals("Select database type:\n", $output[0]);
        $this->assertEquals("0: mysql\n", $output[1]);
        $this->assertEquals("1: mssql\n", $output[2]);
        $this->assertEquals("Database host: Enter = '127.0.0.1'\n", $output[3]);
        $this->assertEquals("Port number: Enter = '3306'\n", $output[4]);
        $this->assertEquals("Username:\n", $output[5]);
        $this->assertEquals("Password:\n", $output[6]);
        $this->assertEquals("***********\n", $output[7]);
        $this->assertEquals("Database name:\n", $output[8]);
        $this->assertEquals("Give your connection a friendly name: Enter = '$connName'\n", $output[9]);
        $this->assertEquals("Trying to connect to the database...\n", $output[10]);
        $this->assertEquals("Trying with 'localhost'...\n", $output[11]);
        $this->assertEquals("Error: Unable to connect to the database.\n", $output[12]);
        $this->assertStringContainsString("Error: Unable to connect to database: 1045 - Access denied for user", $output[13]);
        $this->assertEquals("Would you like to store connection information anyway?(y/N)\n", $output[14]);
    }
    /**
     * @test
     * Tests that all args bypass interactive prompts and --no-check skips connection attempt.
     */
    public function testAddDBConnection03() {
        $connName = 'my-test-conn-'.time();
        $countBefore = count(App::getConfig()->getDBConnections());

        $output = $this->executeSingleCommand(new AddDbConnectionCommand(), [
            '--db-type=mysql',
            '--host=127.0.0.1',
            '--port=3306',
            '--user=root',
            '--password=secret',
            '--database=mydb',
            '--name='.$connName,
            '--no-check',
        ], []);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Success: Connection information was stored in application configuration.\n"
        ], $output);

        $connections = App::getConfig()->getDBConnections();
        $this->assertCount($countBefore + 1, $connections);
        $this->assertArrayHasKey($connName, $connections);
        $conn = $connections[$connName];
        $this->assertEquals('mysql', $conn->getDatabaseType());
        $this->assertEquals('127.0.0.1', $conn->getHost());
        $this->assertEquals(3306, $conn->getPort());
        $this->assertEquals('root', $conn->getUsername());
        $this->assertEquals('mydb', $conn->getDBName());
    }
    /**
     * @test
     * Tests that providing some args still prompts for the missing ones.
     */
    public function testAddDBConnection04() {
        $connName = 'db-connection-'.(count(App::getConfig()->getDBConnections()) + 1);

        $output = $this->executeSingleCommand(new AddDbConnectionCommand(), [
            '--db-type=mysql',
            '--host=127.0.0.1',
            '--port=3306',
            '--user=root',
            '--password=123456',
            '--database=testing_db',
        ], [
            "\n" // Hit Enter to pick default connection name
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $output = $this->getOutput();

        $this->assertEquals("Give your connection a friendly name: Enter = '$connName'\n", $output[0]);
        $this->assertEquals("Trying to connect to the database...\n", $output[1]);
        $this->assertEquals("Success: Connected. Adding the connection...\n", $output[2]);
        $this->assertEquals("Success: Connection information was stored in application configuration.\n", $output[3]);
    }
    /**
     * @test
     * Tests that --extras JSON is decoded and stored on the connection.
     */
    public function testAddDBConnection05() {
        $connName = 'extras-conn-'.time();
        $extras = ['charset' => 'utf8mb4', 'timeout' => '30'];

        $output = $this->executeSingleCommand(new AddDbConnectionCommand(), [
            '--db-type=mysql',
            '--host=127.0.0.1',
            '--port=3306',
            '--user=root',
            '--password=secret',
            '--database=mydb',
            '--name='.$connName,
            '--extras='.json_encode($extras),
            '--no-check',
        ], []);

        $this->assertEquals(0, $this->getExitCode());
        $connections = App::getConfig()->getDBConnections();
        $this->assertArrayHasKey($connName, $connections);
        $this->assertEquals($extras, $connections[$connName]->getExtars());
    }
    /**
     * @test
     * Tests that an unsupported --db-type value shows a warning and falls back to interactive select.
     */
    public function testAddDBConnection06() {
        $output = $this->executeSingleCommand(new AddDbConnectionCommand(), [
            '--db-type=oracle',
        ], [
            '0',       // select mysql
            '127.0.0.1',
            "\n",      // default port
            'root',
            '123456',
            'testing_db',
            "\n",      // default connection name
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $output = $this->getOutput();

        $this->assertEquals("Warning: Database not supported: oracle\n", $output[0]);
        $this->assertEquals("Select database type:\n", $output[1]);
    }
}
