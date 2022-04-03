<?php

namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\CommandRunner;
use webfiori\framework\cli\commands\AddCommand;
use webfiori\framework\WebFioriApp;
use webfiori\framework\File;
use webfiori\framework\ConfigController;
/**
 * Description of TestAddCommand
 *
 * @author Ibrahim
 */
class AddCommandTest extends TestCase {
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
            '',
            'n'
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
            "Error: Unable to connect to the database.\n",
            "Error: Unable to connect to database: 1045 - Access denied for user 'root'@'".getHostByName(getHostName())."' (using password: YES)\n",
            "Would you like to store connection information anyway?(y/N)\n"
        ], $this));
    }
    /**
     * @test
     */
    public function testAddDBConnection03() {
        $runner = new CommandRunner([
            '0',
            '0',
            '',
            '',
            'root',
            '12345',
            'testing_db',
            '',
            'y'
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
            "Error: Unable to connect to the database.\n",
            "Error: Unable to connect to database: 1045 - Access denied for user 'root'@'".getHostByName(getHostName())."' (using password: YES)\n",
            "Would you like to store connection information anyway?(y/N)\n",
            "Success: Connection information was stored in the class \"".APP_DIR_NAME."\\AppConfig\".\n"
        ], $this));
    }
    /**
     * @test
     */
    public function testAddLang00() {
        $runner = new CommandRunner([
            '2',
            'FK',
            'F Name',
            'F description',
            'Default f Title',
            'ltr',
        ]);
        $runner->runCommand(new AddCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
            "Language code:\n",
            "Name of the website in the new language:\n",
            "Description of the website in the new language:\n",
            "Default page title in the new language:\n",
            "Select writing direction:\n",
            "0: ltr\n",
            "1: rtl\n",
            "Success: Language added. Also, a class for the language is created at \"".APP_DIR_NAME."\langs\" for that language.\n"
        ], $this));
        $this->assertTrue(class_exists('\\app\\langs\\LanguageFK'));
        $this->removeClass('\\app\\langs\\LanguageFK');
        ConfigController::get()->resetConfig();
    }
    /**
     * @test
     */
    public function testAddLang01() {
        $runner = new CommandRunner([
            '2',
            'EN',
        ]);
        $runner->runCommand(new AddCommand());
        $this->assertEquals(0, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
            "Language code:\n",
            "Info: This language already added. Nothing changed.\n",
        ], $this));
        ConfigController::get()->resetConfig();
    }
    /**
     * @test
     */
    public function testAddLang02() {
        $runner = new CommandRunner([
            '2',
            'FKRR',
        ]);
        $runner->runCommand(new AddCommand());
        $this->assertEquals(-1, $runner->getExitStatus());
        $this->assertTrue($runner->isOutputEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
            "Language code:\n",
            "Error: Invalid language code.\n",
        ], $this));
        $this->assertTrue(class_exists('\\app\\langs\\LanguageFK'));
        $this->removeClass('\\app\\langs\\LanguageFK');
    }
    private function removeClass($classPath) {
        $file = new File(ROOT_DIR.$classPath.'.php');
        $file->remove();
    }
}
