<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\AddDbConnectionCommand;

/**
 * Test cases for All commands workflow
 *
 * @author Ibrahim
 */
class AddDbConnectionCommandTest extends CLITestCase {
    /**
     * @test
     */
    public function testAddDBConnection00() {
        //Step 1: Add DB Connection
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
            "******\n",
            "Database name:\n",
            "Give your connection a friendly name: Enter = '$connName'\n",
            "Trying to connect to the database...\n",
            "Success: Connected. Adding the connection...\n",
            "Success: Connection information was stored in application configuration.\n"
        ], $output);
        $this->assertEquals(0, $this->getExitCode());

        //Step 2: Create Database Table Structure

        $className = 'TestTable'.time();
        $columnsJson = json_encode([
            ['name' => 'id', 'type' => 'INT', 'size' => 11, 'primary' => true, 'autoIncrement' => true],
            ['name' => 'name', 'type' => 'VARCHAR', 'size' => 255, 'nullable' => false],
            ['name' => 'email', 'type' => 'VARCHAR', 'size' => 255, 'nullable' => true]
        ]);

        $output = $this->executeMultiCommand([
            CreateTableCommand::class,
            '--class-name' => $className,
            '--table-name' => 'users',
            '--columns' => $columnsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Schema\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Schema\\'.$className);

        //Step 3: Create Entity Class

        $className = 'TestEntity'.time();
        $propsJson = json_encode([
            ['name' => 'id', 'type' => 'int', 'nullable' => false],
            ['name' => 'name', 'type' => 'string', 'nullable' => false],
            ['name' => 'email', 'type' => 'string', 'nullable' => true]
        ]);

        $output = $this->executeMultiCommand([
            CreateEntityCommand::class,
            '--class-name' => $className,
            '--properties' => $propsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Domain\\'.$className));
        $this->removeClass('\\App\\Domain\\'.$className);

        //Step 4: Create Repo

        $className = 'TestRepo'.time();
        $propsJson = json_encode([
            ['name' => 'id', 'type' => 'int'],
            ['name' => 'name', 'type' => 'string'],
            ['name' => 'email', 'type' => 'string']
        ]);

        $output = $this->executeMultiCommand([
            CreateRepositoryCommand::class,
            '--class-name' => $className,
            '--entity' => 'App\\Domain\\User',
            '--table' => 'users',
            '--id-field' => 'id',
            '--properties' => $propsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists('\\App\\Infrastructure\\Repository\\'.$className));
        $this->removeClass('\\App\\Infrastructure\\Repository\\'.$className);

        //Step 5: Create Migration
        //Modify migration code to create the table.
        //Step 6: Create Seeder
        //Modify seeder code to add 30 random users
        //Step 7: Run migrations
        //Step 8: Create Web Service
        $className = 'TestService'.time();

        $output = $this->executeSingleCommand(new CreateServiceCommand(), [], [
            $className,
            "\n", // Use default description
            'n'   // Don't add methods
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter service class name:\n",
            "Enter service description: Enter = 'REST API Service'\n",
            "Add methods to the service?(y/N)\n",
            "Success: Service class created at: ".APP_PATH."Apis".DIRECTORY_SEPARATOR.$className."Service.php\n"
        ], $output);

        $this->assertTrue(class_exists('\\App\\Apis\\'.$className.'Service'));
        $this->removeClass('\\App\\Apis\\'.$className.'Service');
        //Step 9: Modify the web service to use The repo

        //Step 10: Create annon test cases using the class APITestCase

    }

}
