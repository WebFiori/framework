<?php
namespace webfiori\framework\test\cli;

use webfiori\database\ConnectionInfo;
use webfiori\framework\App;

/**
 * Description of CreateRESTTest
 *
 * @author Ibrahim
 */
class CreateRESTTest extends CreateTestCase {
    /**
     * @test
     */
    public function test00() {
        App::getConfig()->removeAllDBConnections();
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'rest'
        ]);
        $runner->setInputs([
            '0',
            'SuperUser',
            'app\\entity\\super',
            'y',
            'n',
            "app\\database\\super",
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
            'varchar',//type
            '50',
            'n',//primary
            'n',//unique
            '',//default
            'n',//null,
            'No Comment.',//optional comments
            "y",
            'is-happy',
            'bool',//type
            'n',
            'true',//default
            'n',//null,
            'Check if the hero is happy or not.',//optional comment
            "n",
            'n',
            "y",
            "app\\apis\\super"
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals(array_merge([
            "Warning: No database connections found in the class \"app\AppConfig\"!\n",
            "Info: Run the command \"add\" to add connections.\n",
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "First thing, we need entity class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\entity'\n",
            "Would you like from your entity class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            "Now, time to collect database table information.\n",
            "Provide us with a namespace for table class: Enter = 'app\database'\n",
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
            "Last thing needed is to provide us with namespace for web services: Enter = 'app\\apis'\n",
            "Creating entity class...\n",
            "Creating database table class...\n",
            "Creating database access class...\n",
            "Writing web services...\n",
            "Done.\n"
        ]), $runner->getOutput());
        $tableClazz = '\\app\\database\\super\\SuperUserTable';
        $entityClazz = '\\app\\entity\\super\\SuperUser';
        $dbClazz = "\\app\\database\\super\\SuperUserDB";
        $apiClazzes = [
            '\\app\\apis\\super\\AddSuperUserService',
            '\\app\\apis\\super\\DeleteSuperUserService',
            '\\app\\apis\\super\\GetAllSuperUsersService',
            '\\app\\apis\\super\\GetSuperUserService',
            '\\app\\apis\\super\\UpdateSuperUserService',
            '\\app\\apis\\super\\UpdateFirstNameOfSuperUserService',
            '\\app\\apis\\super\\UpdateIsHappyOfSuperUserService'
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
    public function test01() {
        App::getConfig()->removeAllDBConnections();
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'rest'
        ]);
        $runner->setInputs([
            '0',
            'SuperUserX',
            'app\\entity\\super',
            'y',
            'n',
            "app\\database\\super",
            "super_users",
            "A table to hold super users information.",
            "id",
            "int",
            "11",
            "n",
            'n',//unique
            '',//default
            'n',//null,
            "The unique ID of the super user.",
            "y",
            'first-name',
            'varchar',//type
            '50',
            'n',//primary
            'n',//unique
            '',//default
            'n',//null,
            'No Comment.',//optional comments
            "y",
            'is-happy',
            'bool',//type
            'n',
            'true',//default
            'n',//null,
            'Check if the hero is happy or not.',//optional comment
            "n",
            'n',
            "y",
            "app\\apis\\super"
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals(array_merge([
            "Warning: No database connections found in the class \"app\AppConfig\"!\n",
            "Info: Run the command \"add\" to add connections.\n","Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "First thing, we need entity class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\entity'\n",
            "Would you like from your entity class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            "Now, time to collect database table information.\n",
            "Provide us with a namespace for table class: Enter = 'app\database'\n",
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
            "Last thing needed is to provide us with namespace for web services: Enter = 'app\\apis'\n",
            "Creating entity class...\n",
            "Creating database table class...\n",
            "Creating database access class...\n",
            "Writing web services...\n",
            "Done.\n"
        ]), $runner->getOutput());
        $tableClazz = '\\app\\database\\super\\SuperUserXTable';
        $entityClazz = '\\app\\entity\\super\\SuperUserX';
        $dbClazz = "\\app\\database\\super\\SuperUserXDB";
        $apiClazzes = [
            '\\app\\apis\\super\\AddSuperUserXService',
            '\\app\\apis\\super\\DeleteSuperUserXService',
            '\\app\\apis\\super\\GetAllSuperUserXsService',
            '\\app\\apis\\super\\GetSuperUserXService',
            '\\app\\apis\\super\\UpdateSuperUserXService',
            '\\app\\apis\\super\\UpdateFirstNameOfSuperUserXService',
            '\\app\\apis\\super\\UpdateIsHappyOfSuperUserXService',
            '\\app\\apis\\super\\UpdateIdOfSuperUserXService'
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
        $runner = App::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'rest',
        ]);
        $runner->setInputs([
            'Super Connection',
            'SuperUserX9',
            'app\\entity\\super',
            'y',
            'n',
            "app\\database\\super",
            "super_users",
            "A table to hold super users information.",
            "id",
            "int",
            "11",
            "n",
            'n',//unique
            '',//default
            'n',//null,
            "The unique ID of the super user.",
            "y",
            'first-name',
            'varchar',//type
            '50',
            'n',//primary
            'n',//unique
            '',//default
            'n',//null,
            'No Comment.',//optional comments
            "y",
            'is-happy',
            'bool',//type
            'n',
            'true',//default
            'n',//null,
            'Check if the hero is happy or not.',//optional comment
            "n",
            'n',
            "y",
            "app\\apis\\super"
        ]);
        $this->assertEquals(0, $runner->start());
        $this->assertEquals(array_merge([
            "Select database connection:\n",
            "0: Super Connection <--\n",
            "First thing, we need entity class information.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = 'app\\entity'\n",
            "Would you like from your entity class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            "Now, time to collect database table information.\n",
            "Provide us with a namespace for table class: Enter = 'app\database'\n",
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
            "Last thing needed is to provide us with namespace for web services: Enter = 'app\\apis'\n",
            "Creating entity class...\n",
            "Creating database table class...\n",
            "Creating database access class...\n",
            "Writing web services...\n",
            "Done.\n"
        ]), $runner->getOutput());
        $tableClazz = '\\app\\database\\super\\SuperUserX9Table';
        $entityClazz = '\\app\\entity\\super\\SuperUserX9';
        $dbClazz = "\\app\\database\\super\\SuperUserX9DB";
        $apiClazzes = [
            '\\app\\apis\\super\\AddSuperUserX9Service',
            '\\app\\apis\\super\\DeleteSuperUserX9Service',
            '\\app\\apis\\super\\GetAllSuperUserX9sService',
            '\\app\\apis\\super\\GetSuperUserX9Service',
            '\\app\\apis\\super\\UpdateSuperUserX9Service',
            '\\app\\apis\\super\\UpdateFirstNameOfSuperUserX9Service',
            '\\app\\apis\\super\\UpdateIsHappyOfSuperUserX9Service',
            '\\app\\apis\\super\\UpdateIdOfSuperUserX9Service',
            '\\app\\apis\\super\\UpdateIsHappyOfSuperUserX9Service'
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
