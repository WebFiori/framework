<?php

namespace webfiori\framework\test\cli;

use PHPUnit\Framework\TestCase;
use webfiori\framework\cli\commands\CreateCommand;
use webfiori\framework\File;
use webfiori\framework\cli\ArrayInputStream;
use webfiori\framework\cli\ArrayOutputStream;
use webfiori\framework\cli\Runner;
use webfiori\database\mssql\MSSQLTable;
use webfiori\database\mysql\MySQLTable;
/**
 * Description of CreateTableTest
 *
 * @author Ibrahim
 */
class CreateTableTest extends TestCase {
    /**
     * @test
     */
    public function testCreateTable00() {
        Runner::setInputStream(new ArrayInputStream([
            '0',
            'mysql',
            'Cool00Table',
            '',
            'cool_table_00',
            'This is the first cool table that was created using CLI.',
            'id',
            '1',
            '11',
            'y',
            'y',
            'The unique ID of the cool thing.',
            'n',
            'n',
            'n'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        $this->assertEquals(0, Runner::runCommand(new CreateCommand()));
        $this->assertTrue(class_exists('\\app\\database\\Cool00Table'));
        $clazz = '\\app\\database\\Cool00Table';
        $this->removeClass($clazz);
        $testObj = new $clazz();
        $this->assertTrue($testObj instanceof MySQLTable);
        $this->assertEquals('`cool_table_00`', $testObj->getName());
        $this->assertEquals('This is the first cool table that was created using CLI.', $testObj->getComment());
        $this->assertEquals(1, $testObj->getColsCount());
        $this->assertEquals([
            'id'
        ], $testObj->getColsKeys());
        $this->assertEquals([
            '`id`'
        ], $testObj->getColsNames());
        
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: Database table from class.\n",
            "6: CLI Command.\n",
            "7: Theme.\n",
            "8: Quit. <--\n",
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"app\database\"\n",
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
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS."database\".\n",
        ], Runner::getOutputStream()->getOutputArray());
        
    }
    /**
     * @test
     */
    public function testCreateTable01() {
        Runner::setInputStream(new ArrayInputStream([
            '0',
            'mssql',
            'Cool01Table',
            '',
            'cool_table_01',
            'This is the first cool table that was created using CLI.',
            'id',
            '1',
            'y',
            'The unique ID of the cool thing.',
            'n',
            'n',
            'n'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        $this->assertEquals(0, Runner::runCommand(new CreateCommand()));
        $clazz = '\\app\\database\\Cool01Table';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
        $testObj = new $clazz();
        $this->assertTrue($testObj instanceof MSSQLTable);
        $this->assertEquals('[cool_table_01]', $testObj->getName());
        $this->assertEquals('This is the first cool table that was created using CLI.', $testObj->getComment());
        $this->assertEquals(1, $testObj->getColsCount());
        $this->assertEquals([
            'id'
        ], $testObj->getColsKeys());
        $this->assertEquals([
            '[id]'
        ], $testObj->getColsNames());
        
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: Database table from class.\n",
            "6: CLI Command.\n",
            "7: Theme.\n",
            "8: Quit. <--\n",
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"app\database\"\n",
            "Enter database table name:\n",
            "Enter your optional comment about the table:\n",
            "Now you have to add columns to the table.\n",
            "Enter a name for column key:\n",
            "Column data type:\n",
            "0: char <--\n",
            "1: int\n",
            "2: varchar\n",
            "3: nvarchar\n",
            "4: nchar\n",
            "5: binary\n",
            "6: varbinary\n",
            "7: date\n",
            "8: datetime2\n",
            "9: time\n",
            "10: money\n",
            "11: bit\n",
            "12: decimal\n",
            "13: float\n",
            "14: boolean\n",
            "15: bool\n",
            "Is this column primary?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS."database\".\n",
        ], Runner::getOutputStream()->getOutputArray());
        
    }
    /**
     * @test
     */
    public function testCreateTable03() {
        Runner::setInputStream(new ArrayInputStream([
            '0',
            'mysql',
            'Cool01Table',
            '',
            'Cool03Table',
            '',
            'cool_table_03',
            '',
            
            'id',
            '1',
            '11',
            'y',
            'y',
            'The unique ID of the cool thing.',
            'y',
            
            'name',
            '2',//type
            '400',//size
            'n',//primary
            'n',//unique
            '',//default
            'n',//null,
            'The name of the user',//optional comment
            'y',
            
            'creation-date',
            '3',//type
            'n',//primary
            'n',//unique
            '',//default
            'n',//null,
            'No Comment.',//optional comment
            'n',
            'n',

            'n',
            'n'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        $this->assertEquals(0, Runner::runCommand(new CreateCommand()));
        $output = Runner::getOutputStream()->getOutputArray();
        $this->assertTrue(class_exists('\\app\\database\\Cool03Table'));
        $clazz = '\\app\\database\\Cool03Table';
        $this->removeClass($clazz);
        $testObj = new $clazz();
        $this->assertTrue($testObj instanceof MySQLTable);
        $this->assertEquals('`cool_table_03`', $testObj->getName());
        $this->assertNull($testObj->getComment());
        $this->assertEquals(3, $testObj->getColsCount());
        $this->assertEquals([
            'id',
            'name',
            'creation-date'
        ], $testObj->getColsKeys());
        $this->assertEquals([
            '`id`',
            '`name`',
            '`creation_date`'
        ], $testObj->getColsNames());
        
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: Database table from class.\n",
            "6: CLI Command.\n",
            "7: Theme.\n",
            "8: Quit. <--\n",
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"app\database\"\n",
            "Error: A class in the given namespace which has the given name was found.\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"app\database\"\n",
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
            "Enter default value (Hit \"Enter\" to skip): Enter = \"\"\n",
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
            "Is this column unique?(y/N)\n",
            "Enter default value (Hit \"Enter\" to skip): Enter = \"\"\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS."database\".\n",
        ], $output);
        
    }
    /**
     * @test
     */
    public function testCreateTable02() {
        Runner::setInputStream(new ArrayInputStream([
            '0',
            'mysql',
            'Cool02Table',
            '',
            'cool_table_02',
            '',
            'id',
            '1',
            '11',
            'y',
            'y',
            'The unique ID of the cool thing.',
            'y',
            
            'id',
            'y',
            
            'name',
            '2',//type
            '400',//size
            'n',//primary
            'n',//unique
            '',//default
            'n',//null,
            'The name of the user',//optional comment
            'n',
            
            'n',
            'n'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        $this->assertEquals(0, Runner::runCommand(new CreateCommand()));
        $output = Runner::getOutputStream()->getOutputArray();
        $this->assertTrue(class_exists('\\app\\database\\Cool02Table'));
        $clazz = '\\app\\database\\Cool02Table';
        $this->removeClass($clazz);
        $testObj = new $clazz();
        $this->assertTrue($testObj instanceof MySQLTable);
        $this->assertEquals('`cool_table_02`', $testObj->getName());
        $this->assertNull($testObj->getComment());
        $this->assertEquals(2, $testObj->getColsCount());
        $this->assertEquals([
            'id',
            'name'
        ], $testObj->getColsKeys());
        $this->assertEquals([
            '`id`',
            '`name`'
        ], $testObj->getColsNames());
        
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: Database table from class.\n",
            "6: CLI Command.\n",
            "7: Theme.\n",
            "8: Quit. <--\n",
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"app\database\"\n",
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
            "Warning: The table already has a key with name 'id'.\n",
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
            "Enter default value (Hit \"Enter\" to skip): Enter = \"\"\n",
            "Can this column have null values?(y/N)\n",
            "Enter your optional comment about the column:\n",
            "Success: Column added.\n",
            "Would you like to add another column?(y/N)\n",
            
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS."database\".\n",
        ], $output);
        
    }
    /**
     * @test
     */
    public function testCreateTable04() {
        Runner::setInputStream(new ArrayInputStream([
            '0',
            'mysql',
            'CoolWithEntity00Table',
            '',
            'cool_table_entity_00',
            '',
            'id',
            '1',
            '11',
            'y',
            'y',
            'The unique ID of the cool thing.',
            'n',
            'n',
            'y',
            'MySuperCoolEntity00',
            '',
            'y',
            'n'
        ]));
        Runner::setOutputStream(new ArrayOutputStream());
        $this->assertEquals(0, Runner::runCommand(new CreateCommand()));
        $output = Runner::getOutputStream()->getOutputArray();
        $this->removeClass('\\app\\database\\CoolWithEntity00Table');
        $clazz = '\\app\\entity\\MySuperCoolEntity00';
        $this->assertTrue(class_exists($clazz));
        $this->removeClass($clazz);
        
        $this->assertEquals([
            "What would you like to create?\n",
            "0: Database table class.\n",
            "1: Entity class from table.\n",
            "2: Web service.\n",
            "3: Background job.\n",
            "4: Middleware.\n",
            "5: Database table from class.\n",
            "6: CLI Command.\n",
            "7: Theme.\n",
            "8: Quit. <--\n",
            "Database type:\n",
            "0: mysql\n",
            "1: mssql\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"app\database\"\n",
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
            "Would you like to add foreign keys to the table?(y/N)\n",
            "Would you like to create an entity class that maps to the database table?(y/N)\n",
            "Enter a name for the new class:\n",
            "Enter an optional namespace for the class: Enter = \"app\\entity\"\n",
            "Would you like from your entity class to implement the interface JsonI?(Y/n)\n",
            "Would you like to add extra attributes to the entity?(y/N)\n",
            'Info: New class was created at "'.ROOT_DIR.DS.'app'.DS."database\".\n",
            'Info: Entity class was created at "'.ROOT_DIR.DS.'app'.DS."entity\".\n",
        ], $output);
        
    }
    private function removeClass($classPath) {
        $file = new File(ROOT_DIR.$classPath.'.php');
        $file->remove();
    }
}
