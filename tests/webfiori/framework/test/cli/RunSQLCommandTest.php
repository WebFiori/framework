<?php
namespace webfiori\framework\test\cli;

use tables\Schema;
use tables\Schema2;
use WebFiori\Database\ConnectionInfo;
use webfiori\framework\App;
use webfiori\framework\cli\CLITestCase;
use webfiori\framework\cli\commands\RunSQLQueryCommand;
use webfiori\framework\config\Controller;
use webfiori\framework\config\JsonDriver;

class RunSQLCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testCLIQuery00() {
        App::getConfig()->removeAllDBConnections();
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);
        
        $output = $this->executeSingleCommand(new RunSQLQueryCommand(), ['run-query'], [
            '0',
            '0',
            'select * from hello;',
            'y'
        ]);

        $this->assertEquals(1146, $this->getExitCode());
        $this->assertEquals([
            "Select database connection:\n",
            "0: testing-connection <--\n",
            "What type of query you would like to run?\n",
            "0: Run general query.\n",
            "1: Run query on table instance.\n",
            "2: Run query from file.\n",
            "Please type in SQL query:\n",
            "The following query will be executed on the database 'testing_db':\n",
            "select * from hello;\n",
            "Continue?(Y/n)\n",
            "Info: Executing query on database testing_db...\n",
            "Error: 1146 - Table 'testing_db.hello' doesn't exist\n"
        ], $output);
    }
    
    /**
     * @test
     */
    public function testCLIQuery01() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);

        $output = $this->executeSingleCommand(new RunSQLQueryCommand(), [
            'run-query',
            '--connection' => 'testing-connection',
        ], [
            '0',
            'drop table test2_x;',
            'y'
        ]);

        $this->assertEquals(1051, $this->getExitCode());
        $this->assertEquals([
            "What type of query you would like to run?\n",
            "0: Run general query.\n",
            "1: Run query on table instance.\n",
            "2: Run query from file.\n",
            "Please type in SQL query:\n",
            "The following query will be executed on the database 'testing_db':\n",
            "drop table test2_x;\n",
            "Continue?(Y/n)\n",
            "Info: Executing query on database testing_db...\n",
            "Error: 1051 - Unknown table 'testing_db.test2_x'\n"
        ], $output);
    }
    
    /**
     * @test
     */
    public function testQueryFromFile00() {
        App::getConfig()->removeAllDBConnections();
        
        $output = $this->executeSingleCommand(new RunSQLQueryCommand(), [
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--file' => 'not-exist'
        ], []);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertEquals([
            'Error: No database connections available. Add connections to application configuration or use the command "add"'.".\n"
        ], $output);
    }
    
    /**
     * @test
     */
    public function testQueryFromFile01() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);

        $output = $this->executeSingleCommand(new RunSQLQueryCommand(), [
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--file' => 'not-exist'
        ], []);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertEquals([
            "Error: No such file: not-exist\n"
        ], $output);
    }
    
    /**
     * @test
     */
    public function testQueryFromFile02() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);

        $output = $this->executeSingleCommand(new RunSQLQueryCommand(), [
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--file' => 'app\\database\\Test2Table.php'
        ], []);

        $this->assertEquals(-1, $this->getExitCode());
        $this->assertEquals([
            "Error: Provided file is not SQL file!\n"
        ], $output);
    }
    
    /**
     * @test
     */
    public function testQueryFromFile03() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);

        $output = $this->executeSingleCommand(new RunSQLQueryCommand(), [
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--file' => 'app\\database\\sql-file.sql'
        ], []);
        
        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Info: Executing query on database testing_db...\n",
            "Success: Query executed without errors.\n"
        ], $output);
    }
}
