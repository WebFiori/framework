<?php
namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\cli\Runner;
use webfiori\file\File;
use webfiori\framework\App;
use webfiori\framework\cli\commands\AddCommand;
use webfiori\framework\config\Controller;

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
        $runner = new Runner();
        $runner->setInputs([
            '3'
        ]);
        $this->assertEquals(0, $runner->runCommand(new AddCommand()));
        $this->assertEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testAddDBConnection00() {
        $runner = App::getRunner();
        $runner->setInputs([
            '0',
            '0',
            '127.0.0.1',
            '',
            'root',
            '123456',
            'testing_db',
            ''
        ]);
        $runner->setArgsVector([
            'webfiori',
            'add'
        ]);
        $this->assertEquals(0, $runner->start());
        $connName = 'db-connection-'.(count(App::getConfig()->getDBConnections()) - 1);
        $this->assertEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
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
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testAddDBConnection01() {
        $runner = App::getRunner();
        $runner->setInputs([
            '0',
            '0',
            '127.0.0.1',
            '',
            'root',
            '12345326',
            'testing_db',
            '',
            'y'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'add'
        ]);
        $connName = 'db-connection-'.count(App::getConfig()->getDBConnections());
        $this->assertEquals(0, $runner->start());

        $this->assertEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
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
            "Error: Unable to connect to the database.\n",
            "Error: Unable to connect to database: 2002 - No such file or directory\n",
            "Would you like to store connection information anyway?(y/N)\n",
            "Success: Connection information was stored in application configuration.\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testAddDBConnection02() {
        $runner = App::getRunner();
        $runner->setInputs([
            '0',
            '0',
            '127.0.0.1',
            '',
            'root',
            '12345326',
            'testing_db',
            '',
            'n'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'add'
        ]);
        $this->assertEquals(0, $runner->start());
        $connName = 'db-connection-'.count(App::getConfig()->getDBConnections());
        $this->assertEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
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
            "Error: Unable to connect to the database.\n",
            "Error: Unable to connect to database: 2002 - No such file or directory\n",
            "Would you like to store connection information anyway?(y/N)\n",
        ], $runner->getOutput());
    }

    /**
     * @test
     */
    public function testAddLang00() {
        $runner = new Runner();
        $runner->setInputs([
            '2',
            'FK',
            'F Name',
            'F description',
            'Default f Title',
            'ltr',
        ]);
        $this->assertEquals(0, $runner->runCommand(new AddCommand()));
        $this->assertEquals([
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
            "Success: Language added. Also, a class for the language is created at \"".APP_DIR."\langs\" for that language.\n"
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\langs\\LangFK'));
        $this->removeClass('\\app\\langs\\LanguageFK');
        Controller::getDriver()->initialize();
    }
    /**
     * @test
     */
    public function testAddLang01() {
        $runner = new Runner();
        $runner->setInputs([
            '2',
            'EN',
        ]);
        $this->assertEquals(0, $runner->runCommand(new AddCommand()));
        $this->assertEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
            "Language code:\n",
            "Info: This language already added. Nothing changed.\n",
        ], $runner->getOutput());
        Controller::getDriver()->initialize();
    }
    /**
     * @test
     */
    public function testAddLang02() {
        $runner = new Runner();
        $runner->setInputs([
            '2',
            'FKRR',
        ]);

        $this->assertEquals(-1, $runner->runCommand(new AddCommand()));
        $this->assertEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
            "Language code:\n",
            "Error: Invalid language code.\n",
        ], $runner->getOutput());
        $this->assertTrue(class_exists('\\app\\langs\\LangFK'));
        $this->removeClass('\\app\\langs\\LanguageFK');
    }
    /**
     * @test
     */
    public function testAddSMTPConnection00() {
        $runner = App::getRunner();
        $runner->setInputs([
            '1',
            '127.0.0.1',
            '',
            'test@example.com',
            '12345326',
            'test@example.com',
            'test@example.com',
            '',
            'n'
        ]);
        $runner->setArgsVector([
            'webfiori',
            'add'
        ]);
        $this->assertEquals(0, $runner->start());
        $connName = 'smtp-connection-'.count(App::getConfig()->getSMTPConnections());
        $this->assertEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
            "SMTP Server address: Enter = '127.0.0.1'\n",
            "Port number: Enter = '25'\n",
            "Username:\n",
            "Password:\n",
            "Sender email address: Enter = 'test@example.com'\n",
            "Sender name: Enter = 'test@example.com'\n",
            "Give your connection a friendly name: Enter = '$connName'\n",
            "Trying to connect. This can take up to 1 minute...\n",
            "Error: Unable to connect to SMTP server.\n",
            "Error Information: \n",
            "Would you like to store connection information anyway?(y/N)\n",
        ], $runner->getOutput());
    }
    private function removeClass($classPath) {
        $file = new File(ROOT_PATH.$classPath.'.php');
        $file->remove();
    }
}
