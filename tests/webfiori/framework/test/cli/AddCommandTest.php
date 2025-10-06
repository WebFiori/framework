<?php
namespace webfiori\framework\test\cli;

use WebFiori\File\File;
use webfiori\framework\App;
use webfiori\framework\cli\CLITestCase;
use webfiori\framework\cli\commands\AddCommand;
use webfiori\framework\config\Controller;

/**
 * Description of TestAddCommand
 *
 * @author Ibrahim
 */
class AddCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function test00() {
        $output = $this->executeSingleCommand(new AddCommand(), [], [
            '3'
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n"
        ], $output);
    }
    /**
     * @test
     */
    public function testAddDBConnection00() {
        $output = $this->executeSingleCommand(new AddCommand(), [], [
            '0',
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
        ], $output);
        $this->assertEquals(0, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testAddDBConnection01() {
        $connName = 'db-connection-'.(count(App::getConfig()->getDBConnections()) + 1);
        
        $output = $this->executeSingleCommand(new AddCommand(), [
            'webfiori',
            'add'
        ], [
            '0',
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
            "Trying with 'localhost'...\n",
            "Error: Unable to connect to the database.\n",
            "Error: Unable to connect to database: 1045 - Access denied for user 'root'@'127.0.0.1' (using password: YES)\n",
            "Would you like to store connection information anyway?(y/N)\n",
            "Success: Connection information was stored in application configuration.\n"
        ], $output);
    }
    /**
     * @test
     */
    public function testAddDBConnection02() {
        $count = count(App::getConfig()->getDBConnections());
        $connName = 'db-connection-'.($count + 1);
        
        $output = $this->executeSingleCommand(new AddCommand(), [
            'webfiori',
            'add'
        ], [
            '0',
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
            "Trying with 'localhost'...\n",
            "Error: Unable to connect to the database.\n",
            "Error: Unable to connect to database: 1045 - Access denied for user 'root'@'127.0.0.1' (using password: YES)\n",
            "Would you like to store connection information anyway?(y/N)\n",
        ], $output);
    }

    /**
     * @test
     */
    public function testAddLang00() {
        // Generate a unique 2-character language code based on current microseconds
        $langCode = substr(str_replace('.', '', microtime(true)), -2);
        // Ensure it's exactly 2 characters and alphabetic
        $langCode = chr(65 + ($langCode[0] % 26)) . chr(65 + ($langCode[1] % 26));
        
        // Clean up if it exists from previous runs
        if (class_exists('\\app\\langs\\Lang' . $langCode)) {
            $this->removeClass('\\app\\langs\\Lang' . $langCode);
        }
        
        $output = $this->executeSingleCommand(new AddCommand(), [], [
            '2',
            $langCode,
            'F Name',
            'F description',
            'Default f Title',
            'ltr',
        ]);

        $this->assertEquals(0, $this->getExitCode());
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
        ], $output);
        $this->assertTrue(class_exists('\\app\\langs\\Lang' . $langCode));
        $this->removeClass('\\app\\langs\\Lang' . $langCode);
        Controller::getDriver()->initialize();
    }
    /**
     * @test
     */
    public function testAddLang01() {
        $output = $this->executeSingleCommand(new AddCommand(), [], [
            '2',
            'EN',
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
            "Language code:\n",
            "Info: This language already added. Nothing changed.\n",
        ], $output);
        Controller::getDriver()->initialize();
    }
    /**
     * @test
     */
    public function testAddLang02() {
        $output = $this->executeSingleCommand(new AddCommand(), [], [
            '2',
            'FKRR',
        ]);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertEquals([
            "What would you like to add?\n",
            "0: New database connection.\n",
            "1: New SMTP connection.\n",
            "2: New website language.\n",
            "3: Quit. <--\n",
            "Language code:\n",
            "Error: Invalid language code.\n",
        ], $output);
        $this->removeClass('\\app\\langs\\LanguageFK');
    }
    /**
     * @test
     */
    public function testAddSMTPConnection00() {
        $connName = 'smtp-connection-'.count(App::getConfig()->getSMTPConnections());
        
        $output = $this->executeSingleCommand(new AddCommand(), [
            'webfiori',
            'add'
        ], [
            '1',
            '127.0.0.1',
            "\n", // Hit Enter to pick default value (port 25)
            'test@example.com',
            getenv('MYSQL_ROOT_PASSWORD') ?: '12345326',
            'test@example.com',
            'test@example.com',
            "\n", // Hit Enter to pick default value (connection name)
            'n'
        ]);

        $this->assertEquals(0, $this->getExitCode());
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
        ], $output);
    }
    
}
