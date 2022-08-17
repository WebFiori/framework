<?php

namespace webfiori\framework\test\cli;

use webfiori\framework\WebFioriApp;

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
        $runner = WebFioriApp::getRunner();
        $runner->setArgsVector([
            'webfiori',
            'create',
            '--c' => 'rest'
        ]);
        $runner->setInput([
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
        $this->assertEquals([
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
            "Enter a name for column key:\n",
            "Column data type:\n",
            "0: char <--\n",
            "1: int\n",
            "2: varchar\n",
            "3: timestamp\n",
            "4: tinyblob\n",
            "5: blob\n",
            "6: mediumblob\n",
            "7: longblob\n",
            "8: datetime\n",
            "9: text\n",
            "10: mediumtext\n",
            "11: decimal\n",
            "12: double\n",
            "13: float\n",
            "14: boolean\n", 
            "15: bool\n",
            "16: bit\n",
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column auto increment?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Enter a name for column key:\n",
            "Column data type:\n",
            "0: char <--\n",
            "1: int\n",
            "2: varchar\n",
            "3: timestamp\n",
            "4: tinyblob\n",
            "5: blob\n",
            "6: mediumblob\n",
            "7: longblob\n",
            "8: datetime\n",
            "9: text\n",
            "10: mediumtext\n",
            "11: decimal\n",
            "12: double\n",
            "13: float\n",
            "14: boolean\n", 
            "15: bool\n",
            "16: bit\n",
            "Enter column size:\n",
            "Is this column primary?(y/N)\n",
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = ''\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Enter a name for column key:\n",
            "Column data type:\n",
            "0: char <--\n",
            "1: int\n",
            "2: varchar\n",
            "3: timestamp\n",
            "4: tinyblob\n",
            "5: blob\n",
            "6: mediumblob\n",
            "7: longblob\n",
            "8: datetime\n",
            "9: text\n",
            "10: mediumtext\n",
            "11: decimal\n",
            "12: double\n",
            "13: float\n",
            "14: boolean\n", 
            "15: bool\n",
            "16: bit\n",
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
        ], $runner->getOutput());
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
}
