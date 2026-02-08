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
        $this->assertEquals("Database name:\n", $output[7]);
        $this->assertEquals("Give your connection a friendly name: Enter = '$connName'\n", $output[8]);
        $this->assertEquals("Trying to connect to the database...\n", $output[9]);
        $this->assertEquals("Trying with 'localhost'...\n", $output[10]);
        $this->assertEquals("Error: Unable to connect to the database.\n", $output[11]);
        $this->assertStringContainsString("Error: Unable to connect to database: 1045 - Access denied for user", $output[12]);
        $this->assertEquals("Would you like to store connection information anyway?(y/N)\n", $output[13]);
        $this->assertEquals("Success: Connection information was stored in application configuration.\n", $output[14]);
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
        $this->assertEquals("Database name:\n", $output[7]);
        $this->assertEquals("Give your connection a friendly name: Enter = '$connName'\n", $output[8]);
        $this->assertEquals("Trying to connect to the database...\n", $output[9]);
        $this->assertEquals("Trying with 'localhost'...\n", $output[10]);
        $this->assertEquals("Error: Unable to connect to the database.\n", $output[11]);
        $this->assertStringContainsString("Error: Unable to connect to database: 1045 - Access denied for user", $output[12]);
        $this->assertEquals("Would you like to store connection information anyway?(y/N)\n", $output[13]);
    }
}
