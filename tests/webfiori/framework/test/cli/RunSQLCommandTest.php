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
    public function test00() {
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
    public function test01() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db');
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
    public function test02() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db');
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
    public function test03() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db');
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
    public function test04() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db');
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
}
