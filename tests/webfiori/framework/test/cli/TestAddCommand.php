<?php

namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\CommandRunner;
use webfiori\framework\cli\commands\AddCommand;
use webfiori\framework\WebFioriApp;
/**
 * Description of TestAddCommand
 *
 * @author Ibrahim
 */
class TestAddCommand extends TestCase {
    /**
     * @test
     */
    public function test00() {
        $runner = new CommandRunner([
            '3'
        ]);
        $runner->runCommand(new AddCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n"
        ], $this));
    }
    /**
     * @test
     */
    public function testAddDBConnection00() {
        $runner = new CommandRunner([
            '0',
            '0',
            '',
            '',
            'root',
            '123456',
            'testing_db',
            ''
        ]);
        $runner->runCommand(new AddCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $connName = 'db-connection-'.count(WebFioriApp::getAppConfig()->getDBConnections());
        $this->assertTrue($runner->isOutputEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
            "Select database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Database host: Enter = \"127.0.0.1\"\n",
            "Port number: Enter = \"3306\"\n",
            "Username:\n",
            "Password:\n",
            "Database name:\n",
            "Give your connection a friendly name: Enter = \"$connName\"\n",
            "Trying to connect to the database...\n",
            "Success: Connected. Adding the connection...\n",
            'Success: Connection information was stored in the class "'.APP_DIR_NAME.'\\AppConfig".'."\n"
        ], $this));
    }
    /**
     * @test
     */
    public function testAddDBConnection01() {
        $runner = new CommandRunner([
            '0',
            '0',
            '',
            '',
            'root',
            '12345',
            'testing_db',
            ''
        ]);
        $runner->runCommand(new AddCommand());
        $this->assertEquals(-1, $runner->getExitStatus());
        $connName = 'db-connection-'.count(WebFioriApp::getAppConfig()->getDBConnections());
        $this->assertTrue($runner->isOutputEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
            "Select database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Database host: Enter = \"127.0.0.1\"\n",
            "Port number: Enter = \"3306\"\n",
            "Username:\n",
            "Password:\n",
            "Database name:\n",
            "Give your connection a friendly name: Enter = \"$connName\"\n",
            "Trying to connect to the database...\n",
            "Error: Unable to connect to the database.\n",
            "Error: Unable to connect to database: 1045 - Access denied for user 'root'@'127.0.0.1' (using password: YES)\n"
        ], $this));
    }
}
