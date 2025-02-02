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
        $clazz = $this->createMigration();
        $this->assertTrue(class_exists($clazz));
        
        $this->assertEquals([
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
            "Error: No connection was found which has the name 'ABC'.\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--ns' => '\\app\\database\\migrations',
            '--connection' => 'ABC'
        ]));
        $this->removeClass($clazz);
        $this->assertEquals(-1, $this->getExitCode());
        App::getConfig()->removeAllDBConnections();
    }
    /**
     * @test
     */
    public function testRunMigrations03() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db');
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
            "Error: The argument --runner has invalid value:  Exception: \"Call to private webfiori\framework\App::__construct() from scope webfiori\framework\cli\commands\RunMigrationsCommand\".\n",
        ], $this->executeMultiCommand([
            RunMigrationsCommand::class,
            '--runner' => '\\webfiori\\framework\\App',
        ]));
        $this->assertEquals(0, $this->getExitCode());
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
    private function createMigration() : string {
        $runner = new MigrationsRunner(APP_PATH.DS.'database'.DS.'migrations'.DS.'commands', '\\app\\database\\migrations\\commands', null);
        $writer = new DatabaseMigrationWriter($runner);
        $writer->writeClass();
        return $writer->getName(true);
    }
}
