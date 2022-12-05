<?php

namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\database\ConnectionInfo;
use webfiori\framework\ConfigController;
use webfiori\framework\WebFioriApp;

class RunSQLCommandTest extends TestCase {
    /**
     * @test
     */
    public function testQueryFromFile00() {
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--file' => 'not-exist'
        ]);
        $runner->setInput([
           
        ]);
        
        
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            'Error: No database connections available. Add connections to application configuration or use the command "add"'.".\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testQueryFromFile01() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        ConfigController::get()->addOrUpdateDBConnection($conn);
        
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--file' => 'not-exist'
        ]);
        $runner->setInput([
           
        ]);
        
        
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            "Error: No such file: not-exist\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testQueryFromFile02() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        ConfigController::get()->addOrUpdateDBConnection($conn);
        
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--file' => 'app\\database\\Test2Table.php'
        ]);
        $runner->setInput([
           
        ]);
        
        
        $this->assertEquals(-1, $runner->start());
        $this->assertEquals([
            "Error: Provided file is not SQL file!\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testQueryFromFile03() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        ConfigController::get()->addOrUpdateDBConnection($conn);
        
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--file' => 'app\\database\\sql-file.sql'
        ]);
        $runner->setInput([
           
        ]);
        
        
        $this->assertEquals(0, $runner->start());
        $this->assertEquals([
            "Info: Executing the query...\n",
            "Success: Query executed without errors.\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testQueryFromFile04() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        ConfigController::get()->addOrUpdateDBConnection($conn);
        
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--file' => 'app\\database\\sql-file.sql'
        ]);
        $runner->setInput([
           'y'
        ]);
        
        
        $this->assertEquals(0, $runner->start());
        
        $this->assertEquals([
            "The following query will be executed on the database:\n",
            "use testing_db;\n",
            "Continue?(Y/n)\n",
            "Info: Executing the query...\n",
            "Success: Query executed without errors.\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testQueryFromFile05() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        ConfigController::get()->addOrUpdateDBConnection($conn);
        
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--file' => 'app\\database\\sql-file.sql'
        ]);
        $runner->setInput([
           'n'
        ]);
        
        
        $this->assertEquals(0, $runner->start());
        
        $this->assertEquals([
            "The following query will be executed on the database:\n",
            "use testing_db;\n",
            "Continue?(Y/n)\n",
            "Info: Nothing to execute.\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testCLIQuery00() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        ConfigController::get()->addOrUpdateDBConnection($conn);
        
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
        ]);
        $runner->setInput([
            '0',
            '0',
            'select * from hello;',
            'y'
        ]);
        
        
        $this->assertEquals(1146, $runner->start());
        
        $this->assertEquals([
            "Select database connection:\n",
            "0: testing-connection <--\n",
            "What type of query you would like to run?\n",
            "0: Run general query.\n",
            "1: Run query on table instance.\n",
            "2: Run query from file.\n",
            "Please type in SQL query:\n",
            "The following query will be executed on the database:\n",
            "select * from hello;\n",
            "Continue?(Y/n)\n",
            "Info: Executing the query...\n",
            "Error: 1146 - Table 'testing_db.hello' doesn't exist\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testQueryFromFile06() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        ConfigController::get()->addOrUpdateDBConnection($conn);
        
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection-2',
            '--file' => 'app\\database\\sql-file.sql'
        ]);
        $runner->setInput([
           'n'
        ]);
        
        
        $this->assertEquals(-1, $runner->start());
        
        $this->assertEquals([
            "Error: No connection with name \"testing-connection-2\" was found!\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testTableQuery00() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        ConfigController::get()->addOrUpdateDBConnection($conn);
        
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm'
        ]);
        $runner->setInput([
            '1',
            'app\database\TestTable',
            '0'
        ]);
        
        
        $this->assertEquals(0, $runner->start());
        
        $this->assertEquals([
            "What type of query you would like to run?\n",
            "0: Run general query.\n",
            "1: Run query on table instance.\n",
            "2: Run query from file.\n",
            "Enter database table class name (include namespace):\n",
            "Select query type:\n",
            "0: Create database table.\n",
            "1: Drop database table.\n",
            "2: Drop and create table.\n",
            "3: Add Column.\n",
            "4: Modify Column.\n",
            "5: Drop Column.\n",
            "Info: Executing the query...\n",
            "Success: Query executed without errors.\n"
        ], $runner->getOutput());
    }
    public function testCLIQuery01() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        ConfigController::get()->addOrUpdateDBConnection($conn);
        
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
        ]);
        $runner->setInput([
            '0',
            'drop table test2_x;',
            'y'
        ]);

        $this->assertEquals(1051, $runner->start());
        
        $this->assertEquals([
            "What type of query you would like to run?\n",
            "0: Run general query.\n",
            "1: Run query on table instance.\n",
            "2: Run query from file.\n",
            "Please type in SQL query:\n",
            "The following query will be executed on the database:\n",
            "drop table test2_x;\n",
            "Continue?(Y/n)\n",
            "Info: Executing the query...\n",
            "Error: 1051 - Unknown table 'testing_db.test2_x'\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     * @depends testTableQuery00
     */
    public function testTableQuery01() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        ConfigController::get()->addOrUpdateDBConnection($conn);
        
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--table' => 'app\\database\\TestTable'
        ]);
        $runner->setInput([
            '1',
            '1'
        ]);
        
        
        $this->assertEquals(0, $runner->start());
        
        $this->assertEquals([
            "What type of query you would like to run?\n",
            "0: Run general query.\n",
            "1: Run query on table instance.\n",
            "2: Run query from file.\n",
            "Select query type:\n",
            "0: Create database table.\n",
            "1: Drop database table.\n",
            "2: Drop and create table.\n",
            "3: Add Column.\n",
            "4: Modify Column.\n",
            "5: Drop Column.\n",
            "Info: Executing the query...\n",
            "Success: Query executed without errors.\n"
        ], $runner->getOutput());
    }
}
