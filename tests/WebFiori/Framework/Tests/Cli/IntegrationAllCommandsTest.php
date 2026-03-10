<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\AddDbConnectionCommand;
use WebFiori\Framework\Cli\Commands\CreateEntityCommand;
use WebFiori\Framework\Cli\Commands\CreateMigrationCommand;
use WebFiori\Framework\Cli\Commands\CreateRepositoryCommand;
use WebFiori\Framework\Cli\Commands\CreateSeederCommand;
use WebFiori\Framework\Cli\Commands\CreateServiceCommand;
use WebFiori\Framework\Cli\Commands\CreateTableCommand;
use WebFiori\Framework\Cli\Commands\FreshMigrationsCommand;
use WebFiori\Framework\Cli\Commands\InitMigrationsCommand;
use WebFiori\Framework\Cli\Commands\RunMigrationsCommandNew;

/**
 * Test cases for All commands workflow
 *
 * @author Ibrahim
 */
class IntegrationAllCommandsTest extends CLITestCase {
    /**
     * @test
     */
    public function testAddDBConnection00() {
        //Step 1: Add DB Connection
        $connName = 'db-connection-'.(count(App::getConfig()->getDBConnections()) + 1);
        $output = $this->executeSingleCommand(new AddDbConnectionCommand(), [], [
            '0',
            '127.0.0.1',
            "\n", // Hit Enter to pick default value (port 3306)
            'root',
            '123456',
            'testing_db',
            "\n" // Hit Enter to pick default value (connection name)
        ]);
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

        $tableClassName = 'TestTable'.time();
        $columnsJson = json_encode([
            ['name' => 'id', 'type' => 'INT', 'size' => 11, 'primary' => true, 'autoIncrement' => true],
            ['name' => 'name', 'type' => 'VARCHAR', 'size' => 255, 'nullable' => false],
            ['name' => 'email', 'type' => 'VARCHAR', 'size' => 255, 'nullable' => true]
        ]);

        $output = $this->executeMultiCommand([
            CreateTableCommand::class,
            '--class-name' => $tableClassName,
            '--table-name' => 'users',
            '--columns' => $columnsJson
        ]);

        $tableClassNs = '\\App\\Infrastructure\\Schema\\'.$tableClassName;
        $this->assertEquals(0, $this->getExitCode());
        $this->assertTrue(class_exists($tableClassNs));
        $this->removeClass($tableClassNs);

        //Step 3: Create Entity Class

        $entityClassName = 'UserEntity'.time();
        $propsJson = json_encode([
            ['name' => 'id', 'type' => 'int', 'nullable' => false],
            ['name' => 'name', 'type' => 'string', 'nullable' => false],
            ['name' => 'email', 'type' => 'string', 'nullable' => true]
        ]);

        $output = $this->executeMultiCommand([
            CreateEntityCommand::class,
            '--class-name' => $entityClassName,
            '--properties' => $propsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $entityClassNs = '\\App\\Domain\\'.$entityClassName;
        $this->assertTrue(class_exists($entityClassNs));
        $this->removeClass($entityClassNs);

        //Step 4: Create Repo

        $repoClassName = 'TestRepo'.time();
        $propsJson = json_encode([
            ['name' => 'id', 'type' => 'int'],
            ['name' => 'name', 'type' => 'string'],
            ['name' => 'email', 'type' => 'string']
        ]);

        $output = $this->executeMultiCommand([
            CreateRepositoryCommand::class,
            '--class-name' => $repoClassName,
            '--entity' => $entityClassNs,
            '--table' => 'users',
            '--id-field' => 'id',
            '--properties' => $propsJson
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $repoClassNs = '\\App\\Infrastructure\\Repository\\'.$repoClassName;
        $this->assertTrue(class_exists($repoClassNs));
        $this->removeClass($repoClassNs);

        //Step 5: Create Migration
        $migrationClass = 'CreateUsersTable'.time();
        $output = $this->executeMultiCommand([
            CreateMigrationCommand::class,
            '--class-name' => $migrationClass,
            '--description' => 'Creates users table'
        ]);
        $this->assertEquals(0, $this->getExitCode());
        $migrationClassNs = '\\App\\Database\\Migrations\\'.$migrationClass;
        
        
        // Modify migration to create the users table
        $migrationFile = APP_PATH.'Database'.DS.'Migrations'.DS.$migrationClass.'.php';
        $content = file_get_contents($migrationFile);
        $content = str_replace(
            '// TODO: Implement migration logic',
            '$db->addTable(\Webfiori\Database\Attributes\AttributeTableBuilder::build(\''.$tableClassNs.'\', $db->getConnectionInfo()->getDatabaseType()));'."\n".
            '            $query = $db->table(\'users\')->createTable()->getQuery();'."\n".
            '            $db->table(\'users\')->createTable()->execute();',
            $content
        );
        $content = str_replace(
            '// TODO: Implement rollback logic',
            '$db->table(\'users\')->drop();'."\n",
            $content
        );
        file_put_contents($migrationFile, $content);
        $this->assertTrue(class_exists($migrationClassNs));
        
        //Step 6: Create Seeder
        $seederClass = 'SeedUsers'.time();
        $output = $this->executeMultiCommand([
            CreateSeederCommand::class,
            '--class-name' => $seederClass,
            '--description' => 'Seeds 30 random users',
            '--depends-on' => $tableClassNs
        ]);
        $this->assertEquals(0, $this->getExitCode());
        $seederClassNs = '\\App\\Database\\Seeders\\'.$seederClass;
        $this->assertTrue(class_exists($seederClassNs));

        // Modify seeder to insert 10 random users
        $seederFile = APP_PATH.'Database'.DS.'Seeders'.DS.$seederClass.'.php';
        $content = file_get_contents($seederFile);
        $content = str_replace(
            '// TODO: Implement seeding logic',
            '$rawDb = new \WebFiori\Database\Database($db->getConnectionInfo());'."\n".
            '        $rawDb->table(\'users\')->insert([\'name\' => \'Ibrahim\', \'email\' => \'user@ibrahim.com\'])->execute();'."\n".
            '        for ($i = 1; $i <= 10; $i++) {'."\n".
            '            $rawDb->table(\'users\')->insert([\'name\' => \'User\'.$i, \'email\' => \'user\'.$i.\'@example.com\'])->execute();'."\n".
            '        }',
            $content
        );
        file_put_contents($seederFile, $content);
        $this->assertTrue(class_exists($seederClassNs));
        
        $output = $this->executeMultiCommand([
            InitMigrationsCommand::class,
            '--connection' => $connName
        ]);
        $this->assertEquals(0, $this->getExitCode());
        //Step 7: Run migrations
        $output = $this->executeMultiCommand([
            FreshMigrationsCommand::class,
            '--connection' => $connName
        ]);
        $this->assertEquals(0, $this->getExitCode());
        $outputStr = implode('', $output);
        $this->assertStringContainsString('Running database changes...', $outputStr);
        $this->assertStringContainsString('App\\Database\\Migrations\\'.$migrationClass, $outputStr);
        $this->assertStringContainsString('App\\Database\\Seeders\\'.$seederClass, $outputStr);
        $this->assertStringContainsString('Info: Applied: 1 migration(s)', $outputStr);
        $this->assertStringContainsString('Info: Applied: 1 seeder(s)', $outputStr);

        //Remove migrations after running
        $this->removeClass($seederClassNs);
        $this->removeClass($migrationClassNs);
        //Step 8: Create Web Service
        $serviceClass = 'Test'.time();

        $output = $this->executeSingleCommand(new CreateServiceCommand(), [], [
            $serviceClass,
            "\n", // Use default description
            'n'   // Don't add methods
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals([
            "Enter service class name:\n",
            "Enter service description: Enter = 'REST API Service'\n",
            "Add methods to the service?(y/N)\n",
            "Success: Service class created at: ".APP_PATH."Apis".DIRECTORY_SEPARATOR.$serviceClass."Service.php\n"
        ], $output);

        
        //Step 9: Modify the web service to use the repo
        $serviceFile = APP_PATH.'Apis'.DS.$serviceClass.'Service.php';
        $serviceContent = file_get_contents($serviceFile);
        // Add use statements for Database, App and the repo
        $serviceContent = str_replace(
            'use WebFiori\Http\WebService;',
            'use App\Infrastructure\Repository\\'.$repoClassName.';'."\n".
            'use WebFiori\Database\Database;'."\n".
            'use WebFiori\Framework\App;'."\n".
            'use WebFiori\Http\WebService;',
            $serviceContent
        );
        // Add a GET method that uses the repo
        $serviceContent = str_replace(
            'class '.$serviceClass.'Service extends WebService {',
            'class '.$serviceClass.'Service extends WebService {'."\n\n".
            '    #[GetMapping]'."\n".
            '    #[AllowAnonymous]'."\n".
            '    #[ResponseBody(200, \'success\', \'application/json\')]'."\n".
            '    public function getUsers(): array {'."\n".
            '        $conn = App::getConfig()->getDBConnection(\''.$connName.'\');'."\n".
            '        $repo = new '.$repoClassName.'(new Database($conn));'."\n".
            '        return $repo->findAll();'."\n".
            '    }',
            $serviceContent
        );
        file_put_contents($serviceFile, $serviceContent);

        //Step 10: Test the web service using APITestCase
        $serviceClassName = '\\App\\Apis\\'.$serviceClass.'Service';
        $this->assertTrue(class_exists($serviceClassName));
        $this->removeClass('\\App\\Apis\\'.$serviceClass.'Service');
        $manager = new \WebFiori\Http\WebServicesManager();
        $manager->addService(new $serviceClassName());
        $apiTest = new class('test') extends \WebFiori\Http\APITestCase {};
        $result = $apiTest->getRequest($manager, 'get-users');
        
        var_dump($result);
        
        $json = json_decode($result, true);
        $this->assertNotNull($json);

        

    }

}
