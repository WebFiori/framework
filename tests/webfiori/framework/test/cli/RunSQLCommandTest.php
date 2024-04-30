<?php
namespace webfiori\framework\test\cli;

use tables\Schema;
use tables\Schema2;
use webfiori\cli\CommandTestCase;
use webfiori\database\ConnectionInfo;
use webfiori\framework\App;
use webfiori\framework\config\Controller;
use webfiori\framework\config\JsonDriver;

class RunSQLCommandTest extends CommandTestCase {
    /**
     * @test
     */
    public function testCLIQuery00() {
        App::getConfig()->removeAllDBConnections();
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->setRunner(App::getRunner());
        
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
        ], $this->executeMultiCommand(['run-query'], [
            '0',
            '0',
            'select * from hello;',
            'y'
        ]));
        $this->assertEquals(1146, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testCLIQuery01() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->setRunner(App::getRunner());

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
        ], $this->executeMultiCommand([
            'run-query',
            '--connection' => 'testing-connection',
        ], [
            '0',
            'drop table test2_x;',
            'y'
        ]));
        $this->assertEquals(1051, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testCLIQuery02() {
        JsonDriver::setConfigFileName('run-sql-test');
        App::setConfigDriver(JsonDriver::class);

        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);
        $driver = new JsonDriver();
        $driver->setConfigFileName('run-sql-test');

        Controller::setDriver($driver);

        $this->assertTrue(get_class(App::getConfig()) == JsonDriver::class);

        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
        ]);
        $runner->setInputs([
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
            "The following query will be executed on the database 'testing_db':\n",
            "select * from hello;\n",
            "Continue?(Y/n)\n",
            "Info: Executing query on database testing_db...\n",
            "Error: 1146 - Table 'testing_db.hello' doesn't exist\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testQueryFromFile00() {
        App::getConfig()->removeAllDBConnections();
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--file' => 'not-exist'
        ]);
        $runner->setInputs([

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
        App::getConfig()->addOrUpdateDBConnection($conn);

        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--file' => 'not-exist'
        ]);
        $runner->setInputs([

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
        App::getConfig()->addOrUpdateDBConnection($conn);

        $this->setRunner(App::getRunner());

        $this->assertEquals([
            "Error: Provided file is not SQL file!\n"
        ], $this->executeMultiCommand([
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--file' => 'app\\database\\Test2Table.php'
        ]));
        $this->assertEquals(-1, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testQueryFromFile03() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);
        $this->setRunner(App::getRunner());

        $this->assertEquals([
            "Info: Executing query on database testing_db...\n",
            "Success: Query executed without errors.\n"
        ], $this->executeMultiCommand([
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--file' => 'app\\database\\sql-file.sql'
        ]));
        
        $this->assertEquals(0, $this->getExitCode());
    }
    /**
     * @test
     */
    public function testQueryFromFile04() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);

        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--file' => 'app\\database\\sql-file.sql'
        ]);
        $runner->setInputs([
           'y'
        ]);


        $this->assertEquals(0, $runner->start());

        $this->assertEquals([
            "The following query will be executed on the database 'testing_db':\n",
            "use testing_db;\n",
            "Continue?(Y/n)\n",
            "Info: Executing query on database testing_db...\n",
            "Success: Query executed without errors.\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testQueryFromFile05() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);

        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--file' => 'app\\database\\sql-file.sql'
        ]);
        $runner->setInputs([
           'n'
        ]);


        $this->assertEquals(0, $runner->start());

        $this->assertEquals([
            "The following query will be executed on the database 'testing_db':\n",
            "use testing_db;\n",
            "Continue?(Y/n)\n",
            "Info: Nothing to execute.\n",
        ], $runner->getOutput());
    }
    /**
     * @test
     */
    public function testQueryFromFile06() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);

        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection-2',
            '--file' => 'app\\database\\sql-file.sql'
        ]);
        $runner->setInputs([
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
    public function testSchemaQuery00() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);

        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--schema' => Schema::class
        ]);
        $runner->setInputs([
            '0',
            'y',
        ]);

        $code = $runner->start();


        $this->assertEquals([
            "Select an option:\n",
            "0: Create Database.\n",
            "1: Run Query on Specific Table.\n",
            "The following query will be executed on the database 'testing_db':\n",
            "create table if not exists `users` (\n"
            ."    `id` int not null,\n"
            ."    `email` varchar(128) not null collate utf8mb4_unicode_520_ci,\n"
            ."    `first_name` varchar(128) not null collate utf8mb4_unicode_520_ci,\n"
            ."    `last_name` varchar(128) null collate utf8mb4_unicode_520_ci,\n"
            ."    `joining_date` datetime not null,\n"
            ."    `created_on` timestamp not null default now()\n"
            .")\n"
            ."engine = InnoDB\n"
            ."default charset = utf8mb4\n"
            ."collate = utf8mb4_unicode_520_ci;\n",
            "Continue?(Y/n)\n",
            "Info: Executing query on database testing_db...\n",
            "Success: Query executed without errors.\n"
        ], $runner->getOutput());
        $this->assertEquals(0, $code);
    }
    /**
     * @test
     */
    public function testSchemaQuery01() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);

        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--schema' => Schema2::class,
            '--create'
        ]);
        $runner->setInputs([
            'y',
        ]);
        $code = $runner->start();
        $this->assertEquals([
            "The following query will be executed on the database 'testing_db':\n",
            "create table if not exists `users` (\n"
            ."    `id` int not null unique,\n"
            ."    `name` varchar(128) not null unique collate utf8mb4_unicode_520_ci,\n"
            ."    `company` varchar(128) not null collate utf8mb4_unicode_520_ci,\n"
            ."    `salary` decimal(10,2) not null default '0',\n"
            ."    `created_on` timestamp not null default now(),\n"
            ."    `last_updated` datetime null\n"
            .")\n"
            ."engine = InnoDB\n"
            ."default charset = utf8mb4\n"
            ."collate = utf8mb4_unicode_520_ci;\n",
            "Continue?(Y/n)\n",
            "Info: Executing query on database testing_db...\n",
            "Success: Query executed without errors.\n"
        ], $runner->getOutput());
        $this->assertEquals(0, $code);
    }
    /**
     * @test
     */
    public function testSchemaQuery03() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);

        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--schema' => Schema2::class,
        ]);
        $runner->setInputs([
            '1',
            "0",
            "2",
            "y"
        ]);
        $code = $runner->start();
        $this->assertEquals([
            "Select an option:\n",
            "0: Create Database.\n",
            "1: Run Query on Specific Table.\n",
            "Select database table:\n",
            "0: users\n",
            "Select query type:\n",
            "0: Create database table.\n",
            "1: Drop database table.\n",
            "2: Drop and create table.\n",
            "3: Add Column.\n",
            "4: Modify Column.\n",
            "5: Drop Column.\n",
            "The following query will be executed on the database 'testing_db':\n",
            "drop table `users`;\n"
            ."create table if not exists `users` (\n"
            ."    `id` int not null unique,\n"
            ."    `name` varchar(128) not null unique collate utf8mb4_unicode_520_ci,\n"
            ."    `company` varchar(128) not null collate utf8mb4_unicode_520_ci,\n"
            ."    `salary` decimal(10,2) not null default '0',\n"
            ."    `created_on` timestamp not null default now(),\n"
            ."    `last_updated` datetime null\n"
            .")\n"
            ."engine = InnoDB\n"
            ."default charset = utf8mb4\n"
            ."collate = utf8mb4_unicode_520_ci;\n",
            "Continue?(Y/n)\n",
            "Info: Executing query on database testing_db...\n",
            "Success: Query executed without errors.\n"
        ], $runner->getOutput());
        $this->assertEquals(0, $code);
    }
    /**
     * @test
     */
    public function testTableQuery00() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);

        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--show-sql'
        ]);
        $runner->setInputs([
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
            "The following query will be executed on the database 'testing_db':\n",
            "create table if not exists `test` (\n".
            "    `id` int not null\n".
            ")\n".
            "engine = InnoDB\n".
            "default charset = utf8mb4\n".
            "collate = utf8mb4_unicode_520_ci;\n",
            "Info: Executing query on database testing_db...\n",
            "Success: Query executed without errors.\n"
        ], $runner->getOutput());
    }
    /**
     * @test
     * @depends testTableQuery00
     */
    public function testTableQuery01() {
        $conn = new ConnectionInfo('mysql', 'root', '123456', 'testing_db', '127.0.0.1');
        $conn->setName('testing-connection');
        App::getConfig()->addOrUpdateDBConnection($conn);

        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'run-query',
            '--connection' => 'testing-connection',
            '--no-confirm',
            '--table' => 'app\\database\\TestTable'
        ]);
        $runner->setInputs([
            '1',
            '1'
        ]);

        $code = $runner->start();



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
            "Info: Executing query on database testing_db...\n",
            "Success: Query executed without errors.\n"
        ], $runner->getOutput());

        $this->assertEquals(0, $code);
    }
}
