<?php
namespace WebFiori\Framework\Test\Cli;

use WebFiori\Database\ConnectionInfo;
use WebFiori\File\File;
use WebFiori\Framework\App;
use WebFiori\Framework\Cli\CLITestCase;
use WebFiori\Framework\Cli\Commands\CreateCommand;

/**
 * Description of CreateRESTTest
 *
 * @author Ibrahim
 */
class CreateRESTTest extends CLITestCase {
    /**
     * @test
     */
    public function test00() {
        App::getConfig()->removeAllDBConnections();
        
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create',
            '--c' => 'rest'
        ], [
            '0',
            'SuperUser',
            'App\\Entity\\super',
            'y',
            'n',
            "App\\Database\\super",
            "super_users",
            "A table to hold super users information.",
            "id",
            "int",
            "11",
            "y",
            "y",
            "The unique ID of the super user.",
            "y",
            'first-name',
            'varchar',
            '50',
            'n',
            'n',
            "\n", // Hit Enter to pick default value (empty default)
            'n',
            'No Comment.',
            "y",
            'is-happy',
            'bool',
            'n',
            'true',
            'n',
            'Check if the hero is happy or not.',
            "n",
            'n',
            "y",
            "App\\Apis\\super"
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals(array_merge([
            "Warning: No database connections found in application configuration.\n",
            "Info: Run the command \"add\" to add connections.\n",
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "First thing, we need entity class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\\Entity'\n",
            "Would you like from your entity class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            "Now, time to collect database table information.\n",
            "Provide us with a namespace for table class: Enter = 'App\Database'\n",
            "Enter database table name:\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            ],
            CreateTableTest::MYSQL_COLS_TYPES,
            [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column auto increment?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            ],
            CreateTableTest::MYSQL_COLS_TYPES,
            [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            ],
            CreateTableTest::MYSQL_COLS_TYPES,
            [
            "Is this column primary?(y/N)\n",
            "Enter default value (true or false) (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to have update methods for every single column?(y/N)\n",
            "Last thing needed is to provide us with namespace for web services: Enter = 'App\\Apis'\n",
            "Creating entity class...\n",
            "Creating database table class...\n",
            "Creating database access class...\n",
            "Writing web services...\n",
            "Done.\n"
        ]), $output);
        
        $tableClazz = '\\App\\Database\\super\\SuperUserTable';
        $entityClazz = '\\App\\Entity\\super\\SuperUser';
        $dbClazz = "\\App\\Database\\super\\SuperUserDB";
        $apiClazzes = [
            '\\App\\Apis\\super\\AddSuperUserService',
            '\\App\\Apis\\super\\DeleteSuperUserService',
            '\\App\\Apis\\super\\GetAllSuperUsersService',
            '\\App\\Apis\\super\\GetSuperUserService',
            '\\App\\Apis\\super\\UpdateSuperUserService',
            '\\App\\Apis\\super\\UpdateFirstNameOfSuperUserService',
            '\\App\\Apis\\super\\UpdateIsHappyOfSuperUserService'
        ];

        foreach ($apiClazzes as $clazz) {
            $this->assertTrue(class_exists($clazz));
            $this->assertTrue(File::isFileExist(ROOT_PATH.DS. str_replace('\\', DS, $clazz).'.php'));
        }
        $this->assertTrue(class_exists($tableClazz));
        $this->assertTrue(class_exists($entityClazz));
        $this->assertTrue(class_exists($dbClazz));

        foreach ($apiClazzes as $clazz) {
            $this->removeClass($clazz);
        }
        $this->removeClass($tableClazz);
        $this->removeClass($entityClazz);
        $this->removeClass($dbClazz);
    }
    
    /**
     * @test
     */
    public function test01() {
        App::getConfig()->removeAllDBConnections();
        
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create',
            '--c' => 'rest'
        ], [
            '0',
            'SuperUserX',
            'App\\Entity\\super',
            'y',
            'n',
            "App\\Database\\super",
            "super_users",
            "A table to hold super users information.",
            "id",
            "int",
            "11",
            "n",
            'n',
            "\n", // Hit Enter to pick default value (empty default)
            'n',
            "The unique ID of the super user.",
            "y",
            'first-name',
            'varchar',
            '50',
            'n',
            'n',
            "\n", // Hit Enter to pick default value (empty default)
            'n',
            'No Comment.',
            "y",
            'is-happy',
            'bool',
            'n',
            'true',
            'n',
            'Check if the hero is happy or not.',
            "n",
            'n',
            "y",
            "App\\Apis\\super"
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals(array_merge([
            "Warning: No database connections found in application configuration.\n",
            "Info: Run the command \"add\" to add connections.\n","Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "First thing, we need entity class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\\Entity'\n",
            "Would you like from your entity class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            "Now, time to collect database table information.\n",
            "Provide us with a namespace for table class: Enter = 'App\Database'\n",
            "Enter database table name:\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            ],
            CreateTableTest::MYSQL_COLS_TYPES,
            [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            ],
            CreateTableTest::MYSQL_COLS_TYPES,
            [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            ],
            CreateTableTest::MYSQL_COLS_TYPES,
            [
            "Is this column primary?(y/N)\n",
            "Enter default value (true or false) (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to have update methods for every single column?(y/N)\n",
            "Last thing needed is to provide us with namespace for web services: Enter = 'App\\Apis'\n",
            "Creating entity class...\n",
            "Creating database table class...\n",
            "Creating database access class...\n",
            "Writing web services...\n",
            "Done.\n"
        ]), $output);
        
        $tableClazz = '\\App\\Database\\super\\SuperUserXTable';
        $entityClazz = '\\App\\Entity\\super\\SuperUserX';
        $dbClazz = "\\App\\Database\\super\\SuperUserXDB";
        $apiClazzes = [
            '\\App\\Apis\\super\\AddSuperUserXService',
            '\\App\\Apis\\super\\DeleteSuperUserXService',
            '\\App\\Apis\\super\\GetAllSuperUserXsService',
            '\\App\\Apis\\super\\GetSuperUserXService',
            '\\App\\Apis\\super\\UpdateSuperUserXService',
            '\\App\\Apis\\super\\UpdateFirstNameOfSuperUserXService',
            '\\App\\Apis\\super\\UpdateIsHappyOfSuperUserXService',
            '\\App\\Apis\\super\\UpdateIdOfSuperUserXService'
        ];

        foreach ($apiClazzes as $clazz) {
            $this->assertTrue(class_exists($clazz));
        }
        $this->assertTrue(class_exists($tableClazz));
        $this->assertTrue(class_exists($entityClazz));
        $this->assertTrue(class_exists($dbClazz));

        foreach ($apiClazzes as $clazz) {
            $this->removeClass($clazz);
        }
        $this->removeClass($tableClazz);
        $this->removeClass($entityClazz);
        $this->removeClass($dbClazz);
    }
    
    /**
     * @test
     */
    public function test02() {
        App::getConfig()->removeAllDBConnections();
        $conn = new ConnectionInfo('mysql','root', '123456', 'testing_db', '127.0.0.1', 3306, [
            'connection-name' => 'Super Connection'
        ]);
        App::getConfig()->addOrUpdateDBConnection($conn);
        
        $output = $this->executeSingleCommand(new CreateCommand(), [
            'WebFiori',
            'create',
            '--c' => 'rest',
        ], [
            'Super Connection',
            'SuperUserX9',
            'App\\Entity\\super',
            'y',
            'n',
            "App\\Database\\super",
            "super_users",
            "A table to hold super users information.",
            "id",
            "int",
            "11",
            "n",
            'n',
            "\n", // Hit Enter to pick default value (empty default)
            'n',
            "The unique ID of the super user.",
            "y",
            'first-name',
            'varchar',
            '50',
            'n',
            'n',
            "\n", // Hit Enter to pick default value (empty default)
            'n',
            'No Comment.',
            "y",
            'is-happy',
            'bool',
            'n',
            'true',
            'n',
            'Check if the hero is happy or not.',
            "n",
            'n',
            "y",
            "App\\Apis\\super"
        ]);

        $this->assertEquals(0, $this->getExitCode());
        $this->assertEquals(array_merge([
            "Select database connection:\n",
            "0: Super Connection <--\n",
            "First thing, we need entity class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'App\\Entity'\n",
            "Would you like from your entity class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            "Now, time to collect database table information.\n",
            "Provide us with a namespace for table class: Enter = 'App\Database'\n",
            "Enter database table name:\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            ],
            CreateTableTest::MYSQL_COLS_TYPES,
            [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            ],
            CreateTableTest::MYSQL_COLS_TYPES,
            [
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n"],
            CreateTableTest::MYSQL_COLS_TYPES,
            ["Is this column primary?(y/N)\n",
            "Enter default value (true or false) (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to have update methods for every single column?(y/N)\n",
            "Last thing needed is to provide us with namespace for web services: Enter = 'App\\Apis'\n",
            "Creating entity class...\n",
            "Creating database table class...\n",
            "Creating database access class...\n",
            "Writing web services...\n",
            "Done.\n"
        ]), $output);
        
        $tableClazz = '\\App\\Database\\super\\SuperUserX9Table';
        $entityClazz = '\\App\\Entity\\super\\SuperUserX9';
        $dbClazz = "\\App\\Database\\super\\SuperUserX9DB";
        $apiClazzes = [
            '\\App\\Apis\\super\\AddSuperUserX9Service',
            '\\App\\Apis\\super\\DeleteSuperUserX9Service',
            '\\App\\Apis\\super\\GetAllSuperUserX9sService',
            '\\App\\Apis\\super\\GetSuperUserX9Service',
            '\\App\\Apis\\super\\UpdateSuperUserX9Service',
            '\\App\\Apis\\super\\UpdateFirstNameOfSuperUserX9Service',
            '\\App\\Apis\\super\\UpdateIsHappyOfSuperUserX9Service',
            '\\App\\Apis\\super\\UpdateIdOfSuperUserX9Service',
            '\\App\\Apis\\super\\UpdateIsHappyOfSuperUserX9Service'
        ];

        foreach ($apiClazzes as $clazz) {
            $this->assertTrue(class_exists($clazz));
        }
        $this->assertTrue(class_exists($tableClazz));
        $this->assertTrue(class_exists($entityClazz));
        $this->assertTrue(class_exists($dbClazz));

        foreach ($apiClazzes as $clazz) {
            $this->removeClass($clazz);
        }
        $this->removeClass($tableClazz);
        $this->removeClass($entityClazz);
        $this->removeClass($dbClazz);
    }
}
